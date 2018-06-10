Hustle.define("Embedded.Listing", function($){
	"use strict";
	var Delete_Confirmation = Hustle.get("Delete_Confirmation");

	return Backbone.View.extend({
		el: "#wpmudev-hustle",
		logShown: false,
		events: {
	        "click .wpmudev-row .wpmudev-box-head" : "toggle_module_accordion",
			"click .wpmudev-row .wpmudev-box-head .wpmudev-box-action" : "module_toggle_clicked",
			"click .hustle-delete-module": "delete_module",
			"click .module-active-state": "toggle_module_activity",
 			"click .button-view-email-list": "view_email_list",
			"click .button-view-log-list": "view_error_log_list",
			"change .module-toggle-tracking-activity": "toggle_tracking_activity",
			"change [name='wph-module-status']": "module_status_updated",
		},
		initialize: function(){
			var self = this;
			// Set visibility for test mode toggles at view load, with no animation
			this.$('.optin-type-active-state').each(function(){
				self.set_testmode_visibiliy( $(this), 0 );
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
		module_toggle_clicked: function(e) {
			e.stopPropagation();
			$(e.target).closest('.wpmudev-box-head').click();
		},
		toggle_module_accordion: function(e){
			if( _.indexOf( ['wpmudev-box-head', 'wpmudev-box-action', 'wpmudev-box-group', 'wpmudev-box-group--inner', 'wpmudev-group-title', 'wpmudev-helper'], e.target.className  ) === -1 ) return;
			
			var $this = $(e.target),
				$icon = $this.parents('.wpmudev-row').find(".wpmudev-box-action"),
				$body = $this.parents('.wpmudev-row').find(".wpmudev-box-body");
				
			$body.slideToggle( 'fast', function(){
				$icon.toggleClass("wpmudev-action-show");
				$body.toggleClass('wpmudev-hidden');
			} );
		},
		delete_module: function(e){			
			var $this = $(e.target).closest('a.hustle-delete-module'),
				id = $this.data('id'),
				nonce = $this.data('nonce'); 
			
			if ( this.delete_confirmation ) {
				this.delete_confirmation.opts.id = id;
				this.delete_confirmation.opts.nonce = nonce;
				this.delete_confirmation.$el.addClass('wpmudev-modal-active');
			}

		},
		toggle_module_activity: function(e){
			var $this = this.$(e.target),
					data = $this.data() || {},
					$row = $this.parents('.wpmudev-row')
			;

			data.action = "hustle_embedded_module_toggle_state";
			data._ajax_nonce = data.nonce;
			$this.prop("disabled", true);
			if ( $this.is(":checked") ) {
					// Show settings.
					$row.find(".wpmudev-box-body .wpmudev-box-disabled")
						.removeClass("wpmudev-box-disabled")
						.addClass("wpmudev-box-enabled")
					;
					// Enable inputs
					$row.find("input").prop("disabled", false);
			} else {
					// Hide settings.
					$row.find(".wpmudev-box-body .wpmudev-box-enabled")
						.removeClass("wpmudev-box-enabled")
						.addClass("wpmudev-box-disabled")
					;
					// Disable inputs
					$row.find("input").prop("disabled", true);
			}

			$.post(ajaxurl, data,function(response){
					$this.prop("disabled", false);

			});
		},
		view_email_list: function(e){
			e.preventDefault();
			e.stopPropagation();
			var $this = $(e.currentTarget).hasClass('wpmudev-button') ? $(e.currentTarget) : $(e.currentTarget).parents('.wpmudev-button'),
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
						type: 'embedded',
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
			e.stopPropagation();
			var $this = $(e.target),
				id = $this.data("id"),
				nonce = $this.data("nonce"),
				type = $this.data("type"),
				new_state = $this.is(":checked");

			$this.attr("disabled", true);

			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: {
					action: "hustle_embedded_module_toggle_tracking_activity",
					id: id,
					type: type,
					_ajax_nonce: nonce
				},
				complete: function(){
					$this.attr("disabled", false);
				},
				success: function( res ){
					if( !res.success )
						$this.attr("checked", !new_state);
				},
				error: function(res){
					if( !res.success )
						$this.attr("checked", !new_state);
				}
			});
		},
		view_error_log_list: function(e){
			var target = $(e.currentTarget),
				data = target.data(),
				id = data.id,
				name = data.name,
				type = 'embedded',
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
				data.action = "hustle_embedded_toggle_test_activity";
			} else {
				data.action = "hustle_embedded_module_toggle_type_state";
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
	});
});
