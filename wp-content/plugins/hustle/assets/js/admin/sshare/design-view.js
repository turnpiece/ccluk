Hustle.define("SShare.Design_View", function($, doc, win){
	"use strict";
	return Hustle.View.extend(_.extend({}, Hustle.get("Mixins.Model_Updater"), {
		template: Optin.template("wpmudev-hustle-sshare-section-design-tpl"),
		target_container: $('#wpmudev-hustle-box-section-design'),
		social_icons: {},
		service_type: 'native',
		click_counter: true,
		excluded_rerender: [ 
			'floating_social_bg',
			'floating_counter_color',
			'floating_counter_border',
			'icon_bg_color',
			'icon_color',
			'drop_shadow_x',
			'drop_shadow_y',
			'drop_shadow_blur',
			'drop_shadow_spread',
			'drop_shadow_color',
			'widget_icon_bg_color',
			'widget_icon_color',
			'widget_bg_color',
			'widget_drop_shadow_x',
			'widget_drop_shadow_y',
			'widget_drop_shadow_blur',
			'widget_drop_shadow_spread',
			'widget_drop_shadow_color',
			'widget_counter_border',
			'widget_counter_color',
		],
		init: function( opts ){
			this.on( 'rendered', this.create_color_pickers );
			this.social_icons = opts.social_icons;
			this.service_type = opts.service_type;
			this.click_counter = opts.click_counter;
			
			return this.render_design();
		},
		render_design: function(args){
			
			if ( this.target_container.length ) {                
				var me = this,
					data = this.model.toJSON();
				
				this._handle_icons_order();
				
				this.setElement( this.template( _.extend( {
					module_type: 'social_sharing',
					social_icons: me.social_icons,
					service_type: me.service_type,
					click_counter: me.click_counter,
				}, data ) ) );
				
				return this;
			}
			return;
		},
		after_render: function() {
			if ( this.target_container.length ) {
				this.create_color_pickers();
				this.make_icons_sortable();
				this._handle_preview();
			}
		},
		create_color_pickers: function() {
			this.$(".wpmudev-color_picker").wpColorPicker({
				change: function(event, ui){
					var $this = $(this);
					$this.val( ui.color.toCSS()).trigger("change");
				}
			});
		},
		make_icons_sortable: function() {
			var me = this,
				sortArgs = {
					items: '.hustle-social-icon',
					revert: true,
					axis: 'x',
					containment: this.$('#wpmudev-reoder-icons'),
					stop: function(e, ui) {
						me._reorder_icons();
					}
				};
			
			this.$('#wph-reorder-icons').sortable(sortArgs).disableSelection();
		},
		model_updated: function(updates) {
			this._handle_preview();
		},
		_handle_preview: function() {
			var me = this,
				$floating_preview = $('#wph-sshare-preview-floating .hustle-shares-floating'),
				$floating_counters = $floating_preview.find('.hustle-shares-counter'),
				$floating_icon_container = $floating_preview.find('.hustle-icon-container'),
				$floating_icon_path = $floating_preview.find('.hustle-icon-path'),
				$floating_icon = $floating_preview.find('.hustle-social-icon'),
				$widget_preview = $('#wph-sshare-preview-widget .hustle-shares-widget'),
				$widget_counters = $widget_preview.find('.hustle-shares-counter'),
				$widget_icon_container = $widget_preview.find('.hustle-icon-container'),
				$widget_icon_path = $widget_preview.find('.hustle-icon-path'),
				$widget_icon = $widget_preview.find('.hustle-social-icon'),
				design_data = this.model.toJSON();
				
			// floating_social_bg
			$floating_preview.css( 'background-color', design_data.floating_social_bg );
			
			// floating_counter_color
			$floating_counters.css( 'color', design_data.floating_counter_color );
			
			// customize_colors
			if ( _.isTrue( design_data.customize_colors ) ) {
				
				if ( design_data.icon_style === 'rounded' || design_data.icon_style === 'squared' ) {
					// icon_bg_color
					$floating_icon_container.css( 'background-color', design_data.icon_bg_color );
				} else if (design_data.icon_style === 'outline' ) {
					// icon_bg_border
					$floating_icon.css( 'border-color', design_data.icon_bg_color );
				}
				
				// icon_color
				$floating_icon_path.css( 'fill', design_data.icon_color );
				
				// If counter exists, use counter border (overrides icon_bg_border).
				if (this.service_type === 'native' && _.isTrue(this.click_counter)) {
					// floating_counter_border
					$floating_icon.css( 'border-color', design_data.floating_counter_border );
				}
				
			}
			
			// floating drop shadow
			if ( _.isTrue( design_data.drop_shadow ) ) {
				var box_shadow = '' + 
					design_data.drop_shadow_x + 'px ' +
					design_data.drop_shadow_y + 'px ' +
					design_data.drop_shadow_blur + 'px ' +
					design_data.drop_shadow_spread + 'px ' +
					design_data.drop_shadow_color;
				
				$floating_preview.css( 'box-shadow', box_shadow );
			}
			
			// widget_bg_color
			$widget_preview.css( 'background-color', design_data.widget_bg_color );
			
			// widget_counter_color
			$widget_counters.css( 'color', design_data.widget_counter_color );
			
			// customize_widget_colors
			if ( _.isTrue( design_data.customize_widget_colors ) ) {
				
				if ( design_data.icon_style === 'rounded' || design_data.icon_style === 'squared' ) {
					// widget_icon_bg_color
					$widget_icon_container.css( 'background-color', design_data.widget_icon_bg_color );
				} else if (design_data.icon_style === 'outline' ) {
					// icon_bg_border
					$widget_icon.css( 'border-color', design_data.widget_icon_bg_color );
				}
				
				// icon_color
				$widget_icon_path.css( 'fill', design_data.widget_icon_color );
				
				// If counter exists, use counter border (overrides widget_icon_bg_border).
				if (this.service_type === 'native' && _.isTrue(this.click_counter)) {
					// widget_counter_border
					$widget_icon.css( 'border-color', design_data.widget_counter_border );
				}
				
			}
			
			// widget drop shadow
			if ( _.isTrue( design_data.widget_drop_shadow ) ) {
				var widget_box_shadow = '' + 
					design_data.widget_drop_shadow_x + 'px ' +
					design_data.widget_drop_shadow_y + 'px ' +
					design_data.widget_drop_shadow_blur + 'px ' +
					design_data.widget_drop_shadow_spread + 'px ' +
					design_data.widget_drop_shadow_color;
				
				$widget_preview.css( 'box-shadow', widget_box_shadow );
			}
			
		},
		_handle_icons_order: function() {
			var reordered = {},
				social_icons = this.social_icons,
				icons_order = this.model.get('icons_order'),
				icons_order_arr = icons_order.split(',');
			
			if ( icons_order && icons_order_arr.length ) {
				_.each(icons_order_arr, function( data, key ) {
					if ( typeof social_icons[data] !== 'undefined' ) {
						reordered[data] = social_icons[data];
						social_icons = _.pick(social_icons, function(val, index){
							if ( data !== index ) {
								return index = val;
							}
						});
					}
				});
				
				// if still have some, append those
				if ( Object.keys(social_icons).length ) {
					reordered = _.extend( reordered, _.pick(social_icons, function(val, index) {
						if ( typeof val !== 'undefined' ) {
							return index = val;
						}
					}) );
				}
				
				this.social_icons = reordered;
			}
		},
		_reorder_icons: function() {
			var order = [];
			$('#wph-reorder-icons .hustle-social-icon').each( function() {
				order.push($(this).data('id'));
			} );
			this.model.set( 'icons_order', order.join() );
		}
	} ) );

});
