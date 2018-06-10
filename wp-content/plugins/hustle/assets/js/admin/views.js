(function( $ ) {
	"use strict";

	var Module = window.Module || {};

	Module.Alert = Backbone.View.extend({
		template: Optin.template("optin-alert-modal"),
		events: {
			"click .inc-opt-alert-modal-close": "close",
			"click .inc-opt-alert-modal": "close",
			"click .inc-opt-alert-modal-close-btn": "close",
			"click .inc-opt-alert-modal-inner-container": "prevent_close"
		},
		initialize: function(options){
			this.options = options;
			return this.render();
		},
		render: function(){
			this.$el.html( this.template(_.extend({
				close_text: optin_vars.messages.ok
			}, this.options) ) );
			this.$el.appendTo("body");
		},
		close: function(e){
			this.$el.hide();
			this.remove();
		},
		prevent_close: function(e){
			e.preventDefault();
			e.stopPropagation();
		}
	});

	Module.Service_Modal = Backbone.View.extend({
		template: Optin.template("wpmudev-hustle-modal-add-new-service-tpl"),
		service_modal_target: '#wph-add-new-service-modal .wpmudev-box-modal',
		add_service_modal: $('#wph-add-new-service-modal'),
		initialize: function(options) {
			this.options = options;
			this.view = options.view;
			return this.render();
		},
		render: function() {
			return this;
		},
		add_service: function($btn) {
			this.view.is_service_modal_updated = false;

			var data = _.extend(
					{
						is_new: true,
						service: 'mailchimp'
					}
				),
				$target_modal = $(this.service_modal_target),
				$this = $btn.closest('a'),
				nonce = $this.data('nonce');

			$target_modal.html('');
			$target_modal.append(this.template(data));
			this.show_modal(false);
		},
		edit_service: function($btn) {
			this.view.is_service_modal_updated = false;

			var $this = $btn.closest('a'),
				service = $this.data('id'),
				nonce = $this.data('nonce'),
				data = _.extend(
					{
						is_new: false,
						service: service
					}
				),
				$target_modal = $(this.service_modal_target);

			$target_modal.html('');
			$target_modal.append(this.template(data));

			this.view.editing_service = service;
			// Get the provider's details.
			this.get_provider_details(service, nonce);
			this.show_modal(true);
		},
		on_provider_changed: function(e) {
			var $this = $(e.target),
				id = $this.val(),
				nonce = $this.data('nonce');

			if ( this.view.editing_service !== id ) {
				this.view.is_service_modal_updated = true;
			}

			this.view.editing_service = id;
			this.get_provider_details(id, nonce);
		},
		get_provider_details: function(id, nonce) {

			var $details_container = $('#wph-provider-account-details'),
				module_id = this.view.module_id,
				module_type = this.view.module_type
			;
			
			$details_container.html('');

			$.ajax({
				url: ajaxurl,
				type: "get",
				async: true,
				data: {
					action: "render_provider_account_options",
					provider_id: id,
					module_id: module_id,
					module_type: module_type,
					_ajax_nonce: nonce
				},
				success: function(response){
					if( response.success === true ) {

						$details_container.html(response.data);
						Hustle.Events.trigger("modules.view.select.render", this.view);
						if ( id !== 'mailchimp' ) {
							if ( $('#wph-mailchimp-group-args').length ) {
								$('#wph-mailchimp-group-args').remove();
							}
						}
					} else {

					}

				}

			});

		},
		show_modal: function( is_edit ) {
			var $modal = $('#wph-add-new-service-modal'),
				$content = $modal.find('.wpmudev-box-modal'),
				view = this.view,
				me = this,
				services = this.view.model.get('email_services'),
				$current_saved_list = $('#optin-provider-account-selected-list');

			this.add_service_modal.addClass('wpmudev-modal-active');
			$('body').addClass('wpmudev-modal-is_active');

			setTimeout(function(){
				$content.addClass('wpmudev-show');
				Hustle.Events.trigger("modules.view.rendered", view);
				$(document).off( 'change', 'select[name="optin_provider_name"]', $.proxy( me.on_provider_changed, me ) );
				$(document).on( 'change', 'select[name="optin_provider_name"]', $.proxy( me.on_provider_changed, me ) );
				$(document).off( 'click', '.optin_refresh_provider_details', $.proxy( me.refresh_provider_details, me ) );
				$(document).on( 'click', '.optin_refresh_provider_details', $.proxy( me.refresh_provider_details, me ) );
				$(document).off( 'click', '.wph-save-optin-service', $.proxy( me.updated_email_service_args, me ) );
				$(document).on( 'click', '.wph-save-optin-service', $.proxy( me.updated_email_service_args, me ) );
				Hustle.Events.off( 'optin.service.saved', me.save_email_service );
				Hustle.Events.on( 'optin.service.saved', me.save_email_service );

				// hide other service if editing
				me.hide_or_show_other_services(is_edit);
				// set selected list
				if ( is_edit && !_.isEmpty( services ) ) {
					Hustle.Events.trigger("optin.service.show.selected", view);
				} else {
					$current_saved_list.hide();
					// Auto load first provider details.
					$content.find('select[name="optin_provider_name"]').trigger('change');
				}

			}, 100);
		},
		hide_or_show_other_services: function( is_edit ) {
			var $select = $('#wph-provider-select .wpmudev-select'),
				$current_saved_list = $('#optin-provider-account-selected-list'),
				services = this.view.model.get('email_services');

			if ( _.isEmpty( services ) ) {
				services = {
					mailchimp: this.view.mailchimp.default_data
				};
			}

			if ( is_edit ) {
				$current_saved_list.show();
			} else {
				$current_saved_list.hide();

				var $siblings = $select.find('option');

				// only show services that are not yet added
				$siblings.each(function(){
					var rel = $(this).attr('value');
					if ( rel in services || rel === 'mailchimp' ) {
						$(this).remove();
					}
				});
			}

		},
		/**
		 * Gets provider account option details, eg api key and etc and update #optin-provider-account-options content
		 */
		refresh_provider_details: function(e){
			e.preventDefault();

			var me = this.view,
				$this = $(e.target),
				$form = $this.closest("form"),
				data = $form.serialize(),
				$api_key = $this.siblings('input#optin_api_key'),
				$placeholder = $("#optin-provider-account-options");

			this.view.is_service_modal_updated = true;

			$placeholder.html( $( "#wpoi-loading-indicator" ).html() );

			data += "&action=refresh_provider_account_details";
			if( typeof this.view.module_id !== 'undefined') data += "&module_id=" + this.view.module_id;

			$this.addClass('wpmudev-button-onload');

			$.post(ajaxurl, data, function( response ){

				if( response.success === true ){

					if( response.data.redirect_to ){
						window.location.href = response.data.redirect_to;
					}else {
						if ( ! response.data ) {
							$placeholder.html( optin_vars.messages.something_went_wrong );
						} else {
							$placeholder.html( response.data );
						}
						Hustle.Events.trigger("modules.view.rendered", me);
					}
				}else{
					if ( ! response.data ) {
						$placeholder.html( optin_vars.messages.something_went_wrong );
					} else {
						$placeholder.html( response.data  );
					}
				}

			}).fail(function( response ) {
				$placeholder.html( optin_vars.messages.something_went_wrong );

			}).always(function(){
				$this.removeClass('wpmudev-button-onload');
			});
		},
		updated_email_service_args: function(e) {
			e.preventDefault();
			Hustle.Events.trigger("optin.service.prepare", this.view);
		},
		save_email_service: function(view) {
			var service = view.editing_service,
				args = view[service].provider_args,
				email_services = {};
				//email_services = view.model.get('email_services');

			// Only use one email service at a time.
			email_services[service] = args;

			// Multiple email services:
			//if ( _.isEmpty( email_services ) ) {
				//email_services = {};
				//email_services[service] = args;
			//} else {
				//email_services[service] = args;
			//}

			view.model.set( 'email_services', email_services );
			view.service_modal.close_modal();
			view.service_modal.append_added_service(service, args);
		},
		close_modal: function() {
			this.add_service_modal.find('.wpmudev-i_close').click();
		},
		append_added_service: function(service, provider_args) {
			var $last_email_provider = $('tr.wph-wizard-content-email-providers').last(),
				$already_exists = $('table#wph-wizard-content-email-options a[data-id="'+ service +'"]'),
				$cloned = $last_email_provider.clone();

			$last_email_provider.html($cloned.html());
			var $updated_service = $('tr.wph-wizard-content-email-providers').last();
			/*if ( $already_exists.length ) {
				var $updated_service = $already_exists.closest('tr.wph-wizard-content-email-providers');
				$updated_service.html($cloned.html());
			} else {
				$cloned.insertAfter($last_email_provider);
				// Only allow one provider.
				$last_email_provider.remove();
				var $updated_service = $('tr.wph-wizard-content-email-providers').last();
			}*/

			$updated_service.addClass('updated-email-provider');
			$updated_service.siblings().removeClass('updated-email-provider');

			// Updating with updated contents

			var $updated_service = $('tr.updated-email-provider'),
				$checkbox = $updated_service.find('input.wph-email-service-toggle'),
				$label = $checkbox.siblings('label'),
				icon_template = Optin.template('wpmudev-'+ service +'-optin-provider-icon-svg'),
				$icon = $updated_service.find('.wph-email-providers-icon'),
				$name = $updated_service.find('a.wph-email-service-edit-link'),
				desc = ( 'desc' in provider_args )
					? provider_args.desc
					: '';

			$checkbox.attr( 'id', 'wph-popup-list_' + service );
			$checkbox.attr( 'data-attribute', service + '_service_provider' );
			// Disable or enable service.
			$checkbox.prop('checked', provider_args.enabled);

			$label.attr( 'for', 'wph-popup-list_' + service );
			$name.attr( 'data-id', service );

			$icon.html( icon_template() );

			if ( service in optin_vars.providers ) {
				$name.find('span.wpmudev-table_name').text( optin_vars.providers[service].name );
				$name.find('span.wpmudev-table_desc:first').text( desc );
			}

		}
	});

	Module.Form_Fields = Backbone.View.extend({
		edit_fields_modal : $('#wph-edit-form-modal'),
		field_list_template: Optin.template("wpmudev-hustle-modal-view-form-fields-tpl"),
		fields_template: Optin.template("wpmudev-hustle-modal-manage-form-fields-tpl"),
		new_fields_template: Optin.template("wpmudev-hustle-modal-add-form-fields-tpl"),
		fields_modal_target: '#wph-edit-form-modal .wpmudev-box-modal',
		initialize: function(options) {
			this.options = options;
			this.view = options.view;
			return this.render();
		},
		render: function() {
			return this;
		},
		manage_form: function() {
			var me = this,
				view = this.view,
				$target_modal = $(me.fields_modal_target),
				form_elements = this.view.model.get('form_elements');

			if ( typeof form_elements !== 'object' ) {
				form_elements = JSON.parse(form_elements);
			}

			$target_modal.html('');

			$target_modal.append( me.fields_template( _.extend( {
				form_fields: form_elements
			} ) ) );

			var $fields_container = $target_modal.find('form#wph-optin-form-fields-form .wpmudev-table-body');

			if ( $fields_container.length ) {
				_.each( form_elements, function( form_field, key ) {
					$fields_container.append( me.new_fields_template( _.extend({
						field: form_field,
						new_field: false
					}) ) );
				} );
			}

			var $content = me.edit_fields_modal.find('.wpmudev-box-modal'),
				$table = me.edit_fields_modal.find('.wpmudev-table-body'),
				$rows = me.edit_fields_modal.find('.wpmudev-table-body-row'),
				$new_button = me.edit_fields_modal.find('#wph-new-form-field'),
				$close = me.edit_fields_modal.find('.wpmudev-i_close'),
				$cancel = me.edit_fields_modal.find('#wph-cancel-edit-form'),
				$save_button = me.edit_fields_modal.find('#wph-save-edit-form'),
				$field_rows =  me.edit_fields_modal.find('.wph-field-row');


			me.edit_fields_modal.addClass('wpmudev-modal-active');
			$('body').addClass('wpmudev-modal-is_active');

			setTimeout(function(){
				$content.addClass('wpmudev-show');
				$new_button.on( 'click' , $.proxy( me.new_form_field, me ) );
				$save_button.on( 'click' , $.proxy( me.save_form_fields, me ) );
				$table.sortable();
				$table.disableSelection();
				$rows.each(function(){
					var $this = $(this),
						$plus = $this.find('.wpmudev-preview-item-manage'),
						$delete = $this.find('.wpmudev-icon-delete');

					$plus.on('click', function(e){
						e.stopPropagation();
						$this.toggleClass('wpmudev-open');
					});
					$delete.on( 'click' , function(e){
						e.preventDefault();
						e.stopPropagation();
						me.delete_form_field($(this));
					});
				});
				$close.on('click', function(e){
					e.stopPropagation();
					$content.removeClass('wpmudev-show').addClass('wpmudev-hide');

					setTimeout(function(){
						me.edit_fields_modal.removeClass('wpmudev-modal-active');
						$('body').removeClass('wpmudev-modal-is_active');
						$content.removeClass('wpmudev-hide');
					}, 500);
				});

				$cancel.on('click', function(e){
					e.preventDefault();
					e.stopPropagation();
					$content.removeClass('wpmudev-show').addClass('wpmudev-hide');

					setTimeout(function(){
						me.edit_fields_modal.removeClass('wpmudev-modal-active');
						$('body').removeClass('wpmudev-modal-is_active');
						$content.removeClass('wpmudev-hide');
					}, 500);
				});
				$field_rows.each(function(){
					me.form_fields_header($(this));
				});
				Hustle.Events.trigger("modules.view.select.render", me);
				Hustle.Events.off( 'optin.service.saved', me.persist_form_fields );
				Hustle.Events.on( 'optin.service.saved', me.persist_form_fields );
			}, 100);
		},
		new_form_field : function(e){
			e.preventDefault();
			e.stopPropagation();
			var me = this,
				view = me.view,
				table = me.edit_fields_modal.find('.wpmudev-table-body'),
				new_button = me.edit_fields_modal.find('#wph-new-form-field');
			if(this.view.service_supports_fields){
				var rows = table.find('.wpmudev-table-body-row');
				rows.each(function(){
					$(this).removeClass('wpmudev-open');
				});
				table.prepend(me.new_fields_template( _.extend( {
					field: { delete: true },
					new_field: true
				} ) ));

				var $plus = table.find('.wpmudev-preview-item-manage:first'),
					$delete = table.find('.wpmudev-icon-delete');

				$plus.on('click', function(e){
					e.stopPropagation();
					$(this).closest('.wpmudev-table-body-row').toggleClass('wpmudev-open');
				});

				$delete.on( 'click' , function(e){
					e.preventDefault();
					e.stopPropagation();
					me.delete_form_field($(this));
				});

				me.form_fields_header(table.find('.wpmudev-table-body-row:first'));
				Hustle.Events.trigger("modules.view.select.render", me);
				me.update_model_fields(me);

			}else{
				new_button.html( optin_vars.messages.form_fields.errors.custom_field_not_supported );
			}
		},
		delete_form_field : function(elem){
			var $id = elem.data('id'),
				$parent_container = elem.closest('.wph-field-row.wpmudev-table-body-row');

			$parent_container.fadeOut( "fast", function() {
				$parent_container.remove();
			});
		},
		form_fields_header : function(elem){
			var $content = elem.find('.wpmudev-table-body-content input, .wpmudev-table-body-content select'),
				$header = elem.find('.wpmudev-table-body-preview'),
				$toprow = $header.closest('.wpmudev-table-body-row');
			$content.each(function(){
				if($(this).is(':checkbox')){
					$(this).on('change',function(e){
						if($(this).is(':checked')){
							$header.find('.wpmudev-preview-item-required').html('<span class="wpdui-fi wpdui-fi-check"></span>');
						}else{
							$header.find('.wpmudev-preview-item-required').html('');
						}
					});
				}else{
					$(this).on('change keyup keypress',function(e){
						var name = $(this).attr('name');
						$header.find('.wpmudev-preview-item-'+name).html($(this).val());
						//update the data-id
						if(name === 'name'){
							$toprow.attr('data-id',$(this).val());
						}
					});
				}
			});
		},
		save_form_fields : function(e){
			e.preventDefault();
			e.stopPropagation();
			var me = this,
				view = me.view;
			me.update_model_fields(me, function(data){
				me.update_optin_fields(view, function(response){
					var content = me.field_list_template( { form_fields: data } );
					$('.wph-form-element-list').empty();
					$('.wph-form-element-list').html(content);
					me.edit_fields_modal.find('.wpmudev-i_close').click();
				});
			});

		},
		//update model
		update_model_fields : function(me, callback){
			var view = me.view,
				$row =  me.edit_fields_modal.find('.wph-field-row'),
				data = {},
				elements = {};
			$row.each(function(){
				var id = $(this).attr('data-id');
				var $content = $(this).find('.wpmudev-table-body-content input, .wpmudev-table-body-content select');

				elements[id] = {};
				$content.each(function(){
					var name = $(this).attr('name');
					var value = $(this).val();
					if(name === 'required'){
						if($(this).is(':checkbox')){
							value = $(this).is(':checked');
						} else{
							value = ( value === 'true' );
						}
					}
					if ( name === 'delete') {
						value = ( value === 'true' );
					}
					elements[id][name] = value;
				});
				data[id] = elements[id];

			});
			view.current_form_elements = data;
			view.model.set( 'form_elements', data, {silent:true} );

			if(typeof callback !== 'undefined' && typeof callback === 'function')
				callback(data);
		},

		update_optin_fields : function(view, callback ){
			var me = this;
			if ( typeof view.current_form_elements === 'object' ) {
				var post_data = JSON.stringify(view.current_form_elements),
					active_email_service = view.model.get('active_email_service'),
					current_button = me.edit_fields_modal.find('#wph-save-edit-form'),
					nonce = current_button.attr('data-nonce'),
					module_id = view.module_id,
					data = {
						'action' : 'add_module_fields',
						'_ajax_nonce'  : nonce,
						'data'   : post_data,
						'provider' : active_email_service,
						'module_id' : module_id
					};

				$.post( ajaxurl, data, function( response ){
					if (typeof callback === 'function') {
						callback(response);
					}
				}).fail(function( response ) {
					if (typeof callback === 'function') {
						callback(response);
					}
				});
			}
		},
		persist_form_fields : function(view) {
			var me = this;
			//We use default elements incase none is selected
			if ( typeof view.current_form_elements === 'object' ) {
				if( Object.keys(view.current_form_elements).length <= 0 ){
					if( typeof wph_default_form_elements != 'undefined' ){
						view.current_form_elements = wph_default_form_elements;
					}
				}
				if( Object.keys(view.current_form_elements).length > 0 ){
					view.model.set( 'form_elements', view.current_form_elements );
					me.update_optin_fields(view, null);
				}
			}
			me.edit_fields_modal.find('.wpmudev-i_close').click();
		}
	});

	/**
	 * Key var to listen user changes before triggering
	 * navigate away message.
	 **/
	Module.hasChanges = false;
	Module.user_change = function() {
		Module.hasChanges = true;
	};

	window.onbeforeunload = function() {
		if ( Module.hasChanges ) {
			return optin_vars.messages.dont_navigate_away;
		}
	};

	$('.highlight_input_text').focus( function(){
		$(this).select();
	});
})( jQuery );
