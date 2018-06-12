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
		this.next_button = window.ForminatorFront.cform.pagination_next;
		this.prev_button = window.ForminatorFront.cform.pagination_prev;
		this.form_id = 0;

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

			if (this.$el.find('input[name=form_id]').length > 0) {
				this.form_id = this.$el.find('input[name=form_id]').val();
			}

			if (this.form_id && typeof window.Forminator_Cform_Paginations === 'object' && typeof window.Forminator_Cform_Paginations[this.form_id] === 'object') {
				if (window.Forminator_Cform_Paginations[this.form_id]['pagination-footer-button']) {
					this.prev_button = window.Forminator_Cform_Paginations[this.form_id]['pagination-footer-button-text'];
				}
				if (window.Forminator_Cform_Paginations[this.form_id]['pagination-right-button']) {
					this.next_button = window.Forminator_Cform_Paginations[this.form_id]['pagination-right-button-text'];
				}
			}

			this.totalSteps = this.settings.totalSteps;
			this.step = this.settings.step;

			if (this.settings.hashStep && this.step > 0) {
				this.go_to(this.step);
			} else {
				this.go_to(0);
			}

			this.render_navigation();
			this.render_bar_navigation();
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

			this.$el.on('reset', function (e) {
				self.on_form_reset(e);
			});

			this.$el.on('forminator.front.pagination.focus.input', function (e, input) {
				self.on_focus_input(e, input);
			});

		},

		/**
		 * On reset event of Form
		 *
		 * @since 1.0.3
		 *
		 * @param e
		 */
		on_form_reset: function (e) {
			// Trigger pagination to first page
			this.go_to(0);
			this.update_buttons();
		},

		/**
		 * On Input focused
		 *
		 * @param e
		 * @param input
		 */
		on_focus_input: function (e, input) {
			//Go to page where element exist
			var step = this.get_page_of_input(input);
			this.go_to(step);
			this.update_buttons();
		},

		render_footer_navigation: function() {
			if ( this.$el.hasClass('forminator-design--material') ) {

				this.$el.append( '<div class="forminator-pagination--footer">' +
					'<button class="forminator-button forminator-pagination-prev"><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">' + this.prev_button + '</span></button>' +
					'<button class="forminator-button forminator-pagination-next"><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">' + this.next_button + '</span></button>' +
				'</div>' );

			} else {

				this.$el.append( '<div class="forminator-pagination--footer">' +
					'<button class="forminator-button forminator-pagination-prev">' + this.prev_button + '</button>' +
					'<button class="forminator-button forminator-pagination-next">' +this.next_button + '</button>' +
				'</div>' );

			}

		},

		render_bar_navigation: function () {
			var $navigation = this.$el.find('.forminator-pagination--bar');

			if (!$navigation.length) return;

			$navigation.html( '<div class="forminator-bar--text">0%</div>' +
			'<div class="forminator-bar--progress">' +
				'<span style="width: 0%"></span>' +
			'</div>' +
			'<div class="forminator-bar--text">100%</div>' );

			this.calculate_bar_percentage();
		},

		calculate_bar_percentage: function () {
			var total     = this.totalSteps,
				current   = this.step,
				$progress = this.$el
			;

			if ( !$progress.length ) return;

			var percentage = Math.round( (current / total) * 100 );

			//$progress.find( '.forminator-bar--text' ).html( percentage + '%' );
			$progress.find( '.forminator-bar--progress span' ).css( 'width', percentage + '%' );
		},

		render_navigation: function () {
			var $navigation = this.$el.find('.forminator-pagination--nav');

			if (!$navigation.length) return;

			var steps = this.$el.find('.forminator-pagination').not('.forminator-pagination-start');

			var finalSteps = this.$el.find('.forminator-pagination-start');

			if ( this.$el.hasClass('forminator-design--material') ) {

				steps.each(function () {
					var $step = $(this),
						label = $step.data('label'),
						step = $step.data('step') - 1
					;

					$navigation.append('<li class="forminator-nav-step forminator-nav-step-' + step + '">' +
						'<span class="forminator-step-text">' + label + '</span>' +
						'</li>'
					);
				});

				finalSteps.each(function () {
					var $step = $(this),
						label = $step.data('label'),
						step = steps.length
					;

					$navigation.append('<li class="forminator-nav-step forminator-nav-step-' + step + '">' +
						'<span class="forminator-step-text">' + label + '</span>' +
						'</li>'
					);
				});

			} else {

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

			}
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
						if (errors === 0) {
							// focus on first error
							element.focus();
						}
						errors++;
					}
				});

			return errors === 0;
		},

		/**
		 * Get page on the input
		 *
		 * @since 1.0.3
		 *
		 * @param input
		 * @returns {number|*}
		 */
		get_page_of_input: function(input) {
			var step_page = this.step;
			var page = $(input).closest('.forminator-pagination');
			if (page.length > 0) {
				var step = $(page).data('step');
				if (typeof step !== 'undefined') {
					step_page = +step;
				}
			}

			return step_page;
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
				if ( this.$el.hasClass('forminator-design--material') ) {
					this.$el.find('.forminator-pagination-next .forminator-button--text').html(submit_button_text);
				} else {
					this.$el.find('.forminator-pagination-next').html(submit_button_text);
				}

			} else {
				// noinspection Annotator
				this.$el.find('.forminator-pagination-next .forminator-button--text').html(this.next_button);
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

			this.calculate_bar_percentage();
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
