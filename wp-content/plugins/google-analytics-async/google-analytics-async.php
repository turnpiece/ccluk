<?php
/*
Plugin Name: Google Analytics +
Plugin URI: http://premium.wpmudev.org/project/google-analytics-for-wordpress-mu-sitewide-and-single-blog-solution/
Description: Enables Google Analytics for your site with statistics inside WordPress admin panel. Single and multi site compatible!
Author: WPMU DEV
Author URI: http://premium.wpmudev.org
Version: 3.1.7.1
WDP ID: 51
License: GNU General Public License (Version 2 - GPLv2)
*/

/*
Copyright 2007-2014 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Google_Analytics_Async
 *
 * @package Google Analytics
 * @copyright Incsub 2007-2014 {@link http://incsub.com}
 * @author Ivan Shaovchev (Incsub) {@link http://ivan.sh}
 * @license GNU General Public License (Version 2 - GPLv2) {@link http://www.gnu.org/licenses/gpl-2.0.html}
 */

define( 'GOOLGEANALYTICS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

class Google_Analytics_Async {

    /** @var string $text_domain The text domain of the plugin */
    var $text_domain = 'ga_trans';
    /** @var string $plugin_dir The plugin directory path */
    var $plugin_dir;
    /** @var string $plugin_url The plugin directory URL */
    var $plugin_url;
    /** @var string $options_name The plugin options string */
    var $options_name = 'ga2_settings';
    /** @var array $settings The plugin site options */
    var $settings;
    /** @var array $settings The plugin network options */
    var $network_settings;
    /** @var array $settings The plugin network or site options depending on localization in admin page */
    var $current_settings;

    /**
     * Constructor.
     */
    function __construct() {
        //Loads WPMUDEV dashboard
        if(file_exists(GOOLGEANALYTICS_PLUGIN_DIR.'google-analytics-async-files/externals/dash-notice/wpmudev-dash-notification.php')) {
            global $wpmudev_notices;
            $wpmudev_notices[] = array( 'id'=> 51, 'name'=> 'Google Analytics', 'screens' => array( 'settings_page_google-analytics-network', 'settings_page_google-analytics' ) );
            include_once(GOOLGEANALYTICS_PLUGIN_DIR.'google-analytics-async-files/externals/dash-notice/wpmudev-dash-notification.php');
        }

        $this->init_vars();
		$this->init();
    }

    /**
     * Initiate plugin.
     *
     * @return void
     */
    function init() {
        add_action( 'init', array( &$this, 'load_plugin_textdomain' ), 0 );
        add_action( 'init', array( &$this, 'enable_admin_tracking' ) );
        add_action( 'admin_init', array( &$this, 'handle_page_requests' ) );
        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
        add_action( 'network_admin_menu', array( &$this, 'network_admin_menu' ) );
        add_action( 'wp_head', array( &$this, 'tracking_code_output' ) );

        //add CSS
        add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
    }

    /**
     * Initiate variables.
     *
     * @return void
     */
    function init_vars() {
        $this->settings = $this->get_options();
        $this->network_settings = $this->get_options(null, 'network');
        $this->current_settings = is_network_admin() ? $this->network_settings : $this->settings;
        if(is_multisite() && !is_network_admin() && (!isset($this->network_settings['track_settings']['capability_reports_overwrite']) || (isset($this->network_settings['track_settings']['capability_reports_overwrite']) && !$this->network_settings['track_settings']['capability_reports_overwrite']))) {
            $this->current_settings['track_settings']['minimum_capability_reports'] = isset($this->network_settings['track_settings']['minimum_capability_reports']) ? $this->network_settings['track_settings']['minimum_capability_reports'] : '';
            $this->current_settings['track_settings']['minimum_role_capability_reports'] = isset($this->network_settings['track_settings']['minimum_role_capability_reports']) ? $this->network_settings['track_settings']['minimum_role_capability_reports'] : '';
        }

        /* Set plugin directory path */
        $this->plugin_dir = GOOLGEANALYTICS_PLUGIN_DIR;
        /* Set plugin directory URL */
        $this->plugin_url = plugin_dir_url(__FILE__);
    }

    /**
     * Add CSS
     *
     * @return void
     */
    function admin_enqueue_scripts($hook) {
        // Including CSS file
        if($hook == 'settings_page_google-analytics') {
            wp_register_style( 'GoogleAnalyticsAsyncStyle', $this->plugin_url . 'google-analytics-async-files/ga-async.css', array(), 2 );
            wp_enqueue_style( 'GoogleAnalyticsAsyncStyle' );
        }
    }

    /**
     * Loads the language file from the "languages" directory.
     *
     * @return void
     */
    function load_plugin_textdomain() {
        load_plugin_textdomain( $this->text_domain, null, dirname( plugin_basename( __FILE__ ) ) . '/google-analytics-async-files/languages/' );
    }

    /**
     * Add Google Analytics options page.
     *
     * @return void
     */
    function admin_menu() {
        /* If Supporter enabled but specific option disabled, disable menu */
        if (
            !is_super_admin()
            && !empty( $this->network_settings['track_settings']['supporter_only'] )
            && function_exists('is_pro_site')
            && !is_pro_site(get_current_blog_id(), $this->network_settings['track_settings']['supporter_only'])
            && apply_filters('ga_additional_block', true)
        ) {
            return;
        } else {
            add_submenu_page( 'options-general.php', 'Google Analytics', 'Google Analytics', 'manage_options', 'google-analytics', array( &$this, 'output_site_settings_page' ) );
        }
    }

	/**
	 * Add network admin menu
	 *
	 * @access public
	 * @return void
	 */
	function network_admin_menu() {
        add_submenu_page( 'settings.php', 'Google Analytics', 'Google Analytics', 'manage_network', 'google-analytics', array( &$this, 'output_network_settings_page' ) );
	}

    /**
     * Enable admin tracking.
     *
     * @return void
     */
    function enable_admin_tracking() {
		if ( !empty( $this->network_settings['track_settings']['track_admin'] ) )
            add_action( 'admin_head', array( &$this, 'tracking_code_output' ) );
    }

    /**
     * Google Analytics code output.
     *
     * @return void
     */
    function tracking_code_output() {
        if(is_preview() || wp_doing_ajax()) {
            return false;
        }

        $network_settings = isset( $this->network_settings['track_settings'] ) ? $this->network_settings['track_settings'] : array();
        $site_settings    = isset( $this->settings['track_settings'] ) ? $this->settings['track_settings'] : array();

        /* Unset tracking code if it matches the root site one */
		if ( isset( $network_settings['tracking_code'] )
			&& isset( $site_settings['tracking_code'] )
			&& $network_settings['tracking_code'] == $site_settings['tracking_code']
		) {
			unset( $site_settings['tracking_code'] );
		}

        if (
            ( isset( $network_settings['tracking_code'] ) && !empty( $network_settings['tracking_code'] ) ) ||
            ( !is_admin() && isset( $site_settings['tracking_code'] ) && !empty( $site_settings['tracking_code'] ) )
        ):

        if ( isset( $network_settings['anonymize_ip'] ) && $network_settings['anonymize_ip'] && isset( $network_settings['anonymize_ip_force'] ) && $network_settings['anonymize_ip_force'] ) {
            $site_settings['anonymize_ip'] = true;
        }
        ?>

            <script>
                (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                })(window,document,'script','//www.google-analytics.com/analytics.js','gaplusu');

                function gaplus_track() {
                    <?php if ( isset( $network_settings['tracking_code'] ) && !empty( $network_settings['tracking_code'] ) ): ?>
                            gaplusu('create', '<?php echo $network_settings['tracking_code']; ?>', 'auto');
                        <?php if ( isset( $network_settings['anonymize_ip'] ) && $network_settings['anonymize_ip'] ): ?>
                            gaplusu('set', 'anonymizeIp', true);
                        <?php endif; ?>
                        <?php if ( isset( $network_settings['display_advertising'] ) && $network_settings['display_advertising'] ): ?>
                            gaplusu('require', 'displayfeatures');
                        <?php endif; ?>
                        <?php do_action('ga_plus_network_tracking_code_add_vars', ''); ?>
                            gaplusu('send', 'pageview');
                    <?php endif; ?>

                    <?php if ( !is_admin() && isset( $site_settings['tracking_code'] ) && !empty( $site_settings['tracking_code'] ) ): ?>
                            gaplusu('create', '<?php echo $site_settings['tracking_code']; ?>', 'auto', {'name': 'single'});
                        <?php if ( isset($site_settings['anonymize_ip']) && !empty( $site_settings['anonymize_ip'] ) ): ?>
                            gaplusu('single.set', 'anonymizeIp', true);
                        <?php endif; ?>
                        <?php if ( $site_settings['display_advertising'] ): ?>
                            gaplusu('single.require', 'displayfeatures');
                        <?php endif; ?>
                            <?php do_action('ga_plus_site_tracking_code_add_vars', 'b'); ?>
                            gaplusu('single.send', 'pageview');
                    <?php endif; ?>
                }

                <?php if(apply_filters('ga_load_tracking', true)) { ?>
                    gaplus_track();
                <?php } ?>

            </script>

		<?php
        endif;
    }

    /**
     * Update Google Analytics settings into DB.
     *
     * @return void
     */
    function handle_page_requests() {
        if ( isset( $_POST['submit'] ) ) {

			if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'submit_settings_network' ) ) {
            //save network settings
                $this->save_options( array('track_settings' => $_POST), 'network' );

                wp_redirect( add_query_arg( array( 'page' => 'google-analytics', 'dmsg' => urlencode( __( 'Changes were saved!', $this->text_domain ) ) ), 'settings.php' ) );
                exit;
			}
			elseif ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'submit_settings' ) ) {
            //save settings

                $this->save_options( array('track_settings' => $_POST) );

                wp_redirect( add_query_arg( array( 'page' => 'google-analytics', 'dmsg' => urlencode( __( 'Changes were saved!', $this->text_domain ) ) ), 'options-general.php' ) );
                exit;
			}
        }

        if(function_exists('wp_add_privacy_policy_content')) {
            $privacy_text = __( "This website uses Google Analytics to track website traffic. Collected data is processed in such a way that visitors cannot be identified.", $this->text_domain );
            wp_add_privacy_policy_content('Google Analytics +', $privacy_text);
        }
    }

	/**
	 * Network settings page
	 *
	 * @access public
	 * @return void
	 */
	function output_network_settings_page() {
        /* Get Network settings */
        $this->output_site_settings_page( 'network' );
	}

    /**
     * Admin options page output
     *
     * @return void
     */
    function output_site_settings_page( $network = '' ) {
	    global $google_analytics_async_dashboard;
        $google_loggedin = isset($this->current_settings['google_login']['logged_in']) ? 1 : 0;
        /* analytics repot account */
        if($google_loggedin) {
            $accounts = $google_analytics_async_dashboard->get_accounts();
        }

        require_once( $this->plugin_dir . "google-analytics-async-files/page-settings.php" );
    }

    /**
     * Save plugin options.
     *
     * @param  array $params The $_POST array
     * @return void
     */
    function save_options( $params, $network = ''  ) {
        /* Remove unwanted parameters */
        unset( $params['_wpnonce'], $params['_wp_http_referer'], $params['submit'] );
        /* Update options by merging the old ones */

        if ( '' == $network )
            $options = get_option( $this->options_name );
        else
            $options = get_site_option( $this->options_name );

        if(!is_array($options))
            $options = array();

        $options = array_merge( $options, $params );

        if ( '' == $network )
            update_option( $this->options_name, $options );
        else
            update_site_option( $this->options_name, $options );
    }

    /**
     * Get plugin options.
     *
     * @param  string|NULL $key The key for that plugin option.
     * @return array $options Plugin options or empty array if no options are found
     */
    function get_options( $key = null, $network = '' ) {

        if ( '' == $network )
            $options = get_option( $this->options_name );
        else
            $options = get_site_option( $this->options_name );

        if(!is_array($options))
            $options = array();

        do_action('ga_plus_before_return_options', $options, $network, $this->options_name);
		$options = apply_filters('ga_get_options', $options, $network, $this->options_name);

        /* Check if specific plugin option is requested and return it */
        if ( isset( $key ) && array_key_exists( $key, $options ) )
            return $options[$key];
        else
            return $options;
    }
}

global $google_analytics_async;
$google_analytics_async = new Google_Analytics_Async();

include_once 'google-analytics-async-files/class-google-analytics-async-dashboard.php';