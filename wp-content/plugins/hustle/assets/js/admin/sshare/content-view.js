Hustle.define("SShare.Content_View", function($, doc, win){
	"use strict";
	return Hustle.View.extend(_.extend({}, Hustle.get("Mixins.Model_Updater"), {
		template: Optin.template("wpmudev-hustle-sshare-section-services-tpl"),
		target_container: $('#wpmudev-hustle-box-section-services'),
		init: function( opts ){
			
			// unset listeners
			this.stopListening( this.model, 'change', this.model_updated );
			
			// set listeners
			this.listenTo( this.model, 'change', this.model_updated );
			
			return this.render();
		},
		events: {
			'click ul.wpmudev-tabs-menu li label': 'toggle_checkbox',
			// Native icons.
			'change .wpmudev-social-item input.wpmudev-social-item-native-enable': 'toggle_icon',
			// Custom icons.
			'change .wpmudev-social-custom .wpmudev-social-item input.toggle-checkbox': 'toggle_icon',
		},
		render: function(args){
			
			if ( this.target_container.length ) {                
				var me = this,
					data = this.model.toJSON();
				
				this.setElement( this.template( _.extend( {
					module_type: 'social_sharing'
				}, data ) ) );
				
				return this;
			}
			return;
		},
		after_render: function() {
			if ( this.target_container.length ) {
				
			}
		},
		model_updated: function(e) {
			var changed = e.changed;
			
			// for service_type
			if ( 'service_type' in changed ) {
				this.service_type_updated(changed.service_type);
			}
			
			// for click_counter
			if ( 'click_counter' in changed ) {
				this.click_counter_updated(changed.click_counter);
			}
		},
		service_type_updated: function(val) {
			var $counter_options = this.$('#wpmudev-sshare-counter-options'),
				$native_options = $('.wph-wizard-services-icons-native'),
				$custom_options = $('.wph-wizard-services-icons-custom');
			
			if ( val === 'native' ) {
				$counter_options.removeClass('wpmudev-hidden');
				$custom_options.addClass('wpmudev-hidden');
				$native_options.removeClass('wpmudev-hidden');
			} else {
				$counter_options.addClass('wpmudev-hidden');
				$native_options.addClass('wpmudev-hidden');
				$custom_options.removeClass('wpmudev-hidden');
			}
		},
		click_counter_updated: function(val) {
			$('#wph-wizard-services-icons-native .wpmudev-social-item').each(function() {
				var $checkbox = $(this).find('.toggle-checkbox'),
					is_checked = $checkbox.is(':checked'),
					$input_counter = $(this).find('input.wpmudev-input_number');
					
				if ( val && is_checked ) {
					$input_counter.removeClass('wpmudev-hidden');
				} else {
					if ( !$input_counter.hasClass('wpmudev-hidden') ) {
						$input_counter.addClass('wpmudev-hidden');
					} 
				}
			});
		},
		toggle_checkbox: function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			var $this = this.$(e.target),
				$li = $this.closest('li'),
				$input = $li.find('input'),
				prop = $input.data('attribute');
				
			if ( $li.hasClass('current') ) return;
			
			$li.addClass('current');
			$li.siblings().removeClass('current');
			this.model.set( prop, $input.val() );
			
		},
		toggle_icon: function(e) {
			var $this = this.$(e.target),
				is_checked = $this.is(':checked'),
				counter_enabled = this.model.get('click_counter'),
				$parent_container = $this.closest('.wpmudev-social-item'),
				$input_counter = $parent_container.find('input.wpmudev-input_number'),
				show_counter = ( is_checked && counter_enabled ) ? true : false;
				
			if( is_checked ) {
				$parent_container.removeClass('disabled');
			} else {
				$parent_container.addClass('disabled');
			}
			
			// Only show counter for native icons.
			if ( $this.parents('.wpmudev-social-native').length > 0 ) {
				if ( show_counter ) {
					$input_counter.removeClass('wpmudev-hidden');
				} else {
					$input_counter.addClass('wpmudev-hidden');
				}
			}
		},
		set_social_icons: function() {
			var services = this.model.toJSON();
			services = this.get_social_icons_data(services);
			this.model.set( 'social_icons', services.social_icons, {silent:true} );
		},
		get_social_icons_data: function( services ) {
			
			var $social_containers = $( '#wph-wizard-services-icons-' + services['service_type'] + ' .wpmudev-social-item'),
				social_icons = {};
			
			$social_containers.each( function() {
				var $sc = $(this),
					$toggle_input = $sc.find('input.toggle-checkbox'),
					icon = $toggle_input.data('id'),
					$counter = $sc.find('input.wpmudev-input_number'),
					$link = $sc.find('input.wpmudev-input_text');
					
					// check if counter have negative values
					if ( $counter.length ) {
						var counter_val = parseInt($counter.val());
						if ( counter_val < 0 ) {
							$counter.val(0);
						}
					}
					
					if ( $toggle_input.is(':checked') ) {
						social_icons[icon] = {
							'enabled': true,
							'counter': ( $counter.length ) ? $counter.val() : '0',
							'link': ( $link.length ) ? $link.val() : ''
						};
					}
				
			} );
			
			if ( $social_containers.length ) {
				services['social_icons'] = social_icons;
			}
			
			return services;
		},
	} ) );

});
