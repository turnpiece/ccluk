<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Controller;

use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\Hardener;
use WP_Defender\Module\Hardener\Model\Settings;

class Rest extends Controller {
	public function __construct() {
		$namespace = 'wp-defender/v1';
		$namespace .= '/tweaks';
		$routes    = [
			$namespace . '/processTweak'   => 'processTweak',
			$namespace . '/revertTweak'    => 'revertTweak',
			$namespace . '/ignoreTweak'    => 'ignoreTweak',
			$namespace . '/restoreTweak'   => 'restoreTweak',
			$namespace . '/updateSettings' => 'updateSettings',
			$namespace . '/reCheck'        => 'reCheck',
		];
		$this->registerEndpoints( $routes, Hardener::getClassName() );
	}

	/**
	 * Save settings
	 */
	public function updateSettings() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'updateSettings' ) ) {
			return;
		}

		$settings                      = Hardener\Model\Settings::instance();
		$data                          = stripslashes( $_POST['data'] );
		$data                          = json_decode( $data, true );
		$settings->notification        = $data['notification'];
		$settings->notification_repeat = $data['notification_repeat'];
		$recipients                    = [];
		foreach ( $data['recipients'] as $key => $recipient ) {
			if ( filter_var( $recipient['email'], FILTER_VALIDATE_EMAIL ) ) {
				$recipients[] = $recipient;
			}
		}
		$settings->receipts = $recipients;
		$settings->save();
		$this->submitStatsToDev();
		wp_send_json_success( array(
			'message' => __( "Your settings have been updated.", wp_defender()->domain )
		) );
	}

	public function reCheck() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'recheck' ) ) {
			return;
		}
		$type = HTTP_Helper::retrievePost( 'type' );
		if ( $type == 'prevent-php' ) {
			$url = WP_Helper::getUploadUrl();
			$url = $url . '/wp-defender/index.php';
		} else {
			$url = wp_defender()->getPluginUrl() . 'languages/wpdef-default.pot';
		}
		$model = Settings::instance();
		$cache = $model->getDValues( 'head_requests' );
		Utils::instance()->log( sprintf( 'clean up %s', $url ), 'tweaks' );
		unset( $cache[ $url ] );
		$model->setDValues( 'head_requests', $cache );
		wp_send_json_success( $this->tweaksSummary() );
	}

	/**
	 * Endpoint for recieve process request for all tweaks, we will dispatch the envelope to tweak from here
	 */
	public function processTweak() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'processTweak' ) ) {
			return;
		}

		$slug = HTTP_Helper::retrievePost( 'slug' );
		do_action( "processingHardener" . $slug );
		//fall back
		wp_send_json_success( array_merge( [
			'message' => __( "Security tweak successfully resolved.", wp_defender()->domain ),
		], $this->tweaksSummary() ) );
	}

	/**
	 * Revert a tweak request, this will un-apply the affect of a tweak
	 */
	public function revertTweak() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'revertTweak' ) ) {
			return;
		}

		$slug = HTTP_Helper::retrievePost( 'slug' );
		do_action( "processRevert" . $slug );
		//fall back
		wp_send_json_success( array_merge( [
			'message' => __( "Security tweak successfully reverted.", wp_defender()->domain ),
		], $this->tweaksSummary() ) );
	}

	/**
	 * Ignore a tweak request
	 */
	public function ignoreTweak() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'ignoreTweak' ) ) {
			return;
		}
		$slug = HTTP_Helper::retrievePost( 'slug' );
		$rule = Hardener\Model\Settings::instance()->getRuleBySlug( $slug );
		if ( is_object( $rule ) ) {
			$rule->ignore();
			wp_send_json_success( array_merge( [
				'message' => __( "Security tweak successfully ignored.", wp_defender()->domain ),
			], $this->tweaksSummary() ) );
		}
	}

	/**
	 * Restore request, to move a tweak from ignored to issues section
	 */
	public function restoreTweak() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'restoreTweak' ) ) {
			return;
		}

		$slug = HTTP_Helper::retrievePost( 'slug' );
		$rule = Hardener\Model\Settings::instance()->getRuleBySlug( $slug );
		if ( is_object( $rule ) ) {
			$rule->restore();
			wp_send_json_success( array_merge( [
				'message' => __( "Security tweak successfully restored.", wp_defender()->domain ),
			], $this->tweaksSummary() ) );
		}
	}

	/**
	 * Shorthand for returning the count of tweaks, by each section
	 * @return array
	 */
	private function tweaksSummary() {
		$settings = Hardener\Model\Settings::instance( true );

		return [
			'summary' => [
				'issues' => count( $settings->issues ),
				'fixed'  => count( $settings->fixed ),
				'ignore' => count( $settings->ignore ),
			],
			'issues'  => $settings->getTweaksAsArray( 'issues', true ),
			'fixed'   => $settings->getTweaksAsArray( 'fixed', true ),
			'ignore'  => $settings->getTweaksAsArray( 'ignore', true ),
		];
	}

	/**
	 * Declaring behaviors
	 * @return array
	 */
	public function behaviors() {
		$behaviors = array(
			'utils' => '\WP_Defender\Behavior\Utils',
		);

		return $behaviors;
	}
}