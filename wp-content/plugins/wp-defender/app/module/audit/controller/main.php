<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Audit\Controller;

use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Audit\Component\Audit_API;
use WP_Defender\Module\Audit\Model\Events;
use WP_Defender\Module\Audit\Model\Settings;

class Main extends \WP_Defender\Controller {
	protected $slug = 'wdf-logging';

	/**
	 * Declaring behaviors
	 * @return array
	 */
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

	public function __construct() {
		if ( $this->isNetworkActivate( wp_defender()->plugin_slug ) ) {
			$this->addAction( 'network_admin_menu', 'adminMenu' );
		} else {
			$this->addAction( 'admin_menu', 'adminMenu' );
		}

		if ( $this->isInPage() || $this->isDashboard() ) {
			$this->addAction( 'defender_enqueue_assets', 'scripts', 11 );
		}

		if ( Settings::instance()->enabled == 1 ) {
			$this->addAction( 'wp_loaded', 'setupEvents', 1 );
			$this->addAction( 'shutdown', 'triggerEventSubmit' );
			if ( ! wp_next_scheduled( 'auditSyncWithCloud' ) ) {
				wp_schedule_event( time(), 'daily', 'auditSyncWithCloud' );
			}
			$this->addAction( 'auditSyncWithCloud', 'syncWithCloud' );
		}
		//report cron
		$this->addAction( 'auditReportCron', 'auditReportCron' );
	}

	public function syncWithCloud() {
		Events::instance()->sendToApi();
		Events::instance()->fetch();
		Events::instance()->checksumData();
	}

	public function sort_email_data( $a, $b ) {
		return $a['count'] < $b['count'];
	}

	/**
	 * hook all the action for listening on events
	 */
	public function setupEvents() {
		Audit_API::setupEvents();
	}

	public function triggerEventSubmit() {
		$data = WP_Helper::getArrayCache()->get( 'events_queue', array() );
		if ( is_array( $data ) && count( $data ) ) {
			if ( Events::instance()->hasData( [], true ) ) {
				Events::instance()->append( $data );
			} else {
				Audit_API::onCloud( $data );
			}
		}
	}

	/**
	 * Sending report email by cron
	 */
	public function auditReportCron() {
		if ( wp_defender()->isFree ) {
			return;
		}

		$settings = Settings::instance();

		if ( $settings->notification == false ) {
			return;
		}

		$lastReportSent = $settings->lastReportSent;
		if ( $lastReportSent == null ) {
			//no sent, so just assume last 30 days, as this only for monthly
			$lastReportSent = strtotime( '-31 days', current_time( 'timestamp' ) );
		}

		if ( ! $this->isReportTime( $settings->frequency, $settings->day, $lastReportSent ) ) {
			return false;
		}

		switch ( $settings->frequency ) {
			case 1:
				$date_from = strtotime( '-24 hours' );
				$date_to   = time();
				break;
			case 7:
				$date_from = strtotime( '-7 days' );
				$date_to   = time();
				break;
			case 30:
				$date_from = strtotime( '-30 days' );
				$date_to   = time();
				break;
			default:
				//param not from the button on frontend, log it
				error_log( sprintf( 'Unexpected value %s from IP %s', $settings->frequency, Utils::instance()->getUserIp() ) );
				break;
		}

		if ( ! isset( $date_from ) && ! isset( $date_to ) ) {
			//something wrong
			return;
		}

		$date_from = date( 'Y-m-d', $date_from );
		$date_to   = date( 'Y-m-d', $date_to );
		$filters   = [
			'date_from' => $date_from . ' 0:00:00',
			'date_to'   => $date_to . ' 23:59:59',
			'paged'     => - 1,
		];
		if ( Events::instance()->hasData() ) {
			$logs = Events::instance()->getData( $filters );
		} else {
			$logs = Audit_API::pullLogs( $filters );
			if ( is_wp_error( $logs ) ) {
				return;
			}
		}

		$data       = $logs['data'];
		$email_data = array();
		foreach ( $data as $row => $val ) {
			if ( ! isset( $email_data[ $val['event_type'] ] ) ) {
				$email_data[ $val['event_type'] ] = array(
					'count' => 0
				);
			}

			if ( ! isset( $email_data[ $val['event_type'] ][ $val['action_type'] ] ) ) {
				$email_data[ $val['event_type'] ][ $val['action_type'] ] = 1;
			} else {
				$email_data[ $val['event_type'] ][ $val['action_type'] ] += 1;
			}
			$email_data[ $val['event_type'] ]['count'] += 1;
		}

		uasort( $email_data, array( &$this, 'sort_email_data' ) );

		//now we create a table
		if ( is_array( $email_data ) && count( $email_data ) ) {
			ob_start();
			?>
			<table class="wrapper main" align="center"
			       style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
				<tbody>
				<tr style="padding: 0; text-align: left; vertical-align: top;">
					<td class="wrapper-inner main-inner"
					    style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; hyphens: auto; line-height: 26px; margin: 0; padding: 40px; text-align: left; vertical-align: top; word-wrap: break-word;">

						<table class="main-intro"
						       style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top;">
							<tbody>
							<tr style="padding: 0; text-align: left; vertical-align: top;">
								<td class="main-intro-content"
								    style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; hyphens: auto; line-height: 26px; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
									<h3 style="Margin: 0; Margin-bottom: 0; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 32px; font-weight: normal; line-height: 32px; margin: 0; margin-bottom: 0; padding: 0 0 28px; text-align: left; word-wrap: normal;"><?php _e( "Hi {USER_NAME},", wp_defender()->domain ) ?></h3>
									<p style="Margin: 0; Margin-bottom: 0; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; line-height: 26px; margin: 0; margin-bottom: 0; padding: 0 0 24px; text-align: left;">
										<?php printf( __( "It's WP Defender here, reporting from the frontline with a quick update on what's been happening at <a href=\"%s\">%s</a>.", wp_defender()->domain ), network_site_url(), network_site_url() ) ?></p>
								</td>
							</tr>
							</tbody>
						</table>

						<table class="results-list"
						       style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top;">
							<thead class="results-list-header" style="border-bottom: 2px solid #ff5c28;">
							<tr style="padding: 0; text-align: left; vertical-align: top;">
								<th class="result-list-label-title"
								    style="Margin: 0; color: #ff5c28; font-family: Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; line-height: 48px; margin: 0; padding: 0; text-align: left; width: 35%;">
									<?php _e( "Event Type", wp_defender()->domain ) ?>
								</th>
								<th class="result-list-data-title"
								    style="Margin: 0; color: #ff5c28; font-family: Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; line-height: 48px; margin: 0; padding: 0; text-align: left;">
									<?php _e( "Action Summaries", wp_defender()->domain ) ?>
								</th>
							</tr>
							</thead>
							<tbody class="results-list-content">
							<?php $count = 0; ?>
							<?php foreach ( $email_data as $key => $row ): ?>
								<tr style="padding: 0; text-align: left; vertical-align: top;">
									<?php if ( $count == 0 ) {
										$style = '-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 700; hyphens: auto; line-height: 28px; margin: 0; padding: 20px 5px; text-align: left; vertical-align: top; word-wrap: break-word;';
									} else {
										$style = '-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; border-top: 2px solid #ff5c28; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 700; hyphens: auto; line-height: 28px; margin: 0; padding: 20px 5px; text-align: left; vertical-align: top; word-wrap: break-word;';
									} ?>
									<td class="result-list-label bordered"
									    style="<?php echo $style ?>">
										<?php echo ucfirst( Audit_API::get_action_text( strtolower( $key ) ) ) ?>
									</td>
									<td class="result-list-data bordered"
									    style="<?php echo $style ?>">
										<?php foreach ( $row as $i => $v ): ?>
											<?php if ( $i == 'count' ) {
												continue;
											} ?>
											<span
													style="display: inline-block; font-weight: 400; width: 100%;">
												<?php echo ucwords( Audit_API::get_action_text( strtolower( $i ) ) ) ?>
                                                : <?php echo $v ?>
											</span>
										<?php endforeach; ?>
									</td>
								</tr>
								<?php $count ++; ?>
							<?php endforeach; ?>
							</tbody>
							<tfoot class="results-list-footer">
							<tr style="padding: 0; text-align: left; vertical-align: top;">
								<td colspan="2"
								    style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; hyphens: auto; line-height: 26px; margin: 0; padding: 10px 0 0; text-align: left; vertical-align: top; word-wrap: break-word;">
									<p style="Margin: 0; Margin-bottom: 0; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; line-height: 26px; margin: 0; margin-bottom: 0; padding: 0 0 24px; text-align: left;">
										<a class="plugin-brand"
										   href="{LOGS_URL}"
										   style="Margin: 0; color: #ff5c28; display: inline-block; font: inherit; font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left; text-decoration: none;"><?php _e( "You can view the full audit report for your site here.", wp_defender()->domain ) ?>
											<img
													class="icon-arrow-right"
													src="<?php echo wp_defender()->getPluginUrl() ?>assets/email-images/icon-arrow-right-defender.png"
													alt="Arrow"
													style="-ms-interpolation-mode: bicubic; border: none; clear: both; display: inline-block; margin: -2px 0 0 5px; max-width: 100%; outline: none; text-decoration: none; vertical-align: middle; width: auto;"></a>
									</p>
								</td>
							</tr>
							</tfoot>
						</table>
						<table class="main-signature"
						       style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top;">
							<tbody>
							<tr style="padding: 0; text-align: left; vertical-align: top;">
								<td class="main-signature-content"
								    style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; hyphens: auto; line-height: 26px; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
									<p style="Margin: 0; Margin-bottom: 0; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; line-height: 26px; margin: 0; margin-bottom: 0; padding: 0 0 24px; text-align: left;">
										Stay safe,</p>
									<p class="last-item"
									   style="Margin: 0; Margin-bottom: 0; color: #555555; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; line-height: 26px; margin: 0; margin-bottom: 0; padding: 0; text-align: left;">
										WP Defender <br><strong>WPMU DEV Security Hero</strong></p>
								</td>
							</tr>
							</tbody>
						</table>
					</td>
				</tr>
				</tbody>
			</table>
			<?php
			$table = ob_get_clean();
		} else {
			$table = '<p>' . sprintf( esc_html__( "There were no events logged for %s", wp_defender()->domain ), network_site_url() ) . '</p>';
		}

		$template = $this->renderPartial( 'email_template', array(
			'message' => $table,
			'subject' => sprintf( esc_html__( "Here's what's been happening at %s", wp_defender()->domain ), network_site_url() )
		), false );


		foreach ( Settings::instance()->receipts as $item ) {
			//prepare the parameters
			$email = $item['email'];

			$logs_url       = network_admin_url( 'admin.php?page=wdf-logging&date_from=' . date( 'm/d/Y', strtotime( $date_from ) ) . '&date_to=' . date( 'm/d/Y', strtotime( $date_to ) ) );
			$logs_url       = apply_filters( 'report_email_logs_link', $logs_url, $email );
			$no_reply_email = "noreply@" . parse_url( get_site_url(), PHP_URL_HOST );
			$no_reply_email = apply_filters( 'wd_audit_noreply_email', $no_reply_email );
			$headers        = array(
				'From: Defender <' . $no_reply_email . '>',
				'Content-Type: text/html; charset=UTF-8'
			);
			$params         = array(
				'USER_NAME' => $item['first_name'],
				'SITE_URL'  => network_site_url(),
				'LOGS_URL'  => $logs_url
			);
			$email_content  = $template;
			foreach ( $params as $key => $val ) {
				$email_content = str_replace( '{' . $key . '}', $val, $email_content );
			}
			wp_mail( $email, sprintf( __( "Here's what's been happening at %s", wp_defender()->domain ), network_site_url() ), $email_content, $headers );
		}

		$settings->lastReportSent = time();
		$settings->save();
	}

	/**
	 * Add submit admin page
	 */
	public function adminMenu() {
		$cap = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_submenu_page( 'wp-defender', esc_html__( "Audit Logging", wp_defender()->domain ), esc_html__( "Audit Logging", wp_defender()->domain ), $cap, $this->slug, array(
			&$this,
			'actionIndex'
		) );
	}

	public function scripts() {
		if ( $this->isInPage() ) {
			wp_enqueue_style( 'wpmudev-sui' );
			wp_enqueue_style( 'defender' );

			wp_register_script( 'defender-audit', wp_defender()->getPluginUrl() . 'assets/app/audit.js', array(
				'def-vue',
				'defender',
				'wp-i18n'
			), wp_defender()->version, true );
			wp_localize_script( 'defender-audit', 'auditData', $this->_scriptsData() );
			Utils::instance()->createTranslationJson( 'defender-audit' );
			wp_set_script_translations( 'defender-audit', 'wpdef', wp_defender()->getPluginPath() . 'languages' );
			wp_enqueue_script( 'defender-audit' );
			wp_enqueue_script( 'wpmudev-sui' );
			wp_enqueue_script( 'audit-momentjs', wp_defender()->getPluginUrl() . 'assets/js/vendor/moment/moment.min.js' );
//			wp_enqueue_style( 'audit-daterangepicker', wp_defender()->getPluginUrl() . 'assets/js/vendor/daterangepicker/daterangepicker.css' );
			wp_enqueue_script( 'audit-daterangepicker', wp_defender()->getPluginUrl() . 'assets/js/vendor/daterangepicker/daterangepicker.js' );
		}
	}

	/**
	 * @return array
	 * @throws \ReflectionException
	 */
	public function _scriptsData() {
		if ( ! $this->checkPermission() ) {
			return [];
		}
		$settings = Settings::instance();
		$tz       = get_option( 'gmt_offset' );
		if ( substr( $tz, 0, 1 ) == '-' ) {
			$tz = ' - ' . str_replace( '-', '', $tz );
		} else {
			$tz = ' + ' . $tz;
		}

		return [
			'filters'   => [
				'types' => Audit_API::getEventType()
			],
			'summary'   => [
				'report_time' => $settings->get_report_times_as_string()
			],
			'misc'      => [
				'date_format'  => Utils::instance()->convertPHPToMomentFormat( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
				'times_of_day' => Utils::instance()->getTimes(),
				'days_of_week' => Utils::instance()->getDaysOfWeek(),
				'tz'           => $tz,
				'current_time' => \WP_Defender\Behavior\Utils::instance()->formatDateTime( current_time( 'timestamp' ), false )
			],
			'model'     => [
				'report'   => $settings->exportByKeys( [ 'notification', 'receipts', 'frequency', 'day', 'time' ] ),
				'settings' => $settings->exportByKeys( [ 'storage_days' ] )
			],
			'endpoints' => $this->getAllAvailableEndpoints( \WP_Defender\Module\Audit::getClassName() ),
			'nonces'    => [
				'loadData'       => wp_create_nonce( 'loadData' ),
				'updateSettings' => wp_create_nonce( 'updateSettings' ),
				'exportAsCvs'    => wp_create_nonce( 'exportAsCvs' )
			],
			'enabled'   => $settings->enabled
		];
	}

	/**
	 * Main screen
	 */
	public function actionIndex() {
		$this->render( 'main' );
	}
}