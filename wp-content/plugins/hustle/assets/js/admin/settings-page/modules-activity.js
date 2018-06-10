Hustle.define("Settings.Modules_Activity", function($){
	"use strict";
	return Backbone.View.extend({
		el: "#wpmudev-settings-activity",
		events: {
			"change .hustle-for-admin-user-toggle": "toggle_for_user",
			"change .hustle-for-logged-in-user-toggle": "toggle_for_user"
		},
		initialize: function(){

		},
		toggle_for_user: function(e){
			var $this = this.$( e.target ),
				id = $this.data("id"),
				nonce = $this.data("nonce"),
				user = $this.data("user");

			$this.attr("disabled", true);
			$.ajax( {
				url: ajaxurl,
				type: "POST",
				data:  {
					action: "hustle_toggle_module_for_user",
					id: id,
					_ajax_nonce: nonce,
					user_type: user
				},
				complete: function( res, res_status ){
					$this.attr("disabled", false);
				},
				success: function(res){
					if( !res.success )
						$this.prop("checked", !$this.is(":checked") );
				},
				error: function(){
					$this.prop("checked", !$this.is(":checked") );
				}

			});

		}
	});

});
