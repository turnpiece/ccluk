<?php
/**
 * Shipper modal template partials: migration selection, delete destination
 *
 * @package shipper
 */

?>

<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="shipper-destdelete-confirmation"
		class="sui-modal-content sui-content-fade-in shipper-destdelete"
		aria-modal="true"
		aria-labelledby="shipper-destdelete-confirmation-title"
		aria-describedby="shipper-destdelete-confirmation-description"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<button class="sui-button-icon sui-button-float--left shipper-previous">
					<i class="sui-icon-chevron-left sui-md" aria-hidden="true"></i>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close modal', 'shipper' ); ?></span>
				</button>

				<h3 id="shipper-destdelete-confirmation-title" class="sui-box-title sui-lg">
					<?php esc_html_e( 'Delete Destination', 'shipper' ); ?>
				</h3>

				<p class="shipper-description" id="shipper-destdelete-confirmation-description">
					<?php
					wp_kses_post(
						sprintf(
							/* translators: %s: destination site. */
							__( 'Are you sure you want to delete %s from your destinations list?', 'shipper' ),
							'<b class="shipper-destdelete-target"></b>'
						)
					);
					?>
				</p>
			</div> <!-- sui box header -->

			<div class="sui-box-footer sui-flatten sui-content-center">
				<div class="shipper-destdelete-actions">
					<a href="<?php echo esc_url( remove_query_arg( array( 'check', 'begin' ) ) ); ?>" class="sui-button sui-button-ghost shipper-destdelete-cancel">
						<?php esc_html_e( 'Cancel', 'shipper' ); ?>
					</a>
					<a href="<?php echo esc_url( remove_query_arg( array( 'check', 'begin', 'type', 'site' ) ) ); ?>" class="sui-button sui-button-ghost sui-button-red shipper-destdelete-continue">
						<i class="sui-icon-trash" aria-hidden="true"></i>
						<?php esc_html_e( 'Delete', 'shipper' ); ?>
					</a>
				</div>
			</div> <!-- sui box body -->
		</div> <!-- sui-box -->
	</div> <!-- sui-modal-dialog -->
</div> <!-- sui-modal -->