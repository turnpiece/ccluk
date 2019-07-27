<?php
defined( 'ABSPATH' ) || exit;

class BP_Better_Messages_Bulk
{

    public static function instance()
    {

        // Store the instance locally to avoid private static replication
        static $instance = null;

        // Only run these methods if they haven't been run previously
        if ( null === $instance ) {
            $instance = new BP_Better_Messages_Bulk;
            $instance->setup_actions();
        }

        // Always return the instance
        return $instance;

        // The last metroid is in captivity. The galaxy is at peace.
    }

    public function setup_actions()
    {
        add_action( 'wp_ajax_bp_better_messages_select_users',  array( $this, 'select_users') );
        add_action( 'wp_ajax_bp_better_messages_send_messages', array( $this, 'send_messages') );
        add_action( 'wp_ajax_bp_better_messages_change_report', array( $this, 'change_report') );
        add_action( 'wp_ajax_bp_better_messages_delete_report', array( $this, 'delete_report') );
        add_action( 'init',                                     array( $this, 'register_post_type') );
        add_filter( 'bp_better_messages_can_send_message',      array( $this, 'disabled_thread_reply' ), 10, 3);
    }

    public function delete_report(){
        global $wpdb;
        if( ! current_user_can('manage_options') ) return false;
        if ( ! wp_verify_nonce( $_POST[ 'nonce' ], 'delete_report' ) ) return false;

        $report_id = intval($_POST['report_id']);
        $report = get_post($report_id);
        if( ! $report ) wp_send_json(false);

        $threads = get_post_meta($report->ID, 'thread_ids');
        foreach($threads as $thread_id){
            $messages_ids_sql = $wpdb->prepare("SELECT id FROM `{$wpdb->base_prefix}bp_messages_messages` WHERE `thread_id` = %d", $thread_id);
            $messages_ids = $wpdb->get_col($messages_ids_sql);
            //DELETE MESSAGES META
            $wpdb->query("DELETE FROM `{$wpdb->base_prefix}bp_messages_meta` WHERE `message_id` IN (". implode(',', $messages_ids) .")");
            //DELETE MESSAGES
            $wpdb->query($wpdb->prepare("DELETE FROM `{$wpdb->base_prefix}bp_messages_messages` WHERE `thread_id` = %d", $thread_id));
            //DELETE RECIPIENTS
            $wpdb->query($wpdb->prepare("DELETE FROM `{$wpdb->base_prefix}bp_messages_recipients` WHERE `thread_id` = %d", $thread_id));
        }

        // DELETE REPORT
        wp_delete_post($report->ID, true);

        exit;
    }

    public function change_report(){
        if( ! current_user_can('manage_options') ) return false;
        if ( ! wp_verify_nonce( $_POST[ 'nonce' ], 'change_report' ) ) return false;

        $report_id = intval($_POST['report_id']);
        $report = get_post($report_id);
        if( ! $report ) wp_send_json(false);

        $key = sanitize_text_field($_POST['property']);
        $value = sanitize_text_field($_POST['value']);

        $result = update_post_meta($report->ID, $key, $value);

        wp_send_json($result);
    }

    public function disabled_thread_reply( $allowed, $user_id, $thread_id ){
        $reports = get_posts(array(
            'post_type' => 'bpbm-bulk-report',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => 'thread_ids',
                    'value' => $thread_id
                )
            )
        ));

        if(isset($reports[0])){
            $disableReply = get_post_meta($reports[0]->ID, 'disableReply', true);
            if($disableReply === '1') $allowed = false;
        }

        return $allowed;
    }

    public function register_post_type(){
        register_post_type( 'bpbm-bulk-report', array(
            'public' => false
        ) );
    }

    public function send_messages(){
        if( ! current_user_can('manage_options') ) return false;
        if ( ! wp_verify_nonce( $_POST[ 'nonce' ], 'send_messages' ) ) return false;

        $errors = array();
        $form = wp_parse_args($_POST['selector']);
        $sentTo = $form['sent-to'];

        $args = array(
            'count_total' => true,
            'number'      => sanitize_text_field($_POST['perPage']),
            'paged'       => sanitize_text_field($_POST['current']),
            'exclude' => array( get_current_user_id() )
        );


        if($form['singleThread'] == '1'){
            $args['number'] = -1;
        }

        switch ($sentTo){
            case 'role';
                $args['role__in'] = $form['roles'];
                break;
            case 'group':
                $users = groups_get_group_members(array(
                    'group_id' => intval($form['group']),
                    'per_page' => -1
                ));

                $usersIds = array();
                foreach($users['members'] as $user){
                    if($user->ID == get_current_user_id()) continue;
                    $usersIds[] = $user->ID;
                }

                unset($args['exclude']);

                $args['include'] = $usersIds;

                break;
        }

        $report_id = sanitize_text_field($_POST['report_id']);
        $hide_thread = get_post_meta($report_id, 'hideThread', true);

        // The Query
        $users = new WP_User_Query( $args );
        $usersIds = array();
        // User Loop
        if ( ! empty( $users->get_results() ) ) {
            foreach ( $users->get_results() as $user ) {
                if($form['singleThread'] == '1'){
                    $usersIds[] = $user->ID;
                    continue;
                }

                $args = array(
                    'subject'    => sanitize_text_field( $form[ 'subject' ] ),
                    'content'    => esc_textarea( $form[ 'message' ] ),
                    'error_type' => 'wp_error',
                    'recipients' => array($user->ID)
                );

                do_action_ref_array( 'bp_better_messages_before_new_thread', array( &$args, &$errors ));

                if( empty( $errors ) ){
                    $thread_id = messages_new_message( $args );
                    add_post_meta($report_id, 'thread_ids', $thread_id, false);
                    if( $hide_thread == '1' ) BP_Messages_Thread::delete( $thread_id );
                }
            }

            if($form['singleThread'] == '1'){
                $args = array(
                    'subject'    => sanitize_text_field( $form[ 'subject' ] ),
                    'content'    => esc_textarea( $form[ 'message' ] ),
                    'error_type' => 'wp_error',
                    'recipients' => $usersIds
                );

                do_action_ref_array( 'bp_better_messages_before_new_thread', array( &$args, &$errors ));

                if( empty( $errors ) ){
                    $thread_id = messages_new_message( $args );
                    add_post_meta($report_id, 'thread_ids', $thread_id, false);
                    if( $hide_thread == '1' ) BP_Messages_Thread::delete( $thread_id );
                }
            }

        }

        echo 'ok';
        exit;
    }

    public function select_users(){
        if( ! current_user_can('manage_options') ) return false;
        if ( ! wp_verify_nonce( $_POST[ 'nonce' ], 'select_users' ) ) return false;

        $form = wp_parse_args($_POST['selector']);
        $sentTo = $form['sent-to'];

        $args = array(
            'count_total' => true,
            'exclude' => array( get_current_user_id() )
        );

        switch ($sentTo){
            case 'all':
                $users = new WP_User_Query($args);
                break;
            case 'role';
                $args['role__in'] = $form['roles'];
                $users = new WP_User_Query($args);
                break;
            case 'group':
                $users = groups_get_group_members(array(
                    'group_id' => intval($form['group']),
                    'per_page' => -1
                ));

                $usersIds = array();
                foreach($users['members'] as $user){
                    if($user->ID == get_current_user_id()) continue;
                    $usersIds[] = $user->ID;
                }

                unset($args['exclude']);

                $args['include'] = $usersIds;

                $users = new WP_User_Query($args);
            break;
        }

        $errors = array();
        $return = array(
            'total' => $users->get_total()
        );

        if(isset($_POST['report_id'])){
            if( empty(trim($form[ 'message' ]) )){
                $errors['empty'] = __('Message is empty', 'bp-better-messages');
            }

            if( $users->get_total() == 0 ){
                $errors['no_users'] = __('No users was selected', 'bp-better-messages');
            }

            if( count($errors) == 0){
                $return['report_id'] = wp_insert_post(array(
                    'post_type' => 'bpbm-bulk-report',
                    'meta_input' => array(
                        'subject'       => sanitize_text_field( $form[ 'subject' ] ),
                        'disableReply'  => (isset($form['disableReply'])) ? '1' : '0',
                        'hideThread'    => (isset($form['hideThread'])) ? '1' : '0'
                    )
                ));
            } else {
                $return['errors'] = $errors;
            }
        }

        wp_send_json($return);
    }

}

function BP_Better_Messages_Bulk()
{
    return BP_Better_Messages_Bulk::instance();
}