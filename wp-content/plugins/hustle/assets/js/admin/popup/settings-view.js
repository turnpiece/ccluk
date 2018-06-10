Hustle.define("Pop_Up.Settings_View", function($, doc, win){
	"use strict";
	return Hustle.View.extend(_.extend({}, Hustle.get("Mixins.Model_Updater"), {
		template: Optin.template("wpmudev-hustle-popup-section-settings-tpl"),
		target_container: $('#wpmudev-hustle-box-section-settings'),
		init: function( opts ){
			this.conditions_view = opts.conditions_view;
			if ( this.target_container.length ) {
				return this.render();
			}
		},
		render: function(args){
			this.setElement( this.template( _.extend( {
				shortcode_id: optin_vars.current.shortcode_id
			}, this.model.toJSON() ) ) );
			return this;
		},
		after_render: function() {
			if ( this.target_container.length ) {
				this.$(".wph-conditions").replaceWith( this.conditions_view.$el );
			}
		}
	} ) );

});
