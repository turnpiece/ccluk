<?php 

$slug       = 'ccluk_homepage_newsletter';
$audience   = get_theme_mod( $slug.'_audience', 'all' );
$disable    = get_theme_mod( $slug.'_disable' ) == 1 ? true : false;

if ($disable || 
    $audience == 'none' || 
    $audience == 'logged_in' && !is_user_logged_in() || 
    $audience == 'logged_out' && is_user_logged_in())
    return;

$id         = get_theme_mod( $slug.'_id', esc_html__('newsletter', 'onesocial') );
$form       = get_theme_mod( 'ccluk_newsletter_signup_form' );
//$title      = get_theme_mod( $slug.'_title', sprintf( __('Signup for our newsletter', 'onesocial' ), get_bloginfo('name') ) );
$text       = get_theme_mod( $slug.'_text' );
$privacy_text = get_theme_mod( $slug.'_privacy_text' );
$privacy_page = get_theme_mod( 'ccluk_newsletter_privacy_page' );
$page_url   = $page_id ? get_permalink( $page_id ) : wp_registration_url();

if ( ccluk_is_selective_refresh() ) {
    $disable = false;
}

// Get data
if ($form) :
    if ( ! ccluk_is_selective_refresh() ) : ?>
    <section id="<?php echo esc_attr( $id ) ?>" <?php do_action('ccluk_section_atts', 'newsletter'); ?> class="section newsletter site-content green-bg">
    <?php endif; ?>

        <?php do_action('ccluk_section_before_inner', 'newsletter'); ?>

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
                <?php echo $form ?>
            </div>

        </div>

        <?php do_action('ccluk_section_after_inner', 'newsletter'); ?>

    <?php if ( ! ccluk_is_selective_refresh() ) : ?>
    </section>
    <?php endif; ?>
<?php endif; 
