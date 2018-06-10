var Module = window.Module || {};

Hustle.define("Model", function() {
	"use strict";

	return Backbone.Model.extend({
		initialize: function() {
			this.on( 'change', this.user_has_change, this );
			Backbone.Model.prototype.initialize.apply( this, arguments );
			var attrs = this.attributes;
			this.display_preview_button( attrs );
		},
		user_has_change: function() {
			Module.hasChanges = true;
			var attrs = this.attributes;
			this.display_preview_button( attrs );
		},
		display_preview_button: function( attrs ) {
			var previewEl = $('.wpmudev-preview');
			if( attrs.has_title || '' !== attrs.main_content || attrs.use_feature_image || attrs.show_cta || attrs.use_email_collection ) {
				previewEl.show();
			} else {
				previewEl.hide();
			}
		}
	});
});

Hustle.define("Models.M", function(){
	"use strict";
	return Hustle.get("Model").extend({
			toJSON: function(){
				var json = _.clone(this.attributes);
				for(var attr in json) {
					if((json[attr] instanceof Backbone.Model) || (json[attr] instanceof Backbone.Collection)) {
						json[attr] = json[attr].toJSON();
					}
				}
				return json;
			},
			set: function(key, val, options){

				if( typeof key === "string" &&  key.indexOf(".") !== -1 ){
					var parent = key.split(".")[0],
						child = key.split(".")[1],
						parent_model = this.get(parent);

					if( parent_model && parent_model instanceof Backbone.Model ){
						parent_model.set(child, val, options);
						this.trigger("change:" + key, key, val, options);
						this.trigger("change:" + parent, key, val, options);
					}

				}else{
					Backbone.Model.prototype.set.call(this, key, val, options);
				}
			},
			get: function(key){
				if( typeof key === "string" &&  key.indexOf(".") !== -1 ){
					var parent = key.split(".")[0],
						child = key.split(".")[1];
					return this.get(parent).get(child);
				}else{
					return Backbone.Model.prototype.get.call(this, key);
				}
			}
		});
});

Hustle.define("Models.Trigger", function(){
	"use strict";
   return  Hustle.get("Model").extend({
	   defaults:{
		   trigger: "time", // time | scroll | click | exit_intent | adblock
		   on_time: false,
		   on_time_delay: 5,
		   on_time_unit: "seconds",
		   on_scroll: "scrolled", // scrolled | selector
		   on_scroll_page_percent: "20",
		   on_scroll_css_selector: "",
		   on_click_element: "",
		   on_exit_intent: true,
		   on_exit_intent_per_session: true,
		   on_exit_intent_delayed: false,
		   on_exit_intent_delayed_time: 5,
		   on_exit_intent_delayed_unit: "seconds",
		   on_adblock: false,
		   on_adblock_delayed: false,
		   on_adblock_delayed_time: 5,
		   on_adblock_delayed_unit: "seconds"
	   }
   });
});

Module.Model  = Hustle.get("Models.M").extend({
	defaults: {
		module_name: '',
		module_type: 'popup',
		active: 1,
		test_mode: 0
	}
});
