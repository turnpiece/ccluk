Hustle.define( 'SShare.Settings_View', function( $, doc, win ) {
	'use strict';

	return Hustle.View.extend(_.extend({}, Hustle.get("Mixins.Model_Updater"), {
		template: Hustle.template("wpmudev-hustle-sshare-section-settings-tpl"),
		target_container: $('#wpmudev-hustle-box-section-settings'),
		message_editor: false,
		events: {
			"click .wpmudev-copy button" : "copy_shortcode",
		},
		init: function( options ){
			 
			// unset listeners
			this.stopListening( this.model, 'change', this.model_updated );
			
			// set listeners
			this.listenTo( this.model, 'change', this.model_updated );

			this.conditions_view = options.conditions_view;
			this.listenTo( this.model, "change:enabled", this.toggle_panel );
			this.conditions_view.on("toggle_condition", this.update_conditions_label);
			this.conditions_view.on("change:update_view_label", this.update_conditions_label);
			return this.render();
		},
		render: function() {
			if ( this.target_container.length ) {                
				var me = this,
					data = this.model.toJSON();
				
				this.setElement( this.template( _.extend( {
					module_type: 'social_sharing'
				}, data ) ) );
				
				this.$(".wph-conditions").replaceWith(  this.conditions_view.$el );
				
				return this;
			}
			return;
		},
		after_render: function() {
			if ( this.target_container.length ) {
				
			}
		},
		toggle_panel: function( model ){
			this.$(".switch-wrap").toggleClass("open closed");
			this.$el.find("#wph-floating-social-condition-labels").toggle();
		},
		update_conditions_label: function( conditions_view ){
			$('#wph-floating-social-condition-labels').html( conditions_view.get_all_conditions_labels() );
		},
		model_updated: function(e) {
			var changed = e.changed;
			
			console.log(changed);
			
			// location_type
			if ( 'location_type' in changed ) {
				var $location_type_options = this.$('#wpmudev-sshare-selector-location-options');
				if ( changed['location_type'] === 'selector' ) {
					$location_type_options.removeClass('wpmudev-hidden');
				} else {
					$location_type_options.addClass('wpmudev-hidden');
				}
			}
			
			// location_align_x
			if ( 'location_align_x' in changed ) {
				var $location_align_x_left = this.$('#wpmudev-floating-horizontal-left'),
					$location_align_x_right = this.$('#wpmudev-floating-horizontal-right');
				if ( changed['location_align_x'] === 'left' ) {
					$location_align_x_left.addClass('current');
					$location_align_x_right.removeClass('current');
				} else {
					$location_align_x_left.removeClass('current');
					$location_align_x_right.addClass('current');
				}
			}
			
			// location_align_y
			if ( 'location_align_y' in changed ) {
				var $location_align_y_top = this.$('#wpmudev-floating-vertical-top'),
					$location_align_y_bottom = this.$('#wpmudev-floating-vertical-bottom');
				if ( changed['location_align_y'] === 'top' ) {
					$location_align_y_top.addClass('current');
					$location_align_y_bottom.removeClass('current');
				} else {
					$location_align_y_top.removeClass('current');
					$location_align_y_bottom.addClass('current');
				}
			}
		},
		copy_shortcode: function(e) {
			var $this = $(e.target),
				$input = $this.siblings('input')
			;
			// Temporarily enable input to copy.
			$input.prop('disabled', false).select();
			// Copy selection to clipboard.
			document.execCommand('copy');
			// Disable input again to avoid changes.
			$input.prop('disabled', true);
		}
	} ) );
});
