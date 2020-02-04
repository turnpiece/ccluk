<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Change_Admin extends Rule {
	static $slug = 'replace-admin-username';
	static $service;

	public function getDescription() {
		$this->renderPartial( 'rules/change-admin' );
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "You have a user account with the admin username.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "You don't have a user account sporting the admin username, great.", wp_defender()->domain );
	}

	public function check() {
		return $this->getService()->check();
	}

	public function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process' );
	}

	public function revert() {

	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return __( "Change default admin user account", wp_defender()->domain );
	}

	/**
	 *
	 */
	public function process() {
		$username = HTTP_Helper::retrievePost( 'username' );
		$this->getService()->setUsername( $username );
		$ret = $this->getService()->process();
		if ( is_wp_error( $ret ) ) {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		} else {
			wp_send_json_success( array(
				'message' => sprintf( __( "Your admin name has changed. You will need to <a href='%s'><strong>%s</strong></a>.<br/>This will auto reload after <span class='hardener-timer'>5</span> seconds.", wp_defender()->domain ), wp_login_url( network_admin_url( 'admin.php?page=wdf-hardener' ) ), "re-login" ),
				'reload'  => 5,
				'url'     => wp_login_url( network_admin_url( 'admin.php?page=wdf-hardener' ) )
			) );
		}
	}

	/**
	 * @return Change_Admin_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Change_Admin_Service();
		}

		return self::$service;
	}
}