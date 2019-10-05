<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package CCLUK
 */

/**
 * Display header brand
 * @since 1.2.1
 */
function ccluk_site_logo(){
    $classes = array();
    $html = '' ;
    $classes['logo'] = 'no-logo-img';

    if ( function_exists( 'has_custom_logo' ) ) {
        if ( has_custom_logo()) {
            $classes['logo'] = 'has-logo-img';
            $html .= '<div class="site-logo-div">';
            $html .= get_custom_logo();
            $html .= '</div>';
        }
    }

    $hide_sitetile = get_theme_mod( 'ccluk_hide_sitetitle',  0 );
    $hide_tagline  = get_theme_mod( 'ccluk_hide_tagline', 0 );

    if ( ! $hide_sitetile ) {
        $classes['title'] = 'has-title';
        if ( is_front_page() && !is_home() ) {
            $html .= '<h1 class="site-title"><a class="site-text-logo" href="' . esc_url(home_url('/')) . '" rel="home">' . get_bloginfo('name') . '</a></h1>';
        } else {
            $html .= '<p class="site-title"><a class="site-text-logo" href="' . esc_url(home_url('/')) . '" rel="home">' . get_bloginfo('name') . '</a></p>';
        }
    }

    if ( ! $hide_tagline ) {
        $description = get_bloginfo( 'description', 'display' );
        if ( $description || is_customize_preview() ) {
            $classes['desc'] = 'has-desc';
            $html .= '<p class="site-description">'.$description.'</p>';
        }
    } else {
        $classes['desc'] = 'no-desc';
    }

    echo '<div class="site-brand-inner '.esc_attr( join( ' ', $classes ) ).'">'.$html.'</div>';
}

if ( ! function_exists( 'ccluk_posted_on' ) ) {
    /**
     * Prints HTML with meta information for the current post-date/time and author.
     */
    function ccluk_posted_on()
    {
        $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
        if (get_the_time('U') !== get_the_modified_time('U')) {
            $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated hide" datetime="%3$s">%4$s</time>';
        }

        $time_string = sprintf($time_string,
            esc_attr(get_the_date('c')),
            esc_html(get_the_date()),
            esc_attr(get_the_modified_date('c')),
            esc_html(get_the_modified_date())
        );

        $posted_on = sprintf(
            esc_html_x('Posted on %s', 'post date', 'onesocial'),
            '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string . '</a>'
        );

        $byline = sprintf(
            esc_html_x('by %s', 'post author', 'onesocial'),
            '<span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a></span>'
        );

        echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.

    }
}

if ( ! function_exists( 'ccluk_entry_footer' ) ) {
    /**
     * Prints HTML with meta information for the categories, tags and comments.
     */
    function ccluk_entry_footer()
    {
        // Hide category and tag text for pages.
        if ('post' === get_post_type()) {
            /* translators: used between list items, there is a space after the comma */
            $categories_list = get_the_category_list(esc_html__(', ', 'onesocial'));
            if ($categories_list && ccluk_categorized_blog()) {
                printf('<span class="cat-links">' . esc_html__('Posted in %1$s', 'onesocial') . '</span>', $categories_list); // WPCS: XSS OK.
            }

            /* translators: used between list items, there is a space after the comma */
            $tags_list = get_the_tag_list('', esc_html__(', ', 'onesocial'));
            if ($tags_list) {
                printf('<span class="tags-links">' . esc_html__('Tagged %1$s', 'onesocial') . '</span>', $tags_list); // WPCS: XSS OK.
            }
        }

        if (!is_single() && !post_password_required() && (comments_open() || get_comments_number())) {
            echo '<span class="comments-link">';
            comments_popup_link(esc_html__('Leave a comment', 'onesocial'), esc_html__('1 Comment', 'onesocial'), esc_html__('% Comments', 'onesocial'));
            echo '</span>';
        }

    }
}

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function ccluk_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'ccluk_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,

			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'ccluk_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so ccluk_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so ccluk_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in ccluk_categorized_blog.
 */
function ccluk_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'ccluk_categories' );
}
add_action( 'edit_category', 'ccluk_category_transient_flusher' );
add_action( 'save_post',     'ccluk_category_transient_flusher' );


if ( ! function_exists( 'ccluk_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own ccluk_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @return void
 */
function ccluk_comment( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    switch ( $comment->comment_type ) :
        case 'pingback' :
        case 'trackback' :
        // Display trackbacks differently than normal comments.
    ?>
    <li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
        <p><?php _e( 'Pingback:', 'onesocial' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'onesocial' ), '<span class="edit-link">', '</span>' ); ?></p>
    <?php
            break;
        default :
        // Proceed with normal comments.
        global $post;
    ?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
        <article id="comment-<?php comment_ID(); ?>" class="comment clearfix">

            <?php echo get_avatar( $comment, 60 ); ?>

            <div class="comment-wrapper">

                <header class="comment-meta comment-author vcard">
                    <?php
                        printf( '<cite><b class="fn">%1$s</b> %2$s</cite>',
                            get_comment_author_link(),
                            // If current post author is also comment author, make it known visually.
                            ( $comment->user_id === $post->post_author ) ? '<span>' . __( 'Post author', 'onesocial' ) . '</span>' : ''
                        );
                        printf( '<a class="comment-time" href="%1$s"><time datetime="%2$s">%3$s</time></a>',
                            esc_url( get_comment_link( $comment->comment_ID ) ),
                            get_comment_time( 'c' ),
                            /* translators: 1: date, 2: time */
                            sprintf( __( '%1$s', 'onesocial' ), get_comment_date() )
                        );
                        comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'onesocial' ), 'after' => '', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) );
                        edit_comment_link( __( 'Edit', 'onesocial' ), '<span class="edit-link">', '</span>' );
                    ?>
                </header><!-- .comment-meta -->

                <?php if ( '0' == $comment->comment_approved ) : ?>
                    <p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'onesocial' ); ?></p>
                <?php endif; ?>

                <div class="comment-content entry-content">
                    <?php comment_text(); ?>
                    <?php  ?>
                </div><!-- .comment-content -->

            </div><!--/comment-wrapper-->

        </article><!-- #comment-## -->
    <?php
        break;
    endswitch; // end comment_type check
}
endif;

if ( ! function_exists( 'ccluk_get_social_profiles' ) ) {
    /**
     * Get social profiles
     *
     * @since 1.1.4
     * @return bool|array
     */
    function ccluk_get_social_profiles()
    {
        $array = get_theme_mod('ccluk_social_profiles');
        if (is_string($array)) {
            $array = json_decode($array, true);
        }
        $html = '';
        if (!empty($array) && is_array($array)) {
            foreach ($array as $k => $v) {
                $array[$k] = wp_parse_args($v, array(
                    'network' => '',
                    'icon' => '',
                    'link' => '',
                ));

                //Get/Set social icons
                // If icon isset
                $icons = array();
                $array[$k]['icon'] = trim($array[$k]['icon']);
                if ($array[$k]['icon'] != '' && strpos($array[$k]['icon'], 'fa-') !== 0) {
                    $icons['fa-' . $array[$k]['icon']] = 'fa-' . $array[$k]['icon'];
                } else {
                    $icons[$array[$k]['icon']] = $array[$k]['icon'];
                }
                $network = ($array[$k]['network']) ? sanitize_title($array[$k]['network']) : false;
                if ($network) {
                    $icons['fa-' . $network] = 'fa-' . $network;
                }

                $array[$k]['icon'] = join(' ', $icons);

            }
        }

        foreach ( (array) $array as $s) {
            if ($s['icon'] != '') {
                $html .= '<a target="_blank" href="' . $s['link'] . '" title="' . esc_attr($s['network']) . '"><i class="fa ' . esc_attr($s['icon']) . '"></i></a>';
            }
        }

        return $html;
    }
}

if ( ! function_exists( 'ccluk_get_section_gallery_data' ) ) {
    /**
     * Get Gallery data
     *
     * @since 1.2.6
     *
     * @return array
     */
    function ccluk_get_section_gallery_data()
    {

        $source = 'page'; // get_theme_mod( 'ccluk_gallery_source' );
        if( has_filter( 'ccluk_get_section_gallery_data' ) ) {
            $data =  apply_filters( 'ccluk_get_section_gallery_data', false );
            return $data;
        }

        $data = array();

        switch ( $source ) {
            default:
                $page_id = get_theme_mod( 'ccluk_gallery_source_page' );
                $images = '';
                if ( $page_id ) {
                    $gallery = get_post_gallery( $page_id , false );
                    if ( $gallery ) {
                        $images = $gallery['ids'];
                    }
                }

                $image_thumb_size = apply_filters( 'ccluk_gallery_page_img_size', 'ccluk-small' );

                if ( ! empty( $images ) ) {
                    $images = explode( ',', $images );
                    foreach ( $images as $post_id ) {
                        $post = get_post( $post_id );
                        if ( $post ) {
                            $img_thumb = wp_get_attachment_image_src($post_id, $image_thumb_size );
                            if ($img_thumb) {
                                $img_thumb = $img_thumb[0];
                            }

                            $img_full = wp_get_attachment_image_src( $post_id, 'full' );
                            if ($img_full) {
                                $img_full = $img_full[0];
                            }

                            if ( $img_thumb && $img_full ) {
                                $data[ $post_id ] = array(
                                    'id'        => $post_id,
                                    'thumbnail' => $img_thumb,
                                    'full'      => $img_full,
                                    'title'     => $post->post_title,
                                    'content'   => $post->post_content,
                                );
                            }
                        }
                    }
                }
            break;
        }

        return $data;

    }
}

/**
 * Generate HTML content for gallery items.
 *
 * @since 1.2.6
 *
 * @param $data
 * @param bool|true $inner
 * @return string
 */
function ccluk_gallery_html( $data, $inner = true, $size = 'thumbnail' ) {
    $max_item = get_theme_mod( 'ccluk_g_number', 10 );
    $html = '';
    if ( ! is_array( $data ) ) {
        return $html;
    }
    $n = count( $data );
    if ( $max_item > $n ) {
        $max_item =  $n;
    }
    $i = 0;
    while( $i < $max_item ){
        $photo = current( $data );
        $i ++ ;
        if ( $size == 'full' ) {
            $thumb = $photo['full'];
        } else {
            $thumb = $photo['thumbnail'];
        }

        $html .= '<a href="'.esc_attr( $photo['full'] ).'" class="g-item" title="'.esc_attr( wp_strip_all_tags( $photo['title'] ) ).'">';
        if ( $inner ) {
            $html .= '<span class="inner">';
                $html .= '<span class="inner-content">';
                $html .= '<img src="'.esc_url( $thumb ).'" alt="">';
                $html .= '</span>';
            $html .= '</span>';
        } else {
            $html .= '<img src="'.esc_url( $thumb ).'" alt="">';
        }

        $html .= '</a>';
        next( $data );
    }
    reset( $data );

    return $html;
}


/**
 * Generate Gallery HTML
 *
 * @since 1.2.6
 * @param bool|true $echo
 * @return string
 */
function ccluk_gallery_generate( $echo = true ){

    $div = '';

    $data = ccluk_get_section_gallery_data();
    $display_type = get_theme_mod( 'ccluk_gallery_display', 'grid' );
    $lightbox = get_theme_mod( 'ccluk_g_lightbox', 1 );
    $class = '';
    if ( $lightbox ) {
        $class = ' enable-lightbox ';
    }
    $col = absint( get_theme_mod( 'ccluk_g_col', 4 ) );
    if ( $col <= 0 ) {
        $col = 4;
    }
    switch( $display_type ) {
        case 'masonry':
            $html = ccluk_gallery_html( $data );
            if ( $html ) {
                $div .= '<div data-col="'.$col.'" class="g-zoom-in gallery-masonry '.$class.' gallery-grid g-col-'.$col.'">';
                $div .= $html;
                $div .= '</div>';
            }
            break;
        case 'carousel':
            $html = ccluk_gallery_html( $data );
            if ( $html ) {
                $div .= '<div data-col="'.$col.'" class="g-zoom-in gallery-carousel'.$class.'">';
                $div .= $html;
                $div .= '</div>';
            }
            break;
        case 'slider':
            $html = ccluk_gallery_html( $data , true , 'full' );
            if ( $html ) {
                $div .= '<div class="gallery-slider'.$class.'">';
                $div .= $html;
                $div .= '</div>';
            }
            break;
        case 'justified':
            $html = ccluk_gallery_html( $data, false );
            if ( $html ) {
                $gallery_spacing = absint( get_theme_mod( 'ccluk_g_spacing', 20 ) );
                $div .= '<div data-spacing="'.$gallery_spacing.'" class="g-zoom-in gallery-justified'.$class.'">';
                $div .= $html;
                $div .= '</div>';
            }
            break;
        default: // grid
            $html = ccluk_gallery_html( $data );
            if ( $html ) {
                $div .= '<div class="gallery-grid g-zoom-in '.$class.' g-col-'.$col .'">';
                $div .= $html;
                $div .= '</div>';
            }
            break;
    }

    if ( $echo ) {
        echo $div;
    } else {
        return $div;
    }

}

if ( ! function_exists( 'ccluk_footer_site_info' ) ) {
    /**
     * Add Copyright and Credit text to footer
     * @since 1.1.3
     */
    function ccluk_footer_site_info()
    {
        ?>
        <?php printf(esc_html__('Copyright %1$s %2$s %3$s', 'onesocial'), '&copy;', esc_attr(date('Y')), esc_attr(get_bloginfo())); ?>
        <span class="sep"> &ndash; </span>
        <?php printf(esc_html__('%1$s theme by %2$s', 'onesocial'), '<a href="' . esc_url('https://www.famethemes.com/themes/onepress', 'onesocial') . '">CCLUK</a>', 'FameThemes'); ?>
        <?php
    }
}
add_action( 'ccluk_footer_site_info', 'ccluk_footer_site_info' );


/**
 * Breadcrumb NavXT Compatibility.
 */
function ccluk_breadcrumb() {
	if ( function_exists('bcn_display') ) {
        ?>
        <div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
            <div class="container">
                <?php bcn_display(); ?>
            </div>
        </div>
        <?php
	}
}

if ( ! function_exists( 'ccluk_is_selective_refresh' ) ) {
    function ccluk_is_selective_refresh()
    {
        return isset($GLOBALS['ccluk_is_selective_refresh']) && $GLOBALS['ccluk_is_selective_refresh'] ? true : false;
    }
}
