<?php


class ClassCleantalkFindSpamUsersChecker extends ClassCleantalkFindSpamChecker
{

    public function __construct() {

        parent::__construct();

        $this->page_title = esc_html__( 'Check users for spam', 'cleantalk' );
        $this->page_script_name = 'users.php';
        $this->page_slug = 'users';

        // Preparing data
        $current_user = wp_get_current_user();
        if( ! empty( $_COOKIE['ct_paused_users_check'] ) )
            $prev_check = json_decode( stripslashes( $_COOKIE['ct_paused_users_check'] ), true );

        wp_enqueue_script( 'ct_users_checkspam',  plugins_url('/cleantalk-spam-protect/js/cleantalk-users-checkspam.min.js'), array( 'jquery', 'jqueryui' ), APBCT_VERSION );
        wp_localize_script( 'ct_users_checkspam', 'ctUsersCheck', array(
            'ct_ajax_nonce'               => wp_create_nonce('ct_secret_nonce'),
            'ct_prev_accurate'            => !empty($prev_check['accurate']) ? true                : false,
            'ct_prev_from'                => !empty($prev_check['from'])     ? $prev_check['from'] : false,
            'ct_prev_till'                => !empty($prev_check['till'])     ? $prev_check['till'] : false,
            'ct_timeout'                  => __('Failed from timeout. Going to check users again.', 'cleantalk'),
            'ct_timeout_delete'           => __('Failed from timeout. Going to run a new attempt to delete spam users.', 'cleantalk'),
            'ct_confirm_deletion_all'     => __('Delete all spam users?', 'cleantalk'),
            'ct_iusers'                   => __('users.', 'cleantalk'),
            'ct_csv_filename'             => "user_check_by_".$current_user->user_login,
            'ct_status_string'            => __("Checked %s, found %s spam users and %s bad users (without IP or email)", 'cleantalk'),
            'ct_status_string_warning'    => "<p>".__("Please do backup of WordPress database before delete any accounts!", 'cleantalk')."</p>"
        ));

        wp_enqueue_style( 'cleantalk_admin_css_settings_page', plugins_url().'/cleantalk-spam-protect/css/cleantalk-spam-check.min.css', array(), APBCT_VERSION, 'all' );

        require_once(CLEANTALK_PLUGIN_DIR . 'inc/find-spam/ClassCleantalkUsersListTable.php');

    }

    public function getCurrentScanPage() {

        require_once(CLEANTALK_PLUGIN_DIR . 'inc/find-spam/ClassCleantalkUsersListTableScan.php');
        $this->list_table = new ABPCTUsersListTableScan();

        $this->getCurrentScanPanel( $this );
        echo '<form action="" method="POST">';
        $this->list_table->display();
        echo '</form>';

    }

    public function getTotalSpamPage(){

        require_once(CLEANTALK_PLUGIN_DIR . 'inc/find-spam/ClassCleantalkUsersListTableSpam.php');
        $this->list_table = new ABPCTUsersListTableSpam();

        echo '<form action="" method="POST">';
        $this->list_table->display();
        echo '</form>';

    }

    public function getSpamLogsPage(){

        require_once(CLEANTALK_PLUGIN_DIR . 'inc/find-spam/ClassCleantalkUsersListTableLogs.php');
        $this->list_table = new ABPCTUsersListTableLogs();

        echo '<form action="" method="POST">';
        $this->list_table->display();
        echo '</form>';

    }

    /**
     * Get date last checked user or date first registered user
     *
     * @return string   date "M j Y"
     */
    public static function lastCheckDate() {

        // Checked users
        $params = array(
            'fields' => 'ID',
            'meta_key' => 'ct_checked',
            'count_total' => true,
            'orderby' => 'ct_checked'
        );
        $tmp = new WP_User_Query( $params );
        $cnt_checked = $tmp->get_total();

        if( $cnt_checked > 0 ) {

            // If we have checked users return last user reg date
            $users = $tmp->get_results();
            return self::getUserRegister( end( $users ) );

        } else {

            // If we have not any checked users return first user registered date
            $params = array(
                'fields' => 'ID',
                'number' => 1,
                'orderby' => 'user_registered'
            );
            $tmp = new WP_User_Query( $params );

            return self::getUserRegister( current( $tmp->get_results() ) );

        }

    }

    /**
     * Get date user registered
     *
     * @param $user_id
     * @return string Date format"M j Y"
     */
    private static function getUserRegister( $user_id ) {

        $user_data = get_userdata( $user_id );
        $registered = $user_data->user_registered;

        return date( "M j Y", strtotime( $registered ) );

    }

    static function ct_ajax_check_users(){

        check_ajax_referer('ct_secret_nonce', 'security');

        global $apbct;

        $amount = !empty($_POST['amount']) && intval($_POST['amount'])
            ? intval($_POST['amount'])
            : 100;

        $skip_roles = array(
            'administrator'
        );

        $params = array(
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'ct_checked_now',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => 'ct_checked',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => 'ct_bad',
                    'compare' => 'NOT EXISTS'
                )
            ),
            'orderby' => 'registered',
            'order' => 'ASC',
            'number' => $amount,
        );

        if(isset($_POST['from'], $_POST['till'])){

            $from_date = date('Y-m-d', intval(strtotime($_POST['from'])));
            $till_date = date('Y-m-d', intval(strtotime($_POST['till'])));

            $params['date_query'] = array(
                'column'   => 'user_registered',
                'after'     => $from_date,
                'before'    => $till_date,
                'inclusive' => true,
            );
        }

        $u = get_users( $params );

        $check_result = array(
            'end' => 0,
            'checked' => 0,
            'spam' => 0,
            'bad' => 0,
            'error' => 0
        );

        if( count($u) > 0 ){

            if( ! empty( $_POST['accurate_check'] ) ){
                // Leaving users only with first comment's date. Unsetting others.
                foreach( $u as $user_index => $user ){

                    if( ! isset( $curr_date ) )
                        $curr_date = ( substr( $user->data->user_registered, 0, 10 ) ? substr( $user->data->user_registered, 0, 10 ) : '' );

                    if( substr( $user->data->user_registered, 0, 10 ) != $curr_date )
                        unset( $u[$user_index] );

                }
                unset( $user_index, $user );
            }

            // Checking comments IP/Email. Gathering $data for check.
            $data = array();

            for( $i=0; $i < count($u); $i++ ){

                $user_meta = get_user_meta( $u[$i]->ID, 'session_tokens', true );
                if( is_array( $user_meta ) )
                    $user_meta = array_values( $user_meta );

                $curr_ip    = !empty( $user_meta[0]['ip' ])      ? trim( $user_meta[0]['ip'] )      : '';
                $curr_email = !empty( $u[$i]->data->user_email ) ? trim( $u[$i]->data->user_email ) : '';

                // Check for identity
                $curr_ip    = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $curr_ip) === 1 ? $curr_ip    : null;
                $curr_email = preg_match('/^\S+@\S+\.\S+$/', $curr_email) === 1                    ? $curr_email : null;

                if( empty( $curr_ip ) && empty( $curr_email ) ){
                    $check_result['bad']++;
                    update_user_meta( $u[$i]->ID,'ct_bad','1',true );
                    update_user_meta( $u[$i]->ID, 'ct_checked', date("Y-m-d H:m:s"), true) ;
                    unset( $u[$i] );
                }else{
                    if( !empty( $curr_ip ) )
                        $data[] = $curr_ip;
                    if( !empty( $curr_email ) )
                        $data[] = $curr_email;
                    // Patch for empty IP/Email
                    $u[$i]->data->user_ip    = empty($curr_ip)    ? 'none' : $curr_ip;
                    $u[$i]->data->user_email = empty($curr_email) ? 'none' : $curr_email;
                }
            }

            // Recombining after checking and unsettting
            $u = array_values( $u );

            // Drop if data empty and there's no users to check
            if( count( $data ) == 0 ){
                if( $_POST['unchecked'] === 0 )
                    $check_result['end'] = 1;
                print json_encode( $check_result );
                die();
            }

            $result = CleantalkAPI::method__spam_check_cms( $apbct->api_key, $data, !empty($_POST['accurate_check']) ? $curr_date : null );

            if( empty( $result['error'] ) ){

                for( $i=0; $i < sizeof( $u ); $i++ ) {

                    $check_result['checked']++;
                    update_user_meta( $u[$i]->ID, 'ct_checked', date("Y-m-d H:m:s"), true) ;
                    update_user_meta( $u[$i]->ID, 'ct_checked_now', date("Y-m-d H:m:s"), true) ;

                    // Do not display forbidden roles.
                    foreach ( $skip_roles as $role ) {
                        if ( in_array( $role, $u[$i]->roles ) ){
                            delete_user_meta( $u[$i]->ID, 'ct_marked_as_spam' );
                            continue 2;
                        }
                    }

                    $mark_spam_ip = false;
                    $mark_spam_email = false;

                    $uip = $u[$i]->data->user_ip;
                    $uim = $u[$i]->data->user_email;

                    if( isset( $result[$uip] ) && $result[$uip]['appears'] == 1 )
                        $mark_spam_ip = true;

                    if( isset($result[$uim]) && $result[$uim]['appears'] == 1 )
                        $mark_spam_email = true;

                    if ( $mark_spam_ip || $mark_spam_email ){
                        $check_result['spam']++;
                        update_user_meta( $u[$i]->ID, 'ct_marked_as_spam', '1', true );
                    }

                }

                echo json_encode( $check_result );

            } else {

                $check_result['error'] = 1;
                $check_result['error_message'] = $result['error'];

                echo json_encode( $check_result );

            }
        } else {

            $check_result['end'] = 1;

            $log_data  = static::get_log_data();
            static::writeSpamLog( 'users', date("Y-m-d H:i:s"), $log_data['checked'], $log_data['spam'], $log_data['bad'] );

            echo json_encode( $check_result );

        }

        die;

    }

    /**
     * Run query for deleting 'ct_checked_now' meta. Need for the new scan.
     *
     * @return void
     */
    public static function ct_ajax_clear_users()
    {
        check_ajax_referer( 'ct_secret_nonce', 'security' );

        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key IN ('ct_checked_now')");

        if ( isset($_POST['from']) && isset($_POST['till']) ) {
            if ( preg_match('/[a-zA-Z]{3}\s{1}\d{1,2}\s{1}\d{4}/', $_POST['from'] ) && preg_match('/[a-zA-Z]{3}\s{1}\d{1,2}\s{1}\d{4}/', $_POST['till'] ) ) {

                $from = date('Y-m-d', intval(strtotime($_POST['from']))) . ' 00:00:00';
                $till = date('Y-m-d', intval(strtotime($_POST['till']))) . ' 23:59:59';

                $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE 
                meta_key IN ('ct_checked','ct_marked_as_spam','ct_bad') 
                AND meta_value >= '{$from}' 
                AND meta_value <= '{$till}';");

                die();

            }
        }

        die();
    }

    public static function ct_ajax_info($direct_call = false) {

        if (!$direct_call)
            check_ajax_referer( 'ct_secret_nonce', 'security' );

        // Checked users
        $params_checked = array(
            'fields' => 'ID',
            'meta_key' => 'ct_checked_now',
            'count_total' => true,
            'orderby' => 'ct_checked_now'
        );
        $checked_users = new WP_User_Query($params_checked);
        $cnt_checked = $checked_users->get_total();

        // Spam users
        $params_spam = array(
            'fields' => 'ID',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'ct_marked_as_spam',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => 'ct_checked_now',
                    'compare' => 'EXISTS'
                ),
            ),
            'count_total' => true,
        );
        $spam_users = new WP_User_Query($params_spam);
        $cnt_spam = $spam_users->get_total();

        // Bad users (without IP and Email)
        $params_bad = array(
            'fields' => 'ID',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'ct_bad',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => 'ct_checked_now',
                    'compare' => 'EXISTS'
                ),
            ),
            'count_total' => true,
        );
        $bad_users = new WP_User_Query($params_bad);
        $cnt_bad = $bad_users->get_total();

        $return = array(
            'message'  => '',
            'spam'     => $cnt_spam,
            'checked'  => $cnt_checked,
            'bad'      => $cnt_bad,
        );

        if( ! $direct_call ) {
            $return['message'] .= sprintf (
                esc_html__('Checked %s, found %s spam users and %s bad users (without IP or email)', 'cleantalk'),
                $cnt_checked,
                $cnt_spam,
                $cnt_bad
            );
        } else {
            if( isset( $return['checked'] ) && 0 == $return['checked']  ) {
                $return['message'] = esc_html__( 'Never checked yet or no new spam.', 'cleantalk' );
            } else {
                $return['message'] .= sprintf (
                    __("Last check %s: checked %s users, found %s spam users and %s bad users (without IP or email).", 'cleantalk'),
                    self::lastCheckDate(),
                    $cnt_checked,
                    $cnt_spam,
                    $cnt_bad
                );
            }
        }

        $backup_notice = '&nbsp;';
        if ($cnt_spam > 0) {
            $backup_notice = __("Please do backup of WordPress database before delete any accounts!", 'cleantalk');
        }
        $return['message'] .= "<p>$backup_notice</p>";

        if($direct_call){
            return $return['message'];
        }else{
            echo json_encode($return);
            die();
        }
    }

    private static function get_log_data() {

        // Checked users
        $params_checked = array(
            'fields' => 'ID',
            'meta_key' => 'ct_checked_now',
            'count_total' => true,
            'orderby' => 'ct_checked_now'
        );
        $checked_users = new WP_User_Query($params_checked);
        $cnt_checked = $checked_users->get_total();

        // Spam users
        $params_spam = array(
            'fields' => 'ID',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'ct_marked_as_spam',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => 'ct_checked_now',
                    'compare' => 'EXISTS'
                ),
            ),
            'count_total' => true,
        );
        $spam_users = new WP_User_Query($params_spam);
        $cnt_spam = $spam_users->get_total();

        // Bad users (without IP and Email)
        $params_bad = array(
            'fields' => 'ID',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'ct_bad',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => 'ct_checked_now',
                    'compare' => 'EXISTS'
                ),
            ),
            'count_total' => true,
        );
        $bad_users = new WP_User_Query($params_bad);
        $cnt_bad = $bad_users->get_total();

        return array(
            'spam'     => $cnt_spam,
            'checked'  => $cnt_checked,
            'bad'      => $cnt_bad,
        );

    }

    /**
     * Admin action 'wp_ajax_ajax_ct_get_csv_file' - prints CSV file to AJAX
     */
    public static function ct_get_csv_file() {

        check_ajax_referer( 'ct_secret_nonce', 'security' );

        $text = 'login,email,ip' . PHP_EOL;

        $params = array(
            'meta_query' => array(
                array(
                    'key' => 'ct_marked_as_spam',
                    'compare' => '1'
                ),
            ),
            'orderby' => 'registered',
            'order' => 'ASC',
        );

        $u = get_users( $params );

        for( $i=0; $i < count($u); $i++ ){
            $user_meta = get_user_meta( $u[$i]->ID, 'session_tokens', true );
            if( is_array( $user_meta ) )
                $user_meta = array_values( $user_meta );
            $text .= $u[$i]->user_login.',';
            $text .= $u[$i]->data->user_email.',';
            $text .= ! empty( $user_meta[0]['ip']) ? trim( $user_meta[0]['ip'] ) : '';
            $text .=  PHP_EOL;
        }

        $filename = ! empty( $_POST['filename'] ) ? $_POST['filename'] : false;

        if( $filename !== false ) {
            header('Content-Type: text/csv');
            echo $text;
        } else {
            echo 'Export error.'; // file not exists or empty $_POST['filename']
        }
        die();

    }

    public static function ct_ajax_delete_all_users($count_all = 0)
    {
        check_ajax_referer( 'ct_secret_nonce', 'security' );

        global $wpdb;

        $r = $wpdb->get_results("select count(*) as cnt from $wpdb->usermeta where meta_key='ct_marked_as_spam';", OBJECT );

        if(!empty($r)){

            $count_all = $r ? $r[0]->cnt : 0;

            $args = array(
                'meta_key' => 'ct_marked_as_spam',
                'meta_value' => '1',
                'fields' => array('ID'),
                'number' => 50
            );
            $users = get_users($args);

            if ($users){
                foreach($users as $user){
                    wp_delete_user($user->ID);
                    usleep(5000);
                }
            }
        }

        die($count_all);
    }

}