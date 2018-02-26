<?php

defined( 'ABSPATH' ) or exit;

/**
 * Upgrade old filter to new subscriber filter, setting the correct interest ID's.
 *
 * @since 1.4.7
 * @ignore
 * @access private
 *
 * @param MC4WP_MailChimp_Subscriber $subscriber
 * @param WP_User $user
 *
 * @return MC4WP_MailChimp_Subscriber
 */
function _mailchimp_sync_update_groupings_to_interests( MC4WP_MailChimp_Subscriber $subscriber, WP_User $user ) {

    // run old filter
    $data = (array) apply_filters( 'mailchimp_sync_user_data', array(), $user );

    // nothing? good!
    if( empty( $data['INTERESTS'] ) ) {
        return $subscriber;
    }

    // set subscriber property from data
    foreach( $data['INTERESTS'] as $interest_id ) {
        $subscriber->interests[ $interest_id ] = true;
    }

    // remove old data & merge_fields key
    unset( $data['INTERESTS'] );
    unset( $subscriber->merge_fields['INTERESTS'] );

    return $subscriber;
}
