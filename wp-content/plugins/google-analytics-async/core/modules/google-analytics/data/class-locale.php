<?php
/**
 * The locale view class for the Analytics module.
 *
 * This class will handle all the strings required in analytics module.
 *
 * @link    http://wpmudev.com
 * @since   3.2.4
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Analytics\Data
 */

namespace Beehive\Core\Modules\Google_Analytics\Data;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\Template;

/**
 * Class Locale
 *
 * @package Beehive\Core\Modules\Google_Analytics\Data
 */
class Locale {

	/**
	 * Get the localise vars for the post stats box.
	 *
	 * This data will be available only in post stats scripts.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public static function post() {
		return array(
			'label' => array(
				'users'            => __( 'Users', 'ga_trans' ),
				'pageviews'        => __( 'Pageviews', 'ga_trans' ),
				'sessions'         => __( 'Sessions', 'ga_trans' ),
				'page_sessions'    => __( 'Pages/Sessions', 'ga_trans' ),
				'average_sessions' => __( 'Avg. Time', 'ga_trans' ),
				'bounce_rates'     => __( 'Bounce Rate', 'ga_trans' ),
			),
			'desc'  => array(
				'users'                   => __( 'Users who have initiated at least one session during the date range.', 'ga_trans' ),
				/* translators: %1$s: No. of users. %2$s: Date period. */
				'screen_users'            => __( '%1$d users who have initiated at least one session between %2$s.', 'ga_trans' ),
				'pageviews'               => __( 'Pageviews is the total number of pages viewed. Repeated views of a single page are counted.', 'ga_trans' ),
				/* translators: %1$s: No. of views. %2$s: Date period. */
				'screen_pageviews'        => __( '%1$d views of this page between %2$s.', 'ga_trans' ),
				'sessions'                => __( 'Total number of Sessions within the date range. A session is the period of time user is actively engaged with your website, app, etc.', 'ga_trans' ),
				/* translators: %1$s: No. of sessions. %2$s: Date period. */
				'screen_sessions'         => __( '%1$d number of sessions within %2$s.', 'ga_trans' ),
				'page_sessions'           => __( 'Pages/Sessions (Average Page Depth) is the average number of pages viewed during a session. Repeated views of a single page are counted.', 'ga_trans' ),
				/* translators: %1$d: No. of page sessions. %2$s: Date period. */
				'screen_page_sessions'    => __( '%1$d page sessions between %2$s.', 'ga_trans' ),
				'average_sessions'        => __( 'The average length of a session.', 'ga_trans' ),
				/* translators: %1$s: Length of sessions. %2$s: Date period. */
				'screen_average_sessions' => __( 'This page has an average session length of %1$s between %2$s.', 'ga_trans' ),
				'bounce_rates'            => __( 'The percentage of single-page sessions in which there was no interaction with the page. A bounced session has a duration of 0 seconds.', 'ga_trans' ),
				/* translators: %1$s: No. of single-page sessions. %2$s: Date period. */
				'screen_bounce_rates'     => __( '%1$s of single-page sessions without interaction between %2$s.', 'ga_trans' ),
			),
		);
	}

	/**
	 * Get the localise vars specific to the dashboard widget.
	 *
	 * This data will be available only in dashboard widget script.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public static function dashboard_widget() {
		return array(
			'title'       => array(),
			'menus'       => array(),
			'notice'      => array(
				'auth_required' => sprintf( /* translators: %s: Link to login. */
					__( 'A Google Analytics account hasn\'t been linked yet. To see statistics, <a href="%s">link an account</a>.', 'ga_trans' ),
					Template::accounts_url( 'google', is_network_admin() )
				),
				'empty_data'    => __( 'It may take up to 24 hours for data to begin feeding. Please check back soon.', 'ga_trans' ),
			),
			'placeholder' => array(),
			'error'       => array(),
			'label'       => array(
				'all_stats'              => __( 'See all stats', 'ga_trans' ),
				'refresh_data'           => __( 'Refresh data', 'ga_trans' ),
				'general_stats'          => __( 'General stats', 'ga_trans' ),
				'audience'               => __( 'Audience', 'ga_trans' ),
				'top_pages'              => __( 'Top Pages & Views', 'ga_trans' ),
				'sessions'               => __( 'Sessions', 'ga_trans' ),
				'pageviews'              => __( 'Pageviews', 'ga_trans' ),
				'page_sessions'          => __( 'Pages/Sessions', 'ga_trans' ),
				'average_sessions'       => __( 'Avg. Time', 'ga_trans' ),
				'bounce_rates'           => __( 'Bounce Rate', 'ga_trans' ),
				'users'                  => __( 'Users', 'ga_trans' ),
				'traffic'                => __( 'Traffic', 'ga_trans' ),
				'top_page'               => __( 'Top Page', 'ga_trans' ),
				'top_country'            => __( 'Top Country', 'ga_trans' ),
				'top_referral'           => __( 'Top Referral', 'ga_trans' ),
				'top_search_engine'      => __( 'Top Search Engine', 'ga_trans' ),
				'top_medium'             => __( 'Top Medium', 'ga_trans' ),
				'top_social_network'     => __( 'Top Social Network', 'ga_trans' ),
				'none'                   => __( 'None', 'ga_trans' ),
				'no_data_available'      => __( 'No data available.', 'ga_trans' ),
				'fetching_data'          => __( 'Fetching latest data...', 'ga_trans' ),
				'top_pages_most_visited' => __( 'Top Pages/most visited', 'ga_trans' ),
				'views'                  => __( 'Views', 'ga_trans' ),
				'top_countries'          => __( 'Top Countries', 'ga_trans' ),
				'returning_visitors'     => __( 'Returning visitors', 'ga_trans' ),
				'new_visitors'           => __( 'New visitors', 'ga_trans' ),
			),
			'button'      => array(),
			'tooltip'     => array(),
			'desc'        => array(
				'no_donut_data'      => __( 'No data for this period of time', 'ga_trans' ),
				'returning_visitors' => __( 'returning visitors', 'ga_trans' ),
				'new_visitors'       => __( 'new visitors', 'ga_trans' ),
			),
		);
	}

	/**
	 * Get the localise vars specific to the GA admin pages.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public static function admin() {
		return array(
			'notice'      => array(
				/* translators: %s: Google support doc link. */
				'privacy_policy'                => __( 'Note: Enabling this feature requires <a href="%s" target="_blank">updating your privacy policy</a>.', 'ga_trans' ),
				/* translators: %s: Link to statistics page. */
				'account_setup_both'            => __( 'Your Google account is now connected and analytics are being tracked. You can view this site\'s analytics on the <a href="%s">Statistics page</a>.', 'ga_trans' ),
				/* translators: %s: Link to statistics page. */
				'account_setup_tracking'        => __( 'Your Tracking ID is set up and being output in the %1$s section of your site\'s code. To view analytics in your <a href="%2$s">WordPress Dashboard</a>, connect the Google account you\'re tracking analytics for.', 'ga_trans' ),
				/* translators: %s: Link to statistics page. */
				'account_setup_login'           => __( 'Your Google account is now connected and you can view this site\'s analytics on the <a href="%s">Statistics page</a>. If you want to track analytics using Beehive, add your Tracking ID below to set up tracking.', 'ga_trans' ),
				'no_accounts'                   => sprintf( /* translators: %s: Google analytics signup link. */
					__( 'You don\'t have any Google Analytics profile connected to your account. To get going, just <a href="%s" target="_blank">sign up for Google Analytics</a>.', 'ga_trans' ),
					'https://analytics.google.com/analytics/web/'
				),
				'no_streams'                    => sprintf( /* translators: %s: Google analytics signup link. */
					__( 'You don\'t have any Google Analytics profile connected to your account. To get going, just <a href="%s" target="_blank">sign up for Google Analytics</a>.', 'ga_trans' ),
					'https://analytics.google.com/analytics/web/'
				),
				/* translators: %s: Link to accounts page. */
				'authentication_required'       => __( 'To choose your profile first you need to go to the <a href="%s">Authentication</a> page and connect your Google account.', 'ga_trans' ),
				'invalid_tracking_id'           => sprintf( /* translators: %s: Google support docs link. */
					__( 'Whoops, looks like that\'s an invalid tracking ID. Double check you have your <a href="%s" target="_blank">Google tracking ID</a> and try again.', 'ga_trans' ),
					'https://support.google.com/analytics/answer/1032385?rd=1'
				),
				/* translators: %1$s: < and %2$s: >. */
				'automatic_tracking_enabled'    => __( 'You\'ve selected to automatically detect the tracking ID in the Account settings. This tracking ID is being output in the %1$shead%2$s section of your pages.', 'ga_trans' ),
				/* translators: %1$s: < and %2$s: >. */
				'automatic_measurement_enabled' => __( 'You\'ve selected to automatically detect the measurement ID in the Account settings. This measurement ID is being output in the %1$shead%2$s section of your pages.', 'ga_trans' ),
				'measurement_id'                => sprintf(
				/* translators: %s: Google support docs link. */
					__( ' To learn more about Analytics 4 Beta features, check out <a href="%s" target="_blank">What\'s new</a>.', 'ga_trans' ),
					'https://blog.google/products/marketingplatform/analytics/new_google_analytics'
				),
				'invalid_measurement_id'        => sprintf(
				/* translators: %s: Google support docs link. */
					__( 'Whoops, looks like that\'s an invalid measurement ID. Double check you have your <a href="%s" target="_blank">Analytics 4 measurement ID</a> and try again.', 'ga_trans' ),
					'https://support.google.com/analytics/answer/9539598?hl=en'
				),
				/* translators: %s: Google API error message. */
				'api_error'                     => __( 'Google API error: %s', 'ga_trans' ),
			),
			'error'       => array(
				'tracking_id' => __( 'Please use a valid Google Analytics Tracking ID', 'ga_trans' ),
			),
			'placeholder' => array(
				'measurement_id' => __( 'E.g: G-XXXXXXXXXX', 'ga_trans' ),
			),
			'label'       => array(
				'admin_tracking'                 => __( 'Admin pages tracking', 'ga_trans' ),
				'admin_tracking_enable'          => __( 'Enable Admin pages tracking', 'ga_trans' ),
				'display_ad'                     => __( 'Display Advertising', 'ga_trans' ),
				'display_ad_enable'              => __( 'Enable Display Advertising Support', 'ga_trans' ),
				'exclude_roles_tracking'         => __( 'Exclude Roles from Tracking', 'ga_trans' ),
				'statistics_posts'               => __( 'Statistics in post types', 'ga_trans' ),
				'ip_anonymization'               => __( 'IP Anonymization', 'ga_trans' ),
				'ip_anonymization_enable'        => __( 'Enable IP Anonymization', 'ga_trans' ),
				'ip_anonymization_force'         => __( 'Force on sub-sites tracking', 'ga_trans' ),
				'ip_anonymization_force_network' => __( 'Whole network tracking', 'ga_trans' ),
				'ip_anonymization_force_subsite' => __( 'Force on sub-sites tracking', 'ga_trans' ),
				'prosites_settings'              => __( 'Pro Site Permissions', 'ga_trans' ),
				'analytics_profile'              => __( 'Universal Analytics Profile', 'ga_trans' ),
				'analytics_4_profile'            => __( 'Analytics 4 Profile', 'ga_trans' ),
				'network_tracking_id'            => __( 'Network Tracking ID', 'ga_trans' ),
				'tracking_id'                    => __( 'Tracking ID', 'ga_trans' ),
				'use_different_tracking'         => __( 'Use a different tracking ID', 'ga_trans' ),
				'use_different_measurement'      => __( 'Use a different measurement ID', 'ga_trans' ),
				'network_measurement_id'         => __( 'Network Measurement ID', 'ga_trans' ),
				'measurement_id'                 => __( 'Measurement ID', 'ga_trans' ),
				'user_roles'                     => __( 'User Roles', 'ga_trans' ),
				'post_types'                     => __( 'Post Types', 'ga_trans' ),
			),
			'button'      => array(),
			'tooltip'     => array(
				'tracking_only' => __( 'Note: This will only feed data to Google, to view analytics in your Dashboard you\'ll need to authenticate your account on the Settings tab.', 'ga_trans' ),
			),
			'desc'        => array(
				'admin_tracking'           => __( 'When enabled, the activity in the wp-admin area will be tracked and included in statistics. By default, only the activity on the front-end pages of your site are tracked.', 'ga_trans' ),
				/* translators: %s: Google support docs link. */
				'display_ad'               => __( 'Enable support for Google\'s Display Advertising and get additional demographic and interest reports. You can read more about it <a href="%s" target="_blank">here</a>.', 'ga_trans' ),
				/* translators: %s: Google support docs link. */
				'ip_anonymization'         => __( 'When enabled, visitor IP address will be <a href="%s" target="_blank">anonymized</a>.', 'ga_trans' ),
				'prosites_settings'        => __( 'We see you have Pro Sites active. Choose which levels you want to access analytics.', 'ga_trans' ),
				'account'                  => __( 'Connect and authenticate your Google Analytics account to view analytics data on Beehive\'s Dashboard. If you only want to connect your site to Google Analytics without displaying the analytics data on Beehive\'s dashboard, add the Measurement ID or Tracking ID in the corresponding fields.', 'ga_trans' ),
				'analytics_profile'        => __( 'Authenticate with Google to connect your Universal Analytics profile and begin feeding analytics to your WordPress Dashboard.', 'ga_trans' ),
				'analytics_4_profile'      => __( 'Authenticate with Google to connect your Google Analytics 4 profile and begin feeding analytics to your WordPress Dashboard.', 'ga_trans' ),
				// translators: %s Google account setup link.
				'account_not_here'         => sprintf( __( 'Site not here? Try logging into another <a href="%s">Google account</a>', 'ga_trans' ), Template::accounts_url() ),
				'network_tracking'         => __( 'Copy and paste your Google Universal Analytics tracking ID to add it to your website. Note: This tracking code will track your whole network. To track subsites, you need to add the tracking code separately for each one.', 'ga_trans' ),
				'tracking_statistics'      => __( 'Copy and paste your Google Universal Analytics tracking ID to add it to your website. Note: Universal Analytics refers to the previous generation of Analytics.', 'ga_trans' ),
				'tracking_id_help'         => sprintf(
				/* translators: %s: Google support docs link. */
					__( 'Having trouble finding your tracking code? You can grab it <a href="%s" target="_blank">here</a>.', 'ga_trans' ),
					'https://support.google.com/analytics/answer/1032385?rd=1'
				),
				'tracking_id_inherited'    => __( 'Note: Currently your statistics are provided from network wide tracking code. You can increase stats accuracy by logging in and configuring your own profile.', 'ga_trans' ),
				'measurement_id_inherited' => __( 'Note: Currently your statistics are provided from network wide measurement ID. You can increase stats accuracy by logging in and configuring your own stream.', 'ga_trans' ),
				'measurement_id'           => __( 'Copy and paste your Google Analytics ID to add it to your website. Note: The new Google Analytics 4 is now the default experience for all new properties.', 'ga_trans' ),
				'network_measurement_id'   => __( 'Copy and paste your Google Analytics ID to add it to your website. Note: This measurement ID will track your whole network. To track subsites, you need to add the Measurement ID separately for each one.', 'ga_trans' ),
				'exclude_roles_tracking'   => __( 'Choose which user roles you want to exclude from having their activity tracked when they\'re logged in and visiting the site.', 'ga_trans' ),
				'statistics_posts'         => __( 'Choose which post types you want to include the statistics meta box in. By default, it will only display on posts and pages.', 'ga_trans' ),
			),
		);
	}

	/**
	 * Get the localise vars specific to the all statistics page.
	 *
	 * This data will be available only in statistics pages script.
	 *
	 * @since 3.3.7
	 *
	 * @return array
	 */
	public static function statistics() {
		return array(
			'title'  => array(
				'google_analytics' => __( 'Google Analytics', 'ga_trans' ),
				'statistics'       => __( 'Statistics', 'ga_trans' ),
			),
			'notice' => array(
				/* translators: %s: Link to accounts page. */
				'google_not_linked'          => __( 'A Google Analytics account hasn\'t been linked yet. To see statistics, <a href="%s">link an account</a>.', 'ga_trans' ),
				'google_not_linked_simple'   => __( 'A Google Analytics account hasn\'t been linked yet.', 'ga_trans' ),
				/* translators: %s: Link to accounts page. */
				'realtime_google_not_linked' => __( 'A Google Analytics account hasn’t been linked yet. To see real-time statistics, <a href="%s">link an account</a>.', 'ga_trans' ),
				'google_no_data'             => __( 'There\'s no analytics data to display yet. Either you haven\'t had traffic to this site or we don\'t have any data from API yet. Please check back in few hours.', 'ga_trans' ),
			),
			'label'  => array(
				'visitors'               => __( 'Visitors', 'ga_trans' ),
				'mediums'                => __( 'Mediums', 'ga_trans' ),
				'top_pages'              => __( 'Top Pages', 'ga_trans' ),
				'search_engines'         => __( 'Search Engines', 'ga_trans' ),
				'social_networks'        => __( 'Social Networks', 'ga_trans' ),
				'top_countries'          => __( 'Top Countries', 'ga_trans' ),
				'realtime_visitors'      => __( 'Real-Time Visitors', 'ga_trans' ),
				'fetching_data'          => __( 'Fetching latest data...', 'ga_trans' ),
				'compare_periods'        => __( 'Compare to last period', 'ga_trans' ),
				'no_information'         => __( 'No information', 'ga_trans' ),
				'top_pages_most_visited' => __( 'Top Pages/most visited', 'ga_trans' ),
				'trend'                  => __( 'Trend', 'ga_trans' ),
				'average_sessions'       => __( 'Avg. Sessions', 'ga_trans' ),
				'sessions'               => __( 'Sessions', 'ga_trans' ),
				'users'                  => __( 'Users', 'ga_trans' ),
				'pageviews'              => __( 'Pageviews', 'ga_trans' ),
				'has'                    => __( 'has', 'ga_trans' ),
				'page_sessions'          => __( 'Page/Sessions', 'ga_trans' ),
				'bounce_rates'           => __( 'Bounce Rates', 'ga_trans' ),
				'country_code'           => __( 'Country Code', 'ga_trans' ),
				'country_name'           => __( 'Country Name', 'ga_trans' ),
				'visits_percentage'      => __( 'Visits Percentage', 'ga_trans' ),
				'total_visits'           => __( 'Total Visits', 'ga_trans' ),
			),
			'desc'   => array(
				'empty_visitors_chart' => __( 'Visitors chart is empty. There\'s no data to display.', 'ga_trans' ),
				/* translators: %s: Percent no. of visits per country. */
				'percentage_visits'    => __( '%s visits.', 'ga_trans' ),
			),
		);
	}
}