(function($, doc, win){
	"use strict";
	
	var Optin = window.Optin || {};
	
	Optin.SS_log_view = Backbone.Model.extend({
		url: inc_opt.ajaxurl + '?action=module_viewed',
		defaults: {
			page_type: inc_opt.page_type,
			page_id: inc_opt.page_id,
			type: '',
			uri: encodeURI( window.location.href ),
			module_type: 'social_sharing'
		},
		parse: function( res ) {
			if ( res.success ) {
				console.log('Log success!');
			} else {
				console.log('Log failed!');
			}
		}
	});
	Optin.SS_log_conversion = Optin.SS_log_view.extend({ url: inc_opt.ajaxurl + '?action=hustle_sshare_converted' });
	
	Optin.SShare_native_share_enpoints = {
		'facebook': 'https://www.facebook.com/sharer/sharer.php?u=',
		'twitter': 'https://twitter.com/intent/tweet?url=',
		'google': 'https://plus.google.com/share?url=',
		'pinterest': 'https://www.pinterest.com/pin/create/button/?url=',
		'reddit': 'https://www.reddit.com/submit?url=',
		'linkedin': 'https://www.linkedin.com/shareArticle?mini=true&url=',
		'vkontakte': 'https://vk.com/share.php?url=',
	};
	
	Optin.SShare = Backbone.View.extend({
		template: Optin.template("hustle-sshare-front-tpl"),
		events: {
			'click a.hustle-social-icon-native': 'click_social_native',
			'click a.hustle-social-icon-custom': 'click_social_linked'
		},
		initialize: function( opts ) {
			this.opts = opts;
			this.module_id = opts.module_id;
			this.module_type = 'social_sharing';
			this.content = opts.content;
			this.design = opts.design;
			this.settings = opts.settings;
			this.is_compat = ( typeof opts.is_compat !== 'undefined' ) 
				? true
				: false;
			
			if ( typeof opts.parent !== 'undefined' ) {
				this.parent = opts.parent;
			}
			
			this.model_json = _.extend(
				{
					module_id: this.module_id,
					module_display_type: this.module_display_type
				},
				this.content,
				this.design,
				this.settings
			);
			
			this.render();
		},

		render: function(args){
			var parent_container = this.parent,
				location_align_x = this.model_json.location_align_x,
				location_align_y = this.model_json.location_align_y,
				current_tpl_settings = _.templateSettings;
				
			// if needs compatibility e.g. upfront which uses another _.templateSettings
			if ( this.is_compat ) {
				Optin.global_mixin();
				// force our _.templateSettings setup
				_.templateSettings = {
					evaluate:    /<#([\s\S]+?)#>/g,
					interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
					escape:      /\{\{([^\}]+?)\}\}(?!\})/g
				};
			}
			
			this._handle_icons_order();
			
			this.setElement( this.template( _.extend( {}, this.model_json ) ) );
			
			if ( this.module_display_type === 'floating_social' ) {                    
				if ( this.model_json.location_type === 'content' ) {
					parent_container = $('#content');
				} else if ( this.model_json.location_type === 'selector' ) {
					parent_container = $( this.model_json.location_target );
				} else {
					parent_container = $('body');
				}
			}
			
			if ( parent_container.length == 0 ) return;
			this.$el.appendTo(parent_container);
			
			// location align for floating social
			if ( this.module_display_type === 'floating_social' ) {
				var $floating_social_container = $('.hustle-sshare-module-id-' + this.model_json.module_id);
				if ( location_align_x === 'left' ) {
					$floating_social_container.css( 'left', this.model_json.location_left + 'px' );
				} else {
					$floating_social_container.css( 'right', this.model_json.location_right + 'px' );
				}
				if ( location_align_y === 'top' ) {
					$floating_social_container.css( 'top', this.model_json.location_top + 'px' );
				} else {
					$floating_social_container.css( 'top', 'auto' );
					$floating_social_container.css( 'bottom', this.model_json.location_bottom + 'px' );
				}
			}
			
			// after getting the template, revert back to previous _.templateSettings
			if ( this.is_compat ) {
				_.templateSettings = current_tpl_settings;
			}
			
			this.html = this.$el.html();
			this.log_view(this.module_display_type, this.opts);
		},
		_handle_icons_order: function() {
			var reordered = {},
				social_icons = this.model_json.social_icons,
				icons_order = this.model_json.icons_order,
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
				
				this.model_json.social_icons = reordered;
			}
		},
		sanitize_url: function( url ) {
			if ( url ) {
				if (!/^(f|ht)tps?:\/\//i.test(url)) {
					url = "http://" + url;
				}
			}
			return url;
		},
		click_social_native: function(e) {
			e.preventDefault();
			
			var me = this,
				$this = this.$(e.target),
				$anchor = $this.closest('a.hustle-social-icon-native'),
				social = $anchor.data('social');
				
			this._update_social_counter($anchor);
			// update other module with same social icon
			$('a[data-social="'+ social +'"]').not($anchor).each( function(){
				me._update_social_counter($(this));
			} );
				
			// update social counter and log conversion
			this.log_conversion(this.module_display_type, this.opts, social, 'native');
				
			if ( social && typeof Optin.SShare_native_share_enpoints[social] != 'undefined' ) {
				window.open(
					Optin.SShare_native_share_enpoints[social]+ hustle_vars.current_url, 
					'MsgWindow', 
					'menubar=no,toolbar=no,resizable=yes,scrollbars=yes'
				);
			}
		},
		click_social_linked: function(e) {
			var $this = this.$(e.target),
				$anchor = $this.closest('a.linked-social-share'),
				social = $anchor.data('social');
				
			// log conversion only if allowed
			if ( this.opts.tracking_types != null && _.isTrue( this.opts.tracking_types[this.module_display_type] ) ) {
				this.log_conversion(this.module_display_type, this.opts, social, 'linked');
			}
		},
		_update_social_counter: function($a){
			_.delay(function(){
				var $counter = $a.find('.hustle-shares-counter span');
				if ( $counter.length ) {
					var val = parseInt($counter.text()) + 1;
					$counter.text(val);
				}
			}, 5000);
		},
		log_view: function( type, ss ){
			if ( ss.tracking_types != null && _.isTrue( ss.tracking_types[type] ) ) {
				if ( typeof Optin.SS_log_view != 'undefined' ) {
					var logView = new Optin.SS_log_view();
					logView.set( 'type', type );
					logView.set( 'module_id', ss.module_id );
					logView.save();
				}
			}
			// set cookies used for "show less than" display condition
			if( !window.hasOwnProperty( "optin_vars" ) ){ // don't set cookie in admin
				var show_count_key = Hustle.consts.Module_Show_Count + this.module_type + "-" + ss.module_id,
					current_show_count = Hustle.cookie.get( show_count_key );
				Hustle.cookie.set( show_count_key, current_show_count + 1, 30 );
			}
		},
		log_conversion: function( type, ss, source, service_type ) {
			var track_conversion = ( ss.tracking_types != null && _.isTrue( ss.tracking_types[type] ) )
				? true
				: false;
				
			if ( typeof Optin.SS_log_conversion != 'undefined' ) {
				var logConversion = new Optin.SS_log_conversion();
				logConversion.set( 'type', type );
				logConversion.set( 'module_id', ss.module_id );
				logConversion.set( 'source', source + '_icon' );
				logConversion.set( 'track', track_conversion );
				logConversion.set( 'service_type', service_type );
				logConversion.save();
			}
		}
	});
	
	Optin.SShare_floating = Optin.SShare.extend({
		module_display_type: 'floating_social',
		display_type: 'column'
	});
	
	Optin.SShare_widget = Optin.SShare.extend({
		module_display_type: 'widget',
		display_type: 'row'
	});
	
	Optin.SShare_shortcode = Optin.SShare.extend({
		module_display_type: 'shortcode',
		display_type: 'row'
	});
	
	/**
	 * Render inline sshare ( widget )
	 */
	Optin.render_hustle_sshare_module_embeds = function(use_compat) {
		$('.hustle_sshare_module_widget_wrap, .hustle_sshare_module_shortcode_wrap').each( function() {
			var $this = $(this),
				id = $this.data('id'),
				type = $this.data('type'),
				is_admin = hustle_vars.is_admin === '1';
				
				if( !id ) return;
				
				var module = _.find(Modules, function ( mod, key ) {
					return id == key;
				});
				
				if (!module) return;
				
				var type_enabled = type + '_enabled';
				
				// if not admin and test mode enabled
				if ( typeof module.test_types !== 'undefined' 
						&& module.test_types !== null 
						&& typeof module.test_types[type] !== 'undefined'
						&& ( module.test_types[type] || module.test_types[type] === 'true' )
						&& !is_admin ) {
					return;
					
				} else if ( typeof module.test_types !== 'undefined' 
						&& module.test_types !== null 
						&& typeof module.test_types[type] !== 'undefined'
						&& ( module.test_types[type] || module.test_types[type] === 'true' )
						&& is_admin ) {
					// bypass the enabled settings
					module.settings[ type_enabled ] = 'true';
				}
				
				if ( !_.isTrue( module.settings[type_enabled] ) ) return;
				
				module.parent = $this;
				if ( typeof use_compat !== 'undefined' && use_compat ) {
					module.is_compat = true;
				}
				
				$this.html('');
				if ( type === 'widget' ) {
					new Optin.SShare_widget(module);
				} else {
					new Optin.SShare_shortcode(module);
				}
		});
	};
	
	Optin.render_hustle_sshare_module_embeds(false);
	
	Hustle.Events.on("upfront:editor:widget:render", function(widget) {
		Optin.render_hustle_sshare_module_embeds(true);
	});
	Hustle.Events.on("upfront:editor:shortcode:render", function(shortcode) {
		Optin.render_hustle_sshare_module_embeds(true);
	});

}(jQuery, document, window));
