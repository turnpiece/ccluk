<?php
/**
 * Shipper models: PHP system info
 *
 * @package shipper
 */

/**
 * PHP info model class
 */
class Shipper_Model_System_Php extends Shipper_Model {

	const VERSION       = 'version';
	const VERSION_MAJOR = 'version_major';
	const TIMEOUT       = 'max_execution_time';
	const RESTRICTED    = 'open_basedir';
	const UPLOAD        = 'upload_max_filesize';
	const POSTSIZE      = 'post_max_size';
	const MEMORY        = 'memory_limit';
	const ZIP_SUPPORT   = 'zip_archive';
	const AWS_SUPPORT   = 'aws_support';
	const HAS_SUHOSIN   = 'has_suhosin';

	/**
	 * Constructor
	 *
	 * Populates internal data structure
	 */
	public function __construct() {
		// Call this first, to fill out the static vars.
		$this->has_conflicting_dependencies();

		Shipper_Helper_System::optimize(); // Give it our best shot at optimization first.
		$this->populate();
	}

	/**
	 * Populates internal data structure
	 */
	public function populate() {
		$this->set_data(
			array(
				self::TIMEOUT     => (int) ini_get( self::TIMEOUT ),
				self::RESTRICTED  => ! ! ini_get( self::RESTRICTED ),
				self::UPLOAD      => $this->get_ini_bytes( ini_get( self::UPLOAD ) ),
				self::POSTSIZE    => $this->get_ini_bytes( ini_get( self::POSTSIZE ) ),
				self::MEMORY      => (int) ini_get( self::MEMORY ) * 1024 * 1024,
				self::ZIP_SUPPORT => ! ! class_exists( 'ZipArchive' ),
			)
		);

		$this->set( self::VERSION, phpversion() );

		// We know this define doesn't exist in older PHP versions.
		// That's why we're checking for it in the first place.
		// So, silence the sniffer.
		// @codingStandardsIgnoreStart
		$major = defined( 'PHP_MAJOR_VERSION' )
			? PHP_MAJOR_VERSION
			: 4 // Prior to v5.2.7.
		;
		// @codingStandardsIgnoreEnd

		$this->set( self::VERSION_MAJOR, $major );

		$has_suhosin = false;
		if ( function_exists( 'extension_loaded' ) ) {
			$has_suhosin = extension_loaded( 'suhosin' );
		}
		$this->set( self::HAS_SUHOSIN, $has_suhosin );

		$this->set( self::AWS_SUPPORT, $this->get_aws_support_level() );
	}


	/**
	 * Convert PHP ini notation to byte values
	 *
	 * Straight from the manual: http://php.net/manual/en/function.ini-get.php
	 *
	 * @param string $val Size value in ini shorthand notation.
	 *
	 * @return int|string
	 */
	public function get_ini_bytes( $val ) {
		$val   = trim( $val );
		$test  = strtolower( $val );
		$units = array(
			'g' => 1073741824,
			'm' => 1048576,
			'k' => 1024,
		);

		foreach ( $units as $base => $mult ) {
			$unit = strrchr( $test, $base );
			if ( false === $unit ) {
				continue;
			}
			if ( 2 === strlen( $unit ) ) {
				$unit = $base;
			}

			return (int) $val * $mult;
		}

		return $val;
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
			case self::RESTRICTED:
			case self::ZIP_SUPPORT:
				return ! ! $this->get( $key, $fallback ) ? __( 'Yes', 'shipper' ) : __( 'No', 'shipper' );

			case self::MEMORY:
			case self::UPLOAD:
			case self::POSTSIZE:
				return size_format( $this->get( $key, $fallback ) );
		}

		return $this->get( $key, $fallback );
	}

	/**
	 * Checks whether we have our preferred SDK version supported
	 *
	 * Checks Suhosin extension presence, S3 client presence and AWS SDK version.
	 *
	 * @return bool
	 */
	public function get_aws_support_level() {
		if ( version_compare( phpversion(), '7.4', '<' ) ) {
			// Too old PHP.
			return false;
		}
		return $this->has_proper_aws_sdk_version() &&
			$this->has_aws_s3_client();
	}

	/**
	 * Checks if dependencies other than actual AWS SDK have already been loaded.
	 *
	 * Most notably, GuzzleHttp.
	 * Uses static variables and is being called right from the constructor.
	 * This is because the dependencies can get loaded already (by us) in other checks.
	 *
	 * @deprecated since v1.0.3 - not needed anymore since we're not using Phar
	 *
	 * @return bool
	 */
	public function has_conflicting_dependencies() {
		return false;
	}

	/**
	 * Check whether we have proper AWS SDK version
	 *
	 * Returns false for older (2.x) SDK libraries.
	 *
	 * @return bool
	 */
	public function has_proper_aws_sdk_version() {
		if ( class_exists( 'Aws\Common\Aws' ) ) {
			// This is old SDK and should be enough to barf.
			// Still, let's do the right thing and actually compare versions.
			return (bool) version_compare(
				Aws\Common\Aws::VERSION,
				'3.0.0',
				'gte'
			);
		}
		return true;
	}

	/**
	 * Check if we have (and can instantiate) an S3 client
	 *
	 * @return bool
	 */
	public function has_aws_s3_client() {
		if ( ! class_exists( 'Aws\S3\S3Client' ) ) {
			// Require external SDK just in time for this.
			require_once dirname( SHIPPER_PLUGIN_FILE ) . '/vendor/autoload.php';
		}

		$client = false;
		try {
			$client = new Aws\S3\S3Client(
				array(
					'version' => '2006-03-01',
					'region'  => 'us-east-1',
					'use_aws_shared_config_files' => false,
				)
			);
		} catch ( Exception $e ) {
			return false;
		}
		return is_object( $client );
	}
}