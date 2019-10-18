<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Audit\Model;

use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;

class Settings extends \Hammer\WP\Settings {

	private static $_instance;

	/**
	 * @var bool
	 */
	public $enabled = false;

	/**
	 * @var string
	 */
	public $frequency = '7';
	/**
	 * @var string
	 */
	public $day = 'sunday';
	/**
	 * @var string
	 */

	public $time = '0:00';
	/**
	 * Toggle notification on or off
	 * @var bool
	 */
	public $notification = true;

	/**
	 * @var array
	 */
	public $receipts = array();

	public $dummy = array();
	/**
	 * @var
	 */
	public $lastReportSent;

	public $storage_days = '6 months';

	/**
	 * @return array
	 */
	public function behaviors() {
		return array(
			'utils' => '\WP_Defender\Behavior\Utils'
		);
	}

	public function __construct( $id, $isMulti ) {
		if ( is_admin() || is_network_admin() && current_user_can( 'manage_options' ) ) {
			$user = wp_get_current_user();
			if ( is_object( $user ) ) {
				$this->receipts[] = array(
					'first_name' => $user->display_name,
					'email'      => $user->user_email
				);
			}
			$this->day  = strtolower( date( 'l' ) );
			$this->time = '4:00';
		}
		parent::__construct( $id, $isMulti );
		$this->notification = ! ! $this->notification;
		$times              = Utils::instance()->getTimes();
		if ( ! isset( $times[ $this->time ] ) ) {
			$this->time = '4:00';
		}
		if ( ! is_array( $this->receipts ) ) {
			$this->receipts = [];
		}
		$this->receipts = array_values( $this->receipts );
		if ( ! in_array( (string) $this->storage_days, [
			'24 hours',
			'7 days',
			'30 days',
			'3 months',
			'6 months',
			'12 months'
		] ) ) {
			$this->storage_days = '6 months';
		}
	}

	/**
	 * @return Settings
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			$class           = new Settings( 'wd_audit_settings', WP_Helper::is_network_activate( wp_defender()->plugin_slug ) );
			self::$_instance = $class;
		}

		return self::$_instance;
	}

	/**
	 * Get report time as string, we will use this in summary box
	 * @return string
	 */
	public function get_report_times_as_string() {
		$report_time = '-';
		if ( $this->notification == true ) {
			if ( $this->frequency == 1 ) {
				$report_time = sprintf( __( "at %s", wp_defender()->domain ),
					strftime( '%I:%M %p', strtotime( $this->time ) ) );
			} else {
				$report_time = sprintf( __( "%s at %s", wp_defender()->domain ),
					ucfirst( $this->day ),
					strftime( '%I:%M %p', strtotime( $this->time ) ) );

			}
		}

		return $report_time;
	}

	public function events() {
		$that = $this;

		return array(
			self::EVENT_BEFORE_SAVE => array(
				array(
					function () use ( $that ) {
						//need to turn off notification or report off if no recipients
						if ( empty( $this->receipts ) ) {
							$this->notification = false;
						}
						//sanitize
						foreach ( $this->receipts as $key => &$receipt ) {
							$receipt = array_map( 'sanitize_text_field', $receipt );
							if ( ! filter_var( $receipt['email'], FILTER_VALIDATE_EMAIL ) ) {
								unset( $this->receipts[ $key ] );
							}
						}
					}
				)
			)
		);
	}

	/**
	 * Define labels for settings key, we will use it for HUB
	 *
	 * @param null $key
	 *
	 * @return array|mixed
	 */
	public function labels( $key = null ) {
		$labels = [
			'notification' => __( 'Notification', wp_defender()->domain ),
			'receipts'     => __( 'Recipients', wp_defender()->domain ),
			'day'          => __( "Day of the week", wp_defender()->domain ),
			'time'         => __( "Time of day", wp_defender()->domain ),
			'frequency'    => __( "Frequency", wp_defender()->domain ),
			'storage_days' => __( "Storage for", wp_defender()->domain )
		];

		if ( $key != null ) {
			return isset( $labels[ $key ] ) ? $labels[ $key ] : null;
		}

		return $labels;
	}
}