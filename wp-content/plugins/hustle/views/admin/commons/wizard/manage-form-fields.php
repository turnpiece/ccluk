<div id="wph-edit-form-modal" class="wpmudev-modal">

    <div class="wpmudev-modal-mask" aria-hidden="true"></div>

    <div class="wpmudev-box-modal">

    </div>

</div>

<script id="wpmudev-hustle-modal-manage-form-fields-tpl" type="text/template">

    <div class="wpmudev-box-head">

        <div class="wpmudev-box-reset">

            <h2><?php _e( "Edit Form Fields", Opt_In::TEXT_DOMAIN ); ?></h2>

            <a id="wph-new-form-field" href="" class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost"><?php _e( "Add New Field", Opt_In::TEXT_DOMAIN ); ?></a>

        </div>

        <?php $this->render("general/icons/icon-close" ); ?>

    </div>

    <div class="wpmudev-box-body">

        <form action="" id="wph-optin-form-fields-form">

            <div class="wpmudev-table-fields">

                <div class="wpmudev-table-head">

                    <div class="wpmudev-table-head-item wpmudev-head-item-label"><?php _e( "Label", Opt_In::TEXT_DOMAIN ); ?></div>
                    <div class="wpmudev-table-head-item wpmudev-head-item-name"><?php _e( "Name", Opt_In::TEXT_DOMAIN ); ?></div>
                    <div class="wpmudev-table-head-item wpmudev-head-item-type"><?php _e( "Type", Opt_In::TEXT_DOMAIN ); ?></div>
                    <div class="wpmudev-table-head-item wpmudev-head-item-required"><span class="wpdui-fi wpdui-fi-asterisk"></span></div>
                    <div class="wpmudev-table-head-item wpmudev-head-item-placeholder"><?php _e( "Placeholder", Opt_In::TEXT_DOMAIN ); ?></div>

                </div>

                <div class="wpmudev-table-body">

                    <?php // will be replaced with actual fields content ?>

                </div>

            </div>

        </form>

    </div>

    <div class="wpmudev-box-footer">

        <a href="" id="wph-cancel-edit-form" class="wpmudev-button wpmudev-button-ghost"><?php _e( "Cancel", Opt_In::TEXT_DOMAIN ); ?></a>

        <a href="" id="wph-save-edit-form" class="wpmudev-button wpmudev-button-blue" data-nonce="<?php echo wp_create_nonce( 'optin_add_module_fields' ); ?>" ><?php _e( "Save Form", Opt_In::TEXT_DOMAIN ); ?></a>

    </div>

</script>

<script id="wpmudev-hustle-modal-add-form-fields-tpl" type="text/template">

	<#
	var field_label = ( typeof field.label !== 'undefined' ) ? field.label : '<?php _e( 'Field Label', Opt_In::TEXT_DOMAIN ); ?>',
		field_name = ( typeof field.name !== 'undefined' ) ? field.name : '<?php _e( 'Field Name', Opt_In::TEXT_DOMAIN ); ?>',
		field_type = ( typeof field.type !== 'undefined' ) ? field.type : '<?php _e( 'Field Type', Opt_In::TEXT_DOMAIN ); ?>',
		field_placeholder = ( typeof field.placeholder !== 'undefined' ) ? field.placeholder : '<?php _e( 'Field Placeholder', Opt_In::TEXT_DOMAIN ); ?>',
        field_delete = ( typeof field.delete !== 'undefined' ) ? field.delete : true;
	#>

	<div class="wph-field-row wpmudev-table-body-row {{ ( _.isTrue(new_field) ) ? 'wpmudev-open' : 'wpmudev-close' }}" data-id="{{field_name}}">

        <div class="wpmudev-table-body-preview">

            <div class="wpmudev-table-preview-item wpmudev-preview-item-drag"><?php $this->render( "general/icons/icon-drag" ); ?></div>

            <div class="wpmudev-table-preview-item wpmudev-preview-item-label">{{field_label}}</div>

            <div class="wpmudev-table-preview-item wpmudev-preview-item-name">{{field_name}}</div>

            <div class="wpmudev-table-preview-item wpmudev-preview-item-type">{{field_type}}</div>

            <div class="wpmudev-table-preview-item wpmudev-preview-item-required wph-form-field-required-">

				<# if ( typeof field.required !== 'undefined' && _.isTrue( field.required ) ) { #>
					<span class="wpdui-fi wpdui-fi-check"></span>
				<# } #>

			</div>

            <div class="wpmudev-table-preview-item wpmudev-preview-item-placeholder">{{field_placeholder}}</div>

            <div class="wpmudev-table-preview-item wpmudev-preview-item-manage"><?php $this->render("general/icons/icon-plus" ); ?></div>

        </div>

        <div class="wpmudev-table-body-content">

            <div class="wpmudev-row">

                <div class="wpmudev-col col-12 col-sm-6">

                    <label><?php echo __('Field label', Opt_In::TEXT_DOMAIN); ?></label>

                    <input type="text" name="label" placeholder="<?php echo __('Type label...', Opt_In::TEXT_DOMAIN); ?>" value="{{field_label}}" class="wpmudev-input_text">

                </div>

                <div class="wpmudev-col col-12 col-sm-6">

                    <label><?php echo __('Field name', Opt_In::TEXT_DOMAIN); ?></label>

                    <input type="text" name="name" placeholder="<?php echo __('Type name...', Opt_In::TEXT_DOMAIN); ?>" value="{{field_name}}" class="wpmudev-input_text" {{ _.isFalse( field_delete ) ? 'disabled="disabled"' : '' }}>

                </div>

            </div>

            <div class="wpmudev-row">

                <div class="wpmudev-col col-12 col-sm-6">

                    <label><?php echo __('Field type', Opt_In::TEXT_DOMAIN); ?></label>

                    <select class="wpmudev-select" name="type" {{ _.isFalse( field_delete ) ? 'disabled="disabled"' : '' }}>

                        <option><?php _e( "Choose field type", Opt_In::TEXT_DOMAIN ); ?></opion>
                        <option value="name" {{ ( field_type === 'name' ) ? 'selected="selected"' : '' }}><?php _e( "Name", Opt_In::TEXT_DOMAIN ); ?></option>
                        <option value="address" {{ ( field_type === 'address' ) ? 'selected="selected"' : '' }}><?php _e( "Address", Opt_In::TEXT_DOMAIN ); ?></option>
                        <option value="phone" {{ ( field_type === 'phone' ) ? 'selected="selected"' : '' }}><?php _e( "Phone", Opt_In::TEXT_DOMAIN ); ?></option>
                        <option value="text" {{ ( field_type === 'text' ) ? 'selected="selected"' : '' }}><?php _e( "Text", Opt_In::TEXT_DOMAIN ); ?></option>
                        <option value="number" {{ ( field_type === 'number' ) ? 'selected="selected"' : '' }} ><?php _e( "Number", Opt_In::TEXT_DOMAIN ); ?></option>
                        <option value="email" {{ ( field_type === 'email' ) ? 'selected="selected"' : '' }} ><?php _e( "Email", Opt_In::TEXT_DOMAIN ); ?></option>
                        <option value="url" {{ ( field_type === 'url' ) ? 'selected="selected"' : '' }} ><?php _e( "URL", Opt_In::TEXT_DOMAIN ); ?></option>
                        <# if ( field_type === 'submit' ) { #>
                            <option value="submit" selected="selected" ><?php _e( "Button", Opt_In::TEXT_DOMAIN ); ?></option>
                        <# } #>
                    </select>

                </div>

                <div class="wpmudev-col col-12 col-sm-6">

                    <label><?php echo __('Field placeholder', Opt_In::TEXT_DOMAIN); ?></label>

                    <input type="text" name="placeholder" placeholder="<?php echo __('Type placeholder...', Opt_In::TEXT_DOMAIN); ?>" value="{{field_placeholder}}" class="wpmudev-input_text">

                </div>

            </div>

            <div class="wpmudev-row wph-form-field-delete-edit-">

                <# if ( _.isTrue( field_delete ) ) { #>

                    <div class="wpmudev-col col-12 col-sm-6">

                        <div class="wpmudev-switch-labeled">

                            <div class="wpmudev-switch">

                                <input id="wph-field-{{field_name}}" class="toggle-checkbox wph-field-edit-required-{{field_name}}" name="required" type="checkbox" {{ _.checked( ( typeof field.required !== 'undefined' && _.isTrue( field.required ) ), true ) }}>

                                <label class="wpmudev-switch-design" for="wph-field-{{field_name}}"></label>

                            </div>

                            <label class="wpmudev-switch-label" for="wph-field-{{field_name}}">This field is required</label>

                        </div>

                    </div>

                    <div class="wpmudev-col col-12 col-sm-6">

                        <input type="hidden" name="delete" class="wph-field-edit-delete-{{field_name}}" value="true" />

                        <a href="#" data-id="wph-field-{{field_name}}" class="wpmudev-icon-delete" aria-hidden="true"><?php $this->render("general/icons/icon-delete"); ?><span><?php _e( "Delete field", Opt_In::TEXT_DOMAIN ); ?></span></a>

                        <a href="#" data-id="wph-field-{{field_name}}" class="wpmudev-screen-reader-text"><?php _e( "Delete field", Opt_In::TEXT_DOMAIN ); ?></a>

                    </div>

                <# } else { #>

                    <input type="hidden" name="required" value="true" />

                    <input type="hidden" name="delete" value="false" />

				<# } #>

            </div>

        </div>

    </div>

</script>