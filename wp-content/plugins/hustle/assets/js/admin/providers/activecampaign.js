(function($){
	'use strict';

	Optin.Mixins.add_services_mixin( 'activecampaign', function( content_view ) {
		return new Optin.Provider({
			id: 'activecampaign',
			provider_args: { enabled: 0 },
			default_data: {
				enabled: false,
				api_key: '',
				url: '',
			},
			errors: {
				email_list: {
					name: 'email_provider_lists',
					iconClass: 'dashicons-warning-account_name'
				}
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
				if ( 'activecampaign' in email_services ) {
					var list_name = ( 'list_name' in email_services.activecampaign )
						? email_services.activecampaign.list_name
						: '';
					
					if ( $label.length ) {
						$label.html( $label.text().replace( 'campaign', '<strong>' + list_name + '</strong>' ) )
					}
					
					if ( typeof email_services.activecampaign.api_key !== 'undefined' ) {
						setTimeout(function(){
							$('input[name="optin_api_key"]').attr( 'value', email_services.activecampaign.api_key ) ;
						}, 500);
					}

					if ( typeof email_services.activecampaign.url !== 'undefined' ) {
						setTimeout(function(){
							$('input[name="optin_url"]').attr( 'value', email_services.activecampaign.url ) ;
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
					enabled = view.model.get('email_services').activecampaign ? view.model.get('email_services').activecampaign.enabled : false,
					url = $('input[name="optin_url"]').val(),
					$list = $('select[name="optin_email_list"]'),
					list_id = $list.val(),
					list_name = $list.find('option:selected').text();
					
				var current_args = {
						api_key: api_key,
						enabled: enabled,
						url: url,
						list_id: list_id,
						list_name: list_name,
						desc: api_key
					},
					args = _.extend( view.activecampaign.provider_args, current_args );
				
				view.activecampaign.provider_args = args;
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
