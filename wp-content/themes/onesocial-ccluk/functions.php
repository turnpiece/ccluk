<?php
/**
 * @package OneSocial Child Theme
 * The parent theme functions are located at /onesocial/buddyboss-inc/theme-functions.php
 * Add your own functions in this file.
 */

define( 'CCLUK_DEBUGGING', false );

/**
 * Add image size for posts
 *
 */
add_image_size( 'ccluk-medium', 750, 1000, false );
add_image_size( 'ccluk-hero', 1200, 800, true );
add_image_size( 'ccluk-feature', 580, 387, true );

/**
 * Customizer additions.
 */
require get_stylesheet_directory() . '/inc/customizer.php';

// BP custom text
load_plugin_textdomain( 'buddypress', FALSE, get_stylesheet_directory() . '/languages/buddypress-en_GB.mo' );

// Category archives to include news posts
function ccluk_show_cpt_archives( $query ) {
    if( is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) {
        $query->set( 
            'post_type', 
            array(
                'post', 'ccluk_news'
            )
        );
        return $query;
    }
}
add_filter( 'pre_get_posts', 'ccluk_show_cpt_archives' );

/*
 * Override default home page title
 *
 */
function ccluk_override_post_title($title){

    if (is_front_page()) {

        $sep = apply_filters( 'document_title_separator', '-' );

        $title = implode( " $sep ", array( get_bloginfo( 'name', 'display' ), get_bloginfo( 'description', 'display' ) ) );
        $title = wptexturize( $title );
        $title = convert_chars( $title );
        $title = esc_html( $title );
        $title = capital_P_dangit( $title );
    }

    return $title;
}
add_filter('pre_get_document_title', 'ccluk_override_post_title', 99);

/**
 * Sets up theme defaults
 *
 * @since OneSocial Child Theme 1.0.0
 */
function ccluk_theme_setup()
{
    /**
     * Makes child theme available for translation.
     * Translations can be added into the /languages/ directory.
     * Read more at: http://www.buddyboss.com/tutorials/language-translations/
     */

    // Translate text from the PARENT theme.
    load_theme_textdomain( 'onesocial', get_stylesheet_directory() . '/languages' );

    // Translate text from the CHILD theme only.
    // Change 'onesocial' instances in all child theme files to 'ccluk_theme'.
    // load_theme_textdomain( 'ccluk_theme', get_stylesheet_directory() . '/languages' );

    // add class to front page
    if (is_front_page()) {
        add_filter( 'body_class', function( $classes ) {
            $classes[] = 'front-page';
            return $classes;
        });
    }

}
add_action( 'after_setup_theme', 'ccluk_theme_setup' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since OneSocial Child Theme  1.0.0
 */
function ccluk_theme_scripts_styles()
{
  /**
   * Scripts and Styles loaded by the parent theme can be unloaded if needed
   * using wp_deregister_script or wp_deregister_style.
   *
   * See the WordPress Codex for more information about those functions:
   * http://codex.wordpress.org/Function_Reference/wp_deregister_script
   * http://codex.wordpress.org/Function_Reference/wp_deregister_style
   **/

  /*
   * Styles
   *
   * need to ensure this stylesheet loads after the parent stylesheets
   *
   */
  wp_enqueue_style( 'onesocial-ccluk-custom', get_stylesheet_directory_uri().'/assets/css/custom.'.(CCLUK_DEBUGGING ? '' : 'min.').'css', array( 'onesocial-main-global' ) );

}
add_action( 'wp_enqueue_scripts', 'ccluk_theme_scripts_styles', 9999 );


/****************************** CUSTOM FUNCTIONS ******************************/

// Add your own custom functions here


function boss_generate_option_css() {

    return;

  $accent_color  = onesocial_get_option( 'accent_color' );
  $body_font_size = onesocial_options[boss_body_font_family][font-size];


  ?>
  <style>

    /* Accent color */
    a { color: <?php echo $accent_color; ?>; }
          .widget_mc4wp_form_widget form p input[type="submit"], .widget.widget_newsletterwidget form p input[type="submit"],
          .footer-widget #switch-mode input[type="submit"],
          .woocommerce #respond input#submit, 
          .woocommerce a.button, 
          .woocommerce button.button, 
          .woocommerce input.button,
    button,
    input[type="button"],
    input[type="reset"],
    input[type="submit"],
    article.post-password-required input[type=submit],
    li.bypostauthor cite span,
    a.button,
    #buddypress ul.button-nav li a,
    #buddypress div.generic-button a,
    #secondary div.generic-button a,
    #buddypress .comment-reply-link,
    .select2-container--default .select2-results__option--highlighted[aria-selected],
    .entry-header .entry-title a.button,
    a.bp-title-button,
    #search-members-form > label:after,
    #messages-bulk-manage,
    .boss-search-wrapper label:after,
    .groups-members-search label:after,
    #buddypress #group-create-nav .group-create,
    #buddypress div#item-nav .item-list-tabs ul li .bb-menu-button,
    .is-mobile #buddypress #mobile-item-nav ul li:active,
    .is-mobile #buddypress #mobile-item-nav ul li.current,
    .is-mobile #buddypress #mobile-item-nav ul li.selected,
    #buddyboss-bbpress-media-attach,
    #buddyboss-comment-media-attach,
    .woocommerce .site-content nav.woocommerce-pagination ul li .current,
    #trigger-sidebar:hover .bb-side-icon,
    #trigger-sidebar:hover .bb-side-icon:before,
    #trigger-sidebar:hover .bb-side-icon:after,
    .header-account-login .count,
    .header-notifications a.header-button span b,
    #aw-whats-new-submit-bbmedia {
      background-color: <?php echo $accent_color; ?>;
    }

    .woocommerce span.onsale,
    .boss-modal-form .button,
    .bb-sidebar-on .bb-side-icon,
    .bb-sidebar-on .bb-side-icon:after,
    .bb-sidebar-on .bb-side-icon:before,
    #primary .author-follow div.generic-button a:before,
    #buddypress div#item-header .inner-avatar-wrap div.generic-button a:before,
    #secondary .inner-avatar-wrap div.generic-button a:before,
    body .selectionSharer a.action:hover,
    #page #main .author-follow div.generic-button.loading a:before,
    #page #main #buddypress .inner-avatar-wrap .generic-button.loading a:before,
    body #selectionSharerPopunder-inner a.action:hover {
      background: <?php echo $accent_color; ?>;
    }

    .woocommerce ul.products li.product .price,
    .woocommerce div.product p.price,
    .woocommerce div.product span.price,
    .woocommerce [type='checkbox']:checked + span,
    .header-account-login .pop .boss-logout,
    .header-account-login .pop a:hover,
    .bboss_ajax_search_item .item .item-title,
    body .bb-global-search-ac li.bbls-category:hover a:after,
    .bb-global-search-ac.ui-menu .bbls-view_all_type-type a:hover:after,
    .bbp-topics-front ul.super-sticky div.bbp-topic-title-content:before,
    .bbp-topics ul.super-sticky div.bbp-topic-title-content:before,
    .bbp-topics ul.sticky div.bbp-topic-title-content:before,
    .bbp-forum-content ul.sticky:before,
    .bbp-forum-data .last-activity a:hover,
    #onesocial-recommended-by .title, .bbp-forum-data .post-num,
    div.bbp-breadcrumb a:hover,
    li.bbp-forum-info a.bbp-forum-title:before,
    li.bbp-topic-title a.bbp-topic-permalink:before,
    #buddypress div#subnav.item-list-tabs ul li.feed a:hover ,
    #buddypress div#subnav.item-list-tabs ul li.feed a:before,
    #buddypress div.messages-options-nav .buddyboss-select-inner:after,
    #buddypress table#message-threads input[type="checkbox"]:checked + strong:after,
    #buddypress table#message-threads tr td.thread-info p a:hover,
    .info-group .members-list-filter li a:hover,
    .info-group .members-list-filter li a.selected,
    .info-group .bb-follow-title span,
    #buddypress .btn-group.social a:hover,
    #buddypress .standard-form div.submit a.prev:hover,
    #buddypress #group-settings-form input[type="submit"],
    #buddypress .standard-form div.submit input,
    .dir-header span,
    #buddypress .dir-list ul.item-list .item-title a:hover ,
    #create-group-form #invite-list strong,
    #create-group-form.standard-form label span.highlight,
    #buddypress form#whats-new-form #whats-new-submit input[type="submit"],
    #whats-new-form .whats-author,
    #buddypress #activity-stream .acomment-options .acomment-like.unfav-comment:before,
    #buddypress #activity-stream .activity-meta .unfav.bp-secondary-action:before,
    #primary #buddypress #activity-stream div.activity-meta a:hover:before,
    #buddypress div.activity-comments div.acomment-meta a:not(.activity-time-since):hover,
    #buddypress .activity-header a:hover,
    #posts-carousel footer a,
    #posts-carousel h3 a:hover,
    .comments-area article header cite a,
    .post-author-info .entry-meta a:not(.entry-date),
    .post-author-info .author-name a:hover ,
    .posts-stream ul li h2 a:hover,
    .entry-meta a.read-more,
    .bb-comment-author,
    .author-follow div.generic-button.pending_friend + span,
    .author-follow div.generic-button.following + span,
    .post-author .load-more-posts.active .bb-icon-bars-f:before,
    .entry-content blockquote .author,
    .comment-content blockquote .author,
    .entry-header .entry-title a:hover,
    .widget_search #searchform button i:before,
    .widget #bbp-search-index-form button i:before,
    .entry-meta a.read-more,
    .settings #buddypress div#subnav.item-list-tabs ul li.current a,
    .bb-user-notifications .avatar + a,
    #main #buddypress .bb-member-quick-link-wrapper .action .generic-button a:hover,
    #main-wrap #page #main #buddypress div.item-list-tabs li.hideshow ul a:hover,
    a.comment-reply-link:hover, a.comment-edit-link:hover,
    .bb-user-name,
    .dir-header .bb-count,
    .boss-author-name,
    .posts-stream ul li h3 a:hover,
    #main #buddypress .button.bp-secondary-action.loading:after,
    #main #buddypress .acomment-like.bp-secondary-action.loading:after,
    #main #buddypress .activity-comments .acomment-options a:hover,
    #main #buddypress .activity-comments .acomment-options a:hover:after,
    #main #buddypress .activity-comments .acomment-options a:hover:before,
    #buddypress .bboss_search_page a.loading:after,
    .header-account-login .pop .count,
    .is-mobile #main-wrap #page #buddypress div.item-list-tabs:not(#object-nav) ul li.current a,
    .is-mobile #main-wrap #page #buddypress div.item-list-tabs:not(#object-nav) ul li.selected a,
    li.bbp-forum-info a.bbp-forum-title:hover,
    li.bbp-topic-title a.bbp-topic-permalink:hover,
    .footer-inner-top a:hover,
    .boss-group-invite-friend-list strong,
    .sap-container-wrapper .sap-story-publish,
    .sap-container-wrapper .sap-story-publish:hover,
    .recommend-title,
    .liked .fa-heart,
    .bookmarked .bb-helper-icon.fa-bookmark,
    .fa.bb-helper-icon.fa-spinner.fa-spin,
    .os-loader i,
    .breadcrumb-wrapper a:hover,
    .sap-container-wrapper .sap-author-name.sap-author-name,
    .sap-publish-popup.sap-publish-popup .sap-action-button,
    #send-private-message.generic-button a:before {
      color: <?php echo $accent_color; ?>;
    }

    #onesocial_recommend:after,
    #onesocial_recommend:before,
    #bbpress-forums #favorite-toggle .is-favorite .favorite-toggle:before,
    #bbpress-forums #subscription-toggle .is-subscribed .subscription-toggle:before,
    #bbpress-forums #favorite-toggle .is-favorite .favorite-toggle:after,
    #bbpress-forums #subscription-toggle .is-subscribed .subscription-toggle:after,
    #buddypress #item-title-area .highlight,
    .bb-cover-photo .update-cover-photo div,
    #main ul.horiz-gallery .see-more a ,
    .posts-stream ul li time div:first-child:after,
    .posts-stream ul li time div:first-child:before,
    .posts-stream ul li time div:first-child,
    .group-join a:before, .author-follow a:before,
    .pagination .current,
    .bbp-pagination-links span,
    .loader,
    .loader:before,
    .loader:after,
    .sap-load-more-posts,
    .button-load-more-posts,
    #buddypress .activity-list li.load-more a,
    #buddypress .activity-list li.load-newest a,
    #fwslider .readmore a,
    #fwslider .progress {
      background-color: <?php echo $accent_color; ?>;
    }

    .toggle-sap-widgets:hover .cls-1 {
      fill: <?php echo $accent_color; ?>;
    }

    .bb-cover-photo,
    .bb-cover-photo .progress {
      background: <?php echo onesocial_get_option( 'onesocial_group_cover_bg' ); ?>;
    }

    @-webkit-keyframes load1 {
      0%,
      80%,
      100% {
        box-shadow: 0 0 <?php echo $accent_color; ?>;
        height: 4em;
      }

      40% {
        box-shadow: 0 -2em <?php echo $accent_color; ?>;
        height: 5em;
      }
    }

    @keyframes load1 {
      0%,
      80%,
      100% {
        box-shadow: 0 0 <?php echo $accent_color; ?>;
        height: 4em;
      }

      40% {
        box-shadow: 0 -2em <?php echo $accent_color; ?>;
        height: 5em;
      }
    }

    .header-notifications .pop,
    .header-account-login .pop,
    .bbp-header li.bbp-forum-info,
    .bbp-header li.bbp-topic-title,
    .info-group .members-list-filter,
    .info-group .trigger-filter.active:before,
    .entry-meta a.read-more,
    #buddypress #group-create-nav .group-create,
    .sap-publish-popup.sap-publish-popup .sap-action-button,
    .sap-container-wrapper .sap-story-publish,
    #main #buddypress div.item-list-tabs li.hideshow > ul {
      border-color: <?php echo $accent_color; ?>;
    }

    .main-navigation .nav-menu > li:hover > a,
    .main-navigation div > ul > .current-menu-item > a,
    .main-navigation div > ul > .current-menu-ancestor > a {
      box-shadow: 0 -2px 0 <?php echo $accent_color; ?> inset;
    }

    .header-button.underlined {
      box-shadow: 0 -1px 0 <?php echo $accent_color; ?> inset;
    }

    input[type="checkbox"] + span:before,
    input[type="checkbox"] + label:before,
    input[type="checkbox"] + strong:before,
    input[type="radio"] + span:before,
    input[type="radio"] + label:before,
    input[type="radio"] + strong:before {
      -webkit-box-shadow: 0px 0px 0px 2px <?php echo $accent_color; ?>;
      -moz-box-shadow: 0px 0px 0px 2px <?php echo $accent_color; ?>;
      box-shadow: 0px 0px 0px 2px <?php echo $accent_color; ?>;
    }

    .woocommerce-checkout [type='checkbox']:checked + span:before {
      -webkit-box-shadow: 0px 0px 0px 1px <?php echo $accent_color; ?>;
      -moz-box-shadow: 0px 0px 0px 1px <?php echo $accent_color; ?>;
      box-shadow: 0px 0px 0px 1px <?php echo $accent_color; ?>;
    }

    /********** Desktop  *************/
    .is-desktop #buddypress div#group-create-tabs ul li.current a,
    .is-desktop #buddypress div#group-create-tabs ul li:hover a,
    .bp-user.messages.is-desktop #buddypress div#subnav.item-list-tabs ul li#compose-personal-li {
      border-color: <?php echo $accent_color; ?>;
    }

    .is-desktop #buddypress div#group-create-tabs ul li a,
    .is-desktop #buddypress div#group-create-tabs ul li:before,
    .bp-user.messages.is-desktop #buddypress div#subnav.item-list-tabs ul li:hover a,
    .bp-user.messages.is-desktop #buddypress div#subnav.item-list-tabs ul li.selected a,
    .bp-user.messages.is-desktop #buddypress div#subnav.item-list-tabs ul li.current a,
    .bp-user.messages.is-desktop #buddypress div#subnav.item-list-tabs ul li a span,
    .search.is-desktop #buddypress div:not(#item-nav) > .dir-form div.item-list-tabs ul li a span,
    .search.is-desktop #buddypress div:not(#item-nav) > .dir-form div.item-list-tabs ul li.active a,
    .search.is-desktop #buddypress div:not(#item-nav) > .dir-form div.item-list-tabs ul li:hover a,
    .settings.bp-user.is-desktop #buddypress div#subnav.item-list-tabs ul li:hover a,
    .settings.bp-user.is-desktop #buddypress div:not(#item-nav) > .item-list-tabs ul li:hover a,
    .is-desktop #buddypress div#item-nav .item-list-tabs ul li:hover > a,
    .is-desktop #buddypress div#item-nav .item-list-tabs ul li.selected > a,
    .is-desktop #buddypress div#item-nav .item-list-tabs ul li.current > a,
    .is-desktop #buddypress div#item-nav .item-list-tabs ul li a span,
    .is-desktop #buddypress div#item-nav .item-list-tabs ul li:hover a:after,
    .is-desktop #buddypress div#item-nav .item-list-tabs ul li.selected a:after,
    .is-desktop #buddypress div#item-nav .item-list-tabs ul li.current a:after,
    .is-desktop .buddyboss-select-inner span,
    .is-desktop .post-author .load-more-posts:hover,
    .is-desktop .post-author .load-more-posts a:hover,
    .is-desktop .header-notifications a#user-messages span.count:before,
    .is-desktop .header-notifications a span.pending-count:before,
    .is-desktop .header-notifications .pop a:hover {
      color: <?php echo $accent_color; ?>;
    }

    .is-desktop #buddypress div#subnav.item-list-tabs ul li a span,
    .is-desktop #buddypress > div[role="navigation"].item-list-tabs ul li a span,
    .is-desktop #buddypress div:not(#item-nav) > .item-list-tabs ul li a span,
    .is-desktop #buddypress .dir-form div.item-list-tabs ul li a span,
    .bp-legacy div#item-body div.item-list-tabs ul li a span,
    .is-desktop #buddypress div#item-nav .item-list-tabs ul li:hover a:before,
    .is-desktop #buddypress div#item-nav .item-list-tabs ul li.selected a:before,
    .is-desktop #buddypress div#item-nav .item-list-tabs ul li.current a:before,
    .is-desktop .header-button:hover {
      background-color: <?php echo $accent_color; ?>;
    }

    /********** End Desktop  *************/

    /* Body Text color */
    body, .forgetme:hover, .joinbutton:hover, .siginbutton:hover {
      color: <?php echo onesocial_get_option( 'body_text_color' ); ?>;
    }

    /* Heading Text color */
    h1, h2, h3, h4, h5, h6, .entry-header .entry-title {
      color: <?php echo onesocial_get_option( 'heading_text_color' ); ?>;
    }

    .site-title {
      color: <?php echo onesocial_get_option( 'sitetitle_color' ); ?>;
    }

    /* Layout colors */

    <?php $primary_color   = onesocial_get_option( 'boss_primary_color' ); ?>
    <?php $secondary_color = onesocial_get_option( 'boss_secondary_color' ); ?>

    body, body #main-wrap, .formatted-content {
      background-color: <?php echo $primary_color; ?>;
    }
          
          @media screen and (max-width: 1024px) and (min-width: 768px) {
              .side-panel {
                  background-color: <?php echo $primary_color; ?>;
              }                
          }

          body:not(.buddypress) #content article, body.buddypress #content article.error404, .site-content nav.nav-single, .site-content #comments, .bp-legacy div#item-body,
    .os-loader,
    .medium-editor-insert-plugin .medium-insert-buttons .medium-insert-buttons-addons li,
    .sap-publish-popup,
    .posts-stream,
    .posts-stream .inner,
    .sl-count:after,
    .sl-count:before,
    .sl-icon:after,
    .sl-icon:before,
    #buddypress div#group-create-tabs ul li:before,
    .sap-editor-wrap .sap-story-publish:hover,
    .sap-editor-wrap .sap-story-publish,
    .main-navigation li ul ul,
    .main-navigation li ul,
    #main #buddypress div.item-list-tabs li.hideshow > ul,
    .settings.bp-user #buddypress div#item-nav .item-list-tabs > ul,
    .header-account-login .pop .bp_components .menupop:not(#wp-admin-bar-my-account) > .ab-sub-wrapper,
    .header-account-login .pop .links li > .sub-menu,
    .header-account-login .pop .bp_components .menupop:not(#wp-admin-bar-my-account) > .ab-sub-wrapper:before,
    .header-account-login .pop .links li > .sub-menu:before,
    .header-notifications .pop,
    .header-account-login .pop,
    #whats-new-header:after,
    a.to-top,
    #onesocial-recommended-by:before, .bbp-forum-data:before {
      background-color: <?php echo $primary_color; ?>;
    }

    .settings.bp-user #item-nav .item-list-tabs > ul:after {
      border-bottom-color: <?php echo $primary_color; ?>;
    }

    #onesocial-recommended-by:after, .bbp-forum-data:after {
      border-color: transparent <?php echo $primary_color; ?>; transparent transparent;
    }

    .incom-bubble-style:before {
      border-color: <?php echo $primary_color; ?> transparent transparent transparent;
    }

    div.bbp-template-notice,
    div.indicator-hint,
    #bbpress-forums #bbp-your-profile fieldset input,
    #bbpress-forums #bbp-your-profile fieldset textarea,
    div#sitewide-notice div#message p {
      background-color: <?php echo $secondary_color; ?>;
    }

    #header-search form,
    .site-header {
      background-color: <?php echo onesocial_get_option( 'titlebar_bg' ); ?>;
    }

    /***************** Mobile ******************/

    .is-mobile #buddypress #mobile-item-nav-wrap,
    .is-mobile #buddypress ul#activity-stream li.activity-item,
    body.has-activity.is-mobile #buddypress div.item-list-tabs,
    body.has-activity.is-mobile #buddypress form#whats-new-form,
    body.photos.is-mobile #buddypress form#whats-new-form,
    .is-mobile #buddypress div.activity-comments form.root {
      background-color: <?php echo $secondary_color; ?>;
    }

    /***************** End Mobile ******************/

    /***************** Desktop ******************/

    .is-desktop #buddypress div.buddyboss-media-form-wrapper form {
      background-color: <?php echo $secondary_color; ?>;
    }

    <?php if ( onesocial_get_option( 'boss_cover_group_size' ) == 200 ) { ?>
      .bb-cover-photo {
        height: 200px;
      }
    <?php } ?>

    .footer-inner-top {
      background-color: <?php echo onesocial_get_option( 'footer_widget_background' ) ?>;
    }

    .footer-inner-bottom {
      background-color: <?php echo onesocial_get_option( 'footer_background' ) ?>;
    }

    <?php if ( $font = onesocial_get_option( 'boss_body_font_family' ) ) { ?>
    .is-mobile .entry-content,
    .is-mobile .entry-summary,
    .is-mobile .mu-register {
    <?php if (!empty($font['font-family'])) : ?>
        font-family: <?php echo $font['font-family'] ?>;
    <?php endif; ?>
    <?php if (!empty($font['font-weight'])) : ?>
        font-weight: <?php echo $font['font-weight'] ?>;
    <?php endif; ?>
    <?php if (!empty($font['font-style'])) : ?>
        font-weight: <?php echo $font['font-style'] ?>;
    <?php endif; ?>
    <?php if (!empty($font['font-size'])) : ?>
        font-size: <?php echo $font['font-size'] ?>;
    <?php endif; ?>
    }
    <?php } ?>
  </style>
  <?php 
}

/* Add Action */
add_action( 'wp_head', 'boss_generate_option_css', 200 );

// create news post type
function ccluk_create_news_post_type() {
    register_post_type( 'ccluk_news',
        array(
          'labels' => array(
            'name' => __( 'News' ),
            'singular_name' => __( 'News' )
          ),
          'public' => true,
          'has_archive' => true,
          'rewrite' => array('slug' => 'news'),
          'supports' => array( 'title', 'editor', 'thumbnail', 'revisions' ),
          'taxonomies' => array( 'category', 'post_tag' ),
          'menu_position' => 4
        )
    );

    // add to Buddypress activity stream
    add_post_type_support( 'ccluk_news', 'buddypress-activity' );
}
add_action( 'init', 'ccluk_create_news_post_type' );

// from OnePress
// load section into home page
if ( ! function_exists( 'ccluk_load_section' ) ) {
    /**
     * Load section
     * @since 2.0.0
     * @param $section_id
     */
    function ccluk_load_section( $section_id )
    {
        /**
         * Hook before section
         */
        do_action('ccluk_before_section_' . $section_id);
        do_action('ccluk_before_section_part', $section_id);

        get_template_part('section-parts/section', $section_id );

        /**
         * Hook after section
         */
        do_action('ccluk_after_section_part', $section_id);
        do_action('ccluk_after_section_' . $section_id);
    }
}

if ( ! function_exists( 'ccluk_is_selective_refresh' ) ) {
    function ccluk_is_selective_refresh()
    {
        return isset($GLOBALS['ccluk_is_selective_refresh']) && $GLOBALS['ccluk_is_selective_refresh'] ? true : false;
    }
}

if ( ! function_exists( 'ccluk_get_section_about_data' ) ) {
    /**
     * Get About data
     *
     * @return array
     */
    function ccluk_get_section_about_data()
    {
        $boxes = get_theme_mod('ccluk_homepage_about_boxes');
        if (is_string($boxes)) {
            $boxes = json_decode($boxes, true);
        }
        $page_ids = array();
        if (!empty($boxes) && is_array($boxes)) {
            foreach ($boxes as $k => $v) {
                if (isset ($v['content_page'])) {
                    $v['content_page'] = absint($v['content_page']);
                    if ($v['content_page'] > 0) {
                        $page_ids[] = wp_parse_args($v, array('enable_link' => 0, 'hide_title' => 0));
                    }
                }
            }
        }

        return $page_ids;
    }
}

if ( ! function_exists( 'ccluk_posted_on' ) ) {

    function ccluk_posted_on() {
        printf( '<a href="%1$s" title="%2$s" rel="bookmark" class="entry-date"><time datetime="%3$s">%4$s</time></a>', esc_url( get_permalink() ), esc_attr( get_the_time() ), esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ));
    }
    
}

/**
 * Admin styles
 */
function ccluk_admin_assets() {

    /**
     * Assign the OneSocial version to a var
     */
    $theme               = wp_get_theme( 'onesocial' );
    $onesocial_version   = $theme[ 'Version' ];

    wp_enqueue_style( 'ccluk-main-admin-css', get_stylesheet_directory_uri() . '/assets/css/admin.css', array(), $onesocial_version, 'all' );
}
add_action( 'admin_enqueue_scripts', 'ccluk_admin_assets' );