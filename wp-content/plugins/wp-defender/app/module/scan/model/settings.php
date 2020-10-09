<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Model;

use Hammer\Helper\WP_Helper;
use Hammer\Queue\Queue;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Scan\Behavior\Core_Files;
use WP_Defender\Module\Scan\Behavior\Core_Scan;
use WP_Defender\Module\Scan\Behavior\Pro\Content_Files;
use WP_Defender\Module\Scan\Behavior\Pro\Content_Yara_Scan;
use WP_Defender\Module\Scan\Behavior\Pro\Vuln_Scan;
use WP_Defender\Module\Scan\Component\Scan_Api;

class Settings extends \Hammer\WP\Settings {

	private static $_instance;
	/**
	 * Scan WP core files
	 *
	 * @var bool
	 */
	public $scan_core = true;
	/**
	 * Verify plugins/themes with our db to see if any known bugs
	 * @var bool
	 */
	public $scan_vuln = true;

	/**
	 * @var bool
	 */
	public $scan_content = true;

	/**
	 * Receipts to sending notification
	 * @var array
	 */
	public $recipients = array();

	/**
	 * @var array
	 */
	public $recipients_notification = array();

	/**
	 * Toggle notification on or off
	 * @var bool
	 */
	public $notification = true;

	/**
	 * @var bool
	 */
	public $report = false;
	/**
	 * Toggle only sending error email or all email
	 *
	 * @var bool
	 */
	public $always_send = false;

	/**
	 * @var bool
	 */
	public $always_send_notification = false;

	/**
	 * Maximum filesize to scan, only apply for content scan
	 * @var int
	 */
	public $max_filesize = 1;

	/**
	 * Subject for email with issues
	 * @var string
	 */
	public $email_subject_issue = '';
	/**
	 * Subject for email without issues
	 * @var string
	 */
	public $email_subject = '';
	/**
	 * @var string|void
	 */
	public $email_has_issue = '';
	/**
	 * @var string|void
	 */
	public $email_all_ok = '';

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
	public $time = '4:00';

	/**
	 * @var
	 */
	public $last_report_sent;

	/**
	 * @return array
	 */
	public function behaviors() {
		$behaviors = array(
			'utils' => '\WP_Defender\Behavior\Utils'
		);

		if ( wp_defender()->isFree == false ) {
			$behaviors['pro'] = '\WP_Defender\Module\Scan\Behavior\Pro\Model';
		}

		return $behaviors;
	}

	public function __construct( $id, $is_multi ) {
		$this->email_subject_issue = __( 'Scan of {SITE_URL} complete. {ISSUES_COUNT} issues found.',
			wp_defender()->domain );
		$this->email_has_issue     = __( 'Hi {USER_NAME},

WP Defender here, reporting back from the front.

I\'ve finished scanning {SITE_URL} for vulnerabilities and I found {ISSUES_COUNT} issues that you should take a closer look at!
{ISSUES_LIST}

Stay Safe,
WP Defender
Official WPMU DEV Superhero', wp_defender()->domain );
		$this->email_subject       = __( 'Scan of {SITE_URL} complete. {ISSUES_COUNT} issues found.',
			wp_defender()->domain );
		$this->email_all_ok        = __( 'Hi {USER_NAME},

WP Defender here, reporting back from the front.

I\'ve finished scanning {SITE_URL} for vulnerabilities and I found nothing. Well done for running such a tight ship!

Keep up the good work! With regular security scans and a well-hardened installation you\'ll be just fine.

Stay safe,
WP Defender
Official WPMU DEV Superhero', wp_defender()->domain );
		//call parent to load stored
		if ( ( is_admin() || is_network_admin() ) && current_user_can( 'manage_options' ) ) {
			$user = wp_get_current_user();
			if ( is_object( $user ) ) {
				$this->recipients[]              = array(
					'first_name' => $user->display_name,
					'email'      => $user->user_email
				);
				$this->recipients_notification[] = array(
					'first_name' => $user->display_name,
					'email'      => $user->user_email
				);
			}

			//default is weekly
			$this->day  = strtolower( date( 'l' ) );
			$this->time = '4:00';
		}
		parent::__construct( $id, $is_multi );
		$this->notification = ! ! $this->notification;
		$this->report       = ! ! $this->report;
		$this->scan_content = ! ! $this->scan_content;
		$this->scan_core    = ! ! $this->scan_core;
		$this->scan_vuln    = ! ! $this->scan_vuln;

		if ( ! is_array( $this->recipients ) ) {
			$this->recipients = [];
		}
		$this->recipients = array_values( $this->recipients );
		if ( ! is_array( $this->recipients_notification ) ) {
			$this->recipients_notification = [];
		}
		$this->recipients_notification = array_values( $this->recipients_notification );

		$times = Utils::instance()->getTimes();
		if ( ! isset( $times[ $this->time ] ) ) {
			$this->time = '4:00';
		}
	}

	/**
	 * Act like a factory, return available scans based on pro or not
	 * @return array
	 */
	public function getScansAvailable() {
		$scans = array();
		if ( $this->scan_core ) {
			$scans[] = 'gather_core_files';
			$scans[] = 'core';
		}

		if ( $this->scan_vuln && wp_defender()->isFree != true ) {
			$scans[] = 'vuln';
		}

		if ( $this->scan_content && wp_defender()->isFree != true ) {
			//$scans[] = 'md5';
			$scans[] = 'content';
		}

		return $scans;
	}

	/**
	 * @return Settings
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			$class           = new Settings( 'wd_scan_settings',
				WP_Helper::is_network_activate( wp_defender()->plugin_slug ) );
			self::$_instance = $class;
		}

		return self::$_instance;
	}

	/**
	 * @param $slug
	 * @param  array  $args
	 *
	 * @return Queue|null
	 */
	public static function queueFactory( $slug, $args = array() ) {
		switch ( $slug ) {
			case 'gather_core_files':
				$queue                = new Queue( [ 'dummy' ], 'gather_core_files', true );
				$queue->args          = $args;
				$queue->args['owner'] = $queue;
				$queue->attachBehavior( 'core_files', new Core_Files() );

				return $queue;
			case 'core':
				$queue                = new Queue(
					Scan_Api::getCoreFiles(),
					'core',
					true
				);
				$queue->args          = $args;
				$queue->args['owner'] = $queue;
				$queue->attachBehavior( 'core', new Core_Scan() );

				return $queue;
			case 'vuln':
				if ( ! class_exists( '\WP_Defender\Module\Scan\Behavior\Pro\Vuln_Scan' ) ) {
					return null;
				}

				$queue = new Queue( array(
					'dummy'
				), 'vuln', true );

				$queue->args          = $args;
				$queue->args['owner'] = $queue;
				$queue->attachBehavior( 'vuln', new Vuln_Scan() );

				return $queue;
				break;
			case 'gather_content_files':
				if ( ! class_exists( '\WP_Defender\Module\Scan\Behavior\Pro\Content_Yara_Scan' ) ) {
					return null;
				}
				$queue                = new Queue( [ 'dummy' ], 'gather_content_files', true );
				$queue->args          = $args;
				$queue->args['owner'] = $queue;
				$queue->attachBehavior( 'content_files', new Content_Files() );

				return $queue;
			case 'content':
				if ( ! class_exists( '\WP_Defender\Module\Scan\Behavior\Pro\Content_Yara_Scan' ) ) {
					return null;
				}
				//dont use composer autoload preventing bloating
				$queue                   = new Queue( Scan_Api::getContentFiles(), 'content', true );
				$queue->args             = $args;
				$queue->args['owner']    = $queue;
				$patterns                = Scan_Api::getPatterns();
				$queue->args['patterns'] = $patterns;
				$queue->attachBehavior( 'content', new Content_Yara_Scan() );

				return $queue;
				break;
			default:
				//param not from the button on frontend, log it
				error_log( sprintf( 'Unexpected value %s from IP %s', $slug, Utils::instance()->getUserIp() ) );
				break;
		}
	}

	public function events() {
		$that = $this;

		return array(
			self::EVENT_BEFORE_SAVE => array(
				array(
					function () use ( $that ) {
						//need to turn off notification or report off if no recipients
						$keys = array(
							'recipients'              => 'report',
							'recipients_notification' => 'notification'
						);
						foreach ( $keys as $key => $attr ) {
							$recipients = $this->$key;
							$recipients = ! is_array( $recipients ) ? [] : $recipients;
							foreach ( $recipients as $k => &$recipient ) {
								$recipient = array_map( 'sanitize_text_field', $recipient );
								if ( ! filter_var( $recipient['email'], FILTER_VALIDATE_EMAIL ) ) {
									unset( $recipients[ $k ] );
								}
							}
							$this->$key = $recipients;
							$this->$key = array_filter( $this->$key );
							if ( count( $this->$key ) == 0 ) {
								$this->$attr = false;
							}
						}

					}
				)
			)
		);
	}

	public function format_hub_data() {
		return [
			'scan_core'                => $this->scan_core ? __( 'Yes', wp_defender()->domain ) : __( 'No',
				wp_defender()->domain ),
			'scan_vuln'                => $this->scan_vuln ? __( 'Yes', wp_defender()->domain ) : __( 'No',
				wp_defender()->domain ),
			'scan_content'             => $this->scan_content ? __( 'Yes', wp_defender()->domain ) : __( 'No',
				wp_defender()->domain ),
			'max_filesize'             => $this->max_filesize . ' MB',
			'notification'             => $this->notification ? __( 'Activate',
				wp_defender()->domain ) : __( 'Inactivate',
				wp_defender()->domain ),
			'always_send_notification' => $this->always_send_notification ? __( 'Yes',
				wp_defender()->domain ) : __( 'No', wp_defender()->domain ),
			'email_subject_issue'      => $this->email_subject_issue,
			'email_subject'            => $this->email_subject,
			'email_has_issue'          => $this->email_has_issue,
			'email_all_ok'             => $this->email_all_ok,
			'recipients_notification'  => empty( $this->recipients_notification ) ? __( 'No recipients',
				wp_defender()->domain ) : Utils::instance()->recipientsToString( $this->recipients_notification ),
			'report'                   => $this->report ? __( 'Activate',
				wp_defender()->domain ) : __( 'Inactivate', wp_defender()->domain ),
			'always_send'              => $this->always_send ? __( 'Yes',
				wp_defender()->domain ) : __( 'No', wp_defender()->domain ),
			'recipients'               => empty( $this->recipients ) ? __( 'No recipients',
				wp_defender()->domain ) : Utils::instance()->recipientsToString( $this->recipients ),
			'frequency'                => Utils::instance()->format_frequency_for_hub( $this->frequency, $this->day,
				$this->time )
		];
	}

	/**
	 * @return array
	 */
	public function export_strings( $configs ) {
		$model = new Settings( 'wd_scan_settings',
			WP_Helper::is_network_activate( wp_defender()->plugin_slug ) );
		$model->import( $configs );
		$strings = [];
		if ( $this->is_any_active() ) {
			$strings[] = __( 'Active', wp_defender()->domain );
		} else {
			$strings[] = __( 'Inactive', wp_defender()->domain );
		}

		if ( $model->notification ) {
			$strings[] = __( 'Email notifications active', wp_defender()->domain );
		}
		if ( $model->report && ! wp_defender()->isFree ) {
			$strings[] = sprintf( __( 'Email reports sending %s', wp_defender()->domain ),
				Utils::instance()->frequencyToText( $model->frequency ) );
		} elseif ( wp_defender()->isFree ) {
			$strings[] = sprintf( __( 'Email report inactive %s', wp_defender()->domain ),
				'<span class="sui-tag sui-tag-pro">Pro</span>' );
		}

		return $strings;
	}

	public function is_any_active() {
		if ( ! $this->scan_core && wp_defender()->isFree ) {
			return false;
		} elseif ( ( ! $this->scan_core && ! $this->scan_content && ! $this->scan_vuln ) && ! wp_defender()->isFree ) {
			return false;
		}

		return true;
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
			'scan_core'                => __( "Scan WordPress Core", wp_defender()->domain ),
			'scan_vuln'                => __( "Scan Plugins & Themes", wp_defender()->domain ),
			'scan_content'             => __( "Scan Suspicious Code", wp_defender()->domain ),
			'max_filesize'             => __( "Maximum included filesize", wp_defender()->domain ),
			'notification'             => __( "Send scan completion email", wp_defender()->domain ),
			'always_send_notification' => __( "Send scan completion emails even if no issues are detected",
				wp_defender()->domain ),
			'email_subject_issue'      => __( "Email subject when an issue is found", wp_defender()->domain ),
			'email_subject'            => __( "Email subject when no issues are found", wp_defender()->domain ),
			'email_has_issue'          => __( "Email body when an issue is found", wp_defender()->domain ),
			'email_all_ok'             => __( "Email body when no issues are found", wp_defender()->domain ),
			'recipients_notification'  => __( "Scan completion email recipients", wp_defender()->domain ),

			'report'      => __( "Send scheduled email reports", wp_defender()->domain ),
			'always_send' => __( "Send email report even if no issues are detected",
				wp_defender()->domain ),
			'recipients'  => __( "Scheduled email report recipients", wp_defender()->domain ),
			'frequency'   => __( "Report Frequency", wp_defender()->domain ),

		];
		if ( $key != null ) {
			return isset( $labels[ $key ] ) ? $labels[ $key ] : null;
		}

		return $labels;
	}
}