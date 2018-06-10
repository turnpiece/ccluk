(function( $ ) {
	"use strict";
	
	Optin = Optin || {};
	
	Optin.Hustle_Embeddeds = {
		render_hustle_module_embeds: function(use_compat) {
			var me = this;
			$('.hustle_module_after_content_wrap, .hustle_module_widget_wrap, .hustle_module_shortcode_wrap').each(function () {
				var $this = $(this),
					id = $this.data('id'),
					type = $this.data('type'),
					is_admin = hustle_vars.is_admin === '1';
				
				if( !id ) return;
				
				var module = _.find(Modules, function ( mod, key ) {
					return id == key;
				});
				
				if (!module) return;
				
				if ( module.test_types !== null ) {
					// if not admin and test mode enabled
					if ( typeof module.test_types !== 'undefined' 
							&& typeof module.test_types[type] !== 'undefined'
							&& ( module.test_types[type] || module.test_types[type] === 'true' )
							&& !is_admin ) {
						return;
						
					} else if ( typeof module.test_types !== 'undefined' 
							&& typeof module.test_types[type] !== 'undefined'
							&& ( module.test_types[type] || module.test_types[type] === 'true' )
							&& is_admin ) {
						// bypass the enabled settings
						module.settings[ type + '_enabled' ] = 'true';
					}
				}
				
				
				if ( !_.isTrue( module.settings[ type + '_enabled' ] ) ) return;
				
				// sanitize cta_url 
				if ( module.content.cta_url ) {
					if (!/^(f|ht)tps?:\/\//i.test(module.content.cta_url)) {
						module.content.cta_url = "http://" + module.content.cta_url;
					}
				}
				
				var template = ( parseInt(module.content.use_email_collection, 10) )
					? Optin.template("wpmudev-hustle-modal-with-optin-tpl")
					: Optin.template("wpmudev-hustle-modal-without-optin-tpl");
				
				$this.html( template(module) );

				// supply with provider args
				if ( typeof module.content.args !== 'undefined' && typeof module.content.active_email_service !== 'undefined' ) {
					var provider_template = Optin.template( 'optin-'+ module.content.active_email_service +'-args-tpl' ),
						provider_content = provider_template( module.content.args),
						$target_provider_container = $('.hustle-modal-provider-args-container');
						
					if ( $target_provider_container.length ) {
						$target_provider_container.html(provider_content);
					}
				}
				
				module.type = type;
				me.on_animation_in(module, $this);
				
				// bypass type from (widget,shortcode) into embedded for cookie purposes
				module.type = 'embedded';
				// added display type for log view cookie purposes
				module.display_type = type;
				// trigger the log view
				$(document).trigger( 'hustle:module:displayed', module );
				
				// Log cta conversion
				$this.find('a.hustle-modal-cta').on( 'click', function(){
					if ( typeof Optin.Module_log_cta_conversion != 'undefined' ) {
						var log_cta_conversion = new Optin.Module_log_cta_conversion();
						log_cta_conversion.set( 'type', type );
						log_cta_conversion.set( 'module_type', 'embedded' );
						log_cta_conversion.set( 'module_id', id );
						log_cta_conversion.save();
					}
				} );
				
				// Hide close button.
				$this.find('.hustle-modal-close').hide();
			});
			
		},
		on_animation_in: function( module, $this ) {
			var me = this,
				$modal = $this.find('.hustle-modal'),
				animation_in = module.settings.animation_in;
				
			if ( $modal.hasClass('hustle-animated') ) {
				setTimeout( function() {
					$modal.addClass('hustle-animate-' + animation_in );
					Optin.apply_custom_size(module, $this);
				}, 100);
			} else {
				// Apply custom size regardless of no animation.
				Optin.apply_custom_size(module, $this);
			}
		},
	};

	// added delay to wait for markups to finish
	_.delay( function(){
		Optin.Hustle_Embeddeds.render_hustle_module_embeds(false);
	}, 500 );
	
	Hustle.Events.on("upfront:editor:widget:render", function(widget) {
		Optin.Hustle_Embeddeds.render_hustle_module_embeds(true);
	});
	Hustle.Events.on("upfront:editor:shortcode:render", function(shortcode) {
		Optin.Hustle_Embeddeds.render_hustle_module_embeds(true);
	});

}(jQuery));
