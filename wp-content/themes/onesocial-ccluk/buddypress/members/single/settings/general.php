<?php
/** This action is documented in bp-templates/bp-legacy/buddypress/members/single/settings/profile.php */
do_action( 'bp_before_member_settings_template' );
?>

<form action="<?php echo bp_displayed_user_domain() . bp_get_settings_slug() . '/general'; ?>" method="post" class="standard-form boss-general-settings" id="settings-form">

	<?php if ( !is_super_admin() ) : ?>

		<div class="clearfix current-password-wrapper">
			<label for="pwd"><?php _e( 'Current Password', 'onesocial' ); ?></label>
			<a class="lost-password" href="<?php echo wp_lostpassword_url(); ?>" title="<?php esc_attr_e( 'Password Lost and Found', 'onesocial' ); ?>"><?php _e( 'Lost your password?', 'onesocial' ); ?></a>
		</div>

		<input type="password" name="pwd" id="pwd" size="16" value="" placeholder="<?php _e( 'Enter', 'onesocial' ); ?>" class="settings-input small" <?php bp_form_field_attributes( 'password' ); ?> />

	<?php endif; ?>

	<label for="email"><?php _e( 'Account Email', 'onesocial' ); ?></label>
	<input type="email" name="email" id="email" value="<?php echo bp_get_displayed_user_email(); ?>" class="settings-input" <?php bp_form_field_attributes( 'email' ); ?>/>

	<label for="pass1"><?php _e( 'Change Password <span>(leave blank for no change)</span>', 'onesocial' ); ?></label>
	<input type="password" name="pass1" id="pass1" size="16" value="" class="settings-input small password-entry" <?php bp_form_field_attributes( 'password' ); ?> placeholder="<?php _e( 'New Password', 'buddypress' ); ?>"/>
	<div id="pass-strength-result"></div>
	<input type="password" name="pass2" id="pass2" size="16" value="" class="settings-input small password-entry-confirm" <?php bp_form_field_attributes( 'password' ); ?> placeholder="<?php _e( 'Repeat New Password', 'buddypress' ); ?>"/>

	<?php
	/**
	 * Fires before the display of the submit button for user general settings saving.
	 *
	 * @since BuddyPress (1.5.0)
	 */
	do_action( 'bp_core_general_settings_before_submit' );
	?>

	<div class="submit">
		<input type="submit" name="submit" value="<?php esc_attr_e( 'Save Changes', 'onesocial' ); ?>" id="submit" class="auto" />
	</div>

	<?php
	/**
	 * Fires after the display of the submit button for user general settings saving.
	 *
	 * @since BuddyPress (1.5.0)
	 */
	do_action( 'bp_core_general_settings_after_submit' );
	?>

	<?php wp_nonce_field( 'bp_settings_general' ); ?>

</form>

<?php
/** This action is documented in bp-templates/bp-legacy/buddypress/members/single/settings/profile.php */
do_action( 'bp_after_member_settings_template' );
