<?php
if ( $is_edit && $module ) {
    $module_content = $module->get_content();
    $email_services = $module_content->email_services;
    $active_email_service = $module_content->active_email_service;
}
?>
<div id="wph-wizard-content-email" class="wpmudev-box-content {{ ( _.isFalse(use_email_collection) ? 'last' : '' ) }}">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Email collection module", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "Configure if you want to collect emails from visitors who see this embed and how you want those emails to be stored.", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-embedded-email_collection" class="toggle-checkbox" type="checkbox" data-attribute="use_email_collection" {{_.checked(_.isTrue(use_email_collection), true)}}>

				<label class="wpmudev-switch-design" for="wph-embedded-email_collection" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-embedded-email_collection"><?php _e( "Add email collection to this embed", Opt_In::TEXT_DOMAIN ); ?></label>

		</div>

        <table id="wph-wizard-content-email-options" cellspacing="0" cellpadding="0" class="wpmudev-table {{ ( _.isFalse(use_email_collection) ) ? 'wpmudev-hidden_table' : 'wpmudev-show_table' }}">

			<thead>

				<tr><th><?php _e( "Email Collection Service", Opt_In::TEXT_DOMAIN ); ?></th></tr>

			</thead>

			<tbody>

				<tr><td>

                    <table cellspacing="0" cellpadding="0" class="wpmudev-table_inner">

                        <tbody>

                            <tr class="{{ ( _.isFalse(save_local_list) ) ? 'wpmudev-disabled' : '' }}">

                                <td>

                                    <div class="wpmudev-switch">

                                        <input id="wph-embedded-list_hustle" class="toggle-checkbox" type="checkbox" data-attribute="save_local_list" {{_.checked(_.isTrue(save_local_list), true)}} >

                                        <label class="wpmudev-switch-design" for="wph-embedded-list_hustle" aria-hidden="true"></label>

                                    </div>

                                </td>

                                <td><?php $this->render( "general/icons/icon-hustle", array() ); ?></td>

                                <td>
                                    <span class="wpmudev-table_name"><?php _e( "Local Hustle List", Opt_In::TEXT_DOMAIN ); ?></span>
                                    <span class="wpmudev-table_desc"><?php _e( "Will save email addresses to an exportable CSV list", Opt_In::TEXT_DOMAIN ); ?></span>
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </td></tr><?php // Local Hustle List ?>



				<?php
				$active_service = "mailchimp";
				if ( $is_edit && $module && $email_services && is_array( $email_services ) ) :
				$total_email_services = count( $email_services );
				$total_email_services_count = 0;
				foreach ( $email_services as $service_key => $email_service ) :

					if ( $total_email_services > 1 ) {
						if ( $active_email_service ) {
							if ( $active_email_service != $service_key ) {
								continue;
							}
						} else if ( $total_email_services_count > 0 ) {
							continue;
						}

					}
					$total_email_services_count++;

                    $api_key = ( isset( $email_service['api_key'] ) ) ? $email_service['api_key'] : '';
                    $service_name = ( isset( $providers[$service_key] ) && isset( $providers[$service_key]['name'] ) )
                        ? $providers[$service_key]['name']
						: '' ;
					$active_service = $service_key;
					?>

                    <tr class="wph-wizard-content-email-providers"><td>

                        <table cellspacing="0" cellpadding="0" class="wpmudev-table_inner">

                            <tbody>

                                <tr class="wpmudev-disabled">

                                    <td>

                                        <div class="wpmudev-switch">

                                            <input id="wph-embedded-list_<?php echo $service_key; ?>" class="toggle-checkbox wph-email-service-toggle" type="checkbox" data-attribute="<?php echo $service_key; ?>_service_provider" <?php echo ( $active_email_service == $service_key ) ? 'checked="checked"' : '';?> >

                                            <label class="wpmudev-switch-design" for="wph-embedded-list_<?php echo $service_key; ?>" aria-hidden="true"></label>

                                        </div>

                                    </td>

                                    <td class="wph-email-providers-icon">
                                    <?php if ( $service_key == 'mad_mimi' ) : ?>

                                        <div class="wpmudev-icon wpmudev-i_madmimi"></div>

                                    <?php else : ?>

                                        <?php $this->render( "general/icons/icon-" .$service_key , array() ); ?>

                                    <?php endif; ?>

                                    </td>

									<td>
										<a data-id="<?php echo $service_key; ?>" href="#" class="wpmudev-table_name wph-email-service-edit-link" data-nonce="<?php echo wp_create_nonce('change_provider_name') ?>" >
											<span class="wpmudev-table_name"><?php echo $service_name; ?></span>
											<span class="wpmudev-table_desc"><?php echo $api_key; ?></span>
											<span class="wpmudev-table_desc"><?php _e( "Click here to edit or change your email provider", Opt_In::TEXT_DOMAIN ); ?></span>
										</a>

									</td>

                                </tr>

                            </tbody>

                        </table>

                    </td></tr>

                <?php endforeach; ?>
			<?php else:?>
			<tr class="wph-wizard-content-email-providers"><td>

				<table cellspacing="0" cellpadding="0" class="wpmudev-table_inner">

					<tbody>

						<tr class="{{ ( _.isFalse(email_services.mailchimp.enabled) ) ? 'wpmudev-disabled' : '' }}" >

							<td>

								<div class="wpmudev-switch">

									<input id="wph-embedded-list_mailchimp" class="toggle-checkbox wph-email-service-toggle" type="checkbox" data-attribute="mailchimp_service_provider" {{_.checked(_.isTrue(email_services.mailchimp.enabled), true)}} >

									<label class="wpmudev-switch-design" for="wph-embedded-list_mailchimp" aria-hidden="true"></label>

								</div>

							</td>

							<td class="wph-email-providers-icon"><?php $this->render( "general/icons/icon-mailchimp", array() ); ?></td>

							<td><a data-id="mailchimp" href="#" class="wph-email-service-edit-link" data-nonce="<?php echo wp_create_nonce('change_provider_name') ?>">
								<span class="wpmudev-table_name"><?php _e( "MailChimp", Opt_In::TEXT_DOMAIN ); ?></span>
								<span class="wpmudev-table_desc"><# if ( _.isEmpty ( email_services.mailchimp.api_key ) ) { #><?php _e( "Connect to start growing your lists.", Opt_In::TEXT_DOMAIN ); ?><# } else { #>{{email_services.mailchimp.api_key}}<# } #></span>
								<span class="wpmudev-table_desc"><?php _e( "Click here to edit or change your email provider", Opt_In::TEXT_DOMAIN ); ?></span>
							</a></td>

						</tr>

					</tbody>

				</table>

			</td></tr><?php // MailChimp ?>
            <?php endif; ?>

			</tbody>

			<tfoot>

				<tr>
					<td>
						<a href="#" class="wph-email-service-edit-link wpmudev-button wpmudev-button-blue" data-id="<?php echo $active_service; ?>" data-nonce="<?php echo wp_create_nonce('change_provider_name') ?>" ><?php _e( "Add Another Service", Opt_In::TEXT_DOMAIN ); ?></a>
						<label class="wpmudev-label--notice"><span><?php _e( 'At this time you can only integrate one email service. To change this, edit your existing integration.', Opt_In::TEXT_DOMAIN ); ?></span></label>
					</td>
				</tr>

			</tfoot>

		</table>

	</div>

</div><?php // #wph-wizard-content-email ?>

<?php $this->render( "admin/embedded/wizard/boxes/box-form_elements", array(
    'module'  => $module,
    'default_form_fields' => $default_form_fields
) ); ?>

<?php $this->render( "admin/embedded/wizard/boxes/box-form_submission", array() ); ?>


<?php // Opt-in provider icons template ?>
<?php foreach( $providers as $provider ) : ?>
    <script id="wpmudev-<?php echo $provider['id']; ?>-optin-provider-icon-svg" type="text/template">
        <?php if ( $provider['id'] == 'mad_mimi' ) : ?>

            <div class="wpmudev-icon wpmudev-i_madmimi"></div>

        <?php else : ?>

            <?php $this->render( "general/icons/icon-" . $provider['id'], array() ); ?>

        <?php endif; ?>
    </script>
<?php endforeach; ?>