<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\IP_Lockout\Model;

use Hammer\GeoIP\GeoIp;
use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\IP_Lockout\Component\IP_API;

class Settings extends \Hammer\WP\Settings {
	private static $_instance;

	public $login_protection = false;
	public $login_protection_login_attempt = 5;
	public $login_protection_lockout_timeframe = 300;
	public $login_protection_lockout_duration = 300;
	public $login_protection_lockout_duration_unit = 'seconds';
	public $login_protection_lockout_message = "You have been locked out due to too many invalid login attempts.";
	public $login_protection_ban_admin_brute = false;
	public $login_protection_lockout_ban = false;
	public $username_blacklist = '';

	public $detect_404 = false;
	public $detect_404_threshold = 20;
	public $detect_404_timeframe = 300;
	public $detect_404_lockout_duration = 300;
	public $detect_404_lockout_duration_unit = 'seconds';
	public $detect_404_whitelist;
	public $detect_404_blacklist;
	public $detect_404_ignored_filetypes;
	public $detect_404_filetypes_blacklist;
	public $detect_404_lockout_message = "You have been locked out due to too many attempts to access a file that doesn't exist.";
	public $detect_404_lockout_ban = false;
	public $detect_404_logged = true;

	public $ip_blacklist = array();
	public $ip_whitelist = array();
	public $ip_lockout_message = 'The administrator has blocked your IP from accessing this website.';

	public $country_blacklist = [];
	public $country_whitelist = [];

	public $login_lockout_notification = true;
	public $ip_lockout_notification = true;

	public $report = true;
	public $report_frequency = '7';
	public $report_day = 'sunday';
	public $report_time = '0:00';

	public $geoIP_db = null;

	public $storage_days = 30;

	public $receipts = array();
	public $report_receipts = array();
	public $lastReportSent;

	public $cooldown_enabled = false;
	public $cooldown_number_lockout = '3';
	public $cooldown_period = '24';

	public $cache = array();

	public function __construct( $id, $isMulti ) {
		if ( ( is_admin() || is_network_admin() ) && current_user_can( 'manage_options' ) ) {
			$user = wp_get_current_user();
			if ( is_object( $user ) ) {
				$this->receipts[]        = array(
					'first_name' => $user->display_name,
					'email'      => $user->user_email
				);
				$this->report_receipts[] = array(
					'first_name' => $user->display_name,
					'email'      => $user->user_email
				);
			}

			$this->ip_whitelist = $this->getUserIp() . PHP_EOL;
			//default is weekly
			$this->report_day  = strtolower( date( 'l' ) );
			$this->report_time = '4:00';
		}
		parent::__construct( $id, $isMulti );
		/**
		 * Make sure those is boolen
		 */
		$this->login_protection             = ! ! $this->login_protection;
		$this->detect_404                   = ! ! $this->detect_404;
		$this->login_protection_lockout_ban = ! ! $this->login_protection_lockout_ban;
		$this->detect_404_lockout_ban       = ! ! $this->detect_404_lockout_ban;
		$this->report                       = ! ! $this->report;
		$this->detect_404_logged            = ! ! $this->detect_404_logged;

		$times = Utils::instance()->getTimes();
		if ( ! isset( $times[ $this->report_time ] ) ) {
			$this->report_time = '4:00';
		}
		if ( ! is_array( $this->receipts ) ) {
			$this->receipts = [];
		}
		$this->receipts = array_values( $this->receipts );
		if ( ! is_array( $this->report_receipts ) ) {
			$this->report_receipts = [];
		}
		$this->report_receipts = array_values( $this->report_receipts );
		if ( is_string( $this->country_whitelist ) ) {
			$this->country_whitelist = explode( ',', $this->country_whitelist );
			$this->country_whitelist = ( array_values( array_filter( $this->country_whitelist ) ) );
		}
		if ( is_string( $this->country_blacklist ) ) {
			$this->country_blacklist = explode( ',', $this->country_blacklist );
			$this->country_blacklist = ( array_values( array_filter( $this->country_blacklist ) ) );
		}
	}

	/**
	 * @return array
	 */
	public function behaviors() {
		return array(
			'utils' => '\WP_Defender\Behavior\Utils'
		);
	}

	/**
	 * @return array
	 */
	public function rules() {
		return array(
			[
				[
					'login_protection_login_attempt',
					'login_protection_lockout_timeframe',
					'login_protection_lockout_duration',
					'detect_404_threshold',
					'detect_404_timeframe',
					'detect_404_lockout_duration',
					'storage_days',
				],
				'integer',
			],
		);
	}

	/**
	 * @return array
	 */
	public function filters() {
		return [
			'username_blacklist',
			'login_protection_lockout_message',
			'detect_404_ignored_filetypes',
			'detect_404_filetypes_blacklist',
			'detect_404_lockout_message',
			'detect_404_whitelist',
			'detect_404_blacklist',
			'ip_lockout_message',
			'ip_blacklist',
			'ip_whitelist',
			'ip_lockout_message',
		];
	}

	/**
	 * @return array
	 */
	public function get404Whitelist() {
		$arr = array_filter( explode( PHP_EOL, $this->detect_404_whitelist ) );
		$arr = array_map( 'trim', $arr );

		return $arr;
	}

	/**
	 * @return array
	 */
	public function get404Ignorelist() {
		$arr = array_filter( explode( PHP_EOL, $this->detect_404_ignored_filetypes ) );
		$arr = array_map( 'trim', $arr );
		$arr = array_map( 'strtolower', $arr );

		return $arr;
	}

	/**
	 * @return mixed
	 */
	public function getDetect404Whitelist() {
		$arr = array_filter( explode( PHP_EOL, $this->detect_404_whitelist ) );
		$arr = array_map( 'trim', $arr );

		return $arr;
	}

	/**
	 * @return mixed
	 */
	public function getDetect404Blacklist() {
		$arr = array_filter( explode( PHP_EOL, $this->detect_404_blacklist ) );
		$arr = array_map( 'trim', $arr );

		return $arr;
	}

	/**
	 * @return array
	 */
	public function getIpBlacklist() {
		if ( is_array( $this->ip_blacklist ) ) {
			$arr = $this->ip_blacklist;
		} else {
			$arr = array_filter( explode( PHP_EOL, $this->ip_blacklist ) );
		}
		$arr = array_map( 'trim', $arr );

		return $arr;
	}

	/**
	 * @return array
	 */
	public function getDetect404IgnoredFiletypes() {
		$exts = explode( PHP_EOL, $this->detect_404_ignored_filetypes );
		$exts = array_map( 'trim', $exts );
		$exts = array_map( 'strtolower', $exts );

		return $exts;
	}

	/**
	 * @return mixed
	 */
	public function getDetect404FiletypesBlacklist() {
		$exts = explode( PHP_EOL, $this->detect_404_filetypes_blacklist );
		$exts = array_map( 'trim', $exts );
		$exts = array_map( 'strtolower', $exts );

		return $exts;
	}

	/**
	 * @return array
	 */
	public function getIpWhitelist() {
		//backward compatibility
		if ( is_array( $this->ip_whitelist ) ) {
			$arr = $this->ip_whitelist;
		} else {
			$arr = array_filter( explode( PHP_EOL, $this->ip_whitelist ) );
		}
		$arr = array_map( 'trim', $arr );

		return $arr;
	}

	/**
	 * @return array
	 */
	public function getCountryBlacklist() {
		if ( is_array( $this->country_blacklist ) ) {
			return $this->country_blacklist;
		}
		//fallback to older version than 2.2
		$arr = array_filter( explode( ',', $this->country_blacklist ) );
		$arr = array_map( 'trim', $arr );

		return $arr;
	}

	/**
	 * @return array
	 */
	public function getCountryWhitelist() {
		if ( is_array( $this->country_whitelist ) ) {
			return $this->country_whitelist;
		}
		//fallback to older version than 2.2
		$arr = array_filter( explode( ',', $this->country_whitelist ) );
		$arr = array_map( 'trim', $arr );

		return $arr;
	}

	/**
	 * @param $ip
	 *
	 * @return bool
	 */
	public function isWhitelist( $ip ) {
		$whitelist        = $this->getIpWhitelist();
		$defaultWhitelist = apply_filters( 'ip_lockout_default_whitelist_ip', array() );
		$whitelist        = array_merge( $whitelist, $defaultWhitelist );
		foreach ( $whitelist as $wip ) {
			if ( ! stristr( $wip, '-' ) && ! stristr( $wip, '/' ) && trim( $wip ) == $ip ) {
				return true;
			} elseif ( stristr( $wip, '-' ) ) {
				$ips = explode( '-', $wip );
				if ( IP_API::compareInRange( $ip, $ips[0], $ips[1] ) ) {
					return true;
				}
			} elseif ( stristr( $wip, '/' ) && IP_API::compareCIDR( $ip, $wip ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $ip
	 *
	 * @return bool
	 */
	public function isBlacklist( $ip ) {
		$blacklist = $this->getIpBlacklist();
		foreach ( $blacklist as $wip ) {
			if ( ! stristr( $wip, '-' ) && ! stristr( $wip, '/' ) && trim( $wip ) == $ip ) {
				return true;
			} elseif ( stristr( $wip, '-' ) ) {
				$ips = explode( '-', $wip );
				if ( IP_API::compareInRange( $ip, $ips[0], $ips[1] ) ) {
					return true;
				}
			} elseif ( stristr( $wip, '/' ) && IP_API::compareCIDR( $ip, $wip ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add IP to list
	 *
	 * @param $ip
	 * @param string $list blocklist|allowlist
	 * @since 2.3.2
	 */
	public function addIpToList( $ip, $list ) {
		$ips  = array();
		$type = '';
		if ( 'blocklist' === $list ) {
			$ips  = $this->getIpBlacklist();
			$type = 'ip_blacklist';
		} elseif ( 'allowlist' === $list ) {
			$ips  = $this->getIpWhitelist();
			$type = 'ip_whitelist';
		}
		if ( empty( $type ) ) {
			return;
		}

		$ips[]       = $ip;
		$ips         = array_unique( $ips );
		$this->$type = implode( PHP_EOL, $ips );
		$this->save();
	}

	/**
	 * Remove IP from list
	 *
	 * @param $ip
	 * @param string $list blocklist|allowlist
	 * @since 2.3.2
	 */
	public function removeIpFromList( $ip, $list ) {
		$ips  = array();
		$type = '';
		if ( 'blocklist' === $list ) {
			$ips  = $this->getIpBlacklist();
			$type = 'ip_blacklist';
		} elseif ( 'allowlist' === $list ) {
			$ips  = $this->getIpWhitelist();
			$type = 'ip_whitelist';
		}
		if ( empty( $type ) ) {
			return;
		}

		$key = array_search( $ip, $ips );
		if ( $key !== false ) {
			unset( $ips[ $key ] );
			$ips         = array_unique( $ips );
			$this->$type = implode( PHP_EOL, $ips );
			$this->save();
		}
	}

	/**
	 * @param $ip
	 * @param $range
	 *
	 * @return bool
	 * @src http://stackoverflow.com/a/594134
	 */
	function cidrMatch( $ip, $range ) {
		list ( $subnet, $bits ) = explode( '/', $range );
		$ip     = ip2long( $ip );
		$subnet = ip2long( $subnet );
		$mask   = - 1 << ( 32 - $bits );
		$subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned

		return ( $ip & $mask ) == $subnet;
	}

	public function before_update() {
		//validate ips
		$remove_ips = array();
		$isSelf     = false;
		if ( isset( $_POST['ip_blacklist'] ) ) {
			$blacklist = Http_Helper::retrievePost( 'ip_blacklist' );
			$blacklist = explode( PHP_EOL, $blacklist );
			foreach ( $blacklist as $k => $ip ) {
				$ip = trim( $ip );
				if ( $this->validateIp( $ip ) === false ) {
					unset( $blacklist[ $k ] );
					$remove_ips[] = $ip;
				} elseif ( $ip == $this->getUserIp() ) {
					$isSelf = true;
				}
			}
			$this->ip_blacklist = implode( PHP_EOL, $blacklist );
		}

		if ( isset( $_POST['ip_whitelist'] ) ) {
			$whitelist = Http_Helper::retrievePost( 'ip_whitelist' );
			$whitelist = explode( PHP_EOL, $whitelist );
			foreach ( $whitelist as $k => $ip ) {
				$ip = trim( $ip );
				if ( $this->validateIp( $ip ) === false ) {
					unset( $whitelist[ $k ] );
					$remove_ips[] = $ip;
				}
			}
			$this->ip_whitelist = implode( PHP_EOL, $whitelist );
		}
		$remove_ips = array_filter( $remove_ips );

		if ( ! empty( $remove_ips ) && count( $remove_ips ) ) {
			WP_Helper::getArrayCache()->set( 'faultIps', $remove_ips );
			WP_Helper::getArrayCache()->set( 'isBlacklistSelf', $isSelf );
		}
	}

	/**
	 * $ip an be single ip, or a range like xxx.xxx.xxx.xxx - xxx.xxx.xxx.xxx or CIDR
	 *
	 * @param $ip
	 *
	 * @return bool
	 */
	public function validateIp( $ip ) {
		if (
			! stristr( $ip, '-' )
			&& ! stristr( $ip, '/' )
			&& filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			//only ip, no -, no /
			return true;
		} elseif ( stristr( $ip, '-' ) ) {
			$ips = explode( '-', $ip );
			foreach ( $ips as $ip ) {
				if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return false;
				}
			}
			if ( IP_API::compareIP( $ips[0], $ips[1] ) ) {
				return true;
			}
		} elseif ( stristr( $ip, '/' ) ) {
			list( $ip, $bits ) = explode( '/', $ip );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) && filter_var( $bits, FILTER_VALIDATE_INT ) ) {
				if ( $this->isIPV4( $ip ) && 0 <= $bits && $bits <= 32 ) {
					return true;
				} elseif ( $this->isIPV6( $ip ) && 0 <= $bits && $bits <= 128 && IP_API::isV6Support() ) {
					return true;
				}
			}
		}

		return false;
	}

	public function beforeValidate() {
		$emails = [];
		foreach ( $this->receipts as $receipt ) {
			if ( in_array( $receipt['email'], $emails ) ) {
				$this->addError( 'recipients', __( "Recipients' emails can't be duplicate", wp_defender()->domain ) );

				return false;
			} else {
				$emails[] = $receipt['email'];
			}
		}
	}

	/**
	 * @param $ip
	 *
	 * @return mixed
	 */
	private function isIPV4( $ip ) {
		return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
	}

	/**
	 * @param $ip
	 *
	 * @return mixed
	 */
	private function isIPV6( $ip ) {
		return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 );
	}

	/**
	 * @return array
	 */
	public function events() {
		$that = $this;

		return array(
			self::EVENT_BEFORE_SAVE => array(
				array(
					function () use ( $that ) {
						$that->before_update();

						foreach ( $this->receipts as $k => &$receipt ) {
							$receipt = array_map( 'sanitize_text_field', $receipt );
							if ( ! filter_var( $receipt['email'], FILTER_VALIDATE_EMAIL ) ) {
								unset( $this->receipts[ $k ] );
							}
						}

						foreach ( $this->report_receipts as $k => &$receipt ) {
							$receipt = array_map( 'sanitize_text_field', $receipt );
							if ( ! filter_var( $receipt['email'], FILTER_VALIDATE_EMAIL ) ) {
								unset( $this->report_receipts[ $k ] );
							}
						}

						//need to turn off notification or report off if no recipients
						$this->receipts = array_filter( $this->receipts );
						if ( count( $this->receipts ) == 0 ) {
							$this->ip_lockout_notification    = false;
							$this->login_lockout_notification = false;
						}
						$this->report_receipts = array_filter( $this->report_receipts );
						if ( count( $this->report_receipts ) == 0 ) {
							$this->report = false;
						}
					}
				)
			)
		);
	}

	/**
	 * @return array|string
	 */
	public function getUsernameBlacklist() {
		$usernames = $this->username_blacklist;
		$usernames = explode( PHP_EOL, $usernames );
		$usernames = array_map( 'trim', $usernames );
		$usernames = array_map( 'strtolower', $usernames );
		$usernames = array_filter( $usernames );

		return $usernames;
	}

	/**
	 * @return bool
	 */
	public function isGeoDBDownloaded() {
		if ( is_null( $this->geoIP_db ) || ! is_file( $this->geoIP_db ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function isCountryBlacklist() {
		//return if php less than 5.4
		if ( version_compare( phpversion(), '5.4', '<' ) ) {
			return false;
		}
		$country = IP_API::getCurrentCountry();
		if ( $country == false ) {
			return false;
		}
		//if this country is whitelisted, so we dont need to blacklist this
		if ( $this->isCountryWhitelist() ) {
			return false;
		}

		$blacklisted = $this->getCountryBlacklist();
		if ( empty( $blacklisted ) ) {
			return false;
		}
		if ( in_array( 'all', $blacklisted ) ) {
			return true;
		}
		if ( in_array( strtoupper( $country['iso'] ), $blacklisted ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isCountryWhitelist() {
		$country   = IP_API::getCurrentCountry();
		$whitelist = $this->getCountryWhitelist();
		if ( empty( $whitelist ) ) {
			return false;
		}

		if ( in_array( strtoupper( $country['iso'] ), $whitelist ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return Settings
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			$class           = new Settings( 'wd_lockdown_settings',
				WP_Helper::is_network_activate( wp_defender()->plugin_slug ) );
			self::$_instance = $class;
		}

		return self::$_instance;
	}


	/**
	 * Define labels for settings key, we will use it for HUB
	 *
	 * @param  null  $key
	 *
	 * @return array|mixed
	 */
	public function labels( $key = null ) {
		$labels = [
			'login_protection'                  => __( "Login Protection", wp_defender()->domain ),
			'login_protection_login_attempt'    => __( "Login Protection - Threshold", wp_defender()->domain ),
			'login_protection_lockout_duration' => __( "Login Protection - Duration", wp_defender()->domain ),
			'login_protection_lockout_message'  => __( "Login Protection - Lockout Message", wp_defender()->domain ),
			'username_blacklist'                => __( "Login Protection - Banned Usernames", wp_defender()->domain ),
			'detect_404'                        => __( "404 Detection", wp_defender()->domain ),
			'detect_404_threshold'              => __( "404 Detection - Threshold", wp_defender()->domain ),
			'detect_404_lockout_duration'       => __( "404 Detection - Duration", wp_defender()->domain ),
			'detect_404_lockout_message'        => __( "404 Detection - Lockout Message", wp_defender()->domain ),
			'detect_404_blacklist'              => __( "404 Detection - Files & Folders Blacklist",
				wp_defender()->domain ),
			'detect_404_whitelist'              => __( "404 Detection - Files & Folders Whitelist",
				wp_defender()->domain ),
			'detect_404_filetypes_blacklist'    => __( "404 Detection - Filetypes & Extensions Whitelist",
				wp_defender()->domain ),
			'detect_404_ignored_filetypes'      => __( "404 Detection - Filetypes & Extensions Blacklist",
				wp_defender()->domain ),
			'detect_404_logged'                 => __( "404 Detection - Monitor logged in users",
				wp_defender()->domain ),
			'ip_blacklist'                      => __( "IP Banning - IP Address Blacklist", wp_defender()->domain ),
			'ip_whitelist'                      => __( "IP Banning - IP Address Whitelist", wp_defender()->domain ),
			'country_blacklist'                 => __( "IP Banning - Country Whitelist", wp_defender()->domain ),
			'country_whitelist'                 => __( "IP Banning - Country Blacklist", wp_defender()->domain ),
			'ip_lockout_message'                => __( "IP Banning - Lockout Message", wp_defender()->domain ),
			'login_lockout_notification'        => __( "Emails - Login Protection Lockout", wp_defender()->domain ),
			'ip_lockout_notification'           => __( "Emails - 404 Detection Lockout", wp_defender()->domain ),
			'receipts'                          => __( "Emails - Lockout Recipients", wp_defender()->domain ),
			'cooldown_enabled'                  => __( "Emails - Repeat Lockouts", wp_defender()->domain ),
			'cooldown_number_lockout'           => __( "Emails - Repeat Lockouts Threshold", wp_defender()->domain ),
			'report'                            => __( "Reports - Scheduled Lockout Report", wp_defender()->domain ),
			'report_frequency'                  => __( "Reports - Frequency", wp_defender()->domain ),
			'report_receipts'                   => __( "Reports - Recipients", wp_defender()->domain ),
			'storage_days'                      => __( "Days to keep logs", wp_defender()->domain ),
		];

		if ( $key != null ) {
			return isset( $labels[ $key ] ) ? $labels[ $key ] : null;
		}

		return $labels;
	}

	/**
	 * @return array
	 */
	public function export_strings( $configs ) {

		return [
			__( 'Active', wp_defender()->domain )
		];
	}

	public function format_hub_data() {
		return [
			'login_protection'                  => $this->login_protection ? __( 'Activate',
				wp_defender()->domain ) : __( 'Inactivate',
				wp_defender()->domain ),
			'login_protection_login_attempt'    => sprintf( __( '%d logins in %d seconds',
				wp_defender()->domain ),
				$this->login_protection_login_attempt, $this->login_protection_lockout_timeframe ),
			'login_protection_lockout_duration' => $this->login_protection_lockout_ban ? __( 'Permanent',
				wp_defender()->domain ) : $this->login_protection_lockout_timeframe . ' ' . $this->login_protection_lockout_duration_unit,
			'login_protection_lockout_message'  => $this->login_protection_lockout_message,
			'username_blacklist'                => empty( $this->username_blacklist ) ? __( 'None',
				wp_defender()->domain ) : $this->username_blacklist,
			'detect_404'                        => $this->detect_404 ? __( 'Activate',
				wp_defender()->domain ) : __( 'Inactivate',
				wp_defender()->domain ),
			'detect_404_threshold'              => sprintf( __( '%d hits in %d seconds',
				wp_defender()->domain ),
				$this->detect_404_threshold, $this->detect_404_timeframe ),
			'detect_404_lockout_duration'       => $this->detect_404_lockout_ban ? __( 'Permanent',
				wp_defender()->domain ) : $this->detect_404_lockout_duration . ' ' . $this->detect_404_lockout_duration_unit,
			'detect_404_lockout_message'        => $this->detect_404_lockout_message,
			'detect_404_blacklist'              => empty( $this->detect_404_blacklist ) ? __( 'None',
				wp_defender()->domain ) : $this->detect_404_blacklist,
			'detect_404_whitelist'              => empty( $this->detect_404_whitelist ) ? __( 'None',
				wp_defender()->domain ) : $this->detect_404_whitelist,
			'detect_404_filetypes_blacklist'    => empty( $this->detect_404_filetypes_blacklist ) ? __( 'None',
				wp_defender()->domain ) : $this->detect_404_filetypes_blacklist,
			'detect_404_ignored_filetypes'      => empty( $this->detect_404_ignored_filetypes ) ? __( 'None',
				wp_defender()->domain ) : $this->detect_404_ignored_filetypes,
			'detect_404_logged'                 => $this->detect_404_logged ? __( 'Yes',
				wp_defender()->domain ) : __( 'No', wp_defender()->domain ),
			'ip_blacklist'                      => empty( $this->ip_blacklist ) ? __( 'None',
				wp_defender()->domain ) : $this->ip_blacklist,
			'country_blacklist'                 => empty( $this->country_blacklist ) ? __( 'None',
				wp_defender()->domain ) : $this->country_blacklist,
			'ip_lockout_message'                => empty( $this->ip_lockout_message ) ? __( 'None',
				wp_defender()->domain ) : $this->ip_lockout_message,
			'login_lockout_notification'        => $this->login_lockout_notification ? __( 'Activate',
				wp_defender()->domain ) : __( 'Inactivate',
				wp_defender()->domain ),
			'ip_lockout_notification'           => $this->ip_lockout_notification ? __( 'Activate',
				wp_defender()->domain ) : __( 'Inactivate',
				wp_defender()->domain ),
			'receipts'                          => empty( $this->receipts ) ? __( 'None',
				wp_defender()->domain ) : Utils::instance()->recipientsToString( $this->receipts ),
			'cooldown_enabled'                  => $this->cooldown_enabled ? __( 'Yes',
				wp_defender()->domain ) : __( 'No', wp_defender()->domain ),
			'cooldown_number_lockout'           => $this->cooldown_number_lockout ? __( 'None',
				wp_defender()->domain ) : $this->cooldown_number_lockout,
			'report'                            => $this->report ? __( 'Activate',
				wp_defender()->domain ) : __( 'Inactivate', wp_defender()->domain ),
			'report_frequency'                  => Utils::instance()->format_frequency_for_hub( $this->report_frequency,
				$this->report_day, $this->report_time ),
			'report_receipts'                   => Utils::instance()->recipientsToString( $this->report_receipts ),
			'storage_days'                      => sprintf( __( '%d days', wp_defender()->domain ),
				$this->storage_days )
		];
	}
}