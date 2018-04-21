<div class="wphb-block-entry">

	<div class="wphb-block-entry-content wphb-block-content-center">

		<img class="wphb-image wphb-image-center wphb-image-icon-content-top"
			 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hummingbird-circle@1x.jpg'; ?>"
			 alt="<?php esc_attr_e( "Let's see what we can improve!", 'wphb' ); ?>">

		<p><?php _e( 'For us to know what to improve we need to test your website. All testing is done in the background via our <br> secure servers. Once complete, we\'ll give you a list of things to improve, and how to do it.', 'wphb' ); ?></p>
		<div class="buttons">
			<a class="sui-button sui-button-primary button-large" data-a11y-dialog-show="run-performance-test" id="run-performance-test">
				<?php esc_html_e( 'Test my website', 'wphb' ); ?>
			</a>
		</div>

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->

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