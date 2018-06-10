<?php

class Hustle_Collection
{

	/**
	 * @return Hustle_Collection
	 */
	public static function instance(){
		return new self;
	}

	/**
	 * Reference to $wpdb global var
	 *
	 * @since 1.0.0
	 *
	 * @var $_db WPDB
	 * @access private
	 */
	protected static $_db;

	function __construct(){
		global $wpdb;
		self::$_db = $wpdb;
	}

	function get_count(){
		return self::$_db->num_rows;
	}

	/**
	 * Returns table name
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function _get_table(){
		return self::$_db->base_prefix . Hustle_Db::TABLE_HUSTLE_MODULES;
	}


	/**
	 * Returns meta table name
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function _get_meta_table(){
		return self::$_db->base_prefix . Hustle_Db::TABLE_HUSTLE_MODULES_META;
	}
}