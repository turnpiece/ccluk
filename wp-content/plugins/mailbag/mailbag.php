<?php
/*
Plugin Name: Mailbag
Plugin URI: http://wordpress.org/plugins/mailbag/
Description: Add MailChimp or Campaign Monitor email forms to your website with a simple shortcode.
Version: 1.4
Author: Array
Author URI: https://www.array.is
*/


// -------------- Setup action and filter hooks -------------- //
register_uninstall_hook( __FILE__, 'mailbag_delete_plugin_options' );
add_action( 'admin_init', 'mailbag_init' );
add_action( 'admin_menu', 'mailbag_add_options_page' );


// -------------- Localization -------------- //
function mailbag_load_textdomain() {
	load_plugin_textdomain( 'mailbag', false, dirname( plugin_basename( __FILE__ ) ) . '/includes/languages/' );
}
add_action( 'init', 'mailbag_load_textdomain' );


// -------------- Delete options upon deactivation and delete -------------- //
function mailbag_delete_plugin_options() {
	delete_option( 'mailbag_options' );
}


// -------------- Register plugin options -------------- //
function mailbag_init() {
	register_setting( 'mailbag_plugin_options', 'mailbag_options' );
}


// -------------- Add menu page -------------- //
function mailbag_add_options_page() {
	global $mailbag_options;
	$mailbag_options = add_options_page( __( 'Mailbag Settings', 'mailbag' ), __( 'Mailbag', 'mailbag' ), 'manage_options', __FILE__, 'mailbag_render_form' );
}


// -------------- Add settings link -------------- //
function mailbag_settings_link( $links ) {
  $settings_link = '<a href="options-general.php?page=mailbag/mailbag.php">Settings</a>';
  array_unshift( $links, $settings_link );
  return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_$plugin", 'mailbag_settings_link' );


// -------------- Allow shortcodes in widgets -------------- //
add_filter('widget_text', 'do_shortcode');


// -------------- Enqueue admin scripts -------------- //
function mailbag_load_admin_scripts( $hook ) {
	global $mailbag_options;

	if( $hook != $mailbag_options )
		return;

	//Register and enqueue custom admin stylesheet
	wp_register_style( 'mailbag_admin_css', plugin_dir_url(__FILE__) . 'includes/css/admin-style.css', false, '1.0.0' );
	wp_enqueue_style( 'mailbag_admin_css' );

	//Register and enqueue custom admin scripts
	wp_register_script('mailbag_js', plugin_dir_url(__FILE__) . 'includes/js/settings.js', array('jquery','media-upload','thickbox'));
	wp_enqueue_script('mailbag_js');

	//UI Tabs
	wp_enqueue_script( 'jquery-ui-tabs' );

}
add_action( 'admin_enqueue_scripts', 'mailbag_load_admin_scripts' );


/* Enqueue scripts and styles */
function mailbag_load_styles() {
	global $mailbag_options;

	$mailbag_options = get_option('mailbag_options');

	if ($mailbag_options['enable_styles'] == 'enabled') {
		//Form styles
		wp_register_style( 'mailbag_form_css', plugin_dir_url(__FILE__) . 'includes/css/form-style.css', false, '1.0.0' );
		wp_enqueue_style( 'mailbag_form_css' );
	}

}
add_action( 'wp_enqueue_scripts', 'mailbag_load_styles' );


/**
 * Enqueue scripts and styles
 */
function mailbag_load_frontend_scripts() {
	// ajaxChimp
	wp_enqueue_script( 'mailbag_ajaxChimp', plugin_dir_url(__FILE__) . 'includes/js/jquery.ajaxchimp.js', '2.2.1', true );
	wp_enqueue_script( 'mailbag-front-js', plugin_dir_url(__FILE__) . 'includes/js/mailbag.js', '2.2.1', true );

	$formURL = mailbag_get_ajax_url();

	wp_localize_script( 'mailbag-front-js', 'mailbag_js_vars', array(
			'ajaxURL' => $formURL
		)
	);
}
add_action( 'wp_enqueue_scripts', 'mailbag_load_frontend_scripts' );


// -------------- Build the options form -------------- //
function mailbag_render_form() {
?>
	<?php $mailbag_options = get_option('mailbag_options'); ?>

	 <!-- Create a header in the default WordPress 'wrap' container -->
    <div class="wrap">
        <img class="logo-icon" src="<?php echo plugins_url( 'mailbag/includes/images/icon.png' , dirname(__FILE__) ); ?>" alt="<?php _e('Mailbag','mailbag'); ?>" />
        <h2 class="logo-text"><?php _e('Mailbag Settings','mailbag'); ?></h2>

		<div id="tabs">
			<ul class="nav-tab-wrapper">
				<li><h2><a class="nav-tab" href="#mc"><span><?php _e('MailChimp','mailbag'); ?></span></a></h2></li>
				<li><h2><a class="nav-tab" href="#cm"><span><?php _e('Campaign Monitor','mailbag'); ?></span></a></h2></li>
				<li><h2><a class="nav-tab" href="#styles"><span><?php _e('Settings','mailbag'); ?></span></a></h2></li>
				<li><h2><a class="nav-tab" href="#usage"><span><?php _e('Usage','mailbag'); ?></span></a></h2></li>
			</ul>

			<form class="mailbag-settings" method="post" action="options.php">
				<?php settings_fields( 'mailbag_plugin_options' ); ?>

				<div id="mc" class="tab-content">
					<div class="settings-field">
						<div class="inside">
							<!-- MailChimp API -->
							<div class="setting">
								<h3><?php _e('MailChimp API Key','mailbag'); ?></h3>

								<div class="options">
									<input type="text" id="mailbag_options[mailchimp_api]" name="mailbag_options[mailchimp_api]" value="<?php if(isset($mailbag_options['mailchimp_api'])) { echo $mailbag_options['mailchimp_api']; } ?>"/>
								</div>
							</div><!-- setting -->

							<!-- MailChimp Lists -->
							<div class="setting" >
								<h3><?php _e('MailChimp Email Lists','mailbag'); ?></h3>

								<div class="options">
									<?php $lists = mailbag_get_mailchimp_lists(); ?>
									<select id="mailbag_options[mailchimp_list]" name="mailbag_options[mailchimp_list]">
										<?php
											if($lists) :
												foreach($lists as $list) :
													echo '<option value="' . $list['id'] . '"' . selected($mailbag_options['mailchimp_list'], $list['id'], false) . '>' . $list['name'] . '</option>';
												endforeach;
											else :
										?>
										<option value="no list"><?php _e('No lists', 'mailbag'); ?></option>
									<?php endif; ?>
									</select>
									<?php if (!$mailbag_options['mailchimp_api']) { ?>
										<small><?php _e('You must save your API key before you can select a list.','mailbag'); ?></small>
									<?php } ?>
								</div>
							</div><!-- setting -->

							<!-- MailChimp Form Text -->
							<div class="setting hide-setting">
								<h3><?php _e('Form Text','mailbag'); ?></h3>

								<div class="options">
									<input type="text" id="mailbag_options[mailchimp_form_text]" name="mailbag_options[mailchimp_form_text]" value="<?php if(isset($mailbag_options['mailchimp_form_text'])) { echo $mailbag_options['mailchimp_form_text']; } ?>"/>
								</div>
								<small><?php _e('Add optional intro text above your subscribe form.','mailbag'); ?></small>
							</div><!-- setting -->

							<!-- MailChimp Button -->
							<div class="setting">
								<h3><?php _e('Button Text','mailbag'); ?></h3>

								<div class="options">
									<input type="text" id="mailbag_options[mailchimp_button]" name="mailbag_options[mailchimp_button]" value="<?php if(isset($mailbag_options['mailchimp_button'])) { echo $mailbag_options['mailchimp_button']; } ?>"/>
								</div>
							</div><!-- setting -->
						</div><!-- inside -->
					</div><!-- settings-field -->
				</div><!-- mc -->

				<div id="cm" class="tab-content">
					<div class="settings-field">
						<div class="inside">
							<!-- Campaign Monitor API -->
							<div class="setting">
								<h3><?php _e('Campaign Monitor API Key','mailbag'); ?></h3>

								<div class="options">
									<input type="text" id="mailbag_options[campaign_api]" name="mailbag_options[campaign_api]" value="<?php if(isset($mailbag_options['campaign_api'])) { echo esc_attr($mailbag_options['campaign_api']); } ?>"/>
								</div>
							</div><!-- setting -->

							<!-- Campaign Monitor Client ID -->
							<div class="setting">
								<h3><?php _e('Campaign Monitor Client ID','mailbag'); ?></h3>

								<div class="options">
									<input type="text" id="mailbag_options[campaign_client_id]" name="mailbag_options[campaign_client_id]" value="<?php if(isset($mailbag_options['campaign_client_id'])) { echo esc_attr($mailbag_options['campaign_client_id']); } ?>"/>
								</div>
							</div><!-- setting -->

							<!-- Campaign Monitor Lists -->
							<div class="setting">
								<h3><?php _e('Campaign Monitor Email Lists','mailbag'); ?></h3>

								<div class="options">
									<?php $lists = mailbag_get_campaign_monitor_lists(); ?>
									<select id="mailbag_options[campaign_list]" name="mailbag_options[campaign_list]">
										<?php
											if($lists) :
												foreach($lists as $id => $list_name) :
													echo '<option value="' . $id . '"' . selected($mailbag_options['campaign_list'], $id, false) . '>' . $list_name . '</option>';
												endforeach;
											else :
										?>
										<option value="no list"><?php _e('No lists', 'mailbag'); ?></option>
									<?php endif; ?>
									</select>
									<?php if (!$mailbag_options['campaign_api']) { ?>
										<small><?php _e('You must save your API key before you can select a list.','mailbag'); ?></small>
									<?php } ?>
								</div>
							</div><!-- setting -->

							<!-- Campaign Form Text -->
							<div class="setting hide-setting">
								<h3><?php _e('Form Text','mailbag'); ?></h3>

								<div class="options">
									<input type="text" id="mailbag_options[campaign_form_text]" name="mailbag_options[campaign_form_text]" value="<?php if(isset($mailbag_options['campaign_form_text'])) { echo $mailbag_options['campaign_form_text']; } ?>"/>
									<small><?php _e('Add optional  intro text above your subscribe form.','mailbag'); ?></small>
								</div>
							</div><!-- setting -->

							<!-- Campaign Monitor Button -->
							<div class="setting">
								<h3><?php _e('Button Text','mailbag'); ?></h3>

								<div class="options">
									<input type="text" id="mailbag_options[campaign_button]" name="mailbag_options[campaign_button]" value="<?php if(isset($mailbag_options['campaign_button'])) { echo $mailbag_options['campaign_button']; } ?>"/>
								</div>
							</div><!-- setting -->
						</div><!-- inside -->
					</div><!-- settings-field -->
				</div><!-- cm -->

				<div id="styles" class="tab-content">
					<div class="settings-field">
						<div class="inside">
							<!-- General Setting -->
							<div class="setting">
								<h3><?php _e('Enable/Disable Mailbag Form Styles','mailbag'); ?></h3>

								<?php
									if(isset($mailbag_options['enable_styles'])) {
										$selected = $mailbag_options['enable_styles'];
									} else {
										$selected = __( 'enabled', 'mailbag' );
									}
								?>

								<div class="options">
									<select name='mailbag_options[enable_styles]'>
										<option value='enabled' <?php selected('enabled', $selected); ?>><?php _e('Enable','mailbag'); ?></option>
										<option value='disabled' <?php selected('disabled', $selected); ?>><?php _e('Disable','mailbag'); ?></option>
									</select>
								</div>
							</div><!-- setting -->
						</div><!-- inside -->
					</div><!-- setting-field -->
				</div><!-- styles -->

				<div id="usage" class="tab-content">
					<div class="settings-field">
						<div class="inside">
							<!-- General Setting -->
							<div class="setting">
								<h3><?php _e('Adding Forms To Your Site','mailbag'); ?></h3>

								<p><?php _e('Once you have entered your keys and chosen a subscription list, you can add the form to any of your posts, pages or widgets with the following shortcodes.','mailbag'); ?></p>

								<p><?php _e('MailChimp Shortcode:','mailbag'); ?> <code>[mailbag_mailchimp]</code></p>
								<p><?php _e('Campaign Monitor Shortcode:','mailbag'); ?> <code>[mailbag_campaign_monitor]</code></p>

								<hr />

								<h3><?php _e('Finding Your MailChimp API Keys','mailbag'); ?></h3>
								<p><a href="http://kb.mailchimp.com/article/where-can-i-find-my-api-key"><?php _e('Where can I find my API key?','mailbag'); ?></a></p>

								<hr />

								<h3><?php _e('Finding Your Campaign Monitor API Keys','mailbag'); ?></h3>
								<p><a href="http://help.campaignmonitor.com/topic.aspx?t=206"><?php _e('Where can I find my API key?','mailbag'); ?></a></p>
								<p><a href="http://www.campaignmonitor.com/api/getting-started/#clientid"><?php _e('Where can I find my Client ID?','mailbag'); ?></a></p>

								<hr />

								<h3><?php _e('Customizing With CSS','mailbag'); ?></h3>

								<p><?php _e('Mailbag ships with a clean and simple default style, which you can optionally disable in the Settings tab. Use these CSS classes to customize the email forms to your liking.','mailbag'); ?></p>

								<p><code>.mailbag-wrap { /* Wraps entire form */ }</code></p>

								<p><code>.mailbag-wrap label { /* Label styles */ }</code></p>

								<p><code>.mailbag-wrap input[type="text"] { /* Name and email input styles */ }</code></p>

								<p><code>.mailbag-wrap input[type="submit"] { /* Submit button styles */ }</code></p>
							</div><!-- setting -->
						</div><!-- inside -->
					</div><!-- setting-field -->
				</div><!-- usage -->

				<div id="submit-options">
					<?php echo submit_button( __( 'Save Settings', 'mailbag' ) ); ?>
				</div>
			</form>
		</div><!-- tabs -->

    </div><!-- wrap -->

	<?php
}


// -------------- Display MailChimp signup list -------------- //
function mailbag_mailchimp_form( $redirect ) {

	$mailbag_options = get_option( 'mailbag_options' );

	ob_start();

	if( isset( $_GET['sent'] ) && $_GET['sent'] == 1 ) {

		echo '<p class="success-subscribe">' . __('You have been successfully subscribed!', 'mailbag') . '</p>';

	} else {

	if(strlen(trim($mailbag_options['mailchimp_api'])) > 0 ) { ?>

		<?php
			$mc_button_text = __( 'Subscribe', 'mailbag' );

			if(isset($mailbag_options['mailchimp_button'])) {
				$mc_button_text = $mailbag_options['mailchimp_button'];
			}

			if(isset($mailbag_options['mailchimp_form_text'])) {
				$mc_form_text = $mailbag_options['mailchimp_form_text'];
			}
		?>

		<form id="mailbag_mailchimp" class="mailbag-wrap" action="/" method="post">
			<fieldset>
				<?php if ( ! empty( $mc_form_text ) ) { ?>
					<legend><?php echo $mc_form_text; ?></legend>
				<?php } ?>

				<div class="mailbag-input">
					<label for="mailbag_mailchimp_email"><?php _e('Enter your email:', 'mailbag'); ?></label>
					<input name="mailbag_mailchimp_email" id="mailbag_mailchimp_email" type="email" placeholder="<?php _e('Email address', 'mailbag'); ?>" />
				</div>
				<div class="mailbag-input">
					<input type="hidden" name="redirect" value="<?php echo $redirect; ?>"/>
					<input type="hidden" name="action" value="mailbag_mailchimp"/>
					<input class="button" type="submit" value="<?php if ( $mc_button_text ) { echo $mc_button_text; } else { _e('Subscribe', 'mailbag'); } ?>"/>
				</div>
			</fieldset>
		</form>
		<?php
	}
	}
	return ob_get_clean();
}


// -------------- Add MailChimp shortcode -------------- //
function mailbag_mailchimp_form_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'redirect' => ''
	), $atts ) );

	if($redirect == '') {
		$redirect = home_url();
	}
	return mailbag_mailchimp_form($redirect);
}
add_shortcode('mailbag_mailchimp', 'mailbag_mailchimp_form_shortcode');


// -------------- Get MailChimp subscription lists -------------- //
function mailbag_get_mailchimp_lists() {

	$mailbag_options = get_option('mailbag_options');

	// check that an API key has been entered
	if(strlen(trim($mailbag_options['mailchimp_api'])) > 0 ) {

		// setup the $lists variable as a blank array
		$lists = array();

		// load the Mail Chimp API class
		require_once('mailchimp/MCAPI.class.php');

		// load a new instance of the API class with our API key
		$api = new MCAPI($mailbag_options['mailchimp_api']);

		// retrieve an array of all email list data
		$list_data = $api->lists();

		//var_dump($list_data);

		// if at least one list was retrieved
		if($list_data) :
			// loop through each list
			foreach($list_data['data'] as $key => $list) :
				// store the list ID in our array ID key
				$lists[$key]['id'] = $list['id'];
				// store the list name our array NAME key
				$lists[$key]['name'] = $list['name'];
			endforeach;
		endif;
		// return an array of the lists with ID and name
		return $lists;
	}
	return false;
}


// -------------- Get MailChimp AJAX link -------------- //
function mailbag_get_ajax_url() {

	$mailbag_options = get_option('mailbag_options');

	// check that an API key has been entered
	if(strlen(trim($mailbag_options['mailchimp_api'])) > 0 ) {

		// setup the $lists variable as a blank array
		$lists = array();

		// load the MailChimp API class
		require_once('mailchimp/MCAPI.class.php');

		// load a new instance of the API class with our API key
		$api = new MCAPI($mailbag_options['mailchimp_api']);

		// retreive the submit url of the selected list
		$filters = array('list_id' => $mailbag_options['mailchimp_list']);
		$list_data = $api->lists( $filters );

		$safeurl= $list_data['data'][0]['subscribe_url_long'];
		$safeurl = str_replace('http://', 'https://', $safeurl );

		return $safeurl;

	}
	return false;
}


// -------------- Process MailChimp subscribe form -------------- //
function mailbag_check_for_email_signup() {

	// only proceed with this function if we are posting from our email subscribe form
	if(isset($_POST['action']) && $_POST['action'] == 'mailbag_mailchimp') {

		// this contains the email address entered in the subscribe form
		$email = $_POST['mailbag_mailchimp_email'];

		// check for a valid email
		if(!is_email($email)) {
			wp_die(__('Your email address is invalid!', 'mailbag'), __('Invalid Email', 'mailbag'));
		}

		// send this email to mailchimp
		mailbag_subscribe_email($email);

		// send user to the confirmation page
		wp_redirect( add_query_arg( 'sent', '1', get_permalink() ) ); exit;
	}
}
add_action( 'init', 'mailbag_check_for_email_signup' );


// -------------- Add email to MailChimp subscription list -------------- //
function mailbag_subscribe_email( $email ) {

	$mailbag_options = get_option('mailbag_options');

	// check that the API option is set
	if(strlen(trim($mailbag_options['mailchimp_api'])) > 0 ) {

		// load the MCAPI wrapper
		require_once('mailchimp/MCAPI.class.php');

		// setup a new instance of the MCAPI class
		$api = new MCAPI($mailbag_options['mailchimp_api']);

		// subscribe the email to the list and return TRUE if successful
		if($api->listSubscribe($mailbag_options['mailchimp_list'], $email, '') === true) {
			return true;
		}
	}

	// return FALSE if any of the above fail
	return false;
}


// -------------- Get Campaign Monitor Lists -------------- //
function mailbag_get_campaign_monitor_lists() {

	$mailbag_options = get_option('mailbag_options');

	if(strlen(trim($mailbag_options['campaign_api'])) > 0 && strlen(trim($mailbag_options['campaign_api'])) > 0 ) {

		$lists = array();

		require_once(dirname(__FILE__) . '/campaign/csrest_clients.php');

		$wrap = new CS_REST_Clients($mailbag_options['campaign_client_id'], $mailbag_options['campaign_api']);

		$result = $wrap->get_lists();

		if($result->was_successful()) {
			foreach($result->response as $list) {
				$lists[$list->ListID] = $list->Name;
			}
			return $lists;
		}
	}
	return array(); // return a blank array if the API key is not set
}

// -------------- Display Campaign Monitor signup form -------------- //
function mailbag_campaign_monitor_form( $redirect ) {

	$mailbag_options = get_option('mailbag_options');

	ob_start();
		if(isset($_GET['submitted']) && $_GET['submitted'] == 'yes') {
			echo '<p class="success-subscribe">' . __('You have been successfully subscribed!', 'mailbag') . '</p>';
		} else {
			if(strlen(trim($mailbag_options['campaign_api'])) > 0 ) { ?>

			<?php
				$cm_button_text = __( 'Subscribe', 'mailbag' );

				if(isset($mailbag_options['campaign_button'])) {
					$cm_button_text = $mailbag_options['campaign_button'];
				}

				if(isset($mailbag_options['campaign_form_text'])) {
					$cm_form_text = $mailbag_options['campaign_form_text'];
				}
			?>
				<form id="mailbag_campaign_monitor" class="mailbag-wrap" action="/" method="post">
					<fieldset>
						<?php if ( ! empty( $cm_form_text ) ) { ?>
							<legend><?php echo $cm_form_text; ?></legend>
						<?php } ?>
						<div class="mailbag-input">
							<label for="mailbag_cm_name"><?php _e('Enter your name:', 'mailbag'); ?></label>
							<input name="mailbag_cm_name" id="mailbag_cm_name" type="text" placeholder="<?php _e('Name', 'mailbag'); ?>"/>
						</div>
						<div class="mailbag-input">
							<label for="mailbag_cm_email"><?php _e('Enter your email:', 'mailbag'); ?></label>
							<input name="mailbag_cm_email" id="mailbag_cm_email" type="text" placeholder="<?php _e('Email address', 'mailbag'); ?>"/>
						</div>
						<div class="mailbag-input">
							<input type="hidden" name="redirect" value="<?php echo $redirect; ?>"/>
							<input type="hidden" name="action" value="mailbag_cm_signup"/>
							<input class="button" type="submit" value="<?php if ( $cm_button_text ) { echo $cm_button_text; } else { _e('Subscribe', 'mailbag'); } ?>"/>
						</div>
					</fieldset>
				</form>
			<?php
		}
	}
	return ob_get_clean();
}


// -------------- Process the Campaign Monitor form -------------- //
function mailbag_check_for_cm_email_signup() {

	// only proceed with this function if we are posting from our email subscribe form
	if(isset($_POST['action']) && $_POST['action'] == 'mailbag_cm_signup') {

		// setup the email and name varaibles
		$email = strip_tags($_POST['mailbag_cm_email']);
		$name = strip_tags($_POST['mailbag_cm_name']);

		// check for a valid email
		if(!is_email($email)) {
			wp_die(__('Your email address is invalid!', 'mailbag'), __('Invalid Email', 'mailbag'));
		}

		// check for a name
		if(strlen(trim($name)) <= 0) {
			wp_die(__('Enter your name!', 'mailbag'), __('No Name', 'mailbag'));
		}

		// send this email to campaign_monitor
		mailbag_cm_subscribe_email($email, $name);

		// send user to the confirmation page
		wp_redirect($_POST['redirect']); exit;
	}
}
add_action( 'init', 'mailbag_check_for_cm_email_signup' );


// -------------- Add email to Campaign Monitor subscription list -------------- //
function mailbag_cm_subscribe_email($email, $name) {

	$mailbag_options = get_option('mailbag_options');

	if(strlen(trim($mailbag_options['campaign_api'])) > 0 ) {

		require_once(dirname(__FILE__) . '/campaign/csrest_subscribers.php');

		$wrap = new CS_REST_Subscribers($mailbag_options['campaign_list'], $mailbag_options['campaign_api']);

		$subscribe = $wrap->add(array(
			'EmailAddress' => $email,
			'Name' => $name,
			'Resubscribe' => true
		));

		if($subscribe->was_successful()) {
			return true;
		}
	}
	return false;
}


// -------------- Add Campaign Monitor shortcode -------------- //
function mailbag_campaign_monitor_form_shortcode($atts, $content = null ) {
	extract( shortcode_atts( array(
		'redirect' => ''
	), $atts ) );

	if($redirect == '') {
		$redirect = add_query_arg('submitted', 'yes', get_permalink());
	}
	return mailbag_campaign_monitor_form($redirect);
}
add_shortcode( 'mailbag_campaign_monitor', 'mailbag_campaign_monitor_form_shortcode' );