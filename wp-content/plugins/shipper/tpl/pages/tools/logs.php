<?php
/**
 * Shipper tools: Logs subpage template
 *
 * @package shipper
 */

$loglines     = array_reverse( Shipper_Helper_Log::get_lines() );
$download_url = wp_nonce_url(
	admin_url( 'admin-ajax.php?action=shipper_download_log' ),
	'shipper_log_download'
);
?>
<div class="sui-box shipper-page-tools-logs">

	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'Logs', 'shipper' ); ?></h2>
		<div class="sui-actions-right">
			<a href="<?php echo esc_url( $download_url ); ?>" class="sui-button sui-button-ghost">
				<i class="sui-icon-download-cloud" aria-hidden="true"></i>
				<?php esc_html_e( 'Download', 'shipper' ); ?>
			</a>
		<?php if ( Shipper_Controller_Data::get()->is_data_recording_enabled() ) { ?>
			<?php
				$data_url = wp_nonce_url(
					admin_url( 'admin-ajax.php?action=shipper_download_data' ),
					'shipper_data_download'
				);
			?>
			<a href="<?php echo esc_url( $data_url ); ?>" class="sui-button sui-button-ghost">
				<i class="sui-icon-download-cloud" aria-hidden="true"></i>
				<?php esc_html_e( 'Download data', 'shipper' ); ?>
			</a>
		<?php } ?>
		</div>
	</div>

	<div class="sui-box-body">
		<p>
			<?php esc_html_e( 'Here are Shipper\'s latest logs, use them to debug any issues you are having.', 'shipper' ); ?>
		</p>

	<?php if ( ! empty( $loglines ) ) { // phpcs:disable ?>
		<textarea class="sui-form-control" readonly><?php
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			foreach ( $loglines as $line ) {
				echo '' .
				esc_textarea( date_i18n( $format, $line['timestamp'] ) ) .
				' - ' .
				esc_textarea( $line['message'] ) .
				"\n";
			}
			// phpcs:enable
		?>
		</textarea>

		<p class="shipper-note">
			<?php esc_html_e( 'Logs will be automatically refreshed each time you initiate a new migration.', 'shipper' ); ?>
		</p>
	<?php } else { ?>
		<div class="sui-notice">
			<div class="sui-notice-content">
				<div class="sui-notice-message">
					<p>
						<?php esc_html_e( 'No event logs have been detected yet.', 'shipper' ); ?>
						<?php esc_html_e( 'They will appear here once you have made an action.', 'shipper' ); ?>
					</p>
				</div>
			</div>
		</div>
	<?php } ?>

	</div>

</div>