<?php

namespace MC4WP\Sync\Webhook;

use MC4WP\Sync\UserRepository;
use MC4WP\Sync\Users;
use WP_User;
use MC4WP_Debug_Log;

/**
 * Class Listener
 *
 * This class listens on your-site.com/mc4wp-sync-api/webhook-listener for MailChimp webhook events.
 *
 * Once triggered, it will look for the corresponding WP user and update it using the field map defined in the settings of the Sync plugin.
 *
 * @package MC4WP\Sync\Webhook
 */
class Listener {

	/**
	 * @var Users
	 */
	private $users;

	/**
	 * @var array
	 */
	private $field_mappers;

	/**
	 * @var string
	 */
	public $url = '/mc4wp-sync-api/webhook-listener';

	/**
	 * @var string
	 */
	protected $secret_key;

	/**
	 * @param Users $users
	 * @param array $field_mappers
	 * @param string $secret_key
	 */
	public function __construct( Users $users, $field_mappers = array(), $secret_key = '' ) {
		$this->users = $users;
		$this->field_mappers = $field_mappers;
		$this->secret_key = $secret_key;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'init', array( $this, 'listen' ) );
	}

	/**
	 * Listen for webhook requests
	 */
	public function listen() {
		if( $this->is_triggered() ) {
			$this->handle();
			exit;
		}
	}

	/**
	 * Yes?
	 *
	 * @return bool
	 */
	public function is_triggered() {
		return strpos( $_SERVER['REQUEST_URI'], $this->url ) !== false;
	}

	/**
	 * Handle the request
	 *
	 * @return boolean
	 */
	public function handle() {

		// check for secret key
		if( ! empty( $this->secret_key ) && ! isset( $_GET[ $this->secret_key ] ) ) {
			status_header( 403 );
			return false;
		}

		$log = $this->get_log();
		define( 'MC4WP_SYNC_DOING_WEBHOOK', true );

		// no parameters = MailChimp webhook validator
		if( empty( $_POST['data'] ) || empty( $_POST['type'] ) ) {
			echo "Listening..";
			status_header( 200 );
			return true;
		}

		$data = stripslashes_deep( $_REQUEST['data'] );
		$type = (string) $_REQUEST['type'];

        /**
         * Filter webhook data that is received by MailChimp.
         *
         * @param array $data
         * @param string $type
         */
        $data = apply_filters( 'mailchimp_sync_webhook_data', $data, $type );

		// parameters but incorrect: throw error status
		if( empty( $data['web_id'] ) && empty( $data['id'] ) ) {
			status_header( 400 );
			return false;
		}

		// find WP user by List_ID + MailChimp unique email ID
		$user = $this->users->get_user_by_mailchimp_id( $data['id'] );

		// No user found? Try "web_id", which was used in API v2.
		if( ! $user ) {
			$user = $this->users->get_user_by_mailchimp_id( $data['web_id'] );
		}

		/**
		 * Filters the WordPress user that is found by the webhook request
		 *
		 * @param WP_User|null $user
		 * @param array $data
		 */
		$user = apply_filters( 'mailchimp_sync_webhook_user', $user, $data );

		if( ! $user instanceof WP_User ) {
			// log a warning
			$log->info( sprintf( "Webhook: No user found for MailChimp ID: %s", $data['id'] ) );

			// fire event when no user is found
			do_action( 'mailchimp_sync_webhook_no_user', $data );
			echo 'No corresponding user found for this subscriber.';

			status_header( 200 );

			// exit early
			return false;
		}

		// we have a user at this point
        $log->info( sprintf( "Webhook: Request of type %s received for user #%d", $type, $user->ID ) );

		$updated = false;

		// User might not have sync key (if supplied by filter)
		// Update it, just in case.
		$user_subscriber_uid = $this->users->get_subscriber_uid( $user->ID );
		if( empty( $user_subscriber_uid ) ) {

		    // since API v3, we use ueid instead of web_id.
		    $v3 = class_exists( 'MC4WP_API_v3' );
			$this->users->set_subscriber_uid( $user->ID, $v3 ? $data['id'] : $data['web_id'] );
			$updated = true;
		}

		// update user email if it's given, valid and different
		if( ! empty( $data['email'] ) && is_email( $data['email'] ) && $data['email'] !== $user->user_email ) {
			add_filter( 'send_email_change_email', '__return_false', 99 );
			wp_update_user(
				array(
					'ID'         => $user->ID,
					'user_email' => $data['email']
				)
			);
			$updated = true;
		}

		// update WP user with data (use reversed field map)
		// loop through mapping rules
		foreach( $this->field_mappers as $rule ) {

			// is this field present in the request data? do not use empty here
			if( isset( $data['merges'][ $rule['mailchimp_field'] ] ) ) {

				// is scalar value?
				$value = $data['merges'][ $rule['mailchimp_field'] ];
				if( ! is_scalar( $value ) ) {
					continue;
				}

				// update user property if it changed
				// @todo Default user properties can be combined into single `wp_update_user` call for performance improvement
				if( $user->{$rule['user_field']} !== $value ) {
					update_user_meta( $user->ID, $rule['user_field'], $value );
					$updated = true;
				}
			}

		}

		if( $updated ) {
			$log->info( sprintf( "Webhook: Updated user #%d", $user->ID ) );
		}

		/**
		 * Fire an event to allow custom actions, like deleting the user if this is an unsubscribe ping.
		 *
		 * @param array $data
		 * @param WP_User $user
		 */
		do_action( 'mailchimp_sync_webhook', $data, $user );

		/**
		 * Fire type specific event.
		 *
		 * The dynamic portion of the hook, $type, regers to the webhook event type.
		 *
		 * Example: mailchimp_sync_webhook_unsubscribe
		 *
		 * @param array $data
		 * @param WP_User $user
		 */
		do_action( 'mailchimp_sync_webhook_' . $type, $data, $user );

		status_header(200);
		echo 'OK';
		return true;
	}

	/**
	 * @return MC4WP_Debug_Log
	 */
	private function get_log() {
		return mc4wp('log');
	}

}