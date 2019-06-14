<?php

$ct_checkjs_frm = 'ct_checkjs_frm';
$ct_checkjs_register_form = 'ct_checkjs_register_form';

$apbct_cookie_request_id_label  = 'request_id';
$apbct_cookie_register_ok_label = 'register_ok';

$ct_checkjs_cf7 = 'ct_checkjs_cf7';
$ct_cf7_comment = '';

$ct_checkjs_jpcf = 'ct_checkjs_jpcf';
$ct_jpcf_patched = false; 
$ct_jpcf_fields = array('name', 'email');

// Comment already proccessed
$ct_comment_done = false;

// Comment already proccessed
$ct_signup_done = false;

//Contains registration error
$ct_registration_error_comment = false;

// Default value for JS test
$ct_checkjs_def = 0;

// COOKIE label to store request id for last approved  
$ct_approved_request_id_label = 'ct_approved_request_id';

// Last request id approved for publication 
$ct_approved_request_id = null;

// Trial notice show time in minutes
$trial_notice_showtime = 10;

// Renew notice show time in minutes
$renew_notice_showtime = 10;

// COOKIE label for WP Landing Page proccessing result
$ct_wplp_result_label = 'ct_wplp_result';

// Flag indicates active JetPack comments 
$ct_jp_comments = false;

// WP admin email notice interval in seconds
$ct_admin_notoice_period = 21600;

// Sevice negative comment to visitor.
// It uses for BuddyPress registrations to avoid double checks
$ct_negative_comment = null;

// Set globals to NULL to avoid massive DB requests. Globals will be set when needed only and by accessors only.
$ct_server = NULL;
$admin_email = NULL;

/**
 * Public action 'plugins_loaded' - Loads locale, see http://codex.wordpress.org/Function_Reference/load_plugin_textdomain
 */
function apbct_plugin_loaded() {
	$dir=plugin_basename( dirname( __FILE__ ) ) . '/../i18n';
    $loaded=load_plugin_textdomain('cleantalk', false, $dir);
}

/**
 * Inner function - Request's wrapper for anything
 * @param array Array of parameters:
 *  'message' - string
 *  'example' - string
 *  'checkjs' - int
 *  'sender_email' - string
 *  'sender_nickname' - string
 *  'sender_info' - array
 *  'post_info' - string
 * @return array array('ct'=> Cleantalk, 'ct_result' => CleantalkResponse)
 */
function apbct_base_call($params = array(), $reg_flag = false){
	
	global $apbct;
	
    $sender_info = !empty($params['sender_info'])
		? CleantalkHelper::array_merge__save_numeric_keys__recursive(apbct_get_sender_info(), (array) $params['sender_info'])
		: apbct_get_sender_info();
	
    !empty($params['message'])
		? $params['message'] = ct_filter_array($params['message'])
		: null;
	
	$default_params = array(
		
		// IPs
		'sender_ip'       => defined('CT_TEST_IP') ? CT_TEST_IP : (isset($params['sender_ip']) ? $params['sender_ip'] : CleantalkHelper::ip__get(array('real'), false)),
		'x_forwarded_for' => CleantalkHelper::ip__get(array('x_forwarded_for'), false),
		'x_real_ip'       => CleantalkHelper::ip__get(array('x_real_ip'), false),
		
		// Misc
		'auth_key'        => $apbct->api_key,
		'js_on'           => apbct_js_test('ct_checkjs', $_COOKIE, true) ? 1 : apbct_js_test('ct_checkjs', $_POST, true),
		
		'agent'           => CLEANTALK_AGENT,
		'sender_info'     => $sender_info,
		'submit_time'     => apbct_get_submit_time(),
	);
	
	$ct_request = new CleantalkRequest(
		CleantalkHelper::array_merge__save_numeric_keys__recursive($default_params, $params)
	);
	
	$ct = new Cleantalk();

	$ct->use_bultin_api = $apbct->settings['use_buitin_http_api'] ? true : false;
	$ct->ssl_on         = $apbct->settings['ssl_on'];
	$ct->ssl_path       = APBCT_CASERT_PATH;
	
	// Options store url without shceme because of DB error with ''://'
	$config = ct_get_server();
	$ct->server_url     = CLEANTALK_MODERATE_URL;
	$ct->work_url       = preg_match('/http:\/\/.+/', $config['ct_work_url']) ? $config['ct_work_url'] : null;
	$ct->server_ttl     = $config['ct_server_ttl'];
	$ct->server_changed = $config['ct_server_changed'];

	$start = microtime(true);
	$ct_result = $reg_flag
		? @$ct->isAllowUser($ct_request)
		: @$ct->isAllowMessage($ct_request);
	$exec_time = microtime(true) - $start;

	// Statistics
	// Average request time
	apbct_statistics__rotate($exec_time);
	// Last request
	$apbct->stats['last_request']['time'] = time();
	$apbct->stats['last_request']['server'] = $ct->work_url;
	$apbct->save('stats');

	// Connection reports
	if ($ct_result->errno === 0 && empty($ct_result->errstr))
        $apbct->data['connection_reports']['success']++;
    else
    {
        $apbct->data['connection_reports']['negative']++;
        $apbct->data['connection_reports']['negative_report'][] = array(
			'date' => date("Y-m-d H:i:s"),
			'page_url' => $_SERVER['REQUEST_URI'],
			'lib_report' => $ct_result->errstr,
			'work_url' => $ct->work_url,
		);

		if(count($apbct->data['connection_reports']['negative_report']) > 20)
			$apbct->data['connection_reports']['negative_report'] = array_slice($apbct->data['connection_reports']['negative_report'], -20, 20);

    }

    if ($ct->server_change) {
		update_option(
			'cleantalk_server',
			array(
				'ct_work_url'       => $ct->work_url,
				'ct_server_ttl'     => $ct->server_ttl,
				'ct_server_changed' => time(),
			)
		);
    }

    $ct_result = ct_change_plugin_resonse($ct_result, $ct_request->js_on);

	// Restart submit form counter for failed requests
    if ($ct_result->allow == 0){
		apbct_cookie(); // Setting page timer and cookies
       	ct_add_event('no');
    }else{
       	ct_add_event('yes');
    }
	
	// Set cookies if it's not.
	if(empty($apbct->flags__cookies_setuped))
		apbct_cookie();
	
    return array('ct' => $ct, 'ct_result' => $ct_result);
	
}

/**
 * Inner function - Default data array for senders 
 * @return array 
 */
function apbct_get_sender_info() {
	
	global $apbct;
	
	// Validate cookie from the backend
	$cookie_is_ok = apbct_cookies_test();
    
	$referer_previous = $apbct->settings['set_cookies__sessions']
			? apbct_alt_session__get('apbct_prev_referer')
			: filter_input(INPUT_COOKIE, 'apbct_prev_referer');
	
	$site_landing_ts = $apbct->settings['set_cookies__sessions']
			? apbct_alt_session__get('apbct_site_landing_ts')
			: filter_input(INPUT_COOKIE, 'apbct_site_landing_ts');
	
	$page_hits = $apbct->settings['set_cookies__sessions']
			? apbct_alt_session__get('apbct_page_hits')
			: filter_input(INPUT_COOKIE, 'apbct_page_hits');
		
	if (count($_POST) > 0) {
		foreach ($_POST as $k => $v) {
			if (preg_match("/^(ct_check|checkjs).+/", $k)) {
        		$checkjs_data_post = $v; 
			}
		}
	}
	
	// AMP check
	$amp_detected = isset($_SERVER['HTTP_REFERER'])
		? strpos($_SERVER['HTTP_REFERER'], '/amp/') !== false || strpos($_SERVER['HTTP_REFERER'], '?amp=1') !== false || strpos($_SERVER['HTTP_REFERER'], '&amp=1') !== false
			? 1
			: 0
		: null;
	
	$site_referer = $apbct->settings['store_urls__sessions']
			? apbct_alt_session__get('apbct_site_referer')
			: filter_input(INPUT_COOKIE, 'apbct_site_referer');
	
	$urls = $apbct->settings['store_urls__sessions']
			? (array)apbct_alt_session__get('apbct_urls')
			: (array)json_decode(filter_input(INPUT_COOKIE, 'apbct_urls'), true);
	
	return array(
		'remote_addr'            => CleantalkHelper::ip__get(array('remote_addr'), false),
        'REFFERRER'              => isset($_SERVER['HTTP_REFERER'])                                ? htmlspecialchars($_SERVER['HTTP_REFERER'])                        : null,
        'USER_AGENT'             => isset($_SERVER['HTTP_USER_AGENT'])                             ? htmlspecialchars($_SERVER['HTTP_USER_AGENT'])                     : null,
		'page_url'               => isset($_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI'])        ? htmlspecialchars($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']) : null,
        'cms_lang'               => substr(get_locale(), 0, 2),
        'ct_options'             => json_encode($apbct->settings),
        'fields_number'          => sizeof($_POST),
        'direct_post'            => $cookie_is_ok === null && $_SERVER['REQUEST_METHOD'] == 'POST' ? 1                                                                 : 0,
		// Raw data to validated JavaScript test in the cloud                                                                                                          
        'checkjs_data_cookies'   => !empty($_COOKIE['ct_checkjs'])                                 ? $_COOKIE['ct_checkjs']                                            : null, 
        'checkjs_data_post'      => !empty($checkjs_data_post)                                     ? $checkjs_data_post                                                : null, 
		// PHP cookies                                                                                                                                                 
        'cookies_enabled'        => $cookie_is_ok,                                                                                                                     
		'REFFERRER_PREVIOUS'     => !empty($referer_previous) && $cookie_is_ok                     ? $referer_previous                                                 : null,
		'site_landing_ts'        => !empty($site_landing_ts) && $cookie_is_ok                      ? $site_landing_ts                                                  : null,
		'page_hits'              => !empty($page_hits)                                             ? $page_hits                                                        : null,
		// JS cookies                                                                                                                                                  
        'js_info'                => !empty($_COOKIE['ct_user_info'])                               ? json_decode(stripslashes($_COOKIE['ct_user_info']), true)         : null,
		'mouse_cursor_positions' => !empty($_COOKIE['ct_pointer_data'])                            ? json_decode(stripslashes($_COOKIE['ct_pointer_data']), true)      : null,
		'js_timezone'            => !empty($_COOKIE['ct_timezone'])                                ? $_COOKIE['ct_timezone']                                           : null,
		'key_press_timestamp'    => !empty($_COOKIE['ct_fkp_timestamp'])                           ? $_COOKIE['ct_fkp_timestamp']                                      : null,
		'page_set_timestamp'     => !empty($_COOKIE['ct_ps_timestamp'])                            ? $_COOKIE['ct_ps_timestamp']                                       : null,
		'form_visible_inputs'    => !empty($_COOKIE['apbct_visible_fields_count'])                 ? $_COOKIE['apbct_visible_fields_count']                            : null,
		'apbct_visible_fields'   => !empty($_COOKIE['apbct_visible_fields'])                       ? apbct_visibile_fields__process($_COOKIE['apbct_visible_fields'])  : null,
		// Misc
		'site_referer'           => !empty($site_referer)                                          ? $site_referer                                                     : null,
		'source_url'             => !empty($urls)                                                  ? json_encode($urls)                                                : null,
		// Debug stuff
		'amp_detected'           => $amp_detected,
		'hook'                   => current_action(),
		'headers_sent'           => !empty($apbct->headers_sent)        ? $apbct->headers_sent        : false,
		'headers_sent__hook'     => !empty($apbct->headers_sent__hook)  ? $apbct->headers_sent__hook  : false,
		'headers_sent__hook'     => !empty($apbct->headers_sent__where) ? $apbct->headers_sent__where : false,
		'request_type'           => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'UNKNOWN',
	);
}

/**
 * Process visible fields for specific form to match the fields from request
 * 
 * @param string $visible_fields
 * 
 * @return string
 */
function apbct_visibile_fields__process($visible_fields) {
    if(strpos($visible_fields, 'wpforms') !== false){
		$visible_fields = preg_replace(
			array('/\[/', '/\]/'),
			'',
			str_replace(
				'][',
				'_',
				str_replace(
					'wpforms[fields]',
					'',
					$visible_fields
				)
			)
		);
	}
	
	return $visible_fields;
}

/*
 * Outputs JS key for AJAX-use only. Stops script.
 */
function apbct_js_keys__get__ajax($direct_call = false){
	if(!$direct_call){
		if(isset($_POST['_ajax_nonce'])){
			if(!wp_verify_nonce($_POST['_ajax_nonce'], 'ct_secret_stuff')){
				wp_doing_ajax()
					? wp_die( -1, 403 )
					: die( '-1' );
			}
		}else{
			wp_doing_ajax()
				? wp_die( -1, 403 )
				: die( '-1' );
		}
	}
	die(json_encode(array(
		'js_key' => ct_get_checkjs_value((bool)$_POST['random_key'])
	)));
}

/**
 * Get ct_get_checkjs_value
 *
 * @param bool $random_key
 *
 * @return int|string|null
 */
function ct_get_checkjs_value($random_key = false) {
	
    global $apbct;
	
    if ($random_key) {
        $keys = $apbct->data['js_keys'];
        $keys_checksum = md5(json_encode($keys));
        
        $key = null;
        $latest_key_time = 0;
        foreach ($keys as $k => $t) {

            // Removing key if it's to old
            if (time() - $t > $apbct->data['js_keys_store_days'] * 86400) {
                unset($keys[$k]);
                continue;
            }

            if ($t > $latest_key_time) {
                $latest_key_time = $t;
                $key = $k;
            }
        }
        
        // Get new key if the latest key is too old
        if (time() - $latest_key_time > $apbct->data['js_key_lifetime']) {
            $key = rand();
            $keys[$key] = time();
        }
        
        if (md5(json_encode($keys)) != $keys_checksum) {
            $apbct->data['js_keys'] = $keys;
            $apbct->saveData();
        }
    } else {
        $key = md5($apbct->api_key . '+' . ct_get_admin_email());
    }

    return $key; 
}

/**
 * Inner function - Current site admin e-mail
 * @return 	string Admin e-mail
 */
function ct_get_admin_email() {
	global $admin_email;
	if(!isset($admin_email))
	{
	    $admin_email = get_option('admin_email');
	}
	return $admin_email;
}

/**
 * Inner function - Current Cleantalk working server info
 * @return 	mixed[] Array of server data
 */
function ct_get_server($force=false) {
	global $ct_server;
	if(!$force && isset($ct_server) && isset($ct_server['ct_work_url']) && !empty($ct_server['ct_work_url'])){
		
		return $ct_server;
		
	}else{
		
	    $ct_server = get_option('cleantalk_server');
	    if (!is_array($ct_server)){
	        $ct_server = array(
				'ct_work_url' => NULL,
				'ct_server_ttl' => NULL,
				'ct_server_changed' => NULL
			);
	    }
	    return $ct_server;
	}
}

/**
 * Inner function - Stores ang returns cleantalk hash of current comment
 * @param	string New hash or NULL
 * @return 	string New hash or current hash depending on parameter
 */
function ct_hash($new_hash = '') {
    /**
     * Current hash
     */
    static $hash;

    if (!empty($new_hash)) {
        $hash = $new_hash;
    }
    return $hash;
}

/**
 * Inner function - Write manual moderation results to PHP sessions 
 * @param 	string $hash Cleantalk comment hash
 * @param 	string $message comment_content
 * @param 	int $allow flag good comment (1) or bad (0)
 * @return 	string comment_content w\o cleantalk resume
 */
function ct_feedback($hash, $allow) {
	global $apbct;
	
    $ct_feedback = $hash . ':' . $allow . ';';
    if($apbct->data['feedback_request'])
		$apbct->data['feedback_request'] = $ct_feedback; 
    else
		$apbct->data['feedback_request'] .= $ct_feedback; 
	
	$apbct->saveData();
}

/**
 * Inner function - Sends the results of moderation
 * Scheduled in 3600 seconds!
 * @param string $feedback_request
 * @return bool
 */
function ct_send_feedback($feedback_request = null) {
	
	global $apbct;
	
    if (empty($feedback_request) && isset($apbct->data['feedback_request']) && preg_match("/^[a-z0-9\;\:]+$/", $apbct->data['feedback_request'])){
		$feedback_request = $apbct->data['feedback_request'];
		$apbct->data['feedback_request'] = '';
		$apbct->saveData();
    }
	
    if ($feedback_request !== null) {
				
        $ct_request = new CleantalkRequest(array(
			// General
			'auth_key' => $apbct->api_key,
			// Additional
			'feedback' => $feedback_request,
		));
		
        $ct = new Cleantalk();
		
		// Server URL handling
		$config = ct_get_server();
		$ct->server_url     = CLEANTALK_MODERATE_URL;
		$ct->work_url       = preg_match('/http:\/\/.+/', $config['ct_work_url']) ? $config['ct_work_url'] : null;
		$ct->server_ttl     = $config['ct_server_ttl'];
		$ct->server_changed = $config['ct_server_changed'];
				
        $ct->sendFeedback($ct_request);
		
        if ($ct->server_change) {
            update_option(
                'cleantalk_server',
				array(
					'ct_work_url'       => $ct->work_url,
					'ct_server_ttl'     => $ct->server_ttl,
					'ct_server_changed' => time(),
                )
            );
        }
		
        return true;
    }

    return false;
}

/**
 * Delete old spam comments 
 * Scheduled in 3600 seconds!
 * @return null 
 */
function ct_delete_spam_comments() {
	
    global $apbct;
    
    if ($apbct->settings['remove_old_spam'] == 1) {
        $last_comments = get_comments(array('status' => 'spam', 'number' => 1000, 'order' => 'ASC'));
        foreach ($last_comments as $c) {
            if (time() - strtotime($c->comment_date_gmt) > 86400 * $apbct->settings['spam_store_days']) {
                // Force deletion old spam comments
                wp_delete_comment($c->comment_ID, true);
            } 
        }
    }

    return null; 
}

/*
* Get data from an ARRAY recursively
* @return array
*/ 
function ct_get_fields_any($arr, $message=array(), $email = null, $nickname = array('nick' => '', 'first' => '', 'last' => ''), $subject = null, $contact = true, $prev_name = ''){
	
	//Skip request if fields exists
	$skip_params = array(
	    'ipn_track_id', 	// PayPal IPN #
	    'txn_type', 		// PayPal transaction type
	    'payment_status', 	// PayPal payment status
	    'ccbill_ipn', 		// CCBill IPN 
		'ct_checkjs', 		// skip ct_checkjs field
		'api_mode',         // DigiStore-API
		'loadLastCommentId' // Plugin: WP Discuz. ticket_id=5571
    );
	
	// Fields to replace with ****
    $obfuscate_params = array(
        'password',
        'pass',
        'pwd',
		'pswd'
    );
	
	// Skip feilds with these strings and known service fields
	$skip_fields_with_strings = array( 
		// Common
		'ct_checkjs', //Do not send ct_checkjs
		'nonce', //nonce for strings such as 'rsvp_nonce_name'
		'security',
		// 'action',
		'http_referer',
		'referer-page',
		'timestamp',
		'captcha',
		// Formidable Form
		'form_key',
		'submit_entry',
		// Custom Contact Forms
		'form_id',
		'ccf_form',
		'form_page',
		// Qu Forms
		'iphorm_uid',
		'form_url',
		'post_id',
		'iphorm_ajax',
		'iphorm_id',
		// Fast SecureContact Froms
		'fs_postonce_1',
		'fscf_submitted',
		'mailto_id',
		'si_contact_action',
		// Ninja Forms
		'formData_id',
		'formData_settings',
		'formData_fields_\d+_id',
		'formData_fields_\d+_files.*',		
		// E_signature
		'recipient_signature',
		'output_\d+_\w{0,2}',
		// Contact Form by Web-Settler protection
        '_formId',
        '_returnLink',
		// Social login and more
		'_save',
		'_facebook',
		'_social',
		'user_login-',
		// Contact Form 7
		'_wpcf7',
		'ebd_settings',
		'ebd_downloads_',
		'ecole_origine',
	);
	
	// Reset $message if we have a sign-up data
    $skip_message_post = array(
        'edd_action', // Easy Digital Downloads
    );
	
   	foreach($skip_params as $value){
   		if(@array_key_exists($value,$_GET)||@array_key_exists($value,$_POST))
   			$contact = false;
   	} unset($value);
	
	if(count($arr)){
		
		foreach($arr as $key => $value){
						
			if(gettype($value) == 'string'){
				
				$tmp = strpos($value, '\\') !== false ? stripslashes($value) : $value;
				$decoded_json_value = json_decode($tmp, true);
				
				// Decoding JSON
				if($decoded_json_value !== null){
					$value = $decoded_json_value;
					
				// Ajax Contact Forms. Get data from such strings:
					// acfw30_name %% Blocked~acfw30_email %% s@cleantalk.org
					// acfw30_textarea %% msg
				}elseif(preg_match('/^\S+\s%%\s\S+.+$/', $value)){
					$value = explode('~', $value);
					foreach ($value as &$val){
						$tmp = explode(' %% ', $val);
						$val = array($tmp[0] => $tmp[1]);
					}
				}
			}
						
			if(!is_array($value) && !is_object($value) && @get_class($value) != 'WP_User'){
				
				if (in_array($key, $skip_params, true) && $key != 0 && $key != '' || preg_match("/^ct_checkjs/", $key))
					$contact = false;
				
				if($value === '')
					continue;
				
				// Skipping fields names with strings from (array)skip_fields_with_strings
				foreach($skip_fields_with_strings as $needle){
					if (preg_match("/".$needle."/", $prev_name.$key) == 1){
						continue(2);
					}
				}unset($needle);
				
				// Obfuscating params
				foreach($obfuscate_params as $needle){
					if (strpos($key, $needle) !== false){
						$value = ct_obfuscate_param($value);
						continue(2);
					}
				}unset($needle);
				
				// Removes shortcodes to do better spam filtration on server side.
				$value = strip_shortcodes($value);

				// Decodes URL-encoded data to string.
				$value = urldecode($value);	

				// Email
				if (!$email && preg_match("/^\S+@\S+\.\S+$/", $value)){
					$email = $value;
					
				// Names
				}elseif (preg_match("/name/i", $key)){
					
					preg_match("/((name.?)?(your|first|for)(.?name)?)$/", $key, $match_forename);
					preg_match("/((name.?)?(last|family|second|sur)(.?name)?)$/", $key, $match_surname);
					preg_match("/^(name.?)?(nick|user)(.?name)?$/", $key, $match_nickname);
					
					if(count($match_forename) > 1)
						$nickname['first'] = $value;
					elseif(count($match_surname) > 1)
						$nickname['last'] = $value;
					elseif(count($match_nickname) > 1)
						$nickname['nick'] = $value;
					else
						$message[$prev_name.$key] = $value;
				
				// Subject
				}elseif ($subject === null && preg_match("/subject/i", $key)){
					$subject = $value;
				
				// Message
				}else{
					$message[$prev_name.$key] = $value;					
				}
				
			}elseif(!is_object($value) && @get_class($value) != 'WP_User'){
				
				$prev_name_original = $prev_name;
				$prev_name = ($prev_name === '' ? $key.'_' : $prev_name.$key.'_');
				
				$temp = ct_get_fields_any($value, $message, $email, $nickname, $subject, $contact, $prev_name);
				
				$message 	= $temp['message'];
				$email 		= ($temp['email'] 		? $temp['email'] : null);
				$nickname 	= ($temp['nickname'] 	? $temp['nickname'] : null);				
				$subject 	= ($temp['subject'] 	? $temp['subject'] : null);
				if($contact === true)
					$contact = ($temp['contact'] === false ? false : true);
				$prev_name 	= $prev_name_original;
			}
		} unset($key, $value);
	}
	
    foreach ($skip_message_post as $v) {
        if (isset($_POST[$v])) {
            $message = null;
            break;
        }
    } unset($v);
	
	//If top iteration, returns compiled name field. Example: "Nickname Firtsname Lastname".
	if($prev_name === ''){
		if(!empty($nickname)){
			$nickname_str = '';
			foreach($nickname as $value){
				$nickname_str .= ($value ? $value." " : "");
			}unset($value);
		}
		$nickname = $nickname_str;
	}
	
    $return_param = array(
		'email' 	=> $email,
		'nickname' 	=> $nickname,
		'subject' 	=> $subject,
		'contact' 	=> $contact,
		'message' 	=> $message
	);	
	return $return_param;
}

/**
* Masks a value with asterisks (*)
* @return string
*/
function ct_obfuscate_param($value = null) {
    if ($value && (!is_object($value) || !is_array($value))) {
        $length = strlen($value);
        $value = str_repeat('*', $length);
    }

    return $value;
}

//New ct_get_fields_any_postdata
function ct_get_fields_any_postdata($arr, $message=array()){
	$skip_params = array(
	    'ipn_track_id', // PayPal IPN #
	    'txn_type', // PayPal transaction type
	    'payment_status', // PayPal payment status
    );
		
	foreach($arr as $key => $value){
		if(!is_array($value)){
			if($value == '')
				continue;
			if (!(in_array($key, $skip_params) || preg_match("/^ct_checkjs/", $key)) && $value!='')
	        	$message[$key] = $value;
		}else{
			$temp = ct_get_fields_any_postdata($value);
			$message = (count($temp) == 0 ? $message : array_merge($message, $temp));
		}
	}
	return $message;
}

/*
* Check if Array has keys with restricted names
*/

$ct_check_post_result=false;

function ct_check_array_keys_loop($key){
	
	global $ct_check_post_result;
	
	$strict = Array('members_search_submit');
	
	for($i=0;$i<sizeof($strict);$i++){
		
		if(stripos($key,$strict[$i])!== false)
			$ct_check_post_result = true;
		
	}
}

function ct_check_array_keys($arr){
	
	global $ct_check_post_result;
	
	if(!is_array($arr))
		return $ct_check_post_result;
	
	foreach($arr as $key=>$value){
		
		if(!is_array($value))
			ct_check_array_keys_loop($key);
		else
			ct_check_array_keys($value);
		
	}
	
	return $ct_check_post_result;
}

function check_url_exclusions($exclusions = NULL){
	
	global $cleantalk_url_exclusions;
	
	if ((isset($cleantalk_url_exclusions) && is_array($cleantalk_url_exclusions) && sizeof($cleantalk_url_exclusions)>0) ||
		($exclusions !== NULL && is_array($exclusions) && sizeof($exclusions)>0)
	){
		
		// Fix for AJAX forms
		$haystack = $_SERVER['REQUEST_URI'] == '/wp-admin/admin-ajax.php' && !empty($_SERVER['HTTP_REFERER'])
			? $_SERVER['HTTP_REFERER']
			: $_SERVER['REQUEST_URI'];
		
		foreach($cleantalk_url_exclusions as $value){
			if(stripos($haystack, $value) !== false){
				return true;
			}
		}
	}
	
	return false;
}

function check_ip_exclusions($exclusions = NULL){
	
	global $cleantalk_ip_exclusions;
	
	if ((isset($cleantalk_ip_exclusions) && is_array($cleantalk_ip_exclusions) && sizeof($cleantalk_ip_exclusions)>0) ||
		($exclusions !== NULL && is_array($exclusions) && sizeof($exclusions)>0)
	){
		foreach($cleantalk_ip_exclusions as $key => $value){
			if(stripos($_SERVER['REMOTE_ADDR'], $value) !== false){
				return true; 
			}
		}
	}
	
	return false;
}

function ct_filter_array(&$data)
{
	global $cleantalk_key_exclusions;
	
	if(isset($cleantalk_key_exclusions) && sizeof($cleantalk_key_exclusions) > 0 && is_array($data)){
		
		foreach($data as $key => $value){
			
			if(!is_array($value)){
				if(in_array($key,$cleantalk_key_exclusions)){
					unset($data[$key]);
				}
			}else{
				$data[$key] = ct_filter_array($value);
			}
		}
		
		return $data;
		
	}else{
		return $data;
	}
}


function cleantalk_debug($key,$value)
{
	if(isset($_COOKIE) && isset($_COOKIE['cleantalk_debug']))
	{
		@header($key.": ".$value);
	}
}

/**
* Function changes CleanTalk result object if an error occured.
* @return object
*/ 
function ct_change_plugin_resonse($ct_result = null, $checkjs = null) {
	
	global $apbct;
	
    if (!$ct_result) {
        return $ct_result;
    }
    
    if(@intval($ct_result->errno) != 0)
    {
    	if($checkjs === null || $checkjs != 1)
    	{
    		$ct_result->allow = 0;
    		$ct_result->spam = 1;
    		$ct_result->comment = sprintf('We\'ve got an issue: %s. Forbidden. Please, enable Javascript. %s.',
                $ct_result->comment,
                $apbct->plugin_name
            );
    	}
    	else
    	{
    		$ct_result->allow = 1;
    		$ct_result->comment = 'Allow';
    	}
    }

    return $ct_result;
}

/**
* Does key has correct symbols? Checks against regexp ^[a-z\d]{3,15}$
* @param api_key
* @return bool
*/
function apbct_api_key__is_correct($api_key = null)
{
	global $apbct;
	$api_key = $api_key !== null ? $api_key : $apbct->api_key;
    return $api_key && preg_match('/^[a-z\d]{3,15}$/', $api_key) ? true : false;
}

function apbct_add_async_attribute($tag, $handle, $src) {
	
	global $apbct;
	
    if(
		$apbct->settings['async_js'] &&
		(
			   $handle === 'ct_public'
			|| $handle === 'ct_public_gdpr'
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