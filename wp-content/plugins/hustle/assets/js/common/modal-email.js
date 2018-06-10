Hustle.define("Modal_Email", function($){
		"use strict";

		var view_email_list_cache = {};

		return  Backbone.View.extend({
				id: "wph-modal-email",
				className: "wpmudev-modal",
				template: Optin.template("hustle-modal-email-tpl"),
				list_header_template: Optin.template( 'wpoi-email-list-header-tpl' ),
				list_template: Optin.template("wpoi-emails-list-tpl"),
				show_delay: 350,
				events: {
						'click .wpmudev-modal-mask, .wpmudev-i_close, .wpmudev-i_close path': 'hide',
				},
				initialize: function(){

						return this.render();
				},
				render: function(){
						var self = this,
								html = this.template(this.model)
						;

						html = html.replace("__id", this.model.id); // add the id to the export csv link
						html = html.replace("__type", this.model.type); // add the type to the export csv link
						this.$el.html( html );

						if( !view_email_list_cache[this.model.id] ){
								view_email_list_cache[this.model.id] = $.ajax({
										url: ajaxurl,
										type: "GET",
										data: {
												action: "hustle_get_email_lists",
												id: self.model.id,
												_ajax_nonce: $("#hustle_get_emails_list_nonce").val()
										}
								});
								this.delay_show = 0;
						}

						view_email_list_cache[this.model.id].then(function(res){
								if( res.success ){
					var module_fields = res.data.module_fields,
						fields = [];

					if ( ! self.model.module_fields.length ) {
						self.model.module_fields = module_fields;
						self.$('.wpmudev-listing-head').html( self.list_header_template({ module_fields: module_fields }));

						// We only need the name and label in listing template
						_.each( module_fields, function( field ) {
							fields.push( {name: field.name, label: field.label} );
						});
					}
 
										var content = self.list_template( { subscriptions: res.data.subscriptions, module_fields: fields  });

										self.$(".wpmudev-listing-body").html( content );
								}

						});

						this.$el.appendTo( "#wpmudev-hustle" );
						this.show();
						return this;
				},
				show: function(){
					// Body.
					$('body').addClass('wpmudev-modal-is_active');
					// Overlay.
					this.$el.addClass('wpmudev-modal-active');
					// Modal.
					this.$el.find('.wpmudev-box-modal').addClass('wpmudev-show').removeClass('wpmudev-hide');
				},
				close: function(e){
						e.preventDefault();
						this.$el.removeClass("show");
						_.delay( function(){
								this.remove();
						}.bind(this), 350);
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

		});

});
