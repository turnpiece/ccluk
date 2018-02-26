<?php

namespace MC4WP\Sync;

interface UserSubscriber {
	
	public function subscribe( $user_id, $double_optin = false, $email_type = 'html', $replace_interests = false, $send_welcome = false  );
	public function update( $user_id, $email_type = 'html', $replace_interests = false );
	public function unsubscribe( $user_id, $email_address, $subscriber_uid = null, $send_goodbye = false, $send_notification = false, $delete_member = false );

}
