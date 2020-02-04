<?php
/**
 * Shipper modal template partials: user add modal (permissions page)
 *
 * @since v1.0.3
 *
 * @package shipper
 */

?>

<div class="sui-dialog sui-dialog-sm" id="shipper-add-user" aria-hidden="true">
	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" role="dialog">

		<div class="sui-box" role="document">
			<div class="sui-box-header">
				<div class="shipper-close">
					<a href="#close">
						<i class="sui-icon-close" aria-hidden="true"></i>
						<span><?php esc_html_e( 'Close', 'shipper' ); ?></span>
					</a>
				</div>
				<h3 class="sui-dialog-title"><?php esc_html_e( 'Add User', 'shipper' ); ?></h3>
			</div>
			<div class="sui-box-body">
				<p>
					<?php esc_html_e( 'Add as many administrators as you like. Only these specific users will be able to see the Shipper menu.', 'shipper' ); ?>
				</p>

				<div class="sui-form-field">
					<label for="" class="sui-label">
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

				<div class="shipper-actions">
					<button
						class="sui-button sui-button-ghost shipper-cancel">
						<?php esc_html_e( 'Cancel', 'shipper' ); ?>
					</button>

					<button
						class="sui-button shipper-add"
						data-add="<?php echo esc_attr(
							wp_create_nonce( 'shipper_user_access_add' )
						); ?>"
						type="button">
						<span><?php esc_html_e( 'Add', 'shipper' ); ?></span>
					</button>
				</div>

			</div><?php // .sui-box-body ?>
		</div><?php // .sui-box ?>

	</div><?php // .sui-dialog-content ?>
</div><?php // .sui-dialog ?>