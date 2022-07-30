<?php
/**
 * Shipper modal template partials: user add modal (permissions page)
 *
 * @since v1.0.3
 *
 * @package shipper
 */

?>
<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="shipper-add-user"
		class="sui-modal-content sui-content-fade-in"
		aria-modal="true"
		aria-labelledby="shipper-add-user-title"
		aria-describedby="shipper-add-user-desc">
			<div class="sui-box">
				<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
					<button class="sui-button-icon sui-button-float--right shipper-cancel">
						<i class="sui-icon-close sui-md" aria-hidden="true"></i>
						<span class="sui-screen-reader-text"><?php esc_html_e( 'Close', 'shipper' ); ?></span>
					</button>

					<h3 id="shipper-add-user-title" class="sui-box-title sui-lg">
						<?php esc_html_e( 'Add User', 'shipper' ); ?>
					</h3>

					<p id="shipper-add-user-desc" class="shipper-description">
						<?php esc_html_e( 'Add as many administrators as you like. Only these specific users will be able to see the Shipper menu.', 'shipper' ); ?>
					</p>
				</div>

				<div class="sui-box-body">
					<div class="sui-form-field">
						<label for="shipper-search-users" id="shipper-search-users-label" class="sui-label">
							<?php esc_html_e( 'Search users', 'shipper' ); ?>
						</label>

						<div class="sui-control-with-icon">
							<select class="sui-select sui-form-control"
								id="shipper-permissions-add"
								data-placeholder="<?php esc_html_e( 'Type Username', 'shipper' ); ?>"
								data-minimum-input-length="3"
								data-wpnonce="<?php echo esc_attr( wp_create_nonce( 'usersearch' ) ); ?>"
							>
							</select>

							<i class="sui-icon-profile-male" aria-hidden="true"></i>
						</div>
					</div>
				</div>

				<div class="shipper-actions sui-box-footer sui-content-separated">
					<button class="sui-button sui-button-ghost shipper-cancel">
						<?php esc_html_e( 'Cancel', 'shipper' ); ?>
					</button>
					<button
						class="sui-button shipper-add"
						data-add="<?php echo esc_attr( wp_create_nonce( 'shipper_user_access_add' ) ); ?>"
					>
						<span><?php esc_html_e( 'Add', 'shipper' ); ?></span>
					</button>
				</div>
			</div>
	</div>
</div>