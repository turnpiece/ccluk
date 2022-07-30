<?php
/**
 * Shipper helpers: path helper
 *
 * Most significantly, determines file type.
 * Also does some other general path-related utility stuff.
 *
 * @package shipper
 */

/**
 * File helper class
 */
class Shipper_Helper_Fs_Path {

	/**
	 * Relativizes an (absolute) path
	 *
	 * @param string $path Path to relativize.
	 * @param string $root Optional root to use for relativization (defaults to ABSPATH).
	 *
	 * @return string
	 */
	public static function get_relpath( $path, $root = false ) {
		$path = preg_replace( '/\.\./', '', $path ); // Never allow up-directory references.
		$root = ! empty( $root )
			? wp_normalize_path( trailingslashit( $root ) )
			: trailingslashit( ABSPATH );

		$relpath = preg_replace(
			'/^' . preg_quote( $root, '/' ) . '/',
			'',
			wp_normalize_path( $path )
		);
		if ( ! self::is_relpath( $relpath ) ) {
			$relpath = ltrim( wp_normalize_path( $relpath ), '/' );
		}

		return wp_normalize_path( $relpath );
	}

	/**
	 * Checks whether a path is relative
	 *
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public static function is_relpath( $path ) {
		return ! preg_match( '/^\//', wp_normalize_path( $path ) );
	}

	/**
	 * Convert relative path to an absolute one
	 *
	 * Absolute paths are always ABSPATH-relative.
	 *
	 * @param string $relpath Path to resolve.
	 *
	 * @return string
	 */
	public static function get_abspath( $relpath ) {
		$relpath = self::get_relpath( $relpath );

		return trailingslashit( ABSPATH ) . $relpath;
	}

	/**
	 * Cleans filename
	 *
	 * @param string $raw Raw string filename.
	 *
	 * @return string
	 */
	public static function clean_fname( $raw ) {
		$fname = preg_replace( '/\.\./', '', $raw ); // Never allow up-directory references.
		$fname = preg_replace( '/\//', '', $fname ); // Never allow child directories in destination.

		// Fix fname being an IP address.
		// This is so we don't hit remote naming restriction policy.
		if ( preg_match( '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $fname ) ) {
			$fname = preg_replace(
				'/(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})/',
				'$1-$2-$3-$4',
				$fname
			);
		}

		return $fname;
	}

	/**
	 * Checks whether a file is a config (i.e. changeable in migration) file
	 *
	 * @param string $path Absolute path to a file to check.
	 *
	 * @return bool
	 */
	public static function is_config_file( $path ) {
		$configs = array(
			'wp-config.php',
			'wp-tests-config.php',
			'.htaccess',
			'php.ini',
			'.user.ini',
			'wordfence-waf.php',
		);

		return in_array( basename( $path ), $configs, true );
	}

	/**
	 * Checks whether a file is a wp-config.php file
	 *
	 * @param string $path Absolute path to a file to check.
	 *
	 * @return bool
	 */
	public static function is_wp_config( $path ) {
		if ( 'wp-tests-config.php' === basename( $path ) ) {
			return true;
		}

		return 'wp-config.php' === basename( $path ) && trailingslashit( ABSPATH ) === trailingslashit( dirname( $path ) );
	}

	/**
	 * Check whether we're dealing with a plugin file
	 *
	 * @param string $abspath Absolute path to a file.
	 *
	 * @return bool
	 */
	public static function is_plugin_file( $abspath ) {
		$active_rx = preg_quote( WP_PLUGIN_DIR, '/' );

		return (bool) preg_match( "/^{$active_rx}/", $abspath );
	}

	/**
	 * Check whether we're dealing with a "must-use" plugin file
	 *
	 * @param string $abspath Absolute path to a file.
	 *
	 * @return bool
	 */
	public static function is_muplugin_file( $abspath ) {
		$active_rx = preg_quote( WPMU_PLUGIN_DIR, '/' );

		return (bool) preg_match( "/^{$active_rx}/", $abspath );
	}

	/**
	 * Check whether we're dealing with a theme file
	 *
	 * @param string $abspath Absolute path to a file.
	 *
	 * @return bool
	 */
	public static function is_theme_file( $abspath ) {
		$active_rx = preg_quote( trailingslashit( WP_CONTENT_DIR ) . 'themes', '/' );

		return (bool) preg_match( "/^{$active_rx}/", $abspath );
	}

	/**
	 * Check whether we're dealing with an active file
	 *
	 * An "active file" is a file that belongs to a plugin ("must-use" or
	 * regular). Also, object caching drop-in.
	 * We are making this distinction because one or more of these might belong
	 * to an active plugin, and we have to move those as one unit.
	 *
	 * @param string $abspath Absolute path to a file.
	 *
	 * @return bool
	 */
	public static function is_active_file( $abspath ) {
		$object_cache_rx = preg_quote( trailingslashit( WP_CONTENT_DIR ), '/' ) . preg_quote( 'object-cache.php', '/' );

		if ( preg_match( "/{$object_cache_rx}$/", $abspath ) ) {
			return true;
		}

		return self::is_plugin_file( $abspath ) || self::is_muplugin_file( $abspath ) || self::is_theme_file( $abspath );
	}

	/**
	 * Gets absolute path to Shipper working directory
	 *
	 * Will create the directory if it doesn't exist, as a side-effect.
	 *
	 * @return string
	 */
	public static function get_working_dir() {
		/**
		 * Gets the working directory root path
		 *
		 * Defaults to system temp directory, or best guesstimate.
		 *
		 * @param string $root_path Temporary working directory.
		 *
		 * @return string
		 */
		$base_root   = apply_filters(
			'shipper_paths_working_dir_root',
			get_temp_dir()
		);
		$root        = trailingslashit( $base_root );
		$shipper_dir = $root . shipper_get_site_uniqid( 'shipper' );

		if ( ! is_dir( $shipper_dir ) ) {
			wp_mkdir_p( $shipper_dir );
			if ( shipper_is_dir_visible( $shipper_dir ) ) {
				// Ouch... so this sucks.
				// Our working directory is web-accessible.
				// Let's attempt at least some protection with .htaccess here.
				self::attempt_htaccess_protect( $shipper_dir );
			}
		}

		/**
		 * Gets the actual working directory path
		 *
		 * @param string $shipper_dir Shipper working directory path.
		 *
		 * @return string
		 */
		$shipper_dir = apply_filters(
			'shipper_paths_working_dir',
			$shipper_dir
		);

		return trailingslashit( $shipper_dir );
	}

	/**
	 * Gets absolute path to Shipper temporary directory
	 *
	 * Will create the directory if it doesn't exist, as a side-effect.
	 *
	 * @return string
	 */
	public static function get_temp_dir() {
		$root        = self::get_working_dir();
		$shipper_dir = "{$root}tmp";

		if ( ! is_dir( $shipper_dir ) ) {
			wp_mkdir_p( $shipper_dir );
		}

		return trailingslashit( $shipper_dir );
	}

	/**
	 * Get package extract dir.
	 *
	 * @return string
	 */
	public static function get_packageextract_dir() {
		return trailingslashit( self::get_temp_dir() . 'package' );
	}

	/**
	 * Gets the WP uploads directory path
	 *
	 * @return string
	 */
	public static function get_uploads_dir() {
		$uploads = wp_upload_dir();

		return trailingslashit( $uploads['basedir'] );
	}

	/**
	 * Gets absolute path to Shipper log directory
	 *
	 * As opposed to working/temp dirs, log directory is to be a
	 * permanent location, with files persisting across migrations.
	 *
	 * Will create the directory if it doesn't exist, as a side-effect.
	 *
	 * @return string
	 */
	public static function get_log_dir() {
		$root        = self::get_uploads_dir();
		$shipper_dir = "{$root}shipper";

		if ( ! is_dir( $shipper_dir ) ) {
			wp_mkdir_p( $shipper_dir );
			if ( shipper_is_dir_visible( $shipper_dir ) ) {
				// Our logs directory is web-accessible.
				// Let's attempt some protection with .htaccess here.
				self::attempt_htaccess_protect( $shipper_dir );
			}
		}

		return trailingslashit( $shipper_dir );
	}

	/**
	 * Gets absolute path to Shipper storage directory
	 *
	 * Will create the directory if it doesn't exist, as a side-effect.
	 *
	 * @return string
	 */
	public static function get_storage_dir() {
		$root        = self::get_working_dir();
		$shipper_dir = "{$root}storage";

		if ( ! is_dir( $shipper_dir ) ) {
			wp_mkdir_p( $shipper_dir );
		}

		return trailingslashit( $shipper_dir );
	}

	/**
	 * Attempt to protect a web-visible directory
	 *
	 * Places a .htaccess file into the directory, which will add
	 * at least some protection from information disclosure.
	 *
	 * @param string $directory Path to the directory to protect.
	 *
	 * @return bool
	 */
	public static function attempt_htaccess_protect( $directory ) {
		if ( empty( $directory ) ) {
			return false;
		}

		$directory = wp_normalize_path( trailingslashit( $directory ) );
		if ( ! is_writable( $directory ) ) {
			return false;
		}

		$lines = array(
			'Order deny,allow',
			'Deny from all',
			'Options -Indexes',
		);

		$fs = Shipper_Helper_Fs_File::open( "{$directory}.htaccess", 'w' );

		if ( ! $fs ) {
			return false;
		}

		return ! ! $fs->fwrite( join( "\n", $lines ) );
	}

	/**
	 * Recursively clean directory path
	 *
	 * @param string $path Path to clean up.
	 * @param string $previous Previous path.
	 *
	 * @return bool
	 */
	public static function rmdir_r( $path, $previous ) {
		$next    = ( ! empty( $previous ) ? trailingslashit( $previous ) : '' ) . basename( $path );
		$cleanup = shipper_glob_all( $path );
		$status  = true;
		foreach ( $cleanup as $file ) {
			if ( is_dir( $file ) ) {
				if ( ! self::rmdir_r( $file, $next ) ) {
					$status = false;
					continue; // Let's not break on errors here, keep on cleaning as much as we can.
				}
				if ( ! rmdir( $file ) ) {
					$status = false;
					continue; // Let's not break on errors here, keep on cleaning as much as we can.
				}
			} else {
				if ( ! is_writable( $file ) ) {
					$status = false;
					continue; // Let's not break on errors here, keep on cleaning as much as we can.
				}

				// Alright, drop it like it's hot.
				if ( ! shipper_delete_file( $file ) ) {
					$status = false;
					continue; // Let's not break on errors here, keep on cleaning as much as we can.
				}
			}
		}

		return $status;
	}
}