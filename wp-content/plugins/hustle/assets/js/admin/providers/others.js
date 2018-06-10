/**
 * Integration of none-too-complicated providers.
 **/

(function($){
	'use strict';

	var providers = ['getresponse', 'campaignmonitor', 'aweber', 'mailerlite'];

	_.each( providers, function( provider ) {
		Optin.Mixins.add_services_mixin( provider, function( content_view ) {
			return new Optin.Provider({
				id: provider,
				provider_args: { enabled: 0 },
				default_data: {
					enabled: false,
					api_key: '',
				},
				show_selected: function() {
					
					// if not the service being edited do not proceed
					if ( content_view.editing_service !== this.id ) {
						return;
					}

					var email_services = content_view.model.get('email_services'),
					data = {},
					$selected_list = $('#optin-provider-account-selected-list'),
					$label = $selected_list.find('.wpmudev-label--notice span');
					var list_name = ( 'list_name' in email_services[provider] )
						? email_services[provider].list_name
						: '';
						
					var api_key = ( 'api_key' in email_services[provider] )
						? email_services[provider].api_key
						: '';
					
					if ( $label.length ) {
						$label.html( $label.text().replace( 'campaign', '<strong>' + list_name + '</strong>' ) )
					}
					
					if ( api_key !== '' ) {
						setTimeout(function(){
							$('input[name="optin_api_key"]').attr( 'value', api_key ) ;
						}, 500);
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
					$list = $('select[name="optin_email_list"]'),
					enabled = view.model.get('email_services')[provider] ? view.model.get('email_services')[provider].enabled : false,
					list_id = $list.val(),
					list_name = $list.find('option:selected').text();
					
					var current_args = {
						api_key: api_key,
						enabled: enabled,
						list_id: list_id,
						list_name: list_name,
						desc: api_key
					},
					args = _.extend( view[provider].provider_args, current_args );
					
					view[provider].provider_args = args;
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
	});
}(jQuery,document));
