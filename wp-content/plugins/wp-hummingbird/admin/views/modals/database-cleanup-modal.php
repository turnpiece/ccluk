<div class="dialog sui-dialog sui-dialog-sm wphb-database-cleanup-modal" aria-hidden="true" id="wphb-database-cleanup-modal">

	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" aria-labelledby="databaseCleanup" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="databaseCleanup">
					<?php esc_html_e( 'Are you sure?', 'wphb' ); ?>
				</h3>
			</div>

			<div class="sui-box-body">

				<p></p>

				<div class="sui-block-content-center">
					<a class="sui-button sui-button-ghost" data-a11y-dialog-hide>
						<?php esc_html_e( 'Cancel', 'wphb' ); ?>
					</a>
					<a class="sui-button wphb-delete-db-row"
					   onclick="WPHB_Admin.advanced.confirmDelete( jQuery(this).attr('data-type') )">
						<?php esc_html_e( 'Delete entries', 'wphb' ); ?>
					</a>
				</div>
			</div>

		</div>

	</div>

</div>