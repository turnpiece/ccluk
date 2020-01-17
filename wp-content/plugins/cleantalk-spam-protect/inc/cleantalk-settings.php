<?php

/**
 * Admin action 'admin_menu' - Add the admin options page
 */
function apbct_settings_add_page() {
	
	global $apbct, $pagenow;
	
	$parent_slug = is_network_admin() ? 'settings.php'                     : 'options-general.php';
	$callback    = is_network_admin() ? 'apbct_settings__display__network' : 'apbct_settings__display';
	
	// Adding settings page
	add_submenu_page(
		$parent_slug,
		$apbct->plugin_name.' '.__('settings'),
		$apbct->plugin_name,
		'manage_options',
		'cleantalk',
		$callback
	);
	
	if(!in_array($pagenow, array('options.php', 'options-general.php', 'settings.php', 'admin.php')))
		return;
	
	register_setting('cleantalk_settings', 'cleantalk_settings', 'apbct_settings__validate');
	
	$fields = array();
	$fields = apbct_settings__set_fileds($fields);
	$fields = APBCT_WPMS && is_main_site() ? apbct_settings__set_fileds__network($fields) : $fields;
	apbct_settings__add_groups_and_fields($fields);
	
}

function apbct_settings__set_fileds( $fields ){
	global $apbct;
	
	$fields =  array(
		
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
				'connection_reports' => array(
					'callback'    => 'apbct_settings__field__statistics',
				),
				'api_key' => array(
					'display'        => !$apbct->white_label || is_main_site(),
					'callback'       => 'apbct_settings__field__apikey',
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
				.'<a href="#" class="apbct_color--gray" onclick="event.preventDefault(); apbct_show_hide_elem(\'apbct_settings__davanced_settings\');">'
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
				'search_test' => array(
					'title'       => __('Test default Wordpress search form for spam', 'cleantalk'),
					'description' => __('Spam protection for Search form.', 'cleantalk')
						. (!$apbct->white_label || is_main_site()
							? sprintf(__('Read more about %sspam protection for Search form%s on our blog.', 'cleantalk'),
								'<a href="https://blog.cleantalk.org/how-to-protect-website-search-from-spambots/" target="_blank">',
								'</a>'
								)
							: ''
						)
				),
				'check_external' => array(
					'title'       => __('Protect external forms', 'cleantalk'),
					'description' => __('Turn this option on to protect forms on your WordPress that send data to third-part servers (like MailChimp).', 'cleantalk'),
					'childrens'   => array('check_external__capture_buffer'),
				),
				'check_external__capture_buffer' => array(
					'title'       => __('Capture buffer', 'cleantalk'),
					'description' => __('This setting gives you more sophisticated and strengthened protection for external forms. But it could break plugins which use a buffer like Ninja Forms.', 'cleantalk'),
					'class'       => 'apbct_settings-field_wrapper--sub',
					'parent'      => 'check_external',
				),
				'check_internal' => array(
					'title'       => __('Protect internal forms', 'cleantalk'),
					'description' => __('This option will enable protection for custom (hand-made) AJAX forms with PHP scripts handlers on your WordPress.', 'cleantalk'),
				),
			),
		),
		
		// Comments and Messages
		'wc' => array(
			'title'          => __('WooCommerce', 'cleantalk'),
			'fields'         => array(
				'wc_checkout_test' => array(
					'title'       => __('WooCommerce checkout form', 'cleantalk'),
					'description' => __('Anti spam test for WooCommerce checkout form.', 'cleantalk'),
					'childrens'   => array('wc_register_from_order')
				),
				'wc_register_from_order' => array(
					'title'           => __('Spam test for registration during checkout', 'cleantalk'),
					'description'     => __('Enable anti spam test for registration process which during woocommerce\'s checkout.', 'cleantalk'),
					'parent'          => 'wc_checkout_test',
					'class'           => 'apbct_settings-field_wrapper--sub',
					'reverse_trigger' => true
				),
			),
		),
		
		// Comments and Messages
		'comments_and_messages' => array(
			'title'          => __('Comments and Messages', 'cleantalk'),
			'fields'         => array(
				'disable_comments__all' => array(
					'title' => __( 'Disable all comments', 'cleantalk' ),
					'description' => __( 'Disabling comments for all types of content.', 'cleantalk' ),
					'childrens' => array(
						'disable_comments__posts',
						'disable_comments__pages',
						'disable_comments__media',
					),
					'options' => array(
						array( 'val' => 1, 'label' => __( 'On' ), 'childrens_enable' => 0, ),
						array( 'val' => 0, 'label' => __( 'Off' ), 'childrens_enable' => 1, ),
					),
				),
				'disable_comments__posts' => array(
					'title'           => __( 'Disable comments for all posts', 'cleantalk' ),
					'class'           => 'apbct_settings-field_wrapper--sub',
					'parent'          => 'disable_comments__all',
					'reverse_trigger' => true,
				),
				'disable_comments__pages' => array(
					'title'           => __( 'Disable comments for all pages', 'cleantalk' ),
					'class'           => 'apbct_settings-field_wrapper--sub',
					'parent'          => 'disable_comments__all',
					'reverse_trigger' => true,
				),
				'disable_comments__media' => array(
					'title'           => __( 'Disable comments for all media', 'cleantalk' ),
					'class'           => 'apbct_settings-field_wrapper--sub',
					'parent'          => 'disable_comments__all',
					'reverse_trigger' => true,
				),
				'bp_private_messages' => array(
					'title'       => __('BuddyPress Private Messages', 'cleantalk'),
					'description' => __('Check buddyPress private messages.', 'cleantalk'),
				),
				'check_comments_number' => array(
					'title'       => __("Don't check trusted user's comments", 'cleantalk'),
					'description' => sprintf(__("Don't check comments for users with above %d comments.", 'cleantalk'), defined('CLEANTALK_CHECK_COMMENTS_NUMBER') ? CLEANTALK_CHECK_COMMENTS_NUMBER : 3),
				),
				'remove_old_spam' => array(
					'title'       => __('Automatically delete spam comments', 'cleantalk'),
					'description' => sprintf(__('Delete spam comments older than %d days.', 'cleantalk'),  $apbct->data['spam_store_days']),
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
					'description' => __('Options helps protect WordPress against spam with any caching plugins. Turn this option on to avoid issues with caching plugins.', 'cleantalk'),
				),
				'use_static_js_key' => array(
					'title'       => __('Use static keys for JS check.', 'cleantalk'),
					'description' => __('Could help if you have cache for AJAX requests and you are dealing with false positives. Slightly decreases protection quality. Auto - Static key will be used if caching plugin is spotted.', 'cleantalk'),
					'options' => array(
						array('val' => 1, 'label'  => __('On'),  ),
						array('val' => 0, 'label'  => __('Off'), ),
						array('val' => -1, 'label' => __('Auto'),),
					),
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
					'title'       => __('Use alternative mechanism for cookies', 'cleantalk'),
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
					'description' => __('Alternative way to connect the Cloud. Use this if you have connection problems.', 'cleantalk'),
				),
			),
		),
		
		// Exclusions
		'exclusions' => array(
			'title'          => __('Exclusions', 'cleantalk'),
			'fields'         => array(
				'exclusions__urls' => array(
					'type'        => 'text',
					'title'       => __('URL exclusions', 'cleantalk'),
					'description' => __('You could type here URL you want to exclude. Use comma as separator.', 'cleantalk'),
				),
				'exclusions__urls__use_regexp' => array(
					'type'        => 'checkbox',
					'title'       => __('Use Regular Expression in URL Exclusions', 'cleantalk'),
				),
				'exclusions__fields' => array(
					'type'        => 'text',
					'title'       => __('Field name exclusions', 'cleantalk'),
					'description' => __('You could type here fields names you want to exclude. Use comma as separator.', 'cleantalk'),
				),
				'exclusions__fields__use_regexp' => array(
					'type'        => 'checkbox',
					'title'       => __('Use Regular Expression in Field Exclusions', 'cleantalk'),
				),
				'exclusions__roles' => array(
					'type'                    => 'select',
					'multiple'                => true,
					'options_callback'        => 'apbct_get_all_roles',
					'options_callback_params' => array(true),
					'description'             => __('Roles which bypass spam test. Hold CTRL to select multiple roles.', 'cleantalk'),
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
					'type'                    => 'select',
					'multiple'                => true,
					'parent'                  => 'comment_notify',
					'options_callback'        => 'apbct_get_all_roles',
					'options_callback_params' => array(true),
					'class'                   => 'apbct_settings-field_wrapper--sub',
				),
				'complete_deactivation' => array(
					'type'        => 'checkbox',
					'title'       => __('Complete deactivation', 'cleantalk'),
					'description' => __('Leave no trace in the system after deactivation.', 'cleantalk'),
				),
			
			),
		),
	);
	
	return $fields;
}

function apbct_settings__set_fileds__network( $fields ){
	global $apbct;
	$additional_fields = array(
		'main' => array(
			'fields' => array(
				'white_label' => array(
					'type' => 'checkbox',
					'title' => __('Enable White Label Mode', 'cleantalk'),
					'description' => sprintf(__("Learn more information %shere%s.", 'cleantalk'), '<a tearget="_blank" href="https://cleantalk.org/ru/help/hosting-white-label">', '</a>'),
					'childrens' => array('white_label__hoster_key', 'white_label__plugin_name', 'allow_custom_key'),
					'network' => true,
				),
				'white_label__hoster_key' => array(
					'title' => __('Hoster API Key', 'cleantalk'),
					'description' => sprintf(__("You can get it in %sCleantalk's Control Panel%s", 'cleantalk'), '<a tearget="_blank" href="https://cleantalk.org/my/?cp_mode=hosting-antispam">', '</a>'),
					'type' => 'text',
					'parent' => 'white_label',
					'class' => 'apbct_settings-field_wrapper--sub',
					'network' => true,
					'required' => true,
				),
				'white_label__plugin_name' => array(
					'title' => __('Plugin name', 'cleantalk'),
					'description' => sprintf(__("Specify plugin name. Leave empty for deafult %sAntispam by Cleantalk%s", 'cleantalk'), '<b>', '</b>'),
					'type' => 'text',
					'parent' => 'white_label',
					'class' => 'apbct_settings-field_wrapper--sub',
					'network' => true,
					'required' => true,
				),
				'allow_custom_key' => array(
					'type'           => 'checkbox',
					'title'          => __('Allow users to use other key', 'cleantalk'),
					'description'    => __('Allow users to use different Access key in their plugin settings on child blogs. They could use different CleanTalk account.', 'cleantalk')
						. (defined('CLEANTALK_ACCESS_KEY')
							? ' <span style="color: red">'
							. __('Constant <b>CLEANTALK_ACCESS_KEY</b> is set. All websites will use API key from this constant. Look into wp-config.php', 'cleantalk')
							. '</span>'
							: ''
						),
					'display'        => APBCT_WPMS && is_main_site(),
					'disabled'       => $apbct->network_settings['white_label'],
					'network' => true,
				),
			)
		)
	);
	
	$fields = array_merge_recursive($fields, $additional_fields);
	
	return $fields;
	
}

function apbct_settings__add_groups_and_fields( $fields ){
	
	global $apbct;
	
	$apbct->settings_fields_in_groups = $fields;
	
	$field_default_params = array(
		'callback'        => 'apbct_settings__field__draw',
		'type'            => 'radio',
		'options' => array(
			array('val' => 1, 'label'  => __('On'),  'childrens_enable' => 1, ),
			array('val' => 0, 'label'  => __('Off'), 'childrens_enable' => 0, ),
		),
		'def_class'          => 'apbct_settings-field_wrapper',
		'class'              => '',
		'parent'             => '',
		'childrens'          => array(),
		'hide'               => array(),
		// 'title'           => 'Default title',
		// 'description'     => 'Default description',
		'display'            => true,  // Draw settings or not
		'reverse_trigger'    => false, // How to allow child settings. Childrens are opened when the parent triggered "ON". This is overrides by this option
		'multiple'           => false,
		'description'        => '',
		'network'            => false,
		'disabled'           => false,
		'required'           => false,
	);
	
	foreach($apbct->settings_fields_in_groups as $group_name => $group){
		
		add_settings_section('apbct_section__'.$group_name, '', 'apbct_section__'.$group_name, 'cleantalk');
		
		foreach($group['fields'] as $field_name => $field){
			
			// Normalize $field['options'] from callback function to this type  array( array( 'val' => 1, 'label'  => __('On'), ), )
			if(!empty($field['options_callback'])){
				$options = call_user_func_array($field['options_callback'], !empty($field['options_callback_params']) ? $field['options_callback_params'] : array());
				foreach ($options as &$option){
					$option = array('val' => $option, 'label' => $option);
				} unset($option);
				$field['options'] = $options;
			}
			
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
}

/**
 * Admin callback function - Displays plugin options page
 */
function apbct_settings__display() {
	
	global $apbct;		
		
		// Title
		echo '<h2 class="apbct_settings-title">'.__($apbct->plugin_name, 'cleantalk').'</h2>';

		// Subtitle for IP license
		if($apbct->moderate_ip)
			echo '<h4 class="apbct_settings-subtitle apbct_color--gray">'. __('Hosting AntiSpam', 'cleantalk').'</h4>';

		echo '<form action="options.php" method="post">';
		
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
					echo __('Plugin Homepage at', 'cleantalk').' <a href="https://cleantalk.org" target="_blank">cleantalk.org</a>.<br/>';
					echo '<span id="apbct_gdpr_open_modal" style="text-decoration: underline;">'.__('GDPR compliance', 'cleantalk').'</span><br/>';
					echo __('Use s@cleantalk.org to test plugin in any WordPress form.', 'cleantalk').'<br>';
					echo __('CleanTalk is registered Trademark. All rights reserved.', 'cleantalk').'<br/>';
					if($apbct->key_is_ok)
						echo '<b style="display: inline-block; margin-top: 10px;">'.sprintf(__('Do you like CleanTalk? %sPost your feedback here%s.', 'cleantalk'), '<a href="https://wordpress.org/support/plugin/cleantalk-spam-protect/reviews/#new-post" target="_blank">', '</a>').'</b><br />';
					apbct_admin__badge__get_premium();
					echo '<div id="gdpr_dialog" style="display: none; padding: 7px;">';
						apbct_settings_show_gdpr_text('print');
					echo '</div>';
				echo '</div>';
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
					echo '<a class="cleantalk_link cleantalk_link-manual" target="__blank" href="https://cleantalk.org/my?user_token='.$apbct->user_token.'&cp_mode=antispam">'
							.__('Click here to get anti-spam statistics', 'cleantalk')
						.'</a>';
					echo '&nbsp;&nbsp;';
					// Support button
					echo '<a class="cleantalk_link cleantalk_link-auto" target="__blank" href="https://wordpress.org/support/plugin/cleantalk-spam-protect">'.__('Support', 'cleantalk').'</a>';
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
			echo '<button name="submit" class="cleantalk_link cleantalk_link-manual" value="save_changes">'.__('Save Changes').'</button>';
		
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

function apbct_settings__display__network(){
	// If it's network admin dashboard
	if(is_network_admin()){
		$site_url = get_site_option('siteurl');
		$site_url = preg_match( '/\/$/', $site_url ) ? $site_url : $site_url . '/';
		$link = $site_url . 'wp-admin/options-general.php?page=cleantalk';
		printf("<h2>" . __("Please, enter the %splugin settings%s in main site dashboard.", 'cleantalk') . "</h2>", "<a href='$link'>", "</a>");
		return;
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
			'key_invalid' => __('Error occured while API key validating. Error: ', 'cleantalk'),
			'key_get' => __('Error occured while automatically gettings access key. Error: ', 'cleantalk'),
			'sfw_send_logs' => __('Error occured while sending sending SpamFireWall logs. Error: ', 'cleantalk'),
			'sfw_update' => __('Error occured while updating SpamFireWall local base. Error: '            , 'cleantalk'),
			'account_check' => __('Error occured while checking account status. Error: ', 'cleantalk'),
			'api' => __('Error occured while excuting API call. Error: ', 'cleantalk'),
			
			// Validating settings
			'settings_validate' => 'Validate Settings',
			'exclusions_urls' => 'URL Exclusions',
			'exclusions_fields' => 'Field Exclusions',
			
			// Unknown
			'unknown' => __('Unknown error. Error: ', 'cleantalk'),
		);
		
		$errors_out = array();
		
		foreach($errors as $type => $error){
			
			if(!empty($error)){
				
				if(is_array(current($error))){
					
					foreach($error as $sub_type => $sub_error){
						$errors_out[$sub_type] = '';
						if(isset($sub_error['error_time']))
							$errors_out[$sub_type] .= date('Y-m-d H:i:s', $sub_error['error_time']) . ': ';
						$errors_out[$sub_type] .= (isset($error_texts[$type])     ? $error_texts[$type]     : ucfirst($type)) . ': ';
						$errors_out[$sub_type] .= (isset($error_texts[$sub_type]) ? $error_texts[$sub_type] : $error_texts['unknown']) . ' ' . $sub_error['error'];
					}
					continue;
				}
				
				$errors_out[$type] = '';
				if(isset($error['error_time'])) 
					$errors_out[$type] .= date('Y-m-d H:i:s', $error['error_time']) . ': ';
				$errors_out[$type] .= (isset($error_texts[$type]) ? $error_texts[$type] : $error_texts['unknown']) . ' ' . (isset($error['error']) ? $error['error'] : '');
				
			}
		}
		
		if(!empty($errors_out)){
			$out .= '<div id="apbctTopWarning" class="error" style="position: relative;">'
				.'<h3 style="display: inline-block;">'.__('Errors:', 'cleantalk').'</h3>';
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
	
	global $apbct;
	
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
	
	if($apbct->moderate == 0){
		$img = $path_to_img."no.png";
		$img_no = $path_to_img."no.png";
		$color="black";
	}
	
	print '<div class="apbct_settings-field_wrapper" style="color:'.$color.'">';
	
		print '<h2>'.__('Protection is active', 'cleantalk').'</h2>';
	
	echo '<img class="apbct_status_icon" src="'.($apbct->settings['registrations_test'] == 1       ? $img : $img_no).'"/>'.__('Registration forms', 'cleantalk');
	echo '<img class="apbct_status_icon" src="'.($apbct->settings['comments_test']==1              ? $img : $img_no).'"/>'.__('Comments forms', 'cleantalk');
	echo '<img class="apbct_status_icon" src="'.($apbct->settings['contact_forms_test']==1         ? $img : $img_no).'"/>'.__('Contact forms', 'cleantalk');
	echo '<img class="apbct_status_icon" src="'.($apbct->settings['general_contact_forms_test']==1 ? $img : $img_no).'"/>'.__('Custom contact forms', 'cleantalk');
	if(!$apbct->white_label || is_main_site())
		echo '<img class="apbct_status_icon" src="'.($apbct->data['moderate'] == 1                     ? $img : $img_no).'"/>'
	        .'<a style="color: black" href="https://blog.cleantalk.org/real-time-email-address-existence-validation/">'.__('Validate email for existence', 'cleantalk').'</a>';
	
	// Autoupdate status
	if($apbct->notice_auto_update && (!$apbct->white_label || is_main_site())){
		echo '<img class="apbct_status_icon" src="'.($apbct->auto_update == 1 ? $img : ($apbct->auto_update == -1 ? $img_no : $img_no_gray)).'"/>'.__('Auto update', 'cleantalk')
		     .' <sup><a href="https://cleantalk.org/help/cleantalk-auto-update" target="_blank">?</a></sup>';
	}
	
	// WooCommerce
	if(class_exists('WooCommerce'))
		echo '<img class="apbct_status_icon" src="'.($apbct->settings['wc_checkout_test'] == 1  ? $img : $img_no).'"/>'.__('WooCommerce checkout form', 'cleantalk');
		if($apbct->moderate_ip)
			print "<br /><br />The anti-spam service is paid by your hosting provider. License #".$apbct->data['ip_license'].".<br />";
	
	print "</div>";
}

/**
 * Admin callback function - Displays inputs of 'apikey' plugin parameter
 */
function apbct_settings__field__apikey(){
	
	global $apbct;
	
	echo '<div id="cleantalk_apikey_wrapper" class="apbct_settings-field_wrapper">';
	
		// Using key from Main site, or from CLEANTALK_ACCESS_KEY constant
		if(APBCT_WPMS && !is_main_site() && (!$apbct->allow_custom_key || defined('CLEANTALK_ACCESS_KEY'))){
			_e('<h3>Key is provided by Super Admin.</h3>', 'cleantalk');
			return;
		}
		
		echo '<label class="apbct_settings__label" for="cleantalk_apkey">' . __('Access key', 'cleantalk') . '</label>';
		
		echo '<input
			id="apbct_setting_apikey"
			class="apbct_setting_text apbct_setting---apikey"
			type="text"
			name="cleantalk_settings[apikey]"
			value="'
				. ($apbct->key_is_ok
					? str_repeat('*', strlen($apbct->api_key))
					: $apbct->api_key
				)
				. '"
			key="' . $apbct->api_key . '"
			size="20"
			placeholder="' . __('Enter the key', 'cleantalk') . '"'
			. ' />';
		
		// Show account name associated with key
		if(!empty($apbct->data['account_name_ob'])){
			echo '<div class="apbct_display--none">'
				. sprintf( __('Account at cleantalk.org is %s.', 'cleantalk'),
					'<b>'.$apbct->data['account_name_ob'].'</b>'
				)
				. '</div>';
		};
		
		// Show key button
		if((apbct_api_key__is_correct($apbct->api_key) && $apbct->key_is_ok)){
			echo '<a id="apbct_showApiKey" class="ct_support_link" style="display: block" href="#">'
				. __('Show the access key', 'cleantalk')
			. '</a>';
			
		// "Auto Get Key" buttons. License agreement
		}else{
			
			echo '<br /><br />';
			
			// Auto get key
			if(!$apbct->ip_license){
				echo '<button class="cleantalk_link cleantalk_link-manual apbct_setting---get_key_auto" name="submit" type="submit"  value="get_key_auto">'
					.__('Get Access Key Automatically', 'cleantalk')
				.'</button>';
				echo '<input type="hidden" id="ct_admin_timezone" name="ct_admin_timezone" value="null" />';
				echo '<br />';
				echo '<br />';
			}
			
			// Warnings and GDPR
			printf( __('Admin e-mail (%s) will be used for registration, if you want to use other email please %sGet Access Key Manually%s.', 'cleantalk'),
				ct_get_admin_email(),
				'<a class="apbct_color--gray" target="__blank" href="'
					. sprintf( 'https://cleantalk.org/register?platform=wordpress&email=%s&website=%s',
						urlencode(ct_get_admin_email()),
						urlencode(parse_url(get_option('siteurl'),PHP_URL_HOST))
					)
					. '">',
				'</a>'
			);
			
			// License agreement
			if(!$apbct->ip_license){
				echo '<div>';
					echo '<input checked type="checkbox" id="license_agreed" onclick="apbctSettingsDependencies(\'apbct_setting---get_key_auto\');"/>';
					echo '<label for="spbc_license_agreed">';
						printf( __('I accept %sLicense Agreement%s.', 'cleantalk'),
							'<a class = "apbct_color--gray" href="https://cleantalk.org/publicoffer" target="_blank">',
							'</a>'
						);
					echo "</label>";
				echo '</div>';
			}
		}
	
	echo '</div>';
}

function apbct_settings__field__action_buttons(){
	
	global $apbct;
	
	echo '<div class="apbct_settings-field_wrapper">';
	
		if(apbct_api_key__is_correct($apbct->api_key) && $apbct->key_is_ok){
			echo '<div>'
				.'<a href="edit-comments.php?page=ct_check_spam" class="ct_support_link">' . __('Check comments for spam', 'cleantalk') . '</a>'
				.'&nbsp;&nbsp;'
				.'&nbsp;&nbsp;'
				.'<a href="users.php?page=ct_check_users" class="ct_support_link">' . __('Check users for spam', 'cleantalk') . '</a>'
				.'&nbsp;&nbsp;'
				.'&nbsp;&nbsp;'
				.'<a href="#" class="ct_support_link" onclick="apbct_show_hide_elem(\'apbct_statistics\')">' . __('Statistics & Reports', 'cleantalk') . '</a>'
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
			__('Last time SpamFireWall was triggered for %s IP at %s', 'cleantalk'),
			$apbct->stats['last_sfw_block']['ip'] ? $apbct->stats['last_sfw_block']['ip'] : __('unknown', 'cleantalk'),
			$apbct->stats['last_sfw_block']['time'] ? date('M d Y H:i:s', $apbct->stats['last_sfw_block']['time']) : __('unknown', 'cleantalk')
		);
		echo '<br>';

		// SFW last update
		$sfw_netwoks_amount = $wpdb->get_results("SELECT count(*) AS cnt FROM `".$wpdb->prefix."cleantalk_sfw`", ARRAY_A);
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
						. ' class="cleantalk_link cleantalk_link-manual"'
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

/**
 * Get all current Wordpress roles, could except 'subscriber' role
 *
 * @param bool $except_subscriber
 *
 * @return array
 */
function apbct_get_all_roles($except_subscriber = false) {
	
	global $wp_roles;
	
	$wp_roles = new WP_Roles();
	$roles = $wp_roles->get_names();
	
	if($except_subscriber) {
		$key = array_search( 'Subscriber', $roles );
		if ( $key !== false ) {
			unset( $roles[ $key ] );
		}
	}
	
	return $roles;
}

function apbct_settings__field__draw($params = array()){
	
	global $apbct;
	
	$value        = $params['network'] ? $apbct->network_settings[$params['name']]   : $apbct->settings[$params['name']];
	$value_parent = $params['parent']
		? ($params['network'] ? $apbct->network_settings[$params['parent']] : $apbct->settings[$params['parent']])
		: false;
	
	$disabled = $params['parent'] && !$value_parent ? ' disabled="disabled"' : '';
	$disabled = $params['disabled']                 ? ' disabled="disabled"' : $disabled;
	
	$childrens =  $params['childrens'] ? 'apbct_setting---' . implode(",apbct_setting---",$params['childrens']) : '';
	$hide      =  $params['hide']      ? implode(",",$params['hide'])      : '';
	
	echo '<div class="'.$params['def_class'].(isset($params['class']) ? ' '.$params['class'] : '').'">';
	
		switch($params['type']){
			
			// Checkbox type
			case 'checkbox':
				echo '<input
					type="checkbox"
					name="cleantalk_settings['.$params['name'].']"
					id="apbct_setting_'.$params['name'].'"
					value="1" '
					." class='apbct_setting_{$params['type']} apbct_setting---{$params['name']}'"
					.($value == '1' ? ' checked' : '')
					.$disabled
					.($params['required'] ? ' required="required"' : '')
					.' onchange="'
						. ($params['childrens'] ? ' apbctSettingsDependencies(\''. $childrens .'\');' : '')
						. ($params['hide']      ? ' apbct_show_hide_elem(\''. $hide . '\');' : '')
						. '"'
					.' />'
					.'<label for="apbct_setting_'.$params['name'].'" class="apbct_setting-field_title--'.$params['type'].'">'
						.$params['title']
					.'</label>';
				echo isset($params['long_description'])
					? '<i setting="'.$params['name'].'" class="apbct_settings-long_description---show icon-help-circled"></i>'
					: '';
				echo '<div class="apbct_settings-field_description">'
					.$params['description']
				.'</div>';				
				break;
			
			// Radio type
			case 'radio':
				
				// Title
				echo isset($params['title'])
					? '<h4 class="apbct_settings-field_title apbct_settings-field_title--'.$params['type'].'">'.$params['title'].'</h4>'
					: '';
				
				// Popup description
				echo isset($params['long_description'])
					? '<i setting="'.$params['name'].'" class="apbct_settings-long_description---show icon-help-circled"></i>'
					: '';
				
				echo '<div class="apbct_settings-field_content apbct_settings-field_content--'.$params['type'].'">';
					
					$disabled = '';
					
					// Disable child option if parent is ON
					if($params['reverse_trigger']){
						if($params['parent'] && $apbct->settings[$params['parent']]){
							$disabled = ' disabled="disabled"';
						}
						
					// Disable child option if parent if OFF
					}else{
						if($params['parent'] && !$apbct->settings[$params['parent']]){
							$disabled = ' disabled="disabled"';
						}
					}
				
					foreach($params['options'] as $option){
						echo '<input'
						     .' type="radio"'
						     ." class='apbct_setting_{$params['type']} apbct_setting---{$params['name']}'"
						     ." id='apbct_setting_{$params['name']}__{$option['label']}'"
						     .' name="cleantalk_settings['.$params['name'].']"'
						     .' value="'.$option['val'].'"'
						     .($params['parent'] ? $disabled : '')
						     .($params['childrens']
								? ' onchange="apbctSettingsDependencies(\'' . $childrens . '\', ' . $option['childrens_enable'] . ')"'
								: ''
						     )
						     .($value == $option['val'] ? ' checked' : '')
							 .($params['required'] ? ' required="required"' : '')
						.' />';
				        echo '<label for="apbct_setting_'.$params['name'].'__'.$option['label'].'"> ' . $option['label'] . '</label>';
						echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					}
				
					echo isset($params['description'])
						? '<div class="apbct_settings-field_description">'.$params['description'].'</div>'
						: '';
					
				echo '</div>';
				break;
			
			// Dropdown list type
			case 'select':
				echo isset($params['title'])
					? '<h4 class="apbct_settings-field_title apbct_settings-field_title--'.$params['type'].'">'.$params['title'].'</h4>'
					: '';
				echo isset($params['long_description'])
					? '<i setting="'.$params['name'].'" class="apbct_settings-long_description---show icon-help-circled"></i>'
					: '';
				echo '<select'
				    . ' id="apbct_setting_'.$params['name'].'"'
					. " class='apbct_setting_{$params['type']} apbct_setting---{$params['name']}'"
			        . ' name="cleantalk_settings['.$params['name'].']'.($params['multiple'] ? '[]"' : '"')
			        . ($params['multiple'] ? ' size="'. count($params['options']). '""' : '')
					. ($params['multiple'] ? ' multiple="multiple"' : '')
			        . $disabled
					. ($params['required'] ? ' required="required"' : '')
					. ' >';
				
					foreach($params['options'] as $option){
						echo '<option'
							. ' value="' . $option['val'] . '"'
							. ($params['multiple']
								? (in_array($option['val'], $value) ? ' selected="selected"' : '')
							    : ($value == $option['val']         ?  'selected="selected"' : '')
							)
							.'>'
								. $option['label']
							. '</option>';
					}
					
				echo '</select>';
				echo isset($params['long_description'])
					? '<i setting="'.$params['name'].'" class="apbct_settings-long_description---show icon-help-circled"></i>'
					: '';
				echo isset($params['description'])
					? '<div class="apbct_settings-field_description">'.$params['description'].'</div>'
					: '';
				
				break;
				
			// Text type
			case 'text':
				
				echo '<input
					type="text"
					id="apbct_setting_'.$params['name'].'"
					name="cleantalk_settings['.$params['name'].']"'
					." class='apbct_setting_{$params['type']} apbct_setting---{$params['name']}'"
					.' value="'. $value .'" '
					.$disabled
					.($params['required'] ? ' required="required"' : '')
					.($params['childrens'] ? ' onchange="apbctSettingsDependencies(\'' . $childrens . '\')"' : '')
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
	
	// Set missing settings.
	foreach($apbct->def_network_settings as $setting => $value){
		if(!isset($settings[$setting])){
			$settings[$setting] = null;
			settype($settings[$setting], gettype($value));
		}
	} unset($setting, $value);
	
	// Validating API key
	$settings['apikey'] = !empty($settings['apikey'])                        ? trim($settings['apikey'])  : '';
	$settings['apikey'] = defined('CLEANTALK_ACCESS_KEY')             ? CLEANTALK_ACCESS_KEY       : $settings['apikey'];
	$settings['apikey'] = is_main_site() || $apbct->allow_custom_key         ? $settings['apikey']        : $apbct->network_settings['apikey'];
	$settings['apikey'] = is_main_site() || !$settings['white_label']        ? $settings['apikey']        : $apbct->settings['apikey'];
	$settings['apikey'] = strpos($settings['apikey'], '*') === false ? $settings['apikey']        : $apbct->settings['apikey'];
	
	// Validate Exclusions
	// URLs
	$result  = apbct_settings__sanitize__exclusions($settings['exclusions__urls'],   $settings['exclusions__urls__use_regexp']);
	$result === false
		? $apbct->error_add( 'exclusions_urls', 'is not valid: "' . $settings['exclusions__urls'] . '"', 'settings_validate' )
		: $apbct->error_delete( 'exclusions_urls', true, 'settings_validate' );
	$settings['exclusions__urls'] = $result ? $result: '';
	
	// Fields
	$result  = apbct_settings__sanitize__exclusions($settings['exclusions__fields'],   $settings['exclusions__fields__use_regexp']);
	$result === false
		? $apbct->error_add( 'exclusions_fields', 'is not valid: "' . $settings['exclusions__fields'] . '"', 'settings_validate' )
		: $apbct->error_delete( 'exclusions_fields', true, 'settings_validate' );
	$settings['exclusions__fields'] = $result ? $result: '';
	
	// WPMS Logic.
	if(APBCT_WPMS && is_main_site()){
		$network_settings = array(
			'allow_custom_key'         => $settings['allow_custom_key'],
			'white_label'              => $settings['white_label'],
			'white_label__hoster_key'  => $settings['white_label__hoster_key'],
			'white_label__plugin_name' => $settings['white_label__plugin_name'],
		);
		unset( $settings['allow_custom_key'], $settings['white_label'], $settings['white_label__hoster_key'], $settings['white_label__plugin_name'] );
	}
	
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
		$user_ip        = CleantalkHelper::ip__get(array('real'), false);
		$timezone       = filter_input(INPUT_POST, 'ct_admin_timezone');
		$language       = apbct_get_server_variable( 'HTTP_ACCEPT_LANGUAGE' );
		$wpms           = APBCT_WPMS && defined('SUBDOMAIN_INSTALL') && !SUBDOMAIN_INSTALL ? true : false;
		$white_label    = $apbct->network_settings['white_label']             ? 1                                                   : 0;
		$hoster_api_key = $apbct->network_settings['white_label__hoster_key'] ? $apbct->network_settings['white_label__hoster_key'] : '';
		
		$result = CleantalkAPI::method__get_api_key(
			'antispam',
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
			$apbct->error_add(
				'key_get',
				$result['error']
				. ($apbct->white_label
					? ' <button name="submit" type="submit" class="cleantalk_link cleantalk_link-manual" value="get_key_auto">'
					: ''
				)
			);
		}
	}
	
	// Feedback with app_agent
	ct_send_feedback('0:' . APBCT_AGENT); // 0 - request_id, agent version.
	
	// Key is good by default
	$apbct->data['key_is_ok'] = true;
	
	// Check account status and validate key. Even if it's not correct because of IP license.
	$result = ct_account_status_check($settings['apikey']);
	
	// Is key valid?
	if($result){
	
		// Deleting errors about invalid key
		$apbct->error_delete('key_invalid key_get', 'save');
	
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
	
	// WPMS Logic.
	if(APBCT_WPMS){
		if(is_main_site()){
			
			// Network settings
			$network_settings['apikey'] = $settings['apikey'];
			$apbct->network_settings = $network_settings;
			$apbct->saveNetworkSettings();
			
			// Network data
			$apbct->network_data = array(
				'key_is_ok'   => $apbct->data['key_is_ok'],
				'moderate'    => $apbct->data['moderate'],
				'valid'       => $apbct->data['valid'],
				'auto_update' => $apbct->data['auto_update'],
				'user_token'  => $apbct->data['user_token'],
				'service_id'  => $apbct->data['service_id'],
			);
			$apbct->saveNetworkData();
		}
		if(!$apbct->white_label && !is_main_site() && !$apbct->allow_custom_key){
			$settings['apikey'] = '';
		}
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

/**
 * Sanitize and validate exclusions.
 * Explode given string by commas and trim each string.
 * Skip element if it's empty.
 *
 * Return false if exclusion is bad
 * Return sanitized string if all is ok
 *
 * @param string $exclusions
 * @param bool   $regexp
 *
 * @return bool|string
 */
function apbct_settings__sanitize__exclusions($exclusions, $regexp = false){
	$result = array();
	if( ! empty( $exclusions ) ){
		$exclusions = explode( ',', $exclusions );
		foreach ( $exclusions as $exclusion ){
			$sanitized_exclusion = trim( $exclusion );
			if ( ! empty( $sanitized_exclusion ) ) {
				if( $regexp && ! apbct_is_regexp( $exclusion ) )
					return false;
				$result[] = $sanitized_exclusion;
			}
		}
	}
	return implode( ',', $result );
}

function apbct_settings_show_gdpr_text($print = false){
	
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

function apbct_settings__get__long_description(){
	
	global $apbct;
	
	check_ajax_referer('ct_secret_nonce' );
	
	$setting_id = $_POST['setting_id'] ? $_POST['setting_id'] : '';
	
	$descriptions = array(
		'white_label'              => array(
			'title' => __( 'XSS check', 'cleantalk' ),
			'desc'  => __( 'Cross-Site Scripting (XSS) — prevents malicious code to be executed/sent to any user. As a result malicious scripts can not get access to the cookie files, session tokens and any other confidential information browsers use and store. Such scripts can even overwrite content of HTML pages. CleanTalk WAF monitors for patterns of these parameters and block them.', 'cleantalk' ),
		),
		'white_label__hoster_key'  => array(
			'title' => __( 'SQL-injection check', 'cleantalk' ),
			'desc'  => __( 'SQL Injection — one of the most popular ways to hack websites and programs that work with databases. It is based on injection of a custom SQL code into database queries. It could transmit data through GET, POST requests or cookie files in an SQL code. If a website is vulnerable and execute such injections then it would allow attackers to apply changes to the website\'s MySQL database.', 'cleantalk' ),
		),
		'white_label__plugin_name' => array(
			'title' => __( 'Check uploaded files', 'cleantalk' ),
			'desc'  => __( 'The option checks each uploaded file to a website for malicious code. If it\'s possible for visitors to upload files to a website, for instance a work resume, then attackers could abuse it and upload an infected file to execute it later and get access to your website.', 'cleantalk' ),
		),
	);
	
	die(json_encode($descriptions[$setting_id]));
}