<?php
/**
 * Shipper controllers: controller runners abstraction
 *
 * Runner controller is a self-calling implementation that works on a single
 * goal (process) until finished (or cancelled).
 * All runner implementations will inherit from this.
 *
 * @package shipper
 */

/**
 * Controller runner abstraction
 */
abstract class Shipper_Controller_Runner extends Shipper_Controller {

	public $process;

	/**
	 * Holds kickstarter helper instance
	 *
	 * @var object Shipper_Helper_Kickstarter instance
	 */
	public $kickstarter;

	/**
	 * Actually performs a process tick
	 *
	 * @return bool Whether to continue with processing
	 */
	abstract public function process_tick();

	/**
	 * Gets process lock for the implementation
	 *
	 * @return string One of the Shipper_Helper_Locks constants
	 */
	abstract public function get_process_lock();

	/**
	 * Implements process-specific cancellation cleanup
	 */
	abstract public function process_cancel();

	/**
	 * Constructor
	 *
	 * @param string $process Current process identifier.
	 */
	protected function __construct( $process ) {
		parent::__construct();
		$this->process = $process;
	}

	/**
	 * Gets action string for this runner
	 *
	 * Used for hook registration and AJAX request creation.
	 *
	 * @return string
	 */
	public function get_action() {
		return sprintf( 'shipper_%s_self_ping', $this->process );
	}

	/**
	 * Gets process tick validity interval window
	 *
	 * Tick requests have limited validity window.
	 * It's 10 minutes by default, filterable.
	 *
	 * @return int
	 */
	public function get_tick_validity_interval() {
		return (int) apply_filters(
			'shipper_runner_tick_validity_interval',
			600,
			$this->process
		);
	}

	/**
	 * Gets request signature hash
	 *
	 * A hash is basically a pseudo-nonce, valid for tick validity interval.
	 *
	 * @return string
	 */
	public function get_hash() {
		$hasher = new Shipper_Helper_Hash( $this->get_tick_validity_interval() );
		return $hasher->get_hash( $this->get_action() );
	}

	/**
	 * Boots the runner and binds hook listeners
	 */
	public function boot() {
		$action = Shipper_Model_Env::is_auth_requiring_env()
			? $this->get_action()
			: sprintf( 'nopriv_%s', $this->get_action() );
		add_action(
			sprintf( 'wp_ajax_%s', $action ),
			array( $this, 'json_process_request' )
		);

		$this->kickstarter = new Shipper_Helper_Kickstarter(
			$this->get_action(),
			array( $this, 'kickstart' )
		);
	}

	/**
	 * Kickstarts the process running, if needed
	 *
	 * @uses run method, if set.
	 *
	 * @return bool
	 */
	public function kickstart() {
		if ( ! is_callable( array( $this, 'run' ) ) ) {
			return false;
		}
		Shipper_Helper_Log::debug( 'kickstart' );

		/**
		 * Fires on kickstart attempt
		 *
		 * @since v1.0.1
		 */
		do_action( 'shipper_kickstarted' );

		return $this->run();
	}

	/**
	 * Processes tick request
	 *
	 * Sets process lock.
	 */
	public function json_process_request() {
		if ( Shipper_Model_Force::maybe_stuck_on_migration_preflight() ) {
			/**
			 * Force fully stuck pre-flight check for API migration.
			 *
			 * @since 1.2.6
			 */
			return;
		}

		if ( function_exists( 'ignore_user_abort' ) ) {
			ignore_user_abort( true );
		}

		if ( function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
		}

		$locks = new Shipper_Helper_Locks();
		$lock  = $this->get_process_lock();

		if ( $locks->has_lock( Shipper_Helper_Locks::LOCK_CANCEL ) ) {
			Shipper_Helper_Log::write( 'Cancel lock set, process shutting down' );
			$this->cancel();
			die;
		}

		if ( $locks->has_lock( $lock ) ) {
			die;
		}

		$data     = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce already checked.
		$hash     = isset( $data['hash'] ) ? sanitize_text_field( $data['hash'] ) : false;
		$expected = $this->get_hash();

		// @codingStandardsIgnoreLine `hash_equals` is WP-backported
		if ( ! hash_equals( $hash, $expected ) ) {
			// We don't have a valid hash param, uh oh!
			die;
		}

		register_shutdown_function( array( $this, 'handle_core_error' ) );

		$locks->set_lock( $lock );

		$this->kickstarter->schedule_reboot();
		/**
		 * Fires just before runner tick processing, after locking
		 *
		 * @param object $runner Runner instance.
		 */
		do_action( 'shipper_runner_pre_request_tick', $this );
		$call_self = $this->process_tick();
		$locks->release_lock( $lock );

		if ( ! empty( $call_self ) ) {
			$this->ping();
		}

		die;
	}

	/**
	 * Handles any errors halting the runner process.
	 */
	public function handle_core_error() {
		$error = error_get_last();

		if ( null === $error || ! is_array( $error ) ) {
			// No error, we're good here.
			return false;
		}

		$fatals = array(
			// Plain-old fatal.
			E_ERROR,
			// Probably won't reach us, but still...
			E_CORE_ERROR,
			E_COMPILE_ERROR,
			// Userland errors.
			E_USER_ERROR,
			E_RECOVERABLE_ERROR,
		);

		if ( in_array( $error['type'], $fatals, true ) ) {
			Shipper_Helper_Log::write(
				sprintf(
					/* translators: %1$s %2$s %3$s: error message, file and line number. */
					__( 'Encountered a FATAL ERROR: %1$s in %2$s on line %3$d', 'shipper' ),
					$error['message'],
					$error['file'],
					$error['line']
				)
			);
			$this->cancel(); // Yeah, cancel too.
		}
	}

	/**
	 * Initializes the runner cancelling attempt
	 *
	 * Needed because the actual process might be going on in a different thread.
	 * Is meant to be called from outside.
	 * Sets the cancellation lock.
	 */
	public function attempt_cancel() {
		$locks = new Shipper_Helper_Locks();
		$lock  = $this->get_process_lock();

		if ( $locks->has_lock( $lock ) ) {
			if ( ! $locks->has_lock( Shipper_Helper_Locks::LOCK_CANCEL ) ) {
				Shipper_Helper_Log::write( 'Lock set, wait for the running tasks to shut down' );
				$locks->set_lock( Shipper_Helper_Locks::LOCK_CANCEL );
			} else {
				$is_old_lock = $locks->is_old_lock( $lock );
				if ( $is_old_lock ) {
					$locks->release_lock( $lock );
					$this->ping();
				}
			}
		} else {
			if ( ! $locks->has_lock( Shipper_Helper_Locks::LOCK_CANCEL ) ) {
				$this->cancel();
			} else {
				if ( $locks->is_old_lock( Shipper_Helper_Locks::LOCK_CANCEL ) ) {
					$this->ping();
				}
			}
		}

	}

	/**
	 * Wraps process cancelling and deals with locking
	 */
	public function cancel() {
		$locks = new Shipper_Helper_Locks();
		$lock  = $this->get_process_lock();

		$locks->set_lock( $lock );
		$status = $this->process_cancel();
		$locks->release_lock( Shipper_Helper_Locks::LOCK_CANCEL );
		$locks->release_lock( $lock );

		$this->kickstarter->unschedule_reboot();

		return $status;
	}

	/**
	 * Call ourselves to continue processing the migration
	 *
	 * @return bool
	 */
	public function ping() {
		$locks = new Shipper_Helper_Locks();
		$lock  = $this->get_process_lock();
		if ( $locks->has_lock( $lock ) ) {
			if ( ! $locks->is_old_lock( $lock ) ) {
				// Process is already locked - meaning, it's still running in another thread.
				// We don't want to re-ping as the other thread will do it for us, once it's done.
				return false;
			} else {
				Shipper_Helper_Log::write( 'Lock overdue, cleaning up' );
				$locks->release_lock( $lock );

				/**
				 * Fires on stale lock removal
				 *
				 * @since v1.0.1
				 *
				 * @param string $lock Process lock identifier.
				 */
				do_action(
					'shipper_flag_cleared',
					$lock
				);
			}
		}

		$args   = http_build_query(
			array(
				'action' => $this->get_action(),
				'hash'   => $this->get_hash(),
			)
		);
		$params = array(
			'url'  => admin_url( "admin-ajax.php?{$args}" ),
			'args' => $this->get_ping_request_args(),
		);
		$rsp    = wp_remote_post( $params['url'], $params['args'] );

		if ( $this->is_blocking_request() ) {
			$code = wp_remote_retrieve_response_code( $rsp );
			if ( 200 !== (int) $code ) {
				Shipper_Helper_Log::debug(
					sprintf(
						/* translators: $d: response code. */
						__( 'Non-success response code (%d)', 'shipper' ),
						$code
					)
				);
			}
		}

		// Re-schedule here. The request can silently drop without ever reaching processor.
		// In this case the kickstart is left unscheduled.
		$this->kickstarter->schedule_reboot();

		/**
		 * Fire `shipper_runner_ping` action
		 *
		 * @since 1.2.6
		 */
		do_action( 'shipper_runner_ping', $this );

		return true;
	}

	/**
	 * Gets a set of arguments used for ping request query
	 *
	 * @return array
	 */
	public function get_ping_request_args() {
		$args = array(
			'timeout'   => $this->get_ping_timeout(),
			'blocking'  => $this->is_blocking_request(),
			'sslverify' => false,
			'headers'   => array(
				'user-agent' => shipper_get_user_agent(),
			),
		);

		if ( Shipper_Model_Env::is_auth_requiring_env() ) {
			$args['cookies'] = $this->get_auth_cookies();
		}

		return $args;
	}

	/**
	 * Gets a set of cookies to identify the ping request
	 *
	 * @return array
	 */
	public function get_auth_cookies() {
		$cookies = array();

		$user = shipper_get_admin_user();
		if ( empty( $user ) ) {
			return $cookies;
		}

		$user_id          = $user->ID;
		$auth_cookie_name = AUTH_COOKIE;
		$scheme           = 'auth';

		$secure = is_ssl();
		$secure = apply_filters( 'secure_auth_cookie', $secure, $user_id );
		if ( $secure ) {
			$auth_cookie_name = SECURE_AUTH_COOKIE;
			$scheme           = 'secure_auth';
		}

		// Allow one hour runtime.
		$expiration = time() + HOUR_IN_SECONDS;

		$cookies[] = new WP_Http_Cookie(
			array(
				'name'  => $auth_cookie_name,
				'value' => wp_generate_auth_cookie( $user_id, $expiration, $scheme ),
			)
		);
		$cookies[] = new WP_Http_Cookie(
			array(
				'name'  => LOGGED_IN_COOKIE,
				'value' => wp_generate_auth_cookie( $user_id, $expiration, 'logged_in' ),
			)
		);

		if ( defined( 'WPE_APIKEY' ) ) {
			// WP Engine's proprietary auth cookie.
			$cookies[] = new WP_Http_Cookie(
				array(
					'name'  => 'wpe-auth',
					'value' => md5( 'wpe_auth_salty_dog|' . WPE_APIKEY ),
				)
			);
		}

		return $cookies;
	}

	/**
	 * Gets the number of seconds allotted to the ping request
	 *
	 * @return float Seconds (can be fraction)
	 */
	public function get_ping_timeout() {
		$timeout = $this->is_blocking_request()
			? 30.0
			: 1.0;

		/**
		 * Gets the runner ping timeout
		 *
		 * This is a (fractional) number of seconds we're allowing
		 * to our self-ping POST request to run.
		 *
		 * Default is 30s for blocking requests, and 0.5s for non-blocking.
		 *
		 * @param float $timeout Ping timeout, in seconds.
		 *
		 * @return float
		 */
		return (float) apply_filters(
			'shipper_runner_ping_timeout',
			$timeout
		);
	}

	/**
	 * Whether our request will be blocking or not
	 *
	 * @return bool
	 */
	public function is_blocking_request() {

		/**
		 * Whether the runner self-ping request is a blocking one
		 *
		 * Defaults to false.
		 *
		 * @param bool $is_blocking Whether the request will be a blocking one.
		 *
		 * @return bool
		 */
		return (bool) apply_filters(
			'shipper_runner_ping_is_blocking',
			false
		);

	}

}