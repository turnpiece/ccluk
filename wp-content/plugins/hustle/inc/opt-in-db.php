<?php

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
if( !class_exists( "Opt_In_Db" ) ):

/**
 * Class Opt_In_Db
 *
 * Takes care of all the db initializations
 *
 */
class Opt_In_Db {

	const DB_VERSION_KEY = 'opt_in_database_version';

	const TABLE_OPT_IN = "optins";

	const TABLE_OPT_IN_META = "optin_meta";

	static $db;

	function __construct(){

		$this->_create_tables();
	}

	/**
	 * Creates plugin tables
	 *
	 * @since 1.0.0
	 */
	private function _create_tables(){


		$db_version = get_site_option( self::DB_VERSION_KEY  );
		// check if current version is equal to database version
		if ( version_compare( $db_version, Opt_In::VERSION, '=' ) ) return;


		foreach( $this->_get_tables() as $name =>  $columns ){
			$sql = $this->_create_table_sql(   $name, $columns );
			dbDelta( $sql );
		}

		update_site_option( self::DB_VERSION_KEY, Opt_In::VERSION );
	}

	/**
	 * Generates CREATE TABLE sql script for provided table name and columns list.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param string $name The name of a table.
	 * @param array $columns The array  of columns, indexes, constraints.
	 * @return string The sql script for table creation.
	 */
	private function _create_table_sql( $name, array $columns ) {
		global $wpdb;

		$charset = '';
		if ( !empty( $wpdb->charset ) ) {
			$charset = " DEFAULT CHARACTER SET " . $wpdb->charset;
		}

		$collate = '';
		if ( !empty( $wpdb->collate ) ) {
			$collate .= " COLLATE " . $wpdb->collate;
		}

		$name = $wpdb->base_prefix . $name;
		return sprintf( 'CREATE TABLE IF NOT EXISTS `%s` (%s)%s%s', $name, implode( ', ', $columns ), $charset, $collate );
	}

	/**
	 * Returns db table arrays with their "Create syntax"
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function _get_tables(){
		global $wpdb;

		$collate = '';
		if ( !empty( $wpdb->collate ) ) {
			$collate .= " COLLATE " . $wpdb->collate;
		}

		return array(
				self::TABLE_OPT_IN  => array(
					'`optin_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT',
					"`blog_id` bigint(20) unsigned NOT NULL DEFAULT '0'",
					'`optin_name` VARCHAR(255) NOT NULL',
					'`optin_title` VARCHAR(255) NOT NULL',
					'`optin_message` LONGTEXT  NOT NULL',
					'`optin_mail_list` VARCHAR(128) NOT NULL',
					'`optin_provider` VARCHAR(255)  NOT NULL',
					'`active` TINYINT DEFAULT 1',
					'`test_mode` TINYINT DEFAULT 0',
					'PRIMARY KEY (`optin_id`)',
					'KEY `blog_id` (`blog_id`)',
					'KEY `active` (`active`)'
				),
				self::TABLE_OPT_IN_META => array(
					"`meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT",
					"`optin_id` bigint(20) unsigned NOT NULL DEFAULT '0'",
					"`meta_key` varchar(191) " . $collate . " DEFAULT NULL",
					"`meta_value` longtext " . $collate,
					"PRIMARY KEY (`meta_id`)",
					"KEY `optin_id` (`optin_id`)",
					"KEY `meta_key` (`meta_key`(191))"
				)
		);
	}
}
endif;