<dialog id="wphb-database-cleanup-modal" class="small wphb-modal no-close wphb-database-cleanup-modal">
	<div class="wphb-dialog-content dialog-upgrade">
		<h1><?php esc_html_e( 'Are you sure?', 'wphb' ); ?></h1>

		<p></p>

		<div class="wphb-block-content-center">
			<a class="close button button-ghost">
				<?php esc_html_e( 'Cancel', 'wphb' ); ?>
			</a>
			<a class="button button-grey wphb-delete-db-row"
			   onclick="WPHB_Admin.advanced.confirmDelete( jQuery(this).attr('data-type') )">
				<?php esc_html_e( 'Delete entries', 'wphb' ); ?>
			</a>
		</div>
	</div>
</dialog><!-- end wphb-database-cleanup-modal -->