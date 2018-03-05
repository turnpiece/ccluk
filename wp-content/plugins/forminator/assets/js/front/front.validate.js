// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;// noinspection JSUnusedLocalSymbols
(function ($, window, document, undefined) {

	"use strict";

	// undefined is used here as the undefined global variable in ECMAScript 3 is
	// mutable (ie. it can be changed by someone else). undefined isn't really being
	// passed in so we can ensure the value of it is truly undefined. In ES5, undefined
	// can no longer be modified.

	// window and document are passed through as local variables rather than global
	// as this (slightly) quickens the resolution process and can be more efficiently
	// minified (especially when both are regularly referenced in your plugin).

	// Create the defaults once
	var pluginName = "forminatorFrontValidate",
		defaults = {
			rules: {},
			messages: {}
		};

	// The actual plugin constructor
	function ForminatorFrontValidate(element, options) {
		this.element = element;
		this.$el = $(this.element);

		// jQuery has an extend method which merges the contents of two or
		// more objects, storing the result in the first object. The first object
		// is generally empty as we don't want to alter the default options for
		// future instances of the plugin
		this.settings = $.extend({}, defaults, options);
		this._defaults = defaults;
		this._name = pluginName;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend(ForminatorFrontValidate.prototype, {
		init: function () {
			var self = this;

			$(this.element).validate({
				// add support for wp_editor when its required
				ignore: ":hidden:not(.forminator-wp-editor-required, .forminator-input-file-required)",
				errorPlacement: function (error, element) {
				},		
				onfocusout: function (element) {
					//datepicker will be validated when its closed
					if ($(element).hasClass('hasDatepicker') === false) {
						$(element).valid();
					}
				},
				highlight: function (element, errorClass, message) {
					var errorMessage = this.errorMap[element.name],
						$field_holder = $(element).closest('.forminator-field--inner');

					if ($field_holder.length === 0) {
						$field_holder = $(element).closest('.forminator-field');
					}

					var $error_holder = $field_holder.find('.forminator-label--validation');

					if ($error_holder.length === 0) {
						$field_holder.append('<label class="forminator-label--validation"></label>');
						$error_holder = $field_holder.find('.forminator-label--validation');
					}
					$(element).attr('aria-invalid', 'true');
					$error_holder.text(errorMessage);
					$field_holder.addClass('forminator-has_error');
				},

				unhighlight: function (element, errorClass, validClass) {
					var $field_holder = $(element).closest('.forminator-field--inner');

					if ($field_holder.length === 0) {
						$field_holder = $(element).closest('.forminator-field');
					}

					var $error_holder = $field_holder.find('.forminator-label--validation');

					$(element).removeAttr('aria-invalid');
					$error_holder.remove();
					$field_holder.removeClass('forminator-has_error');
				},
				rules: self.settings.rules,
				messages: self.settings.messages
			});

		}
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFrontValidate(this, options));
			}
		});
	};
	
	$.validator.addMethod("maxwords", function (value, element, param) {
		return this.optional(element) || jQuery.trim(value).split(/\s+/).length <= param;
	});
	$.validator.addMethod("emailWP", function (value, element, param) {
		if (this.optional(element)) {
			return true;
		}

		// Test for the minimum length the email can be
		if (value.length < 6) {
			return false;
		}

		// Test for an @ character after the first position
		if (value.indexOf('@', 1) < 0) {
			return false;
		}

		// Split out the local and domain parts
		var parts = value.split('@', 2);

		// LOCAL PART
		// Test for invalid characters
		if (!parts[0].match(/^[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~\.-]+$/)) {
			return false;
		}

		// DOMAIN PART
		// Test for sequences of periods
		if (parts[1].match(/\.{2,}/)) {
			return false;
		}

		var domain = parts[1];
		// Split the domain into subs
		var subs = domain.split('.');
		if (subs.length < 2) {
			return false;
		}

		var subsLen = subs.length;
		for (var i = 0; i < subsLen; i++) {
			// Test for invalid characters
			if (!subs[i].match(/^[a-z0-9-]+$/i)) {
				return false;
			}
		}

		return true;
	});

})(jQuery, window, document);


