(function ($) {
	// page ID or "slug"
	window.SS_PAGES.snapshot_page_snapshot_pro_managed_backups_create = function () {

		$(document).ready(function () {
			if ('#wps-backups-settings-schedule' === window.location.hash) {
				$('[for="wps-managed-backups-configs"]').click();
			}
		});

		$("#wps-managed-backups-configure").on("click", function (e) {
			e.preventDefault();
			$("[for='wps-managed-backups-menu-config']").click();
			$('html,body').animate({
				scrollTop: $(".wps-managed-backups-configs").offset().top
			}, 'slow');
		});

		jQuery("input[name='frequency']").change(function () {
			var backup_frequency_options = jQuery("input[name='frequency']:checked").val();
			if ((backup_frequency_options === "once")) {
				jQuery('div#snapshot-schedule-options-container').slideUp('fast');
				jQuery('#snapshot-backup-action').attr('name', 'snapshot-disable-cron');
			} else {
				jQuery('div#snapshot-schedule-options-container').slideDown('slow');
				jQuery('#snapshot-backup-action').attr('name', 'snapshot-schedule');
			}
		}).change();

		jQuery('#wps-build-error-again').on('click', function (e) {
			e.preventDefault();
			jQuery('#wps-build-error').addClass('hidden');
			jQuery('#wps-build-progress').removeClass('hidden');
			jQuery('form#managed-backup-update').trigger('submit');
		});

		jQuery('#checkbox-run-backup-now').on('change', function (e) {
			var run_now = $(this).is(':checked');
			var button = $('#managed-backup-update button[type=submit]');
			button.text(button.attr('data-' + (run_now ? 'run-backup' : 'update-settings') + '-text'));
		});

		jQuery('form#managed-backup-update').off().on('submit', function (e) {

			var form = $(this);

			var snapshot_form_once = jQuery('#frequency-once', form).is(':checked');
			var snapshot_form_run_now = jQuery('#checkbox-run-backup-now', form).is(':checked');

			if (snapshot_form_once || ( !snapshot_form_once && snapshot_form_run_now )) {
				e.preventDefault();

				jQuery('#managed-backup-update').addClass('hidden');
				jQuery('#container.wps-page-builder').removeClass('hidden');

				if (snapshot_form_once) {
					window.Sfb.ManualBackup.handle_start_click();
				} else {
					//Save new backup setting using ajax
					jQuery.ajax({
						type: 'POST',
						url: form.attr('src'),
						data: form.serialize(),
						success: function () {
							//Run backup
							window.Sfb.ManualBackup.handle_start_click();
						}
					});
				}

			}
		});

		/* Handlers for back and cancel buttons */

		var snapshot_ajax_hdl_xhr = null;
		var snapshot_ajax_user_aborted = false;

		function snapshot_button_abort_proc() {
			snapshot_ajax_hdl_xhr !== null && snapshot_ajax_hdl_xhr.abort();
			snapshot_ajax_user_aborted = true;

			var prm = $.post(ajaxurl, {
				action: "snapshot-full_backup-finish"
			}, noop, 'json');

			jQuery('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html("Backup aborted");
			jQuery('.wpmud-box-title .wps-title-result').removeClass("hidden");
			jQuery('#wps-build-progress').addClass('hidden');

			window.Sfb.ManualBackup.current = window.Sfb.ManualBackup.total = 0;
			window.Sfb.ManualBackup.update_progress();

			return false;
		}

		jQuery("#wps-build-progress-cancel").on('click', function (e) {
			e.preventDefault();
			snapshot_button_abort_proc();
		});

		jQuery("#wps-build-error-back").on('click', function (e) {
			e.preventDefault();
			jQuery('#container.wps-page-builder').addClass('hidden');
			jQuery('#managed-backup-update').removeClass('hidden');
		});

		var Sfb = window.Sfb || {
				value: function (key) {
					return (window._snp_vars || {})[key];
				},
				l10n: function (key) {
					return (this.value('l10n') || {})[key] || key;
				}
			};

		var ElementSelector = {
			/**
			 * Used to get all applicable element anchors
			 *
			 * @type {String}
			 */
			element_selector: '',

			/**
			 * Gets applicable elements for targeting
			 *
			 * @uses this.element_selector
			 *
			 * @return {Object} jQuery nodes object
			 */
			get_elements: function () {
				return $(this.element_selector);
			},

			/**
			 * Initializes event listeners
			 *
			 * @return {Boolean}
			 */
			initialize_listeners: function () {
				var me = this;
				this.get_elements().each(function () {
					me.initialize_listener($(this));
				});
				return true;
			},

			/**
			 * Initializes individual event listener for a node
			 *
			 * @param {object} $el jQuery node
			 */
			initialize_listener: function ($el) {
			}
		};

		/**
		 * Shared utilities
		 *
		 * @type {Object}
		 */
		Sfb.Util = {
			/**
			 * Stops event propagation
			 *
			 * @param {Object} e Event
			 *
			 * @return {Boolean} False
			 */
			stop_prop: function (e) {
				if (e && e.preventDefault) e.preventDefault();
				if (e && e.stopPropagation) e.stopPropagation();
				return false;
			}
		};

		/**
		 * Notice actions
		 *
		 * @type {Object}
		 */
		Sfb.Notice = $.extend({}, ElementSelector, {

			/**
			 * Used to get all applicable element anchors
			 *
			 * @type {String}
			 */
			element_selector: '.wp-admin .notice .button.snapshot',

			/**
			 * Used as a fallback if anchor doesn't have `data-target` attribute
			 *
			 * @type {String}
			 */
			target_selector: '.backup-list td.name .location.local:first',

			/**
			 * Used as a fallback if anchor doesn't have `data-subtarget` attribute
			 *
			 * @type {String}
			 */
			subtarget_selector: 'td',

			/**
			 * Handles target scroll clicks
			 *
			 * @param {Object} e Event
			 *
			 * @return {Boolean}
			 */
			handle_target_scroll: function (e) {
				var target = $(this).attr("data-target") || Sfb.Notice.target_selector,
					subtarget = $(this).is('[data-subtarget]') ? $(this).attr("data-target") : Sfb.Notice.subtarget_selector,
					$target = $(target),
					top = 0
				;
				if (subtarget) $target = $target.closest(subtarget);

				if ($target.length) {
					top = ($target.offset() || {}).top - $("#wpadminbar").height();
					if (top > 0) {
						$(window).scrollTop(top);
						$target.fadeOut(300).fadeIn(300);
					}
				}
				return Sfb.Util.stop_prop(e);
			},

			initialize_listener: function ($el) {
				$el
					.off("click.snapshot")
					.on("click.snapshot", this.handle_target_scroll)
				;
			},

			run: function () {
				this.initialize_listeners();
				return true;
			}
		});

		var noop = function () {
		};

		/**
		 * Manual backup button handler
		 *
		 * @type {Object}
		 */
		Sfb.ManualBackup = $.extend({}, ElementSelector, {

			element_selector: '.wp-admin .snapshot-wrap button[name="backup"]',

			/**
			 * Current progress step
			 *
			 * @type {Number}
			 */
			current: 0,

			/**
			 * Total estimated steps for this backup
			 *
			 * @type {Number}
			 */
			total: 0,

			/**
			 * Target node for output
			 *
			 * @type {Object} jQuery node reference
			 */
			$target: false,

			/**
			 * Skip estimate step flag
			 *
			 * If set to true, the overall processing estimation
			 * step will be skipped and, as a consequence, the
			 * progress will be expressed as steps rather than percentages.
			 *
			 * @type {Bool}
			 */
			skip_estimate: false,

			/**
			 * Reports error response message and reinitiates listeners
			 *
			 * @param {Object} rsp Optional response object
			 *
			 * @return {Bool}
			 */
			error_handler: function (rsp) {
				var msg = $.trim((rsp || {}).responseText) || '&lt;empty response&gt;';

				jQuery("#wps-build-error .wps-auth-message p").html('<p>' + msg + '</p>');
				jQuery("#wps-build-error").removeClass("hidden");
				jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
				jQuery("#wps-build-progress").addClass("hidden");

				return !!Sfb.ManualBackup.initialize_listeners();
			},

			/**
			 * Updates progress info message
			 *
			 * The info message will be expressed in percentages, or alternatively
			 * simple step increments, depending on whether we performed the size estimate.
			 *
			 * @return {Bool}
			 */
			update_progress: function () {
				if (this.total > 0 && this.current && this.current <= this.total) {
					var current = this.current > 1 ? this.current - 1 : 1,
						percentage = (current * 100) / this.total
					;
					if (percentage >= 100) percentage = 100;
					jQuery("#wps-build-progress .wps-total-status .wps-loading-number").html(parseInt(percentage, 10) + '%');
					jQuery("#wps-build-progress .wps-total-status .wps-loading-bar span").width(parseInt(percentage, 10) + '%');


				}
				return true;
			},

			/**
			 * Starts backup
			 *
			 * Sends out backup start request
			 *
			 * @return {Object} jQuery promise
			 */
			start_backup: function () {
				return $.post(ajaxurl, {
					action: "snapshot-full_backup-start"
				}, noop, 'json').fail(Sfb.ManualBackup.error_handler);
			},

			/**
			 * Finishes backup
			 *
			 * Sends out backup finish request
			 *
			 * @return {Object} jQuery promise
			 */
			finish_backup: function ( nonce ) {
				this.update_progress();
				var prm = $.post(ajaxurl, {
					action: "snapshot-full_backup-finish",
					security: nonce
				}, noop, 'json');
				prm.then(function (data) {
					if (!data.status) return Sfb.ManualBackup.finish_backup( nonce );
					jQuery("#wps-build-error").addClass("hidden");
					jQuery("#wps-build-progress").addClass("hidden");
					jQuery("#wps-build-success").removeClass("hidden");
					//Sfb.ManualBackup.run();
					//window.location.reload(); // reload when we're done
				}).fail(Sfb.ManualBackup.error_handler);
				return prm;
			},

			/**
			 * Processes files
			 *
			 * Sends out backup files processing request
			 *
			 * @return {Object} jQuery promise
			 */
			process_files: function ( nonce ) {
				this.update_progress();
				$.post(ajaxurl, {
					action: "snapshot-full_backup-process",
					security: nonce
				}, noop, 'json').then(function (data) {
					Sfb.ManualBackup.current++;
					var is_done = false;
					try {
						is_done = !!data.done;
					} catch (e) {
						return error_handler();
					}
					if (!is_done) Sfb.ManualBackup.process_files( nonce );
					else Sfb.ManualBackup.finish_backup( nonce );
				}).fail(Sfb.ManualBackup.error_handler);
			},

			/**
			 * Estimates backup processing size
			 *
			 * Sends out an estimate request
			 *
			 * @return {Object} jQuery promise
			 */
			estimate_backup: function () {
				var prm = $.post(ajaxurl, {
					action: "snapshot-full_backup-estimate"
				}, noop, 'json');
				prm.then(function (data) {
					var total = parseInt((data || {}).total || '0', 10);
					if (total) Sfb.ManualBackup.total = total;
				}).fail(Sfb.ManualBackup.error_handler);
				return prm;
			},

			/**
			 * Handles button click
			 *
			 * @param {Object} e Event
			 *
			 * @return {Bool}
			 */
			handle_start_click: function (e) {
				jQuery('#managed-backup-update').addClass('hidden');
				jQuery('#container.wps-page-builder').removeClass('hidden');

				var security = jQuery("form#managed-backup-update :hidden#snapshot-ajax-nonce");
				Sfb.ManualBackup.current = 1;

				Sfb.ManualBackup.start_backup().then(function (data) {
					var backup = (data || {}).id;
					if (!backup) return Sfb.ManualBackup.error_handler(data);

					if (Sfb.ManualBackup.skip_estimate) {
						return Sfb.ManualBackup.process_files( security.val() );
					} else {
						return Sfb.ManualBackup.estimate_backup().then(function () {
							return Sfb.ManualBackup.process_files( security.val() );
						});
					}
				});

				return Sfb.Util.stop_prop(e);
			},

			/**
			 * Initializes the button click listener
			 *
			 * @param {Object} $el jQuery node reference
			 *
			 * @return {Bool}
			 */
			initialize_listener: function ($el) {
				$el
					.off("click.snapshot", this.handle_start_click)
					.on("click.snapshot", this.handle_start_click)
				;
				return true;
			},

			/**
			 * Starts up the handler
			 *
			 * Boots markup and initializes the listeners
			 *
			 * @return {Bool}
			 */
			run: function () {
				if (!this.get_elements().length) return false;

				var $target_root = $("#snapshot-full_backups-panel header"),
					out_tpl = '<div id="snapshot-ajax-out"><div class="out"></div></div>'
				;

				$("#snapshot-ajax-out").remove();
				if ($target_root.length) {
					$target_root.after(out_tpl);
				} else {
					this.get_elements().first().after(out_tpl);
				}
				this.$target = $("#snapshot-ajax-out").find(".out");
				this.initialize_listeners();
				return true;
			}
		});

		/*
		 Sfb.BackupItem = $.extend({}, ElementSelector, {

		 element_selector: '.backup-list .row-actions a[href="#upload"]',

		 handle_upload: function () {
		 var $row = $(this).closest('tr'),
		 timestamp = $row.find('input[name="delete-bulk[]"]').val()
		 ;
		 if (timestamp) {
		 console.log(timestamp);
		 }
		 return Sfb.Util.stop_prop();
		 },

		 initialize_listener: function ($el) {
		 $el
		 .off("click.snapshot")
		 .on("click.snapshot", this.handle_upload)
		 ;
		 },

		 run: function () {
		 this.initialize_listeners();
		 return true;
		 }
		 });
		 */

		/**
		 * Deals with the logging section
		 *
		 * @type {Object}
		 */
		Sfb.Logs = {

			/**
			 * Log enable/disable toggle
			 *
			 * @type {ElementSelector}
			 */
			Toggler: $.extend({}, ElementSelector, {

				element_selector: '#log-enable',

				/**
				 * Toggles logging level sections on log enable/disable
				 *
				 * Also sets log levels to default on logging disable
				 */
				handle_enable: function () {
					var $toggle = Sfb.Logs.Toggler.get_elements(),
						state = $toggle.is(":checked"),
						dflt = $toggle.attr('data-default')
					;

					if (state) $(".snapshot-settings.log-levels").show();
					else {
						$(".snapshot-settings.log-levels")
							.hide()
							.find('input[type="radio"]')
							.attr("checked", false)
							.end()
							.find('input[type="radio"][value="' + dflt + '"]')
							.attr("checked", true)
						;
					}
				},

				/**
				 * Initializes toggler click listener
				 *
				 * @param {object} $el jQuery node
				 */
				initialize_listener: function ($el) {
					$el
						.off("click.snapshot")
						.on("click.snapshot", this.handle_enable)
					;
				},

				/**
				 * Initializes log enable/disable toggle
				 *
				 * @return {Boolean}
				 */
				run: function () {
					this.initialize_listeners();
					this.handle_enable();
					return true;
				}
			}),

			/**
			 * Log viewer toggle
			 *
			 * @type {ElementSelector}
			 */
			Viewer: $.extend({}, ElementSelector, {

				element_selector: 'a[href="#view-log-file"]',

				/**
				 * Refreshing period.
				 *
				 * This is how long the results will stay before we attempt reload.
				 * Set to false-ish value in integer context to disable
				 * results refreshing altogether.
				 *
				 * @type {Number}
				 */
				ttl: 120000,

				/**
				 * Handles log viewer spawning clicks
				 *
				 * @param {Object} e Event
				 *
				 * @return {Boolean}
				 */
				handle_view: function (e) {

					ttl = Sfb.Logs.Viewer.ttl ? parseInt(Sfb.Logs.Viewer.ttl, 10) : 0;

					$('#wps-snapshot-log div.wps-log-box').replaceWith($('<pre class="wps-log-box">'));

					$('#wps-snapshot-log .wps-log-box').html('Loading...<br />');
					$('#wps-snapshot-log').addClass('show');
					var snapshot_href = ajaxurl + '?action=snapshot-full_backup-get_log';

					var snapshot_log_viewer_polling = setInterval(function ajaxCall() {
						if ($('#wps-snapshot-log').length && $('#wps-snapshot-log').is('.show')) {

							snapshot_ajax_hdl_xhr = jQuery.ajax({
								type: 'POST',
								url: snapshot_href,
								cache: false,
								dataType: 'html',
								error: function (jqXHR, textStatus, errorThrown) {
									clearInterval(snapshot_log_viewer_polling);
								},
								success: function (reply_data) {
									if ($(reply_data).length > 0) {
										$('#wps-snapshot-log .wps-log-box').html($(reply_data).text());
										$('#wps-snapshot-log .wps-log-box').scrollTop($('#wps-snapshot-log .wps-log-box')[0].scrollHeight);
									} else {
										$('#wps-snapshot-log .wps-log-box').html(reply_data);
									}
								}
							});
						} else {
							clearInterval(snapshot_log_viewer_polling);
						}
					}(), ttl);

					return Sfb.Util.stop_prop(e);
				},

				/**
				 * Initializes log viewer click listener
				 *
				 * @param {object} $el jQuery node
				 */
				initialize_listener: function ($el) {
					$el
						.off("click.snapshot")
						.on("click.snapshot", this.handle_view)
					;
				},

				/**
				 * Initializes log enable/disable toggle
				 *
				 * @return {Boolean}
				 */
				run: function () {
					this.initialize_listeners();
					return true;
				}
			}),

			/**
			 * Initializes the whole log section JS
			 *
			 * @return {Boolean}
			 */
			run: function () {
				Sfb.Logs.Toggler.run();
				Sfb.Logs.Viewer.run();

				return true;
			}
		};

		window.Sfb = Sfb;

		$(function () {
			Sfb.Notice.run();
			Sfb.Logs.run();
			Sfb.ManualBackup.run();
		});

	};
})(jQuery);
