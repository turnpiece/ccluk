<?php

namespace MC4WP\Sync\Admin;

use MC4WP\Sync\Plugin;
use MC4WP\Sync\UserSubscriber;
use MC4WP\Sync\Users;
use MC4WP_MailChimp;
use MC4WP_Queue;
use WP_User;

class Manager {

	/**
	 * @const string
	 */
	const SETTINGS_CAP = 'manage_options';

	/**
	 * @var array $options
	 */
	private $options;

	/**
	 * @var Users
	 */
	protected $users;

    /**
     * @var MC4WP_Queue
     */
	private $queue;

	/**
	 * @var FlashMessages
	 */
	private $flash;

	/**
	 * Constructor
	 *
	 * @param array $options
	 * @param Users $users
	 * @param MC4WP_Queue $queue
	 */
	public function __construct( array $options, $users, $queue ) {
		$this->options = $options;
		$this->plugin_slug = plugin_basename( Plugin::FILE );
		$this->users = $users;
		$this->queue = $queue;
		$this->flash = new FlashMessages( 'mc4wp_sync_flash' );
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_filter( 'mc4wp_admin_menu_items', array( $this, 'add_menu_items' ) );
		add_action( 'mc4wp_admin_process_user_sync_queue', array( $this, 'process_queue' ) );
		add_action( 'admin_footer_text', array( $this, 'footer_text' ), 11 );
	}

	

	/**
	 * Runs on `admin_init`
	 */
	public function init() {

		// only run for administrators
		if( ! current_user_can( self::SETTINGS_CAP ) ) {
			return false;
		}

		// register settings
		register_setting( Plugin::OPTION_NAME, Plugin::OPTION_NAME, array( $this, 'sanitize_settings' ) );

		// add link to settings page from plugins page
		add_filter( 'plugin_action_links_' . $this->plugin_slug, array( $this, 'add_plugin_settings_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links'), 10, 2 );

		add_action( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
	}

	/**
	 * Show notices from flash.
	 */
	public function show_notices() {

		// show warnings
		foreach( $this->flash->get('warning') as $message ) {
			echo '<div class="notice notice-warning">';
			echo '<p>' . $message . '</p>';
			echo '</div>';
		}

		// show notices
		foreach( $this->flash->get('success') as $message ) {
			echo '<div class="notice notice-success">';
			echo '<p>' . $message . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Register menu pages
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function add_menu_items( $items ) {
		$item = array(
			'title' => __( 'MailChimp User Sync', 'mailchimp-sync' ),
			'text' => __( 'User Sync', 'mailchimp-sync' ),
			'slug' => 'sync',
			'callback' => array( $this, 'show_settings_page' )
		);

		$items[] = $item;
		return $items;
	}

	/**
	 * Add the settings link to the Plugins overview
	 *
	 * @param array $links
	 * @return array
	 */
	public function add_plugin_settings_link( $links ) {
		$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=mailchimp-for-wp-sync' ), __( 'Settings', 'mailchimp-for-wp' ) );
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Adds meta links to the plugin in the WP Admin > Plugins screen
	 *
	 * @param array $links
	 * @return array
	 */
	public function add_plugin_meta_links( $links, $file ) {
		if( $file !== $this->plugin_slug ) {
			return $links;
		}

		$links[] = sprintf( __( 'An add-on for %s', 'mailchimp-sync' ), '<a href="https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-user-sync&utm_campaign=plugins-page">MailChimp for WordPress</a>' );
		return $links;
	}

	/**
	 * Load assets if we're on the settings page of this plugin
	 *
	 * @return bool
	 */
	public function load_assets() {

		if( ! isset( $_GET['page'] ) || $_GET['page'] !== 'mailchimp-for-wp-sync' ) {
			return false;
		}

		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style( 'mailchimp-sync-admin', $this->asset_url( "/css/admin{$min}.css" ) );
		wp_enqueue_script( 'es5-polyfill', 'https://cdnjs.cloudflare.com/ajax/libs/es5-shim/4.0.3/es5-shim.min.js' );
		wp_enqueue_script( 'mailchimp-sync-wizard', $this->asset_url( "/js/admin{$min}.js" ), array( 'suggest', 'jquery' ), Plugin::VERSION, true );

		return true;
	}

	/**
	 * Outputs the settings page
	 */
	public function show_settings_page() {

		$lists = $this->get_mailchimp_lists();
		$queue = $this->queue;

		if( $this->options['list'] !== '' && isset( $lists[ $this->options['list'] ] ) ) {
			$selected_list = $lists[ $this->options['list'] ];
			$available_mailchimp_fields = array_diff_key( $selected_list->merge_vars, array( 'EMAIL' ) );
		}

		$this->options['field_mappers'] = array_values( $this->options['field_mappers'] );

		// add empty field so we can add more rules
		$this->options['field_mappers'][] = array( 'user_field' => '', 'mailchimp_field' => '' );

		require __DIR__  . '/views/settings-page.php';
	}

	/**
	 * @param $url
	 *
	 * @return string
	 */
	protected function asset_url( $url ) {
		return plugins_url( '/assets' . $url, Plugin::FILE );
	}

	/**
	 * @param $option_name
	 *
	 * @return string
	 */
	protected function name_attr( $option_name ) {

		if( substr( $option_name, -1 ) !== ']' ) {
			return Plugin::OPTION_NAME . '[' . $option_name . ']';
		}

		return Plugin::OPTION_NAME . $option_name;
	}

	/**
	 * @param array $dirty
	 *
	 * @return array $clean
	 */
	public function sanitize_settings( array $dirty ) {

		$clean = $dirty;

		// empty field mappers if list changed
		if( $this->options['list'] !== $clean['list'] ) {
			unset( $clean['field_mappers'] );
		}


		if( isset( $clean['field_mappers'] ) ) {

			// make sure this is an array
			if( ! is_array( $clean['field_mappers'] ) ) {
				unset( $clean['field_mappers'] );
			}

			foreach( $clean['field_mappers'] as $key=> $mapper ) {

				if( empty( $mapper['user_field'] ) || empty( $mapper['mailchimp_field'] ) ) {
					unset( $clean['field_mappers'][ $key ] );
					continue;
				}

				// trim values
				$clean['field_mappers'][ $key ] = array(
					'user_field' => trim( $mapper['user_field'] ),
					'mailchimp_field' => trim( $mapper['mailchimp_field'] )
				);
			}
		}

		// reschedule action if needed
        mc4wp_sync_setup_schedule();

		return $clean;
	}

	/**
	 * Helper function to retrieve MailChimp lists through MailChimp for WordPress
	 *
	 * Will try v3.0+ first, then fallback to older versions.
	 *
	 * @return array
	 */
	protected function get_mailchimp_lists() {
		$mailchimp = new MC4WP_MailChimp();
		return $mailchimp->get_lists();

	}

	/**
	 * Ask for a plugin review in the WP Admin footer, if this is one of the plugin pages.
	 *
	 * @param $text
	 *
	 * @return string
	 */
	public function footer_text( $text ) {

		if( ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'mailchimp-for-wp-sync' ) === 0 ) ) {
			$text = sprintf( 'If you enjoy using <strong>MailChimp Sync</strong>, please leave us a <a href="%s" target="_blank">★★★★★</a> rating. A <strong style="text-decoration: underline;">huge</strong> thank you in advance!', 'https://wordpress.org/support/view/plugin-reviews/mailchimp-sync?rate=5#postform' );
		}

		return $text;
	}

	/**
	 * Returns a HEX color from a percentage (red to green)
	 *
	 * @param        $value
	 * @param int    $brightness
	 * @param int    $max
	 * @param int    $min
	 * @param string $thirdColorHex
	 *
	 * @return string
	 */
	protected function percentage_to_color( $value, $brightness = 255, $max = 100, $min = 0, $thirdColorHex = '00') {
		// Calculate first and second color (Inverse relationship)
		$first = (1-($value/$max))*$brightness;
		$second = ($value/$max)*$brightness;
		// Find the influence of the middle color (yellow if 1st and 2nd are red and green)
		$diff = abs($first-$second);
		$influence = ($brightness-$diff)/2;
		$first = intval($first + $influence);
		$second = intval($second + $influence);
		// Convert to HEX, format and return
		$firstHex = str_pad(dechex($first),2,0,STR_PAD_LEFT);
		$secondHex = str_pad(dechex($second),2,0,STR_PAD_LEFT);
		return $firstHex . $secondHex . $thirdColorHex ;
	}

	/**
	* Processes all queued background jobs
	*/
	public function process_queue() {
		do_action( 'mailchimp_user_sync_run' );	
	}


}
