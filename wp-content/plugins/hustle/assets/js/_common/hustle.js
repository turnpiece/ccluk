(function( $ ) {
	"use strict";

	/**
	 * Defines the Hustle Object
	 *
	 * @type {{define, get_modules, get, modules}}
	 */
	window.Hustle = (function ($, doc, win) {
		var _modules = {},
			_template_options = {
				evaluate:    /<#([\s\S]+?)#>/g,
				interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
				escape:      /\{\{([^\}]+?)\}\}(?!\})/g
			};

		var define = function (module_name, module) {
				var splits = module_name.split(".");
				if (splits.length) { // if module_name has more than one object name, then add the module definition recursively
					var recursive = function (module_name, modules) {
						var arr = module_name.split("."),
							_module_name = arr.splice(0, 1)[0];

						if (!_module_name) return;


						if (!arr.length) {
							var invoked = module.call(null, $, doc, win);
							modules[_module_name] = _.isFunction(invoked) || typeof invoked === "undefined" ? invoked : _.extend(modules[_module_name] || {}, invoked);
						} else {
							modules[_module_name] = modules[_module_name] || {};
						}

						if (arr.length && _module_name)
							recursive(arr.join("."), modules[_module_name]);
					};

					recursive(module_name, _modules);
				} else {
					var m = _modules[module_name] || {};
					_modules[module_name] = _.extend(m, module.call(null, $, doc, win));
				}
			},
			get_modules = function () {
				return _modules;
			},
			get = function (module_name) {
				if (module_name.split(".").length) { // recursively fetch the module
					var module = false,
						recursive = function (module_name, modules) {
							var arr = module_name.split("."),
								_module_name = arr.splice(0, 1)[0];

							module = modules[_module_name];

							if (arr.length)
								recursive(arr.join("."), modules[_module_name]);
						};

					recursive(module_name, _modules);
					return module;
				}

				return _modules[module_name] || false;
			},
			Events = _.extend({}, Backbone.Events),
			View = Backbone.View.extend({
				__base_events:{
				  "click .wph-tabs--wrap .wph-tabs--nav li label": "__base_toggle_tab"
				},
				initialize: function () {
					this.events = _.extend({}, this.events, this.__base_events);

					if (_.isFunction(this.init_mix))
						this.init_mix.apply(this, arguments);



					if (this.render) {
						this.render = _.wrap(this.render, function (render) {
							this.trigger("before_render");
							render.call(this);
							Events.trigger("view.rendered", this);
							this.trigger("rendered");
						});
					}

					if (_.isFunction(this.init))
						this.init.apply(this, arguments);
				},
				__base_toggle_tab: function(e){
					var $this = this.$( e.target ),
						href = ( $this.attr("href") || "" ).replace(/^\#/, ""),
						$content = href ? this.$( "#" + href ) : false,
						$wrap = $this.closest(".wph-tabs--wrap"),
						$li = $this.closest("li");

					if( $content && $content.length ){
						$wrap.find( ".wph-tabs--content" ).not( $content ).removeClass("current");
						$content.addClass("current");
						$li.addClass("current");
						$li.siblings().removeClass("current");
					}
				}
			}),
			template = _.memoize(function ( id ) {
				var compiled;
				return function ( data ) {
					compiled = compiled || _.template( document.getElementById( id ).innerHTML, null, _template_options );
					return compiled( data ).replace("/*<![CDATA[*/", "").replace("/*]]>*/", "");
				};
			}),
			create_template = _.memoize(function( str ){
				var cache;
				return function(data){
					cache = cache || _.template( str, null, _template_options );
					return cache( data );
				};
			}),
			get_template_options = function(){
				return $.extend(  true, {}, _template_options );
			},
			cookie = (function(){
				// Get a cookie value.
				var get = function (name) {
					var i, c, cookie_name, value,
						ca = document.cookie.split(';');


					cookie_name = name + "=";

					for (i = 0; i < ca.length; i += 1) {
						c = ca[i];
						while (c.charAt(0) === ' ') {
							c = c.substring(1, c.length);
						}
						if (c.indexOf(cookie_name) === 0) {
							var _val = c.substring(cookie_name.length, c.length);
							return !_val ? _val : JSON.parse(_val);
						}
					}
					return null;
				};

				// Saves the value into a cookie.
				var set = function (name, value, days) {
					var date, expires;

					value = $.isArray(value) || $.isPlainObject(value) ? JSON.stringify(value) : value;

					if (!isNaN(days)) {
						date = new Date();
						date.setTime(date.getTime() + ( days * 24 * 60 * 60 * 1000 ));
						expires = "; expires=" + date.toGMTString();
					} else {
						expires = "";
					}

					document.cookie = name + "=" + value + expires + "; path=/";
				};
				return {
					set: set,
					get: get
				};
			}()),
			consts = (function(){
				return {
					Module_Show_Count: "hustle_module_show_count-"
				};
			}());

		return {
			define: define,
			get_modules: get_modules,
			get: get,
			Events: Events,
			View: View,
			template: template,
			create_template: create_template,
			get_template_options: get_template_options,
			cookie: cookie,
			consts: consts
		};
	}(jQuery, document, window) );

}(jQuery));
