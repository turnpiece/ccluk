(function($,doc){
	'use strict';

	Optin.Mixins.add_services_mixin( 'sendinblue', function( content_view ) {
		return new Optin.Provider({
			id: 'sendinblue',
			provider_args: { enabled: 0 },
			default_data: {
				enabled: false,
				api_key: '',
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
					$selected_list = $('#optin-provider-account-selected-list'),
					$label = $selected_list.find('.wpmudev-label--notice span');
				if ( 'sendinblue' in email_services ) {
					var list_name = ( 'list_name' in email_services.sendinblue )
						? email_services.sendinblue.list_name
						: '';
					
					if ( $label.length ) {
						$label.html( $label.text().replace( 'campaign', '<strong>' + list_name + '</strong>' ) )
					}
					
					if ( typeof email_services.sendinblue.api_key !== 'undefined' ) {
						setTimeout(function(){
							$('input[name="optin_api_key"]').attr( 'value', email_services.sendinblue.api_key ) ;
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
					enabled = view.model.get('email_services').sendinblue ? view.model.get('email_services').sendinblue.enabled : false,
					$list = $('select[name="optin_email_list"]'),
					list_id = $list.val(),
					list_name = $list.find('option:selected').text();
					
				var current_args = {
						api_key: api_key,
						enabled: enabled,
						list_id: list_id,
						list_name: list_name,
						desc: api_key
					},
					args = _.extend( view.sendinblue.provider_args, current_args );
				
				view.sendinblue.provider_args = args;
				Hustle.Events.trigger("optin.service.saved", view);
			},
			init: function() {
				var me = this,
					view = content_view;
					
				/**
				 * Load more lists
				 * @param {*} e 
				 */
				var load_more_lists = function(e){
					var $this = $(e.target),
						$form = $this.closest("form"),
						data = $form.serialize(),
						$placeholder = $("#optin-provider-account-options");

					$placeholder.html( $( "#wpoi_loading_indicator" ).html() );

					data += "&action=refresh_provider_account_details&load_more=true";
					data += "&optin=sendinblue";

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
							}
							Hustle.Events.trigger("modules.view.rendered", content_view);
						}else{
							if ( ! response.data ) {
								$placeholder.html( optin_vars.messages.something_went_wrong );
							} else {
								$placeholder.html( response.data  );
							}
						}

					}).fail(function( response ) {
						$placeholder.html( optin_vars.messages.something_went_wrong );
					});
				};
				
				Hustle.Events.on( 'optin.service.prepare', $.proxy( this.update_args, this ) );
				Hustle.Events.on( 'optin.service.show.selected', $.proxy( this.show_selected, this ) );
				
				$(doc).on("click", ".sendinblue_optin_load_more_lists", load_more_lists);
			}
		});
	});
}(jQuery,document));
