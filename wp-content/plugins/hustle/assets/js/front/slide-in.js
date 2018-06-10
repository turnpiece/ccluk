(function( $, doc, win ) {
		"use strict";
		if( inc_opt.is_upfront ) return;

	Optin = window.Optin || {};

	Optin.SlideIn = Optin.Module.extend({
		className: 'wph-modal',
		type: 'slidein',
		prevent_hide_after: false,
		delay_time: 0,

		render: function() {
			// Add display position class based upon user setting.
			var cLass = 'inc_opt_slidein inc_opt_slidein_' + this.settings.display_position + ' inc_optin wpoi-slide';
			this.delay_time = this.convert_to_microseconds(this.settings.auto_hide_time, this.settings.auto_hide_unit);

			this.$el.addClass( cLass );

			Optin.Module.prototype.render.apply( this, arguments );
		},

		convert_to_microseconds: function(value, unit) {
			if (unit === "seconds") {
				return parseInt( value, 10 ) * 1000;
			} else if (unit === "minutes") {
				return parseInt( value, 10 ) * 60 * 1000;
			}else {
				return parseInt( value, 10 ) * 60 * 60 * 1000;
			}
		},

		on_module_show: function() {
			if ( this.mask ) {
					this.mask.removeClass('wpoi-show');
			}

			if( _.isTrue( this.settings.auto_hide ) ) {
				var me = this;

				_.delay(function(){
					// if hide after is not prevented, then hide it
					if ( ! me.prevent_hide_after ) {
						me.on_animation_out();
					}
				}, this.delay_time );
			}
			Optin.Module.prototype.on_module_show.apply(this, arguments);
		},

		on_module_hide: function() {
			var should_remove = false;

			if ( 'hide_all' === this.settings.after_close ) {
				Optin.cookie.set( Optin.SLIDE_IN_COOKIE_HIDE_ALL, this.optin_id, 30 );
				should_remove = true;
			}
			if( "no_show" === this.settings.after_close ) {
				Optin.cookie.set( Optin.SLIDE_IN_COOKIE_PREFIX + this.optin_id,  this.optin_id, 30 );
				should_remove = true;
			}

			if ( should_remove ) {
				// Remove completely
				if ( this.mask ) {
						this.mask.remove();
				}
				this.remove();
			}
		},

		click: function() {
			this.prevent_hide_after = true;
		},

		on_animation_in: function() {
			var me = this,
					$modal = this.$el.find('.hustle-modal'),
					direction = this.get_slide_in_direction(this.settings.display_position, 'in')
				;
					
			setTimeout( function() {
					$modal.addClass('hustle-animate-slideIn' + direction );
					me.apply_custom_size();
			}, 100);
		},

		close: function(e) {
			e.stopPropagation();
			this.on_animation_out();
			
			// save cookies for 'after_close' property
			if ( this.settings.after_close === 'no_show_on_post' ) {
				if ( parseInt( inc_opt.page_id, 10 ) > 0 ) {
					Optin.cookie.set( this.cookie_key + '_' + inc_opt.page_id, this.module_id, this.expiration_days );
				}
			} else if ( this.settings.after_close === 'no_show_all' ) {
				Optin.cookie.set( this.cookie_key, this.module_id, this.expiration_days );
			}
		},

		on_animation_out: function() {
			var me = this,
					direction = this.get_slide_in_direction(this.settings.display_position, 'out'),
					$modal = this.$el.find('.hustle-modal'),
					animation_in_class = 'hustle-animate-slideIn' + direction,
					animation_out_class = 'hustle-animate-slideOut' + direction,
					time_out = 1000
			;

			// Start animation out.
			$modal.removeClass(animation_in_class).addClass(animation_out_class);
			
			setTimeout(function(){
					me.$el.removeClass('wph-modal-active');
					$modal.removeClass(animation_out_class);
			}, time_out);

		},

		get_slide_in_direction: function(direction, in_or_out) {
				if (
					direction === 'nw'
					|| direction === 'w'
					|| direction === 'sw'
				) {
					return 'Left';
				} else if (
					direction === 'ne'
					|| direction === 'e'
					|| direction === 'se'
				) {
					return 'Right';
					// If bottom in or top out, use Up.
				} else if (
					(direction === 's' && in_or_out === 'in')
					|| (direction === 'n' && in_or_out === 'out')
				) {
					return 'Up';
				}
				// Else use Down.
				return 'Down';
		}

	});
}(jQuery, document, window));
