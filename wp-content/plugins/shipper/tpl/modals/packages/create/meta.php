<?php
/**
 * Shipper package migration modals: package meta info template
 *
 * @since v1.1
 * @package shipper
 */

$package_name = sprintf(
	'%s-%s',
	date( 'YmdHi' ),
	preg_replace(
		'/[^-_a-z0-9]/i', '',
		Shipper_Model_Stored_Destinations::get_current_domain()
	)
);
?>
<p class="shipper-description" id="<?php echo esc_attr( $main_id ); ?>-description">
	<?php esc_html_e( 'Let\'s begin with the configurations such as giving your package a name and password protecting your installer file.', 'shipper' ); ?>
</p>

<input type="hidden"
	name="shipper-create-package"
	value="<?php echo esc_attr( wp_create_nonce( 'shipper-create-package' ) ); ?>"
/>

<div class="sui-form-field <?php echo esc_attr( $main_id ); ?>-package-name">
	<label class="sui-label" for="<?php echo esc_attr( $main_id ); ?>-package-name">
		<?php esc_html_e( 'Package Name', 'shipper' ); ?>
	</label>
	<input type="text"
		id="<?php echo esc_attr( $main_id ); ?>-package-name"
		name="package-name"
		class="sui-form-control"
		value="<?php echo esc_attr( $package_name ); ?>"
	/>
</div><!-- package-name -->

<div class="sui-form-field <?php echo esc_attr( $main_id ); ?>-package-password">
	<label class="sui-label">
		<?php esc_html_e( 'Password Protection', 'shipper' ); ?>
	</label>
	<div class="sui-tabs sui-side-tabs">
		<div data-tabs>
			<div class="active"><?php esc_html_e( 'Disable', 'shipper' ); ?></div>
			<div><?php esc_html_e( 'Enable', 'shipper' ); ?></div>
		</div><!-- data-tabs -->

		<div data-panes>

			<div data-state="disabled" class="active">
				<p class="sui-p-small"><?php esc_html_e( 'Choose whether you want to restrict the access to the installer file with a password. When enabled, you\'ll be asked to enter your chosen password to begin the installation process.', 'shipper' ); ?></p>
				<input type="hidden" name="password-enabled" value="0"/>
			</div><!-- state disabled -->

			<div data-state="enabled">
				<p class="sui-p-small"><?php esc_html_e( 'Choose whether you want to restrict the access to the installer file with a password. When enabled, you\'ll be asked to enter your chosen password to begin the installation process.', 'shipper' ); ?></p>
				<input type="hidden" name="password-enabled" value="1"/>
				<div class="sui-form-field <?php echo esc_attr( $main_id ); ?>-package-password-password">
					<label class="sui-label">
						<?php esc_html_e( 'Installer Password', 'shipper' ); ?>
					</label>
					<div class="sui-with-button sui-with-button-icon shipper-password">
						<input type="password"
							data-lpignore="true"
							name="installer-password"
							class="sui-form-control"
							placeholder="<?php esc_attr_e( 'Choose your password', 'shipper' ); ?>"
						/>
						<button class="sui-button-icon shipper-toggle-password">
							<i aria-hidden="true" class="sui-icon-eye"></i>
							<span class="sui-password-text sui-screen-reader-text">Show Password</span>
							<span class="sui-password-text sui-screen-reader-text">Hide Password</span>
						</button>
					</div><!-- with button -->
				</div><!-- package-password-password -->
			</div><!-- state enabled -->

		</div><!-- data-panes -->

	</div><!-- .sui-tabs -->
</div><!-- package-password -->

<div class="shipper-modal-bottom-actions">
	<div class="shipper-modal-bottom-action-left">
		<button type="button" class="sui-button sui-button-ghost shipper-cancel">
			<?php esc_html_e( 'Cancel', 'shipper' ); ?>
		</button>
	</div><!-- shipper-modal-bottom-action-left -->
	<div class="shipper-modal-bottom-action-right">
		<button type="button" class="sui-button shipper-next">
			<?php esc_html_e( 'Continue', 'shipper' ); ?>
	</div><!-- shipper-modal-bottom-action-right -->
</div><!-- shipper-modal-bottom-actions -->