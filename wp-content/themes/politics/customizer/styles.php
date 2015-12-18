<?php
/**
 * Implements styles set in the theme customizer
 *
 * @package Politics
 */

/*--------------------------------------------------------------
>>> TABLE OF CONTENTS:
----------------------------------------------------------------
1.0 Header
2.0 Home
3.0 Footer
4.0 Blog
5.0 Colors Sitewide
 --------------------------------------------------------------*/

if ( ! function_exists( 'customizer_library_demo_build_styles' ) && class_exists( 'Customizer_Library_Styles' ) ) :
/**
 * Process user options to generate CSS needed to implement the choices.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function customizer_library_demo_build_styles() {

/*--------------------------------------------------------------
 2.0 Home
--------------------------------------------------------------*/
	// Home Hero Overly Color
	$setting = 'home_overlay_color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.color-overlay:before'
			),
			'declarations' => array(
				'background-color' => $color
			)
		) );
	}

	// Home Hero Opacity Level
	$setting = 'home_opacity';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$numeral = customizer_library_sanitize_text( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.color-overlay:before'
			),
			'declarations' => array(
				'opacity' => $numeral
			)
		) );
	}

/*--------------------------------------------------------------
5.0 Colors Sitewide
--------------------------------------------------------------*/
	// Color: Headers
	$setting = 'color-headers';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.content-area h1, .content-area h2, .content-area h3, .content-area h4, .content-area h5, .content-area h6, .page-header h1'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}

	// Color: Content
	$setting = 'color-content';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.entry-content p, .comment_content p, .home-secondary-content p'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}

	// Color: Links
	$setting = 'color-content-link';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'#content a, .hero-widgets-wrap a'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}

	// Color: Link Hover
	$setting = 'color-content-link-hover';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'#content a:hover, .comment_content a:hover, .hero-widgets-wrap a:hover'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}

	// Color: Top Mini Bar
	$setting = 'color-content-top-bar';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.mini-header'
			),
			'declarations' => array(
				'background-color' => $color
			)
		) );
	}

	// Color: Body Background
	$setting = 'color-content-body-bg';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'body, .home .content-area'
			),
			'declarations' => array(
				'background-color' => $color
			)
		) );
	}

	// Color: Content Background
	$setting = 'color-content-bg';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'#page .site-content .row'
			),
			'declarations' => array(
				'background-color' => $color
			)
		) );
	}

	// Color: Comments Background
	$setting = 'color-comments-bg';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'#comments ol.comment-list .comment-content-wrap'
			),
			'declarations' => array(
				'background-color' => $color
			)
		) );
	}

}
endif;

add_action( 'customizer_library_styles', 'customizer_library_demo_build_styles' );

if ( ! function_exists( 'customizer_library_demo_styles' ) ) :
/**
 * Generates the style tag and CSS needed for the theme options.
 *
 * By using the "Customizer_Library_Styles" filter, different components can print CSS in the header.
 * It is organized this way to ensure there is only one "style" tag.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function customizer_library_demo_styles() {

	do_action( 'customizer_library_styles' );

	echo "\n<!-- Begin Custom CSS -->\n<style type=\"text/css\" id=\"politics-custom-css\">\n";

	$politics_plug_logo_height = get_theme_mod( 'politics-logo-height', customizer_library_get_default( 'politics-logo-height' ) );
	$politics_logo_width = get_theme_mod( 'politics-logo-width', customizer_library_get_default( 'politics-logo-width' ) );

	if ( $politics_plug_logo_height || $politics_logo_width ) { ?>
		.site-branding img {
			height: <?php echo esc_attr( $politics_plug_logo_height ) ?>;
			width: <?php echo esc_attr( $politics_logo_width ) ?>;
		}
	<?php }


	$home_paralax_bg = get_theme_mod( 'home_paralax_bg', customizer_library_get_default( 'home_paralax_bg' ) );
	$home_paralax_bg_color = get_theme_mod( 'home_paralax_bg_color', customizer_library_get_default( 'home_paralax_bg_color' ) );
	?>



  .home .home_paralax::before {
    background-color: <?php echo esc_attr( $home_paralax_bg_color ) ?>;
    background: url('<?php echo esc_url( $home_paralax_bg ) ?>') no-repeat center center;
    background-size: cover;
  }

	<?php

	// Echo the rules
	$css = Customizer_Library_Styles()->build();

	if ( ! empty( $css ) ) {
		echo $css;
	}
	echo "\n</style>\n<!-- End Custom CSS -->\n";
}
endif;

add_action( 'wp_head', 'customizer_library_demo_styles', 11 );
