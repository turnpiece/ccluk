Hustle.define("SShare.Listing", function($, doc, win){
	"use strict";
	var Delete_Confirmation = Hustle.get("Delete_Confirmation");
	return Backbone.View.extend({
		el: "#wpmudev-hustle",
		events: {
			"click .wpmudev-row .wpmudev-box-head" : "toggle_module_accordion",
			"click .wpmudev-row .wpmudev-box-head .wpmudev-box-action" : "module_toggle_clicked",
			"click .social-sharing-edit": "edit",
			"click .hustle-delete-module": "delete_module",
			"change .social-sharing-toggle-activity": "toggle_module_activity",
			"change .social-sharing-toggle-tracking-activity": "toggle_tracking_activity",
			"change [name='wph-module-status']": "module_status_updated",
		},
		delete_confirmations: {},
		initialize: function(){
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
		toggle_module_accordion: function(e) {			
			if( _.indexOf( ['wpmudev-box-head', 'wpmudev-box-action', 'wpmudev-box-group', 'wpmudev-box-group--inner', 'wpmudev-group-title', 'wpmudev-helper'], e.target.className  ) === -1 ) return;
			
			var $this = $(e.target),
				$icon = $this.parents('.wpmudev-row').find(".wpmudev-box-action"),
				$body = $this.parents('.wpmudev-row').find(".wpmudev-box-body");
				
			$body.slideToggle( 'fast', function(){
				$icon.toggleClass("wpmudev-action-show");
				$body.toggleClass('wpmudev-hidden');
			} );
			

		},
		toggle_module_activity: function(e){
			e.stopPropagation();
			var $this = $(e.target),
				id = $this.data("id"),
				nonce = $this.data("nonce"),
				new_state = $this.is(":checked"),
				$row = $this.parents('.wpmudev-row')
			;

			$this.prop("disabled", true);

			if ( new_state ) {
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

			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: {
					action: "hustle_sshare_module_toggle_state",
					id: id,
					_ajax_nonce: nonce
				},
				complete: function(){
					$this.prop("disabled", false);
				},
				success: function( res ){
					if( !res.success )
						$this.attr("checked", !new_state);
				},
				error: function(){
					$this.attr("checked", !new_state);
				}
			});
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
					action: "hustle_sshare_toggle_tracking_activity",
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
		toggle_type_activity: function(e){
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
					action: "hustle_social_sharing_toggle_type_activity",
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
		edit: function(e){
			e.stopPropagation();
		},
		delete_module: function(e) {
			var $this = $(e.target).closest('a.hustle-delete-module'),
				id = $this.data('id'),
				nonce = $this.data('nonce'); 
			
			if ( this.delete_confirmation ) {
				this.delete_confirmation.opts.id = id;
				this.delete_confirmation.opts.nonce = nonce;
				this.delete_confirmation.$el.addClass('wpmudev-modal-active');
			}

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
				data.action = "hustle_sshare_toggle_test_activity";
			} else {
				data.action = "hustle_sshare_module_toggle_type_state";
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
