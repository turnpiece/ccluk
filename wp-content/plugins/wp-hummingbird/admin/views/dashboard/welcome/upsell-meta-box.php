<?php
/**
 * Upsell Hummingbird meta box for Free users.
 *
 * @since 2.0.1
 * @package Hummingbird
 */

?>

<p>
	<?php esc_html_e( 'Get our full WordPress performance optimization suite with Hummingbird Pro and additional benefits of WPMU DEV membership.', 'wphb' ); ?>
</p>

<ul>
	<li><i class="sui-icon-check sui-lg" aria-hidden="true"></i> <?php esc_html_e( 'Whitelabel automated reporting', 'wphb' ); ?></li>
	<li><i class="sui-icon-check sui-lg" aria-hidden="true"></i> <?php esc_html_e( 'Uptime monitoring', 'wphb' ); ?></li>
	<li><i class="sui-icon-check sui-lg" aria-hidden="true"></i> <?php esc_html_e( 'Enhanced file minification with CDN', 'wphb' ); ?></li>
	<li><i class="sui-icon-check sui-lg" aria-hidden="true"></i> <?php esc_html_e( 'Smush Pro for the best image optimization', 'wphb' ); ?></li>
	<li><i class="sui-icon-check sui-lg" aria-hidden="true"></i> <?php esc_html_e( 'Premium WordPress plugins', 'wphb' ); ?></li>
	<li><i class="sui-icon-check sui-lg" aria-hidden="true"></i> <?php esc_html_e( 'Manage unlimited WordPress sites', 'wphb' ); ?></li>
	<li><i class="sui-icon-check sui-lg" aria-hidden="true"></i> <?php esc_html_e( '24/7 live WordPress support', 'wphb' ); ?></li>
	<li><i class="sui-icon-check sui-lg" aria-hidden="true"></i> <?php esc_html_e( 'The WPMU DEV Guarantee', 'wphb' ); ?></li>
</ul>

<a href="<?php echo esc_url( WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dashboard_upsellwidget_button' ) ); ?>" class="sui-button sui-button-purple" target="_blank">
	<?php esc_html_e( 'Try Pro for FREE today!', 'wphb' ); ?>
</a>
