Hustle.define("Settings.Services_Edit_Modal", function($){
	"use strict";
   return Backbone.View.extend({
	   template: Hustle.template("wph-edit-provider-modal-tpl"),
	   id: "wph-edit-provider-modal",
	   events: {
			"click .i-close": "close",
			"click .js-wph-button-cancel": "close",
			"change #wph-provider-edit-modal-provider": "get_provider_options",
			"click #wph-edit-service-save": "save_settings",
			"submit form": "save_settings"
	   },
	   provider_options_nonce: false,
	   initialize: function(){

		   this.render();
	   },
	   render: function(){
		   var self = this;
		   this.$el.html( this.template() ).appendTo("body");

			$.ajax({
				url: ajaxurl,
				type: "get",
				data: {
					action: "hustle_get_providers_edit_modal_content",
					id: self.model.get("id"),
					source: self.model.get("source"),
					_ajax_nonce: self.model.get("nonce")
				},
				success: function(res){
					if( res.success ){
						self.$(".wph-edit-provider-modal-content").html( res.data.html );
						self.provider_options_nonce = res.data.provider_options_nonce;
						//self.delegateEvents();
						Hustle.Events.trigger("view.rendered", self);
					}
				}
			});

	   },
	   get_provider_options: function(e){
		 var self = this,
			 $this = this.$(e.target),
			 $details_placeholder = this.$("#optin_new_provider_account_details"),
			 $options_placeholder =  this.$("#optin_new_provider_account_options");

		   $details_placeholder.empty();
		   $options_placeholder.empty();
		 $.ajax({
			 url: ajaxurl,
			 type: "get",
			 data:{
				 action: "render_provider_account_options",
				 provider_id: $this.val(),
				 _ajax_nonce: self.provider_options_nonce,
				 optin: self.model.get("id")
			 },
			 success: function(res){
				if( res.success ){
					$details_placeholder.html( res.data );
					Hustle.Events.trigger("view.rendered", self);
				}

			 }
		 });
	   },
	   close: function(e){
		  e.preventDefault();
		  this.remove();
	   },
	   hide: function(){
	   },
	   show: function(){
	   },
	   save_settings: function(e){
		   e.preventDefault();
		   var $this = this.$(e.target),
			   nonce = $this.data("nonce"),
			   $selector = this.$("#wph-provider-edit-modal-provider"),
			   $form = $this.closest("form");

		   $.ajax({
			   url: ajaxurl,
			   type: "post",
			   data:{
				   action: "hustle_save_providers_edit_modal",
				   provider_id: $selector.val(),
				   _ajax_nonce: nonce,
				   id: this.model.get("id"),
				   source: this.model.get("source"),
				   form: $form.serialize()
			   },
			   success: function(res){
			   }
		   });
	   }
   });
});
