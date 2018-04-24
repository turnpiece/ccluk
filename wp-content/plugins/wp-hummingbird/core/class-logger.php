<?php
/**
 * Logger class.
 *
 * This one will not use WP_Hummingbird_Filesystem class,
 * because it is used for creating new files only.
 *
 * @package Hummingbird
 * @author: WPMUDEV, Anton Vanyukov (vanyukov)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Hummingbird_Logger
 */
class WP_Hummingbird_Logger {

	/**
	 * Log filename.
	 *
	 * @since  1.7.2
	 *
	 * @access private
	 * @var    string $file
	 */
	private $file;

	/**
	 * Log directory.
	 *
	 * @since  1.7.2
	 *
	 * @access private
	 * @var    string $log_dir
	 */
	private $log_dir;

	/**
	 * Module slug. Module to log for.
	 *
	 * @since  1.7.2
	 * @access private
	 * @var    string $module
	 */
	private $module = '';

	/**
	 * Logger status.
	 *
	 * @since  1.7.2
	 *
	 * @access private
	 * @var    WP_Error|bool $status
	 */
	private $status = false;

	/**
	 * WP_Hummingbird_Logger constructor.
	 *
	 * @since  1.7.2
	 *
	 * @access private
	 * @param  string $module  Module slug.
	 */
	public function __construct( $module ) {
		$this->module = $module;

		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		}

		$this->create_log_dir();
		$this->prepare_file();
	}

	/**
	 * Prepare filename.
	 *
	 * @since  1.7.2
	 *
	 * @access private
	 */
	private function prepare_file() {
		$this->file = $this->module . '-debug.log';

		// Only the minification module has a per/site configuration.
		if ( 'minify' === $this->module ) {
			$this->file = $this->get_domain_prefix() . $this->file;
		}

		$this->file = $this->log_dir . $this->file;
	}

	/**
	 * Get site url to prefix the log file.
	 *
	 * @since  1.7.2
	 *
	 * @access private
	 *
	 * @return string
	 */
	private function get_domain_prefix() {
		if ( ! is_multisite() ) {
			return '';
		}

		$blog = get_blog_details();

		if ( '/' === $blog->path ) {
			return $blog->domain . '-';
		} elseif ( defined( 'SUBDOMAIN_INSTALL' ) && ! SUBDOMAIN_INSTALL ) {
			return $blog->domain . '-' . str_replace( '/', '', $blog->path ) . '-';
		}

		return $blog->path . '-';
	}

	/**
	 * Check if log directory is already create, if not - create it.
	 *
	 * @since  1.7.2
	 *
	 * @access private
	 */
	private function create_log_dir() {
		$this->log_dir = WP_CONTENT_DIR . '/wphb-logs/';

		if ( is_dir( $this->log_dir ) && is_writeable( $this->log_dir ) ) {
			return;
		}

		if ( ! @mkdir( $this->log_dir ) ) {
			$error = error_get_last();
			$this->status = new WP_Error( 'log-dir-error', $error['message'] );
		}
	}

	/**
	 * Attempt to write file.
	 *
	 * @since  1.7.2
	 *
	 * @access private
	 *
	 * @param  string $mode     Accepts any mode from the list: http://php.net/manual/en/function.fopen.php.
	 * @param  string $message  String to write to file.
	 */
	private function write_file( $mode, $message = '' ) {
		try {
			$fp = fopen( $this->file, $mode );
			flock( $fp, LOCK_EX );
			fwrite( $fp, $message );
			flock( $fp, LOCK_UN );
			fclose( $fp );
		} catch ( Exception $e ) {
			$this->status = new WP_Error( 'log-write-error', $e->getMessage() );
		}
	}

	/**
	 * Cleanup on uninstall.
	 *
	 * @since 1.7.2
	 */
	public static function cleanup() {
		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		}

		$log_dir = WP_CONTENT_DIR . '/wphb-logs/';

		// If no directory is present - exit.
		if ( ! is_dir( $log_dir ) ) {
			return;
		}

		try {
			$dir = opendir( $log_dir );
			while ( false !== ( $file = readdir( $dir ) ) ) {
				if ( ( '.' == $file ) || ( '..' == $file ) ) {
					continue;
				}

				$full = $log_dir . $file;
				if ( is_dir( $full ) ) {
					rmdir( $full );
				} else {
					unlink( $full );
				}
			}

			closedir( $dir );
			rmdir( $log_dir );
		} catch ( Exception $e ) {
			error_log( '[' . current_time( 'mysql' ) . '] - Unable to clean Hummingbird log directory. Error: ' . $e->getMessage() );
		}
	}

	/**
	 * Check if module should log or not.
	 *
	 * @since  1.7.2
	 *
	 * @return bool
	 */
	private function should_log() {
		// Don't log if there's an error.
		if ( is_wp_error( $this->status ) ) {
			return false;
		}

		// No module has been set.
		if ( empty( $this->module ) ) {
			return false;
		}

		$do_log = false;
		switch ( $this->module ) {
			case 'minify':
				// Log for minification only if debug is enabled.
				/* @var WP_Hummingbird_Module_Minify $minify */
				$minify = WP_Hummingbird_Utils::get_module( 'minify' );
				$options = $minify->get_options();

				if ( $options['log'] ) {
					$do_log = true;
				}
				break;
			default:
				// Default to logging only when wp debug is set.
				if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					$do_log = true;
				}
				break;
		}

		return $do_log;
	}

	/**
	 * Main logging function.
	 *
	 * @since 1.7.2
	 *
	 * @param mixed $message  Data to write to log.
	 */
	public function log( $message ) {
		if ( ! $this->should_log() ) {
			return;
		}

		if ( ! is_string( $message ) || is_array( $message ) || is_object( $message ) ) {
			$message = print_r( $message, true );
		}

		$message = '[' . date( 'H:i:s' ) . '] ' . $message . PHP_EOL;

		$this->write_file( 'a', $message );
	}
}