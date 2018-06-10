(function($,doc){
	'use strict';    
    
    Optin.Mixins.add_services_mixin( 'e_newsletter', function( content_view ) {
		return new Optin.Provider({
			id: 'e_newsletter',
			provider_args: { enabled: 0 },
            default_data: {
                enabled: false,
                auto_optin: false,
				synced: false
            },
            show_selected: function() {
                
                // if not the service being edited do not proceed
                if ( content_view.editing_service !== this.id ) {
                    return;
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
                
                var enabled = view.model.get('email_services').e_newsletter ? view.model.get('email_services').e_newsletter.enabled : false,
                    auto_optin = $('input[name="optin_auto_optin"]').is(':checked'), 
                    $list = $('input[name="optin_email_list"]'),
                    list_id = '',
					synced = $('input[name="synced"]').val();
                    
				// checkbox e-Newsletter lists 
					var selected = new Array();
					$('input[name="optin_email_list"]:checked').each(function() {
						selected.push( $(this).val() );
					});
					list_id = selected;
						
                auto_optin = (auto_optin) ? 'subscribed' : 'pending';
				
                var current_args = {
                        auto_optin: auto_optin,
                    	enabled: enabled,
						list_id: list_id,
						synced: synced
                    },
                    args = _.extend( view.e_newsletter.provider_args, current_args );
                
                view.e_newsletter.provider_args = args;
                Hustle.Events.trigger("optin.service.saved", view);
            },
			init: function() {
				var me = this,
                    view = content_view;
                
				var auto_optin_updated = function (e){
                    content_view.is_service_modal_updated = true;
				};
				
                // Updates lists
                var lists_updated = function(e){
                    content_view.is_service_modal_updated = true;
                };
               
				$(doc).on("change", "input[name='optin_auto_optin']", auto_optin_updated);
                $(doc).on("change", "input[name='optin_email_list']", lists_updated );
                Hustle.Events.on( 'optin.service.prepare', $.proxy(this.update_args, this ) );
                Hustle.Events.on( 'optin.service.show.selected', $.proxy( this.show_selected, this ) );
			}
		});
	});
    
    
}(jQuery,document));
