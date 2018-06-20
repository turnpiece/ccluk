<?php
/**
 * Asset optimization empty meta box.
 *
 * @package Hummingbird
 */

?>
<img class="wphb-image wphb-image-center wphb-image-icon-content-top"
	 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-reports-disabled@1x.png' ); ?>"
	 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-reports-disabled@2x.png' ); ?> 2x"
	 alt="<?php esc_attr_e( 'Reduce your page load time!', 'wphb' ); ?>">

<p>
	<?php
	printf(
		/* translators: %s: username */
		__( "Hummingbird's Asset Optimization engine can combine and minify the files your website outputs when a <br> user
			visits your website. The less requests your visitors have to make to your server, the <br> better. Let's
			check to see what we can optimise, %s!", 'wphb' ),
		esc_attr( WP_Hummingbird_Utils::get_current_user_info() )
	); ?>
</p>

<a id="check-files" class="sui-button sui-button-primary" data-a11y-dialog-show="check-files-modal">
	<?php esc_html_e( 'Activate', 'wphb' ); ?>
</a>

<?php
WP_Hummingbird_Utils::get_modal( 'check-files' );

// Show the progress bar if we are still checking files.
if ( WP_Hummingbird_Utils::get_module( 'minify' )->is_scanning() || isset( $_GET['wphb-cache-cleared'] ) ) : ?>
	<script>
		window.onload = function () {
			jQuery(function() {
				jQuery('#check-files').click();
			});
		}
	</script>
<?php endif; ?>