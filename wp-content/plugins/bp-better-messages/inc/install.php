<?php

add_action( 'bp_better_messages_activation', 'bp_install_email_templates' );

function bp_install_email_templates()
{

    if ( ! function_exists( 'bp_get_email_post_type' ) ) return;

    $defaults = array(
        'post_status' => 'publish',
        'post_type'   => bp_get_email_post_type(),
    );

    $emails = array(
        'messages-unread-group' => array(
            /* translators: do not remove {} brackets or translate its contents. */
            'post_title'   => __( '[{{{site.name}}}] You have unread messages: {{subject}}', 'bp-better-messages' ),
            /* translators: do not remove {} brackets or translate its contents. */
            'post_content' => __( "You have unread messages: &quot;{{subject}}&quot;\n\n{{{messages.html}}}\n\n<a href=\"{{{thread.url}}}\">Go to the discussion</a> to reply or catch up on the conversation.", 'bp-better-messages' ),
            /* translators: do not remove {} brackets or translate its contents. */
            'post_excerpt' => __( "You have unread messages: \"{{subject}}\"\n\n{{messages.raw}}\n\nGo to the discussion to reply or catch up on the conversation: {{{thread.url}}}", 'bp-better-messages' ),
        )
    );

    $descriptions[ 'messages-unread-group' ] = __( 'A member has unread private messages.', 'bp-better-messages' );

    // Add these emails to the database.
    foreach ( $emails as $id => $email ) {
        $post_args = bp_parse_args( $email, $defaults, 'install_email_' . $id );

        $template = get_page_by_title( $post_args[ 'post_title' ], OBJECT, bp_get_email_post_type() );
        if ( $template ) $post_args[ 'ID' ] = $template->ID;

        $post_id = wp_insert_post( $post_args );

        if ( !$post_id ) {
            continue;
        }

        $tt_ids = wp_set_object_terms( $post_id, $id, bp_get_email_tax_type() );
        foreach ( $tt_ids as $tt_id ) {
            $term = get_term_by( 'term_taxonomy_id', (int)$tt_id, bp_get_email_tax_type() );
            wp_update_term( (int)$term->term_id, bp_get_email_tax_type(), array(
                'description' => $descriptions[ $id ],
            ) );
        }
    }
}

add_action( 'bp_better_messages_activation', 'bpbp_install_tables' );

function bpbp_install_tables(){
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $sql             = array();
    $charset_collate = $GLOBALS['wpdb']->get_charset_collate();
    $bp_prefix       = $GLOBALS['wpdb']->base_prefix;

    $sql[] = "CREATE TABLE {$bp_prefix}bp_messages_messages (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				thread_id bigint(20) NOT NULL,
				sender_id bigint(20) NOT NULL,
				subject varchar(200) NOT NULL,
				message longtext NOT NULL,
				date_sent datetime NOT NULL,
				KEY sender_id (sender_id),
				KEY thread_id (thread_id)
			) {$charset_collate};";

    $sql[] = "CREATE TABLE {$bp_prefix}bp_messages_recipients (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				user_id bigint(20) NOT NULL,
				thread_id bigint(20) NOT NULL,
				unread_count int(10) NOT NULL DEFAULT '0',
				sender_only tinyint(1) NOT NULL DEFAULT '0',
				is_deleted tinyint(1) NOT NULL DEFAULT '0',
				KEY user_id (user_id),
				KEY thread_id (thread_id),
				KEY is_deleted (is_deleted),
				KEY sender_only (sender_only),
				KEY unread_count (unread_count)
			) {$charset_collate};";

    $sql[] = "CREATE TABLE {$bp_prefix}bp_messages_notices (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				subject varchar(200) NOT NULL,
				message longtext NOT NULL,
				date_sent datetime NOT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '0',
				KEY is_active (is_active)
			) {$charset_collate};";

    $sql[] = "CREATE TABLE {$bp_prefix}bp_messages_meta (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				message_id bigint(20) NOT NULL,
				meta_key varchar(255) DEFAULT NULL,
				meta_value longtext DEFAULT NULL,
				KEY message_id (message_id),
				KEY meta_key (meta_key(191))
			) {$charset_collate};";

    dbDelta( $sql );
}

add_action( 'bp_better_messages_deactivation', 'bp_better_messages_unschedule_cron' );

function bp_better_messages_unschedule_cron()
{
	wp_unschedule_event( wp_next_scheduled( 'bp_better_messages_send_notifications' ), 'bp_better_messages_send_notifications' );
	wp_unschedule_event( wp_next_scheduled( 'bp_better_messages_clear_attachments' ), 'bp_better_messages_clear_attachments' );
}


function bp_better_messages_activation()
{
    do_action( 'bp_better_messages_activation' );
}

function bp_better_messages_deactivation()
{
    do_action( 'bp_better_messages_deactivation' );
}