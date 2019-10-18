<?php

/**
 * Permissions settings page template.
 *
 * @var array $roles   Roles array.
 * @var bool  $network Is network settings page?.
 */

defined( 'WPINC' ) || die();

?>

<input type="hidden" name="beehive_settings_group" value="permissions">

<div class="sui-box-settings-row">

	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Roles or Capabilities', 'ga_trans' ); ?></span>
		<span class="sui-description"><?php esc_html_e( 'Choose which user roles or specific capabilities can see analytics in their WordPress Dashboard. User roles that don\'t match these won\'t see your analytics.', 'ga_trans' ); ?></span>
	</div>

	<div class="sui-box-settings-col-2">
		<div class="sui-side-tabs sui-tabs">

			<div data-tabs>
				<div class="active" id="permissions-roles"><?php esc_html_e( 'Roles', 'ga_trans' ); ?></div>
				<div id="permissions-capabilities"><?php esc_html_e( 'Capabilities', 'ga_trans' ); ?></div>
			</div>

			<div data-panes>
				<div class="sui-tab-boxed active">
					<span class="sui-label"><?php esc_html_e( 'Select minimum role', 'ga_trans' ); ?></span>
					<?php foreach ( $roles as $role => $label ) : ?>
						<?php $checked = in_array( $role, beehive_analytics()->settings->get( 'roles', 'permissions', $network, [] ), true ) || 'administrator' === $role; ?>
						<label for="beehive-settings-role-<?php echo esc_attr( $role ); ?>" class="sui-checkbox sui-checkbox-stacked">
							<input type="checkbox" id="beehive-settings-role-<?php echo esc_attr( $role ); ?>" name="permissions[roles][]" value="<?php echo esc_attr( $role ); ?>" <?php checked( $checked ); ?> <?php disabled( $role, 'administrator' ); ?> />
							<span aria-hidden="true"></span>
							<span><?php echo esc_attr( $label ); ?></span>
						</label>
					<?php endforeach; ?>

					<?php if ( $network ) : ?>

						<div class="beehive-notice-toggle">
							<label for="beehive-settings-overwrite-cap" class="sui-toggle">
								<input type="checkbox" id="beehive-settings-overwrite-cap" name="permissions[overwrite_cap]" value="1" <?php checked( beehive_analytics()->settings->get( 'overwrite_cap', 'permissions', true ), 1 ) ?>>
								<span class="sui-toggle-slider"></span>
							</label>
							<label for="beehive-settings-overwrite-cap" class="sui-toggle-label"><?php esc_html_e( 'Allow site admins to overwrite this setting', 'ga_trans' ); ?></label>
						</div>

					<?php endif; ?>

				</div>

				<div class="sui-tab-boxed">
					<div class="sui-form-field">
						<label for="beehive-settings-custom-cap" class="sui-label"><?php _e( 'Custom Capability', 'ga_trans' ); ?></label>
						<input type="text" id="beehive-settings-custom-cap" class="sui-form-control" name="permissions[custom_cap]" placeholder="<?php esc_html_e( 'Set custom capability', 'ga_trans' ); ?>" value="<?php echo beehive_analytics()->settings->get( 'custom_cap', 'permissions', $network ); ?>"/>
						<span class="sui-description"><?php printf( __( 'Specify a custom capability that, if a user role matches it, can see analytics. You can view default capabilities <a href="%s" target="_blank">here</a>.', 'ga_trans' ), 'https://wordpress.org/support/article/roles-and-capabilities/' ); ?></span>
					</div>
				</div>
			</div>

		</div>
	</div>

</div>