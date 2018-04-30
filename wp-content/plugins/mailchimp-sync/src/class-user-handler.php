<?php

namespace MC4WP\Sync;

use Exception;
use MC4WP_API;
use MC4WP_MailChimp;
use MC4WP_MailChimp_Subscriber_Data;
use WP_User;

class UserHandler {

	/**
	 * @var string The List ID to sync with
	 */
	private $list_id;

	/**
	 * @var string
	 */
	public $error = '';

	/**
	 * @var array
	 */
	private $settings = array(
		'double_optin' => 0,
		'send_welcome' => 0,        // deprecated in mc4wp v4.x
		'update_existing' => 1,
		'replace_interests' => 0,
		'email_type' => 'html',
		'send_goodbye' => 0,
		'send_notification' => 0,
		'delete_member' => 0,
		'field_mappers' => array(),
		'enable_user_control' => 0,
	);

	/**
	 * Constructor
	 *
	 * @param string $list_id
	 * @param Users $users
	 * @param array  $settings
	 */
	public function __construct( $list_id, Users $users, array $settings = array() ) {

		$this->list_id = $list_id;
		$this->users = $users;

		// if settings were passed, merge those with the defaults
		if( $settings ) {
			$this->settings = array_merge( $this->settings, $settings );
		}
	}

	/**
	 * Add hooks to call the subscribe, update & unsubscribe methods automatically
	 */
	public function add_hooks() {
		// custom actions for people to use if they want to call the class actions
		// @todo If we ever allow multiple instances of this class, these actions need the list_id property
		add_action( 'mailchimp_sync_handle_user', array( $this, 'subscribe_user' ) );
		add_action( 'mailchimp_sync_subscribe_user', array( $this, 'subscribe_user' ) );
		add_action( 'mailchimp_sync_update_subscriber', array( $this, 'subscribe_user' ) );
		add_action( 'mailchimp_sync_unsubscribe_user', array( $this, 'unsubscribe_user' ), 10, 2 );
	}

	/**
	 * Subscribers or unsubscribes the given user, based on the User Sync settings. 
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	public function handle_user( $user_id ) {

		try {
			$user = $this->users->user( $user_id );
		} catch( Exception $e ) {
			return false;
		}

		// subscribe everyone that matches user criteria (role, filter)
		$subscribe = $this->users->should( $user );

		// if user control is enabled, check if user opted out.
		if( $subscribe && $this->settings['enable_user_control'] ) {
			$default = $this->settings['default_optin_status'] === 'subscribed';
			$subscribe = $this->users->get_optin_status( $user, $default );
		}

		return $subscribe ? $this->subscribe_user( $user->ID ) : $this->unsubscribe_user( $user->ID, $user->user_email );
	}

	/**
	 * Subscribes a user to the selected MailChimp list, stores a meta field with the subscriber uid
	 *
	 * @param int $user_id
	 * @return bool
	 */
	public function subscribe_user( $user_id ) {
		$this->error = '';

		try {
			$user = $this->users->user( $user_id );
		} catch( Exception $e ) {
			$this->error = $e->getMessage();
			return false;
		}

		// if role is set, make sure user has that role
		if( ! $this->users->should( $user ) ) {
			$this->error = sprintf( 'Skipping user %d', $user->ID );
			return false;
		}

		// Only subscribe user if it has a valid email address
		if( '' === $user->user_email || ! is_email( $user->user_email ) ) {
			$this->error = 'Invalid email';
			$this->get_log()->warning( sprintf( 'User Sync > %s is an invalid email address', $user->user_email ) );
			return false;
		}

		$user_subscriber = $this->get_user_subscriber();

		$args = array(
			'double_optin' => $this->settings['double_optin'],
			'email_type' => $this->settings['email_type'],
			'replace_interests' => $this->settings['replace_interests'],
			'send_welcome' => $this->settings['send_welcome'],
			'resubscribe' => $this->settings['enable_user_control']
		);

		try {
			$existed = $user_subscriber->subscribe( $user->ID, $args );
		} catch( Exception $e ) {
			$this->error = (string) $e;
			$this->get_log()->error( sprintf( 'User Sync > Error subscribing or updating user %d: %s', $user_id, $this->error ) );
			return false;
		}
		
		// Success!
		$this->get_log()->info( sprintf( 'User Sync > Successfully %s user %d (%s)', $existed ? 'updated' : 'subscribed', $user->ID, $user->user_email ) );

		return true;
	}

	/**
	 * Delete the subscriber uid from the MailChimp list
	 *
	 * @param int $user_id
	 * @param string $email_address
     * @param string $subscriber_uid (optional)
     *
	 * @return bool
	 */
	public function unsubscribe_user( $user_id, $email_address, $subscriber_uid = null ) {
		$this->error = '';
		$user_subscriber = $this->get_user_subscriber();

		try{
			$existed = $user_subscriber->unsubscribe( $user_id, $email_address, $subscriber_uid, $this->settings['send_goodbye'], $this->settings['send_notification'], $this->settings['delete_member'] );
		} catch( Exception $e ) {
			$this->error = (string) $e;
			$this->get_log()->error( sprintf( 'User Sync > Error unsubscribing user %d: %s', $user_id, $this->error ) );
			return false;
		}

		$this->get_log()->info( sprintf( 'User Sync > Successfully unsubscribed user %d (%s)', $user_id, $email_address ) );
		return true;
	}

	/**
	 * @return UserSubscriber
	 */
	private function get_user_subscriber() {
		if( ! class_exists( 'MC4WP_API_v3' ) ) {
			return new UserSubscriberAPIv2( $this->users, $this->list_id );
		}

		return new UserSubscriberAPIv3( $this->users, $this->list_id );
	}

	/**
	 * Returns an instance of the Debug Log
	 *
	 * @return \MC4WP_Debug_Log
	 */
	private function get_log() {
		return mc4wp( 'log' );
	}

	
}


