<?php
/**
 * Shipper modal template partials: migration cancel modal
 *
 * @package shipper
 */

?>

<div class="sui-dialog sui-dialog-sm" id="shipper-migration-cancel" aria-hidden="true">
	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" role="dialog">

		<div class="sui-box" role="document">
			<div class="sui-box-header">
				<h3 class="sui-dialog-title"><?php esc_html_e( 'Cancel migration', 'shipper' ); ?></h3>
			</div>
			<div class="sui-box-body">
				<p>
					<?php esc_html_e( 'Are you sure you want to cancel the migration?', 'shipper' ); ?>
					<?php esc_html_e( 'This may cause your destination website to break and need restoring.', 'shipper' ); ?>
				</p>

				<div class="shipper-cancel-actions">
					<a href="<?php echo esc_url( remove_query_arg( array( 'check', 'begin' ) ) ) ?>" class="sui-button sui-button-ghost shipper-migration-continue">
						<?php esc_html_e( 'Go back', 'shipper' ); ?>
					</a>
					<a href="<?php echo esc_url( remove_query_arg( array( 'check', 'begin', 'type', 'site' ) ) ) ?>" class="sui-button sui-button-ghost shipper-migration-cancel">
						<?php esc_html_e( 'Cancel migration', 'shipper' ); ?>
					</a>
				</div>

			</div><?php // .sui-box-body ?>
		</div><?php // .sui-box ?>

	</div><?php // .sui-dialog-content ?>
</div><?php // .sui-dialog ?>
