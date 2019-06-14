<?php

/**
 * Admin action 'admin_menu' - Add the admin options page
 */
function apbct_settings__add_page() {
	
	global $apbct, $pagenow, $_wp_last_object_menu;
	
	/*
	if($apbct->white_label){
		// Top menu
		add_menu_page($apbct->plugin_name, $apbct->plugin_name, 'activate_plugins', 'apbct_menu', '',
		// 'dashicons-cf-logo1'
		APBCT_URL_PATH . '/inc/images/logo_small.png' // Menu icon
		, '65.64');
		// Submenus
		// add_submenu_page('apbct_menu', __('Summary', 'cleantalk'),       __('Summary', 'cleantalk'),       'activate_plugins', 'apbct_menu',           'function');
		// add_submenu_page('apbct_menu', __('Anti-Spam log', 'cleantalk'), __('Anti-Spam log', 'cleantalk'), 'activate_plugins', 'apbct_menu__log',      'function');
		add_submenu_page('apbct_menu', __('Settings', 'cleantalk'),      __('Settings', 'cleantalk'),      'activate_plugins', 'apbct_menu', 'apbct_settings_page');		
	}else{
	*/
	// Adding settings page
	if(is_network_admin() && !$apbct->white_label)
		add_submenu_page("settings.php", $apbct->plugin_name.' '.__('settings'), $apbct->plugin_name, 'manage_options', 'cleantalk', 'apbct_settings_page');
	else
		add_options_page($apbct->plugin_name.' '.__('settings'), $apbct->plugin_name, 'manage_options', 'cleantalk', 'apbct_settings_page');
//	}
	
	if(!in_array($pagenow, array('options.php', 'options-general.php', 'settings.php', 'admin.php')))
		return;
	
	register_setting('cleantalk_settings', 'cleantalk_settings', 'apbct_settings__validate');
		
	// add_settings_section('cleantalk_section_settings_main',  '',                                     'apbct_section__settings_main',  'cleantalk');
		
	$field_default_params = array(
		'callback'    => 'apbct_settings__field__draw',
		'type'        => 'radio',
		'def_class'   => 'apbct_settings-field_wrapper',
		'class'       => '',
		'parent'      => '',
		'childrens'   => '',
		'title'       => 'Default title',
		'description' => 'Default description',
		'display'     => true, // Draw settings or not
	);
	
	$apbct->settings_fields_in_groups = array(
		
		'main' => array(
			'title'          => '',	
			'default_params' => array(),
			'description'    => '',
			'html_before'    => '',
			'html_after'     => '',
			'fields'         => array(
				'action_buttons' => array(
					'callback'    => 'apbct_settings__field__action_buttons',
				),
				'api_key' => array(
					'callback'    => 'apbct_settings__field__api_key',
				),
				'connection_reports' => array(
					'callback'    => 'apbct_settings__field__statistics',
				),
			),
		),
		
		'state' => array(
			'title'          => '',	
			'default_params' => array(),
			'description'    => '',
			'html_before'    => '<hr style="width: 100%;">',
			'html_after'     => '',
			'fields'         => array(
				'state' => array(
					'callback'    => 'apbct_settings__field__state',
				),
			),
		),
		
		'debug' => array(
			'title'          => '',	
			'default_params' => array(),
			'description'    => '',
			'html_before'    => '',
			'html_after'     => '',
			'fields'         => array(
				'state' => array(
					'callback'    => 'apbct_settings__field__debug',
				),
			),
		),
		
		// Different
		'different' => array(
			'title'          => '',	
			'default_params' => array(),
			'description'    => '',
			'html_before'    => '<hr>',
			'html_after'     => '',
			'fields'         => array(
				'spam_firewall' => array(
					'type'        => 'checkbox',
					'title'       => __('SpamFireWall', 'cleantalk'),
					'description' => __("This option allows to filter spam bots before they access website. Also reduces CPU usage on hosting server and accelerates pages load time.", 'cleantalk'),
				),
			),
		),
		
		// Forms protection
		'forms_protection' => array(
			'title'          => __('Forms to protect', 'cleantalk'),
			'default_params' => array(),
			'description'    => '',
			'html_before'    => '<hr><br>'
				.'<span id="ct_adv_showhide">'
					.'<a href="#" class="apbct_color--gray" onclick="event.preventDefault(); apbct_show_hide_elem(\'#apbct_settings__davanced_settings\');">'
						.__('Advanced settings', 'cleantalk')
					.'</a>'
				.'</span>'
				.'<div id="apbct_settings__davanced_settings" style="display: none;">',
			'html_after'     => '',
			'fields'         => array(
				'registrations_test' => array(
					'title'       => __('Registration Forms', 'cleantalk'),
					'description' => __('WordPress, BuddyPress, bbPress, S2Member, WooCommerce.', 'cleantalk'),
				),
				'comments_test' => array(
					'title'       => __('Comments form', 'cleantalk'),
					'description' => __('WordPress, JetPack, WooCommerce.', 'cleantalk'),
				),
				'contact_forms_test' => array(
					'title'       => __('Contact forms', 'cleantalk'),
					'description' => __('Contact Form 7, Formidable forms, JetPack, Fast Secure Contact Form, WordPress Landing Pages, Gravity Forms.', 'cleantalk'),
				),
				'general_contact_forms_test' => array(
					'title'       => __('Custom contact forms', 'cleantalk'),
					'description' => __('Anti spam test for any WordPress themes or contacts forms.', 'cleantalk'),
				),
				'wc_checkout_test' => array(
					'title'       => __('WooCommerce checkout form', 'cleantalk'),
					'description' => __('Anti spam test for WooCommerce checkout form.', 'cleantalk'),
				),
				'search_test' => array(
					'title'       => __('Test default Wordpress search form for spam', 'cleantalk'),
					'description' => sprintf(
						__('Spam protection for Search form. Read more about %sspam protection for Search form%s on our blog.', 'cleantalk'),
						'<a href="https://blog.cleantalk.org/how-to-protect-website-search-from-spambots/" target="_blank">',
						'</a>'
					)
				),
				'check_external' => array(
					'title'       => __('Protect external forms', 'cleantalk'),
					'description' => __('Turn this option on to protect forms on your WordPress that send data to third-part servers (like MailChimp).', 'cleantalk'),
				),
				'check_internal' => array(
					'title'       => __('Protect internal forms', 'cleantalk'),
					'description' => __('This option will enable protection for custom (hand-made) AJAX forms with PHP scripts handlers on your WordPress.', 'cleantalk'),
				),
//				'validate_email_existence' => array(
//					'title'       => __('Validate e-mail for existence', 'cleantalk'),
//					'description' => __('Using additional filter for e-mails. Block subscription/comment/registration if e-mail not exists.', 'cleantalk'),
//				),
			),
		),
		
		// Comments and Messages
		'comments_and_messages' => array(
			'title'          => __('Comments and Messages', 'cleantalk'),
			'fields'         => array(
				'bp_private_messages' => array(
					'title'       => __('BuddyPress Private Messages', 'cleantalk'),
					'description' => __('Check buddyPress private messages.', 'cleantalk'),
				),
				'check_comments_number' => array(
					'title'       => __("Don't check trusted user's comments", 'cleantalk'),
					'description' => sprintf(__("Don't check comments for users with above % comments.", 'cleantalk'), defined('CLEANTALK_CHECK_COMMENTS_NUMBER') ? CLEANTALK_CHECK_COMMENTS_NUMBER : 3),
				),
				'remove_old_spam' => array(
					'title'       => __('Automatically delete spam comments', 'cleantalk'),
					'description' => sprintf(__('Delete spam comments older than %d days.', 'cleantalk'),  $apbct->settings['spam_store_days']),
				),
				'remove_comments_links' => array(
					'title'       => __('Remove links from approved comments', 'cleantalk'),
					'description' => __('Remove links from approved comments. Replace it with "[Link deleted]"', 'cleantalk'),
				),
				'show_check_links' => array(
					'title'       => __('Show links to check Emails, IPs for spam.', 'cleantalk'),
					'description' => __('Shows little icon near IP addresses and Emails allowing you to check it via CleanTalk\'s database. Also allowing you to manage comments from the public post\'s page.', 'cleantalk'),
					'display' => !$apbct->white_label,
				),
			),
		),
		
		// Data Processing
		'data_processing' => array(
			'title'          => __('Data Processing', 'cleantalk'),
			'fields'         => array(
				'protect_logged_in' => array(
					'title'       => __("Protect logged in Users", 'cleantalk'),
					'description' => __('Turn this option on to check for spam any submissions (comments, contact forms and etc.) from registered Users.', 'cleantalk'),
				),
				'use_ajax' => array(
					'title'       => __('Use AJAX for JavaScript check', 'cleantalk'),
					'description' => __('Options helps protect WordPress against spam with any caching plugins. Turn this option on to avoid issues with caching plugins.', 'cleantalk')."<strong> ".__('Attention! Incompatible with AMP plugins!', 'cleantalk')."</strong>",
				),
				'general_postdata_test' => array(
					'title'       => __('Check all post data', 'cleantalk'),
					'description' => __('Check all POST submissions from website visitors. Enable this option if you have spam misses on website.', 'cleantalk')
						.(!$apbct->white_label 
							? __(' Or you don`t have records about missed spam here:', 'cleantalk') . '&nbsp;' . '<a href="https://cleantalk.org/my/?user_token='.$apbct->user_token.'&utm_source=wp-backend&utm_medium=admin-bar&cp_mode=antispam" target="_blank">' . __('CleanTalk dashboard', 'cleantalk') . '</a>.'
							: ''
						)
						.'<br />' . __('СAUTION! Option can catch POST requests in WordPress backend', 'cleantalk'),
				),
				'set_cookies' => array(
					'title'       => __("Set cookies", 'cleantalk'),
					'description' => __('Turn this option off to deny plugin generates any cookies on website front-end. This option is helpful if you use Varnish. But most of contact forms will not be protected if the option is turned off! <b>Warning: We strongly recommend you to enable this otherwise it could cause false positives spam detection.</b>', 'cleantalk'),
					'childrens'   => array('set_cookies__sessions'),
				),
				'set_cookies__sessions' => array(
					'title'       => __('Use alternative mechanism for cookies.', 'cleantalk'),
					'description' => __('Doesn\'t use cookie or PHP sessions. Collect data for all types of bots.', 'cleantalk'),
					'parent'      => 'set_cookies',
					'class'       => 'apbct_settings-field_wrapper--sub',
				),
				'ssl_on' => array(
					'title'       => __("Use SSL", 'cleantalk'),
					'description' => __('Turn this option on to use encrypted (SSL) connection with servers.', 'cleantalk'),
				),
				'use_buitin_http_api' => array(
					'title'       => __("Use Wordpress HTTP API", 'cleantalk'),
					'description' => __('Alternative way to connect the CleanTalk\'s Cloud. Use this if you have connection problems.', 'cleantalk'),
				),
			),
		),
		
		// Admin bar
		'admin_bar' => array(
			'title'          => __('Admin bar', 'cleantalk'),
			'default_params' => array(),
			'description'    => '',
			'html_before'    => '',
			'html_after'     => '',
			'fields'         => array(
				'show_adminbar' => array(
					'title'       => __('Show statistics in admin bar', 'cleantalk'),
					'description' => __('Show/hide icon in top level menu in WordPress backend. The number of submissions is being counted for past 24 hours.', 'cleantalk'),
					'childrens' => array('all_time_counter','daily_counter','sfw_counter'),
				),
				'all_time_counter' => array(
					'title'       => __('Show All-time counter', 'cleantalk'),
					'description' => __('Display all-time requests counter in the admin bar. Counter displays number of requests since plugin installation.', 'cleantalk'),
					'parent' => 'show_adminbar',
					'class' => 'apbct_settings-field_wrapper--sub',
				),
				'daily_counter' => array(
					'title'       => __('Show 24 hours counter', 'cleantalk'),
					'description' => __('Display daily requests counter in the admin bar. Counter displays number of requests of the past 24 hours.', 'cleantalk'),
					'parent' => 'show_adminbar',
					'class' => 'apbct_settings-field_wrapper--sub',
				),
				'sfw_counter' => array(
					'title'       => __('SpamFireWall counter', 'cleantalk'),
					'description' => __('Display SpamFireWall requests in the admin bar. Counter displays number of requests since plugin installation.', 'cleantalk'),
					'parent' => 'show_adminbar',
					'class' => 'apbct_settings-field_wrapper--sub',
				),
			),
		),

		// Misc
		'misc' => array(
			'html_after'     => '</div><br>',
			'fields'         => array(
				'collect_details' => array(
					'type'        => 'checkbox',
					'title'       => __('Collect details about browsers', 'cleantalk'),
					'description' => __("Checking this box you allow plugin store information about screen size and browser plugins of website visitors. The option in a beta state.", 'cleantalk'),
				),
				'send_connection_reports' => array(
					'type'        => 'checkbox',
					'title'       => __('Send connection reports', 'cleantalk'),
					'description' => __("Checking this box you allow plugin to send the information about your connection. The option in a beta state.", 'cleantalk'),
				),
				'async_js' => array(
					'type'        => 'checkbox',
					'title'       => __('Async JavaScript loading', 'cleantalk'),
					'description' => __('Use async loading for scripts. Warning: This could reduce filtration quality.', 'cleantalk'),
				),
				'gdpr_enabled' => array(
					'type'        => 'checkbox',
					'title'       => __('Allow to add GDPR notice via shortcode', 'cleantalk'),
					'description' => __(' Adds small checkbox under your website form. To add it you should use the shortcode on the form\'s page: [cleantalk_gdpr_form id="FORM_ID"]', 'cleantalk'),
					'childrens'   => array('gdpr_text'),
				),
				'gdpr_text' => array(
					'type'        => 'text',
					'title'       => __('GDPR text notice', 'cleantalk'),
					'description' => __('This text will be added as a description to the GDPR checkbox.', 'cleantalk'),
					'parent'      => 'gdpr_enabled',
					'class'       => 'apbct_settings-field_wrapper--sub',
				),
				'store_urls' => array(
					'type'        => 'checkbox',
					'title'       => __('Store visited URLs', 'cleantalk'),
					'description' => __("Plugin stores last 10 visited URLs (HTTP REFFERERS) before visitor submits form on the site. You can see stored visited URLS for each visitor in your Dashboard. Turn the option on to improve Anti-Spam protection.", 'cleantalk'),
					'childrens'   => array('store_urls__sessions'),
				),
				'store_urls__sessions' => array(
					'type'        => 'checkbox',
					'title'       => __('Use cookies less sessions', 'cleantalk'),
					'description' => __('Doesn\'t use cookie or PHP sessions. Collect data for all types of bots.', 'cleantalk'),
					'parent'      => 'store_urls',
					'class'       => 'apbct_settings-field_wrapper--sub',
				),
				'comment_notify' => array(
					'type'        => 'checkbox',
					'title'       => __('Notify users with selected roles about new approved comments. Hold CTRL to select multiple roles.', 'cleantalk'),
					'description' => sprintf(__("If enabled, overrides similar Wordpress %sdiscussion settings%s.", 'cleantalk'), '<a href="options-discussion.php">','</a>'),
					'childrens'   => array('comment_notify__roles'),
				),
				'comment_notify__roles' => array(
					'callback'    => 'apbct_settings__field__comment_notify',
				),
				
			),
		),
	);
	
	foreach($apbct->settings_fields_in_groups as $group_name => $group){
		
		add_settings_section('apbct_section__'.$group_name, '', 'apbct_section__'.$group_name, 'cleantalk');
		
		foreach($group['fields'] as $field_name => $field){
			
			$params = !empty($group['default_params']) 
				? array_merge($group['default_params'], $field)
				: array_merge($field_default_params, $field);
			
			$params['name'] = $field_name;
			
			if(!$params['display'])
				continue;
			
			add_settings_field(
				'apbct_field__'.$field_name,
				'',
				$params['callback'],
				'cleantalk',
				'apbct_section__'.$group_name,
				$params
			);
			
		}
		
	}
	
	// GDPR
	// add_settings_field('cleantalk_collect_details', __('Collect details about browsers', 'cleantalk'), 'ct_input_collect_details', 'cleantalk', 'apbct_secton_antispam');
	// add_settings_field('cleantalk_connection_reports', __('Send connection reports', 'cleantalk'), 'ct_send_connection_reports', 'cleantalk', 'apbct_secton_antispam');
}

/**
 * Admin callback function - Displays plugin options page
 */
function apbct_settings_page() {
	
	global $apbct;		
		
		// Title
		echo '<h2 class="apbct_settings-title">'.__($apbct->plugin_name, 'cleantalk').'</h2>';

		// Subtitle for IP license
		if($apbct->moderate_ip)
			echo '<h4 class="apbct_settings-subtitle apbct_color--gray">'. __('Hosting AntiSpam', 'cleantalk').'</h4>';

		echo '<form action="options.php" method="post">';
			
			if(!is_network_admin())
			apbct_settings__error__output();
			
			// Top info
			if(!$apbct->white_label){
				echo '<div style="float: right; padding: 15px 15px 5px 15px; font-size: 13px; position: relative; top: -55px; background: #f1f1f1;">';

					echo __('CleanTalk\'s tech support:', 'cleantalk')
						.'&nbsp;'
						.'<a target="_blank" href="https://wordpress.org/support/plugin/cleantalk-spam-protect">Wordpress.org</a>.'
					// .' <a href="https://community.cleantalk.org/viewforum.php?f=25" target="_blank">'.__("Tech forum", 'cleantalk').'</a>'
					// .($user_token ? ", <a href='https://cleantalk.org/my/support?user_token=$user_token&cp_mode=antispam' target='_blank'>".__("Service support ", 'cleantalk').'</a>' : '').
						.'<br>';
					echo __('Plugin Homepage at', 'cleantalk').' <a href="http://cleantalk.org" target="_blank">cleantalk.org</a>.<br/>';
					echo '<span id="apbct_gdpr_open_modal" style="text-decoration: underline;">'.__('GDPR compliance', 'cleantalk').'</span><br/>';
					echo __('Use s@cleantalk.org to test plugin in any WordPress form.', 'cleantalk').'<br>';
					echo __('CleanTalk is registered Trademark. All rights reserved.', 'cleantalk').'<br/>';
					if($apbct->key_is_ok)
						echo '<b style="display: inline-block; margin-top: 10px;">'.sprintf(__('Do you like CleanTalk? %sPost your feedback here%s.', 'cleantalk'), '<a href="https://wordpress.org/support/plugin/cleantalk-spam-protect/reviews/#new-post" target="_blank">', '</a>').'</b><br />';
					apbct_admin__badge__get_premium();
					echo '<div id="gdpr_dialog" style="display: none; padding: 7px;">';
						apbct_gdpr__show_text('print');
					echo '</div>';
				echo '</div>';
			}
			
			// If it's network admin dashboard
			if(is_network_admin()){
				if(defined('CLEANTALK_ACCESS_KEY')){
					print '<br />'
					.sprintf(__('Your CleanTalk access key is: <b>%s</b>.', 'cleantalk'), CLEANTALK_ACCESS_KEY)
						.'<br />'
						.'You can change it in your wp-config.php file.'
						.'<br />';
				}else{
					print '<br />'
					.__('To set up global CleanTalk access key for all websites, define constant in your wp-config.php file before defining database constants: <br/><pre>define("CLEANTALK_ACCESS_KEY", "place your key here");</pre>', 'cleantalk');
				}
				return;
			}
			
			// Output spam count
			if($apbct->key_is_ok && apbct_api_key__is_correct()){
				if($apbct->spam_count > 0){
					echo '<div class="apbct_settings-subtitle" style="top: 0; margin-bottom: 10px; width: 200px;">'
						.'<br>'
						.'<span>'
							.sprintf(
								__( '%s  has blocked <b>%s</b> spam.', 'cleantalk' ),
								$apbct->plugin_name,
								number_format($apbct->spam_count, 0, ',', ' ')
							)
						.'</span>'
						.'<br>'
						.'<br>'
					.'</div>';
				}
				if(!$apbct->white_label){
					// CP button
					echo '<a class="cleantalk_manual_link" target="__blank" href="https://cleantalk.org/my?user_token='.$apbct->user_token.'&cp_mode=antispam">'
							.__('Click here to get anti-spam statistics', 'cleantalk')
						.'</a>';
					echo '&nbsp;&nbsp;';
					// Support button
					echo '<a class="cleantalk_auto_link" target="__blank" href="https://wordpress.org/support/plugin/cleantalk-spam-protect">'.__('Support', 'cleantalk').'</a>';
					echo '<br>'
						.'<br>';
				}
			}
			
			settings_fields('cleantalk_settings');
			do_settings_fields('cleantalk', 'cleantalk_section_settings_main');
			
			foreach($apbct->settings_fields_in_groups as $group_name => $group){
				
				echo !empty($group['html_before']) ? $group['html_before']                                      : '';
				echo !empty($group['title'])       ? '<h3 style="margin-left: 220px;">'.$group['title'].'</h3>' : '';
				
				do_settings_fields('cleantalk', 'apbct_section__'.$group_name);
				
				echo !empty($group['html_after'])  ? $group['html_after'] : '';
				
			}
			
			echo '<br>';
			echo '<button name="submit" class="cleantalk_manual_link" value="save_changes">'.__('Save Changes').'</button>';
		
		echo "</form>";
		
	if(!$apbct->white_label){
		// Translate banner for non EN locale
		if(substr(get_locale(), 0, 2) != 'en'){
			global $ct_translate_banner_template;
			require_once(CLEANTALK_PLUGIN_DIR.'templates/translate_banner.php');
			printf($ct_translate_banner_template, substr(get_locale(), 0, 2));
		}
	}
}

function apbct_settings__error__output($return = false){
	
	global $apbct;
	
	// If have error message output error block.
	
	$out = '';
	
	if(!empty($apbct->errors) && !defined('CLEANTALK_ACCESS_KEY')){
		
		$errors = $apbct->errors;
		
		$error_texts = array(
			// Misc
			'key_invalid' => __('Error occured while API key validating. Error: ', 'security-malware-firewall'),
			'key_get' => __('Error occured while automatically gettings access key. Error: ', 'security-malware-firewall'),
			'sfw_send_logs' => __('Error occured while sending sending SpamFireWall logs. Error: ', 'security-malware-firewall'),
			'sfw_update' => __('Error occured while updating SpamFireWall local base. Error: '            , 'security-malware-firewall'),
			'account_check' => __('Error occured while checking account status. Error: ', 'security-malware-firewall'),
			'api' => __('Error occured while excuting API call. Error: ', 'security-malware-firewall'),
			// Unknown
			'unknown' => __('Unknown error. Error: ', 'security-malware-firewall'),
		);
		
		$errors_out = array();
		
		foreach($errors as $type => $error){
			
			if(!empty($error)){
				
				if(is_array(current($error))){
					
					foreach($error as $sub_type => $sub_error){
						$errors_out[$sub_type] = '';
						if(isset($sub_error['error_time']))
							$errors_out[$sub_type] .= date('Y-m-d H:i:s', $sub_error['error_time']) . ': ';
						$errors_out[$sub_type] .= ucfirst($type).': ';
						$errors_out[$sub_type] .= (isset($error_texts[$sub_type]) ? $error_texts[$sub_type] : $error_texts['unknown']) . $sub_error['error_string'];
					}
					continue;
				}
				
				$errors_out[$type] = '';
				if(isset($error['error_time'])) 
					$errors_out[$type] .= date('Y-m-d H:i:s', $error['error_time']) . ': ';
				$errors_out[$type] .= (isset($error_texts[$type]) ? $error_texts[$type] : $error_texts['unknown']) . ' ' . (isset($error['error_string']) ? $error['error_string'] : '');
				
			}
		}
		
		if(!empty($errors_out)){
			$out .= '<div id="apbctTopWarning" class="error" style="position: relative;">'
				.'<h3 style="display: inline-block;">'.__('Errors:', 'security-malware-firewall').'</h3>';
				foreach($errors_out as $value){
					$out .= '<h4>'.$value.'</h4>';
				}
				$out .= !$apbct->white_label
					? '<h4 style="text-align: unset;">'.sprintf(__('You can get support any time here: %s.', 'cleantalk'), '<a target="blank" href="https://wordpress.org/support/plugin/cleantalk-spam-protect">https://wordpress.org/support/plugin/cleantalk-spam-protect</a>').'</h4>'
					: '';
			$out .= '</div>';
		}
	}
	
	if($return) return $out; else echo $out;
}

function apbct_settings__field__debug(){
	
	global $apbct;
	
	if($apbct->debug){
		
	echo '<hr /><h2>Debug:</h2>';
	echo '<h4>Constants:</h4>';
	echo 'CLEANTALK_AJAX_USE_BUFFER '.		 	(defined('CLEANTALK_AJAX_USE_BUFFER') ? 		(CLEANTALK_AJAX_USE_BUFFER ? 		'true' : 'flase') : 'NOT_DEFINED')."<br>";
	echo 'CLEANTALK_AJAX_USE_FOOTER_HEADER '.	(defined('CLEANTALK_AJAX_USE_FOOTER_HEADER') ? 	(CLEANTALK_AJAX_USE_FOOTER_HEADER ? 'true' : 'flase') : 'NOT_DEFINED')."<br>";
	echo 'CLEANTALK_ACCESS_KEY '.				(defined('CLEANTALK_ACCESS_KEY') ? 				(CLEANTALK_ACCESS_KEY ? 			CLEANTALK_ACCESS_KEY : 'flase') : 'NOT_DEFINED')."<br>";
	echo 'CLEANTALK_CHECK_COMMENTS_NUMBER '.	(defined('CLEANTALK_CHECK_COMMENTS_NUMBER') ? 	(CLEANTALK_CHECK_COMMENTS_NUMBER ? 	CLEANTALK_CHECK_COMMENTS_NUMBER : 0) : 'NOT_DEFINED')."<br>";
	echo 'CLEANTALK_CHECK_MESSAGES_NUMBER '.	(defined('CLEANTALK_CHECK_MESSAGES_NUMBER') ? 	(CLEANTALK_CHECK_MESSAGES_NUMBER ? 	CLEANTALK_CHECK_MESSAGES_NUMBER : 0) : 'NOT_DEFINED')."<br>";
	echo 'CLEANTALK_PLUGIN_DIR '.				(defined('CLEANTALK_PLUGIN_DIR') ? 				(CLEANTALK_PLUGIN_DIR ? 			CLEANTALK_PLUGIN_DIR : 'flase') : 'NOT_DEFINED')."<br>";
	echo 'WP_ALLOW_MULTISITE '.					(defined('WP_ALLOW_MULTISITE') ? 				(WP_ALLOW_MULTISITE ?				'true' : 'flase') : 'NOT_DEFINED');
	
	echo "<h4>Debug log: <button type='submit' value='debug_drop' name='submit' style='font-size: 11px; padding: 1px;'>Drop debug data</button></h4>";
	echo "<div style='height: 500px; width: 80%; overflow: auto;'>";
		
		$output = print_r($apbct->debug, true);
		$output = str_replace("\n", "<br>", $output);
		$output = preg_replace("/[^\S]{4}/", "&nbsp;&nbsp;&nbsp;&nbsp;", $output);
		echo "$output";
		
	echo "</div>";
		
	}
}

function apbct_settings__field__state(){
	
	global $apbct, $wpdb;
	
	$path_to_img = plugin_dir_url(__FILE__) . "images/";
	
	$img = $path_to_img."yes.png";
	$img_no = $path_to_img."no.png";
	$img_no_gray = $path_to_img."no_gray.png";
	$color="black";
	
	if(!$apbct->key_is_ok){
		$img=$path_to_img."no.png";
		$img_no=$path_to_img."no.png";
		$color="black";
	}
	
	if(!apbct_api_key__is_correct($apbct->api_key)){
		$img = $path_to_img."yes_gray.png";
		$img_no = $path_to_img."no_gray.png";
		$color="gray";
	}
	
	if($apbct->moderate_ip){
		$img = $path_to_img."yes.png";
		$img_no = $path_to_img."no.png";
		$color="black";
	}
	
	if($apbct->data['moderate'] == 0){
		$img = $path_to_img."no.png";
		$img_no = $path_to_img."no.png";
		$color="black";
	}
	
	print '<div class="apbct_settings-field_wrapper" style="color:'.$color.'">';
	
		print '<h2>'.__('Protection is active', 'cleantalk').'</h2>';
		
		echo '<img class="apbct_status_icon" src="'.($apbct->settings['registrations_test'] == 1       || $apbct->moderate_ip ? $img : $img_no).'"/>'
			.__('Registration forms', 'cleantalk');
		echo '<img class="apbct_status_icon" src="'.($apbct->settings['comments_test']==1              || $apbct->moderate_ip ? $img : $img_no).'"/>'
			.__('Comments forms', 'cleantalk');
		echo '<img class="apbct_status_icon" src="'.($apbct->settings['contact_forms_test']==1         || $apbct->moderate_ip ? $img : $img_no).'"/>'
			.__('Contact forms', 'cleantalk');
		echo '<img class="apbct_status_icon" src="'.($apbct->settings['general_contact_forms_test']==1 || $apbct->moderate_ip ? $img : $img_no).'"/>'
			.__('Custom contact forms', 'cleantalk');
		echo '<img class="apbct_status_icon" src="'.($apbct->data['moderate'] == 1                      || $apbct->moderate_ip ? $img : $img_no).'"/>'
			.'<a style="color: black" href="https://blog.cleantalk.org/real-time-email-address-existence-validation/">'.__('Validate email for existence', 'cleantalk').'</a>';

		// Autoupdate status
		if($apbct->notice_auto_update){
			echo '<img class="apbct_status_icon" src="'.($apbct->auto_update == 1 ? $img : ($apbct->auto_update == -1 ? $img_no : $img_no_gray)).'"/>'.__('Auto update', 'cleantalk')
				.' <sup><a href="http://cleantalk.org/help/cleantalk-auto-update" target="_blank">?</a></sup>';
		}
		
		// WooCommerce
		if(class_exists('WooCommerce'))
			echo '<img class="apbct_status_icon" src="'.($apbct->settings['wc_checkout_test'] == 1 || $apbct->moderate_ip ? $img : $img_no).'"/>'.__('WooCommerce checkout form', 'cleantalk');
		
		if($apbct->moderate_ip)
			print "<br /><br />The anti-spam service is paid by your hosting provider. License #".$apbct->data['ip_license'].".<br />";
	
	print "</div>";
}

/**
 * Admin callback function - Displays inputs of 'apikey' plugin parameter
 */
function apbct_settings__field__api_key(){
	
	global $apbct;
	
	echo '<div id="cleantalk_apikey_wrapper" class="apbct_settings-field_wrapper '.(apbct_api_key__is_correct($apbct->api_key) && $apbct->key_is_ok ? 'apbct_display--none"' : '').'">';
		
		// White label
		if($apbct->white_label){
	
		// WPMS and key defined
		}elseif(defined('CLEANTALK_ACCESS_KEY') && is_multisite()){
			
			_e('<h3>Key is provided by Super Admin.<h3>', 'cleantalk');
		
		// Normal flow
		}elseif(true){
			
			echo '<label class="apbct_settings__label" for="cleantalk_apkey">'
				.__('Access key', 'cleantalk')
			.'</label>'
			.'<input 
				type="text"
				name="cleantalk_settings[apikey]"
				value="'.$apbct->api_key.'"
				class="apbct_font-size--14pt"
				size="20"
				placeholder="' . __('Enter the key', 'cleantalk') . '" />';
			
			// Key is correct
			if((apbct_api_key__is_correct($apbct->api_key) && $apbct->key_is_ok) && isset($apbct->data['account_name_ob']) && $apbct->data['account_name_ob'] != ''){
				echo '<br>'
				.sprintf(
					__('Account at cleantalk.org is %s.', 'cleantalk'),
					'<b>'.$apbct->data['account_name_ob'].'</b>'
				);
			}
			
			// Key is NOT correct
			if(!apbct_api_key__is_correct($apbct->api_key) || !$apbct->key_is_ok){
				echo '<br /><br />';
				
				// Auto get key
				if(!$apbct->ip_license){
					echo '<button id="apbct_setting_get_key_auto" name="submit" type="submit" class="cleantalk_manual_link" value="get_key_auto"'
//                      . 'title="'
//						.sprintf(__('Admin e-mail (%s) will be used to get access key if you want to use another email, click on Get Access Key Manually.', 'cleantalk'),
//								ct_get_admin_email()
//							)
//						. '"'
						. '>'
						.__('Get Access Key Automatically', 'cleantalk')
					.'</button>';
//					.'&nbsp;'.__('or', 'cleantalk').'&nbsp;';
					echo '<input type="hidden" id="ct_admin_timezone" name="ct_admin_timezone" value="null" />';
					echo '<br />';
					echo '<br />';
				}
				
				// Manual get key
//				echo '<a class="apbct_color--gray" target="__blank" href="https://cleantalk.org/register?platform=wordpress&email='.urlencode(ct_get_admin_email()).'&website='.urlencode(parse_url(get_option('siteurl'),PHP_URL_HOST)).'">'.__('Get access key manually', 'cleantalk').'</a>';
								
				// Warnings and GDPR
				printf(__('Admin e-mail (%s) will be used for registration, if you want to use other email please %sGet Access Key Manually%s.', 'cleantalk'),
					ct_get_admin_email(),
					'<a href="https://cleantalk.org/register?platform=wordpress&website='. urlencode(parse_url(get_option('siteurl'),PHP_URL_HOST)) .'">',
					'</a>'
				);
				
				if(!$apbct->ip_license){
					echo '<div>';
						echo '<input checked type="checkbox" id="license_agreed" onclick="apbctSettingsDependencies(\'get_key_auto\');"/>';
						echo '<label for="spbc_license_agreed">';
							printf(
								__('I agree with of %sLicense Agreement%s.', 'security-malware-firewall'),
								'<a href="https://cleantalk.org/publicoffer"         target="_blank" style="color:#66b;">', '</a>'
							);
						echo "</label>";
					echo '</div>';
				}
			}
			
		}
	
	echo '</div>';
	
	if($apbct->ip_license){
		// $cleantalk_support_links = "<br /><div>";
        // $cleantalk_support_links .= "<a href='#' class='ct_support_link'>" . __("Show the access key", 'cleantalk') . "</a>";
        // $cleantalk_support_links .= "</div>";
		// echo "<script type=\"text/javascript\">var cleantalk_good_key=true; var cleantalk_support_links = \"$cleantalk_support_links\";</script>";
	}
}

function apbct_settings__field__action_buttons(){
	
	global $apbct;
	
	echo '<div class="apbct_settings-field_wrapper">';
	
		if(apbct_api_key__is_correct($apbct->api_key) && $apbct->key_is_ok){
			echo '<div>'
				.(!$apbct->white_label
					?'<a href="#" class="ct_support_link" onclick="apbct_show_hide_elem(\'#cleantalk_apikey_wrapper\')">' . __('Show the access key', 'cleantalk') . '</a>' . '&nbsp;&nbsp;'	. '&nbsp;&nbsp;'
					: ''
				)
				.'<a href="edit-comments.php?page=ct_check_spam" class="ct_support_link">' . __('Check comments for spam', 'cleantalk') . '</a>'
				.'&nbsp;&nbsp;'
				.'&nbsp;&nbsp;'
				.'<a href="users.php?page=ct_check_users" class="ct_support_link">' . __('Check users for spam', 'cleantalk') . '</a>'
				.'&nbsp;&nbsp;'
				.'&nbsp;&nbsp;'
				.'<a href="#" class="ct_support_link" onclick="apbct_show_hide_elem(\'#apbct_statistics\')">' . __('Statistics & Reports', 'cleantalk') . '</a>'
			.'</div>';
		
		}
		
	echo '</div>';
}

function apbct_settings__field__statistics() {

	global $apbct, $wpdb;
	
	echo '<div id="apbct_statistics" class="apbct_settings-field_wrapper" style="display: none;">';

		// Last request
		printf(
			__('Last spam check request to %s server was at %s.', 'cleantalk'),
			$apbct->stats['last_request']['server'] ? $apbct->stats['last_request']['server'] : __('unknown', 'cleantalk'),
			$apbct->stats['last_request']['time'] ? date('M d Y H:i:s', $apbct->stats['last_request']['time']) : __('unknown', 'cleantalk')
		);
		echo '<br>';

		// Avarage time request
		printf(
			__('Average request time for past 7 days: %s seconds.', 'cleantalk'),
			$apbct->stats['requests'][min(array_keys($apbct->stats['requests']))]['average_time']
				? round($apbct->stats['requests'][min(array_keys($apbct->stats['requests']))]['average_time'], 3)
				: __('unknown', 'cleantalk')
		);
		echo '<br>';

		// SFW last die
		printf(
			__('Last SpamFireWall blocking page was showed to %s IP at %s.', 'cleantalk'),
			$apbct->stats['last_sfw_block']['ip'] ? $apbct->stats['last_sfw_block']['ip'] : __('unknown', 'cleantalk'),
			$apbct->stats['last_sfw_block']['time'] ? date('M d Y H:i:s', $apbct->stats['last_sfw_block']['time']) : __('unknown', 'cleantalk')
		);
		echo '<br>';

		// SFW last update
		$sfw_netwoks_amount = $wpdb->get_results("SELECT count(*) AS cnt FROM `".$wpdb->base_prefix."cleantalk_sfw`", ARRAY_A);
		printf(
			__('SpamFireWall was updated %s. Now contains %s entries.', 'cleantalk'),
			$apbct->stats['sfw']['last_update_time'] ? date('M d Y H:i:s', $apbct->stats['sfw']['last_update_time']) : __('unknown', 'cleantalk'),
			isset($sfw_netwoks_amount[0]['cnt']) ? $sfw_netwoks_amount[0]['cnt'] : __('unknown', 'cleantalk')
		);
		echo '<br>';

		// SFW last sent logs
		printf(
			__('SpamFireWall sent %s events at %s.', 'cleantalk'),
			$apbct->stats['sfw']['last_send_amount'] ? $apbct->stats['sfw']['last_send_amount'] : __('unknown', 'cleantalk'),
			$apbct->stats['sfw']['last_send_time'] ? date('M d Y H:i:s', $apbct->stats['sfw']['last_send_time']) : __('unknown', 'cleantalk')
		);
		echo '<br>';

		// Connection reports
		if ($apbct->connection_reports){
			
			if ($apbct->connection_reports['negative'] == 0){
				_e('There are no failed connections to server.', 'cleantalk');
			}else{
				echo "<table id='negative_reports_table''>
					<tr>
						<td>#</td>
						<td><b>Date</b></td>
						<td><b>Page URL</b></td>
						<td><b>Report</b></td>
						<td><b>Server IP</b></td>
					</tr>";
				foreach($apbct->connection_reports['negative_report'] as $key => $report){
					echo '<tr>'
						. '<td>'.($key+1).'.</td>'
						. '<td>'.$report['date'].'</td>'
						. '<td>'.$report['page_url'].'</td>'
						. '<td>'.$report['lib_report'].'</td>'
						. '<td>'.$report['work_url'].'</td>'
					. '</tr>';
				}
				echo "</table>";
				echo '<br/>';
					echo '<button'
						. ' name="submit"'
						. ' class="cleantalk_manual_link"'
						. ' value="ct_send_connection_report"'
						. (!$apbct->settings['send_connection_reports'] ? ' disabled="disabled"' : '')
						. '>'
							.__('Send report', 'cleantalk')
						.'</button>';
				if (!$apbct->settings['send_connection_reports']){
					echo '<br><br>';
					_e('Please, enable "Send connection reports" setting to be able to send reports', 'cleantalk');
				}
			}

		}
		
	echo '</div>';
}

function apbct_settings__field__comment_notify() {
	
	global $apbct, $wp_roles;
	
	$wp_roles = new WP_Roles();
	$roles = $wp_roles->get_names();
	
	echo '<div class="apbct_settings-field_wrapper apbct_settings-field_wrapper--sub">';
		
		echo '<select multiple="multiple" id="apbct_setting_comment_notify__roles" name="cleantalk_settings[comment_notify__roles][]"'
			.(!$apbct->settings['comment_notify'] ? ' disabled="disabled"' : '')
			.' size="'.(count($roles)-1).'"'
		. '>';
		
			foreach ($roles as $role){
				if($role == 'Subscriber') continue;
				echo '<option'
					.(in_array($role, $apbct->settings['comment_notify__roles']) ? ' selected="selected"' : '')
				. '>'.$role.'</option>';
			}
			
		echo '</select>';
		
	echo '</div>';
}

function apbct_settings__field__draw($params = array()){
	
	global $apbct;
	
	echo '<div class="'.$params['def_class'].(isset($params['class']) ? ' '.$params['class'] : '').'">';
	
		switch($params['type']){
			
			// Checkbox type
			case 'checkbox':
				echo '<input type="checkbox" id="apbct_setting_'.$params['name'].'" name="cleantalk_settings['.$params['name'].']" value="1" '
					.($apbct->settings[$params['name']] == '1' ? ' checked' : '')
					.($params['parent'] && !$apbct->settings[$params['parent']] ? ' disabled="disabled"' : '')
					.(!$params['childrens'] ? '' : ' onchange="apbctSettingsDependencies([\''.implode("','",$params['childrens']).'\'])"')
					.' />'
				.'<label for="apbct_setting_'.$params['name'].'" class="apbct_setting-field_title--'.$params['type'].'">'
					.$params['title']
				.'</label>';
				echo '<div class="apbct_settings-field_description">'
					.$params['description']
				.'</div>';				
				break;
			
			// Radio type
			case 'radio':
				echo '<h4 class="apbct_settings-field_title apbct_settings-field_title--'.$params['type'].'">'
					.$params['title']
				.'</h4>';
				
				echo '<div class="apbct_settings-field_content apbct_settings-field_content--'.$params['type'].'">';
				
					echo '<input type="radio" id="apbct_setting_'.$params['name'].'_yes" name="cleantalk_settings['.$params['name'].']" value="1" '
						.($params['parent'] && !$apbct->settings[$params['parent']] ? ' disabled="disabled"' : '')
						.(!$params['childrens'] ? '' : ' onchange="apbctSettingsDependencies([\''.implode("','",$params['childrens']).'\'])"')
						.($apbct->settings[$params['name']] ? ' checked' : '').' />'
						.'<label for="apbct_setting_'.$params['name'].'_yes"> ' . __('Yes') . '</label>';
						
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					
					echo '<input type="radio" id="apbct_setting_'.$params['name'].'_no" name="cleantalk_settings['.$params['name'].']" value="0" '
						.($params['parent'] && !$apbct->settings[$params['parent']] ? ' disabled="disabled"' : '')
						.(!$params['childrens'] ? '' : ' onchange="apbctSettingsDependencies([\''.implode("','",$params['childrens']).'\'])"')
						.(!$apbct->settings[$params['name']] ? ' checked' : '').' />'
						.'<label for="apbct_setting_'.$params['name'].'_no">'. __('No') . '</label>';
					
					echo '<div class="apbct_settings-field_description">'
						.$params['description']
					.'</div>';
					
				echo '</div>';
				break;
			
			// Text type
			case 'text':
				
				echo '<input type="text" id="apbct_setting_'.$params['name'].'" name="cleantalk_settings['.$params['name'].']"'
					.'class="apbct_input_text apbct_input_text-width--500px"'
					.' value="'. $apbct->settings[$params['name']] .'" '
					.($params['parent'] && !$apbct->settings[$params['parent']] ? ' disabled="disabled"' : '')
					.(!$params['childrens'] ? '' : ' onchange="apbctSettingsDependencies([\''.implode("','",$params['children']).'\'])"')
					.' />'
				. '&nbsp;'
				.'<label for="apbct_setting_'.$params['name'].'" class="apbct_setting-field_title--'.$params['type'].'">'
					.$params['title']
				.'</label>';
				echo '<div class="apbct_settings-field_description">'
					.$params['description']
				.'</div>';				
				break;
		}
		
	echo '</div>';
}

/**
 * Admin callback function - Plugin parameters validator
 * 
 * @global CleantalkState $apbct
 * @param array $settings Array with passed settings
 * @return array Array with processed settings
 */
function apbct_settings__validate($settings) {
	
	global $apbct;
		
	// Set missing settings.
	foreach($apbct->def_settings as $setting => $value){
		if(!isset($settings[$setting])){
			$settings[$setting] = null;
			settype($settings[$setting], gettype($value));
		}
	} unset($setting, $value);
		
	// validating API key
	$settings['apikey'] = isset($settings['apikey']) ? trim($settings['apikey']) : '';
	$settings['apikey'] = defined('CLEANTALK_ACCESS_KEY') ? CLEANTALK_ACCESS_KEY : $settings['apikey'];
	$settings['apikey'] = $apbct->white_label ? $apbct->settings['apikey'] : $settings['apikey'];
	
	// Drop debug data
	if (isset($_POST['submit']) && $_POST['submit'] == 'debug_drop'){
		$apbct->debug = false;
		delete_option('cleantalk_debug');
		return $settings;
	}
	
	// Send connection reports
	if (isset($_POST['submit']) && $_POST['submit'] == 'ct_send_connection_report'){
		ct_mail_send_connection_report();
		return $settings;
	}
	
	// Auto getting key
	if (isset($_POST['submit']) && $_POST['submit'] == 'get_key_auto'){
		
		$website        = parse_url(get_option('siteurl'), PHP_URL_HOST).parse_url(get_option('siteurl'), PHP_URL_PATH);
		$platform       = 'wordpress';
		$user_ip             = CleantalkHelper::ip__get(array('real'), false);
		$timezone       = filter_input(INPUT_POST, 'ct_admin_timezone');
		$language       = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE');
		$wpms           = APBCT_WPMS && defined('SUBDOMAIN_INSTALL') && !SUBDOMAIN_INSTALL ? true : false;
		$white_label    = $apbct->white_label                                   ? 1                                : 0;
		$hoster_api_key = $apbct->white_label&& defined('APBCT_HOSTER_API_KEY') ? APBCT_HOSTER_API_KEY             : '';
		
		$result = CleantalkAPI::method__get_api_key(
			ct_get_admin_email(),
			$website,
			$platform,
			$timezone,
			$language,
			$user_ip,
			$wpms,
			$white_label,
			$hoster_api_key
		);
		
		if(empty($result['error'])){
			
			if(isset($result['user_token'])){
				$apbct->data['user_token'] = $result['user_token'];
			}
			
			if(!empty($result['auth_key'])){
				$settings['apikey'] = $result['auth_key'];
			}
			
		}else{
			if(!$apbct->white_label)
				$apbct->error_add('key_get', $result);
			else
				$apbct->error_add('key_get', $result['error_string'] . ' <button id="apbct_setting_get_key_auto" name="submit" type="submit" class="cleantalk_manual_link" value="get_key_auto">'.__('Get access key automatically', 'cleantalk').'</button>'.'<input type="hidden" id="ct_admin_timezone" name="ct_admin_timezone" value="null" />');
			return $settings;
		}
	}
	
	// Feedback with app_agent
	ct_send_feedback('0:' . CLEANTALK_AGENT); // 0 - request_id, agent version.
	
	// Key is good by default
	$apbct->data['key_is_ok'] = true;
	
	// Is key correct?
	if(apbct_api_key__is_correct($settings['apikey'])){
		
		$result = CleantalkAPI::method__notice_validate_key($settings['apikey'], preg_replace('/http[s]?:\/\//', '', get_option('siteurl'), 1));
		
		// Is key valid?
		if (empty($result['error'])){
			
			if($result['valid'] == 1){
				
				// Deleting errors about invalid key
				$apbct->error_delete('key_invalid key_get', 'save');
				
				// Check account status
				ct_account_status_check($settings['apikey']);
				
				// SFW actions
				if($apbct->settings['spam_firewall'] == 1){
					ct_sfw_update($settings['apikey']);
					ct_sfw_send_logs($settings['apikey']);
				}
				
				// Updating brief data for dashboard widget
				$apbct->data['brief_data'] = CleantalkAPI::method__get_antispam_report_breif($settings['apikey']);
				
			// Key is not valid
			}else{
				$apbct->data['key_is_ok'] = false;
				$apbct->error_add('key_invalid', __('Testing is failed. Please check the Access key.', 'cleantalk'));
			}
			
			// Deleting legacy
			if(isset($apbct->data['testing_failed']))
				unset($apbct->data['testing_failed']);
			
		// Server error when notice_validate_key
		}else{
			$apbct->data['key_is_ok'] = false;
			$apbct->saveData();
			$apbct->error_add('key_invalid', $result);
		}
	
	// Key is not correct
	}else{
		$apbct->data['key_is_ok'] = false;
		if(empty($settings['apikey'])){
			$apbct->error_delete('key_invalid account_check', 'save');
		}else
			$apbct->error_add('key_invalid', __('Key is not correct', 'cleantalk'));
	}
	
	if($apbct->data['key_is_ok'] == false && $apbct->data['moderate_ip'] == 0){
		
		// Notices
		$apbct->data['notice_show']        = 1;
		$apbct->data['notice_renew']       = 0;
		$apbct->data['notice_trial']       = 0;
		$apbct->data['notice_review']      = 0;
		$apbct->data['notice_auto_update'] = 0;
		
		// Other
		$apbct->data['service_id']         = 0;
		$apbct->data['valid']              = 0;
		$apbct->data['moderate']           = 0;
		$apbct->data['ip_license']         = 0;
		$apbct->data['moderate_ip']        = 0;
		$apbct->data['spam_count']         = 0;
		$apbct->data['auto_update']        = 0;
		$apbct->data['user_token']         = '';
		$apbct->data['license_trial']      = 0;
		$apbct->data['account_name_ob']    = '';
	}
	
	$apbct->saveData();
	
	return $settings;
}

function apbct_gdpr__show_text($print = false){
	
	$out = wpautop('The notice requirements remain and are expanded. They must include the retention time for personal data, and contact information for data controller and data protection officer has to be provided.
	Automated individual decision-making, including profiling (Article 22) is contestable, similarly to the Data Protection Directive (Article 15). Citizens have rights to question and fight significant decisions that affect them that have been made on a solely-algorithmic basis. Many media outlets have commented on the introduction of a "right to explanation" of algorithmic decisions, but legal scholars have since argued that the existence of such a right is highly unclear without judicial tests and is limited at best.
	To be able to demonstrate compliance with the GDPR, the data controller should implement measures, which meet the principles of data protection by design and data protection by default. Privacy by design and by default (Article 25) require data protection measures to be designed into the development of business processes for products and services. Such measures include pseudonymising personal data, by the controller, as soon as possible (Recital 78).
	It is the responsibility and the liability of the data controller to implement effective measures and be able to demonstrate the compliance of processing activities even if the processing is carried out by a data processor on behalf of the controller (Recital 74).
	Data Protection Impact Assessments (Article 35) have to be conducted when specific risks occur to the rights and freedoms of data subjects. Risk assessment and mitigation is required and prior approval of the national data protection authorities (DPAs) is required for high risks. Data protection officers (Articles 37–39) are required to ensure compliance within organisations.
	They have to be appointed:')
	.'<ul style="padding: 0px 25px; list-style: disc;">'
		.'<li>for all public authorities, except for courts acting in their judicial capacity</li>'
		.'<li>if the core activities of the controller or the processor are:</li>'
			.'<ul style="padding: 0px 25px; list-style: disc;">'
				.'<li>processing operations, which, by virtue of their nature, their scope and/or their purposes, require regular and systematic monitoring of data subjects on a large scale</li>'
				.'<li>processing on a large scale of special categories of data pursuant to Article 9 and personal data relating to criminal convictions and offences referred to in Article 10;</li>'
			.'</ul>'
		.'</li>'
	.'</ul>';
	
	if($print) echo $out; else return $out;
}