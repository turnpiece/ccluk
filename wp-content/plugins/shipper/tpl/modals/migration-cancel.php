<?php
/**
 * Shipper modal template partials: migration cancel modal
 *
 * @package shipper
 */

?>

<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="shipper-migration-cancel"
		class="sui-modal-content"
	>
		<div class="sui-box" role="document">
			<div class="sui-box-header">
				<h3 class="sui-dialog-title"><?php esc_html_e( 'Cancel migration', 'shipper' ); ?></h3>
			</div>
			<div class="sui-box-body">
				<p class="shipper-description">
					<?php esc_html_e( 'Are you sure you want to cancel the migration?', 'shipper' ); ?>
					<?php esc_html_e( 'This may cause your destination website to break and need restoring.', 'shipper' ); ?>
				</p>

				<div class="sui-notice">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
							<p>
								<?php esc_html_e( 'Is it stuck or taking a very long time? Package Migration lets you upload a package of your site onto any server (local or live) and be migrated in a matter of minutes.', 'shipper' ); ?>
							</p>
						</div>
					</div>
				</div>

				<div class="shipper-cancel-actions">
					<a href="<?php echo esc_url( remove_query_arg( array( 'check', 'begin' ) ) ); ?>" class="sui-button sui-button-ghost shipper-migration-continue">
						<?php esc_html_e( 'Go back', 'shipper' ); ?>
					</a>
					<a href="<?php echo esc_url( remove_query_arg( array( 'check', 'begin', 'type', 'site' ) ) ); ?>" class="sui-button sui-button-ghost shipper-migration-cancel">
						<?php esc_html_e( 'Cancel migration', 'shipper' ); ?>
					</a>
				</div>

			</div><?php // .sui-box-body ?>
		</div><?php // .sui-box ?>

	</div><?php // .sui-modal-content ?>
</div><?php // .sui-modal ?>