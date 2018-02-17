<?php

/**
 * Class WP_Hummingbird_Sources_Collector
 *
 * Manages the collection of all sources that WP HUmmingbird is going to compress
 */
class WP_Hummingbird_Sources_Collector {

	private static $styles_option = 'wphb_styles_collection';
	private static $scripts_option = 'wphb_scripts_collection';

	private $collection_updated = false;

	private $collected = array(
		'styles'  => array(),
		'scripts' => array(),
	);


	public function __construct() {
		$this->collected = self::get_collection();
	}

	public function save_collection() {
		if ( $this->collection_updated ) {
			update_option( self::$styles_option, $this->collected['styles'] );
			update_option( self::$scripts_option, $this->collected['scripts'] );
		}

	}

	public function add_to_collection( $registered, $type ) {
		$registered = (array) $registered;

		if ( isset( $this->collected[ $type ][ $registered['handle'] ] ) && $registered === $this->collected[ $type ][ $registered['handle'] ] ) {
			return;
		}

		$this->collection_updated = true;
		$this->collected[ $type ][ $registered['handle'] ] = $registered;
	}


	public static function get_collection() {
		return array(
			'styles'  => get_option( self::$styles_option, array() ),
			'scripts' => get_option( self::$scripts_option, array() ),
		);
	}

	public static function clear_collection() {
		delete_option( self::$styles_option );
		delete_option( self::$scripts_option );
	}

	public static function clear_handle_from_collection( $handle, $type ) {
		$collection = self::get_collection();
		if ( ! isset( $collection[ $type ][ $handle ] ) ) {
			return;
		}

		unset( $collection[ $type ][ $handle ] );

		update_option( self::$styles_option, $collection['styles'] );
		update_option( self::$scripts_option, $collection['scripts'] );
	}

	/**
	 * @TODO Finish
	 * @param $plugin
	 */
	/*
	public static function remove_sources_from_plugin( $plugin ) {
		$collection = self::get_collection();
		$plugin_dir = '/plugins/' . dirname( $plugin );
		foreach ( $collection['styles'] as $style ) {

		}
	}
	*/

}