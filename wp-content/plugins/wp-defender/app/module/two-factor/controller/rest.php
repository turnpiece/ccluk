<?php

namespace WP_Defender\Module\Two_Factor\Controller;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\Two_Factor;

class Rest extends Controller {
	public function __construct() {
		$namespace = 'wp-defender/v1';
		$namespace .= '/twoFa';
		$routes    = [
			$namespace . '/updateSettings' => 'updateSettings',
			$namespace . '/sendTestEmail'  => 'sendTestEmail',

		];
		$this->registerEndpoints( $routes, Two_Factor::getClassName() );
	}

	/**
	 * Send test email
	 */
	public function sendTestEmail() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'sendTestEmail' ) ) {
			return;
		}

		//get the backup email from current user
		$backup_email = Two_Factor\Component\Auth_API::getBackupEmail( get_current_user_id() );
		$subject      = wp_kses_post( HTTP_Helper::retrievePost( 'email_subject' ) );
		$sender       = HTTP_Helper::retrievePost( 'email_sender' );
		$body         = wp_kses_post( HTTP_Helper::retrievePost( 'email_body' ) );
		$params       = [
			'pass_code'    => '[a-sample-passcode]',
			'display_name' => Utils::instance()->getDisplayName()
		];
		foreach ( $params as $key => $param ) {
			$body = str_replace( "{{$key}}", $param, $body );
		}
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		if ( $sender ) {
			$from_email = get_bloginfo( 'admin_email' );
			$headers[]  = sprintf( 'From: %s <%s>', $sender, $from_email );
		}

		$send_mail = wp_mail( $backup_email, $subject, $body, $headers );
		if ( $send_mail ) {
			wp_send_json_success( array(
				'message' => __( 'Test email has been sent to your email.', wp_defender()->domain ),
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Test email failed.', wp_defender()->domain ),
			) );
		}

	}

	/**
	 * An endpoint for update settings
	 */
	public function updateSettings() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'updateSettings' ) ) {
			return;
		}

		$data     = stripslashes( $_POST['data'] );
		$data     = json_decode( $data, true );
		$settings = $data['settings'];
		$model    = Two_Factor\Model\Auth_Settings::instance();
		$model->import( $settings );
		if ( $model->validate() ) {
			$model->save();
			$res = array(
				'message' => __( "Your settings have been updated.", wp_defender()->domain )
			);
			$this->submitStatsToDev();
			wp_send_json_success( $res );
		} else {
			$res = array(
				'message' => implode( '<br/>', $model->getErrors() )
			);
			wp_send_json_error( $res );
		}
	}

	/**
	 * Import Utils into the class
	 * @return array
	 */
	public function behaviors() {
		$behaviors = array(
			'utils' => '\WP_Defender\Behavior\Utils',
		);

		return $behaviors;
	}
}