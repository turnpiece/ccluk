<?php

/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\IP_Lockout\Model;

use Hammer\Base\DB_Model;
use Hammer\Helper\Array_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\IP_Lockout\Component\Login_Protection_Api;

class Log_Model extends DB_Model {
	const AUTH_FAIL = 'auth_fail', AUTH_LOCK = 'auth_lock', ERROR_404 = '404_error', LOCKOUT_404 = '404_lockout', ERROR_404_IGNORE = '404_error_ignore';
	protected static $tableName = 'defender_lockout_log';

	public $id;
	public $log;
	public $ip;
	public $date;
	public $user_agent;
	public $type;
	public $blog_id;
	public $tried;

	/**
	 * A helper attribute for storing status text to output to frontend
	 * This wont be save into db
	 * @var
	 */
	public $ip_status;
	public $is_mine;
	public $statusText;
	public $actionText;

	/**
	 * @return string
	 * @deprecated 2.2
	 */
	public function get_ip() {
		return esc_html( $this->ip );
	}

	/**
	 * @return string
	 * @deprecated 2.2
	 */
	public function get_log_text( $format = false ) {
		if ( ! $format ) {
			return esc_html( $this->log );
		} else {
			$text = sprintf( __( "Request for file <span class='log-text-table'>%s</span> which doesn't exist",
				wp_defender()->domain ), esc_attr( $this->log ) );

			return $text;
		}
	}

	public function before_update() {
		$this->blog_id = get_current_blog_id();
	}

	public function before_insert() {
		$this->blog_id = get_current_blog_id();
	}

	/**
	 * Get current status of this ip due to whitelist/blacklist data
	 * @return string
	 */
	public function blackOrWhite() {
		$settings = Settings::instance();
		if ( in_array( $this->ip, $settings->getIpWhitelist() ) ) {
			return 'whitelist';
		} elseif ( in_array( $this->ip, $settings->getIpBlacklist() ) ) {
			return 'blacklist';
		}

		return 'na';
	}

	/**
	 * @return string
	 */
	public function get_date() {
		if ( strtotime( '-24 hours' ) > $this->date ) {
			return Utils::instance()->formatDateTime( date( 'Y-m-d H:i:s', $this->date ) );
		} else {
			return Login_Protection_Api::time_since( $this->date ) . ' ' . __( "ago", wp_defender()->domain );
		}
	}

	/**
	 * @return mixed|null
	 */
	public function get_type() {
		$types = array(
			'auth_fail'        => __( "Failed login attempts", wp_defender()->domain ),
			'auth_lock'        => __( "Login lockout", wp_defender()->domain ),
			'404_error'        => __( "404 error", wp_defender()->domain ),
			'404_error_ignore' => __( "404 error", wp_defender()->domain ),
			'404_lockout'      => __( "404 lockout", wp_defender()->domain )
		);

		if ( isset( $types[ $this->type ] ) ) {
			return $types[ $this->type ];
		}

		return null;
	}

	/**
	 * Return summary data
	 * @param  bool  $for_hub
	 *
	 * @return array
	 */
	public static function getSummary( $for_hub = false ) {
		$lockouts = Log_Model::findAll( array(
			'type' => array(
				Log_Model::LOCKOUT_404,
				Log_Model::AUTH_LOCK
			),
			'date' => array(
				'compare' => '>=',
				'value'   => strtotime( '-30 days', current_time( 'timestamp' ) )
			)
		), 'id', 'DESC' );

		if ( count( $lockouts ) == 0 ) {
			$data = array(
				'lastLockout'           => __( "Never", wp_defender()->domain ),
				'lockoutToday'          => 0,
				'lockoutThisMonth'      => 0,
				'loginLockoutToday'     => 0,
				'loginLockoutThisWeek'  => 0,
				'lockout404Today'       => 0,
				'lockout404ThisWeek'    => 0,
				'lockoutLoginThisMonth' => 0,
				'lockout404ThisMonth'   => 0
			);

			return $data;
		}

		//init params
		$lastLockout           = '';
		$lockoutToday          = 0;
		$lockoutThisMonth      = count( $lockouts );
		$loginLockoutToday     = 0;
		$loginLockoutThisWeek  = 0;
		$lockout404ThisWeek    = 0;
		$lockout404Today       = 0;
		$loginLockoutThisMonth = 0;
		$lockout404ThisMonth   = 0;
		//time
		$todayMidnight = strtotime( '-24 hours', current_time( 'timestamp' ) );
		$firstThisWeek = strtotime( '-7 days', current_time( 'timestamp' ) );
		foreach ( $lockouts as $k => $log ) {
			//the other as DESC, so first will be last lockout
			if ( $k == 0 ) {
				$lastLockout = $for_hub
					? date( 'Y-m-d H:i:s', $log->date )
					: Utils::instance()->formatDateTime( date( 'Y-m-d H:i:s', $log->date ) );
			}

			if ( $log->date > $todayMidnight ) {
				$lockoutToday ++;
				if ( $log->type == self::LOCKOUT_404 ) {
					$lockout404Today += 1;
				} else {
					$loginLockoutToday += 1;
				}
			}

			if ( $log->type == Log_Model::AUTH_LOCK && $log->date > $firstThisWeek ) {
				$loginLockoutThisWeek ++;
			} elseif ( $log->type == Log_Model::LOCKOUT_404 && $log->date > $firstThisWeek ) {
				$lockout404ThisWeek ++;
			}

			if ( $log->type === Log_Model::AUTH_LOCK ) {
				$loginLockoutThisMonth += 1;
			} elseif ( $log->type === Log_Model::LOCKOUT_404 ) {
				$lockout404ThisMonth += 1;
			}
		}

		$data = array(
			'lastLockout'           => $lastLockout,
			'lockoutToday'          => $lockoutToday,
			'lockoutThisMonth'      => $lockoutThisMonth,
			'loginLockoutToday'     => $loginLockoutToday,
			'loginLockoutThisWeek'  => $loginLockoutThisWeek,
			'lockout404ThisWeek'    => $lockout404ThisWeek,
			'lockout404Today'       => $lockout404Today,
			'lockoutLoginThisMonth' => $loginLockoutThisMonth,
			'lockout404ThisMonth'   => $lockout404ThisMonth
		);

		return $data;
	}

	/**
	 * Pulling the logs data, use in Logs tab
	 * $filters will have those params
	 *  -date_from
	 *  -date_to
	 * == Defaults is 7 days and always require
	 *  -type: optional
	 *  -ip: optional
	 *
	 * @param  array  $filters
	 * @param  int  $paged
	 * @param  string  $orderBy
	 * @param  string  $order
	 * @param  int  $pageSize
	 *
	 * @return Log_Model[]
	 */
	public static function queryLogs(
		$filters = array(),
		$paged = 1,
		$orderBy = 'id',
		$order = 'DESC',
		$pageSize = 20
	) {
		$params = [
			'date' => [
				'compare' => 'between',
				'from'    => Array_Helper::getValue( $filters, 'dateFrom', strtotime( '-7 days midnight' ) ),
				'to'      => Array_Helper::getValue( $filters, 'dateTo', strtotime( 'tomorrow' ) )
			],
		];

		if ( ( $filter = Array_Helper::getValue( $filters, 'type', null ) ) != null ) {
			$params['type'] = $filter;
		}
		if ( ( $ip = Array_Helper::getValue( $filters, 'ip', null ) ) != null ) {
			$params['ip'] = $ip;
		}

		$offset = ( $paged - 1 ) * $pageSize;
		$models = Log_Model::findAll( $params, $orderBy, $order, "$offset,$pageSize" );
		$count  = Log_Model::count( $params );

		return [ $models, $count ];
	}

	/**
	 * @return array
	 */
	public function events() {
		$that = $this;

		return array(
			self::EVENT_BEFORE_INSERT => array(
				array(
					function () use ( $that ) {
						$that->before_insert();
					}
				)
			),
			self::EVENT_BEFORE_UPDATE => array(
				array(
					function () use ( $that ) {
						$that->before_update();
					}
				)
			)
		);
	}

	/**
	 * @return array
	 */
	public function notSaveFields() {
		return array( 'statusText', 'actionText', 'ip_status', 'is_mine' );
	}
}