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
	var pluginName = "forminatorFrontSubmit",
		defaults = {
			form_type: 'custom-form'
		};

	// The actual plugin constructor
	function ForminatorFrontSubmit(element, options) {
		this.element = element;
		this.$el = $(this.element);
		this.forminatorFront = null;


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
	$.extend(ForminatorFrontSubmit.prototype, {
		init: function () {
			this.forminatorFront = this.$el.data('forminatorFront');
			switch (this.settings.form_type) {
				case 'custom-form':
					this.handle_submit_custom_form();
					break;
				case 'quiz':
					this.handle_submit_quiz();
					break;
				case 'poll':
					this.handle_submit_poll();
					break;

			}
		},
		handle_submit_custom_form: function () {
			var self = this,
				form = $(this.element);

			form.on('submit', function (e) {
				var $this = $(this),
					formData = new FormData(this),
					$target_message = $this.find('.forminator-cform-response-message'),
					$captcha_field = $this.find('.forminator-g-recaptcha');
				if ($captcha_field.length) {
					var $captcha_response = grecaptcha.getResponse();
					$target_message.html('');
					if ($captcha_field.hasClass("error")) {
						$captcha_field.removeClass("error");
					}
					if ($captcha_response.length === 0) {
						if (!$captcha_field.hasClass("error")) {
							$captcha_field.addClass("error");
						}
						$target_message.html('<label class="forminator-label--error"><span>' + window.ForminatorFront.cform.captcha_error + '</span></label>');
						if(typeof self.forminatorFront !== 'undefined') {
							self.forminatorFront.focus_to_element($target_message);
						}

						return false;
					}
				}
				if (self.$el.hasClass('forminator_ajax')) {
					$target_message.html('');
					$target_message.html('<label class="forminator-label--info"><span>' + window.ForminatorFront.cform.processing + '</span></label>');
					if(typeof self.forminatorFront !== 'undefined') {
						self.forminatorFront.focus_to_element($target_message);
					}

					e.preventDefault();
					$.ajax({
						type: 'POST',
						url: window.ForminatorFront.ajaxUrl,
						data: formData,
						cache: false,
						contentType: false,
						processData: false,
						beforeSend: function () {
							$this.find('button').attr('disabled', true);
						},
						success: function (data) {
							// Hide validation errors
							$this.find('.forminator-label--validation').remove();
							$this.find('.forminator-field').removeClass('forminator-has_error');

							$this.find('button').removeAttr('disabled');
							$target_message.html('');

							var $label_class = data.success ? 'success' : 'error';
							if (typeof data.message !== "undefined") {
								$target_message.html('<label class="forminator-label--' + $label_class + '"><span>' + data.message + '</span></label>');
								if (typeof self.forminatorFront !== 'undefined') {
									self.forminatorFront.focus_to_element($target_message);
								}

							} else {
								if (typeof data.data !== "undefined") {
									$label_class = data.data.success ? 'success' : 'error';
									$target_message.html('<label class="forminator-label--' + $label_class + '"><span>' + data.data.message + '</span></label>');
									if (typeof self.forminatorFront !== 'undefined') {
										self.forminatorFront.focus_to_element($target_message);
									}

								}
							}

							if (!data.data.success && data.data.errors.length) {
								if (typeof self.forminatorFront !== 'undefined') {
									self.forminatorFront.show_messages(data.data.errors);
								}

							}

							if (data.success === true) {
								// Reset form
								if ($this[0]) {
									$this[0].reset();

									// Reset upload field
									$this.find(".forminator-upload--remove").hide();
									$this.find('.forminator-upload .forminator-input').val("");
									$this.find('.forminator-upload .forminator-label').html("No file chosen");
								}

								if (typeof data.data.url !== "undefined") {
									window.location.href = data.data.url;
								}
							}
						},
						error: function () {
							$this.find('button').removeAttr('disabled');
							$target_message.html('');
							$target_message.html('<label class="forminator-label--notice"><span>' + window.ForminatorFront.cform.error + '</span></label>');
							if (typeof self.forminatorFront !== 'undefined') {
								self.forminatorFront.focus_to_element($target_message);
							}
						}
					});
					return false;
				}
				return true;
			});
		},
		handle_submit_quiz: function () {
			var self = this;

			this.$el.on('submit', function (e) {
				var form = $(this),
					ajaxData = []
				;
				e.preventDefault();

				// Enable all inputs
				self.$el.find('.forminator-has-been-disabled').removeAttr('disabled');

				// Serialize fields, that should be placed here!
				ajaxData = form.serialize();

				// Disable inputs again
				self.$el.find('.forminator-has-been-disabled').attr('disabled', 'disabled');

				$.ajax({
					type: 'POST',
					url: window.ForminatorFront.ajaxUrl,
					data: ajaxData,
					beforeSend: function () {
						self.$el.find('button').attr('disabled', 'disabled')
					},
					success: function (data) {
						if (data.success) {
							if (data.data.type === 'nowrong') {
								self.$el.find('.quiz-form-button-holder').html(data.data.result)
							} else if (data.data.type === 'knowledge') {
								if (self.$el.find('.quiz-form-button-holder').size() > 0) {
									self.$el.find('.quiz-form-button-holder').html(data.data.finalText);
								}
								Object.keys(data.data.result).forEach(function (key) {
									var parent = self.$el.find('#' + key);
									parent.find('.forminator-question--result').text(data.data.result[key].message);
									parent.find('.forminator-submit-rightaway').attr('disabled', 'disabled');

									var answerClass,
										$answer = self.$el.find('[id|="' + data.data.result[key].answer + '"]'),
										$container = $answer.closest('.forminator-answer')
									;

									if (data.data.result[key].isCorrect) {
										answerClass = 'forminator-is_correct';
									} else {
										answerClass = 'forminator-is_incorrect';
									}
									$container.addClass(answerClass);
								});
							}
						} else {
							self.$el.find('button').removeAttr('disabled');
						}
					}
				});
				return false;
			});

			$('body').on('click', '.forminator-result--header button', function () {
				location.reload();
			});
		},

		handle_submit_poll: function () {
			var self = this;

			this.$el.on('submit', function (e) {
				var $this = $(this);
				var $target_message = self.$el.find('.forminator-poll-response-message');
				if (self.$el.hasClass('forminator_ajax')) {
					$target_message.html('');
					$target_message.html('<label class="forminator-label--info"><span>' + window.ForminatorFront.poll.processing + '</span></label>');
					if (typeof self.forminatorFront !== 'undefined') {
						self.forminatorFront.focus_to_element($target_message);
					}

					e.preventDefault();
					$.ajax({
						type: 'POST',
						url: window.ForminatorFront.ajaxUrl,
						data: self.$el.serialize(),
						beforeSend: function () {
							self.$el.find('button').attr('disabled', true);
						},
						success: function (data) {
							self.$el.find('button').removeAttr('disabled');
							$target_message.html('');
							var $label_class = data.success ? 'success' : 'error';
							if (data.success === false) {
								$target_message.html('<label class="forminator-label--' + $label_class + '"><span>' + data.data + '</span></label>');
								if (typeof self.forminatorFront !== 'undefined') {
									self.forminatorFront.focus_to_element($target_message);
								}

							} else {
								if (typeof data.data !== "undefined") {
									$label_class = data.data.success ? 'success' : 'error';
									$target_message.html('<label class="forminator-label--' + $label_class + '"><span>' + data.data.message + '</span></label>');
									if(typeof self.forminatorFront !== 'undefined') {
										self.forminatorFront.focus_to_element($target_message);
									}

								}
							}

							if (data.success === true) {
								if (typeof data.data.url !== "undefined") {
									window.location.href = data.data.url;
								}
							}
						},
						error: function () {
							self.$el.find('button').removeAttr('disabled');
							$target_message.html('');
							$target_message.html('<label class="forminator-label--notice"><span>' + window.ForminatorFront.poll.error + '</span></label>');
							if(typeof self.forminatorFront !== 'undefined') {
								self.forminatorFront.focus_to_element($target_message);
							}

						}
					});
					return false;
				}
				return true;
			});
		},

		focus_to_element: function ($element) {
			$('html,body').animate({scrollTop: ($element.offset().top - ($(window).height() - $element.outerHeight(true)) / 2)}, 500, function () {
				if (!$element.attr("tabindex")) {
					$element.attr("tabindex", -1).focus();
				}
			});
		},
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFrontSubmit(this, options));
			}
		});
	};

})(jQuery, window, document);