<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Controller;

use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\Hardener;

class Main extends Controller {
	protected $slug = 'wdf-hardener';

	/**
	 * @return array
	 */
	public function behaviors() {
		$behaviors = [
			'utils'     => '\WP_Defender\Behavior\Utils',
			'endpoints' => '\WP_Defender\Behavior\Endpoint',
			'wpmudev'   => '\WP_Defender\Behavior\WPMUDEV'
		];

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

		if ( $this->isInPage() ) {
			$this->addAction( 'defender_enqueue_assets', 'scripts', 11 );
		}

		if ( ! wp_next_scheduled( 'tweaksSendNotification' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'tweaksSendNotification' );
		}

		$this->addAction( 'tweaksSendNotification', 'tweaksSendNotification' );
	}

	public function tweaksSendNotification() {
		$settings = Hardener\Model\Settings::instance();
		//if last seen very near, do no thing
		if ( ! $settings->last_seen ) {
			//should not in here
			$settings->last_seen = time();
			$settings->save();
		}

		if ( strtotime( apply_filters( 'wd_tweaks_notification_interval', '+24 hours' ), apply_filters( 'wd_tweaks_last_action_time', $settings->last_seen ) ) > time() ) {
			return;
		}

		$tweaks = Hardener\Model\Settings::instance()->getIssues();
		if ( count( $tweaks ) == 0 ) {
			//no issue no email
			return;
		}
		$no_reply_email = "noreply@" . parse_url( get_site_url(), PHP_URL_HOST );
		$no_reply_email = apply_filters( 'wd_scan_noreply_email', $no_reply_email );
		$headers        = array(
			'From: Defender <' . $no_reply_email . '>',
			'Content-Type: text/html; charset=UTF-8'
		);

		$subject = _n( 'Security Tweak Report for %s. %s tweak needs attention.', 'Security Tweak Report for %s. %s tweaks needs attention.', count( $tweaks ), wp_defender()->domain );
		$subject = sprintf( $subject, network_site_url(), count( $tweaks ) );
		$canSend = false;
		if ( $settings->last_sent == null ) {
			//this is the case user install this and never check the page
			//send report
			$canSend = true;
		} elseif ( strtotime( apply_filters( 'wd_tweaks_notification_interval', '+24 hours' ), apply_filters( 'wd_tweaks_last_notification_sent', $settings->last_sent ) ) < time() ) {
			//this is the case email already sent once last 24 hours
			if ( $settings->notification == false ) {
				//no repeat
				return;
			}
			$canSend = true;
		}

		if ( $canSend ) {
			foreach ( $settings->receipts as $receipt ) {
				$email = $receipt['email'];
				$ret   = wp_mail( $email, $subject, $this->prepareEmailContent( $receipt['first_name'], $email ), $headers );
			}
			$settings->last_sent = time();
			$settings->save();
		}
	}

	private function prepareEmailContent( $firstName, $email = null ) {
		$issues = "";
		foreach ( Hardener\Model\Settings::instance()->getIssues() as $issue ) {
			$issue  = '<tr style="border:none;padding:0;text-align:left;vertical-align:top">
                                                            <td class="wpmudev-table__row--label"
                                                                style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;border-radius:0 0 0 4px;border-top:.5px solid #d8d8d8;color:#333;font-family:\'Open Sans\',Helvetica,Arial,sans-serif;font-size:16px;font-weight:600;hyphens:auto;line-height:20px;margin:0;padding:10px 15px;text-align:left;vertical-align:top;word-wrap:break-word">
                                                                <img class="wpmudev-table__icon"
                                                                     src="' . wp_defender()->getPluginUrl() . 'assets/email-assets/img/Warning@2x.png"
                                                                     alt="Hero Image"
                                                                     style="-ms-interpolation-mode:bicubic;clear:both;display:inline-block;margin-right:10px;max-width:100%;outline:0;text-decoration:none;vertical-align:middle;width:18px">
                                                                ' . $issue->getTitle() . '
                                                                <span style="color: #888888;font-family: \'Open Sans\';padding-left: 32px;font-size: 13px;font-weight:300;letter-spacing: -0.25px;line-height: 22px;display: block">
                                                                    ' . $issue->getErrorReason() . '
                                                                </span>
                                                            </td>
                                                            <td class="wpmudev-table__row--warning text-right"
                                                                style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;border-radius:0 0 4px 0;border-top:.5px solid #d8d8d8;color:#FACD25;font-family:\'Open Sans\',Helvetica,Arial,sans-serif;font-size:12px;font-weight:400;hyphens:auto;line-height:20px;margin:0;padding:10px 15px;text-align:right;vertical-align:top;word-wrap:break-word">
                                                            </td>
                                                        </tr>';
			$issues .= $issue;
		}
		$contents = $this->renderPartial( 'email/notification', array(
			'userName' => $firstName,
			'siteUrl'  => network_site_url(),
			'viewUrl'  => apply_filters( 'report_email_logs_link', network_admin_url( 'admin.php?page=wdf-hardener' ), $email ),
			'issues'   => $issues,
			'count'    => count( Hardener\Model\Settings::instance()->getIssues() )
		), false );

		return $contents;
	}

	/**
	 * Add submit admin page
	 */
	public function adminMenu() {
		$cap = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_submenu_page( 'wp-defender', esc_html__( "Security Tweaks", wp_defender()->domain ), esc_html__( "Security Tweaks", wp_defender()->domain ), $cap, $this->slug, array(
			&$this,
			'actionIndex'
		) );
	}

	/**
	 * Main screen
	 */
	public function actionIndex() {
		//update the last seen
		$settings            = Hardener\Model\Settings::instance();
		$settings->last_seen = time();
		$settings->save();

		return $this->render( 'main' );
	}

	/**
	 * Enqueue scripts & styles
	 */
	public function scripts() {
		if ( $this->isInPage() ) {
			wp_enqueue_style( 'defender' );
			wp_register_script( 'defender-hardener', wp_defender()->getPluginUrl() . 'assets/app/security-tweaks.js', array(
				'vue',
				'defender',
				'wp-i18n'
			), false, true );
			wp_localize_script( 'defender-hardener', 'security_tweaks', $this->_scriptsData() );
			Utils::instance()->createTranslationJson( 'defender-hardener' );
			wp_set_script_translations( 'defender-hardener', 'wpdef', wp_defender()->getPluginPath() . 'languages' );
			wp_enqueue_script( 'defender-hardener' );
			wp_enqueue_script( 'wpmudev-sui' );
		}
	}

	/**
	 * @return array
	 */
	public function _scriptsData() {
		if ( ! $this->checkPermission() ) {
			return [];
		}
		global $wp_version;
		$settings = Hardener\Model\Settings::instance();

		return [
			'summary'   => [
				'issues_count' => $this->getCount( 'issues' ),
				'fixed_count'  => $this->getCount( 'fixed' ),
				'ignore_count' => $this->getCount( 'ignore' ),
				'php_version'  => phpversion(),
				'wp_version'   => $wp_version
			],
			'issues'    => $settings->getTweaksAsArray( 'issues', true ),
			'fixed'     => $settings->getTweaksAsArray( 'fixed', true ),
			'ignored'   => $settings->getTweaksAsArray( 'ignore', true ),
			'endpoints' => $this->getAllAvailableEndpoints( Hardener::getClassName() ),
			'nonces'    => [
				'processTweak'   => wp_create_nonce( 'processTweak' ),
				'ignoreTweak'    => wp_create_nonce( 'ignoreTweak' ),
				'restoreTweak'   => wp_create_nonce( 'restoreTweak' ),
				'revertTweak'    => wp_create_nonce( 'revertTweak' ),
				'updateSettings' => wp_create_nonce( 'updateSettings' )
			],
			'model'     => [
				'notification_repeat' => $settings->notification_repeat,
				'recipients'          => $settings->receipts,
				'notification'        => $settings->notification
			]
		];
	}

	/**
	 *
	 * @param $type
	 *
	 * @return int
	 */
	public function getCount( $type ) {
		$settings = Hardener\Model\Settings::instance();
		switch ( $type ) {
			case 'issues':
				return count( $settings->issues );
				break;
			case 'fixed':
				return count( $settings->fixed );
				break;
			case 'ignore':
				return count( $settings->ignore );
				break;
			default:
				//param not from the button on frontend, log it
				error_log( sprintf( 'Unexpected value %s from IP %s', $type, Utils::instance()->getUserIp() ) );
				break;
		}
	}
}