<?php

namespace WP_Defender\Module\Scan\Controller;

use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Scan;
use WP_Defender\Module\Scan\Component\Scan_Api;
use WP_Defender\Module\Scan\Model\Settings;
use WP_Defender\Module\Scan\Model\Result_Item;

/**
 * Author: Hoang Ngo
 */
class Main extends \WP_Defender\Controller {
	protected $slug = 'wdf-scan';

	/**
	 * @return array
	 */
	public function behaviors() {
		$behaviors = [
			'utils'     => '\WP_Defender\Behavior\Utils',
			'endpoints' => '\WP_Defender\Behavior\Endpoint',
			'wpmudev'   => '\WP_Defender\Behavior\WPMUDEV'
		];

		if ( wp_defender()->isFree == false ) {
			$behaviors['pro'] = '\WP_Defender\Module\Scan\Behavior\Pro\Reporting';
		}

		return $behaviors;
	}

	/**
	 * Main constructor.
	 */
	public function __construct() {
		if ( $this->isNetworkActivate( wp_defender()->plugin_slug ) ) {
			$this->addAction( 'network_admin_menu', 'adminMenu' );
		} else {
			$this->addAction( 'admin_menu', 'adminMenu' );
		}

		if ( $this->isInPage() || $this->isDashboard() ) {
			$this->addAction( 'defender_enqueue_assets', 'scripts', 11 );
		}

		//process scan in background
		$this->addAction( 'processScanCron', 'processScanCron' );
		//scan as schedule
		$this->addAction( 'scanReportCron', 'scanReportCron' );

		$this->addAction( 'sendScanEmail', 'sendEmailReport' );
	}

	/**
	 * @return bool|void
	 */
	public function scanReportCron() {
		if ( wp_defender()->isFree ) {
			return;
		}

		$settings       = Settings::instance();
		$lastReportSent = $settings->last_report_sent;
		if ( $lastReportSent == null ) {
			$model = Scan_Api::getLastScan();
			if ( is_object( $model ) ) {
				$lastReportSent             = $model->dateFinished;
				$settings->last_report_sent = $lastReportSent;
				//init
				$settings->save();
			} else {
				//no sent, so just assume last 30 days, as this only for monthly
				$lastReportSent = strtotime( '-31 days', current_time( 'timestamp' ) );
			}
		}

		if ( ! $this->isReportTime( $settings->frequency, $settings->day, $lastReportSent ) ) {
			return false;
		}

		//need to check if we already have a scan in progress
		$activeScan = Scan_Api::getActiveScan();
		if ( ! is_object( $activeScan ) ) {
			Scan_Api::createScan();
			$model       = Scan_Api::getActiveScan( true );
			$model->logs = 'report';
			$model->save();
			wp_clear_scheduled_hook( 'processScanCron' );
			wp_schedule_single_event( strtotime( '+1 minutes' ), 'processScanCron' );
		}
	}

	/**
	 * Process a scan via cronjob, only use to process in background, not create a new scan
	 */
	public function processScanCron() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX == true ) {
			//we dont process if ajax, this only for active scan
			return;
		}
		//sometime the scan get stuck, queue it first
		wp_schedule_single_event( strtotime( '+1 minutes' ), 'processScanCron' );

		$activeScan = Scan_Api::getActiveScan();
		if ( ! is_object( $activeScan ) ) {
			//no scan created, return
			return;
		}

		$scanning = new Scan\Component\Scanning();
		$ret      = $scanning->run();

		if ( $ret == true ) {
			//completed
			$this->sendEmailReport();
			$this->submitStatsToDev();
			//scan done, remove the background cron
			wp_clear_scheduled_hook( 'processScanCron' );
		}
	}

	/**
	 * Add submit admin page
	 */
	public function adminMenu() {
		$cap = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_submenu_page( 'wp-defender', esc_html__( "Malware Scanning", wp_defender()->domain ),
			esc_html__( "Malware Scanning", wp_defender()->domain ), $cap, $this->slug, array(
				&$this,
				'actionIndex'
			) );
	}

	/**
	 * Enqueue scripts & styles
	 */
	public function scripts() {
		if ( $this->isInPage() ) {
			wp_enqueue_style( 'defender' );

			wp_register_script( 'defender-scan', wp_defender()->getPluginUrl() . 'assets/app/scan.js', [
				'def-vue',
				'defender',
				'wp-i18n'
			], wp_defender()->version, true );
			wp_enqueue_script( 'defender-prism', wp_defender()->getPluginUrl() . 'assets/js/vendor/prism/prism.js' );
			wp_localize_script( 'defender-scan', 'scanData', $this->_scriptsData() );
			Utils::instance()->createTranslationJson( 'defender-scan' );
			wp_set_script_translations( 'defender-scan', 'wpdef', wp_defender()->getPluginPath() . 'languages' );
			wp_enqueue_script( 'defender-scan' );
			wp_enqueue_script( 'wpmudev-sui' );
		}
	}

	public function _scriptsData() {
		if ( ! $this->checkPermission() ) {
			return [];
		}
		$data = Scan\Component\Data_Factory::buildData();
		$data = array_merge( $data, [
			'nonces'    => [
				'newScan'        => wp_create_nonce( 'newScan' ),
				'processScan'    => wp_create_nonce( 'processScan' ),
				'updateSettings' => wp_create_nonce( 'updateSettings' ),
				'cancelScan'     => wp_create_nonce( 'cancelScan' ),
				'getFileSrcCode' => wp_create_nonce( 'getFileSrcCode' ),
				'ignoreIssue'    => wp_create_nonce( 'ignoreIssue' ),
				'unignoreIssue'  => wp_create_nonce( 'unignoreIssue' ),
				'deleteIssue'    => wp_create_nonce( 'deleteIssue' ),
				'solveIssue'     => wp_create_nonce( 'solveIssue' ),
				'bulkAction'     => wp_create_nonce( 'bulkAction' )
			],
			'endpoints' => $this->getAllAvailableEndpoints( Scan::getClassName() ),
		] );

		return $data;
	}

	/**
	 * Internal route for this module
	 */
	public function actionIndex() {
		$this->render( 'main' );
	}

	public function sendEmailReport( $force = false ) {
		$settings = Settings::instance();

		$model = Scan_Api::getLastScan();
		if ( ! is_object( $model ) ) {
			return;
		}

		$count = $model->countAll( Result_Item::STATUS_ISSUE );

		//Check one instead of validating both conditions
		if ( $model->logs == 'report' ) {
			if ( $settings->report == false ) {
				return;
			}

			if ( $settings->always_send == false && $count == 0 ) {
				return;
			}

			$recipients                 = $settings->recipients;
			$settings->last_report_sent = current_time( 'timestamp' );
		} else {
			if ( $settings->notification == false ) {
				return;
			}

			if ( $settings->always_send_notification == false && $count == 0 ) {
				return;
			}

			$recipients = $settings->recipients_notification;
		}

		if ( empty( $recipients ) ) {
			return;
		}

		foreach ( $recipients as $item ) {
			//prepare the parameters
			$email  = $item['email'];
			$params = array(
				'USER_NAME'      => $item['first_name'],
				'ISSUES_COUNT'   => $count,
				'SCAN_PAGE_LINK' => apply_filters( 'report_email_logs_link',
					network_admin_url( 'admin.php?page=wdf-scan' ), $email ),
				'ISSUES_LIST'    => $this->issuesListHtml( $model ),
				'SITE_URL'       => network_site_url(),
			);
			$params = apply_filters( 'wd_notification_email_params', $params );
			if ( $count == 0 ) {
				$subject       = apply_filters( 'wd_notification_email_subject', $settings->email_subject );
				$email_content = $settings->email_all_ok;
			} else {
				$subject       = apply_filters( 'wd_notification_email_subject_issue', $settings->email_subject_issue );
				$email_content = $settings->email_has_issue;
			}
			$subject       = stripslashes( $subject );
			$email_content = apply_filters( 'wd_notification_email_content_before', $email_content, $model );
			foreach ( $params as $key => $val ) {
				$email_content = str_replace( '{' . $key . '}', $val, $email_content );
				$subject       = str_replace( '{' . $key . '}', $val, $subject );
			}
			//change nl to br
			$email_content = wpautop( stripslashes( $email_content ) );
			$email_content = apply_filters( 'wd_notification_email_content_after', $email_content, $model );

			$email_template = $this->renderPartial( 'email-template', array(
				'subject' => $subject,
				'message' => $email_content
			), false );
			$no_reply_email = "noreply@" . parse_url( get_site_url(), PHP_URL_HOST );
			$no_reply_email = apply_filters( 'wd_scan_noreply_email', $no_reply_email );
			$headers        = array(
				'From: Defender <' . $no_reply_email . '>',
				'Content-Type: text/html; charset=UTF-8'
			);
			wp_mail( $email, $subject, $email_template, $headers );
		}
	}

	/**
	 * Build issues html table
	 *
	 * @param $model
	 *
	 * @return string
	 * @access private
	 * @since 1.0
	 */
	private function issuesListHtml( Scan\Model\Scan $model ) {
		ob_start();
		?>
        <table class="results-list"
               style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top;">
            <thead class="results-list-header" style="border-bottom: 2px solid #ff5c28;">
            <tr style="padding: 0; text-align: left; vertical-align: top;">
                <th class="result-list-label-title"
                    style="Margin: 0; color: #ff5c28; font-family: Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; line-height: 48px; margin: 0; padding: 0; text-align: left; width: 35%;"><?php esc_html_e( "File",
						wp_defender()->domain ) ?></th>
                <th class="result-list-data-title"
                    style="Margin: 0; color: #ff5c28; font-family: Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; line-height: 48px; margin: 0; padding: 0; text-align: left;"><?php esc_html_e( "Issue",
						wp_defender()->domain ) ?></th>
            </tr>
            </thead>
            <tbody class="results-list-content">
			<?php foreach ( $model->getItems() as $k => $item ): ?>
				<?php if ( $k == 0 ): ?>
                    <tr style="padding: 0; text-align: left; vertical-align: top;">
                        <td class="result-list-label"
                            style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 700; hyphens: auto; line-height: 28px; margin: 0; padding: 20px 5px; text-align: left; vertical-align: top; word-wrap: break-word;"><?php echo $item->getTitle() ?>
                            <span
                                    style="display: inline-block; font-weight: 400; width: 100%;"><?php echo $item->getSubTitle() ?></span>
                        </td>
                        <td class="result-list-data"
                            style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 700; hyphens: auto; line-height: 28px; margin: 0; padding: 20px 5px; text-align: left; vertical-align: top; word-wrap: break-word;"><?php echo $item->getIssueDetail() ?></td>
                    </tr>
				<?php else: ?>
                    <tr style="padding: 0; text-align: left; vertical-align: top;">
                        <td class="result-list-label <?php echo $k > 0 ? " bordered" : null ?>"
                            style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; border-top: 2px solid #ff5c28; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 700; hyphens: auto; line-height: 28px; margin: 0; padding: 20px 5px; text-align: left; vertical-align: top; word-wrap: break-word;"><?php echo $item->getTitle() ?>
                            <span
                                    style="display: inline-block; font-weight: 400; width: 100%;"><?php echo $item->getSubTitle() ?></span>
                        </td>
                        <td class="result-list-data <?php echo $k > 0 ? " bordered" : null ?>"
                            style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; border-top: 2px solid #ff5c28; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 700; hyphens: auto; line-height: 28px; margin: 0; padding: 20px 5px; text-align: left; vertical-align: top; word-wrap: break-word;"><?php echo $item->getIssueDetail() ?></td>
                    </tr>
				<?php endif; ?>
			<?php endforeach; ?>
            </tbody>
            <tfoot class="results-list-footer">
            <tr style="padding: 0; text-align: left; vertical-align: top;">
                <td colspan="2"
                    style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; hyphens: auto; line-height: 26px; margin: 0; padding: 10px 0 0; text-align: left; vertical-align: top; word-wrap: break-word;">
                    <p style="Margin: 0; Margin-bottom: 0; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; line-height: 26px; margin: 0; margin-bottom: 0; padding: 0 0 24px; text-align: left;">
                        <a class="plugin-brand" href="<?php echo network_admin_url( 'admin.php?page=wdf-scan' ) ?>"
                           style="Margin: 0; color: #ff5c28; display: inline-block; font: inherit; font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left; text-decoration: none;"><?php esc_html_e( "Let's get your site patched up.",
								wp_defender()->domain ) ?>
                            <img class="icon-arrow-right"
                                 src="<?php echo wp_defender()->getPluginUrl() ?>assets/email-images/icon-arrow-right-defender.png"
                                 alt="Arrow"
                                 style="-ms-interpolation-mode: bicubic; border: none; clear: both; display: inline-block; margin: -2px 0 0 5px; max-width: 100%; outline: none; text-decoration: none; vertical-align: middle; width: auto;"></a>
                    </p>
                </td>
            </tr>
            </tfoot>
        </table>
		<?php
		return ob_get_clean();
	}
}