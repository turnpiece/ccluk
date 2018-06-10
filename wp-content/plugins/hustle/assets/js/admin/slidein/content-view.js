Hustle.define("Slidein.Content_View", function($, doc, win){
	"use strict";
	return Hustle.View.extend(_.extend({}, Hustle.get("Mixins.Model_Updater"), {
		template: Optin.template("wpmudev-hustle-slidein-section-content-tpl"),
		module_id: 0,
		target_container: $('#wpmudev-hustle-box-section-content'),
		editing_service: '',
		is_service_modal_updated: false,
		service_supports_fields: true,
		current_form_elements: [],
		content_form_container : $('.wph-form-element-list'),
		events: {
			'click .wph-email-service-edit-link': 'edit_email_service',
			'click #wph-add-another-service': 'add_email_service',
			'click #wph-edit-form': 'manage_form_fields',
		},
		init: function( opts ){
			this.module_id  = opts.module_id;
			this.module_type = 'slidein';
			
			_.each( Optin.Mixins.get_services_mixins(), function(mix, id){
				if( mix && typeof mix === "function") {
					this[id] = mix( this );
				}
			}, this );
			
			this.service_modal = new Module.Service_Modal({
				view: this
			});
			
			this.form_fields_modal = new Module.Form_Fields({
				view: this
			});
		},
		render: function(args){
			if ( this.target_container.length ) {

				var me = this,
					data = this.model.toJSON();
 
				if ( typeof data.email_services.mailchimp === 'undefined' ) {
					// no mailchimp yet
					data.email_services = {
						mailchimp: this.mailchimp.default_data
					};
				}
 
				if ( data.form_elements === '' ) {
					if( typeof wph_default_form_elements !== 'undefined' ){
						this.model.set( 'form_elements', wph_default_form_elements );
						this.current_form_elements = wph_default_form_elements;
					}
				}

				this.setElement( this.template( _.extend( {
					module_type: 'slidein'
				}, data ) ) );
				
				return this;
			}
			return;
		},
		after_render: function() {
			if ( this.target_container.length ) {
			  this.render_featured_image();
			}
		},
		render_featured_image: function() {
			var Media_Holder = Hustle.get('Featured_Image_Holder'),
				$target = this.$( '#wph-slidein-choose_image' );
				
			if ( !$target.length ) return;
			
			this.media_holder = new Media_Holder({
				model: this.model,
				attribute: 'feature_image',
				module_type: 'slidein',
				target_div: $target
			});
			$target.html('');
			
			$target.html( this.media_holder.$el );
		},

		show_email_service_modal: function(is_edit) {
			var $modal = $('#wph-add-new-service-modal'),
				$content = $modal.find('.wpmudev-box-modal'),
				services = this.model.get('email_services'),
				$current_saved_list = $('#optin-provider-account-selected-list'),
				me = this;
			
			this.add_service_modal.addClass('wpmudev-modal-active');
			$('body').addClass('wpmudev-modal-is_active');

			setTimeout(function(){
				$content.addClass('wpmudev-show');
				Hustle.Events.trigger("modules.view.rendered", me);
				$(document).off( 'click', '.optin_refresh_provider_details', $.proxy( me.refresh_provider_details, me ) );
				$(document).on( 'click', '.optin_refresh_provider_details', $.proxy( me.refresh_provider_details, me ) );
				$(document).off( 'click', '.wph-save-optin-service', $.proxy( me.updated_email_service_args, me ) );
				$(document).on( 'click', '.wph-save-optin-service', $.proxy( me.updated_email_service_args, me ) );
				Hustle.Events.off( 'slidein.optin.service.saved', me.save_email_service );
				Hustle.Events.on( 'slidein.optin.service.saved', me.save_email_service );

				// hide other service if editing
				me.hide_or_show_other_services(is_edit);
				
				// set selected list
				if ( is_edit && !_.isEmpty( services ) ) {
					Hustle.Events.trigger("optin.service.show.selected", me);
				} else {
					$current_saved_list.hide();
				}

			}, 100);
		},
		add_email_service: function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			var $btn = $(e.target);
			this.service_modal.add_service($btn);
		},
		edit_email_service: function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			var $btn = $(e.target);
			this.service_modal.edit_service($btn);
		},
		manage_form_fields: function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			this.form_fields_modal.manage_form();
		}
	} ) );

});
