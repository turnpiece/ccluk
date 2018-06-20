<?php
/**
 * Performance reports meta box header.
 *
 * @package Hummingbird
 */

?>

<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>

<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
	<div class="sui-actions-right">
		<?php $link = WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_test_upgrade_button' ); ?>
		<a class="sui-button sui-button-green" href="<?php echo esc_url( $link ); ?>" target="_blank">
			<?php esc_html_e( 'Upgrade to Pro', 'wphb' ); ?>
		</a>
	</div>
<?php endif; ?>