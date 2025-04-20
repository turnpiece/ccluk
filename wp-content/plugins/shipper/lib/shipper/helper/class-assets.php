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
	public static function get_page_class( $page_sfx, $model = false ) {
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
	public static function get_wrap_class( $page, $model = false ) {
		if ( empty( $model ) ) {
			$model = new Shipper_Model_Stored_Options();
		}
		$cls = array( $page, 'sui-wrap' );

		if ( $model->get( Shipper_Model_Stored_Options::KEY_A11N ) ) {
			$cls[] = 'sui-color-accessible';
		}

		if ( self::is_branding_hidden() ) {
			$cls[] = 'shipper-whitelabel';
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
			! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			&&
			! ( defined( 'WDEV_UNMINIFIED' ) && WDEV_UNMINIFIED )
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
		if ( 'js' !== $type && 'css' !== $type ) {
			return $relpath; } // Assets not ready to be minified.

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
	public static function get_update_interval() {

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
	public static function get_shipper_icon() {
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
			plugin_dir_path( SHIPPER_PLUGIN_FILE ) . 'assets/img/shipper-admin-menu-icon.svg'
		);
	}

	/**
	 * Gets icon as base64-encoded SVG
	 *
	 * @return string
	 */
	public static function get_encoded_icon() {
		$icon = self::get_shipper_icon();
		if ( ! is_readable( $icon ) ) {
			return '';
		}

		$icon = file_get_contents( $icon ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return 'data:image/svg+xml;base64,' . base64_encode( $icon ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Checks whether the WPMU DEV branding is hidden
	 *
	 * @return bool
	 */
	public static function is_branding_hidden() {
		return (bool) apply_filters(
			'wpmudev_branding_hide_branding',
			false
		);
	}

	/**
	 * Gets custom hero image link
	 *
	 * @return string
	 */
	public static function get_custom_hero_image() {
		return apply_filters( 'wpmudev_branding_hero_image', '' );
	}

	/**
	 * Checks whether we have a custom hero image
	 *
	 * @return bool
	 */
	public static function has_custom_hero_image() {
		return ! empty( self::get_custom_hero_image() );
	}

	/**
	 * Gets custom hero image full HTML markup
	 *
	 * Or empty, if we're not overriding the branding.
	 *
	 * @return string
	 */
	public static function get_custom_hero_image_markup() {
		if ( ! self::has_custom_hero_image() ) {
			return '';
		}
		$link = self::get_custom_hero_image();
		return '<img class="shipper-branding-hero" src="' . esc_url( $link ) . '" />';
	}

	/**
	 * Gets custom footer text link
	 *
	 * @param mixed $default String by default, or (bool)false for check.
	 *
	 * @return string
	 */
	public static function get_custom_footer( $default = '' ) {
		return apply_filters( 'wpmudev_branding_footer_text', $default );
	}

	/**
	 * Checks whether we have a custom footer text
	 *
	 * @return bool
	 */
	public static function has_custom_footer() {
		return false !== self::get_custom_footer( false );
	}

	/**
	 * Checks whether we're hiding documentation links
	 *
	 * @return true
	 */
	public static function has_docs_links() {
		return ! apply_filters( 'wpmudev_branding_hide_doc_link', false );
	}

	/**
	 * Returns the footer text
	 * This is either the custom footer (if set and used), or our default
	 * branding footer.
	 *
	 * @return string Footer (HTML)
	 */
	public static function get_footer_text() {
		if ( self::has_custom_footer() ) {
			return self::get_custom_footer();
		}
		return sprintf(
			/* translators: %s: love icon*/
			__( 'Made with %s by WPMU DEV', 'shipper' ),
			'<i class="sui-icon-heart" aria-hidden="true"></i>'
		);
	}

	/**
	 * Get shipper image relative|absolute path
	 *
	 * @since 1.2.6
	 *
	 * @param string $name name of the image.
	 * @param bool   $abs_path Whether to return absolute or relative path.
	 *
	 * @return string
	 */
	public static function get_image( $name, $abs_path = false ) {
		$plugin_url_func = $abs_path ? 'plugin_dir_path' : 'plugin_dir_url';

		return apply_filters(
			'shipper_assets_get_image',
			$plugin_url_func( SHIPPER_PLUGIN_FILE ) . 'assets/img/' . $name,
			$name
		);
	}
}