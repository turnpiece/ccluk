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
	var pluginName = "forminatorFrontPagination",
		defaults = {
			totalSteps: 0,
			step: 0,
			hashStep: 0,
			inline_validation: false
		};

	// The actual plugin constructor
	function ForminatorFrontPagination(element, options) {
		this.element = $(element);
		this.$el = this.element;
		this.totalSteps = 0;
		this.step = 0;
		this.hashStep = false;

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
	$.extend(ForminatorFrontPagination.prototype, {
		init: function () {
			this.totalSteps = this.settings.totalSteps;
			this.step = this.settings.step;

			if (this.settings.hashStep && this.step > 0) {
				this.go_to(this.step);
			} else {
				this.go_to(0);
			}

			this.render_navigation();
			this.render_footer_navigation();
			this.init_events();
			this.update_buttons();
			this.update_navigation();

		},
		init_events: function () {
			var self = this;

			this.$el.find('.forminator-pagination-prev').click(function (e) {
				e.preventDefault();
				self.handle_click('prev');
			});
			this.$el.find('.forminator-pagination-next').click(function (e) {
				e.preventDefault();
				self.handle_click('next');
			});
		},

		render_footer_navigation: function () {
			// noinspection Annotator
			this.$el.append('<div class="forminator-pagination--footer">' +
				'<button class="forminator-button forminator-pagination-prev"><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">' + window.ForminatorFront.cform.pagination_prev + '</span></button>' +
				'<button class="forminator-button forminator-pagination-next"><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">' + window.ForminatorFront.cform.pagination_next + '</span></button>' +
				'</div>');
		},

		render_navigation: function () {
			var $navigation = this.$el.find('.forminator-pagination--nav');

			if (!$navigation.length) return;

			var steps = this.$el.find('.forminator-pagination').not('.forminator-pagination-start');

			steps.each(function () {
				var $step = $(this),
					label = $step.data('label'),
					step = $step.data('step') - 1
				;

				$navigation.append('<li class="forminator-nav-step forminator-nav-step-' + step + '">' +
					'<span class="forminator-step-text">' + label + '</span>' +
					'<span class="forminator-step-dot" aria-label="hidden"></span>' +
					'</li>'
				);
			});

			var finalSteps = this.$el.find('.forminator-pagination-start');

			finalSteps.each(function () {
				var $step = $(this),
					label = $step.data('label'),
					step = steps.length
				;

				$navigation.append('<li class="forminator-nav-step forminator-nav-step-' + step + '">' +
					'<span class="forminator-step-text">' + label + '</span>' +
					'<span class="forminator-step-dot" aria-label="hidden"></span>' +
					'</li>'
				);
			});
		},

		handle_click: function (type) {
			if (type === "prev" && this.step !== 0) {
				this.go_to(this.step - 1);
			} else if (type === "next") {
				//do validation before next if inline validation enabled
				if (this.settings.inline_validation) {
					if (!this.is_step_inputs_valid()) {
						return;
					}
				}

				this.go_to(this.step + 1);
			}

			this.update_buttons();
		},

		/**
		 * Check current inputs on step is in valid state
		 */
		is_step_inputs_valid: function () {
			var valid = true,
				errors = 0,
				validator = this.$el.data('validator'),
				page = this.$el.find('[data-step=' + this.step + ']');

			//inline validation disabled
			if (typeof validator === 'undefined') {
				return true;
			}

			//get fields on current page
			page.find("input, select, textarea, [contenteditable]")
				.not(":submit, :reset, :image, :disabled")
				.not(':hidden:not(.forminator-wp-editor-required, .forminator-input-file-required)')
				.not('[gramm="true"]')
				.each(function (key, element) {
					valid = validator.element(element);
					if (!valid) {
						errors++;
					}
				});

			return errors === 0;
		},

		update_buttons: function () {
			if (this.step === 0) {
				this.$el.find('.forminator-pagination-prev').attr('disabled', true);
			} else {
				this.$el.find('.forminator-pagination-prev').removeAttr('disabled');
			}

			if (this.step === this.totalSteps) {
				//keep pagination content on last step before submit
				this.step--;
				this.$el.submit();
			}

			if (this.step === (this.totalSteps - 1)) {
				var submit_button_text = this.$el.find('.forminator-pagination-submit').html();
				this.$el.find('.forminator-pagination-next .forminator-button--text').html(submit_button_text);
			} else {
				// noinspection Annotator
				this.$el.find('.forminator-pagination-next .forminator-button--text').html(window.ForminatorFront.cform.pagination_next);
			}
		},

		go_to: function (step) {
			this.step = step;

			if (step === this.totalSteps) return false;

			// Hide all parts
			this.$el.find('.forminator-pagination').hide();

			// Show desired page
			this.$el.find('[data-step=' + step + ']').show();

			//exec responsive captcha
			var forminatorFront = this.$el.data('forminatorFront');
			if (typeof forminatorFront !== 'undefined') {
				forminatorFront.responsive_captcha();
			}

			this.update_navigation();
		},

		update_navigation: function () {
			// Update navigation
			this.$el.find('.forminator-step-current').removeClass('forminator-step-current');
			this.$el.find('.forminator-nav-step-' + this.step).addClass('forminator-step-current');
		}
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFrontPagination(this, options));
			}
		});
	};

})(jQuery, window, document);