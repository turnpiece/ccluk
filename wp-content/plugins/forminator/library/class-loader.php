<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Forminator_Loader {

	/**
	 * @var array
	 */
	public $files = array();

	/**
	 * Forminator_Loader constructor.
	 *
	 */
	public function __construct() {}

	/**
	 * Retrieve data
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function load_files( $dir ) {
		$files = scandir( forminator_plugin_dir() . $dir );
		foreach ( $files as $file ) {
			$path = forminator_plugin_dir() . $dir . '/' . $file;

			if( $this->is_php( $file ) && is_file( $path ) ) {
				// Get class name
				$class_name = str_replace( '.php', '', $file );
				// Include file
				include $path;

				// Init class
				$object = $this->init( $class_name );

				$this->files[] = $object;
			}
		}

		return $this->files;
	}

	/**
	 * Check if PHP file
	 *
	 * @since 1.0
	 * @param $file
	 *
	 * @return bool
	 */
	public function is_php( $file ) {
		$check = substr( $file, - 4 );
		if ( $check == '.php' ) {
			return true;
		}

		return false;
	}

	/**
	 * Normalize class name
	 *
	 * @since 1.0
	 * @param $name
	 *
	 * @return mixed|string
	 */
	public function normalize( $name ) {
		$name = str_replace( '-', '_', $name );
		$name = ucwords( $name );

		return $name;
	}

	/**
	 * Init class
	 *
	 * @since 1.0
	 * @param $name
	 *
	 * @return mixed
	 */
	private function init( $name ) {
		$class = 'Forminator_' . $this->normalize( $name );

		if ( class_exists( $class ) ) {
			$object = new $class();

			return $object;
		}
	}
}