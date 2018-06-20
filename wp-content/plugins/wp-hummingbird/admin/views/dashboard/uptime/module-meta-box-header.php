<?php
/**
 * Uptime meta box header on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $title  Meta box title.
 */

?>
<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
	<div class="sui-actions-right">
		<a class="sui-button sui-button-green" href="<?php echo WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_uptime_upgrade_button' ); ?>" target="_blank">
			<?php esc_html_e( 'Upgrade to PRO', 'wphb' ); ?>
		</a>
	</div>
<?php endif; ?>