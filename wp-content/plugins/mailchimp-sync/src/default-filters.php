<?php

defined( 'ABSPATH' ) or exit;

add_filter( 'mailchimp_sync_subscriber_data', '_mailchimp_sync_update_groupings_to_interests', PHP_INT_MAX - 1, 2 );