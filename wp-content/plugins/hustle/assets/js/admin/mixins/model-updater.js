Hustle.define("Mixins.Model_Updater", function($, doc, win) {
	"use strict";
	return {
		init_mix: function(){
			this.events = _.extend({}, this.events, this._events);
			this.delegateEvents();
		},
		_events:{
			"change input[type='text']" : "_update_text",
			"change input[type='number']" : "_update_text",
			"change input[type='checkbox']" : "_update_checkbox",
			"change input[type=radio]": "_update_radios",
			"change select": "_update_select"
		},
		_update_text: function(e){
			var $this = $(e.target),
				attr = $this.data("attribute"),
				model = this[$this.data("model") || "model"],
				opts = _.isTrue( $this.data("silent") ) ? {silent: true} : {};

			if( model && attr ){
				e.stopPropagation();
				model.set.call( model, attr, e.target.value, opts );
			}

		},
		_update_checkbox: function(e){
			var $this = $(e.target),
				attr = $this.data("attribute"),
				model = this[$this.data("model") || "model"],
				opts = _.isTrue( $this.data("silent") ) ? {silent: true} : {};


			if( model && attr ){
				e.stopPropagation();
				model.set.call( model, attr, $this.is(":checked") ? 1 : 0, opts );
			}

		},
		_update_radios: function(e){
			var $this = $(e.target),
				attribute = $this.data('attribute'),
				model = this[$this.data("model") || "model"],
				opts = _.isTrue( $this.data("silent") ) ? {silent: true} : {};


			if( model && attribute ){
				e.stopPropagation();
				model.set.call( model, attribute, e.target.value, opts );
			}


		},
		_update_select: function(e){
			var $this = $(e.target),
				attr = $this.data("attribute"),
				model = this[$this.data("model") || "model"],
				opts = _.isTrue( $this.data("silent") ) ? {silent: true} : {};


			if( model && attr ){
				e.stopPropagation();
				model.set.call( model, attr, $this.val(), opts );
			}

		}
	};
});
