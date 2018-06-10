Hustle.define("SShare.View", function($, doc, win){
	"use strict";

	return Hustle.View.extend({
		el: '.wpmudev-hustle-sshare-wizard-view',
		message_box_tpl: Optin.template('wpoi-social-sharing-message-box-tpl'),
		preview: false,
		preview_model: false,
		initial_data: new Backbone.Model({
			content: '',
		}),
		events: {
			'click .wpmudev-button-save': 'save_changes',
			'click .wpmudev-button-continue': 'save_continue',
			'click .wpmudev-button-finish': 'save_finish',
			'click .wpmudev-button-cancel': 'cancel',
			'click .wpmudev-button-back': 'back',
			'change .wpmudev-menu .wpmudev-select': 'mobile_navigate',
		},
		init: function( opts ){
			this.content_view = opts.content_view;
			this.design_view = opts.design_view;
			this.settings_view = opts.settings_view;

			// unset listeners
			this.stopListening( this.content_view.model, 'change', this.update_base_model );
			this.stopListening( this.design_view.model, 'change', this.design_view_changed );

			// set listeners
			this.listenTo( this.content_view.model, 'change', this.update_base_model );
			this.listenTo( this.design_view.model, 'change', this.design_view_changed );
			$(document).on( 'change keyup keypress', 'input[name=module_name]', $.proxy( this.validate_modal_name, this ) );
			return this.render();
		},
		render: function(){
			// Names & Services
			this.content_view.target_container.html('');
			this.content_view.render();
			this.content_view.delegateEvents();
			this.content_view.target_container.append( this.content_view.$el );
			this.content_view.after_render();

			// Appearance
			this.render_design_view();

			// Settings
			this.settings_view.target_container.html('');
			this.settings_view.render();
			this.settings_view.delegateEvents();
			this.settings_view.target_container.append( this.settings_view.$el );
			this.settings_view.after_render();


			Hustle.Events.trigger("modules.view.rendered", this);
		},
		render_design_view: function() {
			this.design_view.target_container.html('');
			this.design_view.render_design();
			this.design_view.delegateEvents();
			this.design_view.target_container.append( this.design_view.$el );
			this.design_view.after_render();
		},
		design_view_changed: function(e) {
			var key = Object.keys(e.changed)[0];
			if ( this.design_view.excluded_rerender.indexOf( key ) !== -1  ) {
				this.design_view.model_updated(e.changed);
			} else {
				this.render_design_view();
			}
		},
		sanitize_data: function() {

			// module_name
			if ( _.isEmpty( this.model.get('module_name') ) ) {
				this.model.set( 'module_name', this.content_view.model.get('module_name') );
			}

		},
		save: function($btn) {
			if ( !Module.Validate.validate_module_name() ) return false;

			this.sanitize_data();

			// preparing the data
			var me = this,
				module = this.model.toJSON(),
				content = this.content_view.model.toJSON(),
				design = this.design_view.model.toJSON(),
				settings = this.settings_view.model.toJSON();

			content = this.content_view.get_social_icons_data(content);

			// ajax save here
			return $.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'hustle_save_sshare_module',
					_ajax_nonce: $btn.data('nonce'),
					id: ( !$btn.data('id') ) ? '-1' : $btn.data('id'),
					module: module,
					content: content,
					design: design,
					settings: settings,
					shortcode_id: me._get_shortcode_id()
				},
				complete: function(resp) {
					var response = resp.responseJSON;
				}
			});
		},
		save_changes: function(e) {
			e.preventDefault();
			var me = this,
				$btn = $(e.target);
				
			me.$('.wpmudev-button-save, .wpmudev-button-continue').addClass('wpmudev-button-onload').prop('disabled', true);

			var save = this.save($btn);

			if ( save ) {
				save.done( function(resp) {
					if (typeof resp === 'string') {
						resp = JSON.parse(resp);
					}
					if ( resp.success ) {
						var current_url = window.location.pathname + window.location.search;
						if ( current_url.indexOf('&id=') === -1 ) {
							current_url = current_url + '&id=' + resp.data;
							window.history.replaceState( {} , '', current_url );
							me.$('.wpmudev-menu-services-link a, .wpmudev-menu-design-link a, .wpmudev-menu-settings-link a').each(function(){
								$(this).attr( 'href', $(this).data('link') + '&id=' + resp.data );
							});
						}
						$btn.data( 'id', resp.data );
						$btn.siblings().data( 'id', resp.data );
						Module.hasChanges = false;
					}
				} ).always( function() {
					me.$('.wpmudev-button-save, .wpmudev-button-continue').removeClass('wpmudev-button-onload').prop('disabled', false);

				});
			} else {
				// If saving did not work, remove loading icon.
				me.$('.wpmudev-button-save, .wpmudev-button-continue').removeClass('wpmudev-button-onload').prop('disabled', false);
			}
		},
		save_continue: function(e) {
			e.preventDefault();
			var me = this;
			// Disable buttons during save.
			me.$('.wpmudev-button-save, .wpmudev-button-continue').addClass('wpmudev-button-onload').prop('disabled', true);
							
			var save = this.save($(e.target));
				
			if ( save ) {
				save.done( function(resp) {
					if (typeof resp === 'string') {
						resp = JSON.parse(resp);
					}
					if ( resp.success ) {
						var module_id = resp.data;
						// redirect
						var current = optin_vars.current.section || false,
							target_link = '';

						window.onbeforeunload = null;
						if ( !current || current === 'services' ) {
							target_link =  me.$('.wpmudev-menu-design-link a').data('link');
						} else if ( current === 'design' ) {
							target_link = me.$('.wpmudev-menu-settings-link a').data('link');
						}
						if ( target_link.indexOf('&id') === -1 ) {
							target_link += '&id=' + module_id;
						}
						return window.location.replace(target_link);
					}
				} );
			} else {
				// If saving did not work, remove loading icon.
				me.$('.wpmudev-button-save, .wpmudev-button-continue').removeClass('wpmudev-button-onload').prop('disabled', false);
			}

		},
		save_finish: function(e) {
			e.preventDefault();
			var me = this;
			// Disable buttons during save.
			me.$('.wpmudev-button-save, .wpmudev-button-continue').addClass('wpmudev-button-onload').prop('disabled', true);
							
			var save = this.save($(e.target));
				
			if ( save ) {
				save.done( function(resp) {
					if ( resp.success ) {
						var module_id = resp.data;
						window.onbeforeunload = null;
						return window.location.replace( '?page=' + optin_vars.current.listing_page + '&module=' + module_id );
					}
				} );
			} else {
				// If saving did not work, remove loading icon.
				me.$('.wpmudev-button-save, .wpmudev-button-continue').removeClass('wpmudev-button-onload').prop('disabled', false);
			}

		},
		cancel: function(e) {
			e.preventDefault();
			window.onbeforeunload = null;
			window.location.replace( '?page=' + optin_vars.current.listing_page );
			return;
		},
		back: function(e) {
			e.preventDefault();
			var me = this;
			me.$('.wpmudev-button-back').addClass('wpmudev-button-onload');
			// redirect
			var current = optin_vars.current.section;
			window.onbeforeunload = null;
			if ( current === 'design' ) {
				window.location.replace( this.$('.wpmudev-menu-services-link a').attr('href') );
			} else if ( current === 'settings' ) {
				window.location.replace( this.$('.wpmudev-menu-design-link a').attr('href') );
			}
			return;
		},
		mobile_navigate: function(e) {
			e.preventDefault();
			var value = e.target.value;

			if (value === 'services') {
				window.location.replace( this.$('.wpmudev-menu-services-link a').attr('href') );
			} else if (value === 'design') {
				window.location.replace( this.$('.wpmudev-menu-design-link a').attr('href') );
			} else {
				window.location.replace( this.$('.wpmudev-menu-settings-link a').attr('href') );
			}
		},

		//on type or paste
		validate_modal_name : function(e) {
			Module.Validate.on_change_validate_module_name(e);
		},
		update_base_model: function(e) {
			var changed = e.changed;

			// for module_name
			if ( 'module_name' in changed ) {
				this.model.set( 'module_name', changed['module_name'], { silent:true } )
			}

		},
		_get_shortcode_id: function(){
			return this.content_view.model.get('module_name').trim().toLowerCase().replace(/\s+/g, '-');
		},

	});

});
