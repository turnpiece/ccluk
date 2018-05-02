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
	var pluginName = "forminatorFrontCondition",
		defaults = {
			fields: {},
			relations: {}
		};

	// The actual plugin constructor
	function ForminatorFrontCondition(element, options) {
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
	$.extend(ForminatorFrontCondition.prototype, {
		init: function () {
			var self = this;
			this.add_missing_relations();
			this.$el.find('.forminator-field input, .forminator-field select').change(function (e) {
				var $element = $(this),
					element_id = $element.closest('.forminator-col').attr('id');
				if (typeof element_id === 'undefined') {
					element_id = $element.attr('id');
				}

				//lookup condition of fields
				if (!self.has_relations(element_id)) return false;

				// Check if the field has any relations
				var relations = self.get_relations(element_id);

				// Loop all relations the field have
				relations.forEach(function (relation) {
					var logic = self.get_field_logic(relation),
						action = logic.action,
						rule = logic.rule,
						conditions = logic.conditions, // Conditions rules
						matches = 0 // Number of matches
					;

					conditions.forEach(function (condition) {
						// If rule is applicable save in matches
						if (self.is_applicable_rule(condition)) {
							matches++;
						}
					});

					if ((rule === "all" && matches === conditions.length) || (rule === "any" && matches > 0)) {
						self.toggle_field(relation, action, "valid");
					} else {
						self.toggle_field(relation, action, "invalid");
					}
				});
			});

			// Simulate change
			this.$el.find('.forminator-field input, .forminator-field select').change();
			this.init_events();
		},

		/**
		 * Register related events
		 *
		 * @since 1.0.3
		 */
		init_events: function () {
			var self = this;
			this.$el.on('forminator.front.condition.restart', function (e) {
				self.on_restart(e);
			});
		},

		/**
		 * Restart conditions
		 *
		 * @since 1.0.3
		 *
		 * @param e
		 */
		on_restart: function (e) {
			// restart condition
			this.$el.find('.forminator-field input, .forminator-field select').change();
		},

		/**
		 * Add missing relations based on fields.conditions
		 */
		add_missing_relations: function () {
			var self = this;
			var missedRelations = {};
			if (typeof this.settings.fields !== "undefined") {
				var conditionsFields = this.settings.fields;
				Object.keys(conditionsFields).forEach(function (key) {
					var conditions = conditionsFields[key]['conditions'];
					conditions.forEach(function (condition) {
						var relatedField = condition.field;
						if (!self.has_relations(relatedField)) {
							if (typeof missedRelations[relatedField] === 'undefined') {
								missedRelations[relatedField] = [];
							}
							missedRelations[relatedField].push(key);

						}
					});
				});
			}
			Object.keys(missedRelations).forEach(function (relatedField) {
				self.settings.relations[relatedField] = missedRelations[relatedField];
			});
		},

		get_field_logic: function (element_id) {
			if (typeof this.settings.fields[element_id] === "undefined") return [];
			return this.settings.fields[element_id];
		},

		has_relations: function (element_id) {
			return typeof this.settings.relations[element_id] !== "undefined";
		},

		get_relations: function (element_id) {
			if (!this.has_relations(element_id)) return [];

			return this.settings.relations[element_id];
		},

		get_field_value: function (element_id) {
			var $element = this.get_form_field(element_id),
				value = $element.val();

			//check the type of input
			if (this.field_is_radio($element)) {
				value = $element.filter(":checked").val();
			} else if (this.field_is_checkbox($element)) {
				value = [];
				$element.each(function () {
					if ($(this).is(':checked')) {
						value.push($(this).val().toLowerCase());
					}
				});
			}

			if (!value) return "";

			return value;
		},

		field_is_radio: function ($element) {
			var is_radio = false;
			$element.each(function () {
				if ($(this).attr('type') === 'radio') {
					is_radio = true;
					//break
					return false;
				}
			});

			return is_radio;
		},

		field_is_checkbox: function ($element) {
			var is_checkbox = false;
			$element.each(function () {
				if ($(this).attr('type') === 'checkbox') {
					is_checkbox = true;
					//break
					return false;
				}
			});

			return is_checkbox;
		},

		get_form_field: function (element_id) {
			//find element by suffix -field on id input (default behavior)
			var $element = this.$el.find('#' + element_id + '-field');
			if ($element.length === 0) {
				//find element by its on name (for radio on singlevalue)
				$element = this.$el.find('input[name=' + element_id + ']');
				if ($element.length === 0) {
					// for text area that have uniqid, so we check its name instead
					$element = this.$el.find('textarea[name=' + element_id + ']');
					if ($element.length === 0) {
						//find element by its on name[] (for checkbox on multivalue)
						$element = this.$el.find('input[name="' + element_id + '[]"]');
						if ($element.length === 0) {
							//find element by direct id (for name field mostly)
							//will work for all field with element_id-[somestring]
							$element = this.$el.find('#' + element_id);
						}
					}
				}
			}

			return $element;
		},

		is_numeric: function (number) {
			return !isNaN(parseFloat(number)) && isFinite(number);
		},

		is_applicable_rule: function (condition) {
			if (typeof condition === "undefined") return false;

			var value1 = this.get_field_value(condition.field),
				value2 = condition.value,
				operator = condition.operator
			;

			return this.is_matching(value1, value2, operator);
		},

		is_matching: function (value1, value2, operator) {
			// Match values case
			var isArrayValue = Array.isArray(value1);
			if (!isArrayValue) {
				value1 = value1 ? value1.toLowerCase() : '';
			}

			value2 = value2 ? value2.toLowerCase() : '';

			switch (operator) {
				case "is":
					if (!isArrayValue) {
						return value1 === value2;
					} else {
						return $.inArray(value2, value1) > -1;
					}
				case "is_not":
					if (!isArrayValue) {
						return value1 !== value2;
					} else {
						return $.inArray(value2, value1) === -1;
					}
				case "is_great":
					// typecasting to integer, with return `NaN` when its literal chars, so `is_numeric` will fail
					value1 = +value1;
					value2 = +value2;
					return this.is_numeric(value1) && this.is_numeric(value2) ? value1 > value2 : false;
				case "is_less":
					value1 = +value1;
					value2 = +value2;
					return this.is_numeric(value1) && this.is_numeric(value2) ? value1 < value2 : false;
				case "contains":
					return this.contains(value1, value2);
				case "starts":
					return value1.startsWith(value2);
				case "ends":
					return value1.endsWith(value2);
			}

			// Return false if above are not valid
			return false;
		},

		contains: function (field_value, value) {
			return field_value.toLowerCase().indexOf(value) >= 0;
		},

		toggle_field: function (element_id, action, type) {
			var $element_id = this.get_form_field(element_id),
				$column_field = $element_id.closest('.forminator-col'),
				$hidden_upload = $column_field.find('.forminator-input-file-required'),
				$hidden_wp_editor = $column_field.find('.forminator-wp-editor-required');
			
			// Handle show action
			if (action === "show") {
				if (type === "valid") {
					$column_field.removeClass('forminator-hidden');
					if ($hidden_upload.length > 0) {
						$hidden_upload.addClass('do-validate');
					}
					if ($hidden_wp_editor.length > 0) {
						$hidden_wp_editor.addClass('do-validate');
					}
				} else {
					$column_field.addClass('forminator-hidden');
					if ($hidden_upload.length > 0) {
						$hidden_upload.removeClass('do-validate');
					}
					if ($hidden_wp_editor.length > 0) {
						$hidden_wp_editor.removeClass('do-validate');
					}
				}
			}

			// Handle hide action
			if (action === "hide") {
				if (type === "valid") {
					$column_field.addClass('forminator-hidden');
					if ($hidden_upload.length > 0) {
						$hidden_upload.removeClass('do-validate');
					}
					if ($hidden_wp_editor.length > 0) {
						$hidden_wp_editor.removeClass('do-validate');
					}
				} else {
					$column_field.removeClass('forminator-hidden');
					if ($hidden_upload.length > 0) {
						$hidden_upload.addClass('do-validate');
					}
					if ($hidden_wp_editor.length > 0) {
						$hidden_wp_editor.addClass('do-validate');
					}
				}
			}
		}
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFrontCondition(this, options));
			}
		});
	};

})(jQuery, window, document);