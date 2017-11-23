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

		<div class="tooltip-box">
			<span class="checkbox-group">
				<input type="checkbox" class="toggle-checkbox filter-toggles filter-minify" name="filter-minify" id="filter-minify">
				<label for="filter-minify" class="toggle-label">
					<span class="toggle tooltip-l tooltip-left" aria-label="<?php esc_attr_e( 'Compress this file to reduce it’s filesize', 'wphb' ); ?>" tooltip="<?php esc_attr_e( 'Compress this file to reduce it’s filesize', 'wphb' ); ?>"></span>
					<i class="hb-icon-minify"></i>
					<span><?php esc_html_e( 'Minify', 'wphb' ); ?></span>
				</label>

				<?php
				$tooltip = __( 'Combine this file with others if possible', 'wphb' );
				$is_ssl = is_ssl();
				if ( $is_ssl ) {
					$tooltip = __( 'This file can’t be combined', 'wphb' );
					$dont_combine = true;
				}
				?>
				<input type="checkbox" class="toggle-checkbox filter-toggles filter-combine" name="filter-combine" id="filter-combine" <?php echo disabled( $is_ssl ); ?>>
				<label for="filter-combine" class="toggle-label">
					<span class="toggle tooltip-l" aria-label="<?php echo esc_attr( $tooltip ); ?>" tooltip="<?php echo esc_attr( $tooltip ); ?>"></span>
					<i class="hb-icon-minify-combine"></i>
					<span><?php esc_html_e( 'Combine', 'wphb' ); ?></span>
				</label>

				<input type="checkbox" class="toggle-checkbox filter-toggles filter-position-footer" name="filter-position" id="filter-position-footer">
				<label for="filter-position-footer" class="toggle-label">
					<span class="toggle tooltip-l tooltip-right" aria-label="<?php esc_attr_e( 'Load this file in the footer of the page', 'wphb' ); ?>" tooltip="<?php esc_attr_e( 'Load this file in the footer of the page', 'wphb' ); ?>"></span>
					<i class="hb-icon-minify-footer"></i>
					<span><?php esc_html_e( 'Footer', 'wphb' ); ?></span>
				</label>

				<input type="checkbox" class="toggle-checkbox filter-toggles filter-defer" name="filter-defer" id="filter-defer">
				<label for="filter-defer" class="toggle-label">
					<span class="toggle tooltip-l tooltip-right" aria-label="<?php esc_attr_e( 'Execute this script once the page is completely loaded', 'wphb' ); ?>" tooltip="<?php esc_attr_e( 'Execute this script once the page is completely loaded', 'wphb' ); ?>"></span>
					<i class="hb-icon-minify-defer"></i>
					<span><?php esc_html_e( 'Defer', 'wphb' ); ?></span>
				</label>

				<input type="checkbox" class="toggle-checkbox filter-toggles filter-inline" name="filter-inline" id="filter-inline">
				<label for="filter-inline" class="toggle-label">
					<span class="toggle tooltip-l tooltip-right" tooltip="<?php esc_attr_e( 'Inline style', 'wphb' ); ?>"></span>
					<i class="hb-icon-minify-inline"></i>
					<span><?php esc_html_e( 'Inline', 'wphb' ); ?></span>
				</label>
			</span>
		</div><!-- end tooltip-box -->

		<div class="wphb-progress-state">
			<span class="wphb-progress-state-text"><?php esc_html_e( 'Hummingbird will set this configuration for all chosen files. You will still need to set the changes live by clicking Save Changes on the next screen.', 'wphb' ); ?></span>
		</div><!-- end wphb-progress-state -->

	</div><!-- end wphb-dialog-content -->

	<div class="wphb-dialog-footer">
		<div class="alignleft">
			<div class="close button button-ghost button-large"><?php esc_html_e( 'Cancel', 'wphb' ); ?></div>
		</div>
		<div class="alignright">
			<div class="close button button-large save-batch"><?php esc_html_e( 'Apply', 'wphb' ); ?></div>
		</div>
	</div>
</dialog><!-- end check-files-modal -->