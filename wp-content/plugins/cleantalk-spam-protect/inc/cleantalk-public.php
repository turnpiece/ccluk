<?php

/**
 * Init functions 
 * @return 	mixed[] Array of options
 */
function apbct_init() {
    global $ct_wplp_result_label, $ct_jp_comments, $ct_post_data_label, $ct_post_data_authnet_label, $ct_options, $ct_data, $ct_check_post_result, $test_external_forms, $cleantalk_executed, $wpdb;

    $ct_options = ct_get_options();
	$ct_data=ct_get_data();
    	
    //Check internal forms with such "action" http://wordpress.loc/contact-us/some_script.php
    if((isset($_POST['action']) && $_POST['action'] == 'ct_check_internal') &&
        (isset($ct_options['check_internal']) && intval($ct_options['check_internal']))
    ){
        $ct_result = ct_contact_form_validate();
        if($ct_result == null){
            echo 'true';
            die();
        }else{
            echo $ct_result;
            die();
        }
    }
    
    //fix for EPM registration form
    if(isset($_POST) && isset($_POST['reg_email']) && shortcode_exists( 'epm_registration_form' ))
    {
    	unset($_POST['ct_checkjs_register_form']);
    }
    
    if(isset($_POST['_wpnonce-et-pb-contact-form-submitted']))
    {
    	add_shortcode( 'et_pb_contact_form', 'ct_contact_form_validate' );
    }
	
    if(!empty($ct_options['check_external']) 
		&& isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'
		&& isset($_POST['cleantalk_hidden_method'])
		&& isset($_POST['cleantalk_hidden_action'])
	){
    	$action=htmlspecialchars($_POST['cleantalk_hidden_action']);
    	$method=htmlspecialchars($_POST['cleantalk_hidden_method']);
    	unset($_POST['cleantalk_hidden_action']);
    	unset($_POST['cleantalk_hidden_method']);
    	ct_contact_form_validate();
		if(empty($_POST['cleantalk_hidden_ajax'])){
			print "<html><body><form method='$method' action='$action'>";
			ct_print_form($_POST,'');
			print "</form><center>Redirecting to ".$action."... Anti-spam by CleanTalk.</center></body></html>";
			print "<script>
				if(document.forms[0].submit != 'undefined'){
					var objects = document.getElementsByName('submit');
					if(objects.length > 0)
						document.forms[0].removeChild(objects[0]);
				}
				document.forms[0].submit();
			</script>";
			die();
		}
    }
    
    if(isset($ct_options['general_postdata_test']) && $ct_options['general_postdata_test'] == 1 && !@isset($_POST['ct_checkjs_cf7']))
    {
    	$ct_general_postdata_test = @intval($ct_options['general_postdata_test']);
    	//hook for Anonymous Post
    	add_action('template_redirect','ct_contact_form_validate_postdata',1);
    }
    else
    {
    	$ct_general_postdata_test=0;
    }
    
    if (isset($ct_options['general_contact_forms_test']) && $ct_options['general_contact_forms_test'] == 1&&!@isset($_POST['ct_checkjs_cf7']))
    {
		add_action('CMA_custom_post_type_nav','ct_contact_form_validate_postdata',1);
		add_action('template_redirect','ct_contact_form_validate',1);
		if(isset($_POST['reg_redirect_link'])&&isset($_POST['tmpl_registration_nonce_field']))
		{
			unset($_POST['ct_checkjs_register_form']);
			ct_contact_form_validate();
		}
		/*if(isset($_GET['ait-action'])&&$_GET['ait-action']=='register')
		{
			$tmp=$_POST['redirect_to'];
			unset($_POST['redirect_to']);
			ct_contact_form_validate();
			$_POST['redirect_to']=$tmp;
		}*/
	}
	
    if($ct_general_postdata_test==1&&!@isset($_POST['ct_checkjs_cf7']))
    {
    	add_action('CMA_custom_post_type_nav','ct_contact_form_validate_postdata',1);
    }
    
	//add_action('wp_footer','ct_ajaxurl');

    // Fast Secure contact form
    if(defined('FSCF_VERSION')){
	add_filter('si_contact_display_after_fields', 'ct_si_contact_display_after_fields');
	add_filter('si_contact_form_validate', 'ct_si_contact_form_validate');
    }

    // WooCoomerse signups
    if(class_exists('WooCommerce')){
		add_filter('woocommerce_register_post', 'ct_register_post', 1, 3);
    }
	if(class_exists('WC_Wishlists_Wishlist')){
		add_filter('wc_wishlists_create_list_args', 'ct_woocommerce_wishlist_check', 1, 1);
	}
	
	
    // JetPack Contact form
    $jetpack_active_modules = false;
    if(defined('JETPACK__VERSION'))
    {
		if(isset($_POST['action']) && $_POST['action'] == 'grunion-contact-form' ){
			if(JETPACK__VERSION=='3.4-beta')
			{
				add_filter('contact_form_is_spam', 'ct_contact_form_is_spam');
			}
			else if(JETPACK__VERSION=='3.4-beta2'||JETPACK__VERSION>='3.4')
			{
				add_filter('jetpack_contact_form_is_spam', 'ct_contact_form_is_spam_jetpack',50,2);
			}
			else
			{
				add_filter('contact_form_is_spam', 'ct_contact_form_is_spam');
			}
			$jetpack_active_modules = get_option('jetpack_active_modules');
			if ((class_exists( 'Jetpack', false) && $jetpack_active_modules && in_array('comments', $jetpack_active_modules)))
			{
				$ct_jp_comments = true;
			}
		}else
			add_filter('grunion_contact_form_field_html', 'ct_grunion_contact_form_field_html', 10, 2);
    }

    // Contact Form7 
    if(defined('WPCF7_VERSION')){
		add_filter('wpcf7_form_elements', 'apbct_form__contactForm7__addField');
		
		if(WPCF7_VERSION >= '3.0.0')
			add_filter('wpcf7_spam', 'apbct_form__contactForm7__testSpam');
		else
			add_filter('wpcf7_acceptance', 'apbct_form__contactForm7__testSpam');
		
    }
		
    // Formidable
    if(class_exists('FrmSettings')){
	    add_action('frm_validate_entry', 'ct_frm_validate_entry', 1, 2);
	    add_action('frm_entries_footer_scripts', 'ct_frm_entries_footer_scripts', 20, 2);
    }

    // BuddyPress
    if(class_exists('BuddyPress')){
		add_action('bp_before_registration_submit_buttons','ct_register_form',1);
		add_filter('bp_signup_validate', 'ct_registration_errors',1);
		add_filter('bp_signup_validate', 'ct_check_registration_erros', 999999);
		add_action('messages_message_before_save','ct_bp_private_msg_check', 1);
    }

	if(defined('PROFILEPRESS_SYSTEM_FILE_PATH')){
		add_filter('pp_registration_validation', 'ct_registration_errors_ppress', 11, 2);
	}
		
	
    // bbPress
    if(class_exists('bbPress')){
		add_filter('bbp_new_topic_pre_title', 'ct_bbp_get_topic', 1);
		add_filter('bbp_new_topic_pre_content', 'ct_bbp_new_pre_content', 1);
		add_filter('bbp_new_reply_pre_content', 'ct_bbp_new_pre_content', 1);
		add_action('bbp_theme_before_topic_form_content', 'ct_comment_form');
		add_action('bbp_theme_before_reply_form_content', 'ct_comment_form');
    }

	//Custom Contact Forms
	if(defined('CCF_VERSION')){
		add_filter('ccf_field_validator', 'ct_ccf', 1, 4);
	}
	
    add_action('comment_form', 'ct_comment_form');

    //intercept WordPress Landing Pages POST
    if (defined('LANDINGPAGES_CURRENT_VERSION') && !empty($_POST)){
        if(array_key_exists('action', $_POST) && $_POST['action'] === 'inbound_store_lead'){ // AJAX action(s)
            ct_check_wplp();
        }else if(array_key_exists('inbound_submitted', $_POST) && $_POST['inbound_submitted'] == '1'){ // Final submit
            ct_check_wplp();
        }
    }
    
    // intercept S2member POST
    if (defined('WS_PLUGIN__S2MEMBER_PRO_VERSION') && (isset($_POST[$ct_post_data_label]['email']) || isset($_POST[$ct_post_data_authnet_label]['email']))){
        ct_s2member_registration_test(); 
    }
    
    //
    // New user approve hack
    // https://wordpress.org/plugins/new-user-approve/
    //
    if (ct_plugin_active('new-user-approve/new-user-approve.php')) {
        add_action('register_post', 'ct_register_post', 1, 3);
    }
    
    //
    // Gravity forms
    //
    if (defined('GF_MIN_WP_VERSION')) {
        add_filter('gform_get_form_filter', 'apbct_form__gravityForms__addField', 10, 2);
        add_filter('gform_entry_is_spam', 'apbct_form__gravityForms__testSpam', 999, 3);
		add_filter('gform_confirmation', 'apbct_form__gravityForms__showResponse', 999, 4 );
    }
    	
	//
	//Pirate forms
	//
	if(defined('PIRATE_FORMS_VERSION')){
		if(isset($_POST['pirate-forms-contact-name']) && $_POST['pirate-forms-contact-name'] && isset($_POST['pirate-forms-contact-email']) && $_POST['pirate-forms-contact-email'])
			ct_pirate_forms_check();
	}

    //
    // Load JS code to website footer
    //
    if (!(defined( 'DOING_AJAX' ) && DOING_AJAX)) {
        add_action('wp_footer', 'ct_footer_add_cookie', 1);
    }
   
    if ($ct_options['protect_logged_in'] != 1 && is_user_logged_in()) {
        $ct_check_post_result=false;
        ct_contact_form_validate();
    }

    if (ct_is_user_enable()) {
        ct_cookies_test();

        if (isset($ct_options['general_contact_forms_test']) && $ct_options['general_contact_forms_test'] == 1 && !isset($_POST['comment_post_ID']) && !isset($_GET['for'])){
        	$ct_check_post_result=false;
            ct_contact_form_validate();
        }
        if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && $ct_general_postdata_test==1 && !is_admin() && !@isset($_POST['ct_checkjs_cf7'])){
	    	$ct_check_post_result=false;
	    	ct_contact_form_validate_postdata();
	    }
    }
}

// MailChimp Premium for Wordpress
function ct_add_mc4wp_error_message($messages){
		
	$messages['ct_mc4wp_response'] = array(
		'type' => 'error',
		'text' => 'Your message looks like spam.'
	);
	return $messages;
}
add_filter( 'mc4wp_form_messages', 'ct_add_mc4wp_error_message' );

/*
 * Function to set validate fucntion for CCF form
 * Input - Сonsistently each form field
 * Returns - String. Validate function
*/
function ct_ccf($callback, $value, $field_id, $type){
	/*
	if($type == 'name')
		$ct_global_temporary_data['name'] = $value;
	elseif($type == 'email')
		$ct_global_temporary_data['email'] = $value;
	else
		$ct_global_temporary_data[] = $value;
	//*/
	return 'ct_validate_ccf_submission';
}
/*
 * Validate function for CCF form. Gatheering data. Multiple calls.
 * Input - void. Global $ct_global_temporary_data
 * Returns - String. CleanTalk comment.
*/
$ct_global_temporary_data = array();
function ct_validate_ccf_submission($value, $field_id, $required){
	global $ct_global_temporary_data, $ct_options;
	
	$ct_options = ct_get_options();

	//If the check for contact forms enabled
	if(isset($ct_options['contact_forms_test']) && intval($ct_options['contact_forms_test']) == '0')
		return true;
	//If the check for logged in users enabled
	if(isset($ct_options['protect_logged_in']) && intval($ct_options['protect_logged_in']) == 1 && is_user_logged_in())
		return true;
	
	//Accumulate data
	$ct_global_temporary_data[] = $value;
	
	//If it's the last field of the form
	(!isset($ct_global_temporary_data['count']) ? $ct_global_temporary_data['count'] = 1 : $ct_global_temporary_data['count']++);
	$form_id = $_POST['form_id'];
	if($ct_global_temporary_data['count'] != count(get_post_meta( $form_id, 'ccf_attached_fields', true )))
		return true;
	unset($ct_global_temporary_data['count']);
	
	//Getting request params
	$ct_temp_msg_data = ct_get_fields_any($_POST);
	
	unset($ct_global_temporary_data);
			
    $sender_email    = ($ct_temp_msg_data['email']    ? $ct_temp_msg_data['email']    : '');
    $sender_nickname = ($ct_temp_msg_data['nickname'] ? $ct_temp_msg_data['nickname'] : '');
    $subject         = ($ct_temp_msg_data['subject']  ? $ct_temp_msg_data['subject']  : '');
    $contact_form    = ($ct_temp_msg_data['contact']  ? $ct_temp_msg_data['contact']  : true);
    $message         = ($ct_temp_msg_data['message']  ? $ct_temp_msg_data['message']  : array());
	
	if ($subject != '')
        $message['subject'] = $subject;
	
	$post_info['comment_type'] = 'feedback_custom_contact_forms';
    $post_info['post_url'] = $_SERVER['HTTP_REFERER'];
	
	$checkjs = !apbct_js_test('ct_checkjs', $_COOKIE, true)
		? apbct_js_test('ct_checkjs', $_COOKIE, true)
		: apbct_js_test('ct_checkjs', $_POST, true);
	
	//Making a call
	$base_call_result = apbct_base_call(
		array(
			'message'         => $message,
			'sender_email'    => $sender_email,
			'sender_nickname' => $sender_nickname,
			'post_info'       => $post_info,
			'checkjs'         => $checkjs,
			'sender_info'     => array('sender_url' => null),
		)
	);
	
    $ct_result = $base_call_result['ct_result'];
		
	return $ct_result->allow == 0 ? $ct_result->comment : true;;
}

function ct_woocommerce_wishlist_check($args){
	global $ct_options;
	
	$ct_options = ct_get_options();
	
	//Protect logged in users
	if($args['wishlist_status'])
		if(isset($ct_options['protect_logged_in']) && $ct_options['protect_logged_in'] == 0)
			return $args;
	
	//If the IP is a Google bot
	$hostname = gethostbyaddr( filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) );
	if(!strpos($hostname, 'googlebot.com'))
		return $args;
	
	//Getting request params
	$message = '';
	$subject = '';
	$email = $args['wishlist_owner_email'];
	if($args['wishlist_first_name']!='' || $args['wishlist_last_name']!='')
		$nickname = trim($args['wishlist_first_name']." ".$args['wishlist_last_name']);
	else
		$nickname = '';
	
	$post_info['comment_type'] = 'feedback'; 
    $post_info['post_url'] = $_SERVER['HTTP_REFERER']; 
	
	$checkjs = !apbct_js_test('ct_checkjs', $_COOKIE, true)
		? apbct_js_test('ct_checkjs', $_COOKIE, true)
		: apbct_js_test('ct_checkjs', $_POST, true);
		
	//Making a call
	$base_call_result = apbct_base_call(
		array(
			'message'         => $subject." ".$message,
			'sender_email'    => $email,
			'sender_nickname' => $nickname,
			'post_info'       => $post_info,
			'checkjs'         => $checkjs,
			'sender_info'     => array('sender_url' => null),
		)
	);
	
    $ct_result = $base_call_result['ct_result'];

    if ($ct_result->allow == 0)
		wp_die("<h1>".__('Spam protection by CleanTalk', 'cleantalk')."</h1><h2>".$ct_result->comment."</h2>", '', array('response' => 403, "back_link" => true, "text_direction" => 'ltr'));
	else
		return $args;
}

/**
* Public function - Tests new private messages (dialogs)
* return @array with errors if spam has found
*/

function ct_bp_private_msg_check( $bp_message_obj){
	global $ct_options;
	
	$ct_options = ct_get_options();

	//Check for enabled option
	if($ct_options['bp_private_messages'] == 0)
		return;
	
	//Check for quantity of comments
	$is_max_comments = false;
	if(defined('CLEANTALK_CHECK_COMMENTS_NUMBER'))
    	$comments_check_number = CLEANTALK_CHECK_COMMENTS_NUMBER;
    else
     	$comments_check_number = 3;

    if(isset($ct_options['check_comments_number']))
    	$value = @intval($ct_options['check_comments_number']);
    else
    	$value=1;

    if($value == 1){
		$args = array(
			'user_id' => $bp_message_obj->sender_id,
			'box' => 'sentbox',
			'type' => 'all',
			'limit' => $comments_check_number,
			'page' => null,
			'search_terms' => '',
			'meta_query' => array()
		);
    	$sentbox_msgs = BP_Messages_Thread::get_current_threads_for_user($args);
		$cnt_sentbox_msgs = $sentbox_msgs['total'];
		$args['box'] = 'inbox';
		$inbox_msgs = BP_Messages_Thread::get_current_threads_for_user($args);
		$cnt_inbox_msgs = $inbox_msgs['total'];
    	if(($cnt_inbox_msgs + $cnt_sentbox_msgs) >= $comments_check_number)
    		$is_max_comments = true;
    }
	
	if($is_max_comments)
		return;

	//Getting request params
	
	$sender_user_obj = get_user_by('id', $bp_message_obj->sender_id);
	
	$message = $bp_message_obj->message;
	$subject = $bp_message_obj->subject;
	$email = $sender_user_obj->data->user_email;
	$nickname = $sender_user_obj->data->user_login;
	
	$post_info['comment_type'] = 'buddypress_comment'; 
    $post_info['post_url'] = $_SERVER['HTTP_REFERER']; 
	
	$checkjs = !apbct_js_test('ct_checkjs', $_COOKIE, true)
		? apbct_js_test('ct_checkjs', $_COOKIE, true)
		: apbct_js_test('ct_checkjs', $_POST, true);
	
	//Making a call
	
	$base_call_result = apbct_base_call(
		array(
			'message'         => $subject." ".$message,
			'sender_email'    => $email,
			'sender_nickname' => $nickname,
			'post_info'       => $post_info,
			'checkjs'         => $checkjs,
			'sender_info'     => array('sender_url' => null),
		)
	);
    $ct_result = $base_call_result['ct_result'];

    if ($ct_result->allow == 0)
		wp_die("<h1>".__('Spam protection by CleanTalk', 'cleantalk')."</h1><h2>".$ct_result->comment."</h2>", '', array('response' => 403, "back_link" => true, "text_direction" => 'ltr'));
}

/**
* Public function - Tests for Pirate contact froms
* return NULL
*/
function ct_pirate_forms_check(){
	global $ct_options;
	
	$ct_options = ct_get_options();

	//Check for enabled option
	if($ct_options['contact_forms_test'] == 0)
		return;
	
	//Getting request params
	$ct_temp_msg_data = ct_get_fields_any($_POST);
	
    $sender_email    = ($ct_temp_msg_data['email']    ? $ct_temp_msg_data['email']    : '');
    $sender_nickname = ($ct_temp_msg_data['nickname'] ? $ct_temp_msg_data['nickname'] : '');
    $subject         = ($ct_temp_msg_data['subject']  ? $ct_temp_msg_data['subject']  : '');
    $contact_form    = ($ct_temp_msg_data['contact']  ? $ct_temp_msg_data['contact']  : true);
    $message         = ($ct_temp_msg_data['message']  ? $ct_temp_msg_data['message']  : array());

    if($subject != '')
		$message = array_merge(array('subject' => $subject), $message);
	
	$post_info['comment_type'] = 'feedback_pirate_contact_form'; 
    $post_info['post_url'] = $_SERVER['HTTP_REFERER']; 
	
	//Making a call
	$base_call_result = apbct_base_call(
		array(
			'message'         => $message,
			'sender_email'    => $sender_email,
			'sender_nickname' => $sender_nickname,
			'post_info'       => $post_info,
			'checkjs'         => apbct_js_test('ct_checkjs', $_COOKIE, true),
			'sender_info'     => array('sender_url' => null),
		)
	);
	
    $ct_result = $base_call_result['ct_result'];

    if ($ct_result->allow == 0)
		wp_die("<h1>".__('Spam protection by CleanTalk', 'cleantalk')."</h1><h2>".$ct_result->comment."</h2>", '', array('response' => 403, "back_link" => true, "text_direction" => 'ltr'));
}

function ct_ajaxurl() {
	?>
	<script type="text/javascript">
	var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
	</script>
	<?php
	wp_enqueue_script('ct_nocache_js',plugins_url( '/cleantalk_nocache.js' , __FILE__ ));
}

/**
 * Adds hidden filed to comment form 
 */
function ct_comment_form($post_id) {
    global $ct_options, $ct_data;
    $ct_options = ct_get_options();
    $ct_data = ct_get_data();

    if (ct_is_user_enable() === false) {
        return false;
    }

    if ($ct_options['comments_test'] == 0) {
        return false;
    }
    
    ct_add_hidden_fields(true, 'ct_checkjs', false, false);
	
    return null;
}

/**
 * Adds cookie script filed to footer
 */
function ct_footer_add_cookie() {
    
    ct_add_hidden_fields(true, 'ct_checkjs', false, true, true);
	
    return null;
}

/**
 * Adds hidden filed to define avaialbility of client's JavaScript
 * @param 	bool $random_key switch on generation random key for every page load 
 */
function ct_add_hidden_fields($random_key = false, $field_name = 'ct_checkjs', $return_string = false, $cookie_check = false, $no_print = false) {
		
    global $ct_checkjs_def, $ct_plugin_name, $ct_options, $ct_data;

    $ct_options = ct_get_options();
    
    $ct_checkjs_key = ct_get_checkjs_value($random_key); 
    $field_id_hash = md5(rand(0, 1000));
    
    if ($cookie_check && isset($ct_options['set_cookies']) && $ct_options['set_cookies'] == 1) { 
		$html =	"<script type='text/javascript'>
			function ctSetCookie(c_name, value, def_value){
				document.cookie = c_name + '=' + escape(value) + '; path=/';
			}
			ctSetCookie('{$field_name}', '{$ct_checkjs_key}', '{$ct_checkjs_def}');
		</script>";
    } else {
		
		// Fix only for wp_footer -> ct_footer_add_cookie()
		if($no_print)
			return;
		
        $ct_input_challenge = sprintf("'%s'", $ct_checkjs_key);
    	$field_id = $field_name . '_' . $field_id_hash;
		$html = "<input type='hidden' id='{$field_id}' name='{$field_name}' value='{$ct_checkjs_def}' />
		<script type='text/javascript'>
			setTimeout(function(){
				var ct_input_name = '{$field_id}';
				if (document.getElementById(ct_input_name) !== null) {
					var ct_input_value = document.getElementById(ct_input_name).value;
					document.getElementById(ct_input_name).value = document.getElementById(ct_input_name).value.replace(ct_input_value, {$ct_input_challenge});
				}
			}, 1000);
		</script>";
    };

    // Simplify JS code and Fixing issue with wpautop()
    $html = str_replace(array("\n","\r","\t"),'', $html);

    if ($return_string === true) {
        return $html;
    } else {
        echo $html;
    } 
}

/**
 * Is enable for user group
 * @return boolean
 */
function ct_is_user_enable() {
    global $current_user;

    if (!isset($current_user->roles)) {
        return true; 
    }

    $disable_roles = array('administrator', 'editor', 'author');
    foreach ($current_user->roles as $k => $v) {
        if (in_array($v, $disable_roles))
            return false;
    }

    return true;
    //return !current_user_can('publish_posts');
}

/**
* Public function - Insert JS code for spam tests
* return null;
*/
function ct_frm_entries_footer_scripts($fields, $form) {
    global $ct_options, $ct_checkjs_frm;
    
    if ($ct_options['contact_forms_test'] == 0)
        return false;
    
    $ct_checkjs_key = ct_get_checkjs_value();
    $ct_frm_base_name = 'form_';
    $ct_frm_name = $ct_frm_base_name . $form->form_key;
    
    echo "var input = document.createElement('input');
    input.setAttribute('type', 'hidden');
    input.setAttribute('name', '$ct_checkjs_frm');
    input.setAttribute('value', '$ct_checkjs_key');
    for (i = 0; i < document.forms.length; i++) {
        if (document.forms[i].id && document.forms[i].id.search('$ct_frm_name') != -1) {
            document.forms[i].appendChild(input);
        }
    }";
	
    $js_code = ct_add_hidden_fields(true, 'ct_checkjs', true, true);
    $js_code = strip_tags($js_code); // Removing <script> tag
    echo $js_code;
}

/**
* Public function - Test Formidable data for spam activity
* return @array with errors if spam has found
*/
function ct_frm_validate_entry ($errors, $values) {
    global $wpdb, $current_user, $ct_checkjs_frm, $ct_options, $ct_data;

    $ct_options = ct_get_options();
    $ct_data = ct_get_data();
    
    if ($ct_options['contact_forms_test'] == 0) {
        return $errors;
    }
    
    // Skip processing for logged in users.
    if ($ct_options['protect_logged_in'] != 1 && is_user_logged_in()) {
        return $errors;
    }
	
	$ct_temp_msg_data = ct_get_fields_any($values['item_meta']);
	
    $sender_email    = ($ct_temp_msg_data['email']    ? $ct_temp_msg_data['email']    : '');
    $sender_nickname = ($ct_temp_msg_data['nickname'] ? $ct_temp_msg_data['nickname'] : '');
    $subject         = ($ct_temp_msg_data['subject']  ? $ct_temp_msg_data['subject']  : '');
    $contact_form    = ($ct_temp_msg_data['contact']  ? $ct_temp_msg_data['contact']  : true);
    $message         = ($ct_temp_msg_data['message']  ? $ct_temp_msg_data['message']  : array());
	
	// Adding 'input_meta[]' to every field /Formidable fix/
	$message = array_flip($message);
	foreach($message as &$value){
		$value = 'item_meta['.$value.']';
	} unset($value);
	$message = array_flip($message);
	
    $checkjs = !apbct_js_test('ct_checkjs', $_COOKIE, true)
		? apbct_js_test('ct_checkjs', $_COOKIE, true)
		: apbct_js_test('ct_checkjs', $_POST, true);
    
    $base_call_result = apbct_base_call(
		array(
			'message'         => $message,
			'sender_email'    => $sender_email,
			'sender_nickname' => $sender_nickname,
			'post_info'       => array('comment_type' => 'feedback'),
			'checkjs'         => $checkjs
		)
	);
    $ct_result = $base_call_result['ct_result'];

    if ($ct_result->allow == 0) {
        $errors['ct_error'] = '<br /><b>' . $ct_result->comment . '</b><br /><br />';
    }

    return $errors;
}

/**
 * Public filter 'bbp_*' - Get new topic name to global $ct_bbp_topic
 * @param 	mixed[] $comment Comment string 
 * @return  mixed[] $comment Comment string 
 */
function ct_bbp_get_topic($topic){
	global $ct_bbp_topic;
	
	$ct_bbp_topic=$topic;
	
	return $topic;
}

/**
 * Public filter 'bbp_*' - Checks topics, replies by cleantalk
 * @param 	mixed[] $comment Comment string 
 * @return  mixed[] $comment Comment string 
 */
function ct_bbp_new_pre_content ($comment) {
    global $ct_options, $ct_data, $current_user, $ct_bbp_topic;
    
    $ct_options = ct_get_options();
    $ct_data = ct_get_data();

    if ($ct_options['comments_test'] == 0 ) {
        return $comment;
    }
		
    // Skip processing for logged in users and admin.
    if ($ct_options['protect_logged_in'] != 1 && is_user_logged_in() ||
		in_array("administrator", $current_user->roles))
        return $comment;
    
	$checkjs = !apbct_js_test('ct_checkjs', $_COOKIE, true)
		? apbct_js_test('ct_checkjs', $_COOKIE, true)
		: apbct_js_test('ct_checkjs', $_POST, true);
	
    $post_info['comment_type'] = 'bbpress_comment'; 
    $post_info['post_url'] = bbp_get_topic_permalink(); 
	
	if(isset($ct_bbp_topic))
		$message = $ct_bbp_topic." ".$comment;
	else
		$message = $comment;
	
    $base_call_result = apbct_base_call(
		array(
			'message'         => $comment,
			'sender_email'    => isset($_POST['bbp_anonymous_email']) ? $_POST['bbp_anonymous_email'] : null, 
			'sender_nickname' => isset($_POST['bbp_anonymous_name']) ? $_POST['bbp_anonymous_name'] : null, 
			'post_info'       => $post_info,
			'checkjs'         => $checkjs,
			'sender_info'     => array('sender_url' => isset($_POST['bbp_anonymous_website']) ? $_POST['bbp_anonymous_website'] : null),
		)
	);
    $ct_result = $base_call_result['ct_result'];

    if ($ct_result->allow == 0) {
        bbp_add_error('bbp_reply_content', $ct_result->comment);
    }

    return $comment;
}

/**
 * Public filter 'preprocess_comment' - Checks comment by cleantalk server
 * @param 	mixed[] $comment Comment data array
 * @return 	mixed[] New data array of comment
 */
function ct_preprocess_comment($comment) {
    // this action is called just when WP process POST request (adds new comment)
    // this action is called by wp-comments-post.php
    // after processing WP makes redirect to post page with comment's form by GET request (see above)
    global $wpdb, $current_user, $comment_post_id, $ct_comment_done, $ct_approved_request_id_label, $ct_jp_comments, $ct_options, $ct_data;
    
    $ct_options = ct_get_options();
    $ct_data = ct_get_data();

	// Skip processing admin.
    if (in_array("administrator", $current_user->roles))
        return $comment;
    
   	$comments_check_number = defined('CLEANTALK_CHECK_COMMENTS_NUMBER')  ? CLEANTALK_CHECK_COMMENTS_NUMBER              : 3;
   	$value                 = isset($ct_options['check_comments_number']) ? intval($ct_options['check_comments_number']) : 1;	
    
    if($value == 1){
	   	$args = array(
			'author_email' => $comment['comment_author_email'],
    		'status' => 'approve',
    		'count' => false,
    		'number' => $comments_check_number,
    	);
    	$cnt = count(get_comments($args));		
   		$is_max_comments = $cnt >= $comments_check_number ? true : false;
    }
	
    if (
		($comment['comment_type']!='trackback') &&
		(
			ct_is_user_enable() === false || 
			$ct_options['comments_test'] == 0 ||
			$ct_comment_done ||
			(isset($_SERVER['HTTP_REFERER']) && stripos($_SERVER['HTTP_REFERER'],'page=wysija_campaigns&action=editTemplate')!==false) || 
			(isset($is_max_comments) && $is_max_comments) ||
			strpos($_SERVER['REQUEST_URI'],'/wp-admin/')!==false)
	)
	{
        return $comment;
    }

    $local_blacklists = wp_blacklist_check(
        $comment['comment_author'],
        $comment['comment_author_email'], 
        $comment['comment_author_url'], 
        $comment['comment_content'], 
        @$_SERVER['REMOTE_ADDR'], 
        @$_SERVER['HTTP_USER_AGENT']
    );

    // Go out if author in local blacklists
    if ($comment['comment_type']!='trackback' && $local_blacklists === true) {
        return $comment;
    }

    // Skip pingback anti-spam test
    /*if ($comment['comment_type'] == 'pingback') {
        return $comment;
    }*/

    $ct_comment_done = true;

    $comment_post_id = $comment['comment_post_ID'];
	
    // JetPack comments logic
    $post_info['comment_type'] = $ct_jp_comments ? 'jetpack_comment' : $comment['comment_type'];
    $post_info['post_url'] = ct_post_url(null, $comment_post_id);
	
	// Comment type
	$post_info['comment_type'] = empty($post_info['comment_type']) ? 'general_comment' : $post_info['comment_type'];
	
	$checkjs = !apbct_js_test('ct_checkjs', $_COOKIE, true)
		? apbct_js_test('ct_checkjs', $_COOKIE, true)
		: apbct_js_test('ct_checkjs', $_POST, true);

    
    $example = null;
    if ($ct_options['relevance_test']) {
        $post = get_post($comment_post_id);
        if ($post !== null){
            $example['title'] = $post->post_title;
            $example['body'] = $post->post_content;
            $example['comments'] = null;

            $last_comments = get_comments(array('status' => 'approve', 'number' => 10, 'post_id' => $comment_post_id));
            foreach ($last_comments as $post_comment){
                $example['comments'] .= "\n\n" . $post_comment->comment_content;
            }

            $example = json_encode($example);
        }

        // Use plain string format if've failed with JSON
        if ($example === false || $example === null){
            $example = ($post->post_title !== null) ? $post->post_title : '';
            $example .= ($post->post_content !== null) ? "\n\n" . $post->post_content : '';
        }
    }

    $base_call_result = apbct_base_call(
		array(
			'message'         => $comment['comment_content'],
			'example'         => $example,
			'sender_email'    => $comment['comment_author_email'],
			'sender_nickname' => $comment['comment_author'],
			'post_info'       => $post_info,
			'checkjs'         => $checkjs,
			'sender_info'     => array('sender_url' => @$comment['comment_author_url']),
		)
	);
    $ct_result = $base_call_result['ct_result'];
		
	ct_hash($ct_result->id);
	
	//Don't check trusted users
	if (isset($comment['comment_author_email'])){
		$approved_comments = get_comments(array('status' => 'approve', 'count' => true, 'author_email' => $comment['comment_author_email']));
		$new_user = $approved_comments == 0 ? true : false;
	}

    // Change comment flow only for new authors
	if ($new_user || $ct_result->stop_words !== null || $ct_result->spam == 1)
		add_action('comment_post', 'ct_set_meta', 10, 2);	
	
	if($ct_result->allow){ // Pass if allowed
		if(get_option('comment_moderation') === '1') // Wordpress moderation flag
			add_filter('pre_comment_approved', 'ct_set_not_approved', 999, 2);
		else
			add_filter('pre_comment_approved', 'ct_set_approved', 999, 2);
	}else{
		
		global $ct_comment, $ct_stop_words;
		$ct_comment = $ct_result->comment;
		$ct_stop_words = $ct_result->stop_words;
		$err_text = '<center><b style="color: #49C73B;">Clean</b><b style="color: #349ebf;">Talk.</b> ' . __('Spam protection', 'cleantalk') . "</center><br><br>\n" . $ct_result->comment;
		$err_text .= '<script>setTimeout("history.back()", 5000);</script>';
		
		if($ct_result->stop_queue == 1) // Terminate. Definitely spam.
			wp_die($err_text, 'Blacklisted', array('back_link' => true));

		if($ct_result->spam == 3) // Don't move to spam folder. Delete.
			wp_die($err_text, 'Blacklisted', array('back_link' => true));
		
		if($ct_result->spam == 2){
			add_filter('pre_comment_approved', 'ct_set_comment_spam', 997, 2);
			add_action('comment_post', 'ct_wp_trash_comment', 997, 2);
		}
		
		if($ct_result->spam == 1)
			add_filter('pre_comment_approved', 'ct_set_comment_spam', 997, 2);
		
		if($ct_result->stop_words){ // Contains stop_words. Move to pending folder.
			add_filter('pre_comment_approved', 'ct_set_not_approved', 998, 2);
			add_action('comment_post', 'ct_mark_red', 998, 2);
		}
		
		add_action('comment_post', 'ct_die', 999, 2);
	}
		
	if(isset($ct_options['remove_comments_links']) && $ct_options['remove_comments_links'] == '1'){
		$comment = preg_replace("~(http|https|ftp|ftps)://(.*?)(\s|\n|[,.?!](\s|\n)|$)~", '[Link deleted]', $comment);
	}
		
    return $comment;
}

/**
 * Set die page with Cleantalk comment.
 * @global type $ct_comment
    $err_text = '<center><b style="color: #49C73B;">Clean</b><b style="color: #349ebf;">Talk.</b> ' . __('Spam protection', 'cleantalk') . "</center><br><br>\n" . $ct_comment;
 * @param type $comment_status
 */
function ct_die($comment_id, $comment_status) {
    global $ct_comment;
    $err_text = '<center><b style="color: #49C73B;">Clean</b><b style="color: #349ebf;">Talk.</b> ' . __('Spam protection', 'cleantalk') . "</center><br><br>\n" . $ct_comment;
        $err_text .= '<script>setTimeout("history.back()", 5000);</script>';
        if(isset($_POST['et_pb_contact_email']))
        {
        	$mes='<div id="et_pb_contact_form_1" class="et_pb_contact_form_container clearfix"><h1 class="et_pb_contact_main_title">Blacklisted</h1><div class="et-pb-contact-message"><p>'.$ct_comment.'</p></div></div>';
        	wp_die($mes, 'Blacklisted', array('back_link' => true,'response'=>200));
        }
        else
        {
        	wp_die($err_text, 'Blacklisted', array('back_link' => true));
        }
}

/**
 * Set die page with Cleantalk comment from parameter.
 * @param type $comment_body
 */
function ct_die_extended($comment_body) {
    $err_text = '<center><b style="color: #49C73B;">Clean</b><b style="color: #349ebf;">Talk.</b> ' . __('Spam protection', 'cleantalk') . "</center><br><br>\n" . $comment_body;
        $err_text .= '<script>setTimeout("history.back()", 5000);</script>';
        wp_die($err_text, 'Blacklisted', array('back_link' => true));
}

/**
 * Validates JavaScript anti-spam test
 *
 */
function apbct_js_test($field_name = 'ct_checkjs', $data = null, $random_key = false) {
    global $ct_options, $ct_data;
    
    $ct_options = ct_get_options();
    $ct_data = ct_get_data();

    $checkjs = null;
    $js_post_value = null;
    
    if (!$data)
        return $checkjs;

    if (isset($data[$field_name])) {
	    $js_post_value = $data[$field_name];

        //
        // Random key check
        //
        if ($random_key) {
            
            $keys = $ct_data['js_keys'];
            if (isset($keys[$js_post_value])) {
                $checkjs = 1;
            } else {
                $checkjs = 0; 
            }
        } else {
            $ct_challenge = ct_get_checkjs_value();
            
            if(preg_match("/$ct_challenge/", $js_post_value)) {
                $checkjs = 1;
            } else {
                $checkjs = 0; 
            }
        }
        

    }

    return $checkjs;
}

/**
 * Get post url 
 * @param int $comment_id 
 * @param int $comment_post_id
 * @return string|bool
 */
function ct_post_url($comment_id = null, $comment_post_id) {

    if (empty($comment_post_id))
	return null;

    if ($comment_id === null) {
	    $last_comment = get_comments('number=1');
	    $comment_id = isset($last_comment[0]->comment_ID) ? (int) $last_comment[0]->comment_ID + 1 : 1;
    }
    $permalink = get_permalink($comment_post_id);

    $post_url = null;
    if ($permalink !== null)
	$post_url = $permalink . '#comment-' . $comment_id;

    return $post_url;
}

/**
 * Public filter 'pre_comment_approved' - Mark comment unapproved always
 * @return 	int Zero
 */
function ct_set_not_approved() {
    return 0;
}

/**
 * @author Artem Leontiev
 * Public filter 'pre_comment_approved' - Mark comment approved if it's not 'spam' only
 * @return 	int 1
 */
function ct_set_approved($approved, $comment) {
    if ($approved == 'spam'){
        return $approved;
    } else {
        return 1;
    }
}

/**
 * Public filter 'pre_comment_approved' - Mark comment unapproved always
 * @return 	int Zero
 */
function ct_set_comment_spam() {
    return 'spam';
}

/**
 * Public action 'comment_post' - Store cleantalk hash in comment meta 'ct_hash'
 * @param	int $comment_id Comment ID
 * @param	mixed $comment_status Approval status ("spam", or 0/1), not used
 */
function ct_set_meta($comment_id, $comment_status) {
    global $comment_post_id;
    $hash1 = ct_hash();
    if (!empty($hash1)) {
        update_comment_meta($comment_id, 'ct_hash', $hash1);
        if (function_exists('base64_encode') && isset($comment_status) && $comment_status != 'spam') {
			$post_url = ct_post_url($comment_id, $comment_post_id);
			$post_url = base64_encode($post_url);
			if ($post_url === false)
			return false;
			// 01 - URL to approved comment
			$feedback_request = $hash1 . ':' . '01' . ':' . $post_url . ';';
			ct_send_feedback($feedback_request);
		}
    }
    return true;
}

/**
 * Mark bad words
 * @global string $ct_stop_words
 * @param int $comment_id
 * @param int $comment_status Not use
 */
function ct_mark_red($comment_id, $comment_status) {
    global $ct_stop_words;

    $comment = get_comment($comment_id, 'ARRAY_A');
    $message = $comment['comment_content'];
    foreach (explode(':', $ct_stop_words) as $word) {
        $message = preg_replace("/($word)/ui", '<font rel="cleantalk" color="#FF1000">' . "$1" . '</font>', $message);

    }
    $comment['comment_content'] = $message;
    kses_remove_filters();
    wp_update_comment($comment);
}

//
//Send post to trash
//
function ct_wp_trash_comment($comment_id, $comment_status){
	wp_trash_comment($comment_id);
}

/**
	* Tests plugin activation status
	* @return bool 
*/
function ct_plugin_active($plugin_name){
	foreach (get_option('active_plugins') as $k => $v) {
	    if ($plugin_name == $v)
		    return true;
	}
	return false;
}

/**
 * Insert a hidden field to registration form
 * @return null
 */
function ct_register_form() {
    global $ct_checkjs_register_form, $ct_options, $ct_data;
    
    $ct_options = ct_get_options();
    $ct_data = ct_get_data();

    if ($ct_options['registrations_test'] == 0) {
        return false;
    }

    ct_add_hidden_fields(true, $ct_checkjs_register_form, false);
	
    return null;
}

/**
 * Adds notification text to login form - to inform about approved registration
 * @return null
 */
function ct_login_message($message) {
		
    global $errors, $ct_options, $apbct_cookie_register_ok_label;
	
    $ct_options = ct_get_options();
	
    if ($ct_options['registrations_test'] != 0){
        if( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] ){
			if (isset($_COOKIE[$apbct_cookie_register_ok_label])){
				if(is_wp_error($errors)){
					$errors->add('ct_message',sprintf(__('Registration approved by %s.', 'cleantalk'), '<b style="color: #49C73B;">Clean</b><b style="color: #349ebf;">Talk</b>'), 'message');
				}
			}
        }
    }
    return $message;
}

/**
 * Test users registration for pPress
 * @return array with errors 
 */
function ct_registration_errors_ppress($reg_errors, $form_id) {
    
	$email = $_POST['reg_email'];
	$login = $_POST['reg_username'];
	
	$reg_errors = ct_registration_errors($reg_errors, $login, $email);
	        
    return $reg_errors;
}

/**
 * Test users registration for multisite enviroment
 * @return array with errors 
 */
function ct_registration_errors_wpmu($errors) {
    global $ct_signup_done;
    
    //
    // Multisite actions
    //
    $sanitized_user_login = null;
    if (isset($errors['user_name'])) {
        $sanitized_user_login = $errors['user_name']; 
        $wpmu = true;
    }
    $user_email = null;
    if (isset($errors['user_email'])) {
        $user_email = $errors['user_email'];
        $wpmu = true;
    }
    
    if ($wpmu && isset($errors['errors']->errors) && count($errors['errors']->errors) > 0) {
        return $errors;
    }
    
    $errors['errors'] = ct_registration_errors($errors['errors'], $sanitized_user_login, $user_email);

    // Show CleanTalk errors in user_name field
    if (isset($errors['errors']->errors['ct_error'])) {
        $errors['errors']->errors['user_name'] = $errors['errors']->errors['ct_error']; 
        unset($errors['errors']->errors['ct_error']);
     }
    
    return $errors;
}

/**
 *  Shell for action register_post 
 * @return array with errors 
 */
function ct_register_post($sanitized_user_login = null, $user_email = null, $errors) {
    return ct_registration_errors($errors, $sanitized_user_login, $user_email);
}

/**
 * Check messages for external plugins
 * @return array with checking result;
 */

function ct_test_message($nickname, $email, $ip, $text){
	
    $base_call_result = apbct_base_call(
		array(
			'message'         => $text,
			'sender_email'    => $email,
			'sender_nickname' => $nickname,
			'post_info'       => array('comment_type' => 'feedback_plugin_check'),
			'checkjs'         => apbct_js_test('ct_checkjs', $_COOKIE, true),
		)
	);
    
    $ct_result = $base_call_result['ct_result'];
    
    $result=Array(
        'allow' => $ct_result->allow,
        'comment' => $ct_result->comment,
    );
    return $result;
}

/**
 * Check registrations for external plugins
 * @return array with checking result;
 */
function ct_test_registration($nickname, $email, $ip){
    global $ct_checkjs_register_form, $ct_options;
    
    $ct_options = ct_get_options();
    
    $checkjs = apbct_js_test($ct_checkjs_register_form, $_POST, true);
    $sender_info['post_checkjs_passed'] = $checkjs;
	// This hack can be helpfull when plugin uses with untested themes&signups plugins.
    if (!$checkjs) {
        $checkjs = apbct_js_test('ct_checkjs', $_COOKIE, true);
        $sender_info['cookie_checkjs_passed'] = $checkjs;
    }
	
	//Making a call
	$base_call_result = apbct_base_call(
		array(
			'sender_ip'       => $ip,
			'sender_email'    => $email,
			'sender_nickname' => $nickname,
			'sender_info'     => $sender_info,
			'checkjs'         => $checkjs,
		),
		true
	);
	$ct_result = $base_call_result['ct_result'];
	
    $result=Array(
        'allow' => $ct_result->allow,
        'comment' => $ct_result->comment,
    );
    return $result;
}

/**
 * Test users registration
 * @return array with errors 
 */
function ct_registration_errors($errors, $sanitized_user_login = null, $user_email = null) {
    global $ct_checkjs_register_form, $apbct_cookie_request_id_label, $apbct_cookie_register_ok_label, $bp, $ct_signup_done, $ct_negative_comment, $ct_options, $ct_data, $ct_registration_error_comment;
    	
    $ct_options=ct_get_options();
	$ct_data=ct_get_data();
	
    // Go out if a registrered user action
    if (ct_is_user_enable() === false) {
        return $errors;
    }

    if ($ct_options['registrations_test'] == 0) {
        return $errors;
    }

    //
    // The function already executed
    // It happens when used ct_register_post(); 
    //
    if ($ct_signup_done && is_object($errors) && count($errors->errors) > 0) {
        return $errors;
    }

	// Facebook registration
    if ($sanitized_user_login === null && isset($_POST['FB_userdata'])){
        $sanitized_user_login = $_POST['FB_userdata']['name'];
        $facebook = true;
    }
    if ($user_email === null && isset($_POST['FB_userdata'])){
    	$user_email = $_POST['FB_userdata']['email'];
    	$facebook = true;
    }
	
    // BuddyPress actions
    $buddypress = false;
    if ($sanitized_user_login === null && isset($_POST['signup_username'])) {
        $sanitized_user_login = $_POST['signup_username'];
        $buddypress = true;
    }
    if ($user_email === null && isset($_POST['signup_email'])) {
        $user_email = $_POST['signup_email'];
        $buddypress = true;
    }
    
    //
    // Break tests because we already have servers response
    //
    if ($buddypress && $ct_signup_done) {
        if ($ct_negative_comment) {
            $bp->signup->errors['signup_username'] = $ct_negative_comment;
        }
        return $errors;
    }
	
    $checkjs = apbct_js_test($ct_checkjs_register_form, $_POST, true);
    $sender_info['post_checkjs_passed'] = $checkjs;
    // This hack can be helpfull when plugin uses with untested themes&signups plugins.
    if ($checkjs == 0) {
        $checkjs = apbct_js_test('ct_checkjs', $_COOKIE, true);
        $sender_info['cookie_checkjs_passed'] = $checkjs;
    }
    
	$base_call_result = apbct_base_call(
		array(
			'sender_email' => $user_email,
			'sender_nickname' => $sanitized_user_login,
			'sender_info' => $sender_info,
			'checkjs' => $checkjs,
		),
		true
	);
	$ct_result = $base_call_result['ct_result'];
	
    $ct_signup_done = true;

    $ct_result = ct_change_plugin_resonse($ct_result, $checkjs);
    
    if ($ct_result->inactive != 0) {
        ct_send_error_notice($ct_result->comment);
        return $errors;
    }

    if ($ct_result->allow == 0) {
		
        if ($buddypress === true) {
            $bp->signup->errors['signup_username'] = $ct_result->comment;
		}elseif(!empty($facebook)){
        	$_POST['FB_userdata']['email'] = '';
        	$_POST['FB_userdata']['name'] = '';
        	return;
        }else{
			if(is_wp_error($errors))
				$errors->add('ct_error', $ct_result->comment);
				$ct_negative_comment = $ct_result->comment;
        }
		
		$ct_registration_error_comment = $ct_result->comment;
		
    } else {
        if ($ct_result->id !== null) {
            setcookie($apbct_cookie_register_ok_label, $ct_result->id, time()+10, '/');
			setcookie($apbct_cookie_request_id_label,  $ct_result->id, time()+10, '/');
        }
    }
    
    return $errors;
}

/**
 * Checks registration error and set it if it was dropped
 * @return errors 
 */
function ct_check_registration_erros($errors, $sanitized_user_login = null, $user_email = null) {
	global $bp, $ct_registration_error_comment;
	
	if($ct_registration_error_comment){
		
		if(isset($bp))
			if(method_exists($bp, 'signup'))
				if(method_exists($bp->signup, 'errors'))
					if(isset($bp->signup->errors['signup_username']))
						if($bp->signup->errors['signup_username'] != $ct_registration_error_comment)
							$bp->signup->errors['signup_username'] = $ct_registration_error_comment;
							
		if(isset($errors))
			if(method_exists($errors, 'errors'))
				if(isset($errors->errors['ct_error']))
					if($errors->errors['ct_error'][0] != $ct_registration_error_comment)
						$errors->add('ct_error', $ct_registration_error_comment);
				
	}
	return $errors;
}

/**
 * Set user meta (ct_hash) for successed registration
 * @return null 
 */
function ct_user_register($user_id) {
    global $apbct_cookie_request_id_label;
    if (isset($_COOKIE[$apbct_cookie_request_id_label])) {
        if(update_user_meta($user_id, 'ct_hash', $_COOKIE[$apbct_cookie_request_id_label])){
			setcookie($apbct_cookie_request_id_label, '0', 1, '/');
		}
    }
}


/**
 * Test for JetPack contact form 
 */
function ct_grunion_contact_form_field_html($r, $field_label) {
    global $ct_checkjs_jpcf, $ct_jpcf_patched, $ct_jpcf_fields, $ct_options, $ct_data;
    
    $ct_options = ct_get_options();
    $ct_data = ct_get_data();

    if ($ct_options['contact_forms_test'] == 1 && $ct_jpcf_patched === false && preg_match("/[text|email]/i", $r)) {

        // Looking for element name prefix
        $name_patched = false;
        foreach ($ct_jpcf_fields as $v) {
            if ($name_patched === false && preg_match("/(g\d-)$v/", $r, $matches)) {
                $ct_checkjs_jpcf = $matches[1] . $ct_checkjs_jpcf;
                $name_patched = true;
            }
        }

        $r .= ct_add_hidden_fields(true, $ct_checkjs_jpcf, true);
        $ct_jpcf_patched = true;
    }

    return $r;
}
/**
 * Test for JetPack contact form 
 */
function ct_contact_form_is_spam($form) {
    global $ct_checkjs_jpcf, $ct_options, $ct_data;
    
    $ct_options = ct_get_options();
    $ct_data = ct_get_data();

    if ($ct_options['contact_forms_test'] == 0) {
        return null;
    }

    $js_field_name = $ct_checkjs_jpcf;
    foreach ($_POST as $k => $v) {
        if (preg_match("/^.+$ct_checkjs_jpcf$/", $k))
           $js_field_name = $k; 
    }
    
    $sender_email = null;
    $sender_nickname = null;
    $message = '';
    if (isset($form['comment_author_email']))
        $sender_email = $form['comment_author_email']; 

    if (isset($form['comment_author']))
        $sender_nickname = $form['comment_author']; 

    if (isset($form['comment_content']))
        $message = $form['comment_content']; 

    $base_call_result = apbct_base_call(
		array(
			'message'         => $message,
			'sender_email'    => $sender_email,
			'sender_nickname' => $sender_nickname,
			'post_info'       => array('comment_type' => 'feedback'),
			'sender_info'     => array('sender_url' => @$form['comment_author_url']),
			'checkjs'         => apbct_js_test($js_field_name, $_POST, true),
		)
	);
    $ct_result = $base_call_result['ct_result'];

    if ($ct_result->allow == 0) {
        global $ct_comment;
        $ct_comment = $ct_result->comment;
        ct_die(null, null);
        exit;
    }

    return (bool) !$ct_result->allow;
}

function ct_contact_form_is_spam_jetpack($is_spam,$form) {
    global $ct_checkjs_jpcf, $ct_options, $ct_data;
    
    $ct_options = ct_get_options();
    $ct_data = ct_get_data();

    if ($ct_options['contact_forms_test'] == 0) {
        return null;
    }

    $js_field_name = $ct_checkjs_jpcf;
    foreach ($_POST as $k => $v) {
        if (preg_match("/^.+$ct_checkjs_jpcf$/", $k))
           $js_field_name = $k; 
    }
    
    $sender_email = null;
    $sender_nickname = null;
    $message = '';
    if (isset($form['comment_author_email']))
        $sender_email = $form['comment_author_email']; 

    if (isset($form['comment_author']))
        $sender_nickname = $form['comment_author']; 

    if (isset($form['comment_content']))
        $message = $form['comment_content']; 

    $base_call_result = apbct_base_call(
		array(
			'message'         => $message,
			'sender_email'    => $sender_email,
			'sender_nickname' => $sender_nickname,
			'post_info'       => array('comment_type' => 'feedback'),
			'sender_info'     => array('sender_url' => @$form['comment_author_url']),
		)
	);
    $ct_result = $base_call_result['ct_result'];

    if ($ct_result->allow == 0) {
        global $ct_comment;
        $ct_comment = $ct_result->comment;
        ct_die(null, null);
        exit;
    }

    return (bool) !$ct_result->allow;
}



/**
 * Inserts anti-spam hidden to CF7
 */
function apbct_form__contactForm7__addField($html) {
    global $ct_checkjs_cf7, $ct_options;
    
    $ct_options = ct_get_options();

    if ($ct_options['contact_forms_test'] == 0) {
        return $html;
    }

    $html .= ct_add_hidden_fields(true, $ct_checkjs_cf7, true);
	
    return $html;
}

/**
 * Test CF7 message for spam
 */
function apbct_form__contactForm7__testSpam($param) {
    global $ct_checkjs_cf7, $ct_cf7_comment, $ct_options;
    
    $ct_options = ct_get_options();
	
	if(
		$ct_options['contact_forms_test'] == 0 ||
		$param == false && WPCF7_VERSION < '3.0.0'  ||
		$param === true && WPCF7_VERSION >= '3.0.0' ||
		$ct_options['protect_logged_in'] != 1 && is_user_logged_in() // Skip processing for logged in users.
	){
		return $param;
	}
 
	$checkjs = apbct_js_test($ct_checkjs_cf7, $_POST, true)
		? apbct_js_test($ct_checkjs_cf7, $_POST, true)
		: apbct_js_test('ct_checkjs', $_COOKIE, true);
	
	$ct_temp_msg_data = ct_get_fields_any($_POST);
	
    $sender_email    = ($ct_temp_msg_data['email']    ? $ct_temp_msg_data['email']    : '');
    $sender_nickname = ($ct_temp_msg_data['nickname'] ? $ct_temp_msg_data['nickname'] : '');
    $subject         = ($ct_temp_msg_data['subject']  ? $ct_temp_msg_data['subject']  : '');
    $contact_form    = ($ct_temp_msg_data['contact']  ? $ct_temp_msg_data['contact']  : true);
    $message         = ($ct_temp_msg_data['message']  ? $ct_temp_msg_data['message']  : array());
    if ($subject != '') {
        $message = array_merge(array('subject' => $subject), $message);
    }	
	
    $base_call_result = apbct_base_call(
		array(
			'message'         => $message,
			'sender_email'    => $sender_email,
			'sender_nickname' => $sender_nickname,
			'post_info'       => array('comment_type' => 'feedback'),
			'checkjs'         => $checkjs,
		)
	);
    $ct_result = $base_call_result['ct_result'];
   
    if ($ct_result->allow == 0) {
	
		if (WPCF7_VERSION >= '3.0.0')
			$param = true;
		else
			$param = false;
		
			$ct_cf7_comment = $ct_result->comment;
			add_filter('wpcf7_display_message', 'apbct_form__contactForm7__showResponse', 10, 2);
    }

    return $param;
}

/**
 * Changes CF7 status message 
 * @param 	string $hook URL of hooked page
 */
function apbct_form__contactForm7__showResponse($message, $status = 'spam') {
    global $ct_cf7_comment;

    if ($status == 'spam') {
        $message = $ct_cf7_comment; 
    }

    return $message;
}

/**
 * Inserts anti-spam hidden to Fast Secure contact form
 */
function ct_si_contact_display_after_fields($string = '', $style = '', $form_errors = array(), $form_id_num = 0) {
    $string .= ct_add_hidden_fields(true, 'ct_checkjs', true);
    return $string;
}

/**
 * Test for Fast Secure contact form
 */
function ct_si_contact_form_validate($form_errors = array(), $form_id_num = 0) {
    global $ct_options, $ct_data, $cleantalk_executed;
    
    $ct_options = ct_get_options();
    $ct_data = ct_get_data();

    if (!empty($form_errors))
		return $form_errors;

    if ($ct_options['contact_forms_test'] == 0)
		return $form_errors;

    // Skip processing because data already processed.
    if ($cleantalk_executed) {
	    return $form_errors;
    }
	
	//getting info from custom fields
	$ct_temp_msg_data = ct_get_fields_any($_POST);
	
    $sender_email    = ($ct_temp_msg_data['email']    ? $ct_temp_msg_data['email'] : '');
    $sender_nickname = ($ct_temp_msg_data['nickname'] ? $ct_temp_msg_data['nickname'] : '');
    $subject         = ($ct_temp_msg_data['subject']  ? $ct_temp_msg_data['subject'] : '');
    $contact_form    = ($ct_temp_msg_data['contact']  ? $ct_temp_msg_data['contact'] : true);
    $message         = ($ct_temp_msg_data['message']  ? $ct_temp_msg_data['message'] : array());
	if($subject != '') {
        $message['subject'] = $subject;
    }
	
    $base_call_result = apbct_base_call(
		array(
			'message'         => $message,
			'sender_email'    => $sender_email,
			'sender_nickname' => $sender_nickname,
			'post_info'       => array('comment_type' => 'feedback'),
			'checkjs'         => apbct_js_test('ct_checkjs', $_POST, true),
		)
	);
	
    $ct_result = $base_call_result['ct_result'];
    
    $cleantalk_executed = true;

    if ($ct_result->allow == 0) {
        global $ct_comment;
        $ct_comment = $ct_result->comment;
        ct_die(null, null);
        exit;
    }

    return $form_errors;
}

/**
 * Notice for commentators which comment has automatically approved by plugin 
 * @param 	string $hook URL of hooked page
 */
function ct_comment_text($comment_text) {
    global $comment, $ct_approved_request_id_label;

    if (isset($_COOKIE[$ct_approved_request_id_label]) && isset($comment->comment_ID)) {
        $ct_hash = get_comment_meta($comment->comment_ID, 'ct_hash', true);

        if ($ct_hash !== '' && $_COOKIE[$ct_approved_request_id_label] == $ct_hash) {
            $comment_text .= '<br /><br /> <em class="comment-awaiting-moderation">' . __('Comment approved. Anti-spam by CleanTalk.', 'cleantalk') . '</em>'; 
        }
    }

    return $comment_text;
}


/**
 * Checks WordPress Landing Pages raw $_POST values
*/
function ct_check_wplp(){
	
    global $ct_wplp_result_label, $ct_options, $ct_data;
    
    $ct_options = ct_get_options();
    $ct_data = ct_get_data();
	
    if (!isset($_COOKIE[$ct_wplp_result_label])) {
        // First AJAX submit of WPLP form
        if ($ct_options['contact_forms_test'] == 0)
                return;
		
        $post_info['comment_type'] = 'feedback';
        $post_info = json_encode($post_info);
        if ($post_info === false)
            $post_info = '';

        $sender_email = '';
        foreach ($_POST as $v) {
            if (preg_match("/^\S+@\S+\.\S+$/", $v)) {
                $sender_email = $v;
                break;
            }
        }

        $message = '';
        if(array_key_exists('form_input_values', $_POST)){
            $form_input_values = json_decode(stripslashes($_POST['form_input_values']), true);
            if (is_array($form_input_values) && array_key_exists('null', $form_input_values))
                $message = $form_input_values['null'];
        } else if (array_key_exists('null', $_POST)) {
            $message = $_POST['null'];
        }

        $base_call_result = apbct_base_call(
			array(
                'message'      => $message,
                'sender_email' => $sender_email,
                'post_info'    => array('comment_type' => 'feedback'),
			)
		);
		
        $ct_result = $base_call_result['ct_result'];

        if ($ct_result->allow == 0) {
            $cleantalk_comment = $ct_result->comment;
        } else {
            $cleantalk_comment = 'OK';
        }

        setcookie($ct_wplp_result_label, $cleantalk_comment, strtotime("+5 seconds"), '/');
    } else {
        // Next POST/AJAX submit(s) of same WPLP form
        $cleantalk_comment = $_COOKIE[$ct_wplp_result_label];
    }
    if ($cleantalk_comment !== 'OK')
        ct_die_extended($cleantalk_comment);
}

/**
 * Places a hidding field to Gravity forms.
 * @return string 
 */
function apbct_form__gravityForms__addField($form_string, $form){
    $ct_hidden_field = 'ct_checkjs';

    // Do not add a hidden field twice.
    if (preg_match("/$ct_hidden_field/", $form_string)) {
        return $form_string;
    }

    $search = "</form>";
	
	// Adding JS code
    $js_code = ct_add_hidden_fields(true, $ct_hidden_field, true, false);
    $form_string = str_replace($search, $js_code . $search, $form_string);
    
	// Adding field for multipage form. Look for cleantalk.php -> apbct_cookie();
	$append_string = isset($form['lastPageButton']) ? "<input type='hidden' name='ct_multipage_form' value='yes'>" : '';
	$form_string = str_replace($search, $append_string.$search, $form_string);
			
    return $form_string;
}

/**
 * Gravity forms anti-spam test.
 * @return boolean
 */
function apbct_form__gravityForms__testSpam($is_spam, $form, $entry) {
	
    global $ct_options, $ct_data, $cleantalk_executed, $ct_gform_is_spam, $ct_gform_response;
    
    $ct_options = ct_get_options();
    $ct_data = ct_get_data();

    if (
		$ct_options['contact_forms_test'] == 0 ||
		$is_spam ||
		$cleantalk_executed // Return unchanged result if the submission was already tested.
	)
	    return $is_spam;
    
	$ct_temp = array();
	foreach($entry as $key => $value){
		if(is_numeric($key))
			$ct_temp[$key]=$value;
	} unset($key, $value);
		
	$ct_temp_msg_data = ct_get_fields_any($ct_temp);
    $sender_email    = ($ct_temp_msg_data['email']    ? $ct_temp_msg_data['email']    : '');
    $sender_nickname = ($ct_temp_msg_data['nickname'] ? $ct_temp_msg_data['nickname'] : '');
    $subject         = ($ct_temp_msg_data['subject']  ? $ct_temp_msg_data['subject']  : '');
    $contact_form    = ($ct_temp_msg_data['contact']  ? $ct_temp_msg_data['contact']  : true);
    $message         = ($ct_temp_msg_data['message']  ? $ct_temp_msg_data['message']  : array());
	
	// Adding 'input_' to every field /Gravity Forms fix/
	$message = array_flip($message);
	foreach($message as &$value){
		$value = 'input_'.$value;
	} unset($value);
	$message = array_flip($message);
	
    if($subject != '')
        $message['subject'] = $subject;
	
	$checkjs = apbct_js_test('ct_checkjs', $_POST, true)
		? apbct_js_test('ct_checkjs', $_POST, true)
		: apbct_js_test('ct_checkjs', $_COOKIE, true);
	
    $base_call_result = apbct_base_call(
		array(
			'message'         => $message,
			'sender_email'    => $sender_email,
			'sender_nickname' => $sender_nickname,
			'post_info'       => array('comment_type' => 'feedback_gravity'),
			'checkjs'         => $checkjs,
		)
	);
	
    $ct_result = $base_call_result['ct_result'];
    if ($ct_result->allow == 0) {
        $is_spam = true;
		$ct_gform_is_spam = true;
		$ct_gform_response = $ct_result->comment;
    }

    return $is_spam;
}

function apbct_form__gravityForms__showResponse( $confirmation, $form, $entry, $ajax ){
	
	global $ct_gform_is_spam, $ct_gform_response;
	
	if(!empty($ct_gform_is_spam)){
		$confirmation = "<div id='gform_confirmation_wrapper_2' class='gform_confirmation_wrapper '><div id='gform_confirmation_message_2' class='gform_confirmation_message_2 gform_confirmation_message'><font style='color: red'>$ct_gform_response</font></div></div>";
	}
	
	return $confirmation;
}

/**
 * Test S2member registration
 * @return array with errors 
 */
function ct_s2member_registration_test() {
    global $ct_post_data_label, $ct_post_data_authnet_label, $ct_options;
    
    $ct_options = ct_get_options();
    
    if ($ct_options['registrations_test'] == 0) {
        return null;
    }
        
    $sender_email = null;
    if (isset($_POST[$ct_post_data_label]['email']))
        $sender_email = $_POST[$ct_post_data_label]['email'];
    if (isset($_POST[$ct_post_data_authnet_label]['email']))
        $sender_email = $_POST[$ct_post_data_authnet_label]['email'];

    $sender_nickname = null;
    if (isset($_POST[$ct_post_data_label]['username']))
        $sender_nickname = $_POST[$ct_post_data_label]['username'];
    if (isset($_POST[$ct_post_data_authnet_label]['username']))
        $sender_nickname = $_POST[$ct_post_data_authnet_label]['username'];

	//Making a call
	$base_call_result = apbct_base_call(
		array(
			'sender_email'    => sanitize_email($_POST['email']),
			'sender_nickname' => sanitize_email($_POST['login']),
			'sender_info'     => $sender_info,
			'checkjs'         => $checkjs,
		),
		true
	);
	$ct_result = $base_call_result['ct_result'];
   	    
    if ($ct_result->allow == 0) {
        ct_die_extended($ct_result->comment);
    }

    return true;
}

/**
 * General test for any contact form
 */
function ct_contact_form_validate() {
	global $pagenow,$cleantalk_executed, $cleantalk_url_exclusions,$ct_options, $ct_data, $ct_checkjs_frm;

    $ct_options = ct_get_options();
    $ct_data = ct_get_data();
	
	if($cleantalk_executed)
		return null;
	
    if (@sizeof($_POST)==0 ||
    	(isset($_POST['signup_username']) && isset($_POST['signup_email']) && isset($_POST['signup_password'])) ||
        (isset($pagenow) && $pagenow == 'wp-login.php') || // WordPress log in form
        (isset($pagenow) && $pagenow == 'wp-login.php' && isset($_GET['action']) && $_GET['action']=='lostpassword') ||
		(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],'lostpassword') !== false) ||
        (strpos($_SERVER['REQUEST_URI'],'/wp-admin/')!== false && (empty($_POST['your-phone']) && empty($_POST['your-email']) && empty($_POST['your-message']))) || //Bitrix24 Contact
        strpos($_SERVER['REQUEST_URI'],'wp-login.php')!==false||
        strpos($_SERVER['REQUEST_URI'],'wp-comments-post.php')!==false ||
		strpos($_SERVER['REQUEST_URI'],'?provider=facebook&')!==false ||
        (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],'/wp-admin/') !== false) ||
        strpos($_SERVER['REQUEST_URI'],'/login/')!==false||
        strpos($_SERVER['REQUEST_URI'], '/peepsoajax/profilefieldsajax.validate_register')!== false ||
        isset($_GET['ptype']) && $_GET['ptype']=='login' ||
        check_url_exclusions() ||
		check_ip_exclusions() ||
        ct_check_array_keys($_POST) ||
        isset($_POST['ct_checkjs_register_form']) ||
        (isset($_POST['signup_username']) && isset($_POST['signup_password_confirm']) && isset($_POST['signup_submit']) ) ||
        @intval($ct_options['general_contact_forms_test'])==0 ||
        isset($_POST['bbp_topic_content']) ||
        isset($_POST['bbp_reply_content']) ||
        isset($_POST['fscf_submitted']) ||
        strpos($_SERVER['REQUEST_URI'],'/wc-api/')!==false ||
        isset($_POST['log']) && isset($_POST['pwd']) && isset($_POST['wp-submit']) ||
        isset($_POST[$ct_checkjs_frm]) && (@intval($ct_options['contact_forms_test']) == 1) ||// Formidable forms
        isset($_POST['comment_post_ID']) || // The comment form 
        isset($_GET['for']) ||
		(isset($_POST['log'], $_POST['pwd'])) || //WooCommerce Sensei login form fix
        (isset($_POST['_wpcf7'], $_POST['_wpcf7_version'], $_POST['_wpcf7_locale'])) || //CF7 fix) 
		(isset($_POST['hash'], $_POST['device_unique_id'], $_POST['device_name'])) ||//Mobile Assistant Connector fix
		isset($_POST['gform_submit']) || //Gravity form
		(isset($_POST['wc_reset_password'], $_POST['_wpnonce'], $_POST['_wp_http_referer'])) || //WooCommerce recovery password form
		(isset($_POST['woocommerce-login-nonce'], $_POST['login'], $_POST['password'], $_POST['_wp_http_referer'])) || //WooCommerce login form
		(isset($_POST['ccf_form']) && intval($_POST['ccf_form']) == 1) ||
		(isset($_POST['contact_tags']) && strpos($_POST['contact_tags'], 'MBR:') !== false) ||
		(strpos($_SERVER['REQUEST_URI'], 'bizuno.php') && !empty($_POST['bizPass'])) ||
		(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],'my-dashboard/') !== false) // ticket_id=7885
		) {
        return null;
    }

    // Do not execute anti-spam test for logged in users.
    if (isset($_COOKIE[LOGGED_IN_COOKIE]) && $ct_options['protect_logged_in'] != 1)
        return null;
	  
    $post_info['comment_type'] = 'feedback_general_contact_form';
	
	// Skip the test if it's WooCommerce and the checkout test unset
	if(strpos($_SERVER['REQUEST_URI'], 'wc-ajax=checkout') !== false || 
	   strpos($_SERVER['REQUEST_URI'], 'wc-ajax=update_order_review') !== false ||
	   (isset($_POST['_wp_http_referer']) && strpos($_SERVER['REQUEST_URI'], 'wc-ajax=update_order_review') !== false))
	{
		$post_info['comment_type'] = 'order';
		if($ct_options['wc_checkout_test'] == 0){
			return null;
		}
	}
	
	$ct_temp_msg_data = ct_get_fields_any($_POST);
	
    $sender_email    = ($ct_temp_msg_data['email']    ? $ct_temp_msg_data['email']    : '');
    $sender_nickname = ($ct_temp_msg_data['nickname'] ? $ct_temp_msg_data['nickname'] : '');
    $subject         = ($ct_temp_msg_data['subject']  ? $ct_temp_msg_data['subject']  : '');
    $contact_form    = ($ct_temp_msg_data['contact']  ? $ct_temp_msg_data['contact']  : true);
    $message         = ($ct_temp_msg_data['message']  ? $ct_temp_msg_data['message']  : array());
    if ($subject != '') {
        $message = array_merge(array('subject' => $subject), $message);
    }
	
    // Skip submission if no data found
    if ($sender_email === ''|| !$contact_form) {
        return false;
    }
    $cleantalk_executed=true;
    
    if(isset($_POST['TellAFriend_Link'])){
    	$tmp = $_POST['TellAFriend_Link'];
    	unset($_POST['TellAFriend_Link']);
    }
	
    $base_call_result = apbct_base_call(
		array(
			'message'         => $message,
			'sender_email'    => $sender_email,
			'sender_nickname' => $sender_nickname,
			'post_info'       => $post_info,
		)
	);
	
    if(isset($_POST['TellAFriend_Link'])){
    	$_POST['TellAFriend_Link']=$tmp;
    }
	
    $ct_result = $base_call_result['ct_result'];
    if ($ct_result->allow == 0) {
		
		// Recognize contact form an set it's name to $contact_form to use later
		$contact_form = null;
		foreach($_POST as $param => $value){
			if(strpos($param, 'et_pb_contactform_submit') === 0){
				$contact_form = 'divi_theme_contact_form';
				$contact_form_additional = str_replace('et_pb_contactform_submit', '', $param);
			}
			if(strpos($param, 'avia_generated_form') === 0){
				$contact_form = 'enfold_theme_contact_form';
				$contact_form_additional = str_replace('avia_generated_form', '', $param);
			}
			if(!empty($contact_form))
				break;
		}
        
        $ajax_call = false;
        if ((defined( 'DOING_AJAX' ) && DOING_AJAX) 
            ) {
            $ajax_call = true;
		}
        if ($ajax_call) {
            echo $ct_result->comment;
        } else {
			
            global $ct_comment;
            $ct_comment = $ct_result->comment;
            if(isset($_POST['cma-action'])&&$_POST['cma-action']=='add'){
            	$result=Array('success'=>0, 'thread_id'=>null,'messages'=>Array($ct_result->comment));
            	header("Content-Type: application/json");
				print json_encode($result);
				die();
				
            }else if(isset($_POST['TellAFriend_email'])){
            	echo $ct_result->comment;
            	die();
				
            }else if(isset($_POST['gform_submit'])){ // Gravity forms submission
                $response = sprintf("<!DOCTYPE html><html><head><meta charset='UTF-8' /></head><body class='GF_AJAX_POSTBACK'><div id='gform_confirmation_wrapper_1' class='gform_confirmation_wrapper '><div id='gform_confirmation_message_1' class='gform_confirmation_message_1
 gform_confirmation_message'>%s</div></div></body></html>",
                    $ct_result->comment
                );
                echo $response;
            	die();
				
            }elseif(isset($_POST['_wp_http_referer']) && strpos($_POST['_wp_http_referer'],'wc-ajax=update_order_review')){ //WooCommerce checkout ("Place Oreder button")
				$result = Array(
					result => 'failure',
					messages => "<ul class=\"woocommerce-error\"><li>".$ct_result->comment."</li></ul>",
					refresh => 'false',
					reload => 'false'
				);
				print json_encode($result);
				die();
				
			}elseif(isset($_POST['action']) && $_POST['action'] == 'ct_check_internal'){	
                return $ct_result->comment;
				
            }elseif(isset($_POST['vfb-submit']) && defined('VFB_VERSION')){
				wp_die("<h1>".__('Spam protection by CleanTalk', 'cleantalk')."</h1><h2>".$ct_result->comment."</h2>", '', array('response' => 403, "back_link" => true, "text_direction" => 'ltr'));
            // Caldera Contact Forms
			}elseif(isset($_POST['action']) && $_POST['action'] == 'cf_process_ajax_submit'){	
				print json_encode("<h3 style='color: red;'><red>".$ct_result->comment);
				die();
			// Mailster
			}elseif(isset($_POST['_referer'], $_POST['formid'], $_POST['email'])){	
				$return = array(
					'success' => false,
					'html' => '<p>' .  $ct_result->comment  . '</p>',
				);
				print json_encode($return);
				die();
			// Divi Theme Contact Form. Using $contact_form
			}elseif(!empty($contact_form) && $contact_form == 'divi_theme_contact_form'){
				echo "<div id='et_pb_contact_form{$contact_form_additional}'><h1>Your request looks like spam.</h1><div><p>{$ct_result->comment}</p></div></div>";
				die();
			// Enfold Theme Contact Form. Using $contact_form
			}elseif(!empty($contact_form) && $contact_form == 'enfold_theme_contact_form'){
				echo "<div id='ajaxresponse_1' class='ajaxresponse ajaxresponse_1' style='display: block;'><div id='ajaxresponse_1' class='ajaxresponse ajaxresponse_1'><h3 class='avia-form-success'>Antispam by CleanTalk: ".$ct_result->comment."</h3><a href='.'><-Back</a></div></div>";
				die();
            }else{
				ct_die(null, null);
			}
        }
        exit;
    }
	
    return null;
}

/**
 * General test for any post data
 */
function ct_contact_form_validate_postdata() {
	global $pagenow,$cleantalk_executed, $cleantalk_url_exclusions, $ct_options, $ct_data;

    $ct_options = ct_get_options();
    $ct_data = ct_get_data();
    
	if($cleantalk_executed)
		return null;
	
	if ((defined( 'DOING_AJAX' ) && DOING_AJAX))
		return null;
	
    if (@sizeof($_POST)==0 ||
    	(isset($_POST['signup_username']) && isset($_POST['signup_email']) && isset($_POST['signup_password'])) ||
        (isset($pagenow) && $pagenow == 'wp-login.php') || // WordPress log in form
        (isset($pagenow) && $pagenow == 'wp-login.php' && isset($_GET['action']) && $_GET['action']=='lostpassword') ||
        strpos($_SERVER['REQUEST_URI'],'/checkout/')!==false ||
        strpos($_SERVER['REQUEST_URI'],'/wp-admin/')!==false ||
        strpos($_SERVER['REQUEST_URI'],'wp-login.php')!==false||
        strpos($_SERVER['REQUEST_URI'],'wp-comments-post.php')!==false ||
        @strpos($_SERVER['HTTP_REFERER'],'/wp-admin/')!==false ||
        strpos($_SERVER['REQUEST_URI'],'/login/')!==false||
		strpos($_SERVER['REQUEST_URI'],'?provider=facebook&')!==false ||
        isset($_GET['ptype']) && $_GET['ptype']=='login' ||
        check_url_exclusions() ||
		check_ip_exclusions() ||
        ct_check_array_keys($_POST) ||
        isset($_POST['ct_checkjs_register_form']) ||
        (isset($_POST['signup_username']) && isset($_POST['signup_password_confirm']) && isset($_POST['signup_submit']) ) ||
        @intval($ct_options['general_contact_forms_test'])==0 ||
        isset($_POST['bbp_topic_content']) ||
        isset($_POST['bbp_reply_content']) ||
        isset($_POST['fscf_submitted']) ||
        isset($_POST['log']) && isset($_POST['pwd']) && isset($_POST['wp-submit'])||
        strpos($_SERVER['REQUEST_URI'],'/wc-api/')!==false ||
		(isset($_POST['wc_reset_password'], $_POST['_wpnonce'], $_POST['_wp_http_referer'])) || //WooCommerce recovery password form
		(isset($_POST['woocommerce-login-nonce'], $_POST['login'], $_POST['password'], $_POST['_wp_http_referer'])) //WooCommerce login form
        ) {
        return null;
    }
    	
    $message = ct_get_fields_any_postdata($_POST);
	
	// ???
    if(strlen(json_encode($message))<10)
       	return null;
    
	// Skip if request contains params
    $skip_params = array(
	    'ipn_track_id',   // PayPal IPN #
	    'txn_type',       // PayPal transaction type
	    'payment_status', // PayPal payment status
    );
    foreach($skip_params as $key=>$value){
   		if(@array_key_exists($value,$_GET)||@array_key_exists($value,$_POST))
   			return null;
   	}
    
    $base_call_result = apbct_base_call(
		array(
			'message'         => $message,
			'post_info'       => array('comment_type' => 'feedback_general_postdata'),
		)
	);
    
    $cleantalk_executed=true;
    
    $ct_result = $base_call_result['ct_result'];
       
    if ($ct_result->allow == 0) {
        
        if (!(defined( 'DOING_AJAX' ) && DOING_AJAX)) {
            global $ct_comment;
            $ct_comment = $ct_result->comment;
            if(isset($_POST['cma-action'])&&$_POST['cma-action']=='add')
            {
            	$result=Array('success'=>0, 'thread_id'=>null,'messages'=>Array($ct_result->comment));
            	header("Content-Type: application/json");
				print json_encode($result);
				die();
            }
            else
            {
            	ct_die(null, null);
            }
        } else {
            echo $ct_result->comment; 
        }
        exit;
    }

    return null;
}


/**
 * Inner function - Finds and returns pattern in string
 * @return null|bool
 */
function ct_get_data_from_submit($value = null, $field_name = null) {
    if (!$value || !$field_name || !is_string($value)) {
        return false;
    }
    if (preg_match("/[a-z0-9_\-]*" . $field_name. "[a-z0-9_\-]*$/", $value)) {
        return true;
    }
}

/**
 * Sends error notice to admin
 * @return null
 */
function ct_send_error_notice ($comment = '') {
    global $ct_plugin_name, $ct_admin_notoice_period;

    $timelabel_reg = intval( get_option('cleantalk_timelabel_reg') );
    if(time() - $ct_admin_notoice_period > $timelabel_reg){
        update_option('cleantalk_timelabel_reg', time());

        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $message  = __('Attention, please!', 'cleantalk') . "\r\n\r\n";
        $message .= sprintf(__('"%s" plugin error on your site %s:', 'cleantalk'), $ct_plugin_name, $blogname) . "\r\n\r\n";
        $message .= $comment . "\r\n\r\n";
        @wp_mail(ct_get_admin_email(), sprintf(__('[%s] %s error!', 'cleantalk'), $ct_plugin_name, $blogname), $message);
    }

    return null;
}

function ct_print_form($arr,$k)
{
	foreach($arr as $key=>$value)
	{
		if(!is_array($value))
		{
			if($k=='')
			{
				print '<textarea name="'.$key.'" style="display:none;">'.htmlspecialchars($value).'</textarea>';
			}
			else
			{
				print '<textarea name="'.$k.'['.$key.']" style="display:none;">'.htmlspecialchars($value).'</textarea>';
			}
		}
		else
		{
			if($k=='')
			{
				ct_print_form($value,$key);
			}
			else
			{
				ct_print_form($value,$k.'['.$key.']');
			}
		}
	}
}

/**
 * Attaches public scripts and styles.
 */
function ct_enqueue_scripts_public($hook){

	global $current_user, $ct_data, $ct_options;
	
	$ct_options = ct_get_options();
	$ct_data = ct_get_data();
	
	if(!empty($ct_options['registrations_test']) || !empty($ct_options['comments_test']) || !empty($ct_options['contact_forms_test']) || !empty($ct_options['general_contact_forms_test']) || !empty($ct_options['wc_checkout_test']) || !empty($ct_options['check_external']) || !empty($ct_options['check_internal']) || !empty($ct_options['bp_private_messages']) || !empty($ct_options['general_postdata_test']))
		wp_enqueue_script('ct_public',  plugins_url('/cleantalk-spam-protect/js/apbct-public.js'),                        array(),          APBCT_VERSION, 'in_footer');
	
	if(!defined('CLEANTALK_AJAX_USE_FOOTER_HEADER') || (defined('CLEANTALK_AJAX_USE_FOOTER_HEADER') && CLEANTALK_AJAX_USE_FOOTER_HEADER)){
		if(!empty($ct_options['use_ajax']) && stripos($_SERVER['REQUEST_URI'],'.xml') === false && stripos($_SERVER['REQUEST_URI'],'.xsl') === false){
			if(strpos($_SERVER['REQUEST_URI'],'jm-ajax') === false){
				
				if(!empty($ct_options['use_ajax']))
					wp_enqueue_script('ct_nocache',  plugins_url('/cleantalk-spam-protect/inc/cleantalk_nocache.js'),  array(),         APBCT_VERSION, 'in_footer');
				
				if(!empty($ct_options['check_external']))
					wp_enqueue_script('ct_external',  plugins_url('/cleantalk-spam-protect/js/cleantalk_external.js'), array('jquery'), APBCT_VERSION, 'in_footer');
				
				if(!empty($ct_options['check_internal']))
					wp_enqueue_script('ct_internal',  plugins_url('/cleantalk-spam-protect/js/cleantalk_internal.js'), array('jquery'), APBCT_VERSION, 'in_footer');
				
				wp_localize_script('ct_nocache', 'ctNocache', array(
					'ajaxurl'                  => admin_url('admin-ajax.php'),
					'info_flag'                => !empty($ct_options['collect_details']) && !empty($ct_options['set_cookies']) ? true : false,
					'set_cookies_flag'         => empty($ct_options['set_cookies']) ? false : true,
					'blog_home'                => get_home_url().'/',
				));
				
			}
		}
	}
	
	if(in_array("administrator", $current_user->roles)){
		
		if(!empty($ct_options['show_check_links'])){
		
			$ajax_nonce = wp_create_nonce( "ct_secret_nonce" );
			$user_token = !empty($ct_data['user_token']) ? $ct_data['user_token'] : null;
			
			wp_enqueue_style ('ct_public_admin_css', plugins_url('/cleantalk-spam-protect/css/cleantalk-public-admin.css'), array(),         APBCT_VERSION, 'all');
			wp_enqueue_script('ct_public_admin_js',  plugins_url('/cleantalk-spam-protect/js/cleantalk-public-admin.js'),   array('jquery'), APBCT_VERSION, true);
			
			wp_localize_script('ct_public_admin_js', 'ctPublic', array(
				'ct_ajax_nonce'               => $ajax_nonce,
				'ajaxurl'                     => admin_url('admin-ajax.php'),
				'ct_feedback_error'           => __('Error occured while sending feedback.', 'cleantalk'),
				'ct_feedback_no_hash'         => __('Feedback wasn\'t sent. There is no associated request.', 'cleantalk'),
				'ct_feedback_msg'             => sprintf(__("Feedback has been sent to %sCleanTalk Dashboard%s.", 'cleantalk'), $user_token ? "<a target='_blank' href=https://cleantalk.org/my/show_requests?user_token={$user_token}&cp_mode=antispam>" : '', $user_token ? "</a>" : ''),
			));
			
		}
	}
	
	if(!empty($ct_options['debug_ajax'])){
		wp_enqueue_script('ct_debug_js',  plugins_url('/cleantalk-spam-protect/js/cleantalk-debug-ajax.js'), array('jquery'), APBCT_VERSION, true);
		
		wp_localize_script('ct_debug_js', 'apbctDebug', array(
			'reload'                  => false,
			'reload_time'             => 10000,
		));
	}
}

function apbct_add_async_attribute($tag, $handle, $src) {
	
	global $ct_options;
	
	$ct_options = ct_get_options();
	
    if(
		!empty($ct_options['async_js']) &&
		(
			   $handle === 'ct_public'
			|| $handle === 'ct_debug_js'
			|| $handle === 'ct_public_admin_js'
			|| $handle === 'ct_internal'
			|| $handle === 'ct_external'
			|| $handle === 'ct_nocache'
		)
	)
		return str_replace( ' src', ' async="async" src', $tag );
	else
		return $tag;
}

/**
 * Reassign callbackback function for the bootom of comment output.
 */
function ct_wp_list_comments_args($options){
	
	global $current_user, $ct_options;
	
	if(in_array("administrator", $current_user->roles))
		if(!empty($ct_options['show_check_links']))
			$options['end-callback'] = 'ct_comments_output';
	
	return $options;
}

/**
 * Callback function for the bootom comment output.
 */
function ct_comments_output($curr_comment, $param2, $wp_list_comments_args){
	
	$email   = $curr_comment->comment_author_email;
	$ip      = $curr_comment->comment_author_IP;
	$id      = $curr_comment->comment_ID;
	
	$settings_link = '/wp-admin/'.(is_network_admin() ? "settings.php?page=cleantalk" : "options-general.php?page=cleantalk");
	
	echo "<div class='ct_comment_info'>";
		
		echo "<p class='ct_comment_info_title'>".__('Sender info', 'cleantalk')."</p>";
		
		echo "<p class='ct_comment_logo_title'>
				".__('by', 'cleantalk')
				." <a href='{$settings_link}' target='_blank'><img class='ct_comment_logo_img' src='".plugins_url()."/cleantalk-spam-protect/inc/images/logo_color.png'></a>"
				." <a href='{$settings_link}' target='_blank'>CleanTalk</a>"
			."</p>";
		
		// Outputs email if exists
		if($email)
			echo "<a href='https://cleantalk.org/blacklists/$email' target='_blank' title='https://cleantalk.org/blacklists/$email'>"
			."$email"
				."&nbsp;<img src='".plugins_url()."/cleantalk-spam-protect/inc/images/new_window.gif' border='0' style='float:none; box-shadow: transparent 0 0 0 !important;'/>"
			."</a>";
		else
			echo __('No email', 'cleantalk');
		echo "&nbsp;|&nbsp;";
		
		// Outputs IP if exists
		if($ip)
			echo "<a href='https://cleantalk.org/blacklists/$ip' target='_blank' title='https://cleantalk.org/blacklists/$ip'>"
			."$ip"
				."&nbsp;<img src='".plugins_url()."/cleantalk-spam-protect/inc/images/new_window.gif' border='0' style='float:none; box-shadow: transparent 0 0 0 !important;'/>"
			."</a>";
		else
			echo __('No IP', 'cleantalk');
		echo '&nbsp;|&nbsp;';
		
		echo "<span commentid='$id' class='ct_this_is ct_this_is_spam' href='#'>".__('Mark as spam', 'cleantalk')."</span>";
		echo "<span commentid='$id' class='ct_this_is ct_this_is_not_spam ct_hidden' href='#'>".__('Unspam', 'cleantalk')."</span>";
		echo "<p class='ct_feedback_wrap'>";
			echo "<span class='ct_feedback_result ct_feedback_result_spam'>".__('Marked as spam.', 'cleantalk')."</span>";
			echo "<span class='ct_feedback_result ct_feedback_result_not_spam'>".__('Marked as not spam.', 'cleantalk')."</span>";
			echo "&nbsp;<span class='ct_feedback_msg'><span>";
		echo "</p>";
			
	echo "</div>";
	
	// Ending comment output
	echo "</{$wp_list_comments_args['style']}>";
}
?>