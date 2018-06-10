Hustle.define( 'Modal_Error', function($) {
	'use strict';

	var ErrorLog = Backbone.View.extend({
		template: Optin.template( 'hustle-error-list-tpl' ),
		className: 'wpmudev-listing-row',
		controller: false,
		initialize: function(opts) {
			this.controller = opts.controller;
			this.module_fields = opts.module_fields;
			this.render();
		},
		render: function() {
			var me = this,
				html = this.template( {model: this.model, module_fields: this.module_fields} );

			this.$el.html( html );
			this.$el.appendTo( this.controller.$('.wpmudev-listing-body') );
		}
	});

	return Backbone.View.extend({
		id: 'wph-modal-error',
 		template: Optin.template("hustle-modal-error-tpl"),
		list_header_template: Optin.template( 'hustle-error-header-list-tpl' ),
		className: "wpmudev-modal",
		events: {
			'click .inc-opt-close-error-list': 'toggleErrorLog',
			'click .wpmudev-button-clear-logs': 'clearLogs',
			'click .wpmudev-button-delete-logs': '_clean',
			'click .wpmudev-button-cancel-delete-logs': 'cancelDelete',
			'click .wpmudev-modal-mask, .wpmudev-i_close, .wpmudev-i_close path': 'hide',
		},

		initialize: function(opts) {
			this.$el.html( this.template(
				{
					id: this.model.id
				}
			) );
			// Error Logs open button.
			this.button = opts.button;
			return this.render();
		},

		render: function() {
			var me = this;
			var html = this.template(this.model);
			html = html.replace("__id", this.model.id); // add the id to the export csv link
			html = html.replace("__type", this.model.type); // add the type to the export csv link

			this.clearLogButton = this.$('.wpmudev-button-clear-logs');
			this.exportButton = this.$('.wpmudev-button-download-csv');
			this.deleteConfirmation = this.$('.hustle-delete-logs-confirmation');
			$.getJSON( window.ajaxurl, {
				id: this.model.id,
				_wpnonce: optin_vars.error_log_nonce,
				action: 'get_error_list'
			}, function( res ) {
				if ( res.success && res.data && res.data.logs ) {
					var module_fields = res.data.module_fields;
					me.model.module_fields = module_fields;
					me.$('.wpmudev-listing-head').html( me.list_header_template({ module_fields: module_fields }));
					_.each( res.data.logs, function( log ) {
						var error = new ErrorLog({
							module_fields: module_fields,
							model: log,
							controller: me
						});
					});
					me.show();
				}
			});
			this.$el.html(html);
			if ($('#wph-modal-error').length > 0) {
				$('#wph-modal-error').html(this.$el);
			} else {
				this.$el.appendTo('#wpmudev-hustle');
			}
			this.show();
			return this;
		},

		show: function() {
			// Body.
			$('body').addClass('wpmudev-modal-is_active');
			// Overlay.
			this.$el.addClass('wpmudev-modal-active');
			// Modal.
			this.$el.find('.wpmudev-box-modal').addClass('wpmudev-show').removeClass('wpmudev-hide');
		},

		hide: function(e) {
			var $target = $(e.target),
				$modal = this.$el.find('.wpmudev-box-modal'),
				me = this
			;

			// If target is not close button or mask, quit.
			if (
				!$target.hasClass('wpmudev-modal-mask')
				&& !$target.hasClass('wpmudev-i_close')
				&& !$target.parent().hasClass('wpmudev-i_close')
			) {
				return;
			}
	
			// Modal.
			$modal.removeClass('wpmudev-show').addClass('wpmudev-hide');
			
			setTimeout(function() {
				// Overlay.
				me.$el.removeClass('wpmudev-modal-active');
				// Body.
				$('body').removeClass('wpmudev-modal-is_active');
				// Modal.
				$modal.removeClass('wpmudev-hide');
			}, 500);
		},

		toggleErrorLog: function() {
			this.$el.removeClass('show');
		},

		clearLogs: function(e) {
			$(e.target).parents('.wpmudev-footer-clear').find('.hustle-delete-logs-confirmation').removeClass('wpmudev-hidden');
			this.clearLogButton.attr('disabled', true);
			this.exportButton.attr('disabled', true);
		},

		_clean: function() {
			var me = this;

			$.get(window.ajaxurl, {
				id: this.model.id,
				_wpnonce: optin_vars.clear_log_nonce,
				action: 'clear_logs'
			}, function( res ) {
				if ( res.success ) {
					me.toggleErrorLog();
					_.delay(function() {
						me.button.remove();
						me.remove();
					}, 350 );
				}
			});
		},

		cancelDelete: function(e) {
			$(e.target).parents('.hustle-delete-logs-confirmation').addClass('wpmudev-hidden');
			this.clearLogButton.removeAttr('disabled');
			this.exportButton.removeAttr('disabled');
		}
	});
});
