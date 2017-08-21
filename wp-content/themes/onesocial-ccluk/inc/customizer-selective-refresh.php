<?php

/**
 * Load section template
 *
 * @since 1.2.1
 *
 * @param $template_names
 * @return string
 */
function ccluk_customizer_load_template( $template_names ){
    $located = '';

    $is_child =  get_stylesheet_directory() != get_template_directory() ;
    foreach ( (array) $template_names as $template_name ) {
        if (  !$template_name )
            continue;

        if ( $is_child && file_exists( get_stylesheet_directory() . '/' . $template_name ) ) {  // Child them
            $located = get_stylesheet_directory() . '/' . $template_name;
            break;

        } elseif ( defined( 'ONEPRESS_PLUS_PATH' ) && file_exists( ONEPRESS_PLUS_PATH  . $template_name ) ) { // Check part in the plugin
            $located = ONEPRESS_PLUS_PATH . $template_name;
            break;
        } elseif ( file_exists( get_template_directory() . '/' . $template_name) ) { // current_theme
            $located =  get_template_directory() . '/' . $template_name;
            break;
        }
    }
    
    return $located;
}

/**
 * Render customizer section
 * @since 1.2.1
 *
 * @param $section_tpl
 * @param array $section
 * @return string
 */
function ccluk_get_customizer_section_content( $section_tpl, $section = array() ){
    ob_start();
    $GLOBALS['ccluk_is_selective_refresh'] = true;
    $file = ccluk_customizer_load_template( $section_tpl );
    if ( $file ) {
        include $file;
    }
    $content = ob_get_clean();
    return trim( $content );
}


/**
 * Add customizer selective refresh
 *
 * @since 1.2.1
 *
 * @param $wp_customize
 */
function ccluk_customizer_partials( $wp_customize ) {

    // Abort if selective refresh is not available.
    if ( ! isset( $wp_customize->selective_refresh ) ) {
        return;
    }

    $selective_refresh_keys = array(
        // section features
        array(
            'id' => 'features',
            'selector' => '.section-features',
            'settings' => array(
                'ccluk_features_boxes',
                'ccluk_features_title',
                'ccluk_features_subtitle',
                'ccluk_features_desc',
                'ccluk_features_layout',
            ),
        ),

        // section services
        array(
            'id' => 'services',
            'selector' => '.section-services',
            'settings' => array(
                'ccluk_services',
                'ccluk_services_title',
                'ccluk_services_subtitle',
                'ccluk_services_desc',
                'ccluk_service_layout',
            ),
        ),

        // section gallery
        'gallery' => array(
            'id' => 'gallery',
            'selector' => '.section-gallery',
            'settings' => array(
                'ccluk_gallery_source',

                'ccluk_gallery_title',
                'ccluk_gallery_subtitle',
                'ccluk_gallery_desc',
                'ccluk_gallery_source_page',
                'ccluk_gallery_layout',
                'ccluk_gallery_display',
                'ccluk_g_number',
                'ccluk_g_row_height',
                'ccluk_g_col',
            ),
        ),

        // section news
        array(
            'id' => 'news',
            'selector' => '.section-news',
            'settings' => array(
                'ccluk_news_title',
                'ccluk_news_subtitle',
                'ccluk_news_desc',
                'ccluk_news_number',
                'ccluk_news_more_link',
                'ccluk_news_more_text',
            ),
        ),

        // section contact
        array(
            'id' => 'contact',
            'selector' => '.section-contact',
            'settings' => array(
                'ccluk_contact_title',
                'ccluk_contact_subtitle',
                'ccluk_contact_desc',
                'ccluk_contact_cf7',
                'ccluk_contact_cf7_disable',
                'ccluk_contact_text',
                'ccluk_contact_address_title',
                'ccluk_contact_address',
                'ccluk_contact_phone',
                'ccluk_contact_email',
                'ccluk_contact_fax',
            ),
        ),

        // section counter
        array(
            'id' => 'counter',
            'selector' => '.section-counter',
            'settings' => array(
                'ccluk_counter_boxes',
                'ccluk_counter_title',
                'ccluk_counter_subtitle',
                'ccluk_counter_desc',
            ),
        ),
        // section videolightbox
        array(
            'id' => 'videolightbox',
            'selector' => '.section-videolightbox',
            'settings' => array(
                'ccluk_videolightbox_title',
                'ccluk_videolightbox_url',
            ),
        ),

        // Section about
        array(
            'id' => 'about',
            'selector' => '.section-about',
            'settings' => array(
                'ccluk_about_boxes',
                'ccluk_about_title',
                'ccluk_about_subtitle',
                'ccluk_about_desc',
                'ccluk_about_content_source',
            ),
        ),

        // Section team
        array(
            'id' => 'team',
            'selector' => '.section-team',
            'settings' => array(
                'ccluk_team_members',
                'ccluk_team_title',
                'ccluk_team_subtitle',
                'ccluk_team_desc',
                'ccluk_team_layout',
            ),
        ),
    );

    $selective_refresh_keys = apply_filters( 'ccluk_customizer_partials_selective_refresh_keys', $selective_refresh_keys );

    foreach ( $selective_refresh_keys as $section ) {
        foreach ( $section['settings'] as $key ) {
            if ( $wp_customize->get_setting( $key ) ) {
                $wp_customize->get_setting( $key )->transport = 'postMessage';
            }
        }

        $wp_customize->selective_refresh->add_partial( 'section-'.$section['id'] , array(
            'selector' => $section['selector'],
            'settings' => $section['settings'],
            'render_callback' => 'ccluk_selective_refresh_render_section_content',
        ));
    }

    $wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
    $wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
    $wp_customize->get_setting( 'ccluk_hide_sitetitle' )->transport = 'postMessage';
    $wp_customize->get_setting( 'ccluk_hide_tagline' )->transport = 'postMessage';
    $wp_customize->selective_refresh->add_partial( 'header_brand', array(
        'selector' => '.site-header .site-branding',
        'settings' => array( 'blogname', 'blogdescription', 'ccluk_hide_sitetitle', 'ccluk_hide_tagline' ),
        'render_callback' => 'ccluk_site_logo',
    ) );

    // Footer social heading
    $wp_customize->selective_refresh->add_partial( 'ccluk_social_footer_title', array(
        'selector' => '.footer-social .follow-heading',
        'settings' => array( 'ccluk_social_footer_title' ),
        'render_callback' => 'ccluk_selective_refresh_social_footer_title',
    ) );
    // Footer social icons
    $wp_customize->selective_refresh->add_partial( 'ccluk_social_profiles', array(
        'selector' => '.footer-social .footer-social-icons',
        'settings' => array( 'ccluk_social_profiles' ),
        'render_callback' =>  'ccluk_get_social_profiles',
    ) );

    // Footer New letter heading
    $wp_customize->selective_refresh->add_partial( 'ccluk_newsletter_title', array(
        'selector' => '.footer-subscribe .follow-heading',
        'settings' => array( 'ccluk_newsletter_title' ),
        'render_callback' => 'ccluk_selective_refresh_newsletter_title',
    ) );

}
add_action( 'customize_register', 'ccluk_customizer_partials', 50 );



/**
 * Selective render content
 *
 * @param $partial
 * @param array $container_context
 */
function ccluk_selective_refresh_render_section_content( $partial, $container_context = array() ) {
    $tpl = 'section-parts/'.$partial->id.'.php';
    $GLOBALS['ccluk_is_selective_refresh'] = true;
    $file = ccluk_customizer_load_template( $tpl );
    if ( $file ) {
        include $file;
    }
}

function ccluk_selective_refresh_social_footer_title(){
    return get_theme_mod( 'ccluk_social_footer_title' );
}

function ccluk_selective_refresh_newsletter_title(){
    return get_theme_mod( 'ccluk_newsletter_title' );
}
