<?php
/**
 * Shipper models: filesystem path exclusions model
 *
 * @package shipper
 */

/**
 * Blaclist model class
 */
class Shipper_Model_Fs_Blacklist {

	/**
	 * Holds a list of blacklisted files.
	 *
	 * These are full paths.
	 * These have to be an exact match to be omitted.
	 *
	 * @var array
	 */
	private $_files = array();

	/**
	 * Holds a list of blacklisted directories.
	 *
	 * These are full paths.
	 * These need to be a partial match to be omitted.
	 *
	 * @var array
	 */
	private $_dirs = array();

	/**
	 * Constructor
	 *
	 * Also sets up default exclusions list
	 */
	public function __construct() {
		$this->_files = $this->get_default_file_exclusions();
		$this->_dirs = $this->get_default_directory_exclusions();
	}

	/**
	 * Gets a list of directory full paths
	 *
	 * @return array
	 */
	public function get_directories() {
		return (array) $this->_dirs;
	}

	/**
	 * Gets a list of file exclusions (full paths)
	 *
	 * @return array
	 */
	public function get_files() {
		return (array) $this->_files;
	}

	/**
	 * Gets the file exclusions that should be there always
	 *
	 * @return array
	 */
	public function get_default_file_exclusions() {
		$mdl = new Shipper_Model_Stored_Exclusions;
		$exclusions = array_merge( array_keys( $mdl->get_data() ), array(
			// WordPress general.
			trailingslashit( ABSPATH ) . 'wp-activate.php',
			trailingslashit( ABSPATH ) . 'wp-blog-header.php',
			trailingslashit( ABSPATH ) . 'wp-comments-post.php',
			trailingslashit( ABSPATH ) . 'wp-config-sample.php',
			trailingslashit( ABSPATH ) . 'wp-cron.php',
			trailingslashit( ABSPATH ) . 'wp-links-opml.php',
			trailingslashit( ABSPATH ) . 'wp-load.php',
			trailingslashit( ABSPATH ) . 'wp-login.php',
			trailingslashit( ABSPATH ) . 'wp-mail.php',
			trailingslashit( ABSPATH ) . 'wp-settings.php',
			trailingslashit( ABSPATH ) . 'wp-signup.php',
			trailingslashit( ABSPATH ) . 'wp-trackback.php',
			trailingslashit( ABSPATH ) . 'xmlrpc.php',

			// WP Engine specific!
			trailingslashit( WP_CONTENT_DIR ) . 'mysql.sql',
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/mu-plugin.php',
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/slt-force-strong-passwords.php',
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/stop-long-comments.php',

			// GoDaddy specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/gd-system-plugin.php',

			// Kinsta specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/kinsta-mu-plugins.php',

			// Flywheel specific.
			trailingslashit( ABSPATH ) . '.fw-config.php',

			// EasyWP specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wp-nc-easywp.php',

			// Bluehost specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/endurance-browser-cache.php',

			// iThemes specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/cs-cache-enabler.php',
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/cs-filters-and-actions.php',

		) );
		if ( Shipper_Model_Env::is_flywheel() ) {
			// Flywheel does weird stuff with wp-config.
			$exclusions[] = trailingslashit( ABSPATH ) . 'wp-config.php';
		}

		return $exclusions;
	}

	/**
	 * Gets the directory exclusions that should be there always
	 *
	 * @return array
	 */
	public function get_default_directory_exclusions() {
		return array(
			// WordPress general.
			trailingslashit( ABSPATH ) . WPINC,
			trailingslashit( ABSPATH ) . 'wp-admin/',

			// Snapshot-specific.
			'_restore/_imports/',

			// Ourselves too.
			trailingslashit( dirname( SHIPPER_PLUGIN_FILE ) ),

			// Well-known.
			trailingslashit( ABSPATH ) . '.well-known/',
			// Caches.
			trailingslashit( WP_CONTENT_DIR ) . 'cache/',

			// WP Engine specific!
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/force-strong-passwords/',
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wpengine-common/',

			// SiteGround-specific.
			trailingslashit( WP_CONTENT_DIR ) . 'plugins/sg-cachepress/',

			// GoDaddy-specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/gd-system-plugin/',

			// Kinsta specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/kinsta-mu-plugins/',

			// EasyWP specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wp-nc-easywp/',

			// iThemes specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/cs-cache-enabler/',
		);
	}

	/**
	 * Add a directory to the exclusion list
	 *
	 * @param string $path Full path to the excluded directory.
	 */
	public function add_directory( $path ) {
		$this->_dirs[] = wp_normalize_path( $path );
	}

	/**
	 * Add a file to the exclusion list
	 *
	 * @param string $path Full path to the excluded file.
	 */
	public function add_file( $path ) {
		$this->_files[] = wp_normalize_path( $path );
	}

	/**
	 * Checks to see whether a path is in an excluded directory
	 *
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public function is_in_directory( $path ) {
		$path = trailingslashit( wp_normalize_path( $path ) );
		$result = false;

		foreach ( $this->get_directories() as $exclusion ) {
			$result = (bool) stristr( $path, $exclusion );
			if ( ! empty( $result ) ) {
				break;
			}
		}

		return (bool) $result;
	}

	/**
	 * Checks to see whether a path matches an excluded file.
	 *
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public function is_excluded_file( $path ) {
		$path = wp_normalize_path( $path );
		$result = false;

		foreach ( $this->get_files() as $file ) {
			$result = strtolower( $path ) === strtolower( $file );
			if ( ! empty( $result ) ) {
				break;
			}
		}

		return (bool) $result;
	}

	/**
	 * Checks whether a path is excluded.
	 *
	 * This can be either because it's in the excluded directory,
	 * or because it is a directly excluded path.
	 *
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public function is_excluded( $path ) {
		if ( $this->is_in_directory( $path ) ) {
			return true;
		}
		return $this->is_excluded_file( $path );
	}
}