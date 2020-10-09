<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\IP_Lockout\Component;

use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\IP_Lockout\Model\IP_Model;
use WP_Defender\Module\IP_Lockout\Model\Log_Model;
use WP_Defender\Module\IP_Lockout\Model\Settings;

class Notfound_Listener extends Controller {
	public function __construct() {
		if ( Settings::instance()->detect_404 ) {
			$this->addAction( 'template_redirect', 'record404' );
		}
	}

	public function record404() {
		if ( ! is_404() ) {
			return;
		}

		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			//only track subscriber
			return;
		}

		//now check if this from google
		if ( Login_Protection_Api::isGoogleUA() && Login_Protection_Api::isGoogleIP( Utils::instance()->getUserIp() ) ) {
			return;
		}

		//or bing
		if ( Login_Protection_Api::isBingUA() && Login_Protection_Api::isBingIP( Utils::instance()->getUserIp() ) ) {
			return;
		}

		$settings = Settings::instance();
		if ( $settings->detect_404_logged == false && is_user_logged_in() ) {
			return;
		}

		$uri = $_SERVER['REQUEST_URI'];

		/**
		 * Priorities
		 * - Whitelist
		 *      extensions
		 *      files & folders
		 * - Blacklist
		 *      extensions
		 *      files & folders
		 * - attemps inside a window
		 */

		$ext = pathinfo( $uri, PATHINFO_EXTENSION );
		if ( in_array( '.' . $ext, $settings->getDetect404IgnoredFiletypes() ) ) {
			//ext is whitelist, log and return
			$this->log( $uri, Log_Model::ERROR_404_IGNORE, sprintf( __( "Request for file %s which doesn't exist", wp_defender()->domain ), $uri ) );

			return;
		}
		foreach ( $settings->get404Whitelist() as $pattern ) {
			$pattern = preg_quote( $pattern, '/' );
			if ( preg_match( '/' . $pattern . '$/', $uri ) ) {
				//whitelisted, just log and return
				return;
			}
		}
		$model = IP_Model::init();
		if ( in_array( '.' . $ext, $settings->getDetect404FiletypesBlacklist() ) ) {
			//block it
			$this->lock( $model, 'blacklist', $uri );
			$this->log(
				$uri,
				Log_Model::LOCKOUT_404,
				sprintf( __( 'Lockout occurred:  Too many 404 requests for %s', wp_defender()->domain ), $uri )
			);

			return;
		}

		foreach ( $settings->getDetect404Blacklist() as $pattern ) {
			$pattern = preg_quote( $pattern, '/' );
			if ( preg_match( '/' . $pattern . '$/', $uri ) ) {
				$this->lock( $model, 'blacklist', $uri );
				$this->log(
					$uri,
					Log_Model::LOCKOUT_404,
					sprintf( __( 'Lockout occurred:  Too many 404 requests for %s', wp_defender()->domain ), $uri )
				);

				return;
			}
		}

		$this->log(
			$uri,
			Log_Model::ERROR_404,
			sprintf( __( "Request for file %s which doesn't exist", wp_defender()->domain ), $uri )
		);
		//now we need to count the attempt
		$window = strtotime( '- ' . $settings->detect_404_timeframe . ' seconds', time() );
		if ( $window < $model->lock_time ) {
			$window = $model->lock_time;
		}
		$attempts = Log_Model::count( [
			'ip'   => Utils::instance()->getUserIp(),
			'type' => Log_Model::ERROR_404,
			'date' => [ 'compare' => '>', 'value' => $window ]
		] );

		if ( $attempts >= $settings->detect_404_threshold ) {
			//lock it
			$this->lock( $model, 'normal', $uri );
			$this->log(
				$uri,
				Log_Model::LOCKOUT_404,
				sprintf( __( 'Lockout occurred:  Too many 404 requests for %s' ), $uri )
			);
		}
	}

	/**
	 * @param IP_Model $model
	 * @param $scenario
	 * @param $uri
	 */
	private function lock( IP_Model $model, $scenario = 'normal', $uri = '' ) {
		$settings = Settings::instance();
		if ( $settings->detect_404_lockout_ban == true ) {
			$scenario = 'blacklist';
		}
		$model->status    = IP_Model::STATUS_BLOCKED;
		$model->lock_time = time();
		if ( $scenario == 'blacklist' ) {
			$model->release_time = strtotime( '+5 years', time() );
		} else {
			$model->release_time = strtotime( '+ ' . $settings->detect_404_lockout_duration . ' ' . $settings->detect_404_lockout_duration_unit );
		}
		$model->lockout_message = $settings->detect_404_lockout_message;
		$model->save();
		if ( $scenario == 'blacklist' ) {
			$settings->addIpToList( $model->ip, 'blocklist' );
		}
		$model->lock_time = time();

		do_action( 'wd_404_lockout', $model, $scenario );
		//Only ip_lockout_notification is enabled
		if ( isset( $settings->ip_lockout_notification ) && $settings->ip_lockout_notification ) {
			$this->email($model, $uri);
		}
	}

	/**
	 * @param $uri
	 * @param $scenario
	 */
	private function log( $uri, $scenario, $text ) {
		$log             = new Log_Model();
		$log->ip         = Utils::instance()->getUserIp();
		$log->user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null;
		$log->log        = $text;
		$log->date       = time();
		$log->type       = $scenario;
		$log->tried      = $uri;
		$log->save();
	}

	/**
	 * @param IP_Model $model
	 * @param $uri
	 */
	private function email( IP_Model $model, $uri ) {
		$settings = Settings::instance();
		Utils::instance()->log( 'Check to send 404 notification' );
		if ( ! Login_Protection_Api::maybeSendNotification( '404', $model, $settings ) ) {
			return;
		}
		Utils::instance()->log( 'Allow to send 404 notification' );
		$isBlacklisted = $settings->isBlacklist( $model->ip );
		Utils::instance()->log( sprintf( 'Recipients %d', count( $settings->receipts ) ) );
		Utils::instance()->log( $uri );
		foreach ( $settings->receipts as $item ) {
			$content        = $this->renderPartial( $isBlacklisted == true ? 'emails/404-ban' : 'emails/404-lockout', array(
				'admin' => $item['first_name'],
				'ip'    => $model->ip,
				'uri'   => $uri
			), false );
			$no_reply_email = "noreply@" . parse_url( get_site_url(), PHP_URL_HOST );
			$no_reply_email = apply_filters( 'wd_lockout_noreply_email', $no_reply_email );
			$headers        = array(
				'From: Defender <' . $no_reply_email . '>',
				'Content-Type: text/html; charset=UTF-8'
			);
			$ret            = wp_mail( $item['email'], sprintf( __( "404 lockout alert for %s", wp_defender()->domain ), network_site_url() ), $content, $headers );
			Utils::instance()->log( sprintf( 'Mail send result :%s', var_export( $ret, true ) ) );
		}
	}
}