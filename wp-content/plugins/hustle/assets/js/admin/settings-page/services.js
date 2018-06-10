Hustle.define("Settings.Services", function($){
	"use strict";
   return Backbone.View.extend({
	   el: "#providers-edit-box",
	   Modal_View: Hustle.get( "Settings.Services_Edit_Modal" ),
	   modal: false,
	   events: {
		   "click .wph-providers-edit": "open_edit_modal"
	   },
	   open_edit_modal: function( e ){
		   var $this = this.$(e.target),
			   id = $this.data("id"),
			   source = $this.data("source"),
			   nonce = $this.data("nonce");

		   this.modal = new this.Modal_View({ model: new Backbone.Model( {id: id, nonce: nonce, source: source} ) });


	   }

   });
});
