Hustle.define("Settings.Display_Triggers_View", function( $, doc, win ){
	"use strict";

	return Hustle.View.extend(_.extend({}, Hustle.get("Mixins.Model_Updater"), {
		template: Optin.template("wpoi-settings-display-triggers-tpl"),
		events: {
			'click .tabs-header label': "change_tab"
		},
		init: function( opts ){
			this.type = opts.type;
			this.listenTo( this.model, "change:on_adblock",  this.hide_adblock_options_on_toggle);
			this.render();
		},
		render: function(){
			this.$el.html( this.template( this.get_data() ) );
			this.hide_adblock_options_on_toggle();
			return this;
		},
		get_data: function(){
			var data = {};
			data.type = this.type;
			return _.extend( {}, data, this.model.toJSON() );
		},
		change_tab: function(event){
			event.preventDefault();
			var $this = this.$(event.target),
				$this_tab = $this.parent("li"),
				$this_content = this.$( $this.attr("href")),
				$radio = $this.find("input[type='radio']");
			this.$(".tabs-header li").removeClass("current");
			this.$(".tabs-content").removeClass("current");

			$this_tab.addClass("current");
			$this_content.addClass("current");
			$radio.prop("checked", true);

			this.model.set( "trigger",  $radio.val() );
		},
		hide_adblock_options_on_toggle: function(){
			if( _.isTrue( this.model.get( "on_adblock" ) ) ){
				this.$(".wpoi-popup-trigger-on-adblock-option").show();
			}else{
				this.$(".wpoi-popup-trigger-on-adblock-option").hide();
			}
		}
	}) );

});
