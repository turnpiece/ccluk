<?php
/**
 * Asset optimization empty meta box.
 *
 * @package Hummingbird
 */

?>

<?php if ( ! WP_Hummingbird_Utils::hide_wpmudev_branding() ) : ?>
	<img class="sui-image"
		src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-reports-disabled@1x.png' ); ?>"
		srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-reports-disabled@2x.png' ); ?> 2x"
		alt="<?php esc_attr_e( 'Reduce your page load time!', 'wphb' ); ?>">
<?php endif; ?>

<div class="sui-message-content">
	<p>
		<?php
		printf(
			/* translators: %s: username */
			esc_html__(
				"Hummingbird's Asset Optimization engine can combine and minify the files your website outputs when a user
				visits your website. The less requests your visitors have to make to your server, the better. Let's
				check to see what we can optimise, %s!",
				'wphb'
			),
			esc_attr( WP_Hummingbird_Utils::get_current_user_info() )
		);
		?>
	</p>

	<a id="check-files" class="sui-button sui-button-blue" data-a11y-dialog-show="check-files-modal">
		<?php esc_html_e( 'Activate', 'wphb' ); ?>
	</a>
</div>

<?php $this->view( 'modals/check-files-modal' ); ?>

<?php if ( WP_Hummingbird_Utils::get_module( 'minify' )->is_scanning() ) : ?>
	<script>
		window.addEventListener("load", function(){
			jQuery('#check-files').click();
		});
	</script>
<?php endif; ?>
