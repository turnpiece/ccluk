<?php
/**
 * Shipper settings: Permissions subpage template, user item
 *
 * @since v1.0.3
 *
 * @package shipper
 */

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
	</span>

	<span class="shipper-actions">
		<a href="#remove" type="button"
		   class="sui-button-icon shipper-rmv">
			<i class="sui-icon-trash" aria-hidden="true"></i>
		</a>
	</span>
</div>
