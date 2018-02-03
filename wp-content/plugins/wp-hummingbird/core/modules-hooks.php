<?php
/**
 * Manage tweaks for modules
 */

/**
 * We check for current screen, because on the Dashboard page we need to ignore files without original_size
 * parameter. If we include them there are two notices in the error log.
 * On the Minification dedicated page we include files without sizes.
 */
//add_filter( 'wphb_minification_display_enqueued_file', 'wphb_minification_hooks_hide_jquery_switchers', 10, 3 );
function wphb_minification_hooks_hide_jquery_switchers( $display, $handle, $type ) {
	if ( 'toplevel_page_wphb' === get_current_screen()->id ) {
		if ( ( 'scripts' === $type && in_array( $handle['handle'], array( 'jquery', 'jquery-core', 'jquery-migrate' ) ) ) || ( ! isset( $handle['original_size'] ) ) ) {
			return false;
		}
	} else {
		if ( 'scripts' === $type && in_array( $handle['handle'], array( 'jquery', 'jquery-core', 'jquery-migrate' ) ) ) {
			return false;
		}
	}

	return $display;
}

add_filter( 'wphb_combine_resource', 'wphb_minification_combine_jquery', 150, 3 );
add_filter( 'wphb_minify_resource', 'wphb_minification_combine_jquery', 150, 3 );
function wphb_minification_combine_jquery( $combine, $handle, $type ) {
	if ( ( 'scripts' === $type && in_array( $handle, array( 'jquery', 'jquery-core', 'jquery-migrate' ) ) ) ) {
		return false;
	}
	return $combine;
}