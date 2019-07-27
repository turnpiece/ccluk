<?php
global $wpdb;
$wpdb->messagemeta = $wpdb->base_prefix . 'bp_messages_meta';


if( ! function_exists('bp_core_fetch_avatar') ){
function bp_core_fetch_avatar( $args = '' ) {

        global $current_blog;

        // Set the default variables array and parse it against incoming $args array.
        $params = wp_parse_args( $args, array(
            'item_id'       => false,
            'object'        => 'user',
            'type'          => 'thumb',
            'avatar_dir'    => false,
            'width'         => false,
            'height'        => false,
            'class'         => 'avatar',
            'css_id'        => false,
            'alt'           => '',
            'email'         => false,
            'no_grav'       => null,
            'html'          => true,
            'title'         => '',
            'extra_attr'    => '',
            'scheme'        => null,
            'rating'        => get_option( 'avatar_rating' ),
            'force_default' => false,
        ) );

        return get_avatar($params['item_id'], 50);
}
}

if( ! function_exists('bp_core_get_userlink') ) {
    function bp_core_get_userlink($user_id, $no_anchor = false, $just_link = false)
    {
        return get_author_posts_url($user_id);
    }
}

if( ! function_exists('bp_core_get_user_displayname') ) {
    function bp_core_get_user_displayname( $user_id_or_username ) {
        if ( empty( $user_id_or_username ) ) {
            return false;
        }

        if ( ! is_numeric( $user_id_or_username ) ) {
            $user_id = get_user_by( 'slug', $user_id_or_username );
        } else {
            $user_id = $user_id_or_username;
        }

        if ( empty( $user_id ) ) {
            return false;
        }

        $user = get_userdata( $user_id);

        return $user->display_name;
    }
}

if( ! function_exists('bp_loggedin_user_id') ) {
    function bp_loggedin_user_id() {
        return get_current_user_id();
    }
}
if( ! function_exists('bp_displayed_user_id') ) {
    function bp_displayed_user_id() {
        return get_current_user_id();
    }
}

if( ! function_exists('bp_messages_is_message_starred') ) {
function bp_messages_is_message_starred( $mid = 0, $user_id = 0 ) {
    if ( empty( $user_id ) ) {
        $user_id = bp_displayed_user_id();
    }

    if ( empty( $mid ) ) {
        return false;
    }

    $starred = array_flip( (array) bp_messages_get_meta( $mid, 'starred_by_user', false ) );

    if ( isset( $starred[$user_id] ) ) {
        return true;
    } else {
        return false;
    }
}
}


if( ! function_exists('messages_mark_thread_read') ) {
function messages_mark_thread_read( $thread_id ) {
    return BP_Messages_Thread::mark_as_read( $thread_id );
}
}


if( ! function_exists('bp_messages_delete_meta') ) {
function bp_messages_delete_meta( $message_id, $meta_key = false, $meta_value = false, $delete_all = false ) {
    // Legacy - if no meta_key is passed, delete all for the item.
    if ( empty( $meta_key ) ) {
        global $wpdb;

        $keys = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM {$wpdb->messagemeta} WHERE message_id = %d", $message_id ) );

        // With no meta_key, ignore $delete_all.
        $delete_all = false;
    } else {
        $keys = array( $meta_key );
    }

    $retval = false;

    // No keys, so stop now!
    if ( empty( $keys ) ) {
        return $retval;
    }

    add_filter( 'query', 'bp_filter_metaid_column_name' );

    foreach ( $keys as $key ) {
        $retval = delete_metadata( 'message', $message_id, $key, $meta_value, $delete_all );
    }

    remove_filter( 'query', 'bp_filter_metaid_column_name' );

    return $retval;
}
}

if( ! function_exists('bp_messages_get_meta') ) {
    function bp_messages_get_meta( $message_id, $meta_key = '', $single = true ) {
        add_filter( 'query', 'bp_filter_metaid_column_name' );
        $retval = get_metadata( 'message', $message_id, $meta_key, $single );
        remove_filter( 'query', 'bp_filter_metaid_column_name' );

        return $retval;
    }
}

if( ! function_exists('bp_messages_update_meta') ) {
function bp_messages_update_meta( $message_id, $meta_key, $meta_value, $prev_value = '' ) {
    add_filter( 'query', 'bp_filter_metaid_column_name' );
    $retval = update_metadata( 'message', $message_id, $meta_key, $meta_value, $prev_value );
    remove_filter( 'query', 'bp_filter_metaid_column_name' );

    return $retval;
}
}


if( ! function_exists('bp_messages_add_meta') ) {
function bp_messages_add_meta( $message_id, $meta_key, $meta_value, $unique = false ) {
    add_filter( 'query', 'bp_filter_metaid_column_name' );
    $retval = add_metadata( 'message', $message_id, $meta_key, $meta_value, $unique );
    remove_filter( 'query', 'bp_filter_metaid_column_name' );

    return $retval;
}
}


if( ! function_exists('messages_new_message') ) {
function messages_new_message( $args = '' ) {

    // Parse the default arguments.
    $r = bp_parse_args( $args, array(
        'sender_id'  => bp_loggedin_user_id(),
        'thread_id'  => false,   // False for a new message, thread id for a reply to a thread.
        'recipients' => array(), // Can be an array of usernames, user_ids or mixed.
        'subject'    => false,
        'content'    => false,
        'date_sent'  => bp_core_current_time(),
        'error_type' => 'bool'
    ), 'messages_new_message' );

    // Bail if no sender or no content.
    if ( empty( $r['sender_id'] ) || empty( $r['content'] ) ) {
        if ( 'wp_error' === $r['error_type'] ) {
            if ( empty( $r['sender_id'] ) ) {
                $error_code = 'messages_empty_sender';
                $feedback   = __( 'Your message was not sent. Please use a valid sender.', 'buddypress' );
            } else {
                $error_code = 'messages_empty_content';
                $feedback   = __( 'Your message was not sent. Please enter some content.', 'buddypress' );
            }

            return new WP_Error( $error_code, $feedback );

        } else {
            return false;
        }
    }

    // Create a new message object.
    $message            = new BP_Messages_Message;
    $message->thread_id = $r['thread_id'];
    $message->sender_id = $r['sender_id'];
    $message->subject   = $r['subject'];
    $message->message   = $r['content'];
    $message->date_sent = $r['date_sent'];

    // If we have a thread ID...
    if ( ! empty( $r['thread_id'] ) ) {

        // ...use the existing recipients
        $thread              = new BP_Messages_Thread( $r['thread_id'] );
        $message->recipients = $thread->get_recipients();

        // Strip the sender from the recipient list, and unset them if they are
        // not alone. If they are alone, let them talk to themselves.
        if ( isset( $message->recipients[ $r['sender_id'] ] ) && ( count( $message->recipients ) > 1 ) ) {
            unset( $message->recipients[ $r['sender_id'] ] );
        }

        // Set a default reply subject if none was sent.
        if ( empty( $message->subject ) ) {
            $message->subject = sprintf( __( 'Re: %s', 'buddypress' ), $thread->messages[0]->subject );
        }

        // ...otherwise use the recipients passed
    } else {

        // Bail if no recipients.
        if ( empty( $r['recipients'] ) ) {
            if ( 'wp_error' === $r['error_type'] ) {
                return new WP_Error( 'message_empty_recipients', __( 'Message could not be sent. Please enter a recipient.', 'buddypress' ) );
            } else {
                return false;
            }
        }

        // Set a default subject if none exists.
        if ( empty( $message->subject ) ) {
            $message->subject = __( 'No Subject', 'buddypress' );
        }

        // Setup the recipients array.
        $recipient_ids = array();

        // Invalid recipients are added to an array, for future enhancements.
        $invalid_recipients = array();

        // Loop the recipients and convert all usernames to user_ids where needed.
        foreach ( (array) $r['recipients'] as $recipient ) {

            // Trim spaces and skip if empty.
            $recipient = trim( $recipient );
            if ( empty( $recipient ) ) {
                continue;
            }

            // Check user_login / nicename columns first
            // @see http://buddypress.trac.wordpress.org/ticket/5151.
            if ( bp_is_username_compatibility_mode() ) {
                $recipient_id = bp_core_get_userid( urldecode( $recipient ) );
            } else {
                $recipient_id = bp_core_get_userid_from_nicename( $recipient );
            }

            // Check against user ID column if no match and if passed recipient is numeric.
            if ( empty( $recipient_id ) && is_numeric( $recipient ) ) {
                if ( bp_core_get_core_userdata( (int) $recipient ) ) {
                    $recipient_id = (int) $recipient;
                }
            }

            // Decide which group to add this recipient to.
            if ( empty( $recipient_id ) ) {
                $invalid_recipients[] = $recipient;
            } else {
                $recipient_ids[] = (int) $recipient_id;
            }
        }

        // Strip the sender from the recipient list, and unset them if they are
        // not alone. If they are alone, let them talk to themselves.
        $self_send = array_search( $r['sender_id'], $recipient_ids );
        if ( ! empty( $self_send ) && ( count( $recipient_ids ) > 1 ) ) {
            unset( $recipient_ids[ $self_send ] );
        }

        // Remove duplicates & bail if no recipients.
        $recipient_ids = array_unique( $recipient_ids );
        if ( empty( $recipient_ids ) ) {
            if ( 'wp_error' === $r['error_type'] ) {
                return new WP_Error( 'message_invalid_recipients', __( 'Message could not be sent because you have entered an invalid username. Please try again.', 'buddypress' ) );
            } else {
                return false;
            }
        }

        // Format this to match existing recipients.
        foreach ( (array) $recipient_ids as $i => $recipient_id ) {
            $message->recipients[ $i ]          = new stdClass;
            $message->recipients[ $i ]->user_id = $recipient_id;
        }
    }

    // Bail if message failed to send.
    $send = $message->send();
    if ( false === is_int( $send ) ) {
        if ( 'wp_error' === $r['error_type'] ) {
            if ( is_wp_error( $send ) ) {
                return $send;
            } else {
                return new WP_Error( 'message_generic_error', __( 'Message was not sent. Please try again.', 'buddypress' ) );
            }
        }

        return false;
    }

    /**
     * Fires after a message has been successfully sent.
     *
     * @since 1.1.0
     *
     * @param BP_Messages_Message $message Message object. Passed by reference.
     */
    do_action_ref_array( 'messages_message_sent', array( &$message ) );

    // Return the thread ID.
    return $message->thread_id;
}
}

if( ! function_exists('bp_parse_args') ) {
function bp_parse_args( $args, $defaults = array(), $filter_key = '' ) {

    // Setup a temporary array from $args.
    if ( is_object( $args ) ) {
        $r = get_object_vars( $args );
    } elseif ( is_array( $args ) ) {
        $r =& $args;
    } else {
        wp_parse_str( $args, $r );
    }

    // Passively filter the args before the parse.
    if ( !empty( $filter_key ) ) {

        /**
         * Filters the arguments key before parsing if filter key provided.
         *
         * This is a dynamic filter dependent on the specified key.
         *
         * @since 2.0.0
         *
         * @param array $r Array of arguments to use.
         */
        $r = apply_filters( 'bp_before_' . $filter_key . '_parse_args', $r );
    }

    // Parse.
    if ( is_array( $defaults ) && !empty( $defaults ) ) {
        $r = array_merge( $defaults, $r );
    }

    // Aggressively filter the args after the parse.
    if ( !empty( $filter_key ) ) {

        /**
         * Filters the arguments key after parsing if filter key provided.
         *
         * This is a dynamic filter dependent on the specified key.
         *
         * @since 2.0.0
         *
         * @param array $r Array of parsed arguments.
         */
        $r = apply_filters( 'bp_after_' . $filter_key . '_parse_args', $r );
    }

    // Return the parsed results.
    return $r;
}
}

if( ! function_exists('bp_core_current_time') ) {
function bp_core_current_time( $gmt = true, $type = 'mysql' ) {

    /**
     * Filters the current GMT time to save into the DB.
     *
     * @since 1.2.6
     *
     * @param string $value Current GMT time.
     */
    return apply_filters( 'bp_core_current_time', current_time( $type, $gmt ) );
}
}



if( ! function_exists('bp_messages_update_meta_cache') ) {
    function bp_messages_update_meta_cache($message_ids = false)
    {
        global $wpdb;

        bp_update_meta_cache(array(
            'object_ids' => $message_ids,
            'object_type' => 'messages',
            'cache_group' => 'message_meta',
            'object_column' => 'message_id',
            'meta_table' => $wpdb->base_prefix . 'bp_messages_meta',
            'cache_key_prefix' => 'bp_messages_meta'
        ));
    }
}

if( ! function_exists('bp_update_meta_cache') ) {
function bp_update_meta_cache( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'object_ids' 	   => array(), // Comma-separated list or array of item ids.
        'object_type' 	   => '',      // Canonical component id: groups, members, etc.
        'cache_group'      => '',      // Cache group.
        'meta_table' 	   => '',      // Name of the table containing the metadata.
        'object_column'    => '',      // DB column for the object ids (group_id, etc).
        'cache_key_prefix' => ''       // Prefix to use when creating cache key names. Eg 'bp_groups_groupmeta'.
    );
    $r = wp_parse_args( $args, $defaults );
    extract( $r );

    if ( empty( $object_ids ) || empty( $object_type ) || empty( $meta_table ) || empty( $cache_group ) ) {
        return false;
    }

    if ( empty( $cache_key_prefix ) ) {
        $cache_key_prefix = $meta_table;
    }

    if ( empty( $object_column ) ) {
        $object_column = $object_type . '_id';
    }

    if ( ! $cache_group ) {
        return false;
    }

    $object_ids   = wp_parse_id_list( $object_ids );
    $uncached_ids = bp_get_non_cached_ids( $object_ids, $cache_group );

    $cache = array();

    // Get meta info.
    if ( ! empty( $uncached_ids ) ) {
        $id_list   = join( ',', wp_parse_id_list( $uncached_ids ) );
        $meta_list = $wpdb->get_results( esc_sql( "SELECT {$object_column}, meta_key, meta_value FROM {$meta_table} WHERE {$object_column} IN ({$id_list})" ), ARRAY_A );

        if ( ! empty( $meta_list ) ) {
            foreach ( $meta_list as $metarow ) {
                $mpid = intval( $metarow[$object_column] );
                $mkey = $metarow['meta_key'];
                $mval = $metarow['meta_value'];

                // Force subkeys to be array type.
                if ( !isset( $cache[$mpid] ) || !is_array( $cache[$mpid] ) )
                    $cache[$mpid] = array();
                if ( !isset( $cache[$mpid][$mkey] ) || !is_array( $cache[$mpid][$mkey] ) )
                    $cache[$mpid][$mkey] = array();

                // Add a value to the current pid/key.
                $cache[$mpid][$mkey][] = $mval;
            }
        }

        foreach ( $uncached_ids as $uncached_id ) {
            // Cache empty values as well.
            if ( ! isset( $cache[ $uncached_id ] ) ) {
                $cache[ $uncached_id ] = array();
            }

            wp_cache_set( $uncached_id, $cache[ $uncached_id ], $cache_group );
        }
    }

    return $cache;
}
}

if( ! function_exists('bp_get_non_cached_ids') ) {
    function bp_get_non_cached_ids($item_ids, $cache_group)
    {
        $uncached = array();

        foreach ($item_ids as $item_id) {
            $item_id = (int)$item_id;
            if (false === wp_cache_get($item_id, $cache_group)) {
                $uncached[] = $item_id;
            }
        }

        return $uncached;
    }
}

if( ! function_exists('messages_remove_callback_values') ) {
    function messages_remove_callback_values()
    {
        @setcookie('bp_messages_send_to', false, time() - 1000, COOKIEPATH, COOKIE_DOMAIN, is_ssl());
        @setcookie('bp_messages_subject', false, time() - 1000, COOKIEPATH, COOKIE_DOMAIN, is_ssl());
        @setcookie('bp_messages_content', false, time() - 1000, COOKIEPATH, COOKIE_DOMAIN, is_ssl());
    }
}

if( ! function_exists('bp_core_get_suggestions') ) {
function bp_core_get_suggestions( $args ) {
    $args = bp_parse_args( $args, array(), 'get_suggestions' );

    if ( ! $args['type'] ) {
        return new WP_Error( 'missing_parameter' );
    }

    // Members @name suggestions.
    if ( $args['type'] === 'members' ) {
        $class = 'BP_Members_Suggestions';

        // Members @name suggestions for users in a specific Group.
        if ( isset( $args['group_id'] ) ) {
            $class = 'BP_Groups_Member_Suggestions';
        }

    } else {

        /**
         * Filters the default suggestions service to use.
         *
         * Use this hook to tell BP the name of your class
         * if you've built a custom suggestions service.
         *
         * @since 2.1.0
         *
         * @param string $value Custom class to use. Default: none.
         * @param array  $args  Array of arguments for sugggestions.
         */
        $class = apply_filters( 'bp_suggestions_services', '', $args );
    }

    if ( ! $class || ! class_exists( $class ) ) {
        return new WP_Error( 'missing_parameter' );
    }


    $suggestions = new $class( $args );
    $validation  = $suggestions->validate();

    if ( is_wp_error( $validation ) ) {
        $retval = $validation;
    } else {
        $retval = $suggestions->get_suggestions();
    }

    /**
     * Filters the available type of at-mentions.
     *
     * @since 2.1.0
     *
     * @param array|WP_Error $retval Array of results or WP_Error object.
     * @param array          $args   Array of arguments for suggestions.
     */
    return apply_filters( 'bp_core_get_suggestions', $retval, $args );
}
}


if( ! function_exists('bp_is_user_active') ) {
function bp_is_user_active( $user_id = 0 ) {

    // Default to current user.
    if ( empty( $user_id ) && is_user_logged_in() ) {
        $user_id = bp_loggedin_user_id();
    }

    // No user to check.
    if ( empty( $user_id ) ) {
        return false;
    }

    // Assume true if not spam or deleted.
    return true;
}
}

if( ! function_exists('bp_disable_profile_sync') ) {
function bp_disable_profile_sync( $default = false ) {

    /**
     * Filters whether or not profile syncing is disabled.
     *
     * @since 1.6.0
     *
     * @param bool $value Whether or not syncing is disabled.
     */
    return (bool) apply_filters( 'bp_disable_profile_sync', false );
}}


if( ! function_exists('bp_core_get_status_sql') ) {
function bp_core_get_status_sql( $prefix = false ) {
    if ( !is_multisite() )
        return "{$prefix}user_status = 0";
    else
        return "{$prefix}spam = 0 AND {$prefix}deleted = 0 AND {$prefix}user_status = 0";
}
}

if( ! function_exists('bp_esc_like') ) {
function bp_esc_like( $text ) {
    global $wpdb;

    if ( method_exists( $wpdb, 'esc_like' ) ) {
        return $wpdb->esc_like( $text );
    } else {
        return addcslashes( $text, '_%\\' );
    }
}
}

if( ! function_exists('bp_is_username_compatibility_mode') ) {
function bp_is_username_compatibility_mode() {

    /**
     * Filters whether or not to use username compatibility mode.
     *
     * @since 1.5.0
     *
     * @param bool $value Whether or not username compatibility mode should be used.
     */
    return apply_filters( 'bp_is_username_compatibility_mode', defined( 'BP_ENABLE_USERNAME_COMPATIBILITY_MODE' ) && BP_ENABLE_USERNAME_COMPATIBILITY_MODE );
}}

if( ! function_exists('bp_core_get_userid_from_nicename') ) {
function bp_core_get_userid_from_nicename( $user_nicename = '' ) {
    if ( empty( $user_nicename ) ) {
        return false;
    }

    $user = get_user_by( 'slug', $user_nicename );

    /**
     * Filters the user ID based on user_nicename.
     *
     * @since 1.2.3
     *
     * @param int|null $value         ID of the user or null.
     * @param string   $user_nicename User nicename to check.
     */
    return apply_filters( 'bp_core_get_userid_from_nicename', ! empty( $user->ID ) ? $user->ID : NULL, $user_nicename );
}
}

if( ! function_exists('bp_filter_metaid_column_name') ) {
    function bp_filter_metaid_column_name( $q ) {
        /*
         * Replace quoted content with __QUOTE__ to avoid false positives.
         * This regular expression will match nested quotes.
         */
        $quoted_regex = "/'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'/s";
        preg_match_all( $quoted_regex, $q, $quoted_matches );
        $q = preg_replace( $quoted_regex, '__QUOTE__', $q );

        $q = str_replace( 'meta_id', 'id', $q );

        // Put quoted content back into the string.
        if ( ! empty( $quoted_matches[0] ) ) {
            for ( $i = 0; $i < count( $quoted_matches[0] ); $i++ ) {
                $quote_pos = strpos( $q, '__QUOTE__' );
                $q = substr_replace( $q, $quoted_matches[0][ $i ], $quote_pos, 9 );
            }
        }

        return $q;
    }
}

    if( ! function_exists('messages_check_thread_access') ) {
    function messages_check_thread_access( $thread_id, $user_id = 0 ) {
        if ( empty( $user_id ) ) {
            $user_id = bp_loggedin_user_id();
        }

        return BP_Messages_Thread::check_access( $thread_id, $user_id );
    }
}

if( ! function_exists('messages_get_message_thread_id') ) {
function messages_get_message_thread_id( $message_id = 0 ) {
    global $wpdb;

    return (int) $wpdb->get_var( $wpdb->prepare( "SELECT thread_id FROM {$wpdb->base_prefix}bp_messages_messages WHERE id = %d", $message_id ) );
}}

if( ! function_exists('bp_messages_star_set_action') ) {
    function bp_messages_star_set_action( $args = array() ) {
        $r = wp_parse_args( $args, array(
            'action'     => 'star',
            'thread_id'  => 0,
            'message_id' => 0,
            'user_id'    => bp_displayed_user_id(),
            'bulk'       => false
        ) );

        // Set thread ID.
        if ( ! empty( $r['thread_id'] ) ) {
            $thread_id = (int) $r['thread_id'];
        } else {
            $thread_id = messages_get_message_thread_id( $r['message_id'] );
        }
        if ( empty( $thread_id ) ) {
            return false;
        }

        // Check if user has access to thread.
        if( ! messages_check_thread_access( $thread_id, $r['user_id'] ) ) {
            return false;
        }

        $is_starred = bp_messages_is_message_starred( $r['message_id'], $r['user_id'] );

        // Star.
        if ( 'star' == $r['action'] ) {
            if ( true === $is_starred ) {
                return true;
            } else {
                bp_messages_add_meta( $r['message_id'], 'starred_by_user', $r['user_id'] );
                return true;
            }
            // Unstar.
        } else {
            // Unstar one message.
            if ( false === $r['bulk'] ) {
                if ( false === $is_starred ) {
                    return true;
                } else {
                    bp_messages_delete_meta( $r['message_id'], 'starred_by_user', $r['user_id'] );
                    return true;
                }

                // Unstar all messages in a thread.
            } else {
                $thread = new BP_Messages_Thread( $thread_id );
                $mids = wp_list_pluck( $thread->messages, 'id' );

                foreach ( $mids as $mid ) {
                    if ( true === bp_messages_is_message_starred( $mid, $r['user_id'] ) ) {
                        bp_messages_delete_meta( $mid, 'starred_by_user', $r['user_id'] );
                    }
                }

                return true;
            }
        }
    }
}

if( ! function_exists('bp_update_user_last_activity') ){
    function bp_update_user_last_activity( $user_id = 0, $time = '' ) {

        // Fall back on current user.
        if ( empty( $user_id ) ) {
            $user_id = bp_loggedin_user_id();
        }

        // Bail if the user id is 0, as there's nothing to update.
        if ( empty( $user_id ) ) {
            return false;
        }

        // Fall back on current time.
        if ( empty( $time ) ) {
            $time = bp_core_current_time();
        }

        $updated = update_user_meta( $user_id, 'last_activity', $time );

        return BP_Core_User::update_last_activity( $user_id, $time );
    }
}

if(! function_exists('bp_core_enqueue_livestamp')){
    function bp_core_enqueue_livestamp() {
        // If bp-livestamp isn't enqueued, do it now.
        if ( wp_script_is( 'bp-livestamp' ) ) {
            return;
        }

        /*
         * Only enqueue Moment.js locale if we registered it in
         * bp_core_register_common_scripts().
         */
        if ( wp_script_is( 'bp-moment-locale', 'registered' ) ) {
            wp_enqueue_script( 'bp-moment-locale' );

            if ( function_exists( 'wp_add_inline_script' ) ) {
                wp_add_inline_script ( 'bp-livestamp', bp_core_moment_js_config() );
            } else {
                add_action( 'wp_footer', '_bp_core_moment_js_config_footer', 20 );
            }
        }

        wp_enqueue_script( 'bp-livestamp' );
    }
}

if( ! function_exists('_bp_core_moment_js_config_footer') ){
    function _bp_core_moment_js_config_footer() {
        if ( ! wp_script_is( 'bp-moment-locale' ) ) {
            return;
        }

        printf( '<script>%s</script>', bp_core_moment_js_config() );
    }
}