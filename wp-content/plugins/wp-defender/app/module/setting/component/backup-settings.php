<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Setting\Component;

use WP_Defender\Behavior\Utils;
use WP_Defender\Behavior\WPMUDEV;
use WP_Defender\Module\Advanced_Tools\Model\Mask_Settings;
use WP_Defender\Module\Advanced_Tools\Model\Security_Headers_Settings;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Two_Factor\Model\Auth_Settings;

class Backup_Settings {
	const KEY = 'defender_last_settings', INDEXER = 'defender_config_indexer';

	/**
	 * Gather settings from all modules
	 * @return array
	 */
	public static function gatherData() {
		$security_tweaks = Settings::instance()->exportByKeys( [
			'notification_repeat',
			'receipts',
			'notification',
			'data',
			'fixed',
			'issues',
			'ignore',
			'automate'
		] );
		if ( is_array( $security_tweaks['data'] ) && ! empty( $security_tweaks['data'] ) ) {
			//$security_tweaks['data'] = array_merge( $security_tweaks['data'], Settings::instance()->export_extra() );
			unset( $security_tweaks['data']['head_requests'] );
		}
		$scan_model = \WP_Defender\Module\Scan\Model\Settings::instance();
		$scan       = $scan_model->exportByKeys( [
			'scan_core',
			'scan_vuln',
			'scan_content',
			'max_filesize',
			'report',
			'always_send',
			'recipients',
			'day',
			'time',
			'frequency',
			'notification',
			'always_send_notification',
			'recipients_notification',
			'email_subject',
			'email_subject_issue',
			'email_all_ok',
			'email_has_issue'
		] );
		if ( class_exists( '\WP_Defender\Module\Audit\Model\Settings' ) ) {
			$audit_model = \WP_Defender\Module\Audit\Model\Settings::instance();
			$audit       = $audit_model->exportByKeys( [
				'enabled',
				'notification',
				'receipts',
				'frequency',
				'day',
				'time',
				'storage_days'
			] );
			if ( wp_defender()->isFree ) {
				$audit['enabled'] = false;
			}
		} else {
			$audit = [
				'enabled' => false
			];
		}
		$iplockout_model = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
		$iplockout       = $iplockout_model->exportByKeys(
			[
				'login_protection',
				'login_protection_login_attempt',
				'login_protection_lockout_timeframe',
				'login_protection_lockout_ban',
				'login_protection_lockout_duration',
				'login_protection_lockout_duration_unit',
				'login_protection_lockout_message',
				'username_blacklist',
				'detect_404',
				'detect_404_threshold',
				'detect_404_timeframe',
				'detect_404_lockout_ban',
				'detect_404_lockout_duration',
				'detect_404_lockout_duration_unit',
				'detect_404_lockout_message',
				'detect_404_blacklist',
				'detect_404_whitelist',
				'detect_404_filetypes_blacklist',
				'detect_404_ignored_filetypes',
				'detect_404_logged',
				'ip_blacklist',
				'ip_whitelist',
				'country_blacklist',
				'country_whitelist',
				'ip_lockout_message',
				'login_lockout_notification',
				'ip_lockout_notification',
				'receipts',
				'cooldown_enabled',
				'cooldown_number_lockout',
				'cooldown_period',
				'report',
				'report_receipts',
				'report_frequency',
				'report_day',
				'report_time',
				'storage_days',
				'geoIP_db'
			] );
		$advanced_tools  = [
			'two_factor' => \WP_Defender\Module\Two_Factor\Model\Auth_Settings::instance()->export( [ 'is_conflict' ] ),
			'mask_login' => Mask_Settings::instance()->export( [ 'otps' ] )
		];
		$settings        = \WP_Defender\Module\Setting\Model\Settings::instance()->export();
		$ret             = [
			'security_tweaks' => $security_tweaks,
			'scan'            => $scan,
			'iplockout'       => $iplockout,
		];
		if ( isset( $audit ) ) {
			$ret['audit'] = $audit;
		}
		$security_headers = Security_Headers_Settings::instance()->exportByKeys( [
			'sh_xframe',
			'sh_xframe_mode',
			'sh_xframe_urls',
			'sh_xss_protection',
			'sh_xss_protection_mode',
			'sh_content_type_options',
			'sh_content_type_options_mode',
			'sh_strict_transport',
			'hsts_preload',
			'include_subdomain',
			'hsts_cache_duration',
			'sh_referrer_policy',
			'sh_referrer_policy_mode',
			'sh_feature_policy',
			'sh_feature_policy_mode',
			'sh_feature_policy_urls'
		] );
		$ret              = array_merge( $ret, [
			'two_factor'       => $advanced_tools['two_factor'],
			'mask_login'       => $advanced_tools['mask_login'],
			'settings'         => $settings,
			'security_headers' => $security_headers
		] );

		return $ret;
	}

	/**
	 * @return array|object|null
	 */
	public static function getConfigs() {
		$keys    = get_site_option( self::INDEXER, false );
		$results = [];
		foreach ( $keys as $key ) {
			$config = get_site_option( $key );

			if ( $config === false ) {
				self::removeIndex( $key );
			} else {
				$results[ $key ] = $config;
			}
		}

		return $results;
	}

	/**
	 * @param $key
	 */
	public static function makeConfigActive( $key ) {
		$configs = self::getConfigs();
		foreach ( $configs as $k => $config ) {
			if ( $k === $key ) {
				$config['is_active'] = true;
			} else {
				$config['is_active'] = false;
			}
			update_site_option( $k, $config );
		}
	}

	public static function clearConfigs() {
		$keys = get_site_option( self::INDEXER, false );
		foreach ( $keys as $key ) {
			delete_site_option( $key );
		}
		delete_site_option( self::INDEXER );
	}

	/**
	 * Create a default config
	 */
	public static function maybeCreateDefaultConfig() {
		$keys = get_site_option( self::INDEXER, false );
		if ( $keys === false ) {
			$key = 'wp_defender_config_default' . time();
			if ( ! get_site_option( $key ) ) {
				self::createProConfig();
			}
		}
	}

	private static function createProConfig() {
		$user               = wp_get_current_user();
		$default_recipients = [
			[
				'first_name' => $user->display_name,
				'email'      => $user->user_email
			]
		];

		$data = [
			'security_tweaks'  => [
				'notification_repeat' => false,
				'receipts'            => $default_recipients,
				'notification'        => true,
				'automate'            => true,
			],
			'scan'             => [
				'scan_core'                => true,
				'scan_vuln'                => true,
				'scan_content'             => true,
				'max_filesize'             => 3,
				'report'                   => true,
				'always_send'              => false,
				'recipients'               => $default_recipients,
				'day'                      => 'sunday',
				'time'                     => '4:00',
				'frequency'                => '7',
				'notification'             => true,
				'always_send_notification' => false,
				'recipients_notification'  => $default_recipients,
			],
			'audit'            => [
				'enabled'      => true,
				'notification' => true,
				'receipts'     => $default_recipients,
				'frequency'    => '7',
				'day'          => 'sunday',
				'time'         => '4:00',
				'storage_days' => '6 months'
			],
			'iplockout'        => [
				'login_protection'                       => true,
				'login_protection_login_attempt'         => '5',
				'login_protection_lockout_timeframe'     => '300',
				'login_protection_lockout_ban'           => false,
				'login_protection_lockout_duration'      => '4',
				'login_protection_lockout_duration_unit' => 'hours',
				'login_protection_lockout_message'       => __( 'You have been locked out due to too many invalid login attempts.',
					wp_defender()->domain ),
				'username_blacklist'                     => 'admin',
				'detect_404'                             => true,
				'detect_404_threshold'                   => '20',
				'detect_404_timeframe'                   => '300',
				'detect_404_lockout_ban'                 => false,
				'detect_404_lockout_duration'            => '4',
				'detect_404_lockout_duration_unit'       => 'hours',
				'detect_404_lockout_message'             => __( 'You have been locked out due to too many attempts to access a file that doesn\'t exist.',
					wp_defender()->domain ),
				'detect_404_blacklist'                   => '',
				'detect_404_whitelist'                   => '',
				'detect_404_filetypes_blacklist'         => '',
				'detect_404_ignored_filetypes'           => ".css\n.js\n.jpg\n.png\n.gif",
				'detect_404_logged'                      => true,
				'ip_blacklist'                           => '',
				'ip_whitelist'                           => Utils::instance()->getUserIp(),
				'country_blacklist'                      => '',
				'country_whitelist'                      => '',
				'ip_lockout_message'                     => __( 'The administrator has blocked your IP from accessing this website.',
					wp_defender()->domain ),
				'login_lockout_notification'             => false,
				'ip_lockout_notification'                => false,
				'receipts'                               => $default_recipients,
				'cooldown_enabled'                       => false,
				'cooldown_number_lockout'                => '3',
				'cooldown_period'                        => '24',
				'report'                                 => true,
				'report_receipts'                        => $default_recipients,
				'report_frequency'                       => 7,
				'report_day'                             => 'sunday',
				'report_time'                            => '4:00',
				'storage_days'                           => '180',
			],
			'two_factor'       => [
				'enabled'          => true,
				'lost_phone'       => true,
				'force_auth'       => false,
				'force_auth_mess'  => '',
				'user_roles'       => array_keys( get_editable_roles() ),
				'force_auth_roles' => [],
				'custom_graphic'   => false,
			],
			'mask_login'       => [
				'mask_url '            => '',
				'redirect_traffic'     => false,
				'redirect_traffic_url' => '',
				'enabled'              => false
			],
			'security_headers' => [
				'sh_xframe'                    => true,
				'sh_xframe_mode'               => 'sameorigin',
				'sh_xframe_urls'               => '',
				'sh_xss_protection'            => true,
				'sh_xss_protection_mode'       => 'sanitize',
				'sh_content_type_options'      => true,
				'sh_content_type_options_mode' => 'nosniff',
				'sh_strict_transport'          => true,
				'hsts_preload'                 => false,
				'include_subdomain'            => false,
				'hsts_cache_duration'          => '30 days',
				'sh_referrer_policy'           => true,
				'sh_referrer_policy_mode'      => 'origin-when-cross-origin',
				'sh_feature_policy'            => true,
				'sh_feature_policy_mode'       => 'self',
				'sh_feature_policy_urls'       => ''
			],
			'settings'         => [
				'uninstall_data'     => 'keep',
				'uninstall_settings' => 'preserve'
			]
		];

		$configs                = self::parseDataForImport( $data );
		$configs['name']        = __( 'Basic config', wp_defender()->domain );
		$configs['description'] = __( 'Recommended default protection for every site', wp_defender()->domain );
		$configs['immortal']    = true;
		$key                    = 'wp_defender_config_' . sanitize_file_name( $configs['name'] ) . time();
		update_site_option( $key, $configs );
		self::indexKey( $key );
	}

	/**
	 * @param $key
	 */
	public static function indexKey( $key ) {
		$keys   = get_site_option( self::INDEXER, false );
		$keys[] = $key;
		update_site_option( self::INDEXER, $keys );
	}

	/**
	 * @param $key
	 */
	public static function removeIndex( $key ) {
		$keys = get_site_option( self::INDEXER, false );
		unset( $keys[ array_search( $key, $keys ) ] );
		update_site_option( self::INDEXER, $keys );
	}

	/**
	 * Backup the previous data before we process new versioon
	 */
	public static function backupData() {
		$data       = self::gatherData();
		$old_backup = get_site_option( self::KEY );
		if ( ! is_array( $old_backup ) ) {
			$old_backup = [];
		}
		if ( count( $old_backup ) > 50 ) {
			//remove the oldest key
			array_shift( $old_backup );
		}
		$version                               = get_site_option( 'wd_db_version' );
		$old_backup[ $version . '_' . time() ] = $data;
		update_site_option( self::KEY, $old_backup );
	}

	/**
	 * @param $data
	 */
	public static function restoreData( $data ) {
		$need_reauth = false;
		foreach ( $data as $module => $module_data ) {
			$model = self::moduleToModel( $module );
			if ( is_object( $model ) ) {
				foreach ( $module_data as &$value ) {
					if ( ! is_array( $value ) && ! filter_var( $value, FILTER_VALIDATE_BOOLEAN ) ) {
						$value = str_replace( '{nl}', PHP_EOL, $value );
					}
				}
				$model->import( $module_data );
				$model->save();
				if ( 'security_tweaks' === $module ) {
					//there is some tweaks that require a re-login, if so, then we should output a message
					//if combine with mask login, then we need to redirect to new URL
					//the automate fucntion should return that
					$need_reauth = $model->automate();
				}
			}
		}
		//we should disable quick setup
		update_site_option( 'wp_defender_is_activated', 1 );

		return $need_reauth;
	}

	public static function parseDataForImport( $configs = null ) {
		if ( empty( $configs ) ) {
			$configs = self::gatherData();
		}
		$strings = [];
		foreach ( $configs as $module => $module_data ) {
			$model = self::moduleToModel( $module );
			if ( ! is_object( $model ) ) {
				//in free, when audit not present
				$strings[ $module ][] = sprintf( __( 'Inactive %s', wp_defender()->domain ),
					'<span class="sui-tag sui-tag-pro">Pro</span>' );
				continue;
			}
			$strings[ $module ] = $model->export_strings( $module_data );
		}

		return [
			'configs' => $configs,
			'strings' => $strings
		];
	}

	/**
	 * @return array
	 */
	public static function parseDataForHub( $configs = null ) {
		if ( is_null( $configs ) ) {
			$configs = self::gatherData();
		}
		$labels  = [];
		$strings = [];
		foreach ( $configs as $module => $module_data ) {
			$model = self::moduleToModel( $module );
			if ( ! is_object( $model ) ) {
				continue;
			}
			$labels[ $module ]['name'] = self::moduleToName( $module );
			$model->import( $module_data );
			foreach ( $model->format_hub_data() as $key => $value ) {
				if ( $key == 'geoIP_db' ) {
					continue;
				}
				$labels[ $module ]['value'][ $key ] = [
					'name'  => $model->labels( $key ),
					'value' => $value
				];
			}
			$strings[ $module ] = $model->export_strings( $configs );
		}

		return [
			'configs' => $configs,
			'labels'  => $labels,
			'strings' => $strings
		];
	}

	/**
	 * @param $value
	 *
	 * @return mixed|string|void
	 */
	private static function parseDataValue( $value, $key = null ) {
		if ( is_bool( $value ) ) {
			return $value == true ? __( "Yes", wp_defender()->domain ) : __( "No", wp_defender()->domain );
		}
		/**
		 * parse recipients
		 */
		if ( is_array( $value ) ) {
			$ret = [];
			foreach ( $value as $item ) {
				if ( is_array( $item ) ) {
					//this is recipients
					$ret[] = implode( ': ', $item );
				} else {
					//this should be the case of roles picker
					$ret[] = $item;
				}
			}

			return implode( '; ', $ret );
		}
		//parse frequency
		if ( $key == 'frequency' ) {
			$value = Utils::instance()->frequencyToText( $value );
		}

		return $value;
	}

	/**
	 * @param $module
	 *
	 * @return Auth_Settings|Mask_Settings|\WP_Defender\Module\Audit\Model\Settings|Settings|\WP_Defender\Module\IP_Lockout\Model\Settings|\WP_Defender\Module\Scan\Model\Settings|\WP_Defender\Module\Setting\Model\Settings
	 */
	public static function moduleToModel( $module ) {
		switch ( $module ) {
			case 'security_tweaks':
				return Settings::instance();
			case 'scan':
				return \WP_Defender\Module\Scan\Model\Settings::instance();
			case 'audit':
				if ( class_exists( '\WP_Defender\Module\Audit\Model\Settings' ) ) {
					return \WP_Defender\Module\Audit\Model\Settings::instance();
				}
				break;
			case 'iplockout':
				return \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
			case 'settings':
				return \WP_Defender\Module\Setting\Model\Settings::instance();
			case 'two_factor':
				return Auth_Settings::instance();
			case 'mask_login':
				return Mask_Settings::instance();
			case 'security_headers':
				return \WP_Defender\Module\Advanced_Tools\Model\Security_Headers_Settings::instance();
			default:
				break;
		}
	}

	private static function moduleToName( $module ) {
		switch ( $module ) {
			case 'security_tweaks':
				return __( 'Security Recommendations', wp_defender()->domain );
			case 'scan':
				return __( 'Malware Scanning', wp_defender()->domain );
			case 'audit':
				return __( 'Audit Logging', wp_defender()->domain );
			case 'iplockout':
				return __( 'Firewall', wp_defender()->domain );
			case 'settings':
				return __( 'Settings', wp_defender()->domain );
			case 'two_factor':
				return __( '2FA', wp_defender()->domain );
			case 'mask_login':
				return __( 'Mask Login Area', wp_defender()->domain );
			case 'security_headers':
				return __( 'Security Headers', wp_defender()->domain );
			default:
				break;
		}
	}

	public static function resetSettings() {
		$hardener_settings = \WP_Defender\Module\Hardener\Model\Settings::instance();

		foreach ( $hardener_settings->getFixed() as $rule ) {
			$rule->getService()->revert();
		}

		$cache = \Hammer\Helper\WP_Helper::getCache();
		$cache->delete( 'isActivated' );
		$cache->delete( 'wdf_isActivated' );
		$cache->delete( 'wdfchecksum' );
		$cache->delete( 'cleanchecksum' );

		\WP_Defender\Module\Scan\Model\Settings::instance()->delete();
		if ( class_exists( '\WP_Defender\Module\Audit\Model\Settings' ) ) {
			\WP_Defender\Module\Audit\Model\Settings::instance()->delete();
		}
		$hardener_settings->delete();
		\WP_Defender\Module\IP_Lockout\Model\Settings::instance()->delete();
		\WP_Defender\Module\Two_Factor\Model\Auth_Settings::instance()->delete();
		\WP_Defender\Module\Advanced_Tools\Model\Mask_Settings::instance()->delete();
		\WP_Defender\Module\Advanced_Tools\Model\Security_Headers_Settings::instance()->delete();
		\WP_Defender\Module\Setting\Model\Settings::instance()->delete();
		//clear old stuff
		delete_site_option( 'wp_defender' );
		delete_option( 'wp_defender' );
		delete_option( 'wd_db_version' );
		delete_site_option( 'wd_db_version' );

		delete_site_transient( 'wp_defender_free_is_activated' );
		delete_site_transient( 'wp_defender_is_activated' );
		delete_transient( 'wp_defender_free_is_activated' );
		delete_transient( 'wp_defender_is_activated' );

		delete_site_option( 'wp_defender_free_is_activated' );
		delete_site_option( 'wp_defender_is_activated' );
		delete_option( 'wp_defender_free_is_activated' );
		delete_option( 'wp_defender_is_activated' );
	}
}