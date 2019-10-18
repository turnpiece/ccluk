<?php
/**
 * Footer template for SUI.
 */

defined( 'WPINC' ) || die();

?>

<?php
/**
 * Action hook to output the SUI modal contents.
 *
 * @since 3.2.0
 */
do_action( 'beehive_add_modals' );
?>

<div class="sui-footer"><?php esc_html_e( 'Made with', 'ga_trans' ); ?>
    <i class="sui-icon-heart"></i> <?php esc_html_e( 'by WPMU DEV', 'ga_trans' ); ?></div>

<?php if ( beehive_analytics()->is_pro() ) : ?>
    <!-- PRO Navigation -->
    <ul class="sui-footer-nav">
        <li>
            <a href="https://premium.wpmudev.org/hub/" target="_blank"><?php esc_html_e( 'The Hub', 'ga_trans' ); ?></a></li>
        <li>
            <a href="https://premium.wpmudev.org/projects/category/plugins/" target="_blank"><?php esc_html_e( 'Plugins', 'ga_trans' ); ?></a>
        </li>
        <li>
            <a href="https://premium.wpmudev.org/roadmap/" target="_blank"><?php esc_html_e( 'Roadmap', 'ga_trans' ); ?></a>
        </li>
        <li>
            <a href="https://premium.wpmudev.org/hub/support/" target="_blank"><?php esc_html_e( 'Support', 'ga_trans' ); ?></a>
        </li>
        <li>
            <a href="https://premium.wpmudev.org/docs/" target="_blank"><?php esc_html_e( 'Docs', 'ga_trans' ); ?></a></li>
        <li>
            <a href="https://premium.wpmudev.org/hub/community/" target="_blank"><?php esc_html_e( 'Community', 'ga_trans' ); ?></a>
        </li>
        <li>
            <a href="https://premium.wpmudev.org/academy/" target="_blank"><?php esc_html_e( 'Academy', 'ga_trans' ); ?></a>
        </li>
        <li>
            <a href="https://premium.wpmudev.org/terms-of-service/" target="_blank"><?php esc_html_e( 'Terms of Service', 'ga_trans' ); ?></a>
        </li>
        <li>
            <a href="https://incsub.com/privacy-policy/" target="_blank"><?php esc_html_e( 'Privacy Policy', 'ga_trans' ); ?></a>
        </li>
    </ul>
<?php else : ?>
    <!-- FREE Navigation -->
    <ul class="sui-footer-nav">
        <li>
            <a href="https://profiles.wordpress.org/wpmudev#content-plugins" target="_blank"><?php esc_html_e( 'Free Plugins', 'ga_trans' ); ?></a>
        </li>
        <li>
            <a href="https://premium.wpmudev.org/features/" target="_blank"><?php esc_html_e( 'Membership', 'ga_trans' ); ?></a>
        </li>
        <li>
            <a href="https://premium.wpmudev.org/roadmap/" target="_blank"><?php esc_html_e( 'Roadmap', 'ga_trans' ); ?></a>
        </li>
        <li>
            <a href="https://wordpress.org/support/plugin/beehive-analytics" target="_blank"><?php esc_html_e( 'Support', 'ga_trans' ); ?></a>
        </li>
        <li>
            <a href="https://premium.wpmudev.org/docs/" target="_blank"><?php esc_html_e( 'Docs', 'ga_trans' ); ?></a></li>
        <li>
            <a href="https://premium.wpmudev.org/hub-welcome/" target="_blank"><?php esc_html_e( 'The Hub', 'ga_trans' ); ?></a>
        </li>
        <li>
            <a href="https://premium.wpmudev.org/terms-of-service/" target="_blank"><?php esc_html_e( 'Terms of Service', 'ga_trans' ); ?></a>
        </li>
        <li>
            <a href="https://incsub.com/privacy-policy/" target="_blank"><?php esc_html_e( 'Privacy Policy', 'ga_trans' ); ?></a>
        </li>
    </ul>
<?php endif; ?>

<ul class="sui-footer-social">
    <li>
        <a href="https://www.facebook.com/wpmudev" target="_blank"><i class="sui-icon-social-facebook" aria-hidden="true"></i><span class="sui-screen-reader-text"><?php esc_html_e( 'Facebook', 'ga_trans' ); ?></span></a>
    </li>
    <li>
        <a href="https://twitter.com/wpmudev" target="_blank"><i class="sui-icon-social-twitter" aria-hidden="true"></i></a><span class="sui-screen-reader-text"><?php esc_html_e( 'Twitter', 'ga_trans' ); ?></span>
    </li>
    <li>
        <a href="https://www.instagram.com/wpmu_dev/" target="_blank"><i class="sui-icon-instagram" aria-hidden="true"></i><span class="sui-screen-reader-text"><?php esc_html_e( 'Instagram', 'ga_trans' ); ?></span></a>
    </li>
</ul>

</div> <!-- Close sui-wrap -->