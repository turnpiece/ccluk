<div class="dialog sui-dialog sui-dialog-sm" aria-hidden="true" id="bulk-update-modal">

	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" aria-labelledby="bulkUpdate" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="bulkUpdate"><?php esc_html_e( 'Bulk Update', 'wphb' ); ?></h3>
				<div class="sui-actions-right">
					<a data-a11y-dialog-hide class="sui-dialog-close" aria-label="Close this dialog window"></a>
				</div>
			</div>

			<div class="sui-box-body">

				<p><?php esc_html_e( 'Choose what bulk update actions you’d like to apply to the selected files. You still have to publish your changes before they will be set live.', 'wphb' ); ?></p>

				<div class="checkbox-group">
					<input type="checkbox" class="toggle-checkbox filter-toggles filter-minify" name="filter-minify" id="filter-minify" aria-label="<?php esc_attr_e( 'Compress', 'wphb' ); ?>">
					<label for="filter-minify" class="toggle-label sui-tooltip" data-tooltip="<?php esc_attr_e( 'Compress', 'wphb' ); ?>" aria-hidden="true">
						<span class="hb-icon-minify" aria-hidden="true"></span>
					</label>

					<?php
					$tooltip = __( 'Combine', 'wphb' );
					$is_http2 = is_ssl() && 'HTTP/2.0' === $_SERVER['SERVER_PROTOCOL'];
					if ( $is_http2 ) {
						$tooltip = __( "Files can't be combined", 'wphb' );
						$dont_combine = true;
					}
					?>
					<input type="checkbox" class="toggle-checkbox filter-toggles filter-combine" name="filter-combine" id="filter-combine" aria-label="<?php esc_attr_e( 'Combine', 'wphb' ); ?>" <?php echo disabled( $is_http2 ); ?>>
					<label for="filter-combine" class="toggle-label sui-tooltip" data-tooltip="<?php echo esc_attr( $tooltip ); ?>" aria-hidden="true">
						<span class="hb-icon-minify-combine" aria-hidden="true"></span>
					</label>

					<input type="checkbox" class="toggle-checkbox filter-toggles filter-position-footer" name="filter-position" id="filter-position-footer" aria-label="<?php esc_attr_e( 'Footer', 'wphb' ); ?>">
					<label for="filter-position-footer" class="toggle-label sui-tooltip" data-tooltip="<?php esc_attr_e( 'Move to Footer', 'wphb' ); ?>" aria-hidden="true">
						<span class="hb-icon-minify-footer" aria-hidden="true"></span>
					</label>

					<input type="checkbox" class="toggle-checkbox filter-toggles filter-defer" name="filter-defer" id="filter-defer" aria-label="<?php esc_attr_e( 'Defer', 'wphb' ); ?>">
					<label for="filter-defer" class="toggle-label sui-tooltip" data-tooltip="<?php esc_attr_e( 'Defer JavaScript', 'wphb' ); ?>" aria-hidden="true">
						<span class="hb-icon-minify-defer" aria-hidden="true"></span>
					</label>

					<input type="checkbox" class="toggle-checkbox filter-toggles filter-inline" name="filter-inline" id="filter-inline" aria-label="<?php esc_attr_e( 'Inline', 'wphb' ); ?>">
					<label for="filter-inline" class="toggle-label sui-tooltip" data-tooltip="<?php esc_attr_e( 'Inline CSS', 'wphb' ); ?>" aria-hidden="true">
						<span class="hb-icon-minify-inline" aria-hidden="true"></span>
					</label>
				</div><!-- end checkbox-group -->

			</div>
			<div class="sui-box-footer sui-no-padding-top">
				<div class="sui-button sui-button-ghost" data-a11y-dialog-hide="bulk-update-modal"><?php esc_html_e( 'Cancel', 'wphb' ); ?></div>

				<a class="save-batch sui-button" data-a11y-dialog-hide="bulk-update-modal"><?php esc_html_e( 'Apply', 'wphb' ); ?></a>

			</div>

			<div class="wphb-modal-image wphb-modal-image-bottom dev-man">
				<img class="wphb-image"
					 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/graphic-minify-modal-warning@1x.png' ); ?>"
					 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/graphic-minify-modal-warning@2x.png' ); ?> 2x"
					 alt="<?php esc_attr_e( 'Hummingbird','wphb' ); ?>">
			</div>
		</div>

	</div>

</div>
<script type="text/javascript">
	jQuery('label[for^="filter-"]').on('click', function() {
		jQuery(this).toggleClass('toggle-label-background');
	});

	jQuery('.save-batch').on('click', function() {
		var filesCollection = WPHB_Admin.minification.rowsCollection;

		var modal = jQuery( '#bulk-update-modal' );
		// Get the selected batch status
		var minify = modal.find( 'input.filter-minify' ).prop( 'checked' ),
			combine = modal.find( 'input.filter-combine').prop('checked'),
			footer = modal.find( 'input.filter-position-footer' ).prop( 'checked' ),
			defer = modal.find( 'input.filter-defer' ).prop( 'checked' ),
			inline = modal.find( 'input.filter-inline' ).prop( 'checked' ),
			selectedFiles = filesCollection.getSelectedItems();

		for ( var i in selectedFiles ) {
			selectedFiles[i].change( 'minify', minify );
			selectedFiles[i].change( 'combine', combine );
			selectedFiles[i].change( 'footer', footer );
			selectedFiles[i].change( 'defer', defer );
			selectedFiles[i].change( 'inline', inline );
		}

		// Unset all the values in bulk update checkboxes.
		modal.find('input.filter-minify').prop('checked', false);
		modal.find('input.filter-combine').prop('checked', false);
		modal.find('input.filter-position-footer').prop('checked', false);
		modal.find('input.filter-defer').prop('checked', false);
		modal.find('input.filter-inline').prop('checked', false);

		// Remove background class.
		modal.find('label[for="filter-minify"]').removeClass('toggle-label-background');
		modal.find('label[for="filter-combine"]').removeClass('toggle-label-background');
		modal.find('label[for="filter-position-footer"]').removeClass('toggle-label-background');
		modal.find('label[for="filter-defer"]').removeClass('toggle-label-background');
		modal.find('label[for="filter-inline"]').removeClass('toggle-label-background');

		// Enable the Publish Changes button.
		jQuery('input[type=submit]').removeClass('disabled');
	});
</script>