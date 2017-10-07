<?php
/**
 * Filesystem class.
 *
 * @package Hummingbird
 * @author: WPMUDEV, Ignacio Cruz (igmoweb), Anton Vanyukov (vanyukov)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Hummingbird_Filesystem' ) ) {
	/**
	 * Singleton class WP_Hummingbird_Filesystem.
	 *
	 * Manages the file system actions for caching modules.
	 *
	 * @since 1.6.0
	 */
	class WP_Hummingbird_Filesystem {

		/**
		 * WP_Hummingbird_Filesystem singleton instance.
		 *
		 * @since  1.6.0
		 * @access private
		 * @var WP_Hummingbird_Filesystem $_instance
		 */
		private static $instance;

		/**
		 * If filesystem is ok.
		 *
		 * @since  1.6.0
		 * @access private
		 * @var bool $status
		 */
		public $status = false;

		/**
		 * Base dir for files.
		 *
		 * @since  1.6.0
		 * @access private
		 * @var string $basedir
		 */
		private $basedir;

		/**
		 * Base url for links.
		 *
		 * @since 1.6.0
		 * @var string $baseurl
		 */
		public $baseurl;

		/**
		 * Stores path to module directory.
		 *
		 * Used when counting number of cached gravatars or pages for all sites, during cache purge.
		 * Usefull on multisite installs, because removes the need to look for it later on in code.
		 *
		 * @since  1.6.0
		 * @access private
		 * @var string $dir
		 */
		private $dir;

		/**
		 * Cache type.
		 *
		 * @since  1.6.0
		 * @access private
		 * @var string $type
		 */
		private static $type;

		/**
		 * WP_Hummingbird_Filesystem constructor.
		 *
		 * Initiate file system for read/write operations.
		 *
		 * @since  1.6.0
		 * @param  string $type  Type of fs to init.
		 * @access private
		 */
		private function __construct( $type ) {
			$this->status  = $this->init_fs();
			self::$type = $type;

			$site = '/';
			// Do not use per-site folder structure for Gravatar cache.
			if ( is_multisite() && ! self::is_gravatar_cache() ) {
				$blog = get_blog_details();

				if ( '/' === $blog->path ) {
					$site = $site . trailingslashit( $blog->domain );
				} else {
					$site = $blog->path;
				}
			}

			$this->basedir = WP_CONTENT_DIR . '/wphb-cache/' . $type . $site;
			$this->dir = WP_CONTENT_DIR . '/wphb-cache/' . trailingslashit( $type );
			$this->baseurl = trailingslashit( content_url() ) . 'wphb-cache/' . $type . $site;
		}

		/**
		 * Get WP_Hummingbird_Filesystem singleton instance.
		 *
		 * @since  1.6.0
		 * @param  string $type  Type of fs to init. Available: gravatar, page.
		 *                       Default will init the top cache folder.
		 * @return WP_Hummingbird_Filesystem
		 */
		public static function instance( $type = '' ) {
			if ( ! is_object( self::$instance ) ) {
				self::$instance = new WP_Hummingbird_Filesystem( $type );
			}

			return self::$instance;
		}

		/**
		 * Check if using Gravatar cache. Useful when choosing directory structure.
		 *
		 * @since  1.6.0
		 * @access private
		 * @return bool     Return true if using Gravatar cache.
		 */
		private static function is_gravatar_cache() {
			if ( 'gravatar' === self::$type ) {
				return true;
			}
			return false;
		}

		/**
		 * Initiate file system for read/write operations
		 *
		 * @since  1.6.0
		 * @return bool|WP_Error  Return true if everything is ok.
		 */
		private function init_fs() {
			// Need to include file.php for frontend.
			if ( ! function_exists( 'request_filesystem_credentials' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			// Check if the user has write permissions.
			$access_type = get_filesystem_method();
			if ( 'direct' === $access_type ) {
				// You can safely run request_filesystem_credentials() without any issues
				// and don't need to worry about passing in a URL.
				$credentials = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, null );

				// Initialize the Filesystem API.
				if ( ! WP_Filesystem( $credentials ) ) {
					// Some problems, exit.
					return new WP_Error( 'fs-error', __( 'Error: Unexpected error while writing a file. Please view error log for more information.', 'wphb' ) );
				}
			} else {
				// Don't have direct write access.
				return new WP_Error( 'fs-error', __( 'Error: The wp-content directory is not writable. Ensure the folder has proper read/write permissions for caching to function sucesfully.', 'wphb' ) );
			}

			return true;
		}

		/**
		 * List files in a directory.
		 *
		 * @since  1.6.0
		 * @return array|bool      Return list of directory.
		 */
		public function dirlist() {
			if ( is_wp_error( $this->status ) ) {
				return false;
			}

			/* @var WP_Filesystem_Base $wp_filesystem */
			global $wp_filesystem;

			return $wp_filesystem->dirlist( $this->dir, false, true );
		}

		/**
		 * Delete everything in selected folder.
		 *
		 * @since  1.6.0
		 * @return bool
		 */
		public function purge() {
			if ( is_wp_error( $this->status ) ) {
				return false;
			}

			/* @var WP_Filesystem_Base $wp_filesystem */
			global $wp_filesystem;

			foreach ( $wp_filesystem->dirlist( $this->dir ) as $asset ) {
				if ( ! $wp_filesystem->delete( $this->dir . $asset['name'], true, $asset['type'] ) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Clean up during uninstall.
		 *
		 * @since  1.6.0
		 * @return bool
		 */
		public function clean_up() {
			if ( is_wp_error( $this->status ) ) {
				return false;
			}

			/* @var WP_Filesystem_Base $wp_filesystem */
			global $wp_filesystem;

			if ( ! $wp_filesystem->delete( $this->dir, true ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Find file in the filesystem.
		 *
		 * @since  1.6.0
		 * @param  string $file  File to find.
		 * @return bool
		 */
		public function find( $file ) {
			if ( is_wp_error( $this->status ) ) {
				return false;
			}

			/* @var WP_Filesystem_Base $wp_filesystem */
			global $wp_filesystem;

			// If Gravatar cache, we need to use first three letters of hash as a directory.
			$gravatar_dir = '';
			if ( self::is_gravatar_cache() ) {
				$gravatar_dir = trailingslashit( substr( $file, 0, 3 ) );
			}

			return $wp_filesystem->exists( $this->basedir . $gravatar_dir . $file );
		}

		/**
		 * Write file to selected folder.
		 *
		 * @since  1.6.0
		 * @param  string $file     Name of the file.
		 * @param  string $content  File contents.
		 * @return bool|WP_Error
		 */
		public function write( $file, $content ) {
			if ( is_wp_error( $this->status ) ) {
				return false;
			}

			/* @var WP_Filesystem_Base $wp_filesystem */
			global $wp_filesystem;

			// If Gravatar cache, we need to use first three letters of hash as a directory.
			$gravatar_dir = '';
			if ( self::is_gravatar_cache() ) {
				$gravatar_dir = trailingslashit( substr( $file, 0, 3 ) );
			}

			// Check if cache folder exists. If not - create it.
			if ( ! $wp_filesystem->exists( $this->basedir . $gravatar_dir ) ) {
				if ( ! wp_mkdir_p( $this->basedir . $gravatar_dir ) ) {
					return new WP_Error( 'fs-dir-error', sprintf(
						/* translators: %s: directory */
						__( 'Error creating directory %s.', 'wphb' ),
						esc_html( $this->basedir . $gravatar_dir )
					));
				}
			}

			// Create the file.
			if ( ! $wp_filesystem->put_contents( $this->basedir . $gravatar_dir . $file, $content, FS_CHMOD_FILE ) ) {
				return new WP_Error( 'fs-file-error', sprintf(
					/* translators: %s: file */
					__( 'Error uploading file %s.', 'wphb' ),
					esc_html( $file )
				));
			}

			return true;
		}

	}
} // End if().