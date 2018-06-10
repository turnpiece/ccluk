Hustle.define( "SShare.Models",  function(){
	"use strict";
	
	
	var Base = Module.Model.extend({
		defaults: _.extend( Module.Model.prototype.defaults, {
			module_type: 'social_sharing'
		})
	});
	
	var Content = Hustle.get("Models.M").extend({
		defaults: {
			module_name: '',
			active: 1,
			test_mode: 0,
			service_type: 'native',
			click_counter: 1,
			social_icons: ''
		}
	});
	
	var Design = Hustle.get("Models.M").extend({
		defaults: {
			icon_style: 'squared',
			icons_order: '',
			customize_colors: 0,
			icon_bg_color: 'rgba(146, 158, 170, 1)',
			icon_color: 'rgba(255, 255, 255, 1)',
			floating_social_bg: 'rgba(4, 48, 69, 1)',
			floating_counter_border: 'rgba(146, 158, 170, 1)',
			floating_counter_color: 'rgba(255, 255, 255, 1)',
			floating_social_animate_icons: 0,
			drop_shadow: 0,
			drop_shadow_x: 0,
			drop_shadow_y: 0,
			drop_shadow_blur: 0,
			drop_shadow_spread: 0,
			drop_shadow_color: 'rgba(0,0,0,0)',
			floating_inline_count: 0,
			// counter_border: 'rgba(146, 158, 170, 1)',
			// counter_text: 'rgba(255, 255, 255, 1)',
			customize_widget_colors: 0,
			widget_icon_bg_color: 'rgba(146, 158, 170, 1)',
			widget_icon_color: 'rgba(255, 255, 255, 1)',
			widget_bg_color: 'rgba(146, 158, 170, 1)',
			widget_animate_icons: 0,
			widget_drop_shadow: 0,
			widget_drop_shadow_x: 0,
			widget_drop_shadow_y: 0,
			widget_drop_shadow_blur: 0,
			widget_drop_shadow_spread: 0,
			widget_drop_shadow_color: 'rgba(0,0,0,0)',
			widget_inline_count: 0,
			// widget_counter_text: 'rgba(255, 255, 255, 1)',
			widget_counter_border: 'rgba(146, 158, 170, 1)',
			widget_counter_color: 'rgba(255, 255, 255, 1)',
		}
	});

	var Display_Settings = Hustle.get("Models.M").extend({
		defaults:{
			floating_social_enabled: true,
			widget_enabled: true,
			shortcode_enabled: true,
			conditions: '',
			location_type: 'screen',
			location_target: '',
			location_align_x: 'left',
			location_align_y: 'top',
			location_top: 0,
			location_bottom: 0,
			location_right: 0,
			location_left: 0
		},
		initialize: function(data){
			_.extend( this, data );
			if( ! ( this.get('conditions') instanceof Backbone.Model ) ){
				/**
				 * Make sure conditions is not an array
				 */
				if( _.isEmpty( this.get('conditions') ) && _.isArray( this.get('conditions') )  )
					this.conditions = {};

				var hModel = Hustle.get("Model");
				this.set( 'conditions', new hModel( this.conditions ) );
			}
			this.on( 'change', this.user_has_change, this );

		}
	});
	
	var Types = Hustle.get("Models.M").extend({
		defaults: {
			widget: '',
			shortcode: '',
		}
	});
	

	return {
		Base: Base,
		Content: Content,
		Design: Design,
		Display_Settings: Display_Settings,
		Types: Types
	};
});


