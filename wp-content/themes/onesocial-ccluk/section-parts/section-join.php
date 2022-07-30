<?php 

if (!is_user_logged_in()) :

$slug       = 'ccluk_homepage_join';
$id         = get_theme_mod( $slug.'_id', esc_html__('join', 'onesocial') );
$disable    = get_theme_mod( $slug.'_disable' ) == 1 ? true : false;
$title      = get_theme_mod( $slug.'_title', sprintf( __('Join %s', 'onesocial' ), get_bloginfo('name') ) );
$text       = get_theme_mod( $slug.'_text' );
$page_id    = get_theme_mod( $slug.'_source_page' );
$page_url   = 'CCLUK_JOIN_URL';

if ( ccluk_is_selective_refresh() ) {
    $disable = false;
}

// Get data
if (!$disable && $title !== '' ) :
    if ( ! ccluk_is_selective_refresh() ) : ?>
    <section id="<?php echo esc_attr( $id ) ?>" <?php do_action('ccluk_section_atts', 'join'); ?> class="section join site-content green-bg">
    <?php endif; ?>

        <?php do_action('ccluk_section_before_inner', 'join'); ?>

        <div class="section-content">
        <?php if ($text !== '') : ?> 
            <div class="intro list-item">
                <?php echo $text ?>     
            </div>
        <?php endif; ?>
            <a class="cta list-item" href="<?php echo $page_url ?>">
                <?php echo $title ?>
            </a>
        </div>

        <?php do_action('ccluk_section_after_inner', 'join'); ?>

    <?php if ( ! ccluk_is_selective_refresh() ) : ?>
    </section>
    <?php endif; ?>
<?php endif; 

endif; // end of is user logged in