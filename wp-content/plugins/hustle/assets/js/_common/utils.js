var  Optin = Optin || {};

Optin.View = {};
Optin.Models = {};
Optin.Events = {};

if( typeof Backbone !== "undefined")
	_.extend(Optin.Events, Backbone.Events);
(function( $ ) {
	"use strict";
	Optin.COOKIE_PREFIX = "inc_optin_long_hidden-";
	Optin.POPUP_COOKIE_PREFIX = "inc_optin_popup_long_hidden-";
	Optin.SLIDE_IN_COOKIE_PREFIX = "inc_optin_slide_in_long_hidden-";
	Optin.SLIDE_IN_COOKIE_HIDE_ALL = "inc_optin_slide_in_hide_all";
	
	Optin.global_mixin = function() {
		_.mixin({
			/**
			 * Logs to console
			 */
			log: function(){
			  console.log( arguments );
			},
			/**
			 * Converts val to boolian
			 *
			 * @param val
			 * @returns {*}
			 */
			toBool: function(val){
				if( _.isBoolean(val) )
					return val;

				if( _.isString( val ) && ["true", "false", "1"].indexOf( val.toLowerCase() ) !== -1 ){
					return val.toLowerCase() === "true" || val.toLowerCase() === "1" ? true : false;
				}

				if( _.isNumber( val ) )
					return !!val;

				if(_.isUndefined( val ) || _.isNull(val) || _.isNaN( val )  )
					return false;

				return val;
			},
			/**
			 * Checks if val is truthy
			 *
			 * @param val
			 * @returns {boolean}
			 */
			isTrue: function(val) {
				if( _.isUndefined( val ) || _.isNull( val ) || _.isNaN( val ) )
					return false;

				if( _.isNumber( val ) )
					return val !== 0;

				val = val.toString().toLowerCase();
				return ['1', "true", "on"].indexOf( val ) !== -1;
			},
			isFalse: function(val){
			  return !_.isTrue( val );
			},
			control_base: function(checked, current, attribute){
				attribute = _.isUndefined( attribute ) ? "checked" : attribute;
				checked  = _.toBool(checked);
				current = _.isBoolean( checked ) ? _.isTrue( current ) : current;

				if(_.isEqual(checked, current )){
					return  attribute + '=' + attribute;
				}
				return "";
			},
			/**
			 * Returns checked=check if checked variable is equal to current state
			 *
			 *
			 * @param checked checked state
			 * @param current current state
			 * @returns {*}
			 */
			checked: function(checked, current){
				return _.control_base( checked, current, "checked" );
			},
			/**
			 * Adds selected attribute
			 *
			 * @param selected
			 * @param current
			 * @returns {*}
			 */
			selected: function(selected, current){
				return _.control_base( selected, current, "selected" );
			},
			/**
			 * Adds disabled attribute
			 *
			 * @param disabled
			 * @param current
			 * @returns {*}
			 */
			disabled: function( disabled, current ){
				return _.control_base( disabled, current, "disabled" );
			},
			/**
			 * Returns css class based on the passed in condition
			 *
			 * @param conditon
			 * @param cls
			 * @param negating_cls
			 * @returns {*}
			 */
			class: function( conditon, cls, negating_cls ){

				if( _.isTrue( conditon ) )
					return cls;

				return typeof negating_cls !== "undefined" ? negating_cls : "";
			},
			/**
			 * Returns class attribute with relevant class name
			 *
			 * @param conditon
			 * @param cls
			 * @param negating_cls
			 * @returns {string}
			 */
			add_class: function( conditon, cls, negating_cls ){
				return 'class={class}'.replace( "{class}",  _.class( conditon, cls, negating_cls ) );
			},
			toUpperCase: function(str){
				return  _.isString( str ) ? str.toUpperCase() : "";
			}
		});

		if( !_.findKey ) {
			_.mixin({
				findKey: function(obj, predicate, context) {
					predicate = cb(predicate, context);
					var keys = _.keys(obj), key;
					for (var i = 0, length = keys.length; i < length; i++) {
						key = keys[i];
						if (predicate(obj[key], key, obj)) return key;
					}
				}
			});
		}
	};
	
	Optin.global_mixin();
	
	
	/**
	 * Recursive toJSON
	 *
	 * @returns {*}
	 */
	Backbone.Model.prototype.toJSON = function() {
		var json = _.clone(this.attributes);
		for(var attr in json) {
			if((json[attr] instanceof Backbone.Model) || (Backbone.Collection && json[attr] instanceof Backbone.Collection)) {
				json[attr] = json[attr].toJSON();
			}
		}
		return json;
	};


	String.prototype.toInt = function(){
		return parseInt(this, 10);
	};

	String.prototype.isEmpty = function() {
		return (this.length === 0 || !this.trim());
	};

	Optin.template = _.memoize(function ( id ) {
		var compiled,

			options = {
				evaluate:    /<#([\s\S]+?)#>/g,
				interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
				escape:      /\{\{([^\}]+?)\}\}(?!\})/g
			};

		return function ( data ) {
			compiled = compiled || _.template( $( '#' + id ).html(), null, options );
			return compiled( data ).replace("/*<![CDATA[*/", "").replace("/*]]>*/", "");
		};
	});
	
	/**
	 * Compatibility with other plugin/theme e.g. upfront
	 *
	 */
	Optin.template_compat = _.memoize(function ( id ) {
		var compiled;

		return function ( data ) {
			compiled = compiled || _.template( $( '#' + id ).html() );
			return compiled( data ).replace("/*<![CDATA[*/", "").replace("/*]]>*/", "");
		};
	});

	Optin.cookie = Hustle.cookie;

	$(document).on('blur', 'input, textarea, select', function(){
	    var $this = $(this);
	    if($this.is(':input[type=button], :input[type=submit], :input[type=reset]')) return;
	    if( $this.val() && $this.val().trim && $this.val().trim() !== '' ) {
		    $this.parent().addClass('hustle-input-filled');
		} else{
			$this.parent().removeClass('hustle-input-filled');
		}
	});

	Optin.Mixins = {
		_mixins: {},
		_services_mixins: {},
		_desing_mixins: {},
		_display_mixins: {},
		add: function(id, obj){
			this._mixins[id] = obj;
		},
		get_mixins: function(){
			return this._mixins;
		},
		add_services_mixin: function( id, obj ){
			this._services_mixins[id] = obj;
		},
		get_services_mixins: function(){
			return this._services_mixins;
		}
	};


})( jQuery );
