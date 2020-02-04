<?php

/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Model;

use Hammer\Helper\WP_Helper;

class Auth_Settings extends \Hammer\WP\Settings {
	private static $_instance;
	public $enabled = false;
	public $lost_phone = true;
	public $force_auth = false;
	public $force_auth_mess = "You are required to setup two-factor authentication to use this site.";
	public $user_roles = array();
	public $force_auth_roles = array();
	public $custom_graphic = false;
	public $custom_graphic_url = '';
	public $is_conflict = array();
	public $email_subject = '';
	public $email_sender = '';
	public $email_body = '';

	public function __construct( $id, $is_multi ) {
		//fetch the userRoles
		if ( ! function_exists( 'get_editable_roles' ) ) {
			include_once ABSPATH . 'wp-admin/includes/user.php';
		}
		$this->user_roles = array_keys( get_editable_roles() );
		//remove subscriber from the list
		unset( $this->user_roles[ array_search( 'subscriber', $this->user_roles ) ] );
		$this->custom_graphic_url = wp_defender()->getPluginUrl() . 'assets/img/2factor-disabled.svg';
		$this->email_subject      = 'Your OTP code';
		$this->email_sender       = 'admin';
		$this->email_body         = 'Hi {{display_name}},

Your temporary login passcode is <strong>{{passcode}}</strong>.

Copy and paste the passcode into the input field on the login screen to complete logging in.

Regards,
Administrator';
		parent::__construct( $id, $is_multi );
		//have to force it here if it has not convert the new config
		$this->enabled        = ! ! $this->enabled;
		$this->force_auth     = ! ! $this->force_auth;
		$this->custom_graphic = ! ! $this->custom_graphic;
		if ( ! is_array( $this->user_roles ) ) {
			$this->user_roles = [];
		}
		$this->user_roles = array_values( $this->user_roles );
		if ( ! is_array( $this->force_auth_roles ) ) {
			$this->force_auth_roles = [];
		}
		$this->force_auth_roles = array_values( $this->force_auth_roles );
	}

	/**
	 * @return Auth_Settings
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			$class           = new Auth_Settings( 'wd_2auth_settings', WP_Helper::is_network_activate( wp_defender()->plugin_slug ) );
			self::$_instance = $class;
		}

		return self::$_instance;
	}

	/**
	 * @param $plugin
	 *
	 * @return bool|int
	 */
	public function isConflict( $plugin ) {
		if ( in_array( $plugin, $this->is_conflict ) ) {
			return true;
		} elseif ( in_array( '!' . $plugin, $this->is_conflict ) ) {
			return false;
		}

		return 0;
	}

	/**
	 * @param $plugin
	 */
	public function markAsConflict( $plugin ) {
		if ( ! in_array( $plugin, $this->is_conflict ) ) {
			$this->is_conflict [] = $plugin;
			$this->save();
		}
	}

	/**
	 * @param $plugin
	 */
	public function markAsUnConflict( $plugin ) {
		if ( ( $i = array_search( $plugin, $this->is_conflict ) ) !== false ) {
			unset( $this->is_conflict[ $i ] );
		}
		if ( ! in_array( '!' . $plugin, $this->is_conflict ) ) {
			$this->is_conflict [] = '!' . $plugin;
		}
		$this->save();
	}

	public function events() {
		$that = $this;

		return array(
			self::EVENT_AFTER_DELETED => array(
				array(
					function () use ( $that ) {
						global $wpdb;
						$sql = "DELETE from " . $wpdb->usermeta . " WHERE meta_key IN ('defOTPLoginToken','defenderBackupCode','defenderAuthSecret','defenderAuthOn','defenderAuthEmail')";
						$wpdb->query( $sql );
					}
				)
			)
		);
	}

	/**
	 * Email default body.
	 */
	public function two_factor_opt_email_default_body() {
		$content = 'Hi {{display_name}},

Your temporary login passcode is <strong>{{passcode}}</strong>.

Copy and paste the passcode into the input field on the login screen to complete logging in.

Regards,
Administrator';

		return $content;
	}

	/**
	 * Define labels for settings key, we will use it for HUB
	 *
	 * @param null $key
	 *
	 * @return array|mixed
	 */
	public function labels( $key = null ) {
		$labels = [
			'enabled'            => __( 'Two Factor Authentication', wp_defender()->domain ),
			'user_roles'         => __( "User Roles", wp_defender()->domain ),
			'lost_phone'         => __( 'Lost Phone', wp_defender()->domain ),
			'force_auth'         => __( "Force Authentication", wp_defender()->domain ),
			'force_auth_mess'    => __( "Custom warning message", wp_defender()->domain ),
			'force_auth_roles'   => __( "Force Authentication", wp_defender()->domain ),
			'custom_graphic'     => __( "Custom Graphic", wp_defender()->domain ),
			'custom_graphic_url' => __( "Custom Graphic Image", wp_defender()->domain ),
			'email_subject'      => __( "Subject", wp_defender()->domain ),
			'email_sender'       => __( "Sender", wp_defender()->domain ),
			'email_body'         => __( "Body", wp_defender()->domain )

		];

		if ( $key != null ) {
			return isset( $labels[ $key ] ) ? $labels[ $key ] : null;
		}

		return $labels;
	}
}