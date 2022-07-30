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
	private $files = array();

	/**
	 * Holds a list of blacklisted directories.
	 *
	 * These are full paths.
	 * These need to be a partial match to be omitted.
	 *
	 * @var array
	 */
	private $dirs = array();

	/**
	 * Constructor
	 *
	 * Also sets up default exclusions list
	 */
	public function __construct() {
		$this->files = $this->get_default_file_exclusions();
		$this->dirs  = $this->get_default_directory_exclusions();
	}

	/**
	 * Gets a list of directory full paths
	 *
	 * @return array
	 */
	public function get_directories() {
		return apply_filters( 'shipper_get_black_listed_directories', array_map( 'wp_normalize_path', (array) $this->dirs ) );
	}

	/**
	 * Gets a list of file exclusions (full paths)
	 *
	 * @return array
	 */
	public function get_files() {
		return apply_filters( 'shipper_get_black_listed_files', array_map( 'wp_normalize_path', (array) $this->files ) );
	}

	/**
	 * Check whether wp core is skipped or not
	 *
	 * @return bool
	 */
	public function is_skipping_wp_core() {
		return (bool) apply_filters(
			'shipper_blacklist_skip_wp_core',
			true
		);
	}

	/**
	 * Get WP core file exclusions
	 *
	 * @return array|string[]
	 */
	public function get_wp_core_file_exclusions() {
		if ( ! $this->is_skipping_wp_core() ) {
			return array();
		}

		return array(
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
		);
	}

	/**
	 * Gets the file exclusions that should be there always
	 *
	 * @return array
	 */
	public function get_default_file_exclusions() {
		$mdl        = new Shipper_Model_Stored_Exclusions();
		$exclusions = array_merge(
			array_keys( $mdl->get_data() ),
			$this->get_wp_core_file_exclusions(),
			array(
				trailingslashit( WP_CONTENT_DIR ) . 'debug.log',
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

				// WPMU DEV specific.
				trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wpmudev-hosting.php',
			)
		);
		if ( Shipper_Model_Env::is_flywheel() ) {
			// Flywheel does weird stuff with wp-config.
			$exclusions[] = trailingslashit( ABSPATH ) . 'wp-config.php';
		}
		if ( Shipper_Model_Env::is_wpmu_hosting() ) {
			// WPMU DEV hosting object-cache.php because it doesn't do much checks.
			$exclusions[] = trailingslashit( WP_CONTENT_DIR ) . 'object-cache.php';
		}

		return array_map( 'wp_normalize_path', $exclusions );
	}

	/**
	 * Get WP core directory exclusions
	 *
	 * @return array|string[]
	 */
	public function get_wp_core_directory_exclusions() {
		if ( ! $this->is_skipping_wp_core() ) {
			return array();
		}

		return array(
			// WordPress general.
			trailingslashit( ABSPATH ) . WPINC,
			trailingslashit( ABSPATH ) . 'wp-admin/',
		);
	}

	/**
	 * Gets the directory exclusions that should be there always
	 *
	 * @return array
	 */
	public function get_default_directory_exclusions() {
		$is_exclude_shipper = apply_filters( 'shipper_exclude_self_files', true );

		$ignore = array(
			// Snapshot-specific.
			'_restore/_imports/',

			// Exclude shipper-working directory.
			trailingslashit( ABSPATH ) . 'shipper-working/',

			// Hummingbird caches.
			trailingslashit( WP_CONTENT_DIR ) . 'wphb-cache/',

			// Ourselves too.
			$is_exclude_shipper ? trailingslashit( dirname( SHIPPER_PLUGIN_FILE ) ) : null,

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

			// WPMU DEV specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wpmudev-hosting/',
		);
		$ignore = array_filter( $ignore );

		return array_merge(
			$this->get_wp_core_directory_exclusions(),
			$ignore,
			$this->get_non_wp_directories(),
			$this->get_backup_dirs()
		);
	}

	/**
	 * Add a directory to the exclusion list
	 *
	 * @param string $path Full path to the excluded directory.
	 */
	public function add_directory( $path ) {
		$this->dirs[] = wp_normalize_path( $path );
	}

	/**
	 * Add a list of directories to blacklist
	 *
	 * @param array $paths An array of paths.
	 *
	 * @since 1.2.2
	 */
	public function add_directories( $paths ) {
		foreach ( $paths as $path ) {
			$this->add_directory( $path );
		}
	}

	/**
	 * Add a file to the exclusion list
	 *
	 * @param string $path Full path to the excluded file.
	 */
	public function add_file( $path ) {
		$this->files[] = wp_normalize_path( $path );
	}

	/**
	 * Add list of files to blacklist
	 *
	 * @since 1.2.2
	 *
	 * @param array $paths An array of paths.
	 */
	public function add_files( $paths ) {
		foreach ( $paths as $path ) {
			$this->add_file( $path );
		}
	}

	/**
	 * Checks to see whether a path is in an excluded directory
	 *
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public function is_in_directory( $path ) {
		$path   = trailingslashit( wp_normalize_path( $path ) );
		$result = false;

		foreach ( $this->get_directories() as $exclusion ) {
			$result = (bool) stristr( $path, (string) $exclusion );
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
		$path   = wp_normalize_path( $path );
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

	/**
	 * Get a list of non WP directories.
	 *
	 * There are cases, where there are multiple website hosted on a single account.
	 * So there are multiple WP instance and because of these, `pre-flight` check get stuck.
	 * So we'll add those directories to the blacklist.
	 *
	 * @since 1.2.0
	 *
	 * @see https://incsub.atlassian.net/browse/SHI-167
	 *
	 * @return array
	 */
	public function get_non_wp_directories() {
		$root    = trailingslashit( ABSPATH );
		$dirs    = glob( "{$root}*", GLOB_ONLYDIR );
		$wp_dirs = array( 'wp-admin', WPINC, basename( WP_CONTENT_DIR ) );

		$non_wp_dirs = array_filter(
			$dirs,
			function( $dir ) use ( $wp_dirs ) {
				return ! in_array( basename( $dir ), $wp_dirs, true );
			}
		);

		return apply_filters( 'shipper_get_non_wp_directories', $non_wp_dirs );
	}

	/**
	 * Get various backup dirs
	 *
	 * @since 1.2.4
	 *
	 * @return array
	 */
	public function get_backup_dirs() {
		return apply_filters(
			'shipper_get_backup_dirs',
			array(
				trailingslashit( WP_CONTENT_DIR ) . 'backups-dup-lite',
				trailingslashit( WP_CONTENT_DIR ) . 'ai1wm-backups',
				trailingslashit( WP_CONTENT_DIR ) . 'updraft',
			)
		);
	}
}