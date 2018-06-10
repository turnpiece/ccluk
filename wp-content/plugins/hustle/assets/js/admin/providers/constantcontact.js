/**
 * Constant Contact email integration
 */
(function($,doc,win){
	'use strict';

	Optin.Mixins.add_services_mixin( 'constantcontact', function( content_view ) {
		return new Optin.Provider({
			id: 'constantcontact',
			provider_args: { enabled: 0 },
			show_selected: function() {

				content_view.service_supports_fields = false;
				
				// if not the service being edited do not proceed
				if ( content_view.editing_service !== this.id ) {
					return;
				}

				var email_services = content_view.model.get('email_services'),
					$selected_list = $('#optin-provider-account-selected-list'),
					$label = $selected_list.find('.wpmudev-label--notice span');
				if ( 'constantcontact' in email_services ) {
					var list_name = ( 'list_name' in email_services.constantcontact )
						? email_services.constantcontact.list_name
						: '';
					
					if ( $label.length ) {
						$label.html( window.optin_vars.messages.providers.no_fetch_list.replace( 'campaign', '<strong>' + list_name + '</strong>' ) )
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
				
				var $list = $('.constantContact_optin_email_list'),
					enabled = view.model.get('email_services').constantcontact ? view.model.get('email_services').constantcontact.enabled : false,
					list_id = $list.val(),
					list_name = $list.find('option:selected').text();

				var current_args = {
						list_id: list_id,
						enabled: enabled,
						list_name: list_name,
						desc: list_name
					},
					args = _.extend( view.constantcontact.provider_args, current_args );
				
				view.constantcontact.provider_args = args;
				Hustle.Events.trigger("optin.service.saved", view);
			},
			mark_updated: function() {
				// Mark as changed so it saves.
				content_view.is_service_modal_updated = true;
			},
			init: function() {
				var me = this,
					view = content_view;

				var resetReferrer = function() {
					var target = $(this),
						optin_id = target.data('optin'),
						location = target.attr('href'),
						timer, data;

					if ( ! optin_id ) {
						var button = $('.next-button button.wph-button-save', '#wpoi-wizard-services');
						button.trigger( 'click' );

						timer = setInterval(function() {
							optin_id = Optin.step.services.model.get('optin_id');

							if ( parseInt( optin_id ) > 0 ) {
								clearInterval(timer);
								data = {optin_id: optin_id, _wpnonce: window.optin_vars.constantcontact_nonce, action: 'update_constantcontact_referrer' };

								// Update referrer in the background
								$.get(ajaxurl, data);

								_.delay(function() {
									win.location = location;
								}, 300 );
							}
						}, 100 );
					}

					return;
				};

				$(doc).on( 'click', '.constantcontact-authorize', resetReferrer );
				$(doc).on( 'change', '.constantContact_optin_email_list', this.mark_updated );
				
				Hustle.Events.on( 'optin.service.prepare', $.proxy( this.update_args, this ) );
				Hustle.Events.on( 'optin.service.show.selected', $.proxy( this.show_selected, this ) );
			}
		});
	});

}(jQuery,document,window));
