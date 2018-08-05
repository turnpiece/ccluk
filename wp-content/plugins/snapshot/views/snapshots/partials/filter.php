<div class="my-snapshots-filter">

	<div class="msf-left">

		<select name="action" class="bulk-action-selector-top">
			<option value="-1"><?php esc_html_e( "Bulk Actions", "SNAPSHOT_I18N_DOMAIN"); ?>
			<option value="delete"><?php esc_html_e( "Delete", "SNAPSHOT_I18N_DOMAIN"); ?></option>

		</select>

		<input type="submit" id="doaction" class="button button-outline button-gray action" value="<?php esc_html_e( "Apply", "SNAPSHOT_I18N_DOMAIN"); ?>">

	</div>

	<div class="msf-right <?php echo ( $results_count > $per_page ) ? 'pagination-enabled' : ''; ?>">

		<span class="results-count"><?php echo esc_html( $results_count ); ?> results</span>

		<?php if ( $results_count > $per_page ) : ?>

			<ul class="my-snapshot-pagination">

			 <?php Snapshot_Helper_UI::table_pagination($max_pages); ?>

			</ul>

		<?php endif; ?>

	</div>

</div>