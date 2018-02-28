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
	var pluginName = "forminatorFront",
		defaults = {
			form_type: 'custom-form',
			rules: {},
			messages: {},
			conditions: {},
			inline_validation: false
		};

	// The actual plugin constructor
	function ForminatorFront(element, options) {
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
	$.extend(ForminatorFront.prototype, {
		init: function () {
			var self = this;

			//init submit
			$(this.element).forminatorFrontSubmit({form_type: self.settings.form_type});

			//selective activation based on type of form
			switch (this.settings.form_type) {
				case  'custom-form':
					this.init_custom_form();
					break;
				case  'poll':
					this.init_poll_form();
					break;
				case  'quiz':
					this.init_quiz_form();
					break;

			}


			// TODO: confirm usage on form type
			// Handle field activation classes
			this.activate_field();
			// Handle special classes for material design
			this.material_field();

		},
		init_custom_form: function () {
			var self = this;
			//initiate validator
			if (this.settings.inline_validation) {
				$(this.element).forminatorFrontValidate({
					rules: self.settings.rules,
					messages: self.settings.messages
				});
			}

			//initiate pagination
			this.init_pagination();

			//initiate condition
			$(this.element).forminatorFrontCondition(this.settings.conditions);

			//initiate datepicker
			$(this.element).find('.forminator-datepicker').forminatorFrontDatePicker();

			//initiate select2
			this.init_select2();

			//responsive captcha
			this.responsive_captcha();

			// Handle field counter
			this.field_counter();

			// Handle number input
			this.field_number();
			$(window).on('resize', function () {
				self.responsive_captcha();
			});

			// Handle upload field change
			this.upload_field();

		},
		init_poll_form: function () {
			var self = this,
				$selection = this.$el.find('.forminator-radio--field'),
				$input = this.$el.find('.forminator-input');

			if (this.$el.hasClass('forminator-poll-disabled')) {
				this.$el.find('.forminator-radio--field').each(function () {
					$(this).attr('disabled', true);
				});
			}

			$selection.on('click', function () {
				$input.hide();
				$input.attr('name', '');
				var checked = this.checked,
					$id = $(this).attr('id'),
					$name = $(this).attr('name');
				if (self.$el.find('.forminator-input#' + $id + '-extra').length) {
					var $extra = self.$el.find('.forminator-input#' + $id + '-extra');
					if (checked) {
						$extra.attr('name', $name + '-extra');
						$extra.show();
					} else {
						$extra.hide();
					}
				}
				return true;
			});

		},
		init_quiz_form: function () {
			var self = this;

			this.$el.find('.forminator-button').each(function () {
				$(this).prop("disabled", true);
			});

			this.$el.find('.forminator-answer--input').each(function () {
				$(this).attr('checked', false);
			});

			this.$el.find('.forminator-result--header button').on('click', function () {
				location.reload();
			});

			this.$el.find('.forminator-submit-rightaway').click(function () {
				self.$el.submit();
				$(this).closest('.forminator-question--answers').find('.forminator-submit-rightaway').addClass('forminator-has-been-disabled').attr('disabled', 'disabled');
			});

			var social_shares = {
				'facebook': 'https://www.facebook.com/sharer/sharer.php?u=' + window.location.href,
				'twitter': 'https://twitter.com/intent/tweet?url=' + window.location.href,
				'google': 'https://plus.google.com/share?url=' + window.location.href,
				'linkedin': 'https://www.linkedin.com/shareArticle?mini=true&url=' + window.location.href
			};

			this.$el.on('click', '.forminator-share--icon a', function (e) {
				e.preventDefault();
				var social = $(this).data('social');
				if (social_shares[social] !== undefined) {
					var newwindow = window.open(social_shares[social], social, 'height=' + $(window).height() + ',width=' + $(window).width());
					if (window.focus) {
						newwindow.focus();
					}
					return false;
				}
			});

			this.$el.on('change', '.forminator-answer--input', function (e) {
				var count = 0,
					amount_answers = self.$el.find('.forminator-question--answers').length;

				self.$el.find('.forminator-answer--input').each(function () {
					if ($(this).prop('checked')) {
						count++;
					}

					if (count === amount_answers) {
						self.$el.find('.forminator-button').each(function () {
							$(this).prop("disabled", false);
						});
					}
				});

			});

		},
		init_select2: function () {
			$(this.element).find(".forminator-select").wpmuiSelect({
				allowClear: false,
				containerCssClass: "forminator-select2",
				dropdownCssClass: "forminator-dropdown"
			});
		},
		responsive_captcha: function () {
			$(this.element).find('.forminator-g-recaptcha').each(function () {
				if ($(this).is(':visible')) {
					var width = $(this).parent().width(),
						scale = 1;
					if (width < 302) {
						scale = width / 302;
					}
					$(this).css('transform', 'scale(' + scale + ')');
					$(this).css('-webkit-transform', 'scale(' + scale + ')');
					$(this).css('transform-origin', '0 0');
					$(this).css('-webkit-transform-origin', '0 0');
				}
			});
		},
		init_pagination: function () {
			var self = this,
				num_pages = $(this.element).find(".forminator-pagination").length,
				hash = window.location.hash,
				hashStep = false,
				step = 0;

			if (num_pages > 0) {
				//find from hash
				if (typeof hash !== "undefined" && hash.indexOf('step-') >= 0) {
					hashStep = true;
					step = hash.substr(6, 8);
				}

				$(this.element).forminatorFrontPagination({
					totalSteps: num_pages,
					hashStep: hashStep,
					step: step,
					inline_validation: self.settings.inline_validation
				});
			}
		},
		show_messages: function (errors) {
			var self = this;
			errors.forEach(function (value) {
				var element_id = Object.keys(value),
					message = Object.values(value),
					$field = $(self.element).find('#' + element_id);

				if ($field.length) {
					if (!$field.hasClass('forminator-input') && !$field.hasClass('forminator-select') && !$field.hasClass('forminator-button')) {
						$field = $field.find('.forminator-field');
					} else {
						$field = $field.closest('.forminator-field--inner');
					}

					$field = $field.find('.forminator-field');
					$field.addClass('forminator-has_error');

					var $validation = $field.find('.forminator-label--validation');

					if ($validation.length === 0) {
						$field.append('<label class="forminator-label--validation"></label>');
						$validation = $field.find('.forminator-label--validation');
					}

					$validation.html(message);

				}
			});

			return this;
		},
		focus_to_element: function ($element) {
			$('html,body').animate({scrollTop: ($element.offset().top - ($(window).height() - $element.outerHeight(true)) / 2)}, 500, function () {
				if (!$element.attr("tabindex")) {
					$element.attr("tabindex", -1).focus();
				}

			});

		},
		activate_field: function () {
			var form = $(this.element);

			form.find('.forminator-input, .forminator-textarea').each(function () {

				var $ftype = $(this);

				// Set field active class on hover
				$ftype.mouseover(function (e) {
					e.stopPropagation();
					$(this).closest('.forminator-field').addClass('forminator-is_hover');

				}).mouseout(function (e) {
					e.stopPropagation();
					$(this).closest('.forminator-field').removeClass('forminator-is_hover');

				});

				// Set field active class on focus
				$ftype.focus(function (e) {
					e.stopPropagation();
					$(this).closest('.forminator-field').addClass('forminator-is_active');

				}).blur(function (e) {
					e.stopPropagation();
					$(this).closest('.forminator-field').removeClass('forminator-is_active');

				});

				// Set field filled class on change
				$ftype.change(function (e) {
					e.stopPropagation();

					if ($(this).val() !== "") {
						$(this).closest('.forminator-field').addClass('forminator-is_filled');
					} else {
						$(this).closest('.forminator-field').removeClass('forminator-is_filled');
					}

					if ($(this).val() !== "" && $(this).find('forminator-label--validation').text() !== "") {
						$(this).find('.forminator-label--validation').remove();
						$(this).find('.forminator-field').removeClass('forminator-has_error');
					}
				});

			});

			form.find('.forminator-select + .select2').each(function () {

				var $select = $(this);

				// Set field active class on hover
				$select.mouseover(function (e) {
					e.stopPropagation();
					$(this).closest('.forminator-field').addClass('forminator-is_hover');

				}).mouseout(function (e) {
					e.stopPropagation();
					$(this).closest('.forminator-field').removeClass('forminator-is_hover');

				});

				// Set field active class on focus
				$select.on('click', function (e) {
					e.stopPropagation();
					checkSelectActive();
					if ($select.hasClass('select2-container--open')) {
						$(this).closest('.forminator-field').addClass('forminator-is_active');
					} else {
						$(this).closest('.forminator-field').removeClass('forminator-is_active');
					}

				});


			});

			function checkSelectActive() {
				if (form.find('.select2-container').hasClass('select2-container--open')) {
					setTimeout(checkSelectActive, 300);
				} else {
					form.find('.select2-container').closest('.forminator-field').removeClass('forminator-is_active');
				}
			}
		},

		field_counter: function () {
			var form = $(this.element);
			form.find('.forminator-input, .forminator-textarea').each(function () {
				var $input = $(this),
					count = 0;

				$input.on('change keyup', function (e) {
					e.stopPropagation();
					var $field = $(this).closest('.forminator-field'),
						$limit = $field.find('.forminator-field--helper .forminator-label--limit')
					;

					if ($limit.length) {
						if ($limit.data('limit')) {
							if ($limit.data('type') === "characters") {
								count = $(this).val().length;
							} else {
								count = $.trim($(this).val()).split(/\s+/).length;
							}
							$limit.html(count + ' / ' + $limit.data('limit'));
						}
					}
				});

			});

		},

		field_number: function () {
			var form = $(this.element);
			form.find('input[type=number]').on('change keyup', function () {
				var sanitized = $(this).val().replace(/[^0-9]/g, '');
				$(this).val(sanitized);
			});

		},

		material_field: function () {
			var form = $(this.element);
			if (form.is('.forminator-design--material')) {
				var $input = form.find('.forminator-input'),
					$textarea = form.find('.forminator-textarea'),
					$date = form.find('.forminator-date'),
					$product = form.find('.forminator-product');

				var $navigation = form.find('.forminator-pagination--nav'),
					$navitem = $navigation.find('li');

				$('<span class="forminator-nav-border"></span>').insertAfter($navitem);

				$input.prev('.forminator-field--label').addClass('forminator-floating--input');
				$textarea.prev('.forminator-field--label').addClass('forminator-floating--textarea');

				if ($date.hasClass('forminator-has_icon')) {
					$date.prev('.forminator-field--label').addClass('forminator-floating--date');
				} else {
					$date.prev('.forminator-field--label').addClass('forminator-floating--input');
				}

				$product.closest('.forminator-field').addClass('forminator-product--material');

				$input.wrap('<div class="forminator-input--wrap"></div>');
			}


		},

		toggle_file_input: function () {
			var form = $(this.element);
			form.find(".forminator-upload").each(function () {
				var $self = $(this),
					$input = $self.find(".forminator-input"),
					$remove = $self.find(".forminator-upload--remove")
				;

				// Toggle remove button depend on input value
				if ($input.val() !== "") {
					// Show remove button
					$remove.show();
				} else {
					// Hide remove button
					$remove.hide();
				}
			});
		},

		upload_field: function () {
			var self = this,
				form = $(this.element);
			// Toggle file remove button
			this.toggle_file_input();

			// Handle remove file button click
			form.find(".forminator-upload--remove").on('click', function (e) {
				e.preventDefault();

				var $self = $(this),
					$input = $self.siblings('.forminator-input'),
					$label = $self.siblings('.forminator-label')
				;

				// Cleanup
				$input.val("");
				$label.html("No file chosen");
				$self.hide();
			});

			form.find(".forminator-upload-button").on('click', function (e) {
				e.preventDefault();
				var $id = $(this).attr('data-id'),
					$target = form.find('input#' + $id),
					$nameLabel = form.find('label#' + $id);
				$target.trigger('click');
				$target.change(function () {
					var vals = $(this).val(),
						val = vals.length ? vals.split('\\').pop() : '';
					$nameLabel.html(val);

					self.toggle_file_input();
				});
			});
		},

	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFront(this, options));
			}
		});
	};

})(jQuery, window, document);

// noinspection JSUnusedGlobalSymbols
var forminator_render_captcha = function () {
	jQuery('.g-recaptcha').each(function () {
		var siteKey = jQuery(this).data('sitekey'),
			data = {
				sitekey: siteKey,
				theme: jQuery(this).data('theme')
			}
		;

		// Print reCaptcha scripts if sitekey exist
		if (siteKey !== "") {
			// noinspection Annotator
			grecaptcha.render(jQuery(this)[0], data);
		}
	});
};