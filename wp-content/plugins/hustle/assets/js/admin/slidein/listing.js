Hustle.define("Slidein.Listing", function($){
	"use strict";
	var Delete_Confirmation = Hustle.get("Delete_Confirmation");

	return Backbone.View.extend({
		el: "#wpmudev-hustle",
		logShown: false,
		events: {
			"click .hustle-delete-module": "delete_module",
			"click .button-view-email-list": "view_email_list",
			"click .module-toggle-tracking-activity": "toggle_tracking_activity",
			"click .button-view-log-list": "view_error_log_list",
			"click #wpmudev-bulk-action-button": "apply_bulk_action",
			"change [name='wph-module-status']": "module_status_updated",
			"change #wph-all-slideins": "select_all",
		},
		initialize: function(){
			var self = this;
			
			var $item = $('.wpmudev-list .wpmudev-list--element'),
					totalItems = $item.length,
					itemCount  = totalItems;
			
			$item.each(function() {

					$(this).css('z-index', itemCount);
					itemCount--;

					var $dropdown = $(this).find('.wpmudev-dots-dropdown'),
							$button = $dropdown.find('.wpmudev-dots-button'),
							$droplist = $dropdown.find('.wpmudev-dots-nav');

					$button.on('click', function(){
							$(this).toggleClass('wpmudev-active');
							$droplist.toggleClass('wpmudev-hide');
							self.$('.wpmudev-dots-nav').not($droplist).each( function() {
								if ( !$(this).hasClass('wpmudev-hide') ) {
									$(this).toggleClass('wpmudev-hide');
								}
							});
					});

			});
			
			this.delete_confirmation = new Delete_Confirmation({
				action: 'hustle_delete_module',
				onSuccess: function(res){
					if ( res.success ) {
						location.reload();
					}
				}
			});
		},
		delete_module: function(e){
			var $this = $(e.target),
				id = $this.data('id'),
				nonce = $this.data('nonce'); 
			
			if ( this.delete_confirmation ) {
				this.delete_confirmation.opts.id = id;
				this.delete_confirmation.opts.nonce = nonce;
				this.delete_confirmation.$el.addClass('wpmudev-modal-active');
			}
		},
		view_email_list: function(e){
			e.preventDefault();
			e.stopPropagation();
			var $this = this.$(e.target),
					id = $this.data("id"),
					name = $this.data("name"),
					total = $this.data("total"),
					Modal_Email = Hustle.get("Modal_Email");

			// Get rid of old modal.
			if ( this.emailsShown ) {
				this.emailsShown.remove();
			}
			// Render modal.
			this.emailsShown = new Modal_Email({
				model: {
					id: id,
					total: total,
					name: name,
					type: 'slidein',
					module_fields: []
				}
			});
		},
		set_testmode_visibiliy: function( active_toggle, speed ) {
			if( typeof speed === 'undefined' ) speed = 400;
			var $this = active_toggle,
				data = $this.data() || {};

			var $test_mode_toggle = this.$('.wpoi-testmode-active-state[data-id="' + data.id + '"][data-type="' + data.type + '"]').closest(".test-mode");
			if( $this.is( ":checked" ) ){
				$test_mode_toggle.fadeOut( speed );
			} else {
				$test_mode_toggle.fadeIn( speed );
			}

		},
		toggle_tracking_activity: function(e){			
			var $this = $(e.target),
					id = $this.data("id"),
					nonce = $this.data("nonce"),
					type = $this.data("type");

			$.ajax({
					url: ajaxurl,
					type: "POST",
					data: {
							action: "hustle_slidein_module_toggle_tracking_activity",
							id: id,
							type: type,
							_ajax_nonce: nonce
					},
					complete: function(){
						location.reload();
					}
			});
		},
		view_error_log_list: function(e){
			var target = $(e.currentTarget),
				data = target.data(),
				id = data.id,
				name = data.name,
				type = 'slidein',
				Modal_Error = Hustle.get( 'Modal_Error' );

			// Get rid of old modal.
			if ( this.logShown ) {
				this.logShown.remove();
			}
			// Render modal.
			this.logShown = new Modal_Error({
				button: target,
				model: {
					name: name,
					id: id,
					type: type,
					total: data.total
				}
			});
		},
		module_status_updated: function(e) {
			var $this = this.$(e.target),
				value = $this.val(),
				data = $this.data(),
				$li = $this.closest('li.wpmudev-tabs-menu_item');
				
			$li.addClass('current');
			$li.siblings().removeClass('current');
				
			data._ajax_nonce = data.nonce;
			
			if ( value === 'test' ) {
				data.action = "hustle_slidein_toggle_test_activity";
			} else {
				data.action = "hustle_slidein_module_toggle_state";
				if ( value === 'off' ) {
					data.enabled = 'false';
				} else {
					data.enabled = 'true';
				}
			}
			
			$.post(ajaxurl, data,function(response){
				// nothing for now
			});
		},
		select_all: function(e) {
			var $this = $(e.target);
			
			if ( $this.is(':checked') ) {
				this.$('.wph-module-checkbox').prop( 'checked', true );
			} else {
				this.$('.wph-module-checkbox').prop( 'checked', false );
			}
		},
		apply_bulk_action: function(e) {
			var $this = $(e.target),
				action = this.$('select#wpmudev-bulk-action').val(),
				nonce = this.$('select#wpmudev-bulk-action option[value="'+action+'"]').data('nonce'),
				ids = [];
				
			if ( action === 'delete' ) {
				this.$('.wph-module-checkbox:checked').each( function() {
					ids.push( $(this).data('id') );
				});
				
				if ( !_.isEmpty( ids ) ) {
					if ( this.delete_confirmation ) {
						this.delete_confirmation.opts.ids = JSON.stringify(ids);
						this.delete_confirmation.opts.multiple = 1;
						this.delete_confirmation.opts.nonce = nonce;
						this.delete_confirmation.$el.addClass('wpmudev-modal-active');
					}
				}
			}
		}
	});
});
