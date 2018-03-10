<?php
/**
 * Smush meta box header on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $title  Reports module title.
 */

?>
<h3><?php echo esc_html( $title ); ?></h3>
<?php if ( ! WP_Hummingbird_Utils::is_member() && WP_Hummingbird_Utils::is_dash_logged_in() ) : ?>
	<div class="buttons">
		<a class="button button-content-cta" href="<?php echo WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_smush_header_upsell_link' ); ?>" target="_blank">
			<?php esc_html_e( 'Upgrade to Pro', 'wphb' ); ?>
		</a>
	</div>
<?php endif; ?>