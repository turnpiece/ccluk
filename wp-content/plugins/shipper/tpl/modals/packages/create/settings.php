<?php
/**
 * Shipper package migration modals: package-specific settings template
 *
 * @since v1.1
 * @package shipper
 */

?>
	<p class="shipper-description" id="<?php echo esc_attr( $main_id ); ?>-description">
		<?php esc_html_e( 'By default, the package includes your entire website. However, if you don\'t want to migrate any specific files, folders or database tables, you can use the filters below to exclude them from your package.', 'shipper' ); ?>
	</p>
</div>
<div class="sui-box-body sui-box-body-slim">

	<div class="sui-tabs">

		<div data-tabs>
			<div class="active"><?php esc_html_e( 'Files', 'shipper' ); ?></div>
			<div><?php esc_html_e( 'Database', 'shipper' ); ?></div>
			<div><?php esc_html_e( 'Advanced', 'shipper' ); ?></div>
		</div><!-- data-tabs -->

		<div data-panes>
			<div class="active">
				<?php $this->render( 'modals/packages/create/settings-files' ); ?>
			</div>

			<div>
				<p class="shipper-description">
					<?php esc_html_e( 'The selected tables are included in the package. Be careful while excluding database tables because it may break your site or plugins.', 'shipper' ); ?>
				</p>
				<?php $this->render( 'modals/packages/create/settings-database' ); ?>
			</div>

			<div>
				<?php $this->render( 'modals/packages/create/settings-advanced' ); ?>
			</div>

		</div><!-- data-panes -->

	</div><!-- sui-tabs -->
	<?php $this->render( 'modals/exclusion-filters' ); ?>

	<div class="shipper-modal-bottom-actions">
		<div class="shipper-modal-bottom-action-left">
			<button type="button" class="sui-button sui-button-ghost shipper-cancel">
				<?php esc_html_e( 'Cancel', 'shipper' ); ?>
			</button>
		</div><!-- shipper-modal-bottom-action-left -->
		<div class="shipper-modal-bottom-action-right">
			<button type="button" class="sui-button sui-button-primary shipper-next">
				<?php esc_html_e( 'Build Package', 'shipper' ); ?>
		</div><!-- shipper-modal-bottom-action-right -->
	</div><!-- shipper-modal-bottom-actions -->
</div>