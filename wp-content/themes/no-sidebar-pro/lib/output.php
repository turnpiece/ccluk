<?php
/* 
 * Adds the required CSS to the front end.
 */

add_action( 'wp_enqueue_scripts', 'ns_css' );
/**
* Checks the settings for the link color color, accent color, and header
* If any of these value are set the appropriate CSS is output
*
* @since 1.0.0
*/
function ns_css() {

	$handle  = defined( 'CHILD_THEME_NAME' ) && CHILD_THEME_NAME ? sanitize_title_with_dashes( CHILD_THEME_NAME ) : 'child-theme';

	$color_link = get_theme_mod( 'ns_link_color', ns_customizer_get_default_link_color() );
	$color_accent = get_theme_mod( 'ns_accent_color', ns_customizer_get_default_accent_color() );

	$css = '';

	$css .= ( ns_customizer_get_default_link_color() !== $color_link ) ? sprintf( '
		a:hover,
		a:focus,
		.entry-title a:hover,
		.entry-title a:focus,
		.js nav button:focus,
		.js .menu-toggle:focus,
		.pagination a:hover,
		.pagination a:focus {
			color: %1$s;
		}
		@media only screen and (max-width: 720px) {

			.nav-primary li.highlight > a:hover,
			.nav-primary li.menu-item.highlight > a:focus {
				color: %1$s;
			}

		}
		', $color_link ) : '';

	$css .= ( ns_customizer_get_default_accent_color() !== $color_accent ) ? sprintf( '
		button:hover,
		button:focus,
		input:hover[type="button"],
		input:focus[type="button"],
		input:hover[type="reset"],
		input:focus[type="reset"],
		input:hover[type="submit"],
		input:focus[type="submit"],
		.button:hover,
		.button:focus,
		.content .widget .textwidget a.button:hover,
		.content .widget .textwidget a.button:focus,
		.entry-content a.button:hover,
		.entry-content a.button:focus,
		.entry-content a.more-link:hover,
		.entry-content a.more-link:focus,
		.footer-widgets,
		.nav-primary li.highlight > a:hover,
		.nav-primary li.highlight > a:focus {
			background-color: %1$s;
		}

		button:hover,
		button:focus,
		input:hover[type="button"],
		input:focus[type="button"],
		input:hover[type="reset"],
		input:focus[type="reset"],
		input:hover[type="submit"],
		input:focus[type="submit"],
		.button:hover,
		.button:focus,
		.content .widget .textwidget a.button:hover,
		.content .widget .textwidget a.button:focus,
		.entry-content a.button:hover,
		.entry-content a.button:focus,
		.entry-content a.more-link:hover,
		.entry-content a.more-link:focus,
		.nav-primary li.highlight > a:hover,
		.nav-primary li.highlight > a:focus {
			border-color: %1$s;
		}
		', $color_accent ) : '';

	if( $css ){
		wp_add_inline_style( $handle, $css );
	}

}
