<?php
namespace MC4WP\Sync\Admin;

use MC4WP\Sync\Plugin;

defined( 'ABSPATH' ) or exit;


/** @var StatusIndicator $status_indicator */
/** @var array $available_mailchimp_fields */
?>
<div class="wrap" id="mc4wp-admin">

	<p class="breadcrumbs">
		<span class="prefix"><?php echo __( 'You are here: ', 'mailchimp-for-wp' ); ?></span>
		<a href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp' ); ?>">MailChimp for WordPress</a> &rsaquo;
		<span class="current-crumb"><strong>User Sync</strong></span>
	</p>

	<div class="main-content row">

		<!-- Main Content -->
		<div class="main-content col col-4 col-sm-6">
			<h1 class="page-title">MailChimp User Sync</h1>

			<form method="post" action="<?php echo admin_url( 'options.php' ); ?>" id="settings-form">

				<?php settings_fields( Plugin::OPTION_NAME ); ?>

				<h2><?php _e( 'Settings' ); ?></h2>
				<?php settings_errors(); ?>

				<table class="form-table">

					<tr>
						<th scope="row"><?php _e( 'Enable auto-sync', 'mailchimp-sync' ); ?></th>
						<td class="nowrap">
							<label><input type="radio" name="<?php echo $this->name_attr( 'enabled' ); ?>" value="1" <?php checked( $this->options['enabled'], 1 ); ?> /> <?php _e( 'Yes' ); ?></label> <br />
							<label><input type="radio" name="<?php echo $this->name_attr( 'enabled' ); ?>" value="0" <?php checked( $this->options['enabled'], 0 ); ?> /> <?php _e( 'No' ); ?></label>
							<p  class="help"><?php _e( 'Select "yes" if you want the plugin to "listen" to all changes in your WordPress user base and auto-sync them with the selected MailChimp list.', 'mailchimp-sync' ); ?></p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Sync users with this list', 'mailchimp-sync' ); ?></th>
						<td>
							<?php if( empty( $lists ) ) {
								printf( __( 'No lists found, <a href="%s">are you connected to MailChimp</a>?', 'mailchimp-for-wp' ), admin_url( 'admin.php?page=mailchimp-for-wp' ) ); ?>
							<?php } else { ?>

							<select name="<?php echo $this->name_attr( 'list' ); ?>" class="widefat">
								<option disabled <?php selected( $this->options['list'], '' ); ?>><?php _e( 'Select a list..', 'mailchimp-sync' ); ?></option>
								<?php foreach( $lists as $list ) { ?>
									<option value="<?php echo esc_attr( $list->id ); ?>" <?php selected( $this->options['list'], $list->id ); ?>><?php echo esc_html( $list->name ); ?></option>
								<?php } ?>
							</select>
							<?php } ?>

							<p class="help"><?php _e( 'Select the list to synchronize your WordPress user base with.' ,'mailchimp-sync' ); ?></p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Double opt-in?', 'mailchimp-for-wp' ); ?></th>
						<td class="nowrap">
							<label>
								<input type="radio" name="<?php echo $this->name_attr( 'double_optin' ); ?>" value="1" <?php checked( $this->options['double_optin'], 1 ); ?> />
								<?php _e( 'Yes', 'mailchimp-for-wp' ); ?>  &nbsp; <em><?php _e( '(recommended)', 'mailchimp-sync'); ?></em>
							</label> <br />
							<label>
								<input type="radio" id="mc4wp_checkbox_double_optin_0" name="<?php echo $this->name_attr( 'double_optin' ); ?>" value="0" <?php checked( $this->options['double_optin'], 0 ); ?> />
								<?php _e( 'No', 'mailchimp-for-wp' ); ?>
							</label>

							<p class="help">
								<?php _e( 'Select "no" if you do not want people to verify their email address before they are subscribed.', 'mailchimp-sync' ); ?>
								<?php printf( __( '<strong>Warning: </strong> this may affect your <a href="%s">GDPR compliance</a>.', 'mailchimp-sync' ), 'https://kb.mc4wp.com/gdpr-compliance/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=integrations-page' ); ?> 	
							</p>
						</td>
					</tr>

					<?php
					// this option was removed in v4.0
					if( version_compare( MC4WP_VERSION, '4.0', '<' ) ) { ?>
					<?php $enabled = !$this->options['double_optin']; ?>
					<tr id="mc4wp-send-welcome"  valign="top" <?php if(!$enabled) { ?>class="hidden"<?php } ?>>
						<th scope="row"><?php _e( 'Send Welcome Email?', 'mailchimp-for-wp' ); ?></th>
						<td class="nowrap">
							<input type="radio" id="mc4wp_checkbox_send_welcome_1" name="<?php echo $this->name_attr( 'send_welcome' ); ?>" value="1" <?php if($enabled) { checked( $this->options['send_welcome'], 1 ); } else { echo 'disabled'; } ?> />
							<label for="mc4wp_checkbox_send_welcome_1"><?php _e( 'Yes', 'mailchimp-for-wp' ); ?></label> &nbsp;
							<input type="radio" id="mc4wp_checkbox_send_welcome_0" name="<?php echo $this->name_attr( 'send_welcome' ); ?>" value="0" <?php if($enabled) { checked( $this->options['send_welcome'], 0 ); } else { echo 'disabled'; } ?> />
							<label for="mc4wp_checkbox_send_welcome_0"><?php _e( 'No', 'mailchimp-for-wp' ); ?></label> &nbsp;

							<p class="help">
								<?php _e( 'Select "yes" if you want to send your lists Welcome Email if a subscribe succeeds (only when double opt-in is disabled).', 'mailchimp-for-wp' ); ?>
							</p>
						</td>
					</tr>
					<?php } ?>

					<tr valign="top">
						<th scope="row"><?php _e( 'Role to sync', 'mailchimp-sync' ); ?></th>
						<td>
							<select name="<?php echo $this->name_attr('role'); ?>" id="role-select">
								<option value="" <?php selected( $this->options['role'], '' ); ?>><?php _e( 'All roles', 'mailchimp-sync' ); ?></option>
								<?php
								$roles = get_editable_roles();
								foreach( $roles as $key => $role ) {
									echo '<option value="' . $key . '" '. selected( $this->options['role'], $key, false ) .'>' . $role['name'] . '</option>';
								}
								?>
							</select>

							<p class="help"><?php _e( 'Select a specific role to synchronize.', 'mailchimp-sync' ); ?></p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<label><?php _e( 'Send Additional Fields', 'mailchimp-sync' ); ?></label>
							<small style="display: block; font-weight: normal; margin: 6px 0;"><?php _e( 'optional', 'mailchimp-sync' ); ?></small>
						</th>
						<td class="mc4wp-sync-field-map">
							<?php

							if( ! isset( $selected_list ) ) {
								echo '<p class="help">' . __( 'Please select a MailChimp list first (and then save your settings).', 'mailchimp-sync' ) . '</p>';
							} else {

								foreach( $this->options['field_mappers'] as $index => $rule ) {
								?>
								<div class="field-map-row">
									<input name="<?php echo $this->name_attr( '[field_mappers]['.$index.'][user_field]' ); ?>" class="user-field" value="<?php echo esc_attr( $rule['user_field'] ); ?>" placeholder="<?php _e( 'User field' ,'mailchimp-sync' ); ?>">

									&nbsp; <?php _e( 'to', 'mailchimp-sync' ); ?> &nbsp;

									<select name="<?php echo $this->name_attr( '[field_mappers]['.$index.'][mailchimp_field]' ); ?>" class="mailchimp-field">
										<option disabled <?php selected( $rule['mailchimp_field'], '' ); ?>><?php esc_html_e( 'MailChimp field', 'mailchimp-sync' ); ?></option>
										<?php foreach( $available_mailchimp_fields as $field ) { ?>
											<option value="<?php echo esc_attr( $field->tag ); ?>" <?php selected( $field->tag, $rule['mailchimp_field'] ); ?>>
												<?php echo strip_tags( $field->name ); ?>
											</option>
										<?php } ?>
									</select>
									<?php
									// output button to remove this row
									if( $index > 0 ) {
										echo '<input type="button" value="&times;" class="button remove-row" />';
									} ?>
								</div>
								<?php
								}
								?>

								<p><input type="button" class="button add-row" value="&plus; <?php esc_attr_e( 'Add line', 'mailchimp-sync' ); ?>" style="margin-left:0; "/></p>

								<p class="help">
									<?php printf( __( '<strong>Advanced:</strong> This allows you to <a href="%s">synchronise %s with specific MailChimp fields</a>.', 'mailchimp-sync' ), 'https://mc4wp.com/kb/syncing-custom-user-fields-mailchimp/#utm_source=wp-plugin&utm_medium=mailchimp-sync&utm_campaign=settings-page', '"user meta"' ); ?>
								</p>

							<?php } ?>
						</td>
					</tr>

				<tr valign="top">
					<th scope="row"><?php _e( 'Users must opt-in?', 'mailchimp-for-wp' ); ?></th>
					<td class="nowrap">
						<label>
							<input type="radio" name="<?php echo $this->name_attr( 'enable_user_control' ); ?>" value="1" <?php checked( $this->options['enable_user_control'], 1 ); ?> />
							<?php _e( 'Yes', 'mailchimp-for-wp' ); ?> &nbsp; <em><?php _e( '(recommended)', 'mailchimp-sync'); ?></em>
						</label><br />
						<label>
							<input type="radio" name="<?php echo $this->name_attr( 'enable_user_control' ); ?>" value="0" <?php checked( $this->options['enable_user_control'], 0 ); ?> />
							<?php _e( 'No', 'mailchimp-for-wp' ); ?>
						</label>
						<p class="help">
							<?php _e( 'Select "no" if you do not want users to be able to manage their sign-up status from their profile page.', 'mailchimp-for-wp' ); ?> 
							<?php printf( __( '<strong>Warning: </strong> this may affect your <a href="%s">GDPR compliance</a>.', 'mailchimp-sync' ), 'https://kb.mc4wp.com/gdpr-compliance/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=integrations-page' ); ?> 
						</p>

					</td>
				</tr>
	
				<?php $config = array( 'element' => 'mailchimp_sync[enable_user_control]', 'value' => 1 ); ?>
				<tr valign="top" data-showif="<?php echo esc_attr( json_encode( $config ) ); ?>">
					<th scope="row"><?php _e( 'Default opt-in status', 'mailchimp-for-wp' ); ?></th>
					<td class="nowrap">
						<label>
							<input type="radio" name="<?php echo $this->name_attr( 'default_optin_status' ); ?>" value="unsubscribed" <?php checked( $this->options['default_optin_status'], 'unsubscribed' ); ?> />
							<?php _e( 'Not subscribed', 'mailchimp-for-wp' ); ?> &nbsp; <em><?php _e( '(recommended)', 'mailchimp-sync'); ?></em>
						</label> <br />
						<label>
							<input type="radio" name="<?php echo $this->name_attr( 'default_optin_status' ); ?>" value="subscribed" <?php checked( $this->options['default_optin_status'], 'subscribed' ); ?> />
							<?php _e( 'Subscribed', 'mailchimp-for-wp' ); ?>
						</label>
						
						<p class="help">
							<?php _e( 'Select "subscribed" if you want users to be subscribed by default.', 'mailchimp-for-wp' ); ?> 
							<?php printf( __( '<strong>Warning: </strong> this may affect your <a href="%s">GDPR compliance</a>.', 'mailchimp-sync' ), 'https://kb.mc4wp.com/gdpr-compliance/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=integrations-page' ); ?> 							
						</p>
					</td>
				</tr>
	
				<tr valign="top" data-showif="<?php echo esc_attr( json_encode( $config ) ); ?>">
						<th scope="row"><?php _e( 'User profile heading text', 'mailchimp-for-wp' ); ?></th>
						<td class="nowrap">
							<input type="text" name="<?php echo $this->name_attr( 'user_profile_heading_text' ); ?>" value="<?php echo esc_attr( $this->options['user_profile_heading_text'] ); ?>" class="regular-text" />
							<p class="help"><?php _e( 'Enter the heading text you want to show on the user\'s profile for managing their sign-up status.', 'mailchimp-for-wp' ); ?></p>
						</td>
					</tr>
				<tr valign="top" data-showif="<?php echo esc_attr( json_encode( $config ) ); ?>">
						<th scope="row"><?php _e( 'User profile label text', 'mailchimp-for-wp' ); ?></th>
						<td class="nowrap">
							<input type="text" name="<?php echo $this->name_attr( 'user_profile_label_text' ); ?>" value="<?php echo esc_attr( $this->options['user_profile_label_text'] ); ?>" class="regular-text" />
							<p class="help"><?php _e( 'Enter the label text you want to show on the user\'s profile for managing their sign-up status.', 'mailchimp-for-wp' ); ?></p>
						</td>
					</tr>




				</table>

				<?php submit_button(); ?>


			<?php if( '' !== $this->options['list'] ) { ?>

				<hr style="margin: 50px 0;" />

			
				<?php 
				if( $this->options['enabled'] ) { 
					echo '<h2>' . __( 'Background processing', 'mailchimp-sync' ) . '</h2>';
					echo '<p>' . __( 'The plugin is currently listening to changes in your users and will automatically keep your userbase synced with the selected MailChimp list.', 'mailchimp-sync' ) . '</p>';
               
               if( $this->queue instanceof \MC4WP_Queue ) {
               	$number_of_pending_jobs = count( $this->queue->all() );
                  echo '<p>' . sprintf( __( 'There are <strong>%d</strong> background jobs waiting to be processed.', 'mailchimp-sync' ), $number_of_pending_jobs ) . '</p>';
               		
               	if( $number_of_pending_jobs > 0 ) {
               		echo '<p><a class="button" href="' . add_query_arg( array( '_mc4wp_action' => 'process_user_sync_queue' ) ) . '">' . __( 'Process', 'mailchimp-sync' ) . '</a></p>';
               	}
               	
               } 

               echo '<p class="help">' . sprintf( __( 'Keep an eye on the <a href="%s">debug log</a> for any errors in the background sync.', 'mailchimp-sync' ), admin_url( 'admin.php?page=mailchimp-for-wp-other' ) ) . '</p>';

					echo '<hr style="margin: 50px 0;" />';
				} 
				?>

				

				<h2><?php _e( 'Manual Synchronization', 'mailchimp-sync' ); ?></h2>

				<p><?php _e( 'Clicking the following button will perform a manual re-sync of all users matching the given role criteria.', 'mailchimp-sync' ); ?></p>

				<div id="wizard">
					<?php _e( 'Please enable JavaScript to use the Synchronisation Wizard.', 'mailchimp-sync' ); ?>
				</div>

				<hr style="margin: 50px 0;" />

				<h2><?php _e( 'Webhook', 'mailchimp-sync' ); ?></h2>
				<p>If you want to synchronize changes in your MailChimp list back to your WordPress database then you will have to <a href="https://mc4wp.com/kb/configure-webhook-for-2-way-synchronizing/">configure a webhook in your MailChimp account</a>.</p>

				<table class="form-table">
					<tr valign="top">
						<th><label for="webhook-secret-key-input"><?php _e( 'Secret Key', 'mailchimp-sync' ); ?></label></th>
						<td>
							<input type="text" id="webhook-secret-key-input" pattern="[a-zA-Z0-9_]*" name="mailchimp_sync[webhook][secret_key]" value="<?php echo esc_attr( $this->options['webhook']['secret_key'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Your secret key..', 'mailchimp-sync' ); ?>" <?php if( ! empty( $this->options['webhook']['secret_key'] ) ) { echo 'readonly'; } ?> />
							<input id="webhook-generate-button" class="button" type="button" value="<?php esc_attr_e( 'Generate Key', 'mailchimp-sync' ); ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th><label for="webhook-url-input"><?php _e( 'Webhook URL', 'mailchimp-sync' ); ?></label></th>
						<td><input class="widefat" id="webhook-url-input" data-url-format="<?php echo site_url( '/mc4wp-sync-api/webhook-listener?%s' ); ?>" readonly value="<?php echo esc_attr( site_url( sprintf( '/mc4wp-sync-api/webhook-listener?%s', $this->options['webhook']['secret_key'] ) ) ); ?>" onfocus="this.select()" /></td>
					</tr>
				</table>

				<?php submit_button(); ?>

			<?php } ?>

			</form>

			<br style="margin: 40px 0;" />

		<!-- / Main Content -->
		</div>

		<!-- Sidebar -->
		<div class="sidebar col col-2">
			<?php include MC4WP_PLUGIN_DIR . '/includes/views/parts/admin-sidebar.php'; ?>
		</div>

	<!-- / Row -->
	</div>

	<?php
	/**
	 * @ignore
	 */
	do_action( 'mc4wp_admin_footer' );
	?>

</div>
