<?php

namespace MC4WP\Sync;

class UserSubscriberAPIv2 implements UserSubscriber {

    /**
     * @var Users
     */
    protected $users;

    /**
     * @var string
     */
    protected $list_id;

    /**
     * Subscriber2 constructor.
     *
     * @param Users $users
     * @param string $list_id
     */
    public function __construct( Users $users, $list_id ) {
        $this->users = $users;
        $this->list_id = $list_id;
    }

    /**
     * @param int $user_id
     * @param array $args
     *
     * @return bool Whether user was already subscribed to the MailChimp list.
     *
     * @throws \Exception
     */
    public function subscribe( $user_id, array $args = array() ) {
        $args = array_merge( 
            array( 
              'double_optin' => false, 
              'email_type' => 'html', 
              'replace_interests' => false, 
              'send_welcome' => false,
              'resubscribe' => false, // unused
          ), $args );

        $subscriber_uid = $this->users->get_subscriber_uid( $user_id );
        if( ! empty( $subscriber_uid ) ) {
            return $this->update( $user_id, $args );
        }

        $api = $this->get_api();
        $user = $this->users->user( $user_id );
        $merge_vars = $this->users->get_user_merge_fields( $user );
        $update_existing = true;
        $success = $api->subscribe( $this->list_id, $user->user_email, $merge_vars, $args['email_type'], $args['double_optin'], $update_existing, $args['replace_interests'], $args['send_welcome'] );

        if( ! $success ) {
            $error_code = $api->get_error_code();
            $error_message = $api->get_error_message();
            throw new Exception( $error_code, $error_message );
        }

        $last_api_response = $api->get_last_response();
        $subscriber_uid = $last_api_response->leid;
        $this->users->set_subscriber_uid( $user_id, $subscriber_uid );

        return false;
    }

    /**
     * @param int $user_id
     * @param array $args
     *
     * @return bool Whether user was already subscribed to the MailChimp list.
     *
     * @throws \Exception
     */
    public function update( $user_id, array $args = array() ) {
        $user = $this->users->user( $user_id );
        $args = array_merge( 
            array( 
              'email_type' => 'html', 
              'replace_interests' => false, 
          ), $args );

        $merge_vars = $this->users->get_user_merge_fields( $user );
        $merge_vars['new-email'] = $user->user_email;

        $subscriber_uid = $this->users->get_subscriber_uid( $user->ID );

        // update subscriber in mailchimp
        $api = $this->get_api();
        $success = $api->update_subscriber( $this->list_id, array( 'leid' => $subscriber_uid ), $merge_vars, $args['email_type'], $args['replace_interests'] );

        // Error?
        if( ! $success ) {
            $error_code = $api->get_error_code();
            $error_message = $api->get_error_message();

            // subscriber leid did not match anything in the list
            if( in_array( $error_code, array( 215, 232 ) ) ) {

                // delete subscriber leid as it's apparently wrong
                $this->users->delete_subscriber_uid( $user->ID );

                // re-subscribe user
                return $this->subscribe( $user->ID, $args );
            }

            // throw exception for other errors
             throw new Exception( $error_code, $error_message );
        }

        return true;
    }

    /**
     * @param int $user_id
     * @param string $email_address
     * @param string $subscriber_uid
     * @param boolean $send_goodbye
     * @param boolean $send_notification
     * @param boolean $delete_member
     */
    public function unsubscribe( $user_id, $email_address, $subscriber_uid = null, $send_goodbye = false, $send_notification = false, $delete_member = false ) {

        // fetch subscriber_uid
        if( is_null( $subscriber_uid ) ) {
            $subscriber_uid = $this->users->get_subscriber_uid( $user_id );
        }

        // if user is not even subscribed, just bail.
        if( empty( $subscriber_uid ) ) {
            return true;
        }

        $success = $this->get_api()->unsubscribe( $this->list_id, $email_address, $send_goodbye, $send_notification, $delete_member );

        $error_code = $this->get_api()->get_error_code();
        $error_message = $this->get_api()->get_error_message();
        if( ! empty( $error_message ) ) {
            throw new Exception( $error_code, $error_message );
        }

        if( $success ) {
            $this->users->delete_subscriber_uid( $user_id );
        } 

        return true;
    }

    /**
    * @return MC4WP_API
    */
    private function get_api() {
        return mc4wp_get_api();
    }
}
