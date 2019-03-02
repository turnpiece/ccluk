<?php

/**
 * Frontend dependency manager.
 */
if ( ! is_admin() ) { // Doesn't work on admin

	/**
	 * Auto-dispatching the dependencies loading.
	 */
	class AgmDependencies {

		private static $_include = false;

		private function __construct() {}

		public static function serve() {
			self::_add_hooks();
		}

		private static function _add_hooks() {
			add_action( 'wp_head', array( __CLASS__, 'js_init_maps' ) );
		}

		public static function ensure_presence() {
			self::$_include = true;
			self::process_dependencies();
		}

		public static function process_dependencies() {
			static $Present = false;

			if ( self::$_include ) {
				if ( $Present ) { return; }
				$Present = true;

				AgmDependencies::js_data_object();
				AgmDependencies::css_load_styles();
				AgmDependencies::js_google_maps_api();
			}
		}

		public static function get_footer_hook() {
			$footer_hook = defined( 'AGM_FOOTER_HOOK' ) && AGM_FOOTER_HOOK
				? AGM_FOOTER_HOOK
				: 'wp_footer';

			return apply_filters( 'agm-dependencies-footer_hook', $footer_hook );
		}

		/**
		 * Include Google Maps dependencies.
		 */
		public static function js_google_maps_api() {
			lib3()->ui->data(
				'l10nStrings',
				array(
					'close' => __( 'Close', AGM_LANG ),
					'get_directions' => __( 'Get Directions', AGM_LANG ),
					'geocoding_error' => __( 'There was an error geocoding your location. Check the address and try again', AGM_LANG ),
					'missing_waypoint' => __( 'Please, enter values for both point A and point B', AGM_LANG ),
					'directions' => __( 'Directions', AGM_LANG ),
					'posts' => __( 'Posts', AGM_LANG ),
					'showAll' => __( 'Show All', AGM_LANG ),
					'hide' => __( 'Hide', AGM_LANG ),
					'oops_no_directions' => __( 'Oops, we couldn\'t calculate the directions', AGM_LANG ),
				),
				'front'
			);

			lib3()->ui->add( AGM_PLUGIN_URL . 'js/loader.min.js', 'all' );
			lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/google-maps.min.js', 'all' );

			do_action( 'agm-user-scripts' );
		}

		/**
		 * Introduces plugins_url() as root variable (global).
		 */
		public static function js_data_object() {
			$opt = apply_filters( 'agm_google_maps-options', get_option( 'agm_google_maps' ) );

            //Removed  'panoramio'  from defaults['libraries'] array as Panoramio is discontinued by google.
            //Leaving the "libraries" for future use

			$defaults = array(
				'ajax_url'     => admin_url( 'admin-ajax.php' ),
				'root_url'     => AGM_PLUGIN_URL,
				'is_multisite' => (int) is_multisite(),
				'libraries'    => array(),
				'maps_api_key' => !empty($opt['map_api_key']) ? $opt['map_api_key'] : '',
			);

			$vars = apply_filters(
				'agm_google_maps-javascript-data_object',
				apply_filters( 'agm_google_maps-javascript-data_object-user', $defaults )
			);

			lib3()->ui->data( '_agm', $vars );
		}

		/**
		 * Introduces global list of maps to be initialized.
		 */
		public static function js_init_maps() {
			static $Has_global = false;

			if ( $Has_global ) { return true; }
			$Has_global = true;

			echo '<script type="text/javascript">if ( window._agmMaps === undefined ) { _agmMaps = []; }</script>';
			do_action( 'agm_google_maps-add_javascript_data' );
		}

		/**
		 * Includes required styles.
		 */
		public static function css_load_styles() {
			lib3()->ui->add( AGM_PLUGIN_URL . 'css/google_maps_user.min.css', 'front' );
		}

	}
}

// Script concatenation start
if ( defined( 'AGM_OPTMIZIE_SCRIPT_LOAD' ) && AGM_OPTMIZIE_SCRIPT_LOAD ) {

	class Agm_FrontendOptimizedScriptLoad {

		private static $_cache = array();

		private function __construct() {}

		public static function serve() {
			$me = new self;
			$me->_add_hooks();
		}

		private function _add_hooks() {
			if ( ! is_admin() ) {
				add_action( 'script_loader_src', array( $this, 'optimize_scripts' ), 10, 2 );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_optimized_scripts' ), 999 );

				$footer_hook = class_exists( 'AgmDependencies' )
					? AgmDependencies::get_footer_hook()
					: 'wp_footer';

				add_action( $footer_hook, array( $this, 'write_optimized_cache' ), 99 );
			}

			add_action( 'wp_ajax_agm_get_optimized_scripts', array( $this, 'output_cached_scripts' ) );
			add_action( 'wp_ajax_nopriv_agm_get_optimized_scripts', array( $this, 'output_cached_scripts' ) );
		}

		public function optimize_scripts( $src, $handle ) {
			if ( ! preg_match( '/' . preg_quote( AGM_PLUGIN_URL, '/' ) . '/', $src ) ) {
				return $src;
			}
			if ( 'agm-optimized' === $handle ) {
				return $src; // We're good :)
			}
			if ( $this->_endpoint_has_optimized_scripts() ) {
				return false; // We know we're good here, so don't add this
			}

			$filepath = $this->_agm_src_to_filepath( $src );
			if ( ! $filepath ) {
				return $src; // Unknown file
			}

			$this->_endpoint_add_to_optimized_cache( file_get_contents( $filepath ) );
		}

		public function enqueue_optimized_scripts() {
			wp_enqueue_script(
				'agm-optimized',
				admin_url( 'admin-ajax.php?action=agm_get_optimized_scripts&key=' . $this->_get_request_key() ),
				array( 'jquery' ),
				0.1
			);
		}

		public function write_optimized_cache() {
			if ( empty( $this->_cache ) ) {
				return false;
			}
			$this->_endpoint_set_optimized_cache( join( "\n", $this->_cache ) );
		}

		public function output_cached_scripts() {
			$data = stripslashes_deep( $_GET );
			$key = ! empty( $data['key'] ) ? $data['key'] : false;
			if ( empty( $key) ) {
				die();
			}

			$cache = $this->_endpoint_get_optimized_cache( $key );
			if ( empty( $cache ) ) {
				die();
			}

			header( 'Content-type: text/javascript' );
			die( $cache );
		}

		private function _agm_src_to_filepath( $src ) {
			$src = preg_replace( '/\?.*$/', '', $src );
			$raw = preg_replace( '/' . preg_quote( AGM_PLUGIN_URL, '/' ) . '/', AGM_BASE_DIR, $src );
			$filepath = escapeshellcmd( $raw );
			return file_exists( $filepath )
				? $filepath
				: false
			;
		}

		private function _get_request_key() {
			global $wp;
			$url = home_url( $wp->request ); // Use simplified baseurl fetching
			return 'agm-js-' . md5( $url );
		}

		private function _endpoint_has_optimized_scripts() {
			$cache = $this->_endpoint_get_optimized_cache();
			return ! empty( $cache );
		}

		private function _endpoint_get_optimized_cache( $key = false ) {
			$key = ! empty( $key ) ? $key : $this->_get_request_key();
			return get_transient( $key );
		}

		private function _endpoint_set_optimized_cache( $cache ) {
			$key = $this->_get_request_key();
			return set_transient( $key, $cache, DAY_IN_SECONDS );
		}

		private function _endpoint_add_to_optimized_cache( $cache ) {
			$this->_cache[] = $cache;
		}

	}
	Agm_FrontendOptimizedScriptLoad::serve();
}
// End script concatenation