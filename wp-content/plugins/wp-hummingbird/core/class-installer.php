<?php
/**
 * Installer class.
 *
 * @author: WPMUDEV, Ignacio Cruz (igmoweb)
 * @version:
 * @package Hummingbird
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Hummingbird_Installer' ) ) {
	/**
	 * Class WP_Hummingbird_Installer
	 *
	 * Manages activation/deactivation and upgrades of Hummingbird
	 */
	class WP_Hummingbird_Installer {

		/**
		 * Plugin activation
		 */
		public static function activate() {
			if ( ! defined( 'WPHB_ACTIVATING' ) ) {
				define( 'WPHB_ACTIVATING', true );
			}

			/* @noinspection PhpIncludeInspection */
			include_once WPHB_DIR_PATH . 'core/class-utils.php';
			/* @noinspection PhpIncludeInspection */
			include_once( WPHB_DIR_PATH . 'core/class-settings.php' );
			/* @noinspection PhpIncludeInspection */
			include_once( WPHB_DIR_PATH . 'core/class-abstract-module.php' );
			/* @noinspection PhpIncludeInspection */
			include_once( WPHB_DIR_PATH . 'core/modules/class-module-uptime.php' );
			/* @noinspection PhpIncludeInspection */
			include_once( WPHB_DIR_PATH . 'core/modules/class-module-cloudflare.php' );

			update_site_option( 'wphb_version', WPHB_VERSION );

			// Add uptime notice.
			update_site_option( 'wphb-notice-uptime-info-show', 'yes' );

			do_action( 'wphb_activate' );
		}

		/**
		 * Plugin activation in a blog (if the site is a multisite)
		 */
		public static function activate_blog() {
			/* @noinspection PhpIncludeInspection */
			include_once WPHB_DIR_PATH . 'core/class-utils.php';

			update_option( 'wphb_version', WPHB_VERSION );

			do_action( 'wphb_activate' );
		}

		/**
		 * Plugin deactivation
		 */
		public static function deactivate() {
			WP_Hummingbird::flush_cache();

			delete_option( 'wphb_cache_folder_error' );
			delete_site_option( 'wphb_version' );
			delete_site_option( 'wphb-gzip-api-checked' );
			delete_site_option( 'wphb-caching-api-checked' );
			delete_site_option( 'wphb-cloudflare-dash-notice' );
			delete_site_option( 'wphb-notice-free-deactivated-dismissed' );
			delete_site_transient( 'wphb-uptime-remotely-enabled' );

			if ( wp_next_scheduled( 'wphb_minify_clear_files' ) ) {
				wp_clear_scheduled_hook( 'wphb_minify_clear_files' );
			}

			/* @var WP_Hummingbird_Module_Page_Cache $module */
			$module = WP_Hummingbird_Utils::get_module( 'page_cache' );
			$module->toggle_service( false );

			do_action( 'wphb_deactivate' );
		}

		/**
		 * Plugin upgrades
		 */
		public static function maybe_upgrade() {
			if ( defined( 'WPHB_ACTIVATING' ) ) {
				// Avoid to execute this over an over in same thread execution.
				return;
			}

			if ( defined( 'WPHB_UPGRADING' ) && WPHB_UPGRADING ) {
				return;
			}

			self::upgrade();
		}

		/**
		 * Upgrade
		 */
		public static function upgrade() {
			$version = get_site_option( 'wphb_version' );

			if ( false === $version ) {
				self::activate();
			}

			if ( is_multisite() ) {
				$blog_version = get_option( 'wphb_version' );
				if ( false === $blog_version ) {
					self::activate_blog();
				}
			}

			if ( false !== $version && WPHB_VERSION !== $version ) {

				if ( ! defined( 'WPHB_UPGRADING' ) ) {
					define( 'WPHB_UPGRADING', true );
				}

				if ( version_compare( $version, '1.7.1', '<' ) ) {
					self::upgrade_1_7_1();
				}

				if ( version_compare( $version, '1.7.2', '<' ) ) {
					self::upgrade_1_7_2();
				}

				if ( version_compare( $version, '1.8.0', '<' ) ) {
					self::upgrade_1_8();
				}

				if ( version_compare( $version, '1.8.0.4', '<' ) ) {
					self::upgrade_1_8_0_4();
				}

				if ( version_compare( $version, '1.9.0', '<' ) ) {
					self::upgrade_1_9_0();
				}

				update_site_option( 'wphb_version', WPHB_VERSION );
			} // End if().
		}

		/**
		 * Upgrades a single blog in a multisite
		 */
		public static function maybe_upgrade_blog() {
			// 1.3.9 is the first version when blog upgrades are executed
			$version = get_option( 'wphb_version', '1.3.9' );

			if ( WPHB_VERSION === $version ) {
				return;
			}

			if ( version_compare( $version, '1.8.0', '<' ) ) {
				self::upgrade_1_8();
			}

			update_option( 'wphb_version', WPHB_VERSION );
		}

		/**
		 * Remove caching option, as browser caching is always enabled by default.
		 * Enable minification advanced view where there are settings already in use.
		 *
		 * @deprecated 1.7.2
		 */
		private static function upgrade_1_7_1() {
			$options = WP_Hummingbird_Settings::get_settings();

			if ( isset( $options['caching'] ) ) {
				unset( $options['caching'] );
				WP_Hummingbird_Settings::update_settings( $options );
			}

			// Check if there are settings for minification.
			$minfication = array( 'block', 'combine', 'position', 'defer', 'inline' );

			foreach ( $minfication as $action ) {
				if ( ! empty( $options[ $action ]['script'] ) || ! empty( $options[ $action ]['styles'] ) ) {
					add_site_option( 'wphb-minification-view', true );
					break;
				}
			}
		}

		/**
		 * Add new dismissable Uptime notice.
		 * Add new option for asset optimization logging.
		 *
		 * @deprecated 1.9.0
		 */
		private static function upgrade_1_7_2() {
			// Add uptime notice.
			update_site_option( 'wphb-notice-uptime-info-show', 'yes' );

			// Disable logging by default.
			WP_Hummingbird_Settings::update_setting( 'minify_log', false );

			// Clear page cache.
			/* @var WP_Hummingbird_Module_Page_Cache $pc_module */
			$pc_module = WP_Hummingbird_Utils::get_module( 'page_cache' );
			if ( $pc_module->is_active() ) {
				$pc_module->clear_cache();
			}
		}

		/**
		 * Upgrade to new database structure for settings.
		 *
		 * @since 1.8.0
		 */
		private static function upgrade_1_8() {
			$options = get_option( 'wphb_settings' );
			if ( is_multisite() ) {
				$options = get_site_option( 'wphb_settings' );
			}

			// Add cache 404 requests to page caching.
			$config_file = WP_CONTENT_DIR . '/wphb-cache/wphb-cache.php';
			if ( file_exists( $config_file ) ) {
				$settings = json_decode( file_get_contents( $config_file ), true );
				if ( ! isset( $settings['settings']['cache_404'] ) ) {
					$settings['settings']['cache_404'] = 0;
					@file_put_contents( $config_file, json_encode( $settings ) );
				}
			}

			// If this is an array, we probably already have a new db structure.
			if ( is_array( $options['minify'] ) ) {
				return;
			}

			$new_settings = array(
				'minify'     => array(
					'enabled'     => isset( $options['minify'] ) ? $options['minify'] : false,
					'use_cdn'     => isset( $options['use_cdn'] ) ? $options['use_cdn'] : false,
					'log'         => isset( $options['minify_log'] ) ? $options['minify_log'] : false,
					// Only for multisites. Toggles minification in a subsite
					// By default is true as if 'minify' is set to false, this option has no meaning.
					'minify_blog' => isset( $options['minify-blog'] ) ? $options['minify-blog'] : true,
					'view'        => 'basic',
					'block'       => array(
						'scripts' => isset( $options['block']['scripts'] ) ? $options['block']['scripts'] : array(),
						'styles'  => isset( $options['block']['styles'] ) ? $options['block']['styles'] : array(),
					),
					'dont_minify' => array(
						'scripts' => isset( $options['dont_minify']['scripts'] ) ? $options['dont_minify']['scripts'] : array(),
						'styles'  => isset( $options['dont_minify']['scripts'] ) ? $options['dont_minify']['scripts'] : array(),
					),
					'combine'     => array(
						'scripts' => isset( $options['combine']['scripts'] ) ? $options['combine']['scripts'] : array(),
						'styles'  => isset( $options['combine']['scripts'] ) ? $options['combine']['scripts'] : array(),
					),
					'position'    => array(
						'scripts' => isset( $options['position']['scripts'] ) ? $options['position']['scripts'] : array(),
						'styles'  => isset( $options['position']['scripts'] ) ? $options['position']['scripts'] : array(),
					),
					'defer'       => array(
						'scripts' => isset( $options['defer']['scripts'] ) ? $options['defer']['scripts'] : array(),
						'styles'  => isset( $options['defer']['scripts'] ) ? $options['defer']['scripts'] : array(),
					),
					'inline'      => array(
						'scripts' => isset( $options['inline']['scripts'] ) ? $options['inline']['scripts'] : array(),
						'styles'  => isset( $options['inline']['scripts'] ) ? $options['inline']['scripts'] : array(),
					),
				),
				'uptime'     => array(
					'enabled' => isset( $options['uptime'] ) ? $options['uptime'] : false,
				),
				'gravatar'   => array(
					'enabled' => isset( $options['gravatar_cache'] ) ? $options['gravatar_cache'] : false,
				),
				'page_cache' => array(
					'enabled' => isset( $options['page_cache'] ) ? $options['page_cache'] : false,
				),
				'caching'    => array(
					// Always enabled, so no 'enabled' option.
					'expiry_css'        => isset( $options['caching_expiry_css'] ) ? $options['caching_expiry_css'] : '8d/A691200',
					'expiry_javascript' => isset( $options['caching_expiry_javascript'] ) ? $options['caching_expiry_javascript'] : '8d/A691200',
					'expiry_media'      => isset( $options['caching_expiry_media'] ) ? $options['caching_expiry_media'] : '8d/A691200',
					'expiry_images'     => isset( $options['caching_expiry_images'] ) ? $options['caching_expiry_images'] : '8d/A691200',
				),
				'cloudflare' => array(
					'enabled'      => isset( $options['cloudflare-connected'] ) ? $options['cloudflare-connected'] : false,
					'connected'    => false,
					'email'        => isset( $options['cloudflare-email'] ) ? $options['cloudflare-email'] : '',
					'api_key'      => isset( $options['cloudflare-api-key'] ) ? $options['cloudflare-api-key'] : '',
					'zone'         => isset( $options['cloudflare-zone'] ) ? $options['cloudflare-zone'] : '',
					'zone_name'    => isset( $options['cloudflare-zone-name'] ) ? $options['cloudflare-zone-name'] : '',
					'plan'         => isset( $options['cloudflare-plan'] ) ? $options['cloudflare-plan'] : false,
					'page_rules'   => isset( $options['cloudflare-page-rules'] ) ? $options['cloudflare-page-rules'] : array(),
					'cache_expiry' => isset( $options['cloudflare-caching-expiry'] ) ? $options['cloudflare-caching-expiry'] : 691200,
				),
			);

			// Asset optimization view.
			if ( get_site_option( 'wphb-minification-view' ) ) {
				$new_settings['minify']['view'] = 'advanced';
			}

			$cf_connected = WP_Hummingbird_Settings::get_setting( 'wphb-is-cloudflare' );
			if ( isset( $cf_connected ) ) {
				$new_settings['cloudflare']['connected'] = (bool) $cf_connected;
			}

			if ( isset( $options['email-notifications'] ) && $options['email-notifications'] ) {
				$frequency = array( 1, 7, 30 );
				$week_days = array(
					'Monday',
					'Tuesday',
					'Wednesday',
					'Thursday',
					'Friday',
					'Saturday',
					'Sunday',
				);

				$day = $week_days[ array_rand( $week_days, 1 ) ];

				$hour = mt_rand( 0, 23 ) . ':00';

				$new_settings['performance'] = array(
					'reports'    => $options['email-notifications'],
					'recipients' => $options['email-recipients'],
					'frequency'  => in_array( $options['email-frequency'], $frequency, true ) ? $options['email-frequency'] : 7,
					'day'        => in_array( $options['email-frequency'], $week_days, true ) ? $options['email-frequency'] : $day,
					'time'       => $hour,
					'last_sent'  => '',
				);

				$last_sent_report = WP_Hummingbird_Settings::get_setting( 'wphb-last-sent-report' );
				if ( isset( $last_sent_report ) ) {
					$new_settings['performance']['last_sent'] = $last_sent_report;
				}
			} else {
				$new_settings['performance']['reports'] = false;
			}

			if ( isset( $options['subsite-tests'] ) ) {
				$new_settings['performance']['subsite_tests'] = $options['subsite-tests'];
			} else {
				$new_settings['performance']['subsite_tests'] = false;
			}

			if ( ! is_multisite() ) {
				update_option( 'wphb_settings', $new_settings );
			} else {
				update_site_option( 'wphb_settings', $new_settings );
			}

			// Delete old options.
			delete_option( 'wphb-last-sent-report' );
			delete_site_option( 'wphb-is-cloudflare' );
			delete_site_option( 'wphb-minification-view' );

			// Move wphb-last-report-score option to last_score.
			$last_report = get_option( 'wphb-last-report-score' );
			WP_Hummingbird_Settings::update_setting( 'last_score', $last_report['score'], 'performance' );
			delete_site_option( 'wphb-last-report-score' );

			// If page caching is active, we need to update the advanced-cache.php file with the new paths.
			if ( $options['page_cache'] ) {
				/* @var WP_Hummingbird_Module_Page_Cache $pc_module */
				$pc_module = WP_Hummingbird_Utils::get_module( 'page_cache' );
				$pc_module->toggle_service( false );
				$pc_module->toggle_service( true );
			}
		}

		/**
		 * Fix for corrupt scheduled performance scans.
		 *
		 * @since 1.8.0.4
		 */
		private static function upgrade_1_8_0_4() {
			wp_clear_scheduled_hook( 'wphb_performance_scan' );

			$options = WP_Hummingbird_Settings::get_settings( 'performance' );

			// If not member, unset schedule.
			if ( ! WP_Hummingbird_Utils::is_member() ) {
				$options['reports'] = false;
				unset( $options['frequency'] );
				unset( $options['day'] );
				unset( $options['time'] );
			}

			// If schedule is corrupt, reset it.
			if ( isset( $options['reports'] ) && $options['reports'] ) {
				$week_days = array(
					'Monday',
					'Tuesday',
					'Wednesday',
					'Thursday',
					'Friday',
					'Saturday',
					'Sunday',
				);

				if ( ! isset( $options['day'] ) || ! in_array( $options['day'], $week_days, true ) ) {
					$options['day'] = $week_days[ array_rand( $week_days, 1 ) ];
				}

				$options['time'] = mt_rand( 0, 23 ) . ':00';
				$options['last_sent'] = '';

				$frequency = array( 1, 7, 30 );
				if ( ! isset( $options['frequency'] ) || ! in_array( $options['frequency'], $frequency, true ) ) {
					$options['frequency'] = 7;
				}
				wp_schedule_single_event( WP_Hummingbird_Module_Reporting_Cron::get_scheduled_scan_time(), 'wphb_performance_scan' );
			} else {
				$options['reports'] = false;
			}

			WP_Hummingbird_Settings::update_settings( $options, 'performance' );

			// Schedule next scan.
			if ( WP_Hummingbird_Utils::is_member() && $options['reports'] ) {
				wp_schedule_single_event( WP_Hummingbird_Module_Reporting_Cron::get_scheduled_scan_time(), 'wphb_performance_scan' );
			}
		}

		/**
		 * Upgrade to 1.9
		 *
		 * Remove wphb-server-type option, because we are not using it anymore.
		 */
		private static function upgrade_1_9_0() {
			delete_site_option( 'wphb-server-type' );
			delete_metadata( 'user', '', 'wphb-server-type', '', true );
		}

	}
} // End if().