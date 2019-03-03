<?php
/**
 * Shipper helpers: asset helper class
 *
 * Does asset-related work - resolving paths, resolving URLs, loading.
 *
 * @package shipper
 */

/**
 * Asset helper class
 */
class Shipper_Helper_Assets {

	/**
	 * Gets Shipper admin page wrapper class
	 *
	 * @param string $page_sfx Page suffix to use.
	 * @param object $model Optional Shipper_Model_Stored_Options instance (used in tests).
	 *
	 * @return string
	 */
	static public function get_page_class( $page_sfx, $model = false ) {
		return self::get_wrap_class( "shipper-page-{$page_sfx}", $model );
	}

	/**
	 * Gets page wrapper class
	 *
	 * @param string $page Shipper admin page.
	 * @param object $model Optional Shipper_Model_Stored_Options instance (used in tests).
	 *
	 * @return string
	 */
	static public function get_wrap_class( $page, $model = false ) {
		if ( empty( $model ) ) {
			$model = new Shipper_Model_Stored_Options;
		}
		$cls = array( $page, 'sui-wrap' );

		if ( $model->get( Shipper_Model_Stored_Options::KEY_A11N ) ) {
			$cls[] = 'sui-color-accessible';
		}

		return join( ' ', array_map( 'sanitize_html_class', $cls ) );
	}

	/**
	 * Gets the asset URL
	 *
	 * For script/style assets, attempts resolving best possible version,
	 * according to minimization state requests.
	 *
	 * @param string $relpath Relative path to the asset.
	 *
	 * @return string|bool Full asset URL on success, (bool)false on failure
	 */
	public function get_asset( $relpath ) {
		$relpath = 'assets/' . ltrim( $relpath, '/' );

		if (
			! (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG)
			&&
			! (defined( 'WDEV_UNMINIFIED' ) && WDEV_UNMINIFIED)
		) {
			$relpath = $this->get_minified_asset_relpath( $relpath );
		}

		return plugins_url( $relpath, SHIPPER_PLUGIN_FILE );
	}

	/**
	 * Gets relative path to the minified version of the asset - if applicable
	 *
	 * @param string $relpath Relative path to the asset.
	 *
	 * @return string Minified asset version, or regular asset version if not applicable
	 */
	public function get_minified_asset_relpath( $relpath ) {
		$type = $this->get_asset_type( $relpath );
		if ( 'js' !== $type && 'css' !== $type ) { return $relpath; } // Assets not ready to be minified.

		return preg_replace(
			'/' . preg_quote( ".{$type}", '/' ) . '$/i',
			".min.{$type}",
			$relpath
		);
	}

	/**
	 * Gets asset type
	 *
	 * In this context, it actually means asset file extension, normalized.
	 *
	 * @param string $relpath Relative path to the asset.
	 *
	 * @return string
	 */
	public function get_asset_type( $relpath ) {
		return strtolower( pathinfo( $relpath, PATHINFO_EXTENSION ) );
	}

	/**
	 * Gets the update interval for Heartbeat API
	 *
	 * @return int|string Update interval
	 */
	static public function get_update_interval() {

		/**
		 * Gets the update interval for Heartbeat API
		 *
		 * @param int|string $update_interval Heartbeat update interval.
		 *
		 * @return int|string Update interval
		 */
		return apply_filters(
			'shipper_heartbeat_update_interval',
			15
		);
	}

	/**
	 * Returns FS path to shipper icon.
	 *
	 * @return string
	 */
	static public function get_shipper_icon() {
		/**
		 * Returns FS path to shipper icon.
		 *
		 * Used in tests.
		 *
		 * @param string $icon_path Path to icon.
		 *
		 * @return string
		 */
		return apply_filters(
			'shipper_assets_shipper_icon',
			plugin_dir_path( SHIPPER_PLUGIN_FILE ) . 'assets/img/anchor.svg'
		);
	}

	/**
	 * Gets icon as base64-encoded SVG
	 *
	 * @return string
	 */
	static public function get_encoded_icon() {
		$icon = self::get_shipper_icon();
		if ( ! is_readable( $icon ) ) {
			return '';
		}

		$icon = file_get_contents( $icon );
		return 'data:image/svg+xml;base64,' . base64_encode( $icon );
	}
}