<?php
/**
 * Shipper package migration modals: package preflight issues template
 *
 * @since v1.1
 * @package shipper
 */

?>

	<p class="shipper-description shipper-description" id="<?php echo esc_attr( $main_id ); ?>-description">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: get admin username. */
				__( '%s, we’ve uncovered a few potential issues that may affect the package build and migration process. Take a look through them and action what you like. While you can ignore the warnings, you must fix the errors (if any) to continue your migration.', 'shipper' ),
				shipper_get_user_name()
			)
		);
		?>
	</p>
</div>

<div class="shipper-issues">


</div><!-- shipper-issues -->

<div class="shipper-modal-bottom-actions">
	<div class="shipper-modal-bottom-action-left">
		<button type="button" class="sui-button shipper-restart">
			<i class="sui-icon-update" aria-hidden="true"></i>
			<?php esc_html_e( 'Run Pre-Flight', 'shipper' ); ?>
		</button>
	</div><!-- shipper-modal-bottom-action-left -->
	<div class="shipper-modal-bottom-action-right">
		<button type="button" class="sui-button shipper-next">
			<i class="sui-icon-arrow-right" aria-hidden="true"></i>
			<?php esc_html_e( 'Continue Anyway', 'shipper' ); ?>
	</div><!-- shipper-modal-bottom-action-right -->
</div><!-- shipper-modal-bottom-actions -->