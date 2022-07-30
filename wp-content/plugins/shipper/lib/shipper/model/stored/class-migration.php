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

	const ORIG_HUB   = 'hub-started';
	const ORIG_LOCAL = 'local-started';

	const STATE_ACTIVE    = 'active';
	const STATE_COMPLETED = 'complete';

	const COMPONENT_FS   = 'files';
	const COMPONENT_DB   = 'sqls';
	const COMPONENT_META = 'meta';

	const HAS_STARTED          = 'has-started';
	const KEY_CREATED          = 'package_created';
	const PACKAGE_SIZE         = 'package_size';
	const NOTICE_DISMISSED     = 'notice-dismissed';
	const IS_PACKAGE_MIGRATION = 'is_package_migration';

	/**
	 * Gets maximum file size before issuing a warning
	 *
	 * @return int Allowed file size, in bytes
	 */
	public static function get_file_size_threshold() {
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
	public static function get_package_size_threshold() {
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

		/**
		 * Save package size on migration start.
		 *
		 * @since 1.2
		 */
		add_action( 'shipper_migration_start', array( $this, 'save_package_size' ) );
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
		$this->set(
			self::KEY_CREATED,
			strtotime( current_time( 'mysql' ) )
		);
		$this->set( 'state', self::STATE_COMPLETED );
		$this->save();

		// remove everything from the model as we no longer need these info.
		( new Shipper_Model_Stored_Dump() )->clear()->save();

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
	 * Checks whether the migration is complete
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
	 * @param bool $for_display whether to display or not.
	 *
	 * @return string Migration source domain
	 */
	public function get_source( $for_display = false ) {
		if ( $for_display && is_multisite() && $this->get_type() === self::TYPE_EXPORT ) {
			$meta = new Shipper_Model_Stored_MigrationMeta();
			if ( $meta->is_extract_mode() ) {
				return $meta->get_source();
			}
		}

		return $this->get( 'source' );
	}

	/**
	 * Gets migration destination
	 *
	 * @param bool $for_display whether to display or not.
	 *
	 * @return string Migration destination domain
	 */
	public function get_destination( $for_display = false ) {
		if ( $for_display ) {
			$meta = new Shipper_Model_Stored_MigrationMeta();
			if ( $meta->is_extract_mode() ) {
				$task  = new Shipper_Task_Api_Info_Get();
				$data  = $task->apply();
				$sites = ! empty( $data['wordpress'][ Shipper_Model_System_Wp::MS_SUBSITES ] ) ? $data['wordpress'][ Shipper_Model_System_Wp::MS_SUBSITES ] : array();
				$site  = null;
				foreach ( $sites as $item ) {
					if ( $item['blog_id'] === $meta->get_site_id() ) {
						$site = $item;
						break;
					}
				}

				if ( is_array( $site ) ) {
					return $site['domain'] . rtrim( $site['path'], '/' );
				}
			}
		}

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

	/**
	 * Set the migration as package migration
	 *
	 * @since 1.1.4
	 *
	 * @return void
	 */
	public function make_it_package_migration() {
		$this->set( self::IS_PACKAGE_MIGRATION, true );
		$this->save();
	}

	/**
	 * Check whether is it package_migration or not
	 *
	 * @since 1.1.4
	 */
	public function is_package_migration() {
		return $this->get( self::IS_PACKAGE_MIGRATION, false );
	}

	/**
	 * Check whether any important table is missing or not.
	 *
	 * @since 1.1.4
	 *
	 * @return bool
	 */
	public function is_important_tables_missing() {
		global $table_prefix;

		$model = new Shipper_Model_Stored_MigrationMeta();

		if ( $this->is_package_migration() ) {
			$model = new Shipper_Model_Stored_PackageMeta();
		}

		$excluded_tables  = defined( get_class( $model ) . '::KEY_EXCLUSIONS_DB' ) ? $model->get( $model::KEY_EXCLUSIONS_DB, array() ) : array();
		$important_tables = array( 'users', 'usermeta', 'options' );

		if ( $model->is_extract_mode() && $model->get_site_id() !== 1 ) {
			array_pop( $important_tables );
		}

		$important_tables = array_map(
			function( $table ) use ( $table_prefix ) {
				return $table_prefix . $table;
			},
			$important_tables
		);

		return ! ! array_intersect( $important_tables, $excluded_tables );
	}

	/**
	 * Get the package size
	 *
	 * @since 1.2
	 *
	 * @return int
	 */
	public function get_size() {
		return $this->get( self::PACKAGE_SIZE, 0 );
	}

	/**
	 * Set the package size
	 *
	 * @since 1.2
	 *
	 * @param int $bytes number of bytes.
	 *
	 * @return void
	 */
	public function set_size( $bytes ) {
		$this->set( self::PACKAGE_SIZE, $bytes );
	}

	/**
	 * Save package size when package size is known
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function save_package_size() {
		$estimate = new Shipper_Model_Stored_Estimate();
		$this->set_size( $estimate->get( 'package_size', 0 ) );
		$this->save();
	}

	/**
	 * Check whether wp-config file is skipped or not
	 *
	 * @since 1.2
	 *
	 * @return bool
	 */
	public function is_wp_config_skipped() {
		$model = new Shipper_Model_Stored_Options();

		return $model->get( $model::KEY_SKIPCONFIG, false );
	}
}