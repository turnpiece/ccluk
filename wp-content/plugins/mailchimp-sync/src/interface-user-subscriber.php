<?php

namespace MC4WP\Sync;

interface UserSubscriber {
	
	public function subscribe( $user_id, array $args = array()  );
	public function update( $user_id, array $args = array());
	public function unsubscribe( $user_id, $email_address, $subscriber_uid = null, $send_goodbye = false, $send_notification = false, $delete_member = false );

}
