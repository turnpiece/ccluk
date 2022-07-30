<?php
/**
 * The locale view class for the Tag Manager module.
 *
 * This class will handle all the language strings required in tag manager module.
 *
 * @link    http://wpmudev.com
 * @since   3.3.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Tag_Manager\Views
 */

namespace Beehive\Core\Modules\Google_Tag_Manager\Data;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

/**
 * Class Locale
 *
 * @package Beehive\Core\Modules\Google_Tag_Manager\Data
 */
class Locale {

	/**
	 * Get the localise vars for the post stats box.
	 *
	 * This data will be available only in post stats scripts.
	 *
	 * @since 3.2.4
	 * @return array
	 */
	public static function common() {
		return array(
			'title'  => array(
				'tag_manager' => __( 'Google Tag Manager', 'ga_trans' ),
				'deactivate'  => __( 'Deactivate Google Tag Manager', 'ga_trans' ),
			),
			'label'  => array(),
			'button' => array(
				'deactivate'   => __( 'Deactivate', 'ga_trans' ),
				'deactivating' => __( 'Deactivating', 'ga_trans' ),
			),
			'desc'   => array(
				'deactivate' => __( 'Google Tag Manager will no longer be installed on your website. Are you sure you want to deactivate it?', 'ga_trans' ),
			),
			'notice' => array(),
		);
	}

	/**
	 * Get the localise vars for the post stats box.
	 *
	 * This data will be available only in post stats scripts.
	 *
	 * @since 3.2.4
	 * @return array
	 */
	public static function account() {
		return array(
			'title'  => array(),
			'label'  => array(
				'container_id' => __( 'Container ID', 'ga_trans' ),
				'gtm_id'       => __( 'GTM ID', 'ga_trans' ),
			),
			'desc'   => array(
				'activate_gtm'  => __( 'Set up Google Tag Manager on your website and assign predefined and customizable variables to the data layer.', 'ga_trans' ),
				'account_desc'  => __( 'Place the Container ID on your website to easily start tracking and managing your tags in the Tag Manager interface.', 'ga_trans' ),
				'container_id'  => __( 'Copy and paste your Google Tag Manager container ID to add it to your website.', 'ga_trans' ),
				/* translators: %s: Link to support page. */
				'gtm_id'        => __( 'Having trouble finding your GTM ID? Go to the <a href="%s" target="_blank">help center</a> and follow the steps.', 'ga_trans' ),
				/* translators: %s: <head>. */
				'gtm_id_output' => __( 'This tracking ID is being output in the %s section of your pages.', 'ga_trans' ),
			),
			'notice' => array(
				'account_connected'   => __( 'Google Tag Manager has been successfully added to your website.', 'ga_trans' ),
				'duplicate_connected' => __( 'Google Tag Manager has been successfully added to your website. Note: Your network container ID will be ignored since duplicated Container IDs are not supported by Google Tag Manager.', 'ga_trans' ),
				'gtm_invalid_id'      => sprintf( /* translators: %s: Link to GTM docs. */
					__( 'Whoops, looks like that\'s an invalid container ID. Double-check you have your <a href="%s" target="_blank">Google Tag Manager ID</a> and try again.', 'ga_trans' ),
					'https://tagmanager.google.com/'
				),
			),
		);
	}

	/**
	 * Get the localise vars for the post stats box.
	 *
	 * This data will be available only in post stats scripts.
	 *
	 * @since 3.2.4
	 * @return array
	 */
	public static function settings() {
		return array(
			'title'       => array(),
			'label'       => array(
				'variables'     => __( 'Variables', 'ga_trans' ),
				'default'       => __( 'Default', 'ga_trans' ),
				'visitors'      => __( 'Visitors', 'ga_trans' ),
				'integrations'  => __( 'Integrations', 'ga_trans' ),
				'custom'        => __( 'Custom', 'ga_trans' ),
				'variable'      => __( 'Variable', 'ga_trans' ),
				'custom_name'   => __( 'Custom Name', 'ga_trans' ),
				'variable_name' => __( 'Variable Name', 'ga_trans' ),
				'name'          => __( 'Name', 'ga_trans' ),
				'value'         => __( 'Value', 'ga_trans' ),
			),
			'button'      => array(
				'add_variable'    => __( 'Add Variable', 'ga_trans' ),
				'remove_variable' => __( 'Remove', 'ga_trans' ),
			),
			'variable'    => array(
				'post_id'          => __( 'Post ID', 'ga_trans' ),
				'post_title'       => __( 'Post title', 'ga_trans' ),
				'post_type'        => __( 'Post type', 'ga_trans' ),
				'post_date'        => __( 'Post date', 'ga_trans' ),
				'post_author'      => __( 'Post author ID', 'ga_trans' ),
				'post_author_name' => __( 'Post author name', 'ga_trans' ),
				'post_categories'  => __( 'Post categories', 'ga_trans' ),
				'post_tags'        => __( 'Post tags', 'ga_trans' ),
			),
			'visitor'     => array(
				'login_status'       => __( 'Logged in status', 'ga_trans' ),
				'user_role'          => __( 'Logged in user role', 'ga_trans' ),
				'user_id'            => __( 'Logged in user ID', 'ga_trans' ),
				'user_name'          => __( 'Logged in user name', 'ga_trans' ),
				'user_email'         => __( 'Logged in user email', 'ga_trans' ),
				'user_creation_date' => __( 'Logged in user creation date', 'ga_trans' ),
			),
			'integration' => array(
				'forminator_forms'   => __( 'Forminator Forms', 'ga_trans' ),
				'forminator_polls'   => __( 'Forminator Polls', 'ga_trans' ),
				'forminator_quizzes' => __( 'Forminator Quizzes', 'ga_trans' ),
				'hustle_leads'       => __( 'Hustle Leads', 'ga_trans' ),
			),
			'desc'        => array(
				'variables'                   => __( 'Assign variables to the data layer to simplify and automate your tags in Google Tag Manager.', 'ga_trans' ),
				'integrations'                => __( 'Integrate your plugins with Google Tag Manager adding a data layer event to fire a conversion tracking tag after each successful user interaction.', 'ga_trans' ),
				/* translators: %s: Link to GTM references page. */
				'custom_variables'            => __( 'Add global variables that will show on all pages. To see some examples, please visit the <a href="%s" target="_blank">Google Tag Manager Reference</a>.', 'ga_trans' ),
				'forminator_forms'            => __( 'Enable this to include a dataLayer event after a successful form submission.', 'ga_trans' ),
				/* translators: %s: Link to Forminator. */
				'forminator_forms_install'    => sprintf( __( '<a href="%s" target="_blank">Install Forminator</a> to enable. This will  include a dataLayer event after a successful form submission.', 'ga_trans' ), 'https://wpmudev.com/project/forminator-pro/' ),
				/* translators: %s: Link to Forminator. */
				'forminator_forms_activate'   => sprintf( __( '<a href="%s">Activate Forminator</a> to enable. This will  include a dataLayer event after a successful form submission.', 'ga_trans' ), network_admin_url( 'plugins.php' ) ),
				'forminator_polls'            => __( 'Enable this to include a dataLayer event after a successful poll submission.', 'ga_trans' ),
				/* translators: %s: Link to plugins page. */
				'forminator_polls_update'     => sprintf( __( '<a href="%s">Update Forminator</a> to enable. This will  include a dataLayer event after a successful poll submission.', 'ga_trans' ), network_admin_url( 'plugins.php' ) ),
				/* translators: %s: Link to plugins page. */
				'forminator_polls_activate'   => sprintf( __( '<a href="%s">Activate Forminator</a> to enable. This will  include a dataLayer event after a successful poll submission.', 'ga_trans' ), network_admin_url( 'plugins.php' ) ),
				/* translators: %s: Link to Forminator. */
				'forminator_polls_install'    => sprintf( __( '<a href="%s" target="_blank">Install Forminator</a> to enable. This will  include a dataLayer event after a successful poll submission.', 'ga_trans' ), 'https://wpmudev.com/project/forminator-pro/' ),
				'forminator_quizzes'          => __( 'Enable this to include a dataLayer event after a successful quiz submission.', 'ga_trans' ),
				/* translators: %s: Link to plugins page. */
				'forminator_quizzes_update'   => sprintf( __( '<a href="%s">Update Forminator</a> to enable. This will include a dataLayer event after a successful quiz submission.', 'ga_trans' ), network_admin_url( 'plugins.php' ) ),
				/* translators: %s: Link to plugins page. */
				'forminator_quizzes_activate' => sprintf( __( '<a href="%s">Activate Forminator</a> to enable. This will include a dataLayer event after a successful quiz submission.', 'ga_trans' ), network_admin_url( 'plugins.php' ) ),
				/* translators: %s: Link to Forminator. */
				'forminator_quizzes_install'  => sprintf( __( '<a href="%s" target="_blank">Install Forminator</a> to enable. This will include a dataLayer event after a successful quiz submission.', 'ga_trans' ), 'https://wpmudev.com/project/forminator-pro/' ),
				'hustle_leads'                => __( 'Enable this to include a dataLayer event after a successful form submission.', 'ga_trans' ),
				/* translators: %s: Link to Hustle. */
				'hustle_leads_install'        => sprintf( __( '<a href="%s" target="_blank">Install Hustle</a> to enable. This will include a dataLayer event after a successful form submission.', 'ga_trans' ), 'https://wpmudev.com/project/hustle/' ),
				/* translators: %s: Link to Hustle. */
				'hustle_leads_activate'       => sprintf( __( '<a href="%s">Activate Hustle</a> to enable. This will include a dataLayer event after a successful form submission.', 'ga_trans' ), network_admin_url( 'plugins.php' ) ),
			),
			'notice'      => array(
				/* translators: %s: Link to GTM account setup page. */
				'container_id_missing' => __( 'Google Tag Manager has not been added to your website yet. You can easily add it by entering your Container ID on the Google Tag Manager <a href="%s">Account Page</a>.', 'ga_trans' ),
			),
		);
	}
}