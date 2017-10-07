<?php
/**
 * Integration with Avada.
 *
 * Detects if the js compiler is turned on in Avada. If it is and user is using minification, a notice is
 * shown, offering to disable the compiler.
 *
 * @package Hummingbird
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dismiss = isset( $_GET['wphb-avada-dismiss'] ) ? absint( $_GET['wphb-avada-dismiss'] ) : false;
if ( $dismiss ) {
	check_admin_referer( 'wphb-avada-notice-dismiss' );
	update_user_meta( get_current_user_id(), 'wphb-avada-notice-dismissed', true );

	$redirect = remove_query_arg( array( 'wphb-avada-dismiss', '_wpnonce' ) );
	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Check that Avada theme is used
 *
 * @return bool
 * @since 1.5.0
 */
function wphb_et_avada_theme_active() {
	$theme = wp_get_theme();
	return ( 'avada' === strtolower( $theme ) || 'avada' === strtolower( $theme->get_template() ) );
}

/**
 * Show notice
 *
 * @since 1.5.0
 */
function wphb_avada_compiler_notice() {
	$module = wphb_get_module( 'minify' );
	if ( ! $module->is_active() ) {
		return;
	}

	// Only run on HB, Avada pages and Dashboard.
	$hb_pages = array(
		'dashboard',
		'appearance_page_avada_options',
		'toplevel_page_wphb',
		'hummingbird_page_wphb-performance',
		'hummingbird_page_wphb-minification',
		'hummingbird_page_wphb-caching',
		'hummingbird_page_wphb-gzip',
		'hummingbird_page_wphb-uptime',
		'toplevel_page_wphb-network',
		'hummingbird_page_wphb-performance-network',
		'hummingbird_page_wphb-minification-network',
		'hummingbird_page_wphb-caching-network',
		'hummingbird_page_wphb-gzip-network',
		'hummingbird_page_wphb-uptime-network',
	);
	if ( ! in_array( get_current_screen()->id, $hb_pages, true ) ) {
		return;
	}

	$dismissed = get_user_meta( get_current_user_id(), 'wphb-avada-notice-dismissed' );
	if ( $dismissed ) {
		return;
	}

	$settings_url = admin_url( 'themes.php?page=avada_options' );
	$dismiss_url = wp_nonce_url( add_query_arg( 'wphb-avada-dismiss', true ), 'wphb-avada-notice-dismiss' );
	?>
	<div class="notice notice-info wphb-notice is-dismissible">
		<p>
			<?php esc_html_e( 'JS Compiler detected in Avada Theme. For Hummingbird minification settings to work correctly, it is recommended you disable it in Advanced - Dynamic CSS & JS.', 'wphb' ); ?>
		</p>
		<p>
			<a class="button button-ghost" href="<?php echo esc_url( $settings_url ); ?>"><?php esc_html_e( 'Update settings', 'wphb' ); ?></a>
			<a class="button" href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'Do not show again', 'wphb' ); ?></a>
		</p>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wphb' ); ?></span></button>
	</div>
	<?php

}

/**
 * Check to see if JS Compiler is enabled in Avada
 *
 * @since 1.5.0
 */
if ( wphb_et_avada_theme_active() ) {
	if ( ! function_exists( 'wphb_get_module' ) ) {
		include_once( wphb_plugin_dir() . 'helpers/wp-hummingbird-helpers-modules.php' );
	}
	$fusion = get_option( 'fusion_options' );
	if ( '1' === $fusion['js_compiler'] ) {
		add_action( 'admin_notices', 'wphb_avada_compiler_notice' );
	}
}