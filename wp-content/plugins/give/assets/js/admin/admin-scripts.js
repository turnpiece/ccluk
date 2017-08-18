/*!
 * Give Admin JS
 *
 * @description: The Give Admin scripts
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

jQuery.noConflict();
(function ($) {

	/**
	 * Setup Admin Datepicker
	 * @since: 1.0
	 */
	var enable_admin_datepicker = function () {
		// Date picker
		if ($('.give_datepicker').length > 0) {
			var dateFormat = 'mm/dd/yy';
			$('.give_datepicker').datepicker({
				dateFormat: dateFormat
			});
		}
	};

	/**
	 * Setup Pretty Chosen Select Fields
	 */
	var setup_chosen_give_selects = function () {
		// Setup Chosen Selects.
		var $give_chosen_containers = $('.give-select-chosen');

		// Add loader with each input field.
		$give_chosen_containers.on('chosen:ready', function () {
			$(this).next('.chosen-container')
				.find('input.chosen-search-input')
				.after('<span class="spinner"></span>');
		});

		// No results returned from search trigger.
		$give_chosen_containers.on('chosen:no_results', function () {
			var $container     = $(this).next('.chosen-container'),
				$no_results_li = $container.find('li.no-results'),
				error_string   = '';

			if ($container.hasClass('give-select-chosen-ajax') && $no_results_li.length) {
				error_string = give_vars.chosen.ajax_search_msg.replace('{search_term}', '"' + $('input', $container).val() + '"');
			} else {
				error_string = give_vars.chosen.no_results_msg.replace('{search_term}', '"' + $('input', $container).val() + '"');
			}

			$no_results_li.html(error_string);

		});

		// Initiate chosen.
		$give_chosen_containers.chosen({
			inherit_select_classes   : true,
			placeholder_text_single  : give_vars.one_option,
			placeholder_text_multiple: give_vars.one_or_more_option,
		});

		// This fixes the Chosen box being 0px wide when the thickbox is opened
		$('#post').on('click', '.give-thickbox', function () {
			$('.give-select-chosen', '#choose-give-form').css('width', '100%');
		});

		// Variables for setting up the typing timer.
		var typingTimer;               // Timer identifier
		var doneTypingInterval = 342;  // Time in ms, Slow - 521ms, Moderate - 342ms, Fast - 300ms

		// Replace options with search results
		$(document.body).on('keyup', '.give-select.chosen-container .chosen-search input, .give-select.chosen-container .search-field input', function (e) {

			var val           = $(this).val(),
				$container    = $(this).closest('.give-select-chosen'),
				select        = $container.prev(),
				$search_field = $container.find('input[type="text"]'),
				variations    = $container.hasClass('variations'),
				lastKey       = e.which,
				search_type   = 'give_forms_search';

			// Detect if we have a defined search type, otherwise default to donation forms.
			if ($container.prev().data('search-type')) {

				// Don't trigger AJAX if this select has all options loaded.
				if ('no_ajax' === select.data('search-type')) {
					return;
				}

				search_type = 'give_' + select.data('search-type') + '_search';
			}

			// Don't fire if short or is a modifier key (shift, ctrl, apple command key, or arrow keys).
			if (
				val.length <= 3 ||
				!search_type.length ||
				(
					(lastKey === 9) || // Tab
					(lastKey === 13) || // Enter
					(lastKey === 16) || // Shift
					(lastKey === 17) || // Ctrl
					(lastKey === 18) || // Alt
					(lastKey === 19) || // Pause, Break
					(lastKey === 20) || // CapsLock
					(lastKey === 27) || // Esc
					(lastKey === 33) || // Page Up
					(lastKey === 34) || // Page Down
					(lastKey === 35) || // End
					(lastKey === 36) || // Home
					(lastKey === 37) || // Left arrow
					(lastKey === 38) || // Up arrow
					(lastKey === 39) || // Right arrow
					(lastKey === 40) || // Down arrow
					(lastKey === 44) || // PrntScrn
					(lastKey === 45) || // Insert
					(lastKey === 144) || // NumLock
					(lastKey === 145) || // ScrollLock
					(lastKey === 91) || // WIN Key (Start)
					(lastKey === 93) || // WIN Menu
					(lastKey === 224) || // command key
					(lastKey >= 112 && lastKey <= 123) // F1 to F12lastKey
				)
			) {
				return;
			}
			clearTimeout(typingTimer);
			$container.addClass('give-select-chosen-ajax');

			typingTimer = setTimeout(
				function () {
					$.ajax({
						type      : 'GET',
						url       : ajaxurl,
						data      : {
							action: search_type,
							s     : val
						},
						dataType  : 'json',
						beforeSend: function () {
							select.closest('ul.chosen-results').empty();
							$search_field.prop('disabled', true);
						},
						success   : function (data) {

							$container.removeClass('give-select-chosen-ajax');

							// Remove all options but those that are selected.
							$('option:not(:selected)', select).remove();

							if (data.length) {
								$.each(data, function (key, item) {
									// Add any option that doesn't already exist.
									if (!$('option[value="' + item.id + '"]', select).length) {
										select.prepend('<option value="' + item.id + '">' + item.name + '</option>');
									}
								});

								// Trigger update event.
								$container.prev('select.give-select-chosen').trigger('chosen:updated');

							} else {

								// Trigger no result message event.
								$container.prev('select.give-select-chosen').trigger('chosen:no_results');
							}

							// Ensure the original query is retained within the search input.
							$search_field.prop('disabled', false);
							$search_field.val(val).focus();

						}
					}).fail(function (response) {
						if (window.console && window.console.log) {
							console.log(response);
						}
					}).done(function (response) {
						$search_field.prop('disabled', false);
					});
				},
				doneTypingInterval
			);
		});

		$('.give-select-chosen .chosen-search input').each(function () {
			var type        = $(this).parent().parent().parent().prev('select.give-select-chosen').data('search-type');
			var placeholder = '';

			if ('form' === type) {
				placeholder = give_vars.search_placeholder;
			} else {
				type = 'search_placeholder_' + type;
				if (give_vars[type]) {
					placeholder = give_vars[type];
				}
			}
			$(this).attr('placeholder', placeholder);

		});

	};

	/**
	 * Unformat Currency
	 *
	 * @use string give_vars.currency_decimals Number of decimals
	 *
	 * @param   {string}      price Price
	 * @param   {number|bool} dp    Number of decimals
	 *
	 * @returns {string}
	 */
	function give_unformat_currency(price, dp) {
		price = accounting.unformat(price, give_vars.decimal_separator).toString();
		dp    = ( 'undefined' == dp ? false : dp );

		// Set default value for number of decimals.
		if (false !== dp) {
			price = parseFloat(price).toFixed(dp);

			// If price do not have decimal value then set default number of decimals.
		} else {
			price = parseFloat(price).toFixed(give_vars.currency_decimals);
		}

		return price;
	}

	/**
	 * List donation screen JS
	 */

	var Give_List_Donation = {

		init: function () {
			this.delete_single_donation();
			this.resend_single_donation_receipt();
		},

		delete_single_donation: function () {
			$('body').on('click', '.delete-single-donation', function (e) {
				return confirm(give_vars.delete_payment);
			});
		},

		resend_single_donation_receipt: function () {
			$('body').on('click', '.resend-single-donation-receipt', function (e) {
				return confirm(give_vars.resend_receipt);
			});
		}

	};

	/**
	 * Edit donation screen JS
	 */
	var Give_Edit_Donation = {

		init: function () {
			this.edit_address();
			this.add_note();
			this.remove_note();
			this.new_donor();
			this.resend_receipt();
			this.variable_price_list();
		},

		edit_address: function () {

			// Update base state field based on selected base country
			$('select[name="give-payment-address[0][country]"]').change(function () {
				var $this = $(this);

				data = {
					action    : 'give_get_states',
					country   : $this.val(),
					field_name: 'give-payment-address[0][state]'
				};
				$.post(ajaxurl, data, function (response) {

					var state_wrap = $('#give-order-address-state-wrap');

					state_wrap.find('*').not('.order-data-address-line').remove();

					if (typeof ( response.states_found ) != undefined && true == response.states_found) {
						state_wrap.append(response.data);
						state_wrap.find('select').chosen();
					} else {
						state_wrap.append('<input type="text" name="give-payment-address[0][state]" value="" class="give-edit-toggles medium-text"/>');
					}
				});

				return false;
			});

		},

		add_note: function () {

			$('#give-add-payment-note').on('click', function (e) {
				e.preventDefault();
				var postData = {
					action    : 'give_insert_payment_note',
					payment_id: $(this).data('payment-id'),
					note      : $('#give-payment-note').val()
				};

				if (postData.note) {

					$.ajax({
						type   : 'POST',
						data   : postData,
						url    : ajaxurl,
						success: function (response) {
							$('#give-payment-notes-inner').append(response);
							$('.give-no-payment-notes').hide();
							$('#give-payment-note').val('');
						}
					}).fail(function (data) {
						if (window.console && window.console.log) {
							console.log(data);
						}
					});

				} else {
					var border_color = $('#give-payment-note').css('border-color');
					$('#give-payment-note').css('border-color', 'red');
					setTimeout(function () {
						$('#give-payment-note').css('border-color', border_color);
					}, 500);
				}

			});

		},

		remove_note: function () {

			$('body').on('click', '.give-delete-payment-note', function (e) {

				e.preventDefault();

				if (confirm(give_vars.delete_payment_note)) {

					var postData = {
						action    : 'give_delete_payment_note',
						payment_id: $(this).data('payment-id'),
						note_id   : $(this).data('note-id')
					};

					$.ajax({
						type   : 'POST',
						data   : postData,
						url    : ajaxurl,
						success: function (response) {
							$('#give-payment-note-' + postData.note_id).remove();
							if (!$('.give-payment-note').length) {
								$('.give-no-payment-notes').show();
							}
							return false;
						}
					}).fail(function (data) {
						if (window.console && window.console.log) {
							console.log(data);
						}
					});
					return true;
				}

			});

		},

		new_donor: function () {

			$('#give-donor-details').on('click', '.give-payment-new-donor, .give-payment-new-donor-cancel', function (e) {
				e.preventDefault();
				$('.donor-info').toggle();
				$('.new-donor').toggle();

				if ($('.new-donor').is(':visible')) {
					$('#give-new-donor').val(1);
				} else {
					$('#give-new-donor').val(0);
				}

			});

		},

		resend_receipt: function () {
			$('body').on('click', '#give-resend-receipt', function (e) {
				return confirm(give_vars.resend_receipt);
			});
		},


		variable_price_list: function() {
			// Update variable price list when form changes.
			$( '#give_payment_form_select' ).chosen().change( function() {
				var give_form_id,
					variable_prices_html_container = $('.give-donation-level');

				// Check for form ID.
				if (!( give_form_id = $(this).val() )) {
					return false;
				}

				// Bailout.
				if (!variable_prices_html_container.length) {
					return false;
				}

				// Ajax.
				$.ajax({
					type   : 'POST',
					url    : ajaxurl,
					data   : {
						form_id   : give_form_id,
						payment_id: $('input[name="give_payment_id"]').val(),
						action    : 'give_check_for_form_price_variations_html'
					},
					success: function (response) {
						response = response.trim();
						if (response) {

							// Update Variable price html.
							variable_prices_html_container.html(response);

							// Add chosen feature to select tag.
							$('select[name="give-variable-price"]').chosen().change();
						} else {
							// Update Variable price html.
							variable_prices_html_container.html('');
						}
					}
				});
			});

			// Add total donation amount if level changes.
			$('#give-donation-overview').on('change', 'select[name="give-variable-price"]', function () {
				var prices        = jQuery(this).data('prices'),
					$total_amount = $('#give-payment-total');

				if ($(this).val() in prices) {
					$total_amount
						.val(prices[$(this).val()])
						.css('background-color', 'yellow');

					window.setTimeout(
						function () {
							$total_amount.css('background-color', 'white');
						},
						1000
					);
				}
			});
		}

	};

	/**
	 * Settings screen JS
	 */
	var Give_Settings = {

		init: function () {
			this.toggle_options();
			this.main_setting_update_notice();
			this.verify_settings();
		},

		toggle_options: function () {

			/**
			 * Email access
			 */
			var email_access = $('input[name="email_access"]', '.give-setting-tab-body-general');
			email_access.on('change', function () {
				var field_value = $('input[name="email_access"]:checked', '.give-setting-tab-body-general').val();
				if ('enabled' === field_value) {
					$('#recaptcha_key').parents('tr').show();
					$('#recaptcha_secret').parents('tr').show();
				} else {
					$('#recaptcha_key').parents('tr').hide();
					$('#recaptcha_secret').parents('tr').hide();
				}
			}).change();

			/**
			 * Form featured image
			 */
			var form_featured_image = $('input[name="form_featured_img"]', '.give-setting-tab-body-display');
			form_featured_image.on('change', function () {
				var field_value = $('input[name="form_featured_img"]:checked', '.give-setting-tab-body-display').val();
				if ('enabled' === field_value) {
					$('#featured_image_size').parents('tr').show();
				} else {
					$('#featured_image_size').parents('tr').hide();
				}
			}).change();

			/**
			 * Terms and Conditions
			 */
			var terms_and_conditions = $('input[name="terms"]', '.give-setting-tab-body-display');
			terms_and_conditions.on('change', function () {
				var field_value = $('input[name="terms"]:checked', '.give-setting-tab-body-display').val();
				if ('enabled' === field_value) {
					$('#agree_to_terms_label').parents('tr').show();
					$('#wp-agreement_text-wrap').parents('tr').show();
				} else {
					$('#agree_to_terms_label').parents('tr').hide();
					$('#wp-agreement_text-wrap').parents('tr').hide();
				}
			}).change();

			/**
			 * Disable admin notification
			 */
			var admin_notification = $('input[name="admin_notices"]', '.give-setting-tab-body-emails');
			admin_notification.on('change', function () {
				var field_value = $('input[name="admin_notices"]:checked', '.give-setting-tab-body-emails').val();
				if ('enabled' === field_value) {
					$('#donation_notification_subject').parents('tr').show();
					$('#wp-donation_notification-wrap').parents('tr').show();
					$('#admin_notice_emails').parents('tr').show();
				} else {
					$('#donation_notification_subject').parents('tr').hide();
					$('#wp-donation_notification-wrap').parents('tr').hide();
					$('#admin_notice_emails').parents('tr').hide();
				}
			}).change();
		},

		main_setting_update_notice: function () {
			var $setting_message = $('#setting-error-give-setting-updated');
			if ($setting_message.length) {

				// auto hide setting message in 5 seconds.
				window.setTimeout(
					function () {
						$setting_message.slideUp();
					},
					5000
				);
			}
		},

		verify_settings: function () {
			var success_setting = $('#success_page');
			var failure_setting = $('#failure_page');

			/**
			 * Verify success and failure page.
			 */
			success_setting.add(failure_setting).change(function () {
				if (success_setting.val() === failure_setting.val()) {
					var notice_html       = '<div id="setting-error-give-matched-success-failure-page" class="updated settings-error notice is-dismissible"> <p><strong>' + give_vars.matched_success_failure_page + '</strong></p> <button type="button" class="notice-dismiss"><span class="screen-reader-text">' + give_vars.dismiss_notice_text + '</span></button> </div>',
						$notice_container = $('#setting-error-give-matched-success-failure-page');

					// Bailout.
					if ($notice_container.length) {
						return false;
					}

					// Add html.
					$('h2', '#give-mainform').after(notice_html);
					$notice_container = $('#setting-error-give-matched-success-failure-page');

					// Add event to  dismiss button.
					$('.notice-dismiss', $notice_container).click(function () {
						$notice_container.remove();
					});

					// Unset setting field.
					$(this).val('');
				}
			}).change();
		}
	};

	/**
	 * Reports / Exports / Tools screen JS
	 */
	var Give_Reports = {

		init: function () {
			this.date_options();
			this.donors_export();
			this.recount_stats();
		},

		date_options: function () {

			// Show hide extended date options
			$('#give-graphs-date-options').change(function () {
				var $this = $(this);
				if ('other' === $this.val()) {
					$('#give-date-range-options').show();
				} else {
					$('#give-date-range-options').hide();
				}
			});

		},

		donors_export: function () {

			// Show / hide Donation Form option when exporting donors
			$('#give_donor_export_form').change(function () {

				var $this                  = $(this),
					form_id                = $('option:selected', $this).val(),
					customer_export_option = $('#give_customer_export_option');

				if ('0' === $this.val()) {
					customer_export_option.show();
				} else {
					customer_export_option.hide();
				}

				var price_options_select = $('.give_price_options_select');

				// On Form Select, Check if Variable Prices Exist
				if (parseInt(form_id) != 0) {
					var data = {
						action    : 'give_check_for_form_price_variations',
						form_id   : form_id,
						all_prices: true
					};

					$.post(ajaxurl, data, function (response) {
						price_options_select.remove();
						$('#give_donor_export_form_chosen').after(response);
					});
				} else {
					price_options_select.remove();
				}

			});

		},

		recount_stats: function () {

			$('body').on('change', '#recount-stats-type', function () {

				var export_form   = $('#give-tools-recount-form');
				var selected_type = $('option:selected', this).data('type');
				var submit_button = $('#recount-stats-submit');
				var forms         = $('#tools-form-dropdown');

				// Reset the form
				export_form.find('.notice-wrap').remove();
				submit_button.removeClass('button-disabled').attr('disabled', false);
				forms.hide();
				$('.give-recount-stats-descriptions span').hide();

				if ('recount-form' === selected_type) {

					forms.show();
					forms.find('.give-select-chosen').css({
						'width'    : 'auto',
						'min-width': '250px'
					});
				} else if ( 'reset-stats' === selected_type ) {
					export_form.append( '<div class="notice-wrap"></div>' );
					var notice_wrap = export_form.find( '.notice-wrap' );
					notice_wrap.html( '<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' + give_vars.reset_stats_warn + '</label></p></div>' );
					submit_button.addClass( 'button-disabled' ).attr( 'disabled', 'disabled' );

					// Add check when admin try to delete all the test donors.
                } else if ( 'delete-test-donors' === selected_type ) {
                    export_form.append( '<div class="notice-wrap"></div>' );
                    var notice_wrap = export_form.find( '.notice-wrap' );
                    notice_wrap.html( '<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' + give_vars.delete_test_donor + '</label></p></div>' );
                    submit_button.addClass( 'button-disabled' ).attr( 'disabled', 'disabled' );
				} else {
					forms.hide();
					forms.val( 0 );
				}
				$('#' + selected_type).show();
			});

			$('body').on('change', '#confirm-reset', function () {
				var checked = $(this).is(':checked');
				if (checked) {
					$('#recount-stats-submit').removeClass('button-disabled').removeAttr('disabled');
				} else {
					$('#recount-stats-submit').addClass('button-disabled').attr('disabled', 'disabled');
				}
			});

			$('#give-tools-recount-form').submit(function (e) {
				var selection     = $('#recount-stats-type').val();
				var export_form   = $(this);
				var selected_type = $('option:selected', this).data('type');

				if ('reset-stats' === selected_type) {
					var is_confirmed = $('#confirm-reset').is(':checked');
					if (is_confirmed) {
						return true;
					} else {
						has_errors = true;
					}
				}

				export_form.find('.notice-wrap').remove();

				export_form.append('<div class="notice-wrap"></div>');
				var notice_wrap = export_form.find('.notice-wrap');
				var has_errors  = false;

				if (null === selection || 0 === selection) {
					// Needs to pick a method give_vars.batch_export_no_class
					notice_wrap.html('<div class="updated error"><p>' + give_vars.batch_export_no_class + '</p></div>');
					has_errors = true;
				}

				if ('recount-form' === selected_type) {

					var selected_form = $('select[name="form_id"]').val();
					if (selected_form == 0) {
						// Needs to pick give_vars.batch_export_no_reqs
						notice_wrap.html('<div class="updated error"><p>' + give_vars.batch_export_no_reqs + '</p></div>');
						has_errors = true;
					}

				}

				if (has_errors) {
					export_form.find('.button-disabled').removeClass('button-disabled');
					return false;
				}
			});
		}

	};

	/**
	 * Export screen JS
	 */
	var Give_Export = {

		init: function () {
			this.submit();
			this.dismiss_message();
		},

		submit: function () {

			var self = this;

			$(document.body).on('submit', '.give-export-form', function (e) {
				e.preventDefault();

				var submitButton = $(this).find('input[type="submit"]');

				if (!submitButton.hasClass('button-disabled')) {

					var data = $(this).serialize();

					submitButton.addClass('button-disabled');
					$(this).find('.notice-wrap').remove();
					$(this).append('<div class="notice-wrap give-clearfix"><span class="spinner is-active"></span><div class="give-progress"><div></div></div></div>');

					// start the process
					self.process_step(1, data, self);

				}

			});
		},

		process_step: function (step, data, self) {

			$.ajax({
				type    : 'POST',
				url     : ajaxurl,
				data    : {
					form  : data,
					action: 'give_do_ajax_export',
					step  : step,
				},
				dataType: 'json',
				success : function (response) {

					if ('done' == response.step || response.error || response.success) {

						// We need to get the actual in progress form, not all forms on the page
						var export_form = $('.give-export-form').find('.give-progress').parent().parent();
						var notice_wrap = export_form.find('.notice-wrap');

						export_form.find('.button-disabled').removeClass('button-disabled');

						if (response.error) {

							var error_message = response.message;
							notice_wrap.html('<div class="updated error"><p>' + error_message + '</p></div>');

						} else if (response.success) {

							var success_message = response.message;
							notice_wrap.html('<div id="give-batch-success" class="updated notice is-dismissible"><p>' + success_message + '<span class="notice-dismiss"></span></p></div>');

						} else {

							notice_wrap.remove();
							window.location = response.url;

						}

					} else {
						$('.give-progress div').animate({
							width: response.percentage + '%',
						}, 50, function () {
							// Animation complete.
						});
						self.process_step(parseInt(response.step), data, self);
					}

				}
			}).fail(function (response) {
				if (window.console && window.console.log) {
					console.log(response);
				}

				$('.notice-wrap').append(response.responseText);

			});

		},

		dismiss_message: function () {
			$('body').on('click', '#give-batch-success .notice-dismiss', function () {
				$('#give-batch-success').parent().slideUp('fast');
			});
		}

	};

	/**
	 * Updates screen JS
	 */
	var Give_Updates = {
		el: {},

		init: function () {
			this.submit();
			this.dismiss_message();
		},

		submit: function () {
			var self = this, step = 1, resume_update_step = 0;

			self.el.main_container          = Give_Selector_Cache.get('#give-db-updates');
			self.el.update_link             = Give_Selector_Cache.get('a', self.el.main_container);
			self.el.progress_main_container = Give_Selector_Cache.get('.progress-container', self.el.main_container);
			self.el.heading                 = Give_Selector_Cache.get('.update-message', self.el.progress_main_container);
			self.el.progress_container      = Give_Selector_Cache.get('.progress-content', self.el.progress_main_container);

			// Bailout
			if (self.el.update_link.hasClass('active')) {
				return;
			}

			self.el.update_link.on('click', '', function (e) {
				e.preventDefault();

				if ($(this).hasClass('active')) {
					return false;
				}

				// Ask for admin confirmation.
				if (!window.confirm(give_vars.db_update_confirmation_msg)) {
					return;
				}

				$(this).addClass('active').fadeOut();
				self.el.progress_container.find('.notice-wrap').remove();
				self.el.progress_container.append('<div class="notice-wrap give-clearfix"><span class="spinner is-active"></span><div class="give-progress"><div></div></div></div>');
				self.el.progress_main_container.removeClass('give-hidden');

				resume_update_step = parseInt(self.el.heading.data('resume-update'));
				if (resume_update_step) {
					step = resume_update_step;
				}

				// Start the process from first step of first update.
				self.process_step(step, 1, self);
				return false;
			});
		},

		process_step: function (step, update, self) {

			$.ajax({
				type    : 'POST',
				url     : ajaxurl,
				data    : {
					action: 'give_do_ajax_updates',
					step  : parseInt(step),
					update: parseInt(update)
				},
				dataType: 'json',
				success : function (response) {

					// We need to get the actual in progress form, not all forms on the page
					var notice_wrap = Give_Selector_Cache.get('.notice-wrap', self.el.progress_container, true);

					if (-1 !== $.inArray('success', Object.keys(response))) {
						if (response.success) {
							// Update steps info
							if (-1 !== $.inArray('heading', Object.keys(response.data))) {
								self.el.heading.html('<strong>' + response.data.heading + '</strong>');
							}

							self.el.update_link.closest('p').remove();
							notice_wrap.html('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p><button type="button" class="notice-dismiss"></button></div>');

						} else {
							// Update steps info
							if (-1 !== $.inArray('heading', Object.keys(response.data))) {
								self.el.heading.html('<strong>' + response.data.heading + '</strong>');
							}

							notice_wrap.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');

							setTimeout(function () {
								self.el.update_link.removeClass('active').show();
								self.el.progress_main_container.addClass('give-hidden');
							}, 5000);
						}
					} else {
						// Update progress.
						$('.give-progress div', '#give-db-updates').animate({
							width: response.data.percentage + '%',
						}, 50, function () {
							// Animation complete.
						});

						// Update steps info
						if (-1 !== $.inArray('heading', Object.keys(response.data))) {
							self.el.heading.html('<strong>' + response.data.heading.replace('{update_count}', self.el.heading.data('update-count')) + '</strong>');
						}

						self.process_step(parseInt(response.data.step), response.data.update, self);
					}

				}
			}).fail(function (response) {
				if (window.console && window.console.log) {
					console.log(response);
				}

				Give_Selector_Cache.get('.notice-wrap', self.el.progress_container).append(response.responseText);

			}).always(function () {
			});

		},

		dismiss_message: function () {
			$('body').on('click', '#poststuff .notice-dismiss', function () {
				$(this).parent().slideUp('fast');
			});
		}

	};

	/**
	 * Admin Status Select Field Change
	 *
	 * @description: Handle status switching
	 * @since: 1.0
	 */
	var handle_status_change = function () {

		//When sta
		$('select[name="give-payment-status"]').on('change', function () {

			var status = $(this).val();

			$('.give-donation-status').removeClass(function (index, css) {
				return (css.match(/\bstatus-\S+/g) || []).join(' ');
			}).addClass('status-' + status);

		});

	};

	/**
	 * Donor management screen JS
	 */
	var Give_Donor = {

		init          : function () {
			this.edit_donor();
			this.add_email();
			this.remove_user();
			this.cancel_edit();
			this.change_country();
			this.add_note();
			this.delete_checked();
		},
		edit_donor    : function () {
			$('body').on('click', '#edit-donor', function (e) {
				e.preventDefault();
				$('#give-donor-card-wrapper .editable').hide();
				$('#give-donor-card-wrapper .edit-item').fadeIn().css('display', 'block');
				$('.give-select-chosen').css('width', '100%');
			});
		},
		remove_user   : function () {
			$('body').on('click', '#disconnect-donor', function (e) {
				e.preventDefault();

				if (!confirm(give_vars.disconnect_user)) {
					return false;
				}
				var customer_id = $('input[name="customerinfo[id]"]').val();

				var postData = {
					give_action: 'disconnect-userid',
					customer_id: customer_id,
					_wpnonce   : $('#edit-donor-info #_wpnonce').val()
				};

				$.post(ajaxurl, postData, function (response) {
					window.location.href = window.location.href;
				}, 'json');

			});
		},
		cancel_edit   : function () {
			$('body').on('click', '#give-edit-donor-cancel', function (e) {
				e.preventDefault();
				$('#give-donor-card-wrapper .edit-item').hide();
				$('#give-donor-card-wrapper .editable').show();
				$('.give_user_search_results').html('');
			});
		},
		change_country: function () {
			$('select[name="customerinfo[country]"]').change(function () {
				var $this = $(this);
				var data  = {
					action    : 'give_get_states',
					country   : $this.val(),
					field_name: 'customerinfo[state]'
				};

				$.post(ajaxurl, data, function (response) {
					if (typeof ( response.states_found ) != undefined && true == response.states_found) {
						$(':input[name="customerinfo[state]"]').replaceWith(response.data);
					} else {
						$(':input[name="customerinfo[state]"]').replaceWith('<input type="text" name="' + data.field_name + '" value="" class="give-edit-toggles medium-text"/>');
					}
				});

				return false;
			});
		},
		add_note      : function () {
			$('body').on('click', '#add-donor-note', function (e) {
				e.preventDefault();
				var postData = {
					give_action         : 'add-donor-note',
					customer_id         : $('#donor-id').val(),
					donor_note          : $('#donor-note').val(),
					add_donor_note_nonce: $('#add_donor_note_nonce').val()
				};

				if (postData.donor_note) {

					$.ajax({
						type   : 'POST',
						data   : postData,
						url    : ajaxurl,
						success: function (response) {
							$('#give-donor-notes').prepend(response);
							$('.give-no-donor-notes').hide();
							$('#donor-note').val('');
						}
					}).fail(function (data) {
						if (window.console && window.console.log) {
							console.log(data);
						}
					});

				} else {
					var border_color = $('#donor-note').css('border-color');
					$('#donor-note').css('border-color', 'red');
					setTimeout(function () {
						$('#donor-note').css('border-color', border_color);
					}, 500);
				}
			});
		},
		delete_checked: function () {
			$('#give-donor-delete-confirm').change(function () {
				var records_input = $('#give-donor-delete-records');
				var submit_button = $('#give-delete-donor');

				if ($(this).prop('checked')) {
					records_input.attr('disabled', false);
					submit_button.attr('disabled', false);
				} else {
					records_input.attr('disabled', true);
					records_input.prop('checked', false);
					submit_button.attr('disabled', true);
				}
			});
		},
		add_email     : function () {
			if (!$('#add-donor-email').length) {
				return;
			}

			$(document.body).on('click', '#add-donor-email', function (e) {
				e.preventDefault();
				var button  = $(this);
				var wrapper = button.parent();

				wrapper.parent().find('.notice-wrap').remove();
				wrapper.find('.spinner').css('visibility', 'visible');
				button.attr('disabled', true);

				var customer_id = wrapper.find('input[name="donor-id"]').val();
				var email       = wrapper.find('input[name="additional-email"]').val();
				var primary     = wrapper.find('input[name="make-additional-primary"]').is(':checked');
				var nonce       = wrapper.find('input[name="add_email_nonce"]').val();

				var postData = {
					give_action: 'add_donor_email',
					customer_id: customer_id,
					email      : email,
					primary    : primary,
					_wpnonce   : nonce
				};

				$.post(ajaxurl, postData, function (response) {

					if (true === response.success) {
						window.location.href = response.redirect;
					} else {
						button.attr('disabled', false);
						wrapper.after('<div class="notice-wrap"><div class="notice notice-error inline"><p>' + response.message + '</p></div></div>');
						wrapper.find('.spinner').css('visibility', 'hidden');
					}

				}, 'json');

			});
		},
	};

	/**
	 * API screen JS
	 */
	var API_Screen = {

		init: function () {
			this.revoke_api_key();
			this.regenerate_api_key();
		},

		revoke_api_key    : function () {
			$('body').on('click', '.give-revoke-api-key', function (e) {
				return confirm(give_vars.revoke_api_key);
			});
		},
		regenerate_api_key: function () {
			$('body').on('click', '.give-regenerate-api-key', function (e) {
				return confirm(give_vars.regenerate_api_key);
			});
		}
	};

	/**
	 * Edit Donation form screen Js
	 */
	var Edit_Form_Screen = {
		init: function () {
			this.handle_metabox_tab_click();
			this.setup_colorpicker_fields();
			this.setup_media_fields();
			this.setup_repeatable_fields();
			this.handle_repeater_group_events();

			// Multi level repeater field js.
			this.handle_multi_levels_repeater_group_events();
		},

		/**
		 * Toggle metabox tab if mentioned in url.
		 */
		handle_metabox_tab_click: function () {
			var $tab_links = $('.give-metabox-tabs a');

			$tab_links.on('click', function (e) {
				e.preventDefault();
				var $li_parent        = $(this).parent(),
					$sub_field        = $('ul.give-metabox-sub-tabs', $li_parent),
					has_sub_field     = $sub_field.length,
					$all_tab_links_li = $tab_links.parents('li'),
					$all_sub_fields   = $('ul.give-metabox-sub-tabs'),
					in_sub_fields     = $(this).parents('ul.give-metabox-sub-tabs').length;

				if (has_sub_field) {
					$li_parent.toggleClass('active');
					$sub_field.toggleClass('give-hidden');

					var $active_subtab_li = $('li.active', 'ul.give-metabox-sub-tabs');

					// Show hide sub fields if any and exit.
					$all_sub_fields.not($sub_field).addClass('give-hidden');
					$all_tab_links_li.not($li_parent).removeClass('active');

					$active_subtab_li.addClass('active');

					return false;
				} else if (!in_sub_fields) {
					// Hide all tab and sub tabs.
					$all_tab_links_li.each(function (index, item) {
						item = $(item);
						item.removeClass('active');

						if (item.hasClass('has-sub-fields')) {
							$('ul.give-metabox-sub-tabs', item).addClass('give-hidden');
						}
					});
				} else if (in_sub_fields) {
					// Hide all sub tabs.
					$('ul.give-metabox-sub-tabs').addClass('give-hidden');
					$all_tab_links_li.removeClass('active');

					// Hide all tab inside sub tabs.
					$(this).parents('ul.give-metabox-sub-tabs')
						.removeClass('give-hidden')
						.children('li')
						.removeClass('active');

					// Add active class to parent li.
					$(this).parents('li.has-sub-fields').addClass('active');
				}

				// Add active class to current tab link.
				$(this).parent().addClass('active');

				// Hide all tab contents.
				$('.give_options_panel').addClass('give-hidden');

				// Show tab content.
				$($(this).attr('href')).removeClass('give-hidden');

				return false;
			});

			// Auto open tab if mentioned in url.
			if (location.hash.length) {
				var $current_active_tab = $('a[href="' + location.hash + '"]', '.give-metabox-tabs');

				if ($current_active_tab.length) {
					$current_active_tab.trigger('click');
				}
			}
		},

		/**
		 * Initialize colorpicker.
		 */
		setup_colorpicker_fields: function () {
			$(document).ready(function () {
				var $colorpicker_fields = $('.give-colorpicker');

				if ($colorpicker_fields.length) {
					$colorpicker_fields.each(function (index, item) {
						var $item = $(item);

						// Bailout: do not automatically initialize color picker for repeater field group template.
						if ($item.parents('.give-template').length) {
							return;
						}

						$item.wpColorPicker();
					});
				}
			});
		},

		setup_media_fields: function () {
			var give_media_uploader,
				$give_upload_button,
				$body = $('body');

			/**
			 * Set media modal.
			 */
			$body.on('click', '.give-upload-button', function (e) {
				e.preventDefault();
				var $media_modal_config = {};

				// Cache input field.
				$give_upload_button = $(this);

				// Set modal config
				switch ($(this).data('field-type')) {
					case 'media':
						$media_modal_config = {
							title   : give_vars.metabox_fields.media.button_title,
							button  : {text: give_vars.metabox_fields.media.button_title},
							multiple: false, // Set to true to allow multiple files to be selected
							library : {type: 'image'}
						};
						break;

					default:
						$media_modal_config = {
							title   : give_vars.metabox_fields.file.button_title,
							button  : {text: give_vars.metabox_fields.file.button_title},
							multiple: false
						};
				}

				// Extend the wp.media object
				give_media_uploader = wp.media($media_modal_config);

				// When a file is selected, grab the URL and set it as the text field's value
				give_media_uploader.on('select', function () {
					var attachment   = give_media_uploader.state().get('selection').first().toJSON(),
						$input_field = $give_upload_button.prev(),
						fvalue       = ( 'id' === $give_upload_button.data('fvalue') ? attachment.id : attachment.url );

					$body.trigger('give_media_inserted', [attachment, $input_field]);

					// Set input field value.
					$input_field.val(fvalue);
				});

				// Open the uploader dialog
				give_media_uploader.open();
			});

			/**
			 * Show image preview.
			 */
			$body.on('give_media_inserted', function (e, attachment) {
				var $parent          = $give_upload_button.parents('.give-field-wrap'),
					$image_container = $('.give-image-thumb', $parent);

				// Bailout.
				if (!$image_container.length) {
					return false;
				}

				// Bailout and hide preview.
				if ('image' !== attachment.type) {
					$image_container.addClass('give-hidden');
					$('img', $image_container).attr('src', '');
					return false;
				}

				// Set the attachment URL to our custom image input field.
				$image_container.find('img').attr('src', attachment.url);

				// Hide the add image link
				$image_container.removeClass('give-hidden');
			});

			/**
			 * Delete Image Link.
			 */
			$('span.give-delete-image-thumb', '.give-image-thumb').on('click', function (event) {

				event.preventDefault();

				var $parent            = $(this).parents('.give-field-wrap'),
					$image_container   = $(this).parent(),
					$image_input_field = $('input[type="text"]', $parent);

				// Clear out the preview image
				$image_container.addClass('give-hidden');

				// Remove image link from input field.
				$image_input_field.val('');

				// Hide the add image link
				$('img', $image_container).attr('src', '');
			});
		},

		/**
		 * Setup repeater field.
		 */
		setup_repeatable_fields: function () {
			jQuery(function () {
				jQuery('.give-repeatable-field-section').each(function () {
					var $this = $(this);

					// Note: Do not change option params, it can break repeatable fields functionality.
					var options = {
						wrapper                       : '.give-repeatable-fields-section-wrapper',
						container                     : '.container',
						row                           : '.give-row',
						add                           : '.give-add-repeater-field-section-row',
						remove                        : '.give-remove',
						move                          : '.give-move',
						template                      : '.give-template',
						confirm_before_remove_row     : true,
						confirm_before_remove_row_text: give_vars.confirm_before_remove_row_text,
						is_sortable                   : true,
						before_add                    : null,
						after_add                     : handle_metabox_repeater_field_row_count,
						//after_add:  after_add, Note: after_add is internal function in repeatable-fields.js. Uncomment this can cause of js error.
						before_remove                 : null,
						after_remove                  : handle_metabox_repeater_field_row_remove,
						sortable_options              : {
							placeholder: 'give-ui-placeholder-state-highlight',
							start      : function (event, ui) {
								$('body').trigger('repeater_field_sorting_start', [ui.item]);
							},
							stop       : function (event, ui) {
								$('body').trigger('repeater_field_sorting_stop', [ui.item]);
							},
							update     : function (event, ui) {
								// Do not allow any row at position 0.
								if (ui.item.next().hasClass('give-template')) {
									ui.item.next().after(ui.item);
								}

								var $rows = $('.give-row', $this).not('.give-template');

								if ($rows.length) {
									var row_count = 1;
									$rows.each(function (index, item) {
										// Set name for fields.
										var $fields = $('.give-field, label', $(item));

										if ($fields.length) {
											$fields.each(function () {
												var $parent         = $(this).parents('.give-field-wrap'),
													$currentElement = $(this);

												$.each(this.attributes, function (index, element) {
													var old_class_name_prefix = this.value.replace(/\[/g, '_').replace(/]/g, ''),
														old_class_name        = old_class_name_prefix + '_field',
														new_class_name        = '',
														new_class_name_prefix = '';

													// Bailout.
													if (!this.value) {
														return;
													}

													// Reorder index.
													this.value            = this.value.replace(/\[\d+\]/g, '[' + (row_count - 1) + ']');
													new_class_name_prefix = this.value.replace(/\[/g, '_').replace(/]/g, '');

													// Update class name.
													if ($parent.hasClass(old_class_name)) {
														new_class_name = new_class_name_prefix + '_field';
														$parent.removeClass(old_class_name).addClass(new_class_name);
													}

													// Update field id.
													if (old_class_name_prefix == $currentElement.attr('id')) {
														$currentElement.attr('id', new_class_name_prefix);
													}
												});
											});
										}

										row_count++;
									});

									// Fire event.
									$this.trigger('repeater_field_row_reordered', [ui.item]);
								}
							}
						}
						//row_count_placeholder: '{{row-count-placeholder}}' Note: do not modify this param otherwise it will break repeatable field functionality.
					};

					jQuery(this).repeatable_fields(options);
				});
			});
		},

		/**
		 * Handle repeater field events.
		 */
		handle_repeater_group_events: function () {
			var $repeater_fields = $('.give-repeatable-field-section'),
				$body            = $('body');

			// Auto toggle repeater group
			$body.on('click', '.give-row-head button', function () {
				var $parent = $(this).closest('tr');
				$parent.toggleClass('closed');
				$('.give-row-body', $parent).toggle();
			});

			// Reset header title when new row added.
			$repeater_fields.on('repeater_field_new_row_added repeater_field_row_deleted repeater_field_row_reordered', function () {
				handle_repeater_group_add_number_suffix($(this));
			});

			// Disable editor when sorting start.
			$body.on('repeater_field_sorting_start', function (e, row) {
				var $textarea = $('.wp-editor-area', row);

				if ($textarea.length) {
					$textarea.each(function (index, item) {
						window.setTimeout(
							function () {
								tinyMCE.execCommand('mceRemoveEditor', true, $(item).attr('id'));
							},
							300
						);
					});
				}
			});

			// Enable editor when sorting stop.
			$body.on('repeater_field_sorting_stop', function (e, row) {
				var $textarea = $('.wp-editor-area', row);

				if ($textarea.length) {
					$textarea.each(function (index, item) {
						window.setTimeout(
							function () {
								var textarea_id = $(item).attr('id');
								tinyMCE.execCommand('mceAddEditor', true, textarea_id);

								// Switch editor to tmce mode to fix some glitch which appear when you reorder rows.
								window.setTimeout(function () {
									// Hack to show tmce mode.
									switchEditors.go(textarea_id, 'html');
									$('#' + textarea_id + '-tmce').trigger('click');
								}, 100);
							},
							300
						);
					});
				}
			});

			// Process jobs on document load for repeater fields.
			$repeater_fields.each(function (index, item) {
				// Reset title on document load for already exist groups.
				var $item = $(item);
				handle_repeater_group_add_number_suffix($item);

				// Close all tabs when page load.
				if (parseInt($item.data('close-tabs'))) {
					$('.give-row-head button', $item).trigger('click');
					$('.give-template', $item).removeClass('closed');
					$('.give-template .give-row-body', $item).show();
				}
			});

			// Setup colorpicker field when row added.
			$repeater_fields.on('repeater_field_new_row_added', function (e, container, new_row) {
				$('.give-colorpicker', $(this)).each(function (index, item) {
					var $item = $(item);

					// Bailout: skip already init colorpocker fields.
					if ($item.parents('.wp-picker-container').length || $item.parents('.give-template').length) {
						return;
					}

					$item.wpColorPicker();
				});

				// Load WordPress editor by ajax..
				var wysiwyg_editor_container = $('div[data-wp-editor]', new_row);

				if (wysiwyg_editor_container.length) {
					wysiwyg_editor_container.each(function (index, item) {
						var $item                = $(item),
							wysiwyg_editor       = $('.wp-editor-wrap', $item),
							textarea             = $('textarea', $item),
							textarea_id          = 'give_wysiwyg_unique_' + Math.random().toString().replace('.', '_'),
							wysiwyg_editor_label = wysiwyg_editor.prev();

						textarea.attr('id', textarea_id);

						$.post(
							ajaxurl,
							{
								action       : 'give_load_wp_editor',
								wp_editor    : $item.data('wp-editor'),
								wp_editor_id : textarea_id,
								textarea_name: $('textarea', $item).attr('name')
							},
							function (res) {
								wysiwyg_editor.remove();
								wysiwyg_editor_label.after(res);

								// Setup qt data for editor.
								tinyMCEPreInit.qtInit[textarea.attr('id')] = $.extend(
									true,
									tinyMCEPreInit.qtInit['_give_agree_text'],
									{id: textarea_id}
								);

								// Setup mce data for editor.
								tinyMCEPreInit.mceInit[textarea_id] = $.extend(
									true,
									tinyMCEPreInit.mceInit['_give_agree_text'],
									{
										body_class: textarea_id + ' post-type-give_forms post-status-publish locale-' + tinyMCEPreInit.mceInit['_give_agree_text']['wp_lang_attr'].toLowerCase(),
										selector  : '#' + textarea_id
									}
								);

								// Setup editor.
								tinymce.init(tinyMCEPreInit.mceInit[textarea_id]);
								quicktags(tinyMCEPreInit.qtInit[textarea_id]);
								QTags._buttonsInit();

								window.setTimeout(function () {
									// Hack to show tmce mode.
									switchEditors.go(textarea_id, 'html');
									$('#' + textarea_id + '-tmce').trigger('click');
								}, 100);

								if (!window.wpActiveEditor) {
									window.wpActiveEditor = textarea_id;
								}
							}
						);
					});
				}

			});

		},

		/**
		 *  Handle events for multi level repeater group.
		 */
		handle_multi_levels_repeater_group_events: function () {
			var $repeater_fields = $('#_give_donation_levels_field');

			// Add level title as suffix to header title when admin add level title.
			$('body').on('keyup', '.give-multilevel-text-field', function () {
				var $parent                           = $(this).closest('tr'),
					$header_title_container           = $('.give-row-head h2 span', $parent),
					donation_level_header_text_prefix = $header_title_container.data('header-title');

				// Donation level header already set.
				if ($(this).val() && (  $(this).val() === $header_title_container.html() )) {
					return false;
				}

				if ($(this).val()) {
					// Change donaiton level header text.
					$header_title_container.html(donation_level_header_text_prefix + ': ' + $(this).val());
				} else {
					// Reset donation level header heading text.
					$header_title_container.html(donation_level_header_text_prefix);
				}
			});

			//  Add level title as suffix to header title on document load.
			$('.give-multilevel-text-field').each(function (index, item) {

				// Skip first element.
				if (!index) {
					return;
				}

				// Check if item is jquery object or not.
				var $item = $(item);

				var $parent                           = $item.closest('tr'),
					$header_title_container           = $('.give-row-head h2 span', $parent),
					donation_level_header_text_prefix = $header_title_container.data('header-title');

				// Donation level header already set.
				if ($item.val() && (  $item.val() === $header_title_container.html() )) {
					return false;
				}

				if ($item.val()) {
					// Change donaiton level header text.
					$header_title_container.html(donation_level_header_text_prefix + ': ' + $item.val());
				} else {
					// Reset donation level header heading text.
					$header_title_container.html(donation_level_header_text_prefix);
				}
			});

			// Handle row deleted event for levels repeater field.
			$repeater_fields.on('repeater_field_row_deleted', function () {
				var $this = $(this);

				window.setTimeout(
					function () {
						var $parent          = $this,
							$repeatable_rows = $('.give-row', $parent).not('.give-template'),
							$default_radio   = $('.give-give_default_radio_inline', $repeatable_rows),
							number_of_level  = $repeatable_rows.length;

						if (number_of_level === 1) {
							$default_radio.prop('checked', true);
						}
					},
					200
				);
			});

			// Handle row added event for levels repeater field.
			$repeater_fields.on('repeater_field_new_row_added', function (e, container, new_row) {
				var $this        = $(this),
					max_level_id = 0;

				// Auto set default level if no level set as default.
				window.setTimeout(
					function () {
						// Set first row as default if selected default row deleted.
						// When a row is removed containing the default selection then revert default to first repeatable row.
						if ($('.give-give_default_radio_inline', $this).is(':checked') === false) {
							$('.give-row', $this)
								.not('.give-template')
								.first()
								.find('.give-give_default_radio_inline')
								.prop('checked', true);
						}
					},
					200
				);

				// Get max level id.
				$('input[type="hidden"].give-levels_id', $this).each(function (index, item) {
					var $item         = $(item),
						current_level = parseInt($item.val());
					if (max_level_id < current_level) {
						max_level_id = current_level;
					}
				});

				// Auto set level id for new setting level setting group.
				$('input[type="hidden"].give-levels_id', new_row).val(++max_level_id);
			});
		}
	};

	/**
	 * Handle row count and field count for repeatable field.
	 */
	var handle_metabox_repeater_field_row_count = function (container, new_row) {
		var row_count  = $(container).attr('data-rf-row-count'),
			$container = $(container),
			$parent    = $container.parents('.give-repeatable-field-section');

		row_count++;

		// Set name for fields.
		$('*', new_row).each(function () {
			$.each(this.attributes, function (index, element) {
				this.value = this.value.replace('{{row-count-placeholder}}', row_count - 1);
			});
		});

		// Set row counter.
		$(container).attr('data-rf-row-count', row_count);

		// Fire event: Row added.
		$parent.trigger('repeater_field_new_row_added', [container, new_row]);
	};

	/**
	 * Handle row remove for repeatable field.
	 */
	var handle_metabox_repeater_field_row_remove = function (container) {
		var $container = $(container),
			$parent    = $container.parents('.give-repeatable-field-section'),
			row_count  = $(container).attr('data-rf-row-count');

		// Reduce row count.
		$container.attr('data-rf-row-count', --row_count);

		// Fire event: Row deleted.
		$parent.trigger('repeater_field_row_deleted');
	};

	/**
	 * Add number suffix to repeater group.
	 */
	var handle_repeater_group_add_number_suffix = function ($parent) {
		// Bailout: check if auto group numbering is on or not.
		if (!parseInt($parent.data('group-numbering'))) {
			return;
		}

		var $header_title_container = $('.give-row-head h2 span', $parent),
			header_text_prefix      = $header_title_container.data('header-title');

		$header_title_container.each(function (index, item) {
			var $item = $(item);

			// Bailout: do not rename header title in fields template.
			if ($item.parents('.give-template').length) {
				return;
			}

			$item.html(header_text_prefix + ': ' + index);
		});
	};

	/**
	 * Initialize qTips
	 */
	var initialize_qtips = function () {
		jQuery('[data-tooltip!=""]').qtip({ // Grab all elements with a non-blank data-tooltip attr.
			content: {
				attr: 'data-tooltip' // Tell qTip2 to look inside this attr for its content
			},
			style  : {classes: 'qtip-rounded qtip-tipsy'},
			events : {
				show: function (event, api) {
					var $el = $(api.elements.target[0]);
					$el.qtip('option', 'position.my', ($el.data('tooltip-my-position') == undefined) ? 'bottom center' : $el.data('tooltip-my-position'));
					$el.qtip('option', 'position.at', ($el.data('tooltip-target-position') == undefined) ? 'top center' : $el.data('tooltip-target-position'));
				}
			}
		});
	};

	/**
	 * Payment history listing page js
	 */
	var Give_Payment_History = {
		init: function () {
			this.handle_bulk_delete();
		},

		handle_bulk_delete: function () {
			var $payment_filters = $('#give-payments-filter');

			/**
			 * Payment filters
			 */
			$payment_filters.on('submit', function (e) {
				var current_action        = $('select[name="action"]', $(this)).val(),
					current_action_label  = $('select[name="action"]', $(this)).find(':selected').text(),
					$payments             = $('input[name="payment[]"]:checked').length,
					is_status_type_action = ( -1 !== current_action.indexOf('set-status-') ),
					confirm_action_notice = '',
					status                = '';

				// Set common action, if action type is status.
				current_action = is_status_type_action ?
					'set-to-status' :
					current_action;

				if (Object.keys(give_vars.bulk_action).length) {
					for (status in  give_vars.bulk_action) {
						if (status === current_action) {
							// Get status text if current action types is status.
							confirm_action_notice = is_status_type_action ?
								give_vars.bulk_action[current_action].zero.replace('{status}', current_action_label.replace('Set To ', '')) :
								give_vars.bulk_action[current_action].zero;

							// Check if admin selected any donations or not.
							if (!parseInt($payments)) {
								alert(confirm_action_notice);
								return false;
							}

							// Get message on basis of payment count.
							confirm_action_notice = ( 1 < $payments ) ?
								give_vars.bulk_action[current_action].multiple :
								give_vars.bulk_action[current_action].single;

							// Trigger Admin Confirmation PopUp.
							return window.confirm(confirm_action_notice
								.replace('{payment_count}', $payments)
								.replace('{status}', current_action_label.replace('Set To ', ''))
							);
						}
					}
				}

				return true;
			});
		}
	};

	// On DOM Ready.
	$(function () {

		enable_admin_datepicker();
		handle_status_change();
		setup_chosen_give_selects();
		Give_List_Donation.init();
		Give_Edit_Donation.init();
		Give_Settings.init();
		Give_Reports.init();
		Give_Donor.init();
		API_Screen.init();
		Give_Export.init();
		Give_Updates.init();
		Edit_Form_Screen.init();
		Give_Payment_History.init();

		initialize_qtips();

		//Footer
		$('a.give-rating-link').click(function () {
			jQuery(this).parent().text(jQuery(this).data('rated'));
		});

		/**
		 *  Amount format validation form price field setting
		 */

		// This function uses for adding qtip to money/price field.
		function give_add_qtip($fields) {
			// Add qtip to all existing money input fields.
			$fields.each(function () {
				$(this).qtip({
					style   : 'qtip-dark qtip-tipsy',
					content : {
						text: give_vars.price_format_guide.trim()
					},
					show    : '',
					position: {
						my: 'bottom center',
						at: 'top center'
					}
				});
			});
		}

		var $give_money_fields       = $('input.give-money-field, input.give-price-field');
		var thousand_separator       = give_vars.thousands_separator,
			decimal_separator        = give_vars.decimal_separator,
			thousand_separator_count = '',
			alphabet_count           = '',
			price_string             = '',

			// Thousand separation limit in price depends upon decimal separator symbol.
			// If thousand separator is equal to decimal separator then price does not have more then 1 thousand separator otherwise limit is zero.
			thousand_separator_limit = ( decimal_separator === thousand_separator ? 1 : 0 );

		// Add qtip to all existing money input fields.
		give_add_qtip($give_money_fields);

		// Add qtip to new created money/price input field.
		$('#_give_donation_levels_repeat').on('click', 'button.cmb-add-group-row', function () {
			window.setTimeout(
				function () {

					// Update input filed selector.
					$give_money_fields = $('input.give-money-field, input.give-price-field');

					// Add qtip to all existing money input fields.
					give_add_qtip($give_money_fields);
				},
				100
			);
		});

		// Check & show message on keyup event.
		$('#poststuff').on('keyup', 'input.give-money-field, input.give-price-field', function () {
			// Count thousand separator in price string.
			thousand_separator_count = ( $(this).val().match(new RegExp(thousand_separator, 'g')) || [] ).length;
			alphabet_count           = ( $(this).val().match(new RegExp('[a-z]', 'g')) || [] ).length;

			// Show qtip conditionally if thousand separator detected on price string.
			if (
				( -1 !== $(this).val().indexOf(thousand_separator) )
				&& ( thousand_separator_limit < thousand_separator_count )
			) {
				$(this).qtip('show');
			} else if (alphabet_count) {
				// Show qtip if user entered a number with alphabet letter.
				$(this).qtip('show');
			} else {
				$(this).qtip('hide');
			}

			// Reset thousand separator count.
			thousand_separator_count = alphabet_count = '';
		});

		// Format price sting of input field on focusout.
		$('#poststuff').on('focusout', 'input.give-money-field, input.give-price-field', function () {
			price_string = give_unformat_currency($(this).val(), false);

			// Back out.
			if (give_unformat_currency('0', false) === give_unformat_currency($(this).val(), false)) {
				$(this).val('');
				return false;
			}

			// Replace dot decimal separator with user defined decimal separator.
			price_string = price_string.replace('.', decimal_separator);

			// Check if current number is negative or not.
			if (-1 !== price_string.indexOf('-')) {
				price_string = price_string.replace('-', '');
			}

			// Update format price string in input field.
			$(this).val(price_string);
		});

		/**
		 * Responsive setting tab features.
		 */

		// Show/Hide sub tab nav.
		$('.give-settings-page').on('click', '#give-show-sub-nav', function (e) {
			e.preventDefault();

			var $sub_tab_nav = $(this).next();

			if (!$sub_tab_nav.is(':hover')) {
				$sub_tab_nav.toggleClass('give-hidden');
			}

			return false;
		}).on('blur', '#give-show-sub-nav', function () {
			var $sub_tab_nav = $(this).next();

			if (!$sub_tab_nav.is(':hover')) {
				$sub_tab_nav.addClass('give-hidden');
			}
		});

		// Render setting tab.
		give_render_responsive_tabs();
	});
})(jQuery);

/**
 * Responsive js.
 */
jQuery(window).resize(function () {
	give_render_responsive_tabs();
});

/**
 * Render responsive tabs
 */
function give_render_responsive_tabs() {
	var $setting_page_form      = jQuery('.give-settings-page'),
		$main_tab_nav           = jQuery('h2.give-nav-tab-wrapper'),
		setting_page_form_width = $setting_page_form.width(),
		$sub_tab_nav_wrapper    = jQuery('.give-sub-nav-tab-wrapper'),
		$sub_tab_nav            = jQuery('nav', $sub_tab_nav_wrapper),
		$setting_tab_links      = jQuery('h2.give-nav-tab-wrapper>a:not(give-not-tab)'),
		$show_tabs              = [],
		$hide_tabs              = [],
		tab_width               = 0;

	if (600 < jQuery(window).outerWidth()) {
		tab_width = 200;
	}

	// Bailout.
	if (!$setting_page_form.length) {
		return false;
	}

	// Update tab wrapper css.
	$main_tab_nav.css({
		height  : 'auto',
		overflow: 'visible'
	});

	// Show all tab if anyone hidden to calculate correct tab width.
	$setting_tab_links.removeClass('give-hidden');

	var refactor_tabs = new Promise(
		function (resolve, reject) {
			// Collect tabs to show or hide.
			jQuery.each($setting_tab_links, function (index, $tab_link) {
				$tab_link = jQuery($tab_link);
				tab_width = tab_width + parseInt($tab_link.outerWidth());

				if (tab_width < setting_page_form_width) {
					$show_tabs.push($tab_link);
				} else {
					$hide_tabs.push($tab_link);
				}
			});

			resolve(true);
		}
	);

	refactor_tabs.then(function (is_refactor_tabs) {
		// Remove current tab from sub menu and add this to main menu if exist and get last tab from main menu and add this to sub menu.
		if ($hide_tabs.length && ( -1 != window.location.search.indexOf('&tab=') )) {
			var $current_tab_nav = {},
				query_params     = get_url_params();

			$hide_tabs = $hide_tabs.filter(function ($tab_link) {
				var is_current_nav_item = ( -1 != parseInt($tab_link.attr('href').indexOf('&tab=' + query_params['tab'])) );

				if (is_current_nav_item) {
					$current_tab_nav = $tab_link;
				}

				return ( !is_current_nav_item );
			});

			if ($current_tab_nav.length) {
				$hide_tabs.unshift($show_tabs.pop());
				$show_tabs.push($current_tab_nav);
			}
		}

		var show_tabs = new Promise(function (resolve, reject) {
			// Show main menu tabs.
			if ($show_tabs.length) {
				jQuery.each($show_tabs, function (index, $tab_link) {
					$tab_link = jQuery($tab_link);

					if ($tab_link.hasClass('give-hidden')) {
						$tab_link.removeClass('give-hidden');
					}
				});
			}

			resolve(true);
		});

		show_tabs.then(function (is_show_tabs) {
			// Hide sub menu tabs.
			if ($hide_tabs.length) {
				$sub_tab_nav.html('');

				jQuery.each($hide_tabs, function (index, $tab_link) {
					$tab_link = jQuery($tab_link);
					$tab_link.addClass('give-hidden');
					$tab_link.clone().removeClass().appendTo($sub_tab_nav);
				});

				if (!jQuery('.give-sub-nav-tab-wrapper', $main_tab_nav).length) {
					$main_tab_nav.append($sub_tab_nav_wrapper);
				}

				$sub_tab_nav_wrapper.show();
			} else {
				$sub_tab_nav_wrapper.hide();
			}
		});
	});
}

/**
 * Get url query params.
 *
 * @returns {Array}
 */
function get_url_params() {
	var vars   = [], hash;
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	for (var i = 0; i < hashes.length; i++) {
		hash          = hashes[i].split('=');
		vars[hash[0]] = hash[1];
	}
	return vars;
}
