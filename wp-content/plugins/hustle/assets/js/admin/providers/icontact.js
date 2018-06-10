(function($,doc){
	'use strict';

	Optin.Mixins.add_services_mixin( 'icontact', function( content_view ) {
		return new Optin.Provider({
			id: 'icontact',
			provider_args: { enabled: 0 },
			default_data: {
				enabled: false,
				username: '',
				password : ''
			},
			show_selected: function() {
				
				// if not the service being edited do not proceed
				if ( content_view.editing_service !== this.id ) {
					return;
				}

				var email_services = content_view.model.get('email_services'),
					$selected_list = $('#optin-provider-account-selected-list'),
					$label = $selected_list.find('.wpmudev-label--notice span');
				if ( 'icontact' in email_services ) {
					var list_name = ( 'list_name' in email_services.v )
						? email_services.icontact.list_name
						: '';
					
					if ( $label.length ) {
						$label.html( $label.text().replace( 'campaign', '<strong>' + list_name + '</strong>' ) )
					}
					
					if ( typeof email_services.icontact.app_id !== 'undefined' ) {
						setTimeout(function(){
							$('input[name="optin_app_id"]').attr( 'value', email_services.icontact.app_id ) ;
						}, 500);
					}

					if ( typeof email_services.icontact.username !== 'undefined' ) {
						setTimeout(function(){
							$('input[name="optin_username"]').attr( 'value', email_services.icontact.username ) ;
						}, 500);
					}

					if ( typeof email_services.icontact.password !== 'undefined' ) {
						setTimeout(function(){
							$('input[name="optin_password"]').attr( 'value', email_services.icontact.password ) ;
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
					
				var app_id = $('input[name="optin_app_id"]').val(),
					username = $('input[name="optin_username"]').val(),
					enabled = view.model.get('email_services').icontact ? view.model.get('email_services').icontact.enabled : false,
					password = $('input[name="optin_password"]').val(),
					$list = $('select[name="optin_email_list"]'),
					list_id = $list.val(),
					list_name = $list.find('option:selected').text();
					
				var current_args = {
						app_id: app_id,
						username: username,
						enabled: enabled,
						password: password,
						list_id: list_id,
						list_name: list_name,
						desc: app_id
					},
					args = _.extend( view.icontact.provider_args, current_args );
				
				view.icontact.provider_args = args;
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
