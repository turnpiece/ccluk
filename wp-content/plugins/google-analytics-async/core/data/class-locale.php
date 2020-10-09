<?php
/**
 * The locale view class of the plugin.
 *
 * This class will handle all the strings required in Vue files.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.4
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Data
 */

namespace Beehive\Core\Data;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

/**
 * Class Locale
 *
 * @package Beehive\Core\Data
 */
class Locale {

	/**
	 * Get the common vars available to all files.
	 *
	 * This data will be available in all scripts.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public static function common() {
		return array(
			'dialog'      => array(
				'close'    => __( 'Close this dialog.', 'ga_trans' ),
				'go_back'  => __( 'Go back to previous slide.', 'ga_trans' ),
				'continue' => __( 'Continue', 'ga_trans' ),
				'cancel'   => __( 'Cancel', 'ga_trans' ),
			),
			'notice'      => array(
				'dismiss'        => __( 'Dismiss this notice', 'ga_trans' ),
				'changes_saved'  => __( 'Changes were saved successfully.', 'ga_trans' ),
				'changes_failed' => __( 'Could not save the changes. Please try again.', 'ga_trans' ),
			),
			'accordion'   => array(
				'open' => __( 'Open item', 'ga_trans' ),
			),
			'tree'        => array(
				'select'     => __( 'Select this item', 'ga_trans' ),
				'open_close' => __( 'Open or close this item', 'ga_trans' ),
			),
			'header'      => array(
				'doc' => __( 'View Documentation', 'ga_trans' ),
			),
			'footer'      => array(
				'hub'          => __( 'The Hub', 'ga_trans' ),
				'plugins'      => __( 'Plugins', 'ga_trans' ),
				'roadmap'      => __( 'Roadmap', 'ga_trans' ),
				'support'      => __( 'Support', 'ga_trans' ),
				'docs'         => __( 'Docs', 'ga_trans' ),
				'community'    => __( 'Community', 'ga_trans' ),
				'academy'      => __( 'Academy', 'ga_trans' ),
				'tos'          => __( 'Terms of Service', 'ga_trans' ),
				'privacy'      => __( 'Privacy Policy', 'ga_trans' ),
				'facebook'     => __( 'Facebook', 'ga_trans' ),
				'twitter'      => __( 'Twitter', 'ga_trans' ),
				'instagram'    => __( 'Instagram', 'ga_trans' ),
				'free_plugins' => __( 'Free Plugins', 'ga_trans' ),
				'membership'   => __( 'Membership', 'ga_trans' ),
			),
			'label'       => array(
				'dismiss'  => __( 'Dismiss', 'ga_trans' ),
				'settings' => __( 'Settings', 'ga_trans' ),
				'account'  => __( 'Account', 'ga_trans' ),
			),
			'button'      => array(
				'close'          => __( 'Dismiss', 'ga_trans' ),
				'add'            => __( 'Add', 'ga_trans' ),
				'adding'         => __( 'Adding', 'ga_trans' ),
				'refresh'        => __( 'Refresh data', 'ga_trans' ),
				'refreshing'     => __( 'Refreshing data', 'ga_trans' ),
				'activate'       => __( 'Activate', 'ga_trans' ),
				'activating'     => __( 'Activating', 'ga_trans' ),
				'save_changes'   => __( 'Save Changes', 'ga_trans' ),
				'saving_changes' => __( 'Saving Changes', 'ga_trans' ),
				'open_options'   => __( 'Open options', 'ga_trans' ),
				'authorize'      => __( 'Authorize', 'ga_trans' ),
				'authorizing'    => __( 'Authorizing', 'ga_trans' ),
				'got_it'         => __( 'Got it', 'ga_trans' ),
			),
			'tooltip'     => array(
				'refresh' => __( 'Clear Beehive\'s local analytics cache and grab the latest data from Google.', 'ga_trans' ),
			),
			'placeholder' => array(
				'tracking_id' => __( 'E.g: UA-XXXXXXXXX-X', 'ga_trans' ),
			),
		);
	}

	/**
	 * Get the common vars specific to settings.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public static function settings() {
		return array(
			'title'       => array(
				'settings'    => __( 'Settings', 'ga_trans' ),
				'permissions' => __( 'Permissions', 'ga_trans' ),
				'tracking'    => __( 'Tracking Settings', 'ga_trans' ),
				'add_user'    => __( 'Add user', 'ga_trans' ),
			),
			'menus'       => array(
				'tracking'    => __( 'Tracking Settings', 'ga_trans' ),
				'permissions' => __( 'Permissions', 'ga_trans' ),
			),
			'notice'      => array(),
			'placeholder' => array(
				'custom_capability' => __( 'Set custom capability', 'ga_trans' ),
			),
			'error'       => array(),
			'label'       => array(
				'roles'                 => __( 'Roles', 'ga_trans' ),
				'capabilities'          => __( 'Capabilities', 'ga_trans' ),
				'user_role'             => __( 'User Role', 'ga_trans' ),
				'custom_users'          => __( 'Custom Users', 'ga_trans' ),
				'statistics'            => __( 'Statistics', 'ga_trans' ),
				'analytics_settings'    => __( 'Google Analytics Settings', 'ga_trans' ),
				'dashboard_analytics'   => __( 'Dashboard Analytics', 'ga_trans' ),
				'custom_capability'     => __( 'Custom Capability', 'ga_trans' ),
				'administrator'         => __( 'Administrator', 'ga_trans' ),
				'network_administrator' => __( 'Network Administrator', 'ga_trans' ),
				'connected_account'     => __( 'Connected Google Account', 'ga_trans' ),
				'override_permissions'  => __( 'Allow sub-site admins to override these permissions.', 'ga_trans' ),
				'excluded_users'        => __( 'Users who don’t have access to settings', 'ga_trans' ),
				'include_users'         => __( 'Users who have access to settings', 'ga_trans' ),
				'search_users'          => __( 'Search users', 'ga_trans' ),
				'type_user_name'        => __( 'Type Username', 'ga_trans' ),
			),
			'button'      => array(
				'processing' => __( 'Processing', 'ga_trans' ),
				'exclude'    => __( 'Exclude', 'ga_trans' ),
				'include'    => __( 'Include', 'ga_trans' ),
				'add_user'   => __( 'Add User', 'ga_trans' ),
			),
			'tooltip'     => array(
				'administrator'         => __( 'Administrators have access to Beehive statistics by default.', 'ga_trans' ),
				'network_administrator' => __( 'Network administrators have access to Beehive statistics by default.', 'ga_trans' ),
			),
			'desc'        => array(
				'statistics'          => __( 'Choose which user roles or capabilities can view statistics in their WordPress Dashboard area. ', 'ga_trans' ),
				'settings'            => __( 'By default, all administrators have access to Beehive’s settings. You can configure and add permissions for other roles, as well as prevent or grant access to custom users to access Beehive’s settings.', 'ga_trans' ),
				'settings_network'    => __( 'By default, only network administrators and site admins have access to Beehive’s settings. You can configure and add permissions for other roles, as well as prevent or grant access to custom users to access Beehive’s settings.', 'ga_trans' ),
				'tracking_subsite'    => __( 'Log in to your Google Analytics account to to auto configure tracking code and improve the statistics accuracy. Alternatively if you don’t wan to log in, you can add the tracking ID below.', 'ga_trans' ),
				'analytics_settings'  => __( 'Choose which Pro Site levels can configure analytics settings.', 'ga_trans' ),
				'dashboard_analytics' => __( 'Choose which Pro Site levels can view analytics in their WP Admin Dashboard.', 'ga_trans' ),
				/* translators: %s: WordPress.org link. */
				'custom_capability'   => __( 'Specify a custom capability that, if a user role matches it, can see analytics. You can view default capabilities <a href="%s" target="_blank">here</a>.', 'ga_trans' ),
				'user_role'           => __( 'Choose which user roles can have access and configure Beehive’s settings.', 'ga_trans' ),
				'user_role_second'    => __( 'Note: By default, all Administrators have access to Beehive. You can exclude custom users with the Administrator role in the Custom Users / Exclude tab.', 'ga_trans' ),
				'custom_users'        => __( 'In addition to the enabled user roles you can include or exclude individual users.', 'ga_trans' ),
				'include_users'       => __( 'Include users who don’t match the user roles you specified in the User Role tab, but that you want to allow access.', 'ga_trans' ),
				'exclude_users'       => __( 'Exclude users who match the user roles you specified in the User Role tab, but don\'t want to allow access. Note: You can also exclude users with the Administrator role if necessary.', 'ga_trans' ),
				'add_user'            => __( 'Type the username in the searchbox to add. You can add as many users as you like.', 'ga_trans' ),
			),
		);
	}

	/**
	 * Get the common vars specific to accounts page.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public static function accounts() {
		return array(
			'title'       => array(
				'accounts' => __( 'Accounts', 'ga_trans' ),
			),
			'label'       => array(
				'google_account'  => __( 'Google Account', 'ga_trans' ),
				'client_id'       => __( 'Google Client ID', 'ga_trans' ),
				'client_secret'   => __( 'Google Client Secret', 'ga_trans' ),
				'authorize'       => __( 'Authorize', 'ga_trans' ),
				'google_api'      => __( 'Set up API Project', 'ga_trans' ),
				'logout'          => __( 'Log out', 'ga_trans' ),
				'no_account_info' => __( 'No account information', 'ga_trans' ),
			),
			'button'      => array(
				'logging_out' => __( 'Logging out', 'ga_trans' ),
			),
			'tooltip'     => array(),
			'placeholder' => array(
				'client_id'     => __( 'Paste Google Client ID here', 'ga_trans' ),
				'client_secret' => __( 'Paste Google Client Secret here', 'ga_trans' ),
			),
			'error'       => array(
				'client_id'     => __( 'Please enter Client ID.', 'ga_trans' ),
				'client_secret' => __( 'Please enter Client Secret.', 'ga_trans' ),
			),
			'desc'        => array(
				/* translators: %s: Beehive docs link. */
				'google_setup'           => __( 'If you\'re a site admin and experienced with Google\'s tools, you might want to set up an API Project instead. Need help setting this up? Check the docs <a href="%s" target="_blank">here</a>.', 'ga_trans' ),
				'google_account_network' => __( 'Authenticate with Google to easily connect Google Services network-wide and display statistics on all subsites by default.', 'ga_trans' ),
				'google_account_subsite' => __( 'Authenticate with Google to easily connect Google Services and improve the accuracy of statistics.', 'ga_trans' ),
				'google_account_single'  => __( 'Authenticate with Google to easily connect Google Services to your website and display statistics in your WordPress Dashboard.', 'ga_trans' ),
				'logout_first'           => __( 'Logging out will remove analytics from your Dashboard.', 'ga_trans' ),
				'logout_second'          => __( 'Are you sure you want to logout?', 'ga_trans' ),
			),
			'notice'      => array(
				/* translators: %s: Link to All Statistics page. */
				'google_already_connected' => __( 'Note: Your account is already configured at the network level. Optionally, you can set up a different account by adding it below.', 'ga_trans' ),
				'google_account_error'     => __( 'Your Google Analytics account has been connected successfully. Choose which analytics profile you want to use for showing statistics.', 'ga_trans' ),
				/* translators: %1$s: Google support doc link. %2$s WPMUDEV support link. */
				'google_api_error'         => __( 'We couldn\'t authorize your Google account. Please fill in <a href="%1$s" target="_blank">your API information</a> again, or connect with Google using the button below in side tab. If you\'re still stuck, please <a href="%2$s" target="_blank">contact support</a> for assistance.', 'ga_trans' ),
				'auth_success'             => __( 'Your Google account has been connected successfully.', 'ga_trans' ),
				/* translators: %s: WPMUDEV support link. */
				'auth_failed'              => __( 'We couldn\'t authorize your Google account. Please fill in your API information again, or connect with Google using the button in the side tab. If you\'re still stuck, please <a href="%s" target="_blank">contact support</a> for assistance.', 'ga_trans' ),
				'logged_out'               => __( 'You have been successfully logged out.', 'ga_trans' ),
				'account_setup'            => __( 'Your Google account is connected. You can now view analytics in your WordPress Dashboard.', 'ga_trans' ),
			),
		);
	}

	/**
	 * Get the localization vars for the onboarding screens.
	 *
	 * This data will be available only when onboarding is rendered.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public static function onboarding() {
		return array(
			'error'  => array(
				'tracking_id' => __( 'Please use a valid Google Analytics Tracking ID', 'ga_trans' ),
			),
			'label'  => array(
				'admin_tracking'        => __( 'Admin pages tracking', 'ga_trans' ),
				'admin_tracking_enable' => __( 'Enable Admin pages tracking', 'ga_trans' ),
				'finishing_setup'       => __( 'Finishing Setup...', 'ga_trans' ),
				'auth_form_alt'         => __( 'Setup Google Account', 'ga_trans' ),
				/* translators: %s: Beehive plugin name. */
				'welcome'               => __( 'Welcome to %s', 'ga_trans' ),
				'google_tracking_id'    => __( 'Add Google Analytics tracking ID', 'ga_trans' ),
				'google_account_setup'  => __( 'Set up your account', 'ga_trans' ),
				'display_statistics'    => __( 'Display Analytics statistics to:', 'ga_trans' ),
				'network_tracking_id'   => __( 'Network-wide Tracking ID', 'ga_trans' ),
				'tracking_id'           => __( 'Tracking ID', 'ga_trans' ),
				'why_connect'           => __( 'Why do I need to connect with Google?', 'ga_trans' ),
			),
			'button' => array(
				'save_code' => __( 'Save Code', 'ga_trans' ),
			),
			'desc'   => array(
				'admin_tracking'         => __( 'When enabled, you will get statistics from all admin pages.', 'ga_trans' ),
				'finishing_setup'        => __( 'Please wait a few moments while we set up your account. Note that data can take up to 24 hours to display.', 'ga_trans' ),
				'welcome_network'        => __( 'Let\'s get started by connecting your Google Analytics account to get your tracking ID. This will enable statistics for your whole network and all subsites. Alternately, you can choose to enable network-wide tracking by adding a tracking ID manually below.', 'ga_trans' ),
				'welcome_single'         => __( 'Let\'s get started by connecting your Google Analytics account which will auto-configure your tracking ID to improve your statistics accuracy. Alternately, if you don\'t want to log in, you can manually add the tracking ID below.', 'ga_trans' ),
				'google_connect_success' => __( 'We have successfully set up your API project. The next step is to choose your Analytics Profile to get data feeding.', 'ga_trans' ),
				/* translators: %s: Link to get tracking id. */
				'tracking_id'            => __( 'Paste your Google Analytics tracking ID in the field below to enable analytics tracking for the whole network. You can get your ID <a href="%s" target="_blank">here</a>.', 'ga_trans' ),
				'why_connect'            => __( 'We need to authenticate your account with Google to ensure you actually own the analytics data.', 'ga_trans' ),
			),
		);
	}

	/**
	 * Get the localization vars for the auth form.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public static function auth_form() {
		return array(
			'title'       => array(),
			'label'       => array(
				'connect_google'  => __( 'Connect with Google', 'ga_trans' ),
				'access_code'     => __( 'Access Code', 'ga_trans' ),
				'choose_account'  => __( 'Choose your view (profile)', 'ga_trans' ),
				'auto_detect_id'  => __( 'Automatically detect tracking ID', 'ga_trans' ),
				'add_tracking_id' => __( 'Add Tracking ID', 'ga_trans' ),
			),
			'placeholder' => array(
				'access_code'    => __( 'Paste access code here', 'ga_trans' ),
				'no_website'     => __( 'No website information', 'ga_trans' ),
				'select_website' => __( 'Select a website', 'ga_trans' ),
			),
			'desc'        => array(
				'connect_google' => __( 'Easily connect with Google by clicking the “Connect with Google” button and pasting the access code below.', 'ga_trans' ),
			),
			'notice'      => array(
				/* translators: %1$s: Google support doc link. %2$s WPMUDEV support link. */
				'google_connect_error' => __( 'We couldn\'t connect your Google account. Please try reconnecting with the "Connect" button below. Alternately, you can set up a <a href="%1$s" target="_blank">new API project</a> with Google and use that instead. If you\'re still stuck you can <a href="%2$s" target="_blank">contact support</a> for assistance.', 'ga_trans' ),
			),
			'error'       => array(
				'access_code' => __( 'Please enter access code.', 'ga_trans' ),
			),
			'tooltip'     => array(
				'tracking_id' => __( 'A tracking ID is what connects your website to your Google Analytics account. Use \'Automatically detect tracking ID,\' and Beehive will find and set up your tracking code automatically.', 'ga_trans' ),
			),
		);
	}

	/**
	 * Get the localization vars for the dashboard page.
	 *
	 * This data will be only available in dashboard scripts.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public static function dashboard() {
		return array(
			'title'  => array(
				'dashboard'      => __( 'Dashboard', 'ga_trans' ),
				'statistics_box' => __( 'Statistics', 'ga_trans' ),
				'gtm_box'        => __( 'Google Tag Manager', 'ga_trans' ),
			),
			'label'  => array(
				'configure_account' => __( 'Configure account', 'ga_trans' ),
				'pageviews'         => __( 'Pageviews', 'ga_trans' ),
				'new_users'         => __( 'New Users', 'ga_trans' ),
				'top_page'          => __( 'Top Page', 'ga_trans' ),
				'top_search_engine' => __( 'Top Search Engine', 'ga_trans' ),
				'top_medium'        => __( 'Top Medium', 'ga_trans' ),
				'never'             => __( 'Never', 'ga_trans' ),
				'none'              => __( 'None', 'ga_trans' ),
				'coming_soon'       => __( 'Coming Soon', 'ga_trans' ),
				'sessions'          => __( 'Sessions', 'ga_trans' ),
				'users'             => __( 'Users', 'ga_trans' ),
				'page_sessions'     => __( 'Pages/Sessions', 'ga_trans' ),
				'average_sessions'  => __( 'Avg. time', 'ga_trans' ),
				'bounce_rates'      => __( 'Bounce Rate', 'ga_trans' ),
				'view_full_report'  => __( 'View Full Report', 'ga_trans' ),
				'fetching_data'     => __( 'Fetching latest data...', 'ga_trans' ),
				'gtm_account'       => __( 'GTM Account', 'ga_trans' ),
				'gtm_active'        => __( 'Active', 'ga_trans' ),
				'gtm_variables'     => __( 'Variables', 'ga_trans' ),
			),
			'button' => array(
				'finish_setup' => __( 'Finish Setup', 'ga_trans' ),
				'configure'    => __( 'Configure', 'ga_trans' ),
			),
			'desc'   => array(
				'statistics_box' => __( 'View your websites full analytics report with Sessions, Users Pageviews, Average time and Bounce Rate.', 'ga_trans' ),
				'gtm_box'        => __( 'Set up Google Tag Manager on your website and assign predefined and customizable variables to the data layer.', 'ga_trans' ),
			),
			'notice' => array(
				'auth_required'         => __( 'You need to authenticate with your Google account to automatically get the tracking ID for this website and enable access to statistics, or you can add the tracking ID manually.', 'ga_trans' ),
				'auth_required_network' => __( 'Authenticate with Google Analytics account to automatically get the tracking code for this website and enable statistics for whole network. Alternatively, you can just enable network wide tracking by adding in the Settings.', 'ga_trans' ),
				'no_data'               => __( 'We haven\'t collected enough data. Please check back soon.', 'ga_trans' ),
				/* translators: %s: Link to statistics page. */
				'account_setup'         => __( 'Your account has been set up successfully. You can view the statistics <a href="%s">here</a>.', 'ga_trans' ),
				'gtm_not_setup'         => __( 'Google Tag Manager has not been added to your website. Add your Container ID to finish setup.', 'ga_trans' ),
			),
		);
	}

	/**
	 * Get the localization vars for the welcome modal.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public static function welcome() {
		return array(
			'title' => array(
				'new_welcome_title' => __( 'New: Google Tag Manager', 'ga_trans' ),
			),
			'label' => array(
				'new_container_id' => __( 'Container ID', 'ga_trans' ),
				'new_variables'    => __( 'Variables', 'ga_trans' ),
			),
			'desc'  => array(
				'new_welcome_desc' => __( 'With Beehive\'s new integrated Google Tag Manager feature, you can easily activate it on your website and start tracking and managing your tags using the Tag Manager interface.', 'ga_trans' ),
				'new_container_id' => __( 'Placing the Container ID on your website can be done easily using copy/paste.', 'ga_trans' ),
				'new_variables'    => __( 'Assign predefined and customizable variables to the data layer to simplify and automate your tags in Google Tag Manager.', 'ga_trans' ),
			),
		);
	}
}