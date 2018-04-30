<?php

namespace MC4WP\Sync;

use MC4WP\Sync\CLI\CommandProvider;
use MC4WP\Sync\Webhook;

final class Plugin {

	/**
	 * @const VERSION
	 */
	const VERSION = MAILCHIMP_SYNC_VERSION;

	/**
	 * @const FILE
	 */
	const FILE = MAILCHIMP_SYNC_FILE;

	/**
	 * @const DIR
	 */
	const DIR = MAILCHIMP_SYNC_DIR;

	/**
	 * @const OPTION_NAME Option name
	 */
	const OPTION_NAME = 'mailchimp_sync';

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->options = $this->load_options();
	}

	/**
	 * @return array
	 */
	private function load_options() {

		$options = (array) get_option( self::OPTION_NAME, array() );

		$defaults = array(
			'list' => '',
			'double_optin' => 1,
			'send_welcome' => 0,
			'role' => '',
			'enabled' => 1,
			'field_mappers' => array(),
			'webhook' => array(
				'enabled' => 1,
				'secret_key' => ''
			),
			'enable_user_control' => 1,
			'default_optin_status' => 'unsubscribed',
			'user_profile_heading_text' => __( 'Newsletter', 'mailchimp-sync' ),
			'user_profile_label_text' => __( 'Send me occasional email updates.', 'mailchimp-sync'),
		);

		// if some options were present in the database, user has been running plugin for some time
		// for those users, change default (fallback) values to old less-privacy friendly values
		if( ! empty( $options ) && ! empty( $options['list'] ) ) {
			$defaults['double_optin'] = 0;
			$defaults['enable_user_control'] = 0;
			$defaults['default_optin_status'] = 'subscribed';
		}

		$options = array_merge( $defaults, $options );
		$options['webhook'] = array_merge( $defaults['webhook'], $options['webhook'] );
		$options['enabled'] = (int) $options['enabled'];
		$options['send_welcome'] = (int) $options['send_welcome'];
		$options['double_optin'] = (int) $options['double_optin'];

		/**
		 * Filters MailChimp Sync options
		 *
		 * @param array $options
		 */
		return (array) apply_filters( 'mailchimp_sync_options', $options );
	}

}
