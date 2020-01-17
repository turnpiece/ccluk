<?php
/**
 * Shipper package migration modals: package building failure template
 *
 * @since v1.1
 * @package shipper
 */

?>
<div class="sui-notice sui-notice-error">
	<p id="<?php echo esc_attr( $main_id ); ?>-description">
	<?php
		echo wp_kses_post( sprintf(
			__( 'There was an error while trying to build your package! You can check logs for more details or retry creating the package. Not able to solve this? Contact our <a href="%s" target="_blank">support</a> team for further help.', 'shipper' ),
			'https://premium.wpmudev.org/support'
		) );
	?>
	</p>
	<p class="shipper-error-message-wrapper" style="display:none">
		<?php esc_html_e( 'Encountered error:', 'shipper' ); ?>
		<span class="shipper-error-message"></span>
	</p>
</div>

<div class="shipper-modal-bottom-actions">
	<div class="shipper-modal-bottom-action-left">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=shipper-tools' ) ); ?>" type="button" class="sui-button sui-button-ghost shipper-logs">
			<i aria-hidden="true" class="sui-icon-eye"></i>
			<?php esc_html_e( 'View Logs', 'shipper' ); ?>
		</a>
	</div><!-- shipper-modal-bottom-action-left -->
	<div class="shipper-modal-bottom-action-right">
	<button type="button" class="sui-button shipper-restart">
			<i aria-hidden="true" class="sui-icon-update"></i>
			<?php esc_html_e( 'Retry Package', 'shipper' ); ?>
	</div><!-- shipper-modal-bottom-action-right -->
</div><!-- shipper-modal-bottom-actions -->