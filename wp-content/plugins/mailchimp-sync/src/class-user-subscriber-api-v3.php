<?php

namespace MC4WP\Sync;

use MC4WP_API_v3;
use MC4WP_MailChimp_Subscriber;
use WP_User;

class UserSubscriberAPIv3 implements UserSubscriber {

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
  * @param WP_User $user
  * @param $double_optin
  * @param $email_type
  * @return MC4WP_MailChimp_Subscriber
  */
  private function transform( WP_User $user, $double_optin = false, $email_type = 'html' ) {
    $subscriber = new MC4WP_MailChimp_Subscriber();
    $subscriber->email_address = $user->user_email;
    $subscriber->merge_fields = $this->users->get_user_merge_fields( $user );
    $subscriber->email_type = $email_type;
    $subscriber->status = $double_optin ? 'pending' : 'subscribed';

    /**
    * Filter data that is sent to MailChimp
    *
    * @param MC4WP_MailChimp_Subscriber $subscriber
    * @param WP_User $user
    */
    $subscriber = apply_filters( 'mailchimp_sync_subscriber_data', $subscriber, $user );

    return $subscriber;
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
          'resubscribe' => false,
      ), $args );

    $user = $this->users->user( $user_id );
    $subscriber = $this->transform( $user, $args['double_optin'], $args['email_type'] );
    $exists = false;
    $data = $subscriber->to_array();
   
    // get old email
    $mailchimp_email_address = $this->users->get_mailchimp_email_address( $user_id );
    if( empty( $mailchimp_email_address ) ) {
      $mailchimp_email_address = $subscriber->email_address;
    }

    // perform the call
    try {
      $existing_member_data = $this->get_api()->get_list_member( $this->list_id, $mailchimp_email_address );
      $exists = true;

      switch( $existing_member_data->status ) {
        case 'subscribed':
          // this key only exists if list actually has interests
          if ( isset( $existing_member_data->interests ) ) {
            $existing_interests = (array) $existing_member_data->interests;

            // if replace, assume all existing interests disabled
            if ($args['replace_interests']) {
              $existing_interests = array_fill_keys(array_keys($existing_interests), false);
            }

            $new_interests = $data['interests'];
            $data['interests'] = $existing_interests;
            foreach( $new_interests as $interest_id => $interest_status ) {
              $data['interests']["{$interest_id}"] = $interest_status;
            }
          }

          $data['status'] = 'subscribed';
          break;

        case 'transactional':
          break;  

        // if subscriber is cleaned, add as a new subscriber
        case 'cleaned':
          $exists = false;
          break;  

        // do not re-subscribe people that unsubscribed (unless user control is on)
        case 'unsubscribed':
          if( ! $args['resubscribe'] ) {
            $data['status'] = 'unsubscribed';
          }
          break;
      }
    } catch( \MC4WP_API_Resource_Not_Found_Exception $e ) {
      // OK: subscriber does not exist yet, but we're adding it later.
      $exists = false;
    }

    // add or update subscriber
    if( $exists ) {
      $member = $this->get_api()->update_list_member( $this->list_id, $mailchimp_email_address, $data );
    } else {
      $member = $this->get_api()->add_list_member( $this->list_id, $data );
    }
    
    // Store remote email address & last updated timestamp
    $this->users->set_subscriber_uid( $user_id, $member->unique_email_id );
    $this->users->set_mailchimp_email_address( $user_id, $member->email_address );
    $this->users->touch( $user_id );
    return $exists;
  }

  /**
  * @param $user_id
  * @param array $args
  *
  * @return bool Whether user was already subscribed to the MailChimp list.
  *
  * @throws \Exception
  */
  public function update( $user_id, array $args = array() ) {
    return $this->subscribe( $user_id, $args );
  }

  /**
  * @param int $user_id
  * @param string $email_address
  * @param string $subscriber_uid        (optional)
  * @param null $send_goodbye            (unused)
  * @param null $send_notification       (unused)
  * @param null $delete_member           (unused)
  *
  * @return bool Whether user was subscribed to the MailChimp list.
  */
  public function unsubscribe( $user_id, $email_address, $subscriber_uid = null, $send_goodbye = null, $send_notification = null, $delete_member = null ) {

    $mailchimp_email_address = $this->users->get_mailchimp_email_address( $user_id );
    if( empty( $mailchimp_email_address ) ) {
      $mailchimp_email_address = $email_address;
    }

    $exists = false;

    // perform the call    
    try {
      $existing_member_data = $this->get_api()->get_list_member( $this->list_id, $mailchimp_email_address );
      $exists = true;
    } catch( \MC4WP_API_Resource_Not_Found_Exception $e ) {
      // OK: subscriber does not exist, no need to unsubscribe
      $exists = false;
    }

    // only unsubscribe users that are fully subscribed
    if( $exists && $existing_member_data->status === 'subscribed' ) {
      $args = array( 
        'status' => 'unsubscribed' 
      );
      $member = $this->get_api()->update_list_member( $this->list_id, $mailchimp_email_address, $args );
    }

    $this->users->delete_mailchimp_email_address( $user_id );
    $this->users->delete_subscriber_uid( $user_id );
    return $exists;
  }

  /**
  * @return MC4WP_API_v3
  */
  private function get_api() {
    return mc4wp('api');
  }
}
