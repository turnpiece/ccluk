<?php

use WP_Defender\Module\Two_Factor\Model\Auth_Settings;
use WP_Defender\Module\Advanced_Tools\Model\Mask_Settings;
use WP_Defender\Module\Setting\Model\Settings;

/**
 * Author: Hoang Ngo
 */
class WD_Main_Activator {
	public $wp_defender;

	public function __construct( WP_Defender $wp_defender ) {
		add_action( 'init', array( &$this, 'init' ), 9 );
		add_action( 'wp_loaded', array( &$this, 'maybeShowUpgradedNotice' ), 9 );
		add_action( 'init', array( &$this, 'upgradeHook' ), 5 );
	}

	public function upgradeHook() {
		$db_ver = get_site_option( 'wd_db_version' );
		if ( false != $db_ver && version_compare( $db_ver, '2.3.2', '>=' ) ) {
			return;
		}
		if ( false != $db_ver && version_compare( $db_ver, '2.2.9', '<' ) ) {
			// Migrate security headers into Advanced Tools from Security Tweaks
			$this->migrateSecurityHeaders();
		}

		if ( false !== $db_ver && version_compare( $db_ver, '2.3.2', '<' ) ) {
			//add a flag to show tutorials
			update_site_option( 'wp_defender_show_tutorials', true );
		}

		if ( false !== $db_ver && version_compare( $db_ver, '2.3.1', '<' ) ) {
			//add a flag for showing new feature
			update_site_option( 'waf_show_new_feature', true );
		}

		\WP_Defender\Module\Setting\Component\Backup_Settings::backupData();
		if ( false != $db_ver && version_compare( $db_ver, '2.2', '<' ) ) {
			$scan_settings = get_site_option( 'wd_scan_settings' );
			$settings      = \WP_Defender\Module\Scan\Model\Settings::instance();
			if ( isset( $scan_settings['receiptsNotification'] ) ) {
				$settings->recipients_notification = $scan_settings['receiptsNotification'];
			}
			if ( isset( $scan_settings['receipts'] ) ) {
				$settings->recipients = $scan_settings['receipts'];
			}
			if ( isset( $scan_settings['alwaysSendNotification'] ) ) {
				$settings->always_send_notification = $scan_settings['alwaysSendNotification'];
			}
			$result    = $settings->save();
			$msettings = get_site_option( 'wd_main_settings' );
			if ( isset( $msettings['high_contrast_mode'] ) ) {
				$highcontast                  = filter_var( $msettings['high_contrast_mode'], FILTER_VALIDATE_BOOLEAN );
				$settings                     = Settings::instance();
				$settings->high_contrast_mode = $highcontast;
				$ret                          = $settings->save();
			}
		}
		if ( false != $db_ver && version_compare( $db_ver, '2.2.1', '<' ) ) {
			$mask_url_settings = get_site_option( 'wd_masking_login_settings' );
			$model             = Mask_Settings::instance();

			if ( isset( $mask_url_settings['maskUrl'] ) ) {
				$model->mask_url = $mask_url_settings['maskUrl'];
			}
			if ( isset( $mask_url_settings['redirectTraffic'] ) ) {
				$model->redirect_traffic = $mask_url_settings['redirectTraffic'];
			}
			if ( isset( $mask_url_settings['redirectTrafficUrl'] ) ) {
				$model->redirect_traffic_url = $mask_url_settings['redirectTrafficUrl'];
			}
			//delete cache to force update
			$ret              = $model->save();
			$factors_settings = get_site_option( 'wd_2auth_settings' );
			$settings         = Auth_Settings::instance();
			if ( isset( $factors_settings['lostPhone'] ) ) {
				$settings->lost_phone = $factors_settings['lostPhone'];
			}
			if ( isset( $factors_settings['forceAuth'] ) ) {
				$settings->force_auth = $factors_settings['forceAuth'];
			}
			if ( isset( $factors_settings['forceAuthMess'] ) ) {
				$settings->force_auth_mess = $factors_settings['forceAuthMess'];
			}
			if ( isset( $factors_settings['userRoles'] ) ) {
				$settings->user_roles = $factors_settings['userRoles'];
			}
			if ( isset( $factors_settings['forceAuthRoles'] ) ) {
				$settings->force_auth_roles = $factors_settings['forceAuthRoles'];
			}
			if ( isset( $factors_settings['customGraphicURL'] ) ) {
				$settings->custom_graphic_url = $factors_settings['customGraphicURL'];
			}
			if ( isset( $factors_settings['customGraphic'] ) ) {
				$settings->custom_graphic = $factors_settings['customGraphic'];
			}
			$ret = $settings->save();

			//convert slug
			$hardener_settings = get_site_option( 'wd_hardener_settings' );
			if ( is_array( $hardener_settings ) ) {
				$ignore = $hardener_settings['ignore'];
				foreach ( $ignore as $key => $slug ) {
					if ( $slug == 'change_admin' ) {
						$slug = 'replace-admin-username';
					}
					$slug           = str_replace( '_', '-', $slug );
					$ignore[ $key ] = $slug;
				}
				$hardener_settings['ignore'] = $ignore;
				update_site_option( 'wd_hardener_settings', $hardener_settings );
			}
		}
		update_site_option( 'wd_db_version', wp_defender()->db_version );
	}

	public function migrateSecurityHeaders() {
		$sh_settings = get_site_option( 'wd_security_headers_settings' );
		if ( empty( $sh_settings ) ) {
			$model = \WP_Defender\Module\Advanced_Tools\Model\Security_Headers_Settings::instance();
			//Part of Security tweaks data
			$old_settings = get_site_option( 'wd_hardener_settings' );
			if ( ! is_array( $old_settings ) ) {
				$old_settings = json_decode( $old_settings, true );
				if ( is_array( $old_settings ) && isset( $old_settings['data'] ) && ! empty( $old_settings['data'] ) ) {
					//Exists 'X-Frame-Options'
					if ( isset( $old_settings['data']['sh_xframe'] ) && ! empty( $old_settings['data']['sh_xframe'] ) ) {
						$header_data = $old_settings['data']['sh_xframe'];

						$mode = ( isset( $header_data['mode'] ) && ! empty( $header_data['mode'] ) )
							? strtolower( $header_data['mode'] )
							: false;
						if ( 'allow-from' === $mode ) {
							$model->sh_xframe_mode = 'allow-from';
							if ( isset( $header_data['values'] ) && ! empty( $header_data['values'] ) ) {
								$urls                  = explode( ' ', $header_data['values'] );
								$model->sh_xframe_urls = implode( PHP_EOL, $urls );
							}
						} elseif ( in_array( $mode, array( 'sameorigin', 'deny' ), true ) ) {
							$model->sh_xframe_mode = $mode;
						}
						$model->sh_xframe = true;
					}

					//Exists 'X-XSS-Protection'
					if ( isset( $old_settings['data']['sh_xss_protection'] ) && ! empty( $old_settings['data']['sh_xss_protection'] ) ) {
						$header_data = $old_settings['data']['sh_xss_protection'];

						if ( isset( $header_data['mode'] )
						     && ! empty( $header_data['mode'] )
						     && in_array( $header_data['mode'], array( 'sanitize', 'block' ), true )
						) {
							$model->sh_xss_protection_mode = $header_data['mode'];
							$model->sh_xss_protection      = true;
						}
					}

					//Exists 'X-Content-Type-Options'
					if ( isset( $old_settings['data']['sh_content_type_options'] ) && ! empty( $old_settings['data']['sh_content_type_options'] ) ) {
						$header_data = $old_settings['data']['sh_content_type_options'];

						if ( isset( $header_data['mode'] ) && ! empty( $header_data['mode'] ) ) {
							$model->sh_content_type_options_mode = $header_data['mode'];
							$model->sh_content_type_options      = true;
						}
					}

					//Exists 'Strict Transport'
					if ( isset( $old_settings['data']['sh_strict_transport'] ) && ! empty( $old_settings['data']['sh_strict_transport'] ) ) {
						$header_data = $old_settings['data']['sh_strict_transport'];

						if ( isset( $header_data['hsts_preload'] ) && ! empty( $header_data['hsts_preload'] ) ) {
							$model->hsts_preload = (int) $header_data['hsts_preload'];
						}
						if ( isset( $header_data['include_subdomain'] ) && ! empty( $header_data['include_subdomain'] ) ) {
							$model->include_subdomain = in_array( $header_data['include_subdomain'], array(
								'true',
								'1',
								1
							), true ) ? 1 : 0;
						}
						if ( isset( $header_data['hsts_cache_duration'] ) && ! empty( $header_data['hsts_cache_duration'] ) ) {
							$model->hsts_cache_duration = $header_data['hsts_cache_duration'];
						}
						$model->sh_strict_transport = true;
					}

					//Exists 'Referrer Policy'
					if ( isset( $old_settings['data']['sh_referrer_policy'] ) && ! empty( $old_settings['data']['sh_referrer_policy'] ) ) {
						$header_data = $old_settings['data']['sh_referrer_policy'];

						if ( isset( $header_data['mode'] ) && ! empty( $header_data['mode'] ) ) {
							$model->sh_referrer_policy_mode = $header_data['mode'];
							$model->sh_referrer_policy      = true;
						}
					}

					//Exists 'Feature-Policy'
					if ( isset( $old_settings['data']['sh_feature_policy'] ) && ! empty( $old_settings['data']['sh_feature_policy'] ) ) {
						$header_data = $old_settings['data']['sh_feature_policy'];

						if ( isset( $header_data['mode'] ) && ! empty( $header_data['mode'] ) ) {
							$mode                          = strtolower( $header_data['mode'] );
							$model->sh_feature_policy_mode = $mode;
							if ( 'origins' === $mode && isset( $header_data['values'] ) && ! empty( $header_data['values'] ) ) {
								//The values differ from the values of the 'X-Frame-Options' key, because they may be array.
								if ( is_array( $header_data['values'] ) ) {
									$model->sh_feature_policy_urls = implode( PHP_EOL, $header_data['values'] );
									//otherwise
								} elseif ( is_string( $header_data['values'] ) ) {
									$urls                          = explode( ' ', $header_data['values'] );
									$model->sh_feature_policy_urls = implode( PHP_EOL, $urls );
								}
							}
							$model->sh_feature_policy = true;
						}
					}
					//Save
					$model->save();
				}
			}
		}
	}

	/**
	 * Initial
	 */
	public function init() {
		add_filter(
			'plugin_action_links_' . plugin_basename( wp_defender()->plugin_slug ),
			array(
				&$this,
				'addSettingsLink',
			)
		);

		if ( ! \WP_Defender\Behavior\Utils::instance()->checkRequirement() ) {
			//requirement not met, return
			return;
		} else {
			if ( \WP_Defender\Behavior\Utils::instance()->getAPIKey() == false ) {
				wp_defender()->isFree = 1;
			}
			//start to init navigators
			\Hammer\Base\Container::instance()->set( 'dashboard', new \WP_Defender\Controller\Dashboard() );
			\Hammer\Base\Container::instance()->set( 'hardener', new \WP_Defender\Module\Hardener() );
			\Hammer\Base\Container::instance()->set( 'scan', new \WP_Defender\Module\Scan() );
			\Hammer\Base\Container::instance()->set( 'audit', new \WP_Defender\Module\Audit() );
			\Hammer\Base\Container::instance()->set( 'lockout', new \WP_Defender\Module\IP_Lockout() );
			\Hammer\Base\Container::instance()->set( 'waf', new \WP_Defender\Controller\Waf() );
			\Hammer\Base\Container::instance()->set( 'two_fa', new \WP_Defender\Module\Two_Factor() );
			\Hammer\Base\Container::instance()->set( 'advanced_tool', new \WP_Defender\Module\Advanced_Tools() );
			\Hammer\Base\Container::instance()->set( 'gdpr', new \WP_Defender\Controller\GDPR() );
			\Hammer\Base\Container::instance()->set( 'setting', new \WP_Defender\Module\Setting() );
			\Hammer\Base\Container::instance()->set( 'tutorial', new \WP_Defender\Controller\Tutorial() );
			//no need to set debug
			new \WP_Defender\Controller\Debug();
		}
	}

	/**
	 * show a notice for user to say they just upgrade from free
	 */
	public function maybeShowUpgradedNotice() {
		if ( get_site_option( 'defenderJustUpgrade' ) == 1 ) {
			$utils = \WP_Defender\Behavior\Utils::instance();
			if ( $utils->checkPermission() ) {
				if ( \WP_Defender\Behavior\Utils::instance()->isActivatedSingle() ) {
					add_action( 'admin_notices', array( &$this, 'showUpgradedNotification' ) );
				} else {
					add_action( 'network_admin_notices', array( &$this, 'showUpgradedNotification' ) );
				}
			}
		}
	}

	public function showUpgradedNotification() {
		$class   = 'notice notice-info is-dismissible';
		$message = __( "We noticed you have both the free and pro versions of Defender installed, so we've automatically deactivated the free version for you.",
			wp_defender()->domain );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
		delete_site_option( 'defenderJustUpgrade' );
	}

	/**
	 * Add a setting link in plugins page
	 * @return array
	 */
	public function addSettingsLink( $links ) {
		$mylinks = array(
			'<a href="' . admin_url( 'admin.php?page=wp-defender' ) . '">' . __( 'Settings',
				wp_defender()->domain ) . '</a>',
		);

		$mylinks = array_merge( $mylinks, $links );
		$mylinks = array_merge(
			$mylinks,
			array(
				'<a target="_blank" href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/">' . __( 'Docs',
					wp_defender()->domain ) . '</a>',
			)
		);

		return $mylinks;
	}

	/**
	 * @deprecated
	 */
	private function maybeUpgrade() {
		//update can settings
		$option = get_site_option( 'wp_defender' );
		if ( $option ) {
			$setting                  = \WP_Defender\Module\Scan\Model\Settings::instance();
			$setting->scan_core       = isset( $option['use_core_integrity_scan'] ) ? $option['use_core_integrity_scan'] : $setting->scan_core;
			$setting->scan_vuln       = isset( $option['use_vulndb_scan'] ) ? $option['use_vulndb_scan'] : $setting->scan_vuln;
			$setting->scan_content    = isset( $option['use_suspicious_file_scan'] ) ? $option['use_suspicious_file_scan'] : $setting->scan_content;
			$setting->email_all_ok    = isset( $option['completed_scan_email_content_success'] ) ? $option['completed_scan_email_content_success'] : $setting->email_all_ok;
			$setting->email_has_issue = isset( $option['completed_scan_email_content_error'] ) ? $option['completed_scan_email_content_error'] : $setting->email_has_issue;
			$setting->recipients      = isset( $option['recipients'] ) ? $option['recipients'] : $setting->recipients;
			$setting->always_send     = isset( $option['always_notify'] ) ? $option['always_notify'] : $setting->always_send;
			if ( isset( $option['auto_scan'] ) && $option['auto_scan'] == 1 ) {
				$setting->notification = 1;
				$setting->frequency    = $option['schedule']['frequency'];
				$setting->day          = $option['schedule']['day'];
				$setting->time         = $option['schedule']['time'];
			} else {
				$setting->notification = 0;
			}
			$setting->save();
			wp_schedule_single_event( strtotime( '+1 minute' ), 'processScanCron' );
		}

		//update audit log setting
		if ( isset( $option['audit_log'] ) ) {
			$setting            = \WP_Defender\Module\Audit\Model\Settings::instance();
			$setting->enabled   = $option['audit_log']['enabled'];
			$setting->frequency = $option['audit_log']['report_email_frequent'];
			$setting->save();
		}
		//hardener disable pingback
		if ( isset( $option['disable_ping_back'] ) && $option['disable_ping_back']['remove_pingback'] == 1 ) {
			$cache = \Hammer\Helper\WP_Helper::getCache();
			$cache->set( \WP_Defender\Module\Hardener\Component\Disable_Trackback_Service::CACHE_KEY, 1, 0 );
		}
		//hardener security check
		if ( isset( $option['wd_security_key'] ) ) {
			\Hammer\Helper\WP_Helper::getCache()->set(
				\WP_Defender\Module\Hardener\Component\Security_Key_Service::CACHE_KEY,
				$option['wd_security_key']['processed_time']
			);
			\Hammer\Helper\WP_Helper::getCache()->set( 'securityReminderDate',
				strtotime( '+' . $option['remind_interval'], $option['wd_security_key']['processed_time'] ) );
		}
		//merge any ignored of ahrdener
		if ( isset( $option['hardener']['ignores'] ) ) {
			$ignored = $option['hardener']['ignores'];
			if ( is_array( $ignored ) && count( $ignored ) ) {
				$setting = \WP_Defender\Module\Hardener\Model\Settings::instance();
				$mapped  = array(
					'change_default_admin'  => \WP_Defender\Module\Hardener\Component\Change_Admin::$slug,
					'db_prefix'             => \WP_Defender\Module\Hardener\Component\DB_Prefix::$slug,
					'disable_error_display' => \WP_Defender\Module\Hardener\Component\Disable_Trackback::$slug,
					'disable_ping_back'     => \WP_Defender\Module\Hardener\Component\Disable_Trackback::$slug,
					'php_version'           => \WP_Defender\Module\Hardener\Component\PHP_Version::$slug,
					'plugin_theme_editor'   => \WP_Defender\Module\Hardener\Component\Disable_File_Editor::$slug,
					'protect_upload_dir'    => \WP_Defender\Module\Hardener\Component\Prevent_Php::$slug,
					'protect_core_dir'      => \WP_Defender\Module\Hardener\Component\Protect_Information::$slug,
					'wd_security_key'       => \WP_Defender\Module\Hardener\Component\Security_Key::$slug,
					'wp_verify_version'     => \WP_Defender\Module\Hardener\Component\WP_Version::$slug,
				);

				foreach ( $ignored as $oldSlug ) {
					if ( isset( $mapped[ $oldSlug ] ) ) {
						$slug = $mapped[ $oldSlug ];
						$setting->addToIgnore( $slug, false );
					}
				}

			}
		}

		$lockout = get_site_option( 'wd_lockdown_settings' );
		if ( $lockout ) {
			$setting = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
			if ( $lockout['report_frequency'] == 'daily' ) {
				$setting->report_frequency = 1;
			} elseif ( $lockout['report_frequency'] == 'weekly' ) {
				$setting->report_frequency = 7;
			} elseif ( $lockout['report_frequency'] == 'monthly' ) {
				$setting->report_frequency = 30;
			}
			$setting->save();
		}
		update_site_option( 'wd_db_version', $this->wp_defender->db_version );
	}

	/**
	 * @deprecated
	 */
	private function maybeUpgrade15() {
		$settings = \WP_Defender\Module\Scan\Model\Settings::instance();
		if ( $settings->notification ) {
			$cronTime = \WP_Defender\Behavior\Utils::instance()->reportCronTimestamp( $settings->time,
				'scanReportCron' );
			wp_schedule_event( $cronTime, 'daily', 'scanReportCron' );
		}

		$auditSettings = \WP_Defender\Module\Audit\Model\Settings::instance();
		if ( $auditSettings->notification ) {
			wp_clear_scheduled_hook( 'auditReportCron' );
			$cronTime = \WP_Defender\Behavior\Utils::instance()->reportCronTimestamp( $auditSettings->time,
				'auditReportCron' );
			wp_schedule_event( $cronTime, 'daily', 'auditReportCron' );
		}

		$lockoutSettings = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
		if ( $lockoutSettings->report ) {
			wp_clear_scheduled_hook( 'lockoutReportCron' );
			$cronTime = \WP_Defender\Behavior\Utils::instance()->reportCronTimestamp( $lockoutSettings->report_time,
				'lockoutReportCron' );
			wp_schedule_event( $cronTime, 'daily', 'lockoutReportCron' );
		}
		update_site_option( 'wd_db_version', wp_defender()->db_version );
	}

	public function activationHook() {
		$db_ver = get_site_option( 'wd_db_version' );
		\WP_Defender\Module\Setting\Component\Backup_Settings::backupData();
		if ( ! \WP_Defender\Module\IP_Lockout\Component\Login_Protection_Api::checkIfTableExists() ) {
			\WP_Defender\Module\IP_Lockout\Component\Login_Protection_Api::createTables();
		} else {
			\WP_Defender\Module\IP_Lockout\Component\Login_Protection_Api::alterTableFor171();
		}
		if ( $db_ver != false && version_compare( $db_ver, '1.7.1', '<' ) ) {
			update_site_option( 'wd_db_version', "1.7.1" );
		}

		if ( $db_ver != false && version_compare( $db_ver, '2.1.1', '<' ) ) {
			//convert scan notification
			$settings                          = \WP_Defender\Module\Scan\Model\Settings::instance();
			$settings->recipients              = $this->convertOldToNewRecipients( $settings->recipients );
			$settings->recipients_notification = $this->convertOldToNewRecipients( $settings->recipients_notification );
			$settings->save();
			//audit
			$settings           = \WP_Defender\Module\Audit\Model\Settings::instance();
			$settings->receipts = $this->convertOldToNewRecipients( $settings->receipts );
			$settings->save();
			//lockout
			$settings                  = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
			$settings->receipts        = $this->convertOldToNewRecipients( $settings->receipts );
			$settings->report_receipts = $this->convertOldToNewRecipients( $settings->report_receipts );
			$settings->save();
		}
		$this->upgradeHook();
		//init report cron
		$settings = \WP_Defender\Module\Scan\Model\Settings::instance();
		if ( $settings->notification ) {
			$cronTime = \WP_Defender\Behavior\Utils::instance()->reportCronTimestamp( $settings->time,
				'scanReportCron' );
			wp_schedule_event( $cronTime, 'daily', 'scanReportCron' );
		}
		$settings = \WP_Defender\Module\Audit\Model\Settings::instance();
		if ( $settings->notification ) {
			$cronTime = \WP_Defender\Behavior\Utils::instance()->reportCronTimestamp( $settings->time,
				'auditReportCron' );
			wp_schedule_event( $cronTime, 'daily', 'auditReportCron' );
		}
		$settings = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
		if ( $settings->report ) {
			$cronTime = \WP_Defender\Behavior\Utils::instance()->reportCronTimestamp( $settings->report_time,
				'lockoutReportCron' );
			wp_schedule_event( $cronTime, 'daily', 'lockoutReportCron' );
		}
	}

	private function convertOldToNewRecipients( $data ) {
		$tmp = array();
		foreach ( $data as $id ) {
			if ( filter_var( $id, FILTER_VALIDATE_INT ) ) {
				$user = get_user_by( 'id', $id );
				if ( is_object( $user ) ) {
					$temp[] = array(
						'first_name' => $user->display_name,
						'email'      => $user->user_email
					);
				}
			}
		}

		return $tmp;
	}
}