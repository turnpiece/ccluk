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

class Login_Listener extends Controller {
	public function __construct() {
		$settings = Settings::instance();
		if ( $settings->login_protection ) {
			$this->addAction( 'wp_login_failed', 'recordFailLogin', 9999 );
			$this->addFilter( 'authenticate', 'showAttemptLeft', 9999, 3 );
			$this->addAction( 'wp_login', 'clearAttemptStats', 10, 2 );
		}
	}

	/**
	 * Record fail login as log into db
	 *
	 * @param $username
	 */
	public function recordFailLogin( $username ) {
		if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
			//do nothing as wp-login.php wil trigger the wp_signon for cookie login, can cause trouble
			return;
		}
		$settings = Settings::instance();
		//first check if the username is fail to ban
		$username = strtolower( $username );
		$model    = IP_Model::init();

		if ( in_array( $username, $settings->getUsernameBlacklist() ) ) {
			return $this->lock( $model, $username, 'blacklist_uname' );
		}
		//log for the event
		$this->log( $username, Log_Model::AUTH_FAIL, sprintf( esc_html__( "Failed login attempt with username %s", wp_defender()->domain ), $username ) );

		//calculate if this one out of threshold
		$window = strtotime( '- ' . $settings->login_protection_lockout_timeframe . ' seconds' );
		if ( $window < $model->lock_time ) {
			/**
			 * Case if it just banned and the lockout duration too short, we use the locktime instead
			 */
			$window = $model->lock_time;
		}

		$attempt        = Log_Model::count( [
			'ip'      => Utils::instance()->getUserIp(),
			'date'    => [ 'compare' => '>', 'value' => $window ],
			'type'    => Log_Model::AUTH_FAIL,
			'blog_id' => get_current_blog_id()
		] );
		$model->attempt = $attempt;
		if ( $attempt >= $settings->login_protection_login_attempt ) {
			$scenario = $settings->login_protection_lockout_ban ? 'ban' : 'normal';

			return $this->lock( $model, $username, $scenario );
		}
		$model->save();
	}

	/**
	 * Reset the attempt counter
	 */
	public function clearAttemptStats() {
		$model          = IP_Model::init();
		$model->attempt = 1;
		$model->save();
	}

	/**
	 * @param $user
	 * @param $username
	 * @param $password
	 *
	 * @return mixed
	 */
	public function showAttemptLeft( $user, $username, $password ) {
		if ( is_wp_error( $user ) && $_SERVER['REQUEST_METHOD'] == 'POST' && ! in_array( $user->get_error_code(), array(
				'empty_username',
				'empty_password'
			) )
		) {
			$model    = IP_Model::init();
			$settings = Settings::instance();
			if ( in_array( $username, $settings->getUsernameBlacklist() ) ) {
				$user->add( 'def_warning', esc_html__( "You have been locked out by the administrator for attempting to login with a banned username", wp_defender()->domain ) );
			} else {
				$attempt = $model->attempt + 1;
				//because the action authenticate trigger before wp_login_failed, so we need to add 1 for the attemt
				if ( $attempt >= $settings->login_protection_login_attempt ) {
					//show lockout message
					$user->add( 'def_warning', $settings->login_protection_lockout_message );
				} else {
					$user->add( 'def_warning', sprintf( esc_html__( "%d login attempts remaining", wp_defender()->domain ), $settings->login_protection_login_attempt - $attempt ) );
				}
			}
		}

		return $user;
	}

	/**
	 * @param IP_Model $model
	 * @param $username
	 * @param $scenario
	 */
	private function lock( IP_Model $model, $username, $scenario ) {
		$settings = Settings::instance();
		if ( $scenario === 'blacklist_uname' ) {
			$model->lockout_message = esc_html__( "You have been locked out by the administrator for attempting to login with a banned username", wp_defender()->domain );
			$model->status          = IP_Model::STATUS_BLOCKED;
			$model->lock_time       = time();
			$model->save();

			//add to blacklist
			Settings::instance()->addIpToList( $model->ip, 'blocklist' );
			$this->log( $username, Log_Model::AUTH_LOCK, sprintf( esc_html__( "Failed login attempt with a ban username %s", wp_defender()->domain ), $username ) );
		} else {
			$model->status          = IP_Model::STATUS_BLOCKED;
			$model->release_time    = strtotime( '+ ' . $settings->login_protection_lockout_duration . ' ' . $settings->login_protection_lockout_duration_unit );
			$model->lockout_message = $settings->login_protection_lockout_message;
			$model->lock_time       = time();
			$model->save();
			if ( $scenario === 'ban' ) {
				$settings->addIpToList( $model->ip, 'blocklist' );
			}
			$this->log( $username, Log_Model::AUTH_LOCK, __( "Lockout occurred: Too many failed login attempts", wp_defender()->domain ) );
		}
		do_action( 'wd_login_lockout', $model, $scenario );
		if ( $settings->login_lockout_notification ) {
			$this->email( $model );
		}
	}

	/**
	 * Log the event into db, we will use the data in logs page later
	 *
	 * @param $username
	 * @param $type
	 * @param $log
	 */
	private function log( $username, $type, $log ) {
		$model             = new Log_Model();
		$model->ip         = Utils::instance()->getUserIp();
		$model->user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null;
		$model->log        = $log;
		$model->date       = time();
		$model->type       = $type;
		$model->tried      = $username;
		$model->save();
	}

	private function email( IP_Model $model ) {
		$settings = Settings::instance();

		if ( ! Login_Protection_Api::maybeSendNotification( 'login', $model, $settings ) ) {
			return;
		}

		$view = ( $settings->isBlacklist( $model->ip ) ) ? 'emails/login-username-ban' : 'emails/login-lockout';
		foreach ( $settings->receipts as $item ) {
			$content        = $this->renderPartial( $view, array(
				'admin'      => $item['first_name'],
				'ip'         => $model->ip,
				'logs_url'   => apply_filters( 'report_email_logs_link', apply_filters( 'wp_defeder/iplockout/email_report_link', network_admin_url( "admin.php?page=wdf-ip-lockout&view=logs" ) ), $item['email'] ),
				'report_url' => apply_filters( 'report_email_logs_link', network_admin_url( 'admin.php?page=wdf-ip-lockout&view=reporting' ), $item['email'] ),
			), false );
			$no_reply_email = "noreply@" . parse_url( get_site_url(), PHP_URL_HOST );
			$no_reply_email = apply_filters( 'wd_lockout_noreply_email', $no_reply_email );
			$headers        = array(
				'From: Defender <' . $no_reply_email . '>',
				'Content-Type: text/html; charset=UTF-8'
			);
			wp_mail( $item['email'], sprintf( __( "Login lockout alert for %s", wp_defender()->domain ), network_site_url() ), $content, $headers );
		}
	}
}