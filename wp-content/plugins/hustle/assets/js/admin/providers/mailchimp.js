(function($,doc){
	'use strict';    
	
	Optin.Mixins.add_services_mixin( 'mailchimp', function( content_view ) {
		return new Optin.Provider({
			id: 'mailchimp',
			provider_args: { enabled: 0 },
			default_data: {
				enabled: false,
				api_key: '',
				auto_optin: false
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
					args_template = Optin.template("wpmudev-mailchimp-group-args-tpl"),
					data = {},
					$selected_list = $('#optin-provider-account-selected-list'),
					$label = $selected_list.find('.wpmudev-label--notice span');
				
				if ( 'mailchimp' in email_services ) {
					var list_name = ( 'list_name' in email_services.mailchimp )
						? email_services.mailchimp.list_name
						: '';
					
					//update the value
					if ( typeof email_services.mailchimp.api_key !== 'undefined' ) {
						setTimeout(function(){
							$('input[name="optin_api_key"]').attr( 'value', email_services.mailchimp.api_key ) ;
						}, 500);
					}

					if ( $label.length ) {
						$label.html( $label.text().replace( 'campaign', '<strong>' + list_name + '</strong>' ) )
					}
					
					if ( typeof email_services.mailchimp.list_id === 'undefined' || typeof $selected_list.data('nonce') === 'undefined' ) {
						return;
					}
					
					data.list_id = email_services.mailchimp.list_id;
					data.group = email_services.mailchimp.group;
					data._ajax_nonce = $selected_list.data("nonce");
					
					data.action = 'hustle_mailchimp_get_current_settings';

					$.get( ajaxurl, data )
						.done(function(res){
							if( res && res.success ){
								var group = res.data.group,
									options = new Array(),
									selected = email_services.mailchimp.group_interest,
									selected_names = new Array();
									
								if(group !== null){
									_.each( group.interests, function(interest){
										options.push(interest.name);
										if ( typeof selected !== 'undefined' && selected.indexOf(interest.id) !== -1 ) {
											selected_names.push(interest.name);
										}
									} );
									
									$selected_list.append(args_template( {
										name: group.title,
										type: group.type,
										options: options.join(', '),
										selected: selected_names.join(', ')
									} ));
								}
								
							}
						});
					
					
				}
				
			},
			update_args: function(view) {
				
				// if not the service being edited do not proceed
				if ( content_view.editing_service !== this.id ) {
					return;
				}
				
				// if not updated do not save
				if ( !view.is_service_modal_updated ) {
					view.service_modal.close_modal();
					return;
				}
				
				var api_key = $('input[name="optin_api_key"]').val(),
					auto_optin = $('input[name="optin_auto_optin"]').is(':checked'),
					enabled = view.model.get('email_services').mailchimp ? view.model.get('email_services').mailchimp.enabled : false,
					$list = $('select[name="optin_email_list"]'),
					list_id = $list.val(),
					list_name = $list.find('option:selected').text(),
					$group = $('select[name="mailchimp_groups"]'),
					group_id = $group.val(),
					group_name = $group.find('option:selected').text(),
					group_interest = '';
					
				
				if ( group_id !== '-1' ) {
					if ( group_name.toLowerCase().indexOf('radio') !== -1 ) {
						// radio group interests
						var selected_interest = $('input[name="mailchimp_groups_interests"]:checked').val();
						if ( typeof selected_interest !== 'undefined' ) {
							group_interest = selected_interest;
						}
						
					} else if ( group_name.toLowerCase().indexOf('checkboxes') !== -1 ) {
						// checkbox group interests
						var selected = new Array();
						$('input[name="mailchimp_groups_interests[]"]:checked').each(function() {
							selected.push( $(this).val() );
						});
						group_interest = selected;
					} else {
						group_interest = $('select[name="mailchimp_groups_interests"]').val();
					}
				}
				auto_optin = (auto_optin) ? 'subscribed' : 'pending';
				
				var current_args = {
						api_key: api_key,
						auto_optin: auto_optin,
						enabled: enabled,
						list_id: list_id,
						list_name: list_name,
						group: group_id,
						group_interest: group_interest,
						desc: api_key
					},
					args = _.extend( view.mailchimp.provider_args, current_args );
				
				view.mailchimp.provider_args = args;
				Hustle.Events.trigger("optin.service.saved", view);
			},
			init: function() {
				var me = this,
					view = content_view;
				
				// Updates list groups on list change
				var update_list_groups = function(e){
					var $this = $(e.target),
						$form = $("#wph-optin-service-details-form"),
						$wrapper = $('.wph-optin-list-groups'),
						$interests_wrapper = $(".wph-optin-list-group-interests-wrap"),
						$load_more_list = $('.mailchimp_optin_load_more_lists'),
						data = _.reduce( $form.serializeArray(), function(obj, item){
							obj[ item['name'] ] = item['value'];
							return obj;
						}, {});

					data.action = 'hustle_mailchimp_get_list_groups';
					data._ajax_nonce = $this.data("nonce");
					
					$interests_wrapper.empty();
					// Loading indicator.
					$wrapper.html( $( "#wpoi-loading-indicator" ).html() );
					$load_more_list.hide();
					   
					$.get( ajaxurl, data)
						.done(function(res){
							if( res ){
								if ( res.success ) {
									$wrapper.html( res.data );
									$load_more_list.show();
									Hustle.Events.trigger("modules.view.rendered", content_view);
								} else {
									$load_more_list.hide();
									$wrapper.empty();
								}
							   
							}
						});

				};
				
				// Updates group interests on group change
				var update_group_interests = function(e){

					var $wrapper = $(".wph-optin-list-group-interests-wrap"),
						$this = $(e.target),
						$form = $("#wph-optin-service-details-form"),
						data = _.reduce( $form.serializeArray(), function(obj, item){
							obj[ item['name'] ] = item['value'];
							return obj;
						}, {});

					if( ["-1", "0"].indexOf(e.target.value) !== -1 ){ // return if selection is not meaningful
						$wrapper.empty();
						return;
					}

					// Loading indicator.
					$wrapper.html( $( "#wpoi-loading-indicator" ).html() );
					data._ajax_nonce = $this.data("nonce");
					data.action = 'hustle_mailchimp_get_group_interests';

					$.get( ajaxurl, data )
						.done(function(res){
							if( res && res.success ){
								$wrapper.html( res.data.html );
								// Clear interest selection on button click
								$wrapper.find('.wpoi-leave-group-intrests-blank-radios').click(function(e) {
									e.preventDefault();
									$wrapper.find('input[name="mailchimp_groups_interests"]').prop('checked', false)
								});
								Hustle.Events.trigger("modules.view.rendered", content_view);
							}

						   if( res && !res.success )
							   $wrapper.empty();
						})
						.fail(function(res){

						});
				};
				
				// Load more lists
				var load_more_lists = function(e){
					var $this = $(e.target),
						$form = $this.closest("form"),
						data = $form.serialize(),
						$placeholder = $("#optin-provider-account-options");

					// Loading indicator.
					$placeholder.html( $( "#wpoi-loading-indicator" ).html() );

					data += "&action=refresh_provider_account_details&load_more=true";
					data += "&optin=mailchimp";

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
				
				// API key changed
				var api_key_updated = function(e) {
					content_view.is_service_modal_updated = true;
				};
				
				$(doc).on("change", ".mailchimp_optin_email_list", update_list_groups );
				$(doc).on("change", "#mailchimp_groups", update_group_interests );
				$(doc).on("change", "input[name='optin_api_key']", api_key_updated );
				$(doc).on("click", ".mailchimp_optin_load_more_lists", load_more_lists);
				Hustle.Events.on( 'optin.service.prepare', $.proxy(this.update_args, this ) );
				Hustle.Events.on( 'optin.service.show.selected', $.proxy( this.show_selected, this ) );
			}
		});
	});
	
	
}(jQuery,document));
