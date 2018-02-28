(function ($) {
	define([
		'text!tpl/dashboard.html',
	], function( popupTpl ) {
		return Backbone.View.extend({
			className: 'wpmudev-section--popup',

			events: {
				"click .wpmudev-action-done": "save",
				"click .wpmudev-button-clear-exports": "clear_exports"
			},

			initialize: function( options ) {
				this.action = options.action;
				this.nonce = options.nonce;
				this.data = options.data;
				this.id = options.id;

				return this.render();
			},

			render: function() {
				var self = this,
					tpl = false,
					data = {}
				;

				data.action = 'forminator_load_' + this.action + '_popup';
				data._ajax_nonce = this.nonce;
				data.data = this.data;

				if( this.id ) {
					data.id = this.id;
				}

				self.$el.html('<div class="preloader"><div class="wpmudev-loading"></div></div>');
				// make slightly bigger
				self.$el.find( '.preloader .wpmudev-loading' ).css({width:'32px',height:'32px'});

				var ajax = $.post(Forminator.Data.ajaxUrl, data)
					.done(function (result) {
							if (result && result.success) {
								// Append & Show content
								self.$el.html(result.data);
								self.$el.find('.wpmudev-hidden-popup').show(400);

								// Init select2
								Forminator.Utils.init_select2();

								// Init Pagination on custom form if exist
								var custom_form = self.$el.find('.forminator-custom-form');
								if (custom_form.length > 0) {
									Forminator.Pagination.init(custom_form);
								}

								// Delegate events
								self.delegateEvents();
							}
						}
					);

				//remove the preloader
				ajax.always(function () {
					self.$el.find(".preloader").remove();
				});
			},

			save: function ( e ) {
				e.preventDefault();
				var data = {},
					nonce = $( e.target ).data( "nonce" )
				;

				data.action = 'forminator_save_' + this.action + '_popup';
				data._ajax_nonce = nonce;

				// Retieve fields
				$('.wpmudev-popup-form input, .wpmudev-popup-form select').each( function () {
					var field = $( this );
					data[ field.attr('name') ] = field.val();
				});

				$.ajax({
					url: Forminator.Data.ajaxUrl,
					type: "POST",
					data: data,
					success: function( result ) {
						Forminator.Popup.close( false, function() {
							window.location.reload();
						});
					}
				});
			},
			
			clear_exports: function ( e ) {
				e.preventDefault();
				var data = {},
					self = this,
					nonce = $( e.target ).data( "nonce" ),
					form_id = $( e.target ).data( "form-id" )
				;
				
				data.action = 'forminator_clear_' + this.action + '_popup';
				data._ajax_nonce = nonce;
				data.id = form_id;

				$.ajax({
					url: Forminator.Data.ajaxUrl,
					type: "POST",
					data: data,
					success: function() {
						self.render();
					}
				});
			}
		});
	});
})(jQuery);
