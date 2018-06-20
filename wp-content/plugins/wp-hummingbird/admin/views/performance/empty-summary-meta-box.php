<?php
/**
 * Performance empty report meta box.
 *
 * @package Hummingbird
 */

?>

<div class="sui-box-body sui-block-content-center">
	<img class="wphb-image wphb-image-center wphb-image-icon-content-top"
		 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-circle@1x.jpg' ); ?>"
		 alt="<?php esc_attr_e( "Let's see what we can improve!", 'wphb' ); ?>">

	<p class="sui-margin-bottom">
		<?php esc_html_e( "For us to know what to improve we need to test your website. All testing is
			done in the background via our secure servers. Once complete, we'll give you a list of things to
			improve, and how to do it.", 'wphb' ); ?>
	</p>

	<a class="sui-button sui-button-primary" data-a11y-dialog-show="run-performance-test" id="run-performance-test">
		<?php esc_html_e( 'Test my website', 'wphb' ); ?>
	</a>
</div>

<?php
WP_Hummingbird_Utils::get_modal( 'check-performance' );
if ( $doing_report ) : // Show the progress bar if we are still checking files. ?>
	<script>
		window.onload = function () {
			jQuery(function() {
				jQuery('#run-performance-test').click();
			});
		}
	</script>
<?php endif; ?>