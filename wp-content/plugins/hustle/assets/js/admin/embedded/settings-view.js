Hustle.define("Embedded.Settings_View", function($, doc, win){
	"use strict";
	return Hustle.View.extend(_.extend({}, Hustle.get("Mixins.Model_Updater"), {
		template: Optin.template("wpmudev-hustle-embedded-section-settings-tpl"),
		target_container: $('#wpmudev-hustle-box-section-settings'),
		init: function( opts ){
			this.conditions_view = opts.conditions_view;
			if ( this.target_container.length ) {
				return this.render();
			}
		},
		render: function(args){
			this.setElement( this.template( _.extend( {}, this.model.toJSON() ) ) );
			return this;
		},
		after_render: function() {
			if ( this.target_container.length ) {
				this.$(".wph-conditions").replaceWith( this.conditions_view.$el );
			}
		}
	} ) );

});
