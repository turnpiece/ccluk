<div class="wphb-block-entry">
	<div class="wphb-block-entry-content wphb-block-content-center">

		<img class="wphb-image wphb-image-center wphb-image-icon-content-top"
			 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-reports-disabled@1x.png'; ?>"
			 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-reports-disabled@2x.png'; ?> 2x"
			 alt="<?php esc_attr_e( 'Reduce your page load time!', 'wphb' ); ?>">

		<div class="content">
			<p>
				<?php
				printf(
					/* translators: %s: username */
					__( "Hummingbird's Minification engine can combine and minify the files your website outputs when a <br> user visits your website. The less requests your visitors have to make to your server, the <br> better. Let's check to see what we can optimise, %s!", 'wphb' ),
					wphb_get_current_user_info()
				); ?>
			</p>
		</div><!-- end content -->

		<div class="buttons">
			<a id="check-files" class="button button-large" href="#check-files-modal">
				<?php esc_html_e( 'Activate Minification', 'wphb' ); ?>
			</a>
		</div>

	</div><!-- end wphb-block-entry-content -->
</div><!-- end wphb-block-entry -->

<?php
wphb_check_files_modal();

if ( wphb_minification_is_scanning_files() || isset( $_GET['wphb-cache-cleared'] ) ) : // Show the progress bar if we are still checking files. ?>
	<script>
		jQuery(document).ready( function() {
			window.WPHB_Admin.getModule( 'minification' );
			jQuery('#check-files').trigger('click');
		});
	</script>
<?php endif; ?>