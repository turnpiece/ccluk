(function($){
	'use strict';

	Optin.Mixins.add_services_mixin( 'sendy', function( content_view ) {
		return new Optin.Provider({
			id: 'sendy',
			provider_args: { enabled: 0 },
			default_data: {
				enabled: false,
				api_key: '',
				installation_url: '',
				list_id : ''
			},
			show_selected: function() {

				content_view.service_supports_fields = false;
				
				// if not the service being edited do not proceed
				if ( content_view.editing_service !== this.id ) {
					return;
				}

				var email_services = content_view.model.get('email_services'),
					$selected_list = $('#optin-provider-account-selected-list'),
					$label = $selected_list.find('.wpmudev-label--notice span');
				if ( 'sendy' in email_services ) {
					var list_name = ( 'list_id' in email_services.sendy )
						? email_services.sendy.list_id
						: '';
					
					if ( $label.length ) {
						$label.html( window.optin_vars.messages.providers.no_fetch_list.replace( 'campaign', '<strong>' + list_name + '</strong>' ) )
					}
					
					if ( typeof email_services.sendy.api_key !== 'undefined' ) {
						setTimeout(function(){
							$('input[name="optin_api_key"]').attr( 'value', email_services.sendy.api_key ) ;
						}, 500);
					}

					if ( typeof email_services.sendy.installation_url !== 'undefined' ) {
						setTimeout(function(){
							$('input[name="optin_sendy_installation_url"]').attr( 'value', email_services.sendy.installation_url ) ;
						}, 500);
					}
				}
				
			},
			update_args: function(view) {
				
				// if not the service being edited do not proceed
				if ( view.editing_service !== this.id ) {
					return;
				}
				
				// if not updated do not save
				if ( !view.is_service_modal_updated ) {
					view.service_modal.close_modal();
					return;
				}
					
				var api_key = $('input[name="optin_api_key"]').val(),
					enabled = view.model.get('email_services').sendy ? view.model.get('email_services').sendy.enabled : false,
					installation_url = $('input[name="optin_sendy_installation_url"]').val(),
					list_id = $('input[name="optin_email_list"]').val();
					
				var current_args = {
						api_key: api_key,
						enabled: enabled,
						installation_url: installation_url,
						list_id: list_id,
						desc: api_key
					},
					args = _.extend( view.sendy.provider_args, current_args );
				
				view.sendy.provider_args = args;
				Hustle.Events.trigger("optin.service.saved", view);
			},
			init: function() {
				var me = this,
					view = content_view;
				
				Hustle.Events.on( 'optin.service.prepare', $.proxy( this.update_args, this ) );
				Hustle.Events.on( 'optin.service.show.selected', $.proxy( this.show_selected, this ) );
			}
		});
	});
}(jQuery,document));
