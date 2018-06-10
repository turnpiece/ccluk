<?php
/**
 *
 */
?>

<?php
$content_hide = false;
?>

<div id="wpmudev-settings-widget-modules" class="wpmudev-box wpmudev-box-close">

    <div class="wpmudev-box-head">

        <h2><?php _e( "Don't show modules to", Opt_In::TEXT_DOMAIN ); ?></h2>

        <div class="wpmudev-box-action"><?php $this->render("general/icons/icon-plus" ); ?></div>

    </div>

    <div class="wpmudev-box-body<?php if ($content_hide === true) { echo ' wpmudev-hidden'; } ?>">

        <table cellspacing="0" cellpadding="0" class="wpmudev-table">

			<thead>

				<tr>

					<th><?php _e( "Module Name", Opt_In::TEXT_DOMAIN ); ?></th>

					<th><?php _e( "Logged-in User", Opt_In::TEXT_DOMAIN ); ?></th>

					<th><?php _e( "Admin", Opt_In::TEXT_DOMAIN ); ?></th>

				</tr>

			</thead>

            <tbody>

                <?php foreach( $modules as $module ) :

					$admin_id = esc_attr( "hustle-module-admin" . $module->id );
					$logged_id = esc_attr( "hustle-module-logged_in" . $module->id ); ?>

                	<tr>

						<td>

							<div class="wpmudev-settings-module-name">

								<?php if ( $module->module_type === "popup" ) { $tooltip = __( "Pop-up", Opt_In::TEXT_DOMAIN ); }

								else if ( $module->module_type === "slidein" ) { $tooltip = __( "Slide-in", Opt_In::TEXT_DOMAIN ); }

								else if ( $module->module_type === "embedded" ) { $tooltip = __( "Embed", Opt_In::TEXT_DOMAIN ); }

								else if ( $module->module_type === "social_sharing" ) { $tooltip = __( "Social Sharing", Opt_In::TEXT_DOMAIN ); } ?>

								<div class="wpmudev-module-icon wpmudev-tip" data-tip="<?php echo $tooltip; ?>">

									<?php if ( $module->module_type === "popup" ) { ?>

										<?php $this->render("general/icons/admin-icons/icon-popup" ); ?>

									<?php } ?>

									<?php if ( $module->module_type === "slidein" ) { ?>

										<?php $this->render("general/icons/admin-icons/icon-slidein" ); ?>

									<?php } ?>

									<?php if ( $module->module_type === "embedded" ) { ?>

										<?php $this->render("general/icons/admin-icons/icon-shortcode" ); ?>

									<?php } ?>

									<?php if ( $module->module_type === "social_sharing" ) { ?>

										<?php $this->render("general/icons/admin-icons/icon-shares" ); ?>

									<?php } ?>

								</div>

								<div class="wpmudev-module-name"><?php echo $module->module_name ?></div>

							</div>

						</td>

						<td data-title="<?php _e( 'Logged-in User', Opt_In::TEXT_DOMAIN ); ?>">

							<div class="wpmudev-switch">

								<input id="<?php echo $logged_id; ?>" class="toggle-checkbox hustle-for-logged-in-user-toggle" type="checkbox" data-user="logged_in" data-nonce="<?php echo $modules_state_toggle_nonce; ?>" data-id="<?php echo esc_attr( $module->id ); ?>" <?php checked( !$module->is_active_for_logged_in_user, 1 ); ?>>

								<label class="wpmudev-switch-design" for="<?php echo $logged_id; ?>" aria-hidden="true"></label>

							</div>

						</td>

						<td data-title="<?php _e( 'Admin', Opt_In::TEXT_DOMAIN ); ?>">

							<div class="wpmudev-switch">

								<input id="<?php echo $admin_id; ?>" class="toggle-checkbox hustle-for-admin-user-toggle" type="checkbox" data-user="admin" data-nonce="<?php echo $modules_state_toggle_nonce; ?>" data-id="<?php echo esc_attr( $module->id ); ?>" <?php checked( !$module->is_active_for_admin, 1 ); ?>>

								<label class="wpmudev-switch-design" for="<?php echo $admin_id; ?>" aria-hidden="true"></label>

							</div>

						</td>

					</tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>