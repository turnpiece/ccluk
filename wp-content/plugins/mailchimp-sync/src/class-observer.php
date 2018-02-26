<?php


namespace MC4WP\Sync;

use Error;
use MC4WP_Queue as Queue;

class Observer {

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var Users
     */
    private $users;

    /**
     * Worker constructor.
     *
     * @param Queue      $queue
     * @param Users 	$users
     */
    public function __construct( Queue $queue, Users $users ) {
        $this->queue = $queue;
        $this->users = $users;
    }

    /**
     * Add hooks
     */
    public function add_hooks() {
        add_action( 'user_register', array( $this, 'on_user_register' ) );
        add_action( 'profile_update', array( $this, 'on_profile_update' ), 10, 2);
        add_action( 'updated_user_meta', array( $this, 'on_updated_user_meta' ), 10, 3 );
        add_action( 'delete_user', array( $this, 'on_delete_user' ) );
    }

    public function on_user_register( $user_id ) {
        $this->schedule( array(
            'type' => 'subscribe',
            'user_id' => $user_id
        ) );
    }

    public function on_profile_update( $user_id, $old_user_data ) {
        $job_data = array(
            'type' => 'handle',
            'user_id' => $user_id
        );

        $this->schedule( $job_data );
    }

    public function on_updated_user_meta( $meta_id, $user_id, $meta_key  ) {
        //  Don't act on our own keys or hidden meta keys.
        if( strpos( $meta_key, 'mailchimp' ) === 0 || strpos( $meta_key, 'mc4wp_' ) === 0 || strpos( $meta_key, '_' ) === 0 ) {
            return;
        }

        /*
         * Don't act on last-login meta keys.
         *
         * @see https://wordpress.org/plugins/user-last-login/
         * @see https://wordpress.org/plugins/wp-last-login/
         */
        if( in_array( $meta_key, array( 'wp-last-login' ) ) ) {
            return;
        }

        $this->schedule(
            array(
                'type' => 'handle',
                'user_id' => $user_id
            )
        );
    }

    public function on_delete_user( $user_id ) {
        // fetch meta values now, because user is about to be deleted
        $user = $this->users->user( $user_id );
        $mailchimp_email_address = $this->users->get_mailchimp_email_address( $user_id );
        $email_address = empty( $mailchimp_email_address ) ? $user->user_email : $mailchimp_email_address;
        $subscriber_uid = $this->users->get_subscriber_uid( $user_id );

        $this->schedule( array(
            'type' => 'unsubscribe',
            'user_id' => $user_id,
            'email_address' => $email_address,
            'subscriber_uid' => $subscriber_uid,
        ) );
    }

    /**
     * Adds a task to the queue
     *
     * @param array $job_data
     */
    private function schedule( $job_data ) {
        // Don't schedule anything when doing webhook
        if( defined( 'MC4WP_SYNC_DOING_WEBHOOK' ) && MC4WP_SYNC_DOING_WEBHOOK ) {
            return;
        }

        $this->queue->put( $job_data );
    }

}