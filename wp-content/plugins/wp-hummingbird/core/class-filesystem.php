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
		 * If filesystem is ok.
		 *
		 * @since  1.6.0
		 * @access private
		 * @var bool $status
		 */
		public $status = false;

		/**
		 * Gravatar cache directory.
		 *
		 * @since 1.7.0
		 * @var string $gravatar_dir
		 */
		public $gravatar_dir;

		/**
		 * Page cache directory.
		 *
		 * @since 1.7.0
		 * @var string
		 */
		public $cache_dir;

		/**
		 * Base url for Gravatar links.
		 *
		 * @since 1.6.0
		 * @var string $baseurl
		 */
		public $baseurl;

		/**
		 * WP_Hummingbird_Filesystem singleton instance.
		 *
		 * @since  1.6.0
		 * @access private
		 * @var WP_Hummingbird_Filesystem $_instance
		 */
		private static $instance;

		/**
		 * Base dir for files.
		 *
		 * @since 1.6.0
		 * @since 1.7.0 changed from private to public
		 * @var string $basedir
		 */
		public $basedir;

		/**
		 * Stores the domain of the site in multisite network.
		 *
		 * @since  1.7.0
		 * @access private
		 * @var string $site
		 */
		private $site;

		/**
		 * Use WP_Filesystem API.
		 *
		 * @since 1.7.2
		 * @var bool $fs_api
		 */
		private $fs_api = false;

		/**
		 * WP_Hummingbird_Filesystem constructor.
		 *
		 * Initiate file system for read/write operations.
		 *
		 * @since  1.6.0
		 * @access private
		 */
		private function __construct() {
			$this->status  = $this->init_fs();

			if ( is_multisite() ) {
				$blog = get_blog_details();

				if ( '/' === $blog->path ) {
					$this->site = trailingslashit( $blog->domain );
				} else {
					$this->site = $blog->path;
				}
			}

			if ( ! defined( 'WP_CONTENT_DIR' ) ) {
				define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
			}

			// TODO: refactor code to only use basedir instead of gravatar_dir and cache_dir.
			$this->basedir      = WP_CONTENT_DIR . '/wphb-cache/';
			$this->gravatar_dir = WP_CONTENT_DIR . '/wphb-cache/gravatar/';
			$this->cache_dir    = WP_CONTENT_DIR . '/wphb-cache/cache/';
			$this->baseurl      = trailingslashit( content_url() ) . 'wphb-cache/gravatar/';
		}

		/**
		 * Get WP_Hummingbird_Filesystem singleton instance.
		 *
		 * @since  1.6.0
		 * @return WP_Hummingbird_Filesystem
		 */
		public static function instance() {
			if ( ! is_object( self::$instance ) ) {
				self::$instance = new WP_Hummingbird_Filesystem();
			}

			return self::$instance;
		}

		/**
		 * Initiate file system for read/write operations
		 *
		 * @since  1.6.0
		 *
		 * @return bool|WP_Error  Return true if everything is ok.
		 */
		private function init_fs() {
			// Need to include file.php for frontend.
			if ( ! function_exists( 'request_filesystem_credentials' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			// Removes CRITICAL Uncaught Error: Call to undefined function submit_button() in wp-admin/includes/file.php:1287
			require_once( ABSPATH . 'wp-admin/includes/template.php' );

			// Check if the user has write permissions.
			$access_type = get_filesystem_method();
			if ( 'direct' === $access_type ) {
				$this->fs_api = true;

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
				$this->fs_api = false;
			}

			// Can not write to wp-content directory.
			if ( defined( WP_CONTENT_DIR ) && ! is_writeable( WP_CONTENT_DIR ) ) {
				return new WP_Error( 'fs-error', __( 'Error: The wp-content directory is not writable. Ensure the folder has proper read/write permissions for caching to function successfully.', 'wphb' ) );
			}

			return true;
		}

		/**
		 * Native php directory removal (used when WP_Filesystem is not available);
		 *
		 * @since  1.7.2
		 *
		 * @access private
		 * @param  string $path
		 *
		 * @return bool
		 */
		private function native_dir_delete( $path ) {
			if ( is_wp_error( $this->status ) ) {
				return false;
			}

			// Use direct filesystem php functions.
			$dir = @opendir( $path );

			while ( false !== ( $file = readdir( $dir ) ) ) {
				if ( ( '.' == $file ) || ( '..' == $file ) ) {
					return false;
				}

				$full = $path . '/' . $file;
				if ( is_dir( $full ) ) {
					@rmdir( $full );
				} else {
					@unlink( $full );
				}
			}

			closedir( $dir );
			@rmdir( $path );

			return true;
		}

		/**
		 * Delete everything in selected folder.
		 *
		 * @since  1.6.0
		 * @since  1.7.2  Added if $this->fs_api check.
		 *
		 * @param  string $dir  Directory in wp-content/wphb-cache/ to purge file from.
		 *
		 * @return bool
		 */
		public function purge( $dir ) {
			if ( is_wp_error( $this->status ) ) {
				return false;
			}

			if ( $dir ) {
				$dir = trailingslashit( $dir );
			}

			$path = $this->basedir . $dir;

			// If directory not found - exit.
			if ( ! is_dir( $path ) ) {
				return true;
			}

			// Use WP_Filesystem API to delete files.
			if ( $this->fs_api ) {
				/* @var WP_Filesystem_Base $wp_filesystem */
				global $wp_filesystem;

				// Delete all content inside the directory.
				foreach ( $wp_filesystem->dirlist( $path ) as $asset ) {
					if ( ! $wp_filesystem->delete( $path . $asset['name'], true, $asset['type'] ) ) {
						return false;
					}
				}

				// Delete the directory itself.
				if ( ! $wp_filesystem->delete( $path ) ) {
					return false;
				}
			} else {
				// Use direct filesystem php functions.
				if ( ! $this->native_dir_delete( $path ) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Clean up during uninstall.
		 *
		 * @since  1.6.0
		 * @since  1.7.2  Added if $this->fs_api check.
		 *
		 * @return bool
		 */
		public function clean_up() {
			if ( is_wp_error( $this->status ) ) {
				return false;
			}

			// Use WP_Filesystem API.
			if ( $this->fs_api ) {
				/* @var WP_Filesystem_Base $wp_filesystem */
				global $wp_filesystem;

				if ( ! $wp_filesystem->delete( $this->basedir, true ) ) {
					return false;
				}
			} else {
				// Use direct filesystem php functions.
				if ( ! $this->native_dir_delete( $this->basedir ) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Find file in the filesystem.
		 *
		 * @since  1.6.0
		 * @since  1.7.2  Added if $this->fs_api check.
		 *
		 * @param  string $file      File to find.
		 * @param  bool   $gravatar  To search for gravatar or page cache.
		 *
		 * @return bool
		 */
		public function find( $file, $gravatar = false ) {
			if ( is_wp_error( $this->status ) ) {
				return false;
			}

			$path = $this->cache_dir . $this->site;
			if ( $gravatar ) {
				// If Gravatar cache, we need to use first three letters of hash as a directory.
				$hash = trailingslashit( substr( $file, 0, 3 ) );
				$path = $this->gravatar_dir . $hash;
			}

			// Use WP_Filesystem API.
			if ( $this->fs_api ) {
				/* @var WP_Filesystem_Base $wp_filesystem */
				global $wp_filesystem;
				return $wp_filesystem->exists( $path . $file );
			} else {
				// Use direct filesystem php functions.
				return file_exists( $path . $file );
			}
		}

		/**
		 * Write file to selected folder.
		 *
		 * @since  1.6.0
		 * @since  1.7.2  Added if $this->fs_api check.
		 *
		 * @param  string $file      Name of the file.
		 * @param  string $content   File contents.
		 * @param  bool   $gravatar  To search for gravatar or page cache.
		 *
		 * @return bool|WP_Error
		 */
		public function write( $file, $content = '', $gravatar = false ) {
			if ( is_wp_error( $this->status ) ) {
				return false;
			}

			// Determine path for Gravatar module.
			if ( $gravatar ) {
				// If Gravatar cache, we need to use first three letters of hash as a directory.
				$hash = '';
				// No need for a hash if we're just adding a blank index.html file.
				if ( 'index.html' !== $file ) {
					$hash = trailingslashit( substr( $file, 0, 3 ) );
				}

				$path = $this->gravatar_dir . $hash;
			} else {
				// Determin path for page caching module.
				$path = trailingslashit( dirname( $file ) );
				// Remove directory from file.
				$file = basename( $file );
			}

			// Use WP_Filesystem API.
			if ( $this->fs_api ) {
				/* @var WP_Filesystem_Base $wp_filesystem */
				global $wp_filesystem;

				// Check if cache folder exists. If not - create it.
				if ( ! $wp_filesystem->exists( $path ) ) {
					if ( ! @wp_mkdir_p( $path ) ) {
						return new WP_Error( 'fs-dir-error', sprintf(
							/* translators: %s: directory */
							__( 'Error creating directory %s.', 'wphb' ),
							esc_html( $path )
						) );
					}
				}

				// Create the file.
				if ( ! $wp_filesystem->put_contents( $path . $file, $content, FS_CHMOD_FILE ) ) {
					return new WP_Error( 'fs-file-error', sprintf(
						/* translators: %s: file */
						__( 'Error uploading file %s.', 'wphb' ),
						esc_html( $file )
					) );
				}
			} else {
				// Use direct filesystem php functions.

				// Check if cache folder exists. If not - create it.
				if ( ! is_dir( $path ) ) {
					if ( ! @wp_mkdir_p( $path ) ) {
						return new WP_Error( 'fs-dir-error', sprintf(
							/* translators: %s: directory */
							__( 'Error creating directory %s.', 'wphb' ),
							esc_html( $path )
						) );
					}
				}

				// Create the file.
				$file = fopen( $path . $file, 'w' );
				if ( ! fwrite( $file, $content ) ) {
					return new WP_Error( 'fs-file-error', sprintf(
						/* translators: %s: file */
						__( 'Error uploading file %s.', 'wphb' ),
						esc_html( $file )
					) );
				} elseif ( $file ) {
					fclose( $file );
				}
			} // End if().

			return true;
		}

	}
} // End if().