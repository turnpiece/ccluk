<?php
/**
 * Shipper helpers: log file manipulation
 *
 * @package shipper
 */

/**
 * Log helper class
 */
class Shipper_Helper_Log {

	/**
	 * Holds hasher instance, for storage obfuscation
	 *
	 * @var object Shipper_Helper_Hash instance
	 */
	private static $hasher;

	const LOG_BASENAME = 'shipper-log';

	/**
	 * Gets log file name
	 *
	 * @param string $basename Optional basename to use.
	 * @param string $type Optional file type (extension).
	 *
	 * @return string Just the file name
	 */
	public static function get_file_name( $basename = false, $type = 'txt' ) {
		if ( empty( self::$hasher ) ) {
			self::$hasher = new Shipper_Helper_Hash();
		}
		$basename = self::get_validated_basename( $basename );
		$type     = self::get_validated_file_type( $type );

		return self::$hasher->get_concealed( $basename ) . ".{$type}";
	}

	/**
	 * Gets validated basename for log filename generation
	 *
	 * The name will be hashed so we can be relatively lax with the check.
	 *
	 * @param string $basename Basename to validate.
	 *
	 * @return string
	 */
	public static function get_validated_basename( $basename ) {
		return ! empty( $basename ) && is_string( $basename )
			? $basename
			: self::LOG_BASENAME;
	}

	/**
	 * Gets validated file type (extension)
	 *
	 * @param string $type File extension (sans leading dot).
	 *
	 * @return string
	 */
	public static function get_validated_file_type( $type ) {
		$types = array( 'txt', 'csv', 'json' );

		return in_array( $type, $types, true )
			? $type
			: 'txt';
	}

	/**
	 * Gets absolute path to a log file
	 *
	 * @param string $basename Optional basename to use.
	 * @param string $type Optional file type (extension).
	 *
	 * @return string Full path
	 */
	public static function get_file_path( $basename = false, $type = null ) {
		return Shipper_Helper_Fs_Path::get_log_dir() . self::get_file_name( $basename, $type );
	}

	/**
	 * Gets log file contents
	 *
	 * @return string
	 */
	public static function get_contents() {
		$path = self::get_file_path();

		$fs = Shipper_Helper_Fs_File::open( $path );

		if ( ! $fs || ! $fs->isReadable() ) {
			return '';
		}

		return $fs->fread( $fs->getSize() );
	}

	/**
	 * Gets parsed log lines
	 *
	 * @return array A list of line hashes
	 */
	public static function get_lines() {
		$lines    = array();
		$contents = self::get_contents();
		if ( empty( $contents ) ) {
			return $lines;
		}

		foreach ( explode( "\n", $contents ) as $line ) {
			if ( empty( $line ) ) {
				continue;
			}

			$raw     = explode( Shipper_Model::SCOPE_DELIMITER, $line, 2 );
			$time    = ! empty( $raw[0] )
				? trim( $raw[0] )
				: 'never';
			$message = ! empty( $raw[1] )
				? trim( $raw[1] )
				: '';
			$lines[] = array(
				'timestamp' => strtotime( $time ),
				'message'   => $message,
			);
		}

		return $lines;
	}

	/**
	 * Writes a line to the log file
	 *
	 * @param string $msg Message to format and write.
	 *
	 * @return bool
	 */
	public static function write( $msg ) {
		$path        = self::get_file_path();
		$file_exists = file_exists( $path );

		if ( $file_exists && ! is_writable( $path ) ) {
			return false;
		}

		$size = $file_exists ? filesize( $path ) : 0;
		if ( $size > 100 * 1000000 ) {
			// fail safe.
			return false;
		}

		$line = sprintf(
			'%s %s %s' . "\n",
			gmdate( 'Y-m-d H:i:s' ),
			Shipper_Model::SCOPE_DELIMITER,
			$msg
		);

		$fs = Shipper_Helper_Fs_File::open( $path, 'a' );

		if ( ! $fs ) {
			return false;
		}

		return ! ! $fs->fwrite( $line );
	}

	/**
	 * Add a debug line to the log file
	 *
	 * Debug line is only added if debugging is enabled.
	 *
	 * @param string $msg Message to format and write.
	 *
	 * @return bool
	 */
	public static function debug( $msg ) {
		if ( ! apply_filters( 'shipper_log_debug_statements', false ) ) {
			// Not in log debug mode.
			return false;
		}
		$msg = sprintf( '[DEBUG] %s', $msg );
		return self::write( $msg );
	}

	/**
	 * Adds a data dump to the debug csv file
	 *
	 * @param array $data Data to add.
	 */
	public static function data( $data ) {
		$fs = Shipper_Helper_Fs_File::open(
			self::get_file_path( 'debug', 'csv' ),
			'a'
		);

		if ( ! $fs ) {
			return false;
		}

		$fs->fputcsv( $data );
	}

	/**
	 * Clears log file completely
	 *
	 * @return bool
	 */
	public static function clear() {
		$path = self::get_file_path();
		$fs   = Shipper_Helper_Fs_File::open( $path, 'w' );

		if ( ! $fs || $fs->fwrite( '' ) ) {
			self::write( __( 'Log file cleared', 'shipper' ) );
		}

		// Also clear data file.
		$path = self::get_file_path( 'debug', 'csv' );
		if ( file_exists( $path ) ) {
			unlink( $path );
		}

		return true;
	}
}