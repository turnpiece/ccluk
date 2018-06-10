(function($,doc,win){
	"use strict";
	if( inc_opt.is_upfront ) return;

	Optin.Module_log_cta_conversion = Backbone.Model.extend({
		url: inc_opt.ajaxurl + '?action=hustle_cta_converted',
		defaults: {
			page_type: inc_opt.page_type,
			page_id: inc_opt.page_id,
			type: '',
			uri: encodeURI( window.location.href )
		},
		parse: function( res ) {
			if ( res.success ) {
				console.log('Log success!');
			} else {
				console.log('Log failed!');
			}
		}
	});

	/**
	 * Front View Model
	 *
	 * This model is use to render Pop-up and Slide-in.
	 **/
	Optin.Module = Backbone.View.extend({
		isCC: false,
		display_id: 'display',
		showClass: 'wph-modal-active',
		module_id: '',
		module_type: '',
		type: '',
		settings: {},
		data: {},
		appear_after: 'time',
		mask: false,
		should_remove: false,
		parent: 'body',
		anim_in_time: 10,
		anim_out_time: 2000,
		viewed: false,
		cookie_key: '',
		events: {
			'click': 'click',
			'click .hustle-modal-close .hustle-icon': 'close',
			'click button.hustle-modal-close': 'close',
			'click .hustle-modal-cta': 'cta_clicked',
		},
		click: _.noop,

		initialize: function( opts ) {
			this.data = opts;
			this.settings = opts.settings;
			this.module_id = opts.module_id;
			this.module_type = opts.module_type;
			this.maskClass = 'wph-modal-mask wpmudev-modal-mask';
			this.appear_after = this.settings.triggers.trigger;
			this.expiration = parseInt( this.settings.expiration, 10 );
			// Calculate expiration days depends on what's been set
			this.expiration_days = this.get_expiration_days();
			// Escape key event.

			if ( this.module_type === 'popup' ) {
			  this.cookie_key = Optin.POPUP_COOKIE_PREFIX + this.module_id;
			} else if ( this.module_type === 'slidein' ) {
			  this.cookie_key = Optin.SLIDE_IN_COOKIE_PREFIX + this.module_id;
			}

			this.triggers = {
				on_time: this.settings.triggers.on_time,
				on_time_delay: this.settings.triggers.on_time_delay,
				on_time_unit: this.settings.triggers.on_time_unit,
				on_scroll: this.settings.triggers.on_scroll,
				on_scroll_page_percent: parseInt( this.settings.triggers.on_scroll_page_percent ),
				on_scroll_css_selector: this.settings.triggers.on_scroll_css_selector,
				on_click_element: this.settings.triggers.on_click_element,
				on_exit_intent_per_session: this.settings.triggers.on_exit_intent_per_session,
				on_exit_intent_delayed: this.settings.triggers.on_exit_intent_delayed,
				on_exit_intent_delayed_time: this.settings.triggers.on_exit_intent_delayed_time,
				on_exit_intent_delayed_unit: this.settings.triggers.on_exit_intent_delayed_unit,
				on_adblock: this.settings.triggers.on_adblock,
				on_adblock_delayed: this.settings.triggers.on_adblock_delayed,
				on_adblock_delayed_time: this.settings.triggers.on_adblock_delayed_time,
				on_adblock_delayed_unit: this.settings.triggers.on_adblock_delayed_unit
			};

			if ( _.contains( ['time', 'scrolled', 'adblock'], this.appear_after )
				|| ( 'exit_intent' === this.appear_after && _.isTrue( this.settings.triggers.on_exit_intent_per_session ) ) ) {
				this.should_remove = true;
			}

			if ( !this.should_display() ) {
				return;
			}

			this.render();
		},

		// Check if module should display.
		should_display: function() {
			if ( this.settings.after_close === 'no_show_on_post' ) {
				if ( parseInt( inc_opt.page_id, 10 ) > 0 ) {
					return !_.isTrue( Optin.cookie.get( this.cookie_key + '_' + inc_opt.page_id ) );
				} else {
					return true;
				}
			} else if ( this.settings.after_close === 'no_show_all' ) {
				return !_.isTrue( Optin.cookie.get( this.cookie_key ) );
			} else {
				return true;
			}
		},

		get_expiration_days: function() {
			switch (this.settings.expiration_unit) {
				case 'months':
					return this.expiration * 30;
					break;
				case 'weeks':
					return this.expiration * 7;
					break;
				case 'hours':
					return this.expiration / 24;
					break;
				case 'minutes':
					return this.expiration / (24 * 60);
					break;
				case 'seconds':
					return this.expiration / (24 * 60 * 60);
					break;
				default:
					return this.expiration;
			}
		},

		render: function() {

			var template = ( parseInt(this.data.content.use_email_collection, 10) )
				? Optin.template("wpmudev-hustle-modal-with-optin-tpl")
				: Optin.template("wpmudev-hustle-modal-without-optin-tpl");

			var html = template( this.data );

			this.$el.addClass( 'module_id_' + this.module_id );
			this.$el.html( html );

			// supply with provider args
			if ( typeof this.data.content.args !== 'undefined' && typeof this.data.content.active_email_service !== 'undefined' ) {
				var provider_template = Optin.template( 'optin-'+ this.data.content.active_email_service +'-args-tpl' ),
					provider_content = provider_template(this.data.content.args),
					$target_provider_container = this.$('.hustle-modal-provider-args-container');

				if ( $target_provider_container.length ) {
					$target_provider_container.html(provider_content);
				}
			}

			this.$el.appendTo(this.parent);
			this.$el.display = $.proxy( this, 'display' );
			this.$el.on( 'show', $.proxy( this, 'on_module_show' ) );
			this.$el.on( 'hide', $.proxy( this, 'on_module_hide' ) );
			this.$el.data(this.data);
			this.html = this.$el.html();

			// Prepare display
			this.prepare_display();

			// Trigger display
			if (typeof this[this.appear_after + '_trigger'] === 'function') {
			  this[this.appear_after + '_trigger']();
			}

			return this;
		},

		prepare_display: function() {
			var me = this;

			// Marked viewed when display is triggered
			this.viewed = true;

			if( this.$el.is( '.' + this.showClass ) ) {
				// If already shown, return
				return;
			}

			this.$el.html( this.html );
		},

		display: function() {
			var me = this;

			if( this.$el.is( '.' + this.showClass ) ) {
				// If already shown, return
				return;
			}

			this.$el.removeClass( this.settings.animation_out );
			this.add_mask();
			this.$el.trigger( 'show', this );
			// Add escape key listener.
			$(document).on( 'keydown', $.proxy( this.escape_key, this ) );

			// only use the `on_submit` for module with no collection,
			// meaning a form shortcode was added on the module content
			if ( !parseInt(this.data.content.use_email_collection, 10) ) {
				var after_submit = this.data.settings.on_submit,
					data_type = ( typeof this.data.type !== 'undefined' )
						? this.data.type
						: this.data.module_type;
				if ( after_submit === 'close' ) {
               // Fix for CF7.
					if(this.$el.find( "form.wpcf7-form" ).length > 0 ) {
						document.addEventListener( 'wpcf7mailsent', function( event ) {
							me.close();
						}, false );
					} else {
						this.$el.find( "form" ).on("submit", _.bind( this.close, this ) );
					}
					this.handle_compatibility();
				} else if ( after_submit === 'redirect' ) {
					this.$el.find( "form" ).on("submit", _.bind( this.redirect_form_submit, this ) );
				} else {
					// do nothing used for ajax forms
				}

			}
		},

		add_mask: function() {
			var me = this,
			  no_scroll = _.isFalse(this.settings.allow_scroll_page),
			  no_bg_click = _.isFalse(this.settings.not_close_on_background_click);

			if (
				// Only add mask to popups.
				this.data.module_type !== 'popup'
				// Do not duplicate mask.
				|| this.$el.find('.wph-modal-mask').length > 0
			) {
				return;
			}

			if ( no_scroll ) {
			  $('html').addClass('hustle-no-scroll');
			}

			this.mask = $( '<div class="' + this.maskClass + ' ">' );
			this.mask.insertBefore(this.$el.find('.hustle-modal'));

			if ( no_bg_click ) {
			  this.mask.on( 'click', $.proxy( this, 'close' ) );
			}
		},

		time_trigger: function() {

			if ( _.isTrue( this.triggers.on_time ) ) {

				var delay = parseInt( this.triggers.on_time_delay, 10 ) * 1000;

				if( 'minutes' === this.triggers.on_time_unit ) {
					delay *= 60;
				} else if( 'hours' === this.triggers.on_time_unit ) {
					delay *= ( 60 * 60 );
				}
				// Display after a certain time.
				_.delay( $.proxy( this, 'display' ), delay );

			} else {
				this.display();
			}
		},

		click_trigger: function() {
			var me = this,
				selector = '';

			if( "" !== (selector = $.trim( this.triggers.on_click_element ) )  ){
				var $clickable = $(selector);

				if( $clickable.length ) {
					$(doc).on( 'click', selector, function(e) {
						e.preventDefault();
						me.display();
					} );
				}
			}

			// Clickable button added with shortcode
			$(doc).on("click", ".hustle_module_shortcode_trigger", function(e){
				e.preventDefault();
				if( $(this).data("id") == me.data.module_id && $(this).data("type") == me.type ) {
					me.display();
				}
			});
		},

		scroll_trigger: function() {
			var me = this, module_shown = false;

			if( 'scrolled' === this.triggers.on_scroll  ){
				$(win).scroll(_.debounce( function(){
					if ( module_shown ) {
						return;
					}

					if( (  win.pageYOffset * 100 / $(doc).height() ) >= parseFloat( me.triggers.on_scroll_page_percent ) ) {
						me.display();
					   module_shown = true;
					}

				}, 50) );
			}

			if( 'selector' === this.triggers.on_scroll  ){
				 var $el = $( this.triggers.on_scroll_css_selector );

				 if( $el.length ){
					 $(win).scroll(_.debounce( function(){
						 if ( module_shown ) {
							 return;
						 }
						 if( win.pageYOffset >= $el.position().top ) {
							 me.display();
							 module_shown = true;
						 }

					 }, 50));
				 }
			 }
		},

		scrolled_trigger: function() {
			return this.scroll_trigger();
		},

		exit_intent_trigger: function() {
			var me = this,
				delay = 0
			;

			// handle delay
			if ( _.isTrue( this.triggers.on_exit_intent_delayed ) ) {

				delay = parseInt( this.triggers.on_exit_intent_delayed_time, 10 ) * 1000;

				if(  'minutes' === this.triggers.on_exit_intent_delayed_time ) {
					delay *= 60;
				} else if( 'hours' === this.triggers.on_exit_intent_delayed_time ) {
					delay *= ( 60 * 60 );
				}
			}

			// handle per session
			if ( _.isTrue( this.triggers.on_exit_intent_per_session ) ) {
				$(doc).one("mouseleave", _.debounce( function(e){
					me.set_exit_timer();
				}, 300 ));
			} else {
				$(doc).on("mouseleave", _.debounce( function(e){
					me.set_exit_timer();
				}, 300 ));
			}

			// When user moves cursor back into window, reset timer.
			$( 'html' ).on( 'mousemove', _.debounce(function(e) {
				me.reset_exit_timer();
			}, 300));

			// Timer variable to be set or reset.
			this.exit_timer = null;
			// When user moves cursor back into window, reset timer.
			this.reset_exit_timer = function() {
				// Only run if timer is still going.
				if (me.exit_timer) {
					// Reset the timer.
					clearTimeout(me.exit_timer);
				}
			}

			// When cursor is out of window, set timer for trigger.
			this.set_exit_timer = function() {
				// Set the timer, allowing it to be reset.
				me.exit_timer = setTimeout( function trigger() {
					// Timer is done.
					me.exit_timer = null;
					// Display module
					me.display();

				}, delay);
			}
		},

		adblock_trigger: function() {
			var adblock = ! $('#hustle_optin_adBlock_detector').length;

			if ( adblock && _.isTrue( this.triggers.on_adblock ) ) {
				if( _.isFalse( this.triggers.on_adblock_delayed ) ){
					this.display();
				} else {
					var delay = parseInt( this.triggers.on_adblock_delayed_time, 10 ) * 1000;

					if(  'minutes' === this.triggers.on_adblock_delayed_unit ) {
						delay *= 60;
					} else if( 'hours' === this.triggers.on_adblock_delayed_unit ) {
						delay *= ( 60 * 60 );
					}

					_.delay( $.proxy( this, 'display' ), delay );
				}
			}
		},

		on_module_show: function() {
			this.$el.addClass('wph-modal-active');
			this.$el.attr( 'data-id', this.data.module_id );
			this.$el.attr( 'data-type', this.data.module_type );
			this.on_animation_in();
			$(document).trigger( 'hustle:module:displayed', [this.data] );
		},

		on_module_hide: function() {
			$(document).trigger("wpoi:hide", [this.type, this.$el, this.opt ]);
		},

		on_animation_in: function() {
			var me = this,
				$modal = this.$el.find('.hustle-modal'),
				animation_in = this.data.settings.animation_in;

			if (this.$el.hasClass('wph-modal-active') && $modal.hasClass('hustle-animated')) {
				setTimeout( function() {
					if (animation_in === 'no_animation') {
						$modal.addClass('hustle-modal-static');
					} else {
						$modal.addClass('hustle-animate-' + animation_in );
					}
					me.apply_custom_size();
				}, 100);
			} else {
				$modal.addClass('hustle-modal-static');
				// Apply custom size regardless of no animation.
				me.apply_custom_size();
			}
		},

		apply_custom_size: function() {
			Optin.apply_custom_size(this.data, this.$el);
		},

		close: function(e) {
			if ( typeof e !== 'undefined' ) {
				e.stopPropagation();
			}

			// clear any running interval
			this.clear_running_compat_interval();

			var me = this,
				$modal = this.$el.find('.hustle-modal'),
				animation_in_class = 'hustle-animate-' + this.data.settings.animation_in,
				animation_out_class = 'hustle-animate-' + this.data.settings.animation_out,
				time_out = 1000
			;

			if ( $modal.hasClass('hustle-animated') ) {

				if ( this.data.settings.animation_in === 'no_animation' ) {
					$modal.removeClass('hustle-modal-static').addClass(animation_out_class);
				} else {
					$modal.removeClass(animation_in_class).addClass(animation_out_class);
				}

				if ( this.data.settings.animation_out === 'fadeOut' ) {
					time_out = 305;
				}
				if ( this.data.settings.animation_out === 'newspaperOut' ) {
					time_out = 505;
				}
				if ( this.data.settings.animation_out === 'bounceOut' ) {
					time_out = 755;
				}

				setTimeout(function(){
					me.$el.removeClass('wph-modal-active');
					$modal.removeClass(animation_out_class);
				}, time_out);

			}

			if ($modal.hasClass('hustle-modal-static')) {
				$modal.removeClass('hustle-modal-static').hide();
				me.$el.removeClass('wph-modal-active').hide();
			}

			// Allow scrolling if previously disabled.
			$('html').removeClass('hustle-no-scroll');
			// Get rid of escape key listener.
			$(document).off( 'keydown', $.proxy( me.escape_key, this ) );

			// save cookies for 'after_close' property
			if ( this.settings.after_close === 'no_show_on_post' ) {
				if ( parseInt( inc_opt.page_id, 10 ) > 0 ) {
					Optin.cookie.set( this.cookie_key + '_' + inc_opt.page_id, this.module_id, this.expiration_days );
				}
			} else if ( this.settings.after_close === 'no_show_all' ) {
				Optin.cookie.set( this.cookie_key, this.module_id, this.expiration_days );
			}
		},
		escape_key: function(e) {
			// If escape key, close.
			if (e.keyCode === 27) {
				this.close(e);
			}
		},
		redirect_form_submit: function(e){
			var self = this,
				$form = $(e.target);

			window.location.replace( $form.attr("action") );
		},
		/**
		* Some form plugins have their own form submit listener,
		* so have to tackle each one of them and apply the `on_submit` close behavior
		*/
		handle_compatibility: function() {

			// e-newsletter, when a shortcode was added on module content
			var me = this,
				$enewsletter_form = this.$el.find('form#subscribes_form'),
				enewsletter_waited = 1000,
				enewsletter_max_wait = 216000000; // 1 hour

			if ( $enewsletter_form.length ) {
				me.wait_enewsletter_result = setInterval(function(){
					enewsletter_waited += 1000;
					var $enewsletter_message = me.$el.find('#message');
					if ( !_.isEmpty( $enewsletter_message.text().trim() ) || enewsletter_max_wait === enewsletter_waited ) {
						me.close();
					}
				}, 1000);
			}
		},
		clear_running_compat_interval: function() {
			// e-newsletter
			if ( typeof this.wait_enewsletter_result !== 'undefined' ) {
				clearInterval(this.wait_enewsletter_result);
			}

		},
		cta_clicked: function(e) {
			if ( this.module_type !== 'embedded' && typeof Optin.Module_log_cta_conversion != 'undefined' ) {
				var log_cta_conversion = new Optin.Module_log_cta_conversion();
				log_cta_conversion.set( 'type', this.module_type );
				log_cta_conversion.set( 'module_type', this.module_type );
				log_cta_conversion.set( 'module_id', this.module_id );
				log_cta_conversion.save();
			}
		}
	});

}(jQuery,document,window));
