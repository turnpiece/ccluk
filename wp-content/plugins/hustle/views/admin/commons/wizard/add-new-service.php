<div id="wph-add-new-service-modal" class="wpmudev-modal">

    <div class="wpmudev-modal-mask" aria-hidden="true"></div>

    <div class="wpmudev-box-modal">

    </div>

</div>

<script id="wpmudev-hustle-modal-add-new-service-tpl" type="text/template">

    <div class="wpmudev-box-head">

        <h2 class="{{ ( _.isFalse(is_new) ) ? 'wpmudev-hidden' : '' }}" ><?php _e( "Add Email Service", Opt_In::TEXT_DOMAIN ); ?></h2>
        <h2 class="{{ ( _.isTrue(is_new) ) ? 'wpmudev-hidden' : '' }}" ><?php _e( "Update Email Service", Opt_In::TEXT_DOMAIN ); ?></h2>

        <?php $this->render("general/icons/icon-close" ); ?>

    </div>

    <div class="wpmudev-box-body">

        <form id="wph-optin-service-details-form">
            <?php wp_nonce_field( 'refresh_provider_details' ); ?>
            <div id="wph-provider-select" class="wpmudev-provider-block">
                <label><?php _e('Choose email service provider:', Opt_In::TEXT_DOMAIN); ?></label>

                <select name="optin_provider_name" class="wpmudev-select" data-nonce="<?php echo wp_create_nonce('change_provider_name') ?>" value="{{service}}">

                <?php foreach( $providers as $provider ) : ?>

                    <option value="<?php echo $provider['id'];?>" {{_.isTrue('<?php echo $provider['id'];?>' === service) ? 'selected' : ''}}><?php echo $provider['name']; ?></option>

                <?php endforeach; ?>

                </select>

            </div><?php // #wph-provider-select ?>

            <div id="wph-provider-account-details" class="wpmudev-provider-block"></div>

            <div id="optin-provider-account-options" class="wpmudev-provider-block">

                <div id="optin-provider-account-selected-list" class="wpmudev-provider-block" data-nonce="<?php echo wp_create_nonce('optin_provider_current_settings') ?>" >

                    <label class="wpmudev-label--notice">

                        <span><?php _e('Selected list (campaign), Press the Fetch Lists button to update value.', Opt_In::TEXT_DOMAIN ); ?></span>

                    </label>

                </div>

            </div>

            <div id="wpoi-loading-indicator" style="display: none;">

                <label class="wpmudev-label--loading">

                    <span><?php _e('Wait a bit, content is being loaded...', Opt_In::TEXT_DOMAIN); ?></span>

                </label>

            </div>
        <form>

    </div>

    <div class="wpmudev-box-footer">

        <a href="" id="wph-cancel-add-service" class="wpmudev-button wpmudev-button-ghost"><?php _e( "Cancel", Opt_In::TEXT_DOMAIN ); ?></a>

        <a href="" class="wph-save-optin-service wpmudev-button wpmudev-button-blue {{ ( _.isFalse(is_new) ) ? 'wpmudev-hidden' : '' }}"><?php _e( "Add Service", Opt_In::TEXT_DOMAIN ); ?></a>
        <a href="" class="wph-save-optin-service wpmudev-button wpmudev-button-blue {{ ( _.isTrue(is_new) ) ? 'wpmudev-hidden' : '' }}"><?php _e( "Update Service", Opt_In::TEXT_DOMAIN ); ?></a>

    </div>

</script>

<script id="wpmudev-hustle-modal-view-form-fields-tpl" type="text/template">

	<# if( typeof form_fields === 'object'  && Object.keys(form_fields).length ) {
		_.each( form_fields , function( form_field ) {
			var required = '',
				asterisk = '';
			if ( form_field.required == 'true' || form_field.required == true ){
				required = 'class="wpmudev-field-required"';
				asterisk = '<span class="wpdui-fi wpdui-fi-asterisk"></span>';
			}
			#>
			<tr>
				<td {{{required}}} data-text="<?php _e( "Form Element", Opt_In::TEXT_DOMAIN ); ?>">{{{asterisk}}} {{form_field.label}}</td>
				<td data-text="<?php _e( "Form Type", Opt_In::TEXT_DOMAIN ); ?>">{{form_field.type}}</td>
				<td data-text="<?php _e( "Default Text", Opt_In::TEXT_DOMAIN ); ?>">{{form_field.placeholder}}</td>
			</tr>
			<#
		});
	}#>
</script>

<?php
if ( isset($providers) ) {

    // adding provider args for each service
    foreach( $providers as $provider ) {
        $this->render("admin/provider/" . $provider['id'] . '/args');
    }

}

?>