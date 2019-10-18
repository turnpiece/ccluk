<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Setting\Component;

use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Advanced_Tools\Model\Auth_Settings;
use WP_Defender\Module\Advanced_Tools\Model\Mask_Settings;
use WP_Defender\Module\Hardener\Model\Settings;

class Backup_Settings {
	const KEY = 'defender_last_settings';

	/**
	 * Gather settings from all modules
	 * @return array
	 */
	public static function gatherData() {
		$security_tweaks = Settings::instance()->exportByKeys( [
			'notification_repeat',
			'receipts',
			'notification'
		] );
		$scan_model      = \WP_Defender\Module\Scan\Model\Settings::instance();
		$scan            = $scan_model->exportByKeys( [
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
			'email_all_ok',
			'email_has_issue'
		] );
		if ( class_exists( '\WP_Defender\Module\Audit\Model\Settings' ) ) {
			$audit_model = \WP_Defender\Module\Audit\Model\Settings::instance();
			$audit       = $audit_model->exportByKeys( [
				'notification',
				'receipts',
				'frequency',
				'day',
				'time',
				'storage_days'
			] );
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
			'two_factor' => Auth_Settings::instance()->export( [ 'is_conflict' ] ),
			'mask_login' => Mask_Settings::instance()->export( [ 'otps' ] )
		];
		$settings        = \WP_Defender\Module\Setting\Model\Settings::instance()->export();
		$ret             = [
			'security_tweaks' => $security_tweaks,
			'scan'            => $scan,
			'iplockout'       => $iplockout,
			'advanced_tools'  => $advanced_tools,
			'settings'        => $settings
		];
		if ( isset( $audit ) ) {
			$ret['audit'] = $audit;
		}

		return $ret;
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
			}
		}
	}

	/**
	 * @return array
	 */
	public static function parseDataForHub() {
		$configs = self::gatherData();
		//we have to move the 2 factor and mask login into parent, make sure we only have a 2 level array
		$configs['two_factor'] = $configs['advanced_tools']['two_factor'];
		$configs['mask_login'] = $configs['advanced_tools']['mask_login'];
		unset( $configs['advanced_tools'] );
		$labels = [];
		foreach ( $configs as $module => $module_data ) {
			$model                     = self::moduleToModel( $module );
			$labels[ $module ]['name'] = ucfirst( str_replace( '_', ' ', $module ) );
			foreach ( $module_data as $key => $value ) {
				if ( $key == 'geoIP_db' ) {
					continue;
				}
				$labels[ $module ]['value'][ $key ] = [
					'name'  => $model->labels( $key ),
					'value' => self::parseDataValue( $value, $key )
				];
			}
		}

		return [
			'configs' => $configs,
			'labels'  => $labels,
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
	private static function moduleToModel( $module ) {
		switch ( $module ) {
			case 'security_tweaks':
				return Settings::instance();
			case 'scan':
				return \WP_Defender\Module\Scan\Model\Settings::instance();
			case 'audit':
				return \WP_Defender\Module\Audit\Model\Settings::instance();
			case 'iplockout':
				return \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
			case 'settings':
				return \WP_Defender\Module\Setting\Model\Settings::instance();
			case 'two_factor':
				return Auth_Settings::instance();
			case 'mask_login':
				return Mask_Settings::instance();
			default:
				break;
		}
	}
}