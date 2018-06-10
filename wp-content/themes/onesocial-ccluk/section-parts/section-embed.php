<?php 

if (defined('MC4WP_VERSION') && !is_user_logged_in()) :

$slug       = 'ccluk_homepage_embed';
$id         = get_theme_mod( $slug.'_id', esc_html__('embed', 'onesocial') );
$form       = get_theme_mod( $slug.'_form' );
$disable    = get_theme_mod( $slug.'_disable' ) == 1 ? true : false;
$title      = get_theme_mod( $slug.'_title', sprintf( __('Signup to our newsletter', 'onesocial' ), get_bloginfo('name') ) );
$text       = get_theme_mod( $slug.'_text' );
$privacy_text = get_theme_mod( $slug.'_privacy_text' );
$privacy_page = get_theme_mod( $slug.'_privacy_page' );
$page_url   = $page_id ? get_permalink( $page_id ) : wp_registration_url();

if ( ccluk_is_selective_refresh() ) {
    $disable = false;
}

// Get data
if (!$disable && $form && $title !== '' ) :
    if ( ! ccluk_is_selective_refresh() ) : ?>
    <section id="<?php echo esc_attr( $id ) ?>" <?php do_action('ccluk_section_atts', 'embed'); ?> class="section embed site-content green-bg">
    <?php endif; ?>

        <?php do_action('ccluk_section_before_inner', 'embed'); ?>

        <div class="section-content">
        <?php if ($text !== '') : ?> 
            <div class="intro list-item">
                <p><?php echo $text ?></p>

            <?php if ($privacy_text && $privacy_page) : ?>
                <p class="privacy-policy">
                    <a href="<?php echo get_permalink( $privacy_page ) ?>" title="<?php esc_attr_e( 'Our privacy policy', 'onesocial' ) ?>"><?php echo $privacy_text ?></a>
                </p>    
            <?php endif; ?>
            </div>
        <?php endif; ?>
        
            <div class="form list-item">
                <?php //echo do_shortcode('[mc4wp_form id="'.$form.'"]') ?>
                <?php echo do_shortcode('[wd_hustle id="signup-to-our-newsletter" type="embedded"]') ?>
            </div>

        </div>

        <?php do_action('ccluk_section_after_inner', 'embed'); ?>

    <?php if ( ! ccluk_is_selective_refresh() ) : ?>
    </section>
    <?php endif; ?>
<?php endif; 

endif; // end of if embed plugin is active