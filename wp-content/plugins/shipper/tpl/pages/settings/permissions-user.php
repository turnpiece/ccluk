<?php
/**
 * Shipper settings: Permissions subpage template, user item
 *
 * @since v1.0.3
 *
 * @package shipper
 */

$is_current_user    = get_current_user_id() === $user_id;
$is_dashboard_users = in_array( $user_id, shipper_get_dashboard_users(), true );
$tooltip_message    = $is_current_user
	? __( 'You can\'t remove yourself', 'shipper' )
	: __( 'You can\'t remove dashboard user', 'shipper' );

?>
<div class="shipper-user-item sui-recipient">
	<input type="hidden"
		name="permissions[<?php echo esc_attr( Shipper_Model_Stored_Options::KEY_USER_ACCESS ); ?>][]"
		value="<?php echo (int) $user_id; ?>" />
	<span class="shipper-user sui-recipient-name">
		<?php echo esc_html( $name ); ?>
	</span>
	<span class="shipper-email sui-recipient-email">
		<?php echo esc_html( $email ); ?>
		<?php if ( $is_current_user ) : ?>
			<span class="sui-tag"><?php esc_html_e( 'You', 'shipper' ); ?></span>
		<?php endif; ?>
	</span>

	<span class="shipper-actions">
		<a
			href="#remove"
			type="button"
			class="sui-button-icon <?php echo ( $is_dashboard_users || $is_current_user ) ? esc_attr( 'disabled sui-tooltip sui-tooltip-constrained shipper-dash-users' ) : 'shipper-rmv'; ?>"
			style="--tooltip-width: 171px;"
			data-tooltip="<?php echo esc_attr( $tooltip_message ); ?>"
		>
			<i class="sui-icon-trash" aria-hidden="true"></i>
		</a>
	</span>
</div>