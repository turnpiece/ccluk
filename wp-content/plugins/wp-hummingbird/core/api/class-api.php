<?php

class WP_Hummingbird_API {

	public function __construct() {
		spl_autoload_register( array( $this, 'autoload' ) );
		$this->uptime = new WP_Hummingbird_API_Service_Uptime();
		$this->performance = new WP_Hummingbird_API_Service_Performance();
		$this->cloudflare = new WP_Hummingbird_API_Service_Cloudflare();
		$this->minify = new WP_Hummingbird_API_Service_Minify();
	}

	/**
	 * Hummingbird API autoloader
	 *
	 * @param $classname
	 */
	public function autoload( $classname ) {
		if ( strpos( $classname, 'WP_Hummingbird_API_' ) !== 0 ) {
			return;
		}

		$base_dir = WPHB_DIR_PATH . 'core/api';

		$classname = str_replace( 'WP_Hummingbird_API_', '', $classname );
		$class_parts = explode( '_', $classname );

		if ( ! $class_parts ) {
			return;
		}

		$folder = strtolower( $class_parts[0] );
		if ( ! isset( $class_parts[1] ) ) {
			$file = "$base_dir/$folder/$folder.php";
		} else {
			$file_slug = strtolower( $class_parts[1] );
			$file = "$base_dir/$folder/$file_slug.php";
		}

		if ( is_readable( $file ) ) {
			/* @noinspection PhpIncludeInspection */
			include_once( $file );
		}
	}

}