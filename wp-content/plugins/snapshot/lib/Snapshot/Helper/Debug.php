<?php // phpcs:ignore
/**
 * This file defines the Snapshot_Helper_Debug class.
 *
 * @copyright Incsub (http://incsub.com/)
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301 USA
 *
 */

if ( ! defined( 'DEBUG_BACKTRACE_IGNORE_ARGS' ) ) {
	define( 'DEBUG_BACKTRACE_IGNORE_ARGS', 2 );
}

if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/**
 * This Helper creates utility functions for debugging.
 *
 * @since 2.5
 * @package Snapshot
 * @subpackage Helper
 */
class Snapshot_Helper_Debug {

	/**
	 * Logs errors to WordPress debug log.
	 *
	 * The following constants ned to be set in wp-config.php
	 * or elsewhere where turning on and off debugging makes sense.
	 *
	 *     // Essential
	 *     define('WP_DEBUG', true);
	 *     // Enables logging to /wp-content/debug.log
	 *     define('WP_DEBUG_LOG', true);
	 *     // Force debug messages in WordPress to be turned off (using logs instead)
	 *     define('WP_DEBUG_DISPLAY', false);
	 *
	 * @since 1.0.0
	 * @param  mixed $message Array, object or text to output to log.
	 */
	public static function log( $message, $echo_file = false ) {
		$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ); // phpcs:ignore
		$exception = new Exception();
		$debug = array_shift( $trace );
		$caller = array_shift( $trace );
		$exception = $exception->getTrace();
		$callee = array_shift( $exception );

		if ( true === WP_DEBUG ) {
			$msg = isset( $caller['class'] ) ? $caller['class'] . '[' . $callee['line'] . ']: ' : '';

			if ( is_array( $message ) || is_object( $message ) ) {
				$msg .= print_r( $message, true ); // phpcs:ignore
			} else {
				$msg .= $message;
			}

			if ( $echo_file ) {
				$msg .= "\nIn " . $callee['file'] . ' on line ' . $callee['line'];
			}

			error_log( $msg ); // phpcs:ignore
		}
	}

	public static function debug_trace( $return = false ) {
		$traces = debug_backtrace(); // phpcs:ignore
		$fields = array(
			'file',
			'line',
			'function',
			'class',
		);
		$log = array( '---------------------------- Trace start ----------------------------' );

		foreach ( $traces as $i => $trace ) {
			$line = array();
			foreach ( $fields as $field ) {
				if ( ! empty( $trace[ $field ] ) ) {
					$line[] = "$field: {$trace[ $field ]}";
				}
			}
			$log[] = "  [$i]". implode( '; ', $line );
		}

		if ( $return ) {
			return implode( "\n", $log );
		} else {
			error_log( implode( "\n", $log ) ); // phpcs:ignore
		}
	}

	public static function process_error_backtrace( $errno, $errstr, $errfile, $errline, $errcontext ) {
		if ( ! ( error_reporting() & $errno ) ) { // phpcs:ignore
			return;
		}

		switch ( $errno ) {
			case E_WARNING      :
			case E_USER_WARNING :
			case E_STRICT       :
			case E_NOTICE       :
			case E_USER_NOTICE  :
				$type = 'warning';
				$fatal = false;
				break;

			default:
				$type = 'fatal error';
				$fatal = true;
				break;
		}

		$message = "[$type]: '$errstr' file: $errfile, line: $errline";
		error_log( $message ); // phpcs:ignore
		self::debug_trace();

		if ( $fatal ) {
			exit( 1 );
		}
	}

	/**
	 * @todo Is this method still needed?
	 * @param null $errorReporting
	 */
	public static function set_error_reporting($errorReporting = null) {
		if (isset($errorReporting)) {
			$error_reporting_str = '';

			foreach($errorReporting as $er_key => $er_set) {

				if (isset($er_set['stop'])) {
					$error_reporting_str = $error_reporting_str || $er_key;
				}
			}
			$after_error_reporting = error_reporting($error_reporting_str); // phpcs:ignore
		} else {
			error_reporting(0); // phpcs:ignore
		}
	}

}

// phpcs:ignore
set_error_handler(
	array( 'Snapshot_Helper_Debug', 'process_error_backtrace')
);

// Legacy
if( function_exists('class_alias') ) {
	class_alias( 'Snapshot_Helper_Debug', 'SS_Debug' );
}