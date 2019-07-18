<?php
/**
 * Shipper settings: Permissions subpage template
 *
 * @since v1.0.3
 *
 * @package shipper
 */

$model = new Shipper_Model_Stored_Options;
$per_page = $model->get( Shipper_Model_Stored_Options::KEY_PER_PAGE, 10 );
$users = shipper_get_allowed_users();
?>

<div class="sui-box shipper-page-settings-permissions">
	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'Permissions', 'shipper' ); ?></h2>
	</div>

	<form method="POST">
		<input type="hidden"
		       name="permissions[shipper-nonce]"
		       value="<?php echo esc_attr( wp_create_nonce( 'shipper-permissions' ) ); ?>"/>
		<div class="sui-box-body">
			<div class="sui-box-settings-row">
				<div class="sui-box-settings-col-1">
					<label class="sui-settings-label">
						<?php esc_html_e( 'Visibility', 'shipper' ); ?>
					</label>
					<p class="sui-description">
						<?php esc_html_e( 'By default, only the user who authenticated the WPMU DEV Dashboard can access Shipper. Enable other users to access Shipper by adding them here. ', 'shipper' ); ?>
					</p>
				</div>

				<div class="sui-box-settings-col-2">


					<div class="shipper-form-item shipper-users-list">

						<div class="sui-recipients">
						<?php
						foreach ( $users as $user_id ) {
							if ( empty( $user_id ) ) {
								continue;
							}
							$email = false;
							$user_data = get_userdata( $user_id );
							if ( ! empty( $user_data->user_email ) ) {
								$email = $user_data->user_email;
							}
							if ( empty( $email ) ) {
								continue;
							}
							$name = shipper_get_user_name( $user_id );

							$this->render(
								'pages/settings/permissions-user',
								array(
									'user_id' => $user_id,
									'email' => $email,
									'name' => shipper_get_user_name( $user_id ),
								)
							);
						}
						?>
						</div>
					</div>

					<button
						class="sui-button sui-button-ghost shipper-reveal-add"
						type="button">
						<i class="sui-icon-plus" aria-hidden="true"></i>
						<?php esc_html_e( 'Add user', 'shipper' ); ?>
					</button>

					<?php $this->render( 'modals/user-add' ); ?>


				</div>
			</div>
		</div>

		<div class="sui-box-footer shipper-settings-footer">
			<div class="sui-col shipper-actions">
				<button class="sui-button sui-button-primary shipper-permissions-save">
					<?php esc_html_e( 'Save changes', 'shipper' ); ?>
				</button>
			</div>
		</div>
	</form>

<?php // Status notifications ?>
<div style="display:none" class="sui-notice-top sui-notice-error sui-can-dismiss shipper-permissions-notice">
	<div class="sui-notice-content">
		<p><?php esc_html_e( 'Error adding user', 'shipper' ); ?></p>
	</div>
</div>
<div style="display:none" class="sui-notice-top sui-notice-success sui-can-dismiss shipper-permissions-notice">
	<div class="sui-notice-content">
		<p><?php esc_html_e( '{{USER}} is added as a user but you still need to save changes.', 'shipper' ); ?></p>
	</div>
</div>
<?php // End status notifications. ?>

</div>
