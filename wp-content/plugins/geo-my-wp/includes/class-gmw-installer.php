<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Installer' ) ) :

/**
 * GMW_Installer class.
 *
 * Create and updated tables, import forms....
 *
 * @since  3.0
 *
 * @author Eyal Fitoussi
 */
class GMW_Installer {

	/**
	 * Database version
	 * @var integer
	 */
	public static $db_version = 3;

	/**
	 * Run installer
	 */
	public static function init() {

		// Run some tasks on plugin activation.
		self::do_tasks();

		// Update license keys.
		self::update_license_keys();

		// Create database tables.
		self::create_tables();

		// Update the forms table only once.
		if ( ( get_option( 'gmw_forms_table_updated' ) == false ) ) {
			self::update_forms_table();
		}

		// schedule cron jobs
		self::schedule_cron();

		// run GMW update if version changed
		if ( version_compare( GMW_VERSION, get_option( 'gmw_version' ), '>' ) ) {
			self::update();
		}

		// get forms db version
		$saved_db_version = get_option( 'gmw_db_version' );

		// upgrade forms db
		if ( empty( $saved_db_version ) || is_array( $saved_db_version ) || version_compare( self::$db_version, $saved_db_version, '!=' ) ) {
			self::update_db();
		}

		// upgrade locations db
		/*if ( empty( $saved_db_version['locations'] ) || version_compare( self::$db_version['locations'], $saved_db_version['locations'], '>' ) ) {
			self::upgrade_locations_db();
		}

		// upgrade location meta db if needed
		if ( empty( $saved_db_version['locationmeta'] ) || version_compare( self::$db_version['locationmeta'], $saved_db_version['locationmeta'], '>' ) ) {
			self::upgrade_locationmeta_db();
		}*/

		update_option( 'gmw_db_version', self::$db_version );
		update_option( 'gmw_version', GMW_VERSION );
	}

	public static function do_tasks() {

		// Enable updater by default on activation.
		// It is easy to forget that it is disabled and users can miss updateds.
		update_option( 'gmw_extensions_updater', true );

		// Flush all internal cache.
		GMW_Cache_Helper::flush_all();
	}

	/**
	 * Update license keys data.
	 *
	 * This should happens only once after the update to GEO my WP 3.0
	 *
	 * since the wp options for the license keys has changed.
	 *
	 * This as well a fix when updating from v3.0 - beta 1
	 *
	 * @return [type] [description]
	 */
	public static function update_license_keys() {

		// do this only if the new license keys option is not yet exist
		if ( get_option( 'gmw_license_data' ) === false ) {

			// look for license data in old option
			$license_keys = get_option( 'gmw_license_keys' );
			// look for statuses in old option
			$statuses = get_option( 'gmw_premium_plugin_status' );

			$new_licenses = array();

			// proceed only if licenses data exists in old option
			if ( ! empty( $license_keys ) ) {

				foreach ( $license_keys as $key => $value ) {

					if ( empty( $key ) ) {
						continue;
					}

					// if value is not an array means it is coming from old
					// options and need to generate an array.
					if ( ! is_array( $value ) ) {

						$new_licenses[ $key ] = array(
							'key'    => $value,
							'status' => ! empty( $statuses[ $key ] ) ? $statuses[ $key ] : 'inactive',
						);

						// if this is already an array we keep the value as is
					} else {

						$new_licenses[ $key ] = $value;
					}
				}
			}

			update_option( 'gmw_license_data', $new_licenses );
		}
	}

	/**
	 * Create GEO my WP database tables
	 *
	 * @return [type] [description]
	 */
	public static function create_tables() {

		global $wpdb;

		// charset
		$charset_collate = ! empty( $wpdb->charset ) ? "DEFAULT CHARACTER SET {$wpdb->charset}" : 'DEFAULT CHARACTER SET utf8';

		// collation
		$charset_collate .= ! empty( $wpdb->collate ) ? " COLLATE {$wpdb->collate}" : ' COLLATE utf8_general_ci';

		// forms table name
		$forms_table = $wpdb->prefix . 'gmw_forms';

		// check if table exists already
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '{$forms_table}'", ARRAY_A );

		// if form table not exists create it
		if ( count( $table_exists ) == 0 ) {

			// generate table sql
			$sql = "CREATE TABLE $forms_table (
				ID INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
				slug VARCHAR( 50 ) NOT NULL,
				addon VARCHAR( 50 ) NOT NULL,
				component VARCHAR( 50 ) NOT NULL,
				object_type VARCHAR( 50 ) NOT NULL,
				name VARCHAR( 50 ) NOT NULL,
				title VARCHAR( 50 ) NOT NULL,
				prefix VARCHAR( 20 ) NOT NULL,
				data LONGTEXT NOT NULL,
				PRIMARY KEY ID (ID)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// create database table
			dbDelta( $sql );
		}

		// locations table name
		$locations_table = $wpdb->base_prefix . 'gmw_locations';

		// check if table already exists
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '{$locations_table}'", ARRAY_A );

		// create table if not already exists
		if ( count( $table_exists ) == 0 ) {

			// generate table sql
			$sql = "CREATE TABLE $locations_table (
				ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				object_type VARCHAR(20) NOT NULL,
				object_id BIGINT(20) UNSIGNED NOT NULL default 0,
				blog_id BIGINT(20) UNSIGNED NOT NULL default 0,
				user_id BIGINT(20) UNSIGNED NOT NULL default 0,
				parent BIGINT(20) UNSIGNED NOT NULL default 0,
				status INT(11) NOT NULL default 1,
				featured TINYINT NOT NULL default 0,
				title TEXT,
				latitude FLOAT( 10, 6 ) NOT NULL,
	  			longitude FLOAT( 10, 6 ) NOT NULL,
				street_number VARCHAR( 60 ) NOT NULL default '',
				street_name VARCHAR( 144 ) NOT NULL default '',
				street VARCHAR( 144 ) NOT NULL default '',
				premise VARCHAR( 50 ) NOT NULL default '',
				neighborhood VARCHAR( 96 ) NOT NULL default '',
				city VARCHAR( 128 ) NOT NULL default '',
				county VARCHAR( 128 ) NOT NULL default '',	
				region_name VARCHAR( 50 ) NOT NULL default '',
				region_code CHAR( 50 ) NOT NULL,
				postcode VARCHAR( 24 ) NOT NULL default '',
				country_name VARCHAR( 96 ) NOT NULL default '',
				country_code CHAR( 2 ) NOT NULL,
				address varchar( 255 ) NOT NULL default '',
				formatted_address VARCHAR( 255 ) NOT NULL,
				place_id VARCHAR( 255 ) NOT NULL,
				map_icon VARCHAR(50) NOT NULL,
				/*radius NUMERIC( 6,1 ) NOT NULL,*/
				created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				updated DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				PRIMARY KEY ID (ID),
				KEY coordinates (latitude,longitude),
				KEY latitude (latitude),
				KEY longitude (longitude),
				KEY object_type (object_type),
				KEY object_id (object_id),
				KEY blog_id (blog_id),
				KEY user_id (user_id),
				KEY city (city),
				KEY region (region_name),
				KEY postcode (postcode),
				KEY country (country_name),
				KEY country_code (country_code)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// create database table
			dbDelta( $sql );
		}

		// location meta table
		$location_meta_table = $wpdb->base_prefix . 'gmw_locationmeta';

		// check if table already exists
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '$location_meta_table'", ARRAY_A );

		// create table if not exists already
		if ( count( $table_exists ) == 0 ) {

			// generate table sql
			$sql = "CREATE TABLE $location_meta_table (
				meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				location_id BIGINT(20) UNSIGNED NOT NULL default 0,
				meta_key VARCHAR(191) NULL,
				meta_value LONGTEXT NULL,
				PRIMARY KEY meta_id (meta_id),
				KEY location_id (location_id),
				KEY meta_key (meta_key)
			) $charset_collate; ";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// create database table
			dbDelta( $sql );
		}

		// look for post types table
		$posts_table = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}places_locator'", ARRAY_A );

		// look for users table
		$members_table = $wpdb->get_results( "SHOW TABLES LIKE 'wppl_friends_locator'", ARRAY_A );

		// if any of the tables exist set an option that will rigger an admin notice
		// to import existing db tables
		if ( count( $posts_table ) != 0 || count( $members_table ) != 0 ) {
			update_option( 'gmw_old_locations_tables_exist', true );
		}
	}

	/**
	 * Update forms table
	 *
	 * @return [type] [description]
	 */
	public static function update_forms_table() {

		include( GMW_PATH . '/includes/admin/pages/tools/class-gmw-update-forms-table.php' );

		$form_updater = new GMW_Update_Forms_Table();

		$form_updater->init();

		update_option( 'gmw_forms_table_updated', 1 );
	}

	/**
	 * Run plugin's updates
	 *
	 * @return [type] [description]
	 */
	public static function update() {

		global $wpdb;

		$options = get_option( 'gmw_options' );

		// Move map API key to new setting field.
		if ( empty( $options['api_providers']['google_maps_client_side_api_key'] ) && ! empty( $options['api_providers']['google_maps_server_api_key'] ) ) {
			$options['api_providers']['google_maps_client_side_api_key'] = $options['api_providers']['google_maps_server_api_key'];

			unset( $options['api_providers']['google_maps_server_api_key'] );

			update_option( 'gmw_options', $options );
		}

		// Modify location meta key type.
		$column = $wpdb->get_results( "DESCRIBE {$wpdb->base_prefix}gmw_locationmeta meta_key" );

		if ( ! empty( $column ) && 'varchar(255)' == $column[0]->Type ) {
			$wpdb->query( "ALTER TABLE {$wpdb->base_prefix}gmw_locationmeta MODIFY meta_key varchar(191)" );
		}

		// locations table name
		$locations_table = $wpdb->base_prefix . 'gmw_locations';

		// check if table already exists
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '{$locations_table}'", ARRAY_A );

		// Do tasks if table exists
		if ( count( $table_exists ) !== 0 ) {

			// Modify the default value of date columns if needed.
			$column = $wpdb->get_results( "DESCRIBE {$locations_table} created" );

			if ( $column[0]->Default === CURRENT_TIMESTAMP )  {

				$wpdb->query( "
					ALTER TABLE {$locations_table}
					MODIFY created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
					MODIFY updated DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
				);
			}

			// create new radius column if not exists.
			$column = $wpdb->get_results( "SHOW COLUMNS FROM {$locations_table} LIKE 'radius'" ); // WPCS: db call ok, cache ok.

			if ( ! empty( $column ) ) {
				$wpdb->query( "ALTER TABLE {$locations_table} CHANGE COLUMN `radius` `lRadius` NUMERIC( 6,1 ) NOT NULL" ); // WPCS: db call ok, cache ok.
			}

			/*if ( empty( $column ) ) {
				$wpdb->query( "ALTER TABLE {$locations_table} ADD COLUMN radius NUMERIC( 6,1 ) NOT NULL AFTER map_icon" ); // WPCS: db call ok, cache ok.
			}*/

			// Add indexes if not exist.
			$index = $wpdb->get_results( "SHOW INDEX FROM {$locations_table} WHERE Key_name = 'object_type'" );

			if ( empty( $index ) ) {
				$wpdb->query( "
					ALTER TABLE {$locations_table}
					ADD INDEX object_type ( object_type ),
					ADD INDEX object_id ( object_id ),
					ADD INDEX blog_id ( blog_id ),
					ADD INDEX user_id ( user_id )"
				);
			}
		}
	}

	/**
	 * Upgrade forms database tables
	 *
	 * @return [type] [description]
	 */
	public static function update_db() {}

	/**
	 * Upgrade locations database tables
	 *
	 * @return [type] [description]
	 */
	//public static function upgrade_locations_db();

	/**
	 * Upgrade location meta database tables
	 *
	 * @return [type] [description]
	 */
	//public static function upgrade_locationmeta_db() {}

	/**
	 * Setup cron jobs
	 */
	private static function schedule_cron() {
		wp_clear_scheduled_hook( 'gmw_clear_expired_transients' );
		wp_schedule_event( time(), 'twicedaily', 'gmw_clear_expired_transients' );
	}
}

endif;
