<?php
/**
 * Shipper models: WordPress system info
 *
 * @package shipper
 */

/**
 * Server info model class
 */
class Shipper_Model_System_Wp extends Shipper_Model {

	const VERSION     = 'version';
	const DIR_CONTENT = 'WP_CONTENT_DIR';
	const DIR_PLUGINS = 'WP_PLUGIN_DIR';
	const DIR_UPLOADS = 'UPLOADS';
	const MULTISITE   = 'MULTISITE';
	const SUBDOMAIN   = 'SUBDOMAIN_INSTALL';
	const MS_SUBSITES = 'MS_SUBSITES';

	const SHIPPER_VERSION = 'shipper_version';

	/**
	 * Constructor
	 *
	 * Populates internal data structure
	 */
	public function __construct() {
		$this->populate();
	}

	/**
	 * Populates internal data structure
	 */
	public function populate() {
		global $wp_version;

		$this->set_data(
			array(
				self::DIR_CONTENT => Shipper_Helper_Fs_Path::get_relpath( $this->get_define( self::DIR_CONTENT ) ),
				self::DIR_PLUGINS => Shipper_Helper_Fs_Path::get_relpath( $this->get_define( self::DIR_PLUGINS ) ),
				self::MULTISITE   => $this->get_define( self::MULTISITE ),
				self::SUBDOMAIN   => $this->get_define( self::SUBDOMAIN ),
			)
		);
		$this->set( self::VERSION, $wp_version );
		$this->set( self::SHIPPER_VERSION, SHIPPER_VERSION );

		$uploads = wp_upload_dir();
		$this->set( self::DIR_UPLOADS, Shipper_Helper_Fs_Path::get_relpath( $uploads['basedir'] ) );
		$this->set( self::MS_SUBSITES, $this->get_ms_sites() );
	}

	/**
	 * Gets define value
	 *
	 * @param string $dfn Define name.
	 *
	 * @return mixed
	 */
	public function get_define( $dfn ) {
		if ( ! defined( $dfn ) ) {
			return false;
		}

		return constant( $dfn );
	}

	/**
	 * Get value formatted nicely for output
	 *
	 * @param string $key Value key.
	 * @param mixed  $fallback What to use as fallback.
	 *
	 * @return string
	 */
	public function get_output_value( $key, $fallback = false ) {
		switch ( $key ) {
			case self::MULTISITE:
			case self::SUBDOMAIN:
				return $this->get( $key, $fallback ) ? __( 'Yes', 'shipper' ) : __( 'No', 'shipper' );
		}

		return $this->get( $key, $fallback );
	}

	/**
	 * If this is multisite, we need to gather subsite info so we can fetch import
	 *
	 * @return array
	 */
	public function get_ms_sites() {
		if ( ! is_multisite() ) {
			return array();
		}

		$sites = Shipper_Helper_MS::get_all_sites();

		return $sites;
	}
}