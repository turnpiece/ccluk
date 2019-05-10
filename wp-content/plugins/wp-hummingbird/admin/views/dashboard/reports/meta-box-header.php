<?php
/**
 * Reports meta box header on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $title  Reports module title.
 */

?>
<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
	<span class="sui-tag sui-tag-pro" style="margin-left: 10px">
		<?php esc_html_e( 'Pro', 'wphb' ); ?>
	</span>

	<div class="sui-actions-right">
		<a class="sui-button sui-button-purple" href="<?php echo esc_url( WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_reports_upgrade_button' ) ); ?>" target="_blank">
			<?php esc_html_e( 'Upgrade to PRO', 'wphb' ); ?>
		</a>
	</div>
<?php endif; ?>
