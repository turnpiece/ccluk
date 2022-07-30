<?php
/**
 * Shipper controllers: table overrides
 *
 * @package shipper
 */

/**
 * Tables overrides controller class
 */
class Shipper_Controller_Override_Tables extends Shipper_Controller_Override {

	/**
	 * Boots the controller and sets up event listeners
	 */
	public function boot() {
		$constants = $this->get_constants();

		if ( $constants->get( 'SHIPPER_EXPORTED_TABLE_CHARSET' ) ) {
			add_filter(
				'shipper_export_tables_create_charset',
				array( $this, 'apply_exported_table_charset' )
			);
		}

		if ( $constants->get( 'SHIPPER_EXPORTED_TABLE_COLLATION' ) ) {
			add_filter(
				'shipper_export_tables_create_collate',
				array( $this, 'apply_exported_table_collation' )
			);
		}
		/**
		 * Since 1.1 we allow for update prefix by source, dest and custom.
		 */
		$migration = new Shipper_Model_Stored_Migration();
		$prefix    = $migration->get( 'destination_prefix' );

		if ( $constants->get( 'SHIPPER_IMPORT_TABLE_PREFIX' ) || ! empty( $prefix ) ) {
			$this->apply_import_table_prefix( $prefix );
		}
		/**
		 * Here we exclude table if any set
		 */
		add_action( 'shipper_migration_before_task', array( &$this, 'append_exclude_table_listener' ), 10 );
		add_action( 'shipper_export_table_include_row', array( &$this, 'maybe_exclude_row' ), 10, 3 );
		$meta = new Shipper_Model_Stored_MigrationMeta();
		if ( $meta->is_extract_mode() ) {
			add_action( 'shipper_export_table_include_row', array( &$this, 'maybe_include_user' ), 11, 3 );
		}
	}

	/**
	 * Maybe include user
	 *
	 * @param bool   $include whether to include or not.
	 * @param string $raw raw string.
	 * @param string $table table name.
	 *
	 * @return false
	 */
	public function maybe_include_user( $include, $raw, $table ) {
		global $wpdb;
		$meta = new Shipper_Model_Stored_MigrationMeta();
		if ( $table === $wpdb->users ) {
			if ( ! is_user_member_of_blog( $raw['ID'], $meta->get_site_id() ) ) {
				return false;
			}
		}

		if ( $table === $wpdb->usermeta ) {
			if ( ! is_user_member_of_blog( $raw['user_id'], $meta->get_site_id() ) ) {
				return false;
			}
		}

		return $include;
	}

	/**
	 * Maybe exclude a row
	 *
	 * @param bool   $include whether to include or not.
	 * @param string $raw row name.
	 * @param string $table table name.
	 *
	 * @return false
	 */
	public function maybe_exclude_row( $include, $raw, $table ) {
		global $wpdb;
		$tbl        = $wpdb->options;
		$field_name = 'option_name';
		if ( is_multisite() ) {
			$tbl        = $wpdb->sitemeta;
			$field_name = 'meta_key';
		}
		if ( $tbl === $table ) {
			$fields = array(
				// 'wdp_un_analytics_enabled',
				'wdp_un_analytics_site_id',
				'wdp_un_analytics_tracker',
				'wdp_un_analytics_metrics',
				'wdp_un_remote_access',
			);
			if ( isset( $raw[ $field_name ] ) && in_array( $raw[ $field_name ], $fields, true ) ) {
				// we dont copy support staff status.
				return false;
			}
		}

		return $include;
	}

	/**
	 * Append exclude table listener.
	 *
	 * @param object $current_task current running task.
	 *
	 * @return void
	 */
	public function append_exclude_table_listener( $current_task ) {
		if ( ! $current_task instanceof Shipper_Task_Export_Tables ) {
			return;
		}

		add_filter(
			'shipper_path_include_table',
			array( $this, 'maybe_include_table' ),
			10,
			2
		);
	}

	/**
	 * Maybe include a table
	 *
	 * @param bool   $include whether to inlcude a table or not.
	 * @param string $table table name.
	 *
	 * @return bool
	 */
	public function maybe_include_table( $include, $table ) {
		if ( empty( $include ) ) {
			return $include;
		}

		$model      = new Shipper_Model_Stored_MigrationMeta();
		$exclusions = $model->get( Shipper_Model_Stored_MigrationMeta::KEY_EXCLUSIONS_DB );
		if ( empty( $exclusions ) ) {
			return $include;
		}
		return ! in_array( $table, $exclusions, true );
	}

	/**
	 * Binds to needed filters in order to apply the new table prefix
	 *
	 * @param string $prefix table prefix.
	 *
	 * @return bool
	 */
	public function apply_import_table_prefix( $prefix ) {
		if ( ! defined( 'SHIPPER_IMPORT_TABLE_PREFIX' ) && ! $prefix ) {
			return false;
		}
		if ( ! $prefix ) {
			$prefix = SHIPPER_IMPORT_TABLE_PREFIX;
		}
		if ( empty( $prefix ) || ! is_string( $prefix ) ) {
			return false;
		}

		$affected_codecs = array(
			'var',
			'preoptionname',
			'premetakey',
		);
		foreach ( $affected_codecs as $codec ) {
			add_filter(
				"shipper_codec_{$codec}_macro_table_prefix_decode",
				array( $this, 'get_import_table_prefix' )
			);
		}
		add_filter(
			'shipper_destination_table_name',
			array( $this, 'apply_destination_table_prefix' )
		);
	}

	/**
	 * Gets the new import table prefix
	 *
	 * @param string $source Optional source prefix.
	 *
	 * @return string New prefix, or source on failure.
	 */
	public function get_import_table_prefix( $source = '' ) {
		$migration = new Shipper_Model_Stored_Migration();
		$prefix    = $migration->get( 'destination_prefix' );
		$constants = $this->get_constants();

		if ( ! $constants->is_defined( 'SHIPPER_IMPORT_TABLE_PREFIX' ) && ! $prefix ) {
			return $source;
		}

		if ( ! $prefix ) {
			$prefix = $constants->get( 'SHIPPER_IMPORT_TABLE_PREFIX' );
		}

		if ( empty( $prefix ) || ! is_string( $prefix ) ) {
			return $source;
		}

		return $prefix;
	}

	/**
	 * Applies the new table prefix to imported table name
	 *
	 * @param string $table Source table name.
	 *
	 * @return string
	 */
	public function apply_destination_table_prefix( $table ) {
		$prefix = $this->get_import_table_prefix();
		if ( empty( $prefix ) ) {
			return $table;
		}

		global $wpdb;

		return preg_replace(
			'/^' . preg_quote( $wpdb->base_prefix, '/' ) . '/',
			$prefix,
			$table
		);
	}

	/**
	 * Applies export table charset
	 *
	 * @param string $charset Source charset.
	 *
	 * @return string Defined value
	 */
	public function apply_exported_table_charset( $charset ) {
		if ( ! defined( 'SHIPPER_EXPORTED_TABLE_CHARSET' ) ) {
			return $charset;
		}

		return SHIPPER_EXPORTED_TABLE_CHARSET;
	}

	/**
	 * Applies export table collation
	 *
	 * @param string $collation Source collation.
	 *
	 * @return string Defined value
	 */
	public function apply_exported_table_collation( $collation ) {
		if ( ! defined( 'SHIPPER_EXPORTED_TABLE_COLLATION' ) ) {
			return $collation;
		}

		return SHIPPER_EXPORTED_TABLE_COLLATION;
	}
}