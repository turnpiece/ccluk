<?php 

if (defined('MC4WP_VERSION')) :

$slug       = 'ccluk_homepage_mailchimp';
$id         = get_theme_mod( $slug.'_id', esc_html__('mailchimp', 'onesocial') );
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
    <section id="<?php echo esc_attr( $id ) ?>" <?php do_action('ccluk_section_atts', 'mailchimp'); ?> class="section mailchimp site-content green-bg">
    <?php endif; ?>

        <?php do_action('ccluk_section_before_inner', 'mailchimp'); ?>

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
        
        <?php echo do_shortcode('[mc4wp_form id="'.$form.'"]') ?>

        </div>

        <?php do_action('ccluk_section_after_inner', 'mailchimp'); ?>

    <?php if ( ! ccluk_is_selective_refresh() ) : ?>
    </section>
    <?php endif; ?>
<?php endif; 

endif; // end of if MailChimp plugin is active