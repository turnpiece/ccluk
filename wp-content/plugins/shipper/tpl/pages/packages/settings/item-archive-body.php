<?php
/**
 * Shipper package settings templates: archive item body template
 *
 * @since v1.1
 * @package shipper
 */

$model           = new Shipper_Model_Stored_Options();
$has_binary      = Shipper_Helper_System::has_command( 'zip' );
$can_call_system = Shipper_Helper_System::can_call_system();
if ( $can_call_system ) {
	$has_binary = $model->get(
		Shipper_Model_Stored_Options::KEY_PACKAGE_ZIP_BINARY,
		$has_binary
	);
}
$shell_active  = $has_binary && $can_call_system;
$fs_file_limit = $model->get( Shipper_Model_Stored_Options::KEY_PACKAGE_ZIP_LIMIT, 5000 );
?>
<div class="sui-form-field">
	<?php if ( ! Shipper_Helper_System::is_wpmudev_host() ) : ?>
		<span class="sui-label">
		<?php esc_html_e( 'Archive Engine', 'shipper' ); ?>
		</span>
		<span class="sui-description">
		<?php
		echo wp_kses_post( __( 'Choose the archive engine you want to use for creating the package.zip. We recommend using the <b>Shell Zip</b> method since it uses your server\'s internal shell commands for creating the zip file. However, if your host doesn\'t support the Shell Zip method, you can fall back to the <b>ZipArchive</b> method.  ', 'shipper' ) );
		?>
		</span>

		<div class="sui-side-tabs sui-tabs">
			<div data-tabs>
				<div class="<?php echo $shell_active ? 'active' : ''; ?>">
					<?php esc_html_e( 'Shell Zip', 'shipper' ); ?>
				</div>
				<div class="<?php echo ! $shell_active ? 'active' : ''; ?>">
					<?php esc_html_e( 'PHP ZipArchive', 'shipper' ); ?>
				</div>
			</div><!-- data-tabs -->

			<div data-panes>
				<?php if ( ! empty( $has_binary ) && ! empty( $can_call_system ) ) { ?>
					<div class="active">
						<input type="radio" name="archive-use-binary" value="1" style="display:none">
					</div>
				<?php } else { ?>
					<div class="sui-tab-boxed shipper-tab-boxed-error">
						<input type="radio" name="archive-use-binary" value="0" style="display:none">
						<?php if ( ! empty( $can_call_system ) ) { ?>
							<?php if ( empty( $has_binary ) ) { ?>
								<div class="sui-notice sui-notice-error">
									<div class="sui-notice-content">
										<div class="sui-notice-message">
											<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
											<p><?php esc_html_e( 'We couldn\'t find Shell Zip at the default location. Please make sure it is installed or contact your hosting support to install it for you.', 'shipper' ); ?></p>
										</div>
									</div>
								</div>
							<?php } // has_binary ?>
						<?php } else { ?>
								<div class="sui-notice sui-notice-error">
									<div class="sui-notice-content">
										<div class="sui-notice-message">
											<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
											<p><?php esc_html_e( 'The Shell ZIP method requires PHP shell_exec function to work, and your host doesn\'t support that. Please contact your hosting support or system admin to allow the shell_exec function.', 'shipper' ); ?></p>
										</div>
									</div>
								</div>
						<?php } // can call system ?>
					</div><!-- sui-tab-boxed -->
				<?php } ?>
				<div class="sui-tab-boxed <?php echo ! $shell_active ? 'active' : ''; ?>">
					<input type="radio" name="archive-use-binary" value="0" style="display:none">
					<label class="sui-label">
						<?php esc_html_e( 'File Limit', 'shipper' ); ?>
					</label>
					<select name="archive-buffer-size">
						<?php foreach ( shipper_get_zip_file_limit() as $file_limit ) { ?>
							<option <?php selected( $file_limit, $fs_file_limit ); ?>
									value="<?php echo (int) $file_limit; ?>">
								<?php echo esc_html( $file_limit ); ?>
							</option>
						<?php } ?>
					</select>
					<span class="sui-description">
					<?php esc_html_e( 'Number of files to be processed in each chunk of the archive in multi-threaded mode. A larger value will result in a faster build but more unstable on some budget hosts.', 'shipper' ); ?>
				</span>
				</div><!-- sui-tab-boxed -->
			</div><!-- data-panes -->
		</div><!-- sui-side-tabs -->
	<?php else : ?>
		<span class="sui-label">
		<?php esc_html_e( 'File Limit', 'shipper' ); ?>
		</span>
		<span class="sui-description">
		<?php
		echo wp_kses_post( __( 'Number of files to be processed in each chunk of the archive in multi-threaded mode. A larger value will result in a faster build but more unstable on some budget hosts.', 'shipper' ) );
		?>
		</span>
		<select name="archive-buffer-size">
			<?php foreach ( shipper_get_zip_file_limit() as $file_limit ) { ?>
				<option <?php selected( $file_limit, $fs_file_limit ); ?>
					value="<?php echo (int) $file_limit; ?>">
					<?php echo esc_html( $file_limit ); ?>
				</option>
			<?php } ?>
		</select>
		<input type="radio" name="archive-use-binary" value="0" style="display:none">
	<?php endif; ?>
</div><!-- sui-form-field -->