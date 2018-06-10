(function($,doc){
	'use strict';

	Optin.Mixins.add_services_mixin( 'mautic', function( content_view ) {
		return new Optin.Provider({
			id: 'mautic',
			provider_args: { enabled: 0 },
			default_data: {
				enabled: false,
				url: '',
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
				if ( 'mautic' in email_services ) {
					var list_name = ( 'list_name' in email_services.mautic )
						? email_services.mautic.list_name
						: '';
					
					if ( $label.length ) {
						$label.html( $label.text().replace( 'campaign', '<strong>' + list_name + '</strong>' ) )
					}
					
					if ( typeof email_services.mautic.url !== 'undefined' ) {
						setTimeout(function(){
							$('input[name="optin_url"]').attr( 'value', email_services.mautic.url ) ;
						}, 500);
					}

					if ( typeof email_services.mautic.username !== 'undefined' ) {
						setTimeout(function(){
							$('input[name="optin_username"]').attr( 'value', email_services.mautic.username ) ;
						}, 500);
					}

					if ( typeof email_services.mautic.password !== 'undefined' ) {
						setTimeout(function(){
							$('input[name="optin_password"]').attr( 'value', email_services.mautic.password ) ;
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
					
				var api_url = $('input[name="optin_url"]').val(),
					username = $('input[name="optin_username"]').val(),
					enabled = view.model.get('email_services').mautic ? view.model.get('email_services').mautic.enabled : false,
					password = $('input[name="optin_password"]').val(),
					$list = $('select[name="optin_email_list"]'),
					list_id = $list.val(),
					list_name = $list.find('option:selected').text();
					
				var current_args = {
						url: api_url,
						username: username,
						enabled: enabled,
						password: password,
						list_id: list_id,
						list_name: list_name,
						desc: api_url
					},
					args = _.extend( view.mautic.provider_args, current_args );
				
				view.mautic.provider_args = args;
				Hustle.Events.trigger("optin.service.saved", view);
			},
			init: function() {
				var me = this,
					view = content_view;
				
				var validate_url = function(e){
					var url = $(this).val();
					if (url.indexOf("http://") < 0 || url.indexOf("https://") < 0 ) {
						alert(optin_vars.messages.mautic.invalid_url);
						$(this).focus();
					}
				}
				
				$(doc).on("change paste", "input[name='optin_url']", validate_url );
				Hustle.Events.on( 'optin.service.prepare', $.proxy( this.update_args, this ) );
				Hustle.Events.on( 'optin.service.show.selected', $.proxy( this.show_selected, this ) );
			}
		});
	});
}(jQuery,document));
