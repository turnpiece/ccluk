<dialog class="wphb-modal small wphb-progress-modal" id="bulk-update-modal" title="<?php esc_attr_e( 'Bulk update', 'wphb' ); ?>">
	<div class="wphb-dialog-content">
		<p><?php esc_html_e( 'Choose what bulk update actions you wish to apply.', 'wphb' ); ?></p>

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

				// Unset all the values in bulk update checkboxes
				modal.find('input.filter-minify').prop('checked', false);
				modal.find('input.filter-combine').prop('checked', false);
				modal.find('input.filter-position-footer').prop('checked', false);
				modal.find('input.filter-defer').prop('checked', false);
				modal.find('input.filter-inline').prop('checked', false);
			});
		</script>

		<div class="checkbox-group">
			<input type="checkbox" class="toggle-checkbox filter-toggles filter-minify" name="filter-minify" id="filter-minify" aria-label="<?php esc_attr_e( 'Compress', 'wphb' ); ?>">
			<label for="filter-minify" class="toggle-label">
				<span class="toggle tooltip-s" tooltip="<?php esc_attr_e( 'Compress', 'wphb' ); ?>" aria-hidden="true"></span>
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
			<label for="filter-combine" class="toggle-label">
				<span class="toggle tooltip-s" tooltip="<?php echo esc_attr( $tooltip ); ?>" aria-hidden="true"></span>
				<span class="hb-icon-minify-combine" aria-hidden="true"></span>
			</label>

			<input type="checkbox" class="toggle-checkbox filter-toggles filter-position-footer" name="filter-position" id="filter-position-footer" aria-label="<?php esc_attr_e( 'Footer', 'wphb' ); ?>">
			<label for="filter-position-footer" class="toggle-label">
				<span class="toggle tooltip-s" tooltip="<?php esc_attr_e( 'Move to Footer', 'wphb' ); ?>" aria-hidden="true"></span>
				<span class="hb-icon-minify-footer" aria-hidden="true"></span>
			</label>

			<input type="checkbox" class="toggle-checkbox filter-toggles filter-defer" name="filter-defer" id="filter-defer" aria-label="<?php esc_attr_e( 'Defer', 'wphb' ); ?>">
			<label for="filter-defer" class="toggle-label">
				<span class="toggle tooltip-s" tooltip="<?php esc_attr_e( 'Defer JavaScript', 'wphb' ); ?>" aria-hidden="true"></span>
				<span class="hb-icon-minify-defer" aria-hidden="true"></span>
			</label>

			<input type="checkbox" class="toggle-checkbox filter-toggles filter-inline" name="filter-inline" id="filter-inline" aria-label="<?php esc_attr_e( 'Inline', 'wphb' ); ?>">
			<label for="filter-inline" class="toggle-label">
				<span class="toggle tooltip-s" tooltip="<?php esc_attr_e( 'Inline CSS', 'wphb' ); ?>" aria-hidden="true"></span>
				<span class="hb-icon-minify-inline" aria-hidden="true"></span>
			</label>
		</div><!-- end checkbox-group -->

		<div class="wphb-progress-state">
			<span class="wphb-progress-state-text"><?php esc_html_e( 'Note: You still need to set the changes live by clicking Save Changes on the next screen.', 'wphb' ); ?></span>
		</div><!-- end wphb-progress-state -->

	</div><!-- end wphb-dialog-content -->

	<div class="wphb-dialog-footer">
		<div class="alignleft">
			<div class="close button button-ghost"><?php esc_html_e( 'Cancel', 'wphb' ); ?></div>
		</div>
		<div class="alignright">
			<div class="close button save-batch"><?php esc_html_e( 'Apply', 'wphb' ); ?></div>
		</div>
	</div>
</dialog><!-- end check-files-modal -->