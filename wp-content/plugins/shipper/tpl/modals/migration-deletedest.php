<?php
/**
 * Shipper modal template partials: migration selection, delete destination
 *
 * @package shipper
 */

?>

<div class="sui-dialog sui-dialog-sm shipper-destdelete" id="shipper-destdelete-confirmation" aria-hidden="true">
	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" role="dialog">

		<div class="sui-box" role="document">
			<div class="sui-box-header">
				<h3 class="sui-dialog-title">
					<?php esc_html_e( 'Delete Destination', 'shipper' ); ?>
				</h3>
				<button data-a11y-dialog-hide="" class="sui-dialog-close" aria-label="<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>"></button>
			</div>
			<div class="sui-box-body">
				<p>
					<?php printf(
						__( 'Are you sure you want to delete %s from your destinations list?', 'shipper' ),
						'<b class="shipper-destdelete-target"></b>'
					); ?>
				</p>

				<div class="shipper-destdelete-actions">
					<a href="<?php echo esc_url( remove_query_arg( array( 'check', 'begin' ) ) ) ?>" class="sui-button sui-button-ghost shipper-destdelete-cancel">
						<?php esc_html_e( 'Cancel', 'shipper' ); ?>
					</a>
					<a href="<?php echo esc_url( remove_query_arg( array( 'check', 'begin', 'type', 'site' ) ) ) ?>" class="sui-button sui-button-ghost sui-button-red shipper-destdelete-continue">
						<i class="sui-icon-trash" aria-hidden="true"></i>
						<?php esc_html_e( 'Delete', 'shipper' ); ?>
					</a>
				</div>

			</div><?php // .sui-box-body ?>
		</div><?php // .sui-box ?>

	</div><?php // .sui-dialog-content ?>
</div><?php // .sui-dialog ?>