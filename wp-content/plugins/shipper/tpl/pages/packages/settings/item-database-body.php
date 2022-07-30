<?php
/**
 * Shipper package settings templates: database item body template
 *
 * @since v1.1
 * @package shipper
 */

$model           = new Shipper_Model_Stored_Options();
$has_binary      = Shipper_Helper_System::has_command( 'mysqldump' );
$can_call_system = Shipper_Helper_System::can_call_system();
if ( $can_call_system ) {
	$has_binary = $model->get(
		Shipper_Model_Stored_Options::KEY_PACKAGE_DB_BINARY,
		$has_binary
	);
}
$shell_active = $has_binary && $can_call_system;
$db_rows      = $model->get(
	Shipper_Model_Stored_Options::KEY_PACKAGE_DB_LIMIT,
	5000
);
?>
<div class="sui-form-field">
	<?php if ( ! Shipper_Helper_System::is_wpmudev_host() ) : ?>
		<span class="sui-label">
		<?php esc_html_e( 'SQL Script', 'shipper' ); ?>
		</span>
		<span class="sui-description">
		<?php echo wp_kses_post( 'Choose how you want to build the database scripts in your packages. We recommend using <strong>MySQLDump</strong> method whenever possible. However, if your host doesn\'t support this or it\'s causing some issues in the package build process, you can fall back to the <strong>PHP Code</strong> method to build the database scripts.', 'shipper' ); ?>
		</span>
		<div class="sui-side-tabs sui-tabs">
			<div data-tabs>
				<div class="<?php echo $shell_active ? 'active' : ''; ?>">
					<?php esc_html_e( 'MySQLDump', 'shipper' ); ?>
				</div>
				<div class="<?php echo ! $shell_active ? 'active' : ''; ?>">
					<?php esc_html_e( 'PHP Code', 'shipper' ); ?>
				</div>
			</div><!-- data-tabs -->

			<div data-panes>
				<?php if ( ! empty( $has_binary ) && ! empty( $can_call_system ) ) { ?>
					<div class="active">
						<input type="radio" name="database-use-binary" value="1" style="display:none">
					</div>
				<?php } else { ?>
					<div class="sui-tab-boxed shipper-tab-boxed-error">
						<input type="radio" name="database-use-binary" value="0" style="display:none">
						<?php if ( ! empty( $can_call_system ) ) { ?>
							<?php if ( empty( $has_binary ) ) { ?>
								<div class="sui-notice sui-notice-error">
									<div class="sui-notice-content">
										<div class="sui-notice-message">
											<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
											<p><?php esc_html_e( 'We couldn\'t find MySQLDump at the default location. Please make sure it is installed or contact your hosting support to install it for you.', 'shipper' ); ?></p>
										</div>
									</div>
								</div>
							<?php } // has_binary ?>
						<?php } else { ?>
							<div class="sui-notice sui-notice-error">
								<div class="sui-notice-content">
									<div class="sui-notice-message">
										<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
										<p><?php esc_html_e( 'The MySQLDump method requires PHP shell_exec function to work, and your host doesn\'t support that. Please contact your hosting support or system admin to allow the shell_exec function.', 'shipper' ); ?></p>
									</div>
								</div>
							</div>
						<?php } // can call system ?>
					</div><!-- sui-tab-boxed -->
				<?php } // has binary ?>
				<div class="sui-tab-boxed <?php echo ! $shell_active ? 'active' : ''; ?>">
					<input type="radio" name="database-use-binary" value="0" style="display:none">
					<label class="sui-label">
						<?php esc_html_e( 'Query Limit', 'shipper' ); ?>
					</label>
					<select name="database-export-rows">
						<?php foreach ( shipper_get_query_limit() as $rows ) { ?>
							<option <?php selected( $rows, $db_rows ); ?>
									value="<?php echo (int) $rows; ?>">
								<?php echo (int) $rows; ?>
							</option>
						<?php } ?>
					</select>
					<span class="sui-description">
					<?php esc_html_e( 'We recommend using a lower query limit on the budget hosts. A higher limit size will speed up the build time, but it will use more memory.', 'shipper' ); ?>
				</span>
				</div><!-- sui-tab-boxed -->
			</div><!-- data-panes -->
		</div><!-- sui-side-tabs -->
	<?php else : ?>
		<span class="sui-label">
		<?php esc_html_e( 'Query limit', 'shipper' ); ?>
		</span>
		<span class="sui-description">
		<?php esc_html_e( 'Choose the query limit to build SQL scripts. We recommend using a lower query limit on the budget hosts. A higher limit size will speed up the build time, but it will use more memory.', 'shipper' ); ?>
		</span>
		<input type="radio" name="database-use-binary" value="0" style="display:none">
		<select name="database-export-rows">
			<?php foreach ( shipper_get_query_limit() as $rows ) { ?>
				<option <?php selected( $rows, $db_rows ); ?>
					value="<?php echo (int) $rows; ?>">
					<?php echo (int) $rows; ?>
				</option>
			<?php } ?>
		</select>
	<?php endif; ?>
</div><!-- sui-form-field -->