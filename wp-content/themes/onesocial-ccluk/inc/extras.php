<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package CCLUK
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function ccluk_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	$ccluk_sticky_header = get_theme_mod( 'ccluk_sticky_header_disable' );
	if ( $ccluk_sticky_header == '' ) {
		$classes[] = 'sticky-header';
	} else {
        $classes[] = 'no-sticky-header';
    }
    // ccluk_header_transparent
    if ( is_front_page() && is_page_template( 'template-frontpage.php' ) ) {
        if ( get_theme_mod( 'ccluk_header_transparent' ) ) {
            $classes[] = 'header-transparent';
        }
    }

    if ( is_customize_preview() ) {
        $classes[ ] = 'is-customize-preview';
    }

	return $classes;
}
add_filter( 'body_class', 'ccluk_body_classes' );


if ( ! function_exists( 'ccluk_custom_excerpt_length' ) ) :
/**
 * Custom excerpt length for the theme
 */
function ccluk_custom_excerpt_length( $length ) {
	return 30;
}
add_filter( 'excerpt_length', 'ccluk_custom_excerpt_length', 999 );
endif;


if ( ! function_exists( 'ccluk_new_excerpt_more' ) ) :
/**
 * Remove […] string using Filters
 */
function ccluk_new_excerpt_more( $more ) {
	return ' ...';
}
add_filter('excerpt_more', 'ccluk_new_excerpt_more');
endif;


/**
 * Get media from a variable
 *
 * @param array $media
 * @return false|string
 */
if ( ! function_exists( 'ccluk_get_media_url' ) ) {
    function ccluk_get_media_url($media = array())
    {
        $media = wp_parse_args($media, array('url' => '', 'id' => ''));
        $url = '';
        if ($media['id'] != '') {
            $url = wp_get_attachment_url($media['id']);
        }
        if ($url == '' && $media['url'] != '') {
            $url = $media['url'];
        }
        return $url;
    }
}


/**
 * Get theme actions required
 *
 * @return array|mixed|void
 */
function ccluk_get_actions_required( ) {

    $actions = array();
    $front_page = get_option( 'page_on_front' );
    $actions['page_on_front'] = 'dismiss';
    $actions['page_template'] = 'dismiss';
    if ( $front_page <= 0  ) {
        $actions['page_on_front'] = 'active';
        $actions['page_template'] = 'active';

    } else {
        if ( get_post_meta( $front_page, '_wp_page_template', true ) == 'template-frontpage.php' ) {
            $actions['page_template'] = 'dismiss';
        } else {
            $actions['page_template'] = 'active';
        }
    }

    $actions = apply_filters( 'ccluk_get_actions_required', $actions );
    $actions_dismiss =  get_option( 'ccluk_actions_dismiss' );

    if (  $actions_dismiss && is_array( $actions_dismiss ) ) {
        foreach ( $actions_dismiss as $k => $v ) {
            if ( isset ( $actions[ $k ] ) ) {
                $actions[ $k ] = 'dismiss';
            }
        }
    }

    return $actions;
}

add_action('switch_theme', 'ccluk_reset_actions_required');
function ccluk_reset_actions_required () {
    delete_option('ccluk_actions_dismiss');
}
