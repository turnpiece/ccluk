<?php
/**
 * Shipper models: permanent migration data
 *
 * Holds current migration data.
 *
 * @package shipper
 */

/**
 * Stored migration model class
 */
class Shipper_Model_Stored_Migration extends Shipper_Model_Stored {

	const TYPE_EXPORT = 'export';
	const TYPE_IMPORT = 'import';

	const ORIG_HUB = 'hub-started';
	const ORIG_LOCAL = 'local-started';

	const STATE_ACTIVE = 'active';
	const STATE_COMPLETED = 'complete';

	const COMPONENT_FS = 'files';
	const COMPONENT_DB = 'sqls';
	const COMPONENT_META = 'meta';

	const HAS_STARTED = 'has-started';

	/**
	 * Gets maximum file size before issuing a warning
	 *
	 * @return int Allowed file size, in bytes
	 */
	static public function get_file_size_threshold() {
		$max = 8 * 1024 * 1024;

		/**
		 * Maximum individual file size allowed before issuing a warning
		 *
		 * @param int $max Maximum file size, in bytes.
		 *
		 * @return int
		 */
		return apply_filters(
			'shipper_thresholds_max_file_size',
			$max
		);
	}

	/**
	 * Gets maximum package size allowed before issuing a warning
	 *
	 * @return int Raw package size, in bytes
	 */
	static public function get_package_size_threshold() {
		$max = 200 * 1024 * 1024;

		/**
		 * Maximum raw package size allowed before issuing a warning
		 *
		 * @param int $max Maximum package size, in bytes.
		 *
		 * @return int
		 */
		return apply_filters(
			'shipper_thresholds_max_package_size',
			$max
		);
	}

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'migration' );
	}

	/**
	 * Prepare active transaction
	 *
	 * @param string $source Migration source domain.
	 * @param string $destination Migration destination domain.
	 * @param string $type Migration type.
	 * @param string $orig Optional migration origin.
	 *
	 * @return object Shipper_Model_Stored_Migration instance.
	 */
	public function prepare( $source, $destination, $type, $orig = false ) {
		$this->clear();

		$this->set( 'source', $source );
		$this->set( 'destination', $destination );
		$this->set( 'type', $type );

		if ( self::ORIG_HUB !== $orig ) {
			$orig = self::ORIG_LOCAL;
		}
		$this->set( 'origin', $orig );

		$this->save();

		return $this;
	}

	/**
	 * Starts active migration
	 *
	 * @return bool
	 */
	public function begin() {
		if ( ! $this->get_source() ) {
			return false;
		}
		if ( ! $this->get_destination() ) {
			return false;
		}
		if ( ! $this->get_type() ) {
			return false;
		}
		if ( ! $this->get_origin() ) {
			return false;
		}

		$this->set( 'state', self::STATE_ACTIVE );
		$this->save();

		return $this->is_active();
	}

	/**
	 * Completes active migration
	 *
	 * @return bool
	 */
	public function complete() {
		$this->set( 'state', self::STATE_COMPLETED );
		$this->save();

		return ! $this->is_active();
	}

	/**
	 * Checks whether the migration is active
	 *
	 * @return bool
	 */
	public function is_active() {
		return self::STATE_ACTIVE === $this->get( 'state' );
	}

	/**
	 * Checks wheter the migration is complete
	 *
	 * @return bool
	 */
	public function is_completed() {
		return self::STATE_COMPLETED === $this->get( 'state' );
	}

	/**
	 * Checks whether the migration originated from the Hub
	 *
	 * @return bool
	 */
	public function is_from_hub() {
		return self::ORIG_HUB === $this->get( 'origin' );
	}

	/**
	 * Checks whether the migration data is present at all.
	 *
	 * @return bool
	 */
	public function is_empty() {
		$data = $this->get_data();

		return empty( $data );
	}

	/**
	 * Gets migration source
	 *
	 * @return string Migration source domain
	 */
	public function get_source() {
		return $this->get( 'source' );
	}

	/**
	 * Gets migration destination
	 *
	 * @return string Migration destination domain
	 */
	public function get_destination() {
		return $this->get( 'destination' );
	}

	/**
	 * Gets migration type
	 *
	 * @return string Migration type
	 */
	public function get_type() {
		return $this->get( 'type' );
	}

	/**
	 * Gets migration origin
	 *
	 * @return string Migration origin
	 */
	public function get_origin() {
		return $this->get( 'origin' );
	}

	/**
	 * Gets current migration description string
	 *
	 * @return string
	 */
	public function get_description() {
		$source      = $this->get_source();
		$destination = $this->get_destination();

		$direction = self::TYPE_IMPORT === $this->get_type() ? '<=' : '=>';

		$state  = $this->is_active()
			? __( 'running', 'shipper' )
			: __( 'idle', 'shipper' );
		$origin = $this->is_from_hub()
			? __( 'Hub-originated', 'shipper' )
			: __( 'User-initiated', 'shipper' );

		return "[{$source} {$direction} {$destination}]: {$origin}, {$state}";
	}
}