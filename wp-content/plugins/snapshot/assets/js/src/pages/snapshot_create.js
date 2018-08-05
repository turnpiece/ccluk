(function ($) {
	// page ID or "slug"
	window.SS_PAGES.snapshot_page_snapshot_pro_snapshot_create = function () {

		jQuery("input[name='frequency']").change(function () {
			var backup_frequency_options = jQuery("input[name='frequency']:checked").val();
			if ((backup_frequency_options === "once")) {
				jQuery('#snapshot-immediate').attr('disabled', false).attr('checked', true);
				jQuery('#snapshot-interval').attr('disabled', true);
				jQuery('div#snapshot-schedule-options-container').slideUp('fast');
				jQuery('#snapshot-add-update-submit').text(jQuery('#snapshot-add-update-submit').data('title-save-and-run'));
			} else {
				jQuery('#snapshot-immediate').attr('disabled', true).attr('checked', false);
				jQuery('#snapshot-interval').attr('disabled', false);
				jQuery('div#snapshot-schedule-options-container').slideDown('slow');
				if (!jQuery('#checkbox-run-backup-now').is(':checked')) {
					jQuery('#snapshot-add-update-submit').text(jQuery('#snapshot-add-update-submit').data('title-save-only'));
				}

			}
		}).change();

		jQuery("#checkbox-run-backup-now").change(function () {
			if ($(this).is(':checked') && jQuery("input[name='frequency']:checked").val() !== "once") {
				jQuery('#snapshot-add-update-submit').text(jQuery('#snapshot-add-update-submit').data('title-save-and-run'));
			} else {
				if (jQuery("input[name='frequency']:checked").val() !== "once") {
					jQuery('#snapshot-add-update-submit').text(jQuery('#snapshot-add-update-submit').data('title-save-only'));
				}
			}
		}).change();

		jQuery('[name=snapshot-destination]').change(function () {
			var destination_type = jQuery('[name=snapshot-destination]:checked').attr('data-destination-type');
			if (destination_type == "dropbox") {
				jQuery('input#snapshot-destination-sync-mirror').attr('disabled', false);
			} else {
				jQuery('input#snapshot-destination-sync-mirror').attr('disabled', 'disabled');
				jQuery('input#snapshot-destination-sync-archive').attr('checked', 'checked');
			}
		}).change();

		/* Handler for Backup/Restore user Aborts */

		var snapshot_ajax_hdl_xhr = null;
		var snapshot_ajax_user_aborted = false;

		function snapshot_button_abort_proc() {
			snapshot_ajax_hdl_xhr !== null && snapshot_ajax_hdl_xhr.abort();
			snapshot_ajax_user_aborted = true;

			jQuery('#wps-build-error').removeClass('hidden').find(".wps-auth-message p").html(snapshot_messages.snapshot_aborted);
			jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
			jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
			jQuery("#wps-build-progress").addClass("hidden");

			return false;
		}

		jQuery("#wps-show-full-log").on('click', function (e) {
			e.preventDefault();
			var $self = jQuery(this);
			var log = jQuery('#wps-log');
			if (log.is('.hidden')) {
				$self.text($self.attr('data-wps-hide-title'));
				log.removeClass('hidden');
			} else {
				$self.text($self.attr('data-wps-show-title'));
				log.addClass('hidden');
			}
		});

		jQuery("#wps-build-error-again").on('click', function (e) {
			e.preventDefault();
			jQuery('#wps-build-error').addClass("hidden");
			jQuery('#wps-build-progress').removeClass("hidden");
			jQuery(".wpmud-box-title .wps-title-result").addClass("hidden");
			jQuery(".wpmud-box-title .wps-title-progress").removeClass("hidden");
			jQuery("form#snapshot-add-update").submit();
		});

		jQuery("#wps-build-error-back").on('click', function (e) {
			e.preventDefault();
			jQuery('#wps-build-error').addClass("hidden");
			jQuery('#wps-build-progress').removeClass("hidden");
			jQuery('#snapshot-add-update').removeClass('hidden');
			jQuery('#container.wps-page-builder').addClass('hidden');
			jQuery(".wpmud-box-title .wps-title-result").addClass("hidden");
			jQuery(".wpmud-box-title .wps-title-progress").removeClass("hidden");
		});


		jQuery('#wps-cancel').off().on('click', function (e) {
			e.preventDefault();
			snapshot_button_abort_proc();

			$('html,body').animate({
				scrollTop: $("#container.wps-page-builder").offset().top
			}, 'slow');

		});


		$( '#container.wps-page-builder' ).off('click.abort').on( 'click.abort', 'a.snapshot-button-abort', function(e) {
			e.preventDefault();
			snapshot_button_abort_proc();

			$('html,body').animate({
				scrollTop: $("#container.wps-page-builder").offset().top
			}, 'slow');

		});

		jQuery('.snapshot-tables-option, .snapshot-files-option').on('change', function () {

			if (jQuery('#snapshot-tables-option-none:checked').length === 1 && jQuery('#snapshot-files-option-none:checked').length === 1) {
				/* Show a warning and disable the submit button show a warning */
				jQuery("#snapshot-ajax-error").html('<p>' + snapshot_messages.no_files_tables + '</p>').show();
				jQuery("#snapshot-add-update-submit").addClass("button-disabled");
			} else {
				jQuery("#snapshot-ajax-error").html('').hide();
				jQuery("#snapshot-add-update-submit").removeClass("button-disabled");
			}
		});

		/* Used on the 'Add New Snapshot' panel. Handles the form submit to backup one table per request. Seems this was taking too long on some servers. */
		jQuery("form#snapshot-add-update").off().submit(function (e) {
			snapshot_ajax_user_aborted = false;

			jQuery('[id^="snapshot-item-table-"]').remove();
			jQuery('#snapshot-item-file').remove();

			var snapshot_form_files_sections = [];
			var snapshot_form_files_option = jQuery('input.snapshot-files-option:checked').val();
			var snapshot_form_destination_sync = 'archive';
			var snapshot_form_files_ignore = '';

			var security = jQuery(':hidden#snapshot-ajax-nonce').val();

			if (snapshot_form_files_option !== "none") {
				if (snapshot_form_files_option === "all") {

				} else if (snapshot_form_files_option === "selected") {
					jQuery('input.snapshot-backup-sub-options:checked', this).each(function () {
						snapshot_form_files_sections[snapshot_form_files_sections.length] = jQuery(this).attr('value');
					});

					// Do we have tables to process?
					if (snapshot_form_files_sections.length === 0) {

						/* If the user didn't select any sub=options show this warning */
						jQuery("#snapshot-ajax-warning").html('<p>' + snapshot_messages.no_files_selected + '</p>').show();
						return false;
					}
				}
				snapshot_form_files_ignore = jQuery('textarea#snapshot-files-ignore').val();

				snapshot_form_destination_sync = jQuery('input.snapshot-destination-sync:checked').val();
			}

			/* Build and array of the checked tables to backup */
			var snapshot_form_tables_array = [];
			var snapshot_form_tables_option = jQuery('input.snapshot-tables-option:checked').val();

			if (snapshot_form_tables_option === "selected") {

				jQuery('input.snapshot-table-item:checked', this).each(function () {
					var cb_value = jQuery(this).attr('value');
					snapshot_form_tables_array[snapshot_form_tables_array.length] = cb_value;
				});

				// Do we have tables to process?
				if (snapshot_form_tables_array.length === 0) {

					/* If the user didn't select any tables show this warning */
					jQuery("#snapshot-ajax-warning").html('<p>' + snapshot_messages.no_tables_selected + '</p>').show();
					return false;
				}

			} else if (snapshot_form_tables_option === "all") {
				jQuery('input.snapshot-table-item', this).each(function () {
					snapshot_form_tables_array[snapshot_form_tables_array.length] = jQuery(this).attr('value');
				});
			}

			if (snapshot_form_files_option === "none" && snapshot_form_tables_option === "none") {
				/* If the user didn't select any files or tables show a warning */
				jQuery("#snapshot-ajax-error").html('<p>' + snapshot_messages.no_files_tables + '</p>').show();
				return false;
			}

			var snapshot_form_item;
			var snapshot_form_action = jQuery('input#snapshot-action', this).val();
			if (snapshot_form_action === "add" || snapshot_form_action === "update") {
				snapshot_form_item = jQuery('input#snapshot-item', this).val();
			}
			var snapshot_form_data_item = jQuery('input#snapshot-data-item', this).val();

			if (snapshot_form_item === '') {
				jQuery("#snapshot-ajax-warning").html('<p>' + snapshot_messages.missing_timekey + '</p>').show();
				return false;
			}

			/* If the interval is not empty then the user is attempting to set a scheduled snapshot. So return true to allow the form submit */
			var snapshot_form_frequency = jQuery('input[name=frequency]:checked', this).val();
			var snapshot_form_interval = (snapshot_form_frequency !== 'once') ? jQuery('select#snapshot-interval', this).val() : "immediate";
			var snapshot_form_run_now = jQuery('#checkbox-run-backup-now', this).is(':checked');

			if (!snapshot_form_run_now && snapshot_form_interval !== "immediate") {
				return true;
			}

			e.preventDefault();

			var snapshot_form_archive_count = jQuery('input#snapshot-archive-count', this).val();

			var snapshot_form_destination = jQuery('[name=snapshot-destination]:checked', this).val();
			var snapshot_destination_local = jQuery('select#snapshot-destination-local', this).val();
			var snapshot_form_destination_directory = jQuery('input#snapshot-destination-directory', this).val();


			/* From the form grab the Name and Notes field values. */
			var snapshot_form_blog_id = 0;
			if (jQuery('select#snapshot-blog-id', this).length > 0) {
				snapshot_form_blog_id = jQuery('select#snapshot-blog-id', this).val();
			} else {
				snapshot_form_blog_id = jQuery('input#snapshot-blog-id', this).val();
			}

			var snapshot_form_name = jQuery('input#snapshot-name', this).val();
			var snapshot_form_notes = jQuery('textarea#snapshot-notes', this).val();
			//var snapshot_form_files_option		= jQuery('input:radio["name=snapshot-files-option"]:checked').val();
			snapshot_form_files_option = jQuery('input:radio[name=snapshot-files-option]:checked', this).val();

			var snapshot_clean_remote = jQuery("#snapshot-clean-remote", this).is(":checked") ? '1' : '';

			// Clear out the progress text and warning containers
			jQuery('#snapshot-ajax-warning').html('').hide();


			/* Hide the form while processing */
			jQuery('#snapshot-add-update').addClass('hidden');
			var snapshot_item_html;

			for (var table_key in snapshot_form_tables_array) {
				if (!snapshot_form_tables_array.hasOwnProperty(table_key)) continue;
				var table_name = snapshot_form_tables_array[table_key];

				snapshot_item_html = $('<tr id="snapshot-item-table-' + table_name + '"></tr>')
					.append($('#wps-log-process-template').html())
					.find('.wps-log-process.name').html(table_name)
					.end();

				jQuery('tr#wps-log-process-finish').before(snapshot_item_html);

			}

			if (snapshot_form_files_option === 'all' || snapshot_form_files_option === 'selected') {

				snapshot_item_html = $('<tr id="snapshot-item-file"></tr>')
					.append($('#wps-log-process-template').html())
					.find('.wps-log-process.name').html('Files: <span class="snapshot-filename" style="font-style: italic;"></span>')
					.end();

				jQuery('tr#wps-log-process-finish').before(snapshot_item_html);
			}

			// Add/Show the progerss bars.
			jQuery('#container.wps-page-builder').removeClass('hidden');

			var globalProgressTotal = snapshot_form_tables_array.length + 2;
			var globalProgressNow = 0;

			var tablesArray = [];
			var filesArray = [];

			function snapshot_backup_tables_proc(proc_action, idx) {
				var table_name;
				var data;
				var snapshot_percent;

				if (proc_action === 'init') {

					table_name = proc_action;

					data = {
						action: 'snapshot_backup_ajax',
						'snapshot-proc-action': proc_action,
						'snapshot-action': snapshot_form_action,
						'snapshot-item': snapshot_form_item,
						'snapshot-data-item': snapshot_form_data_item,
						'snapshot-blog-id': snapshot_form_blog_id,
						'snapshot-name': snapshot_form_name,
						'snapshot-notes': snapshot_form_notes,
						'snapshot-files-option': snapshot_form_files_option,
						'snapshot-files-sections': snapshot_form_files_sections,
						'snapshot-files-ignore': snapshot_form_files_ignore,
						'snapshot-tables-option': snapshot_form_tables_option,
						'snapshot-tables-array': snapshot_form_tables_array,
						'snapshot-interval': snapshot_form_interval,
						'snapshot-archive-count': snapshot_form_archive_count,
						'snapshot-destination': snapshot_form_destination,
						'snapshot-store-local': snapshot_destination_local,
						'snapshot-destination-directory': snapshot_form_destination_directory,
						'snapshot-destination-sync': snapshot_form_destination_sync,
						'snapshot-clean-remote': snapshot_clean_remote,
						'security': security,
					};

					jQuery('[id^=snapshot-interval-offset]').each(function () {
						data[this.name] = this.value;
					});

					snapshot_ajax_hdl_xhr = jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: data,
						dataType: 'json',
						error: function (jqXHR, textStatus, errorThrown) {
							if (jqXHR.responseText !== false && jqXHR.responseText !== '') {
								jQuery("#wps-build-error .wps-auth-message p").html(jqXHR.responseText);
								jQuery("#wps-build-error").removeClass("hidden");
								jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
								jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
								jQuery("#wps-build-progress").addClass("hidden");
							} else {
								jQuery("#wps-build-error .wps-auth-message p").html('<p>AAA ' + snapshot_messages.snapshot_failed + '</p>');
								jQuery("#wps-build-error").removeClass("hidden");
								jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
								jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
								jQuery("#wps-build-progress").addClass("hidden");

							}
						},
						success: function (reply_data) {

							if (reply_data === undefined || reply_data.errorStatus === undefined) {
								jQuery("#wps-build-error").removeClass("hidden").find(".wps-auth-message p").html('<p>BBB ' + snapshot_messages.snapshot_failed + '</p>');
								jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
								jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
								jQuery("#wps-build-progress").addClass("hidden");


							} else if (reply_data.errorStatus !== false) {
								if (reply_data.errorText === undefined) {
									jQuery("#wps-build-error").removeClass("hidden").find(".wps-auth-message p").html('<p>CCC ' + snapshot_messages.snapshot_failed + '</p>');
									jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
									jQuery("#wps-build-progress").addClass("hidden");
								} else {
									jQuery("#snapshot-ajax-error").append(reply_data.errorText);
									jQuery("#wps-build-error").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
									jQuery("#wps-build-progress").addClass("hidden");
								}

							} else if (reply_data.errorStatus === false || snapshot_ajax_user_aborted === false) {
								var table_name = proc_action; // Init
								jQuery('#container.wps-page-builder #wps-log-process-init .wps-loading-status .wps-loading-bar span').width('100%');
								jQuery('#container.wps-page-builder #wps-log-process-init .wps-loading-status .wps-loading-number').html('100%');
								jQuery('#container.wps-page-builder #wps-log-process-init .wps-loading-status .wps-loading-bar .wps-loader').addClass('done');
								jQuery('#container.wps-page-builder #wps-log-process-init .wps-loading-status').removeClass('wps-spinner');
								jQuery('#container.wps-page-builder #wps-log-process-init .snapshot-button-abort').addClass('hidden');
								globalProgressNow++;

								var globalProgressNowPercent = Math.min(100, Math.round((globalProgressNow * 100) / globalProgressTotal));

								jQuery("#wps-build-progress .wps-total-status .wps-loading-number").html(globalProgressNowPercent + '%');
								jQuery("#wps-build-progress .wps-total-status .wps-loading-bar span").width(globalProgressNowPercent + '%');


								/* IF no error we start on the files. */
								if (snapshot_ajax_user_aborted === false) {

									if (reply_data.files_data !== undefined) {
										filesArray = reply_data.files_data;
										globalProgressTotal += filesArray.length;
									}

									if (reply_data.table_data !== undefined) {
										tablesArray = reply_data.table_data;

										//Removing tables row from progress log if user do not have the right to backup
										var tablesString = JSON.stringify(reply_data.table_data);
										if(tablesString){
											for (var table_key in snapshot_form_tables_array) {
												if (!snapshot_form_tables_array.hasOwnProperty(table_key)) continue;
												var table_name = snapshot_form_tables_array[table_key];
												if(tablesString.indexOf(table_name) === -1) {
													$('#snapshot-item-table-' + table_name).remove();
												}
											}
										}
										snapshot_backup_tables_proc('table', 0);
									}

									if (reply_data.MEMORY !== undefined) {
										if (reply_data.MEMORY.memory_limit !== undefined) {
											jQuery('#snapshot-memory-info').find('.log-memory.number').html(reply_data.MEMORY.memory_limit);
										}
										if (reply_data.MEMORY.memory_usage_current !== undefined) {
											jQuery('#snapshot-memory-info .log-usage.number').html(reply_data.MEMORY.memory_usage_current);
										}
										if (reply_data.MEMORY.memory_usage_peak !== undefined) {
											jQuery('#snapshot-memory-info .log-peak.number').html(reply_data.MEMORY.memory_usage_peak);
										}
									}
								}
							}
						}
					});

				} else if (proc_action === 'table') {

					var table_idx = parseInt(idx);
					var table_count = table_idx + 1;

					/* If we reached the end of the tables send the finish. */
					if (table_count > tablesArray.length) {
						if (filesArray.length > 0) {
							snapshot_backup_tables_proc('file', 0);
						} else {
							snapshot_backup_tables_proc('finish', 0);
						}
						return;
					} else {

						var table_data = tablesArray[table_idx];
						table_name = table_data.table_name;

						jQuery('#container.wps-page-builder #snapshot-item-table-' + table_name + ' a.snapshot-button-abort').removeClass('hidden');
						jQuery('#container.wps-page-builder #snapshot-item-table-' + table_name + ' .wps-spinner').removeClass('hidden');

						data = {
							action: 'snapshot_backup_ajax',
							'snapshot-proc-action': proc_action,
							'snapshot-action': snapshot_form_action,
							'snapshot-item': snapshot_form_item,
							'snapshot-data-item': snapshot_form_data_item,
							'snapshot-blog-id': snapshot_form_blog_id,
							'snapshot-table-data-idx': table_idx,
							'security': security,
						};

						snapshot_ajax_hdl_xhr = jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: data,
							dataType: 'json',
							error: function (jqXHR, textStatus, errorThrown) {
								if (jqXHR.responseText !== false && jqXHR.responseText !== '') {
									jQuery("#wps-build-error .wps-auth-message p").html(jqXHR.responseText);
									jQuery("#wps-build-error").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
									jQuery("#wps-build-progress").addClass("hidden");
								} else {
									jQuery("#wps-build-error .wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
									jQuery("#wps-build-error").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
									jQuery("#wps-build-progress").addClass("hidden");
								}
							},
							success: function (reply_data) {

								if (reply_data === undefined || reply_data.errorStatus === undefined) {
									jQuery("#wps-build-error").removeClass("hidden").find(".wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
									jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
									jQuery("#wps-build-progress").addClass("hidden");
								} else if (reply_data.errorStatus !== false) {
									if (reply_data.errorText === undefined) {
										jQuery("#wps-build-error").removeClass("hidden").find(".wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
										jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
										jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
										jQuery("#wps-build-progress").addClass("hidden");
									} else {
										jQuery("#snapshot-ajax-error").append(reply_data.errorText);
										jQuery("#wps-build-error").removeClass("hidden");
										jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
										jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
										jQuery("#wps-build-progress").addClass("hidden");
									}

								} else if (reply_data.errorStatus === false || snapshot_ajax_user_aborted === false) {

									if (reply_data.table_data === undefined) {
										if (reply_data.errorText !== undefined) {
											jQuery("#snapshot-ajax-error").append(reply_data.errorText);
											jQuery("#wps-build-error").removeClass("hidden");
											jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
											jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
											jQuery("#wps-build-progress").addClass("hidden");
										} else {
											jQuery("#wps-build-error .wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
											jQuery("#wps-build-error").removeClass("hidden");
											jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
											jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
											jQuery("#wps-build-progress").addClass("hidden");
										}

									} else {
										table_data = reply_data.table_data;
										var snapshot_percent;

										var rows_complete = parseInt(table_data.rows_start) + parseInt(table_data.rows_end);

										if (rows_complete > 0) {
											snapshot_percent = Math.ceil((rows_complete / table_data.rows_total) * 100);

											jQuery('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .wps-loading-status .wps-loading-bar span').width(snapshot_percent + '%');
											jQuery('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .wps-loading-status .wps-loading-number').html(snapshot_percent + '%');

										} else {

											snapshot_percent = 100;
											jQuery('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .wps-loading-status .wps-loading-bar span').width(snapshot_percent + '%');
											jQuery('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .wps-loading-status .wps-loading-number').html(snapshot_percent + '%');
										}

										// Are we at 100%? Hide the Abort button
										if (snapshot_percent === 100) {
											jQuery('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .snapshot-button-abort').hide();
											jQuery('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .wps-spinner').hide();
											jQuery('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .wps-loading-status .wps-loading-bar .wps-loader').addClass('done');

											globalProgressNow++;

											var globalProgressNowPercent = Math.min(100, Math.ceil((globalProgressNow * 100) / globalProgressTotal));

											jQuery("#wps-build-progress .wps-total-status .wps-loading-number").html(globalProgressNowPercent + '%');
											jQuery("#wps-build-progress .wps-total-status .wps-loading-bar span").width(globalProgressNowPercent + '%');

										}

										if (reply_data.MEMORY !== undefined) {
											if (reply_data.MEMORY.memory_limit !== undefined) {
												jQuery('#snapshot-memory-info span.memory-limit').html(reply_data.MEMORY.memory_limit);
											}
											if (reply_data.MEMORY.memory_usage_current !== undefined) {
												jQuery('#snapshot-memory-info span.memory-usage').html(reply_data.MEMORY.memory_usage_current);
											}
											if (reply_data.MEMORY.memory_usage_peak !== undefined) {
												jQuery('#snapshot-memory-info span.memory-peak').html(reply_data.MEMORY.memory_usage_peak);
											}
										}

									}
									snapshot_backup_tables_proc('table', table_count);
								}
							}
						});
					}
				} else if (proc_action === "file") {

					var file_idx = parseInt(idx);
					var file_count = file_idx + 1;
					table_name = proc_action;

					/* If we reached the end of the tables send the finish. */
					if (file_count > filesArray.length) {
						snapshot_backup_tables_proc('finish', 0);
						return false;

					} else {

						var file_data_key = filesArray[file_idx];
						jQuery('#snapshot-item-' + table_name + ' .snapshot-filename').html(": " + file_data_key);

						jQuery('#container.wps-page-builder #snapshot-item-' + table_name + ' a.snapshot-button-abort').removeClass('hidden');
						jQuery('#container.wps-page-builder #snapshot-item-' + table_name + ' .wps-spinner').removeClass('hidden');

						data = {

							action: 'snapshot_backup_ajax',
							'snapshot-proc-action': proc_action,
							'snapshot-action': snapshot_form_action,
							'snapshot-item': snapshot_form_item,
							'snapshot-data-item': snapshot_form_data_item,
							'snapshot-blog-id': snapshot_form_blog_id,
							'snapshot-file-data-key': file_data_key,
							'security': security,
						};

						snapshot_ajax_hdl_xhr = jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: data,
							dataType: 'json',
							error: function (jqXHR, textStatus, errorThrown) {
								if (jqXHR.responseText !== false && jqXHR.responseText !== '') {
									jQuery("#wps-build-error .wps-auth-message p").html(jqXHR.responseText);
									jQuery("#wps-build-error").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
									jQuery("#wps-build-progress").addClass("hidden");
								} else {
									jQuery("#wps-build-error .wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
									jQuery("#wps-build-error").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
									jQuery("#wps-build-progress").addClass("hidden");
								}
							},
							success: function (reply_data) {

								if ((reply_data === undefined) || (reply_data.errorStatus === undefined)) {
									jQuery("#wps-build-error").removeClass('hidden').find('.wps-auth-message p').html('<p>' + snapshot_messages.snapshot_failed + '</p>');
									jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
									jQuery("#wps-build-progress").addClass("hidden");
								} else if (reply_data.errorStatus !== false) {
									if (reply_data.errorText === undefined) {
										jQuery("#wps-build-error").removeClass('hidden').find('.wps-auth-message p').html('<p>' + snapshot_messages.snapshot_failed + '</p>');
										jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
										jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
										jQuery("#wps-build-progress").addClass("hidden");
									} else {
										jQuery("#snapshot-ajax-error").append(reply_data.errorText);
										jQuery("#wps-build-error").removeClass("hidden");
										jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
										jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
										jQuery("#wps-build-progress").addClass("hidden");
									}

								} else if (reply_data.errorStatus === false || snapshot_ajax_user_aborted === false) {
									var snapshot_percent;

									if (file_count < filesArray.length) {

										snapshot_percent = Math.ceil((file_count / filesArray.length) * 100);

										jQuery('#container.wps-page-builder #snapshot-item-' + table_name + ' .wps-loading-status .wps-loading-bar span').width(snapshot_percent + '%');
										jQuery('#container.wps-page-builder #snapshot-item-' + table_name + ' .wps-loading-status .wps-loading-number').html(snapshot_percent + '%');

									} else {

										globalProgressNow++;

										var globalProgressNowPercent = Math.min(100, Math.ceil((globalProgressNow * 100) / globalProgressTotal));

										jQuery("#wps-build-progress .wps-total-status .wps-loading-number").html(globalProgressNowPercent + '%');
										jQuery("#wps-build-progress .wps-total-status .wps-loading-bar span").width(globalProgressNowPercent + '%');


										snapshot_percent = 100;
										jQuery('#container.wps-page-builder #snapshot-item-' + table_name + ' .wps-loading-status .wps-loading-bar span').width(snapshot_percent + '%');
										jQuery('#container.wps-page-builder #snapshot-item-' + table_name + ' .wps-loading-status .wps-loading-number').html(snapshot_percent + '%');
										jQuery('#container.wps-page-builder #snapshot-item-' + table_name + ' .wps-loading-status .wps-loading-bar .wps-loader').addClass('done');

									}

									// Are we at 100%? Hide the Abort button
									if (snapshot_percent == 100) {
										jQuery('#container.wps-page-builder #snapshot-item-' + table_name + ' .snapshot-button-abort').hide();
										jQuery('#container.wps-page-builder #snapshot-item-' + table_name + ' .wps-spinner').hide();
										jQuery('#snapshot-item-' + table_name + ' .snapshot-filename').html("");
									}

									if (reply_data.MEMORY !== undefined) {
										if (reply_data.MEMORY.memory_limit !== undefined) {
											jQuery('#snapshot-memory-info span.memory-limit').html(reply_data.MEMORY.memory_limit);
										}
										if (reply_data.MEMORY.memory_usage_current !== undefined) {
											jQuery('#snapshot-memory-info span.memory-usage').html(reply_data.MEMORY.memory_usage_current);
										}
										if (reply_data.MEMORY.memory_usage_peak !== undefined) {
											jQuery('#snapshot-memory-info span.memory-peak').html(reply_data.MEMORY.memory_usage_peak);
										}
									}

									snapshot_backup_tables_proc('file', file_count);
								}
							}
						});
					}

				} else if (proc_action == 'finish') {

					table_name = proc_action;
					jQuery('#container.wps-page-builder #snapshot-item-table-' + table_name + ' .wps-loading-status .wps-loading-bar span').width('0%');
					jQuery('#container.wps-page-builder #snapshot-item-table-' + table_name + ' .wps-loading-status .wps-loading-number').html('0%');

					var table_text = jQuery('#container.wps-page-builder #snapshot-item-table-' + table_name + ' .snapshot-text').html();
					jQuery('#container.wps-page-builder #snapshot-item-table-' + table_name + ' .snapshot-text').html(table_text + ' (creating zip archive of tables)');

					data = {
						action: 'snapshot_backup_ajax',
						'snapshot-proc-action': proc_action,
						'snapshot-action': snapshot_form_action,
						'snapshot-item': snapshot_form_item,
						'snapshot-data-item': snapshot_form_data_item,
						'snapshot-blog-id': snapshot_form_blog_id,
						'security': security,
					};

					snapshot_ajax_hdl_xhr = jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: data,
						dataType: 'json',
						error: function (jqXHR, textStatus, errorThrown) {
							if (jqXHR.responseText !== false && jqXHR.responseText !== '') {
								jQuery("#wps-build-error .wps-auth-message p").html(jqXHR.responseText);
								jQuery("#wps-build-error").removeClass("hidden");
								jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
								jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
								jQuery("#wps-build-progress").addClass("hidden");
							} else {
								jQuery("#wps-build-error").removeClass('hidden').find('.wps-auth-message p').html('<p>' + snapshot_messages.snapshot_failed + '</p>');
								jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
								jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
								jQuery("#wps-build-progress").addClass("hidden");
							}
						},
						success: function (reply_data) {

							if ((reply_data === undefined) || (reply_data.errorStatus === undefined)) {
								jQuery("#wps-build-error").removeClass('hidden').find('.wps-auth-message p').html('<p>' + snapshot_messages.snapshot_failed + '</p>');
								jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
								jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
								jQuery("#wps-build-progress").addClass("hidden");
							} else if (reply_data.errorStatus !== false) {
								if (reply_data.errorText === undefined) {
									jQuery("#wps-build-error").removeClass('hidden').find('.wps-auth-message p').html('<p>' + snapshot_messages.snapshot_failed + '</p>');
									jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
									jQuery("#wps-build-progress").addClass("hidden");
								} else {
									jQuery("#snapshot-ajax-error").append(reply_data.errorText);
									jQuery("#wps-build-error").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-result").removeClass("hidden");
									jQuery(".wpmud-box-title .wps-title-progress").addClass("hidden");
									jQuery("#wps-build-progress").addClass("hidden");
								}

							} else if ((reply_data.errorStatus === false) || (snapshot_ajax_user_aborted === false)) {

								globalProgressNow++;

								var globalProgressNowPercent = Math.min(100, Math.ceil((globalProgressNow * 100) / globalProgressTotal));

								jQuery("#wps-build-progress .wps-total-status .wps-loading-number").html(globalProgressNowPercent + '%');
								jQuery("#wps-build-progress .wps-total-status .wps-loading-bar span").width(globalProgressNowPercent + '%');


								var table_name = "finish";
								jQuery('#container.wps-page-builder #wps-log-process-finish .wps-loading-status .wps-loading-bar span').width('100%');
								jQuery('#container.wps-page-builder #wps-log-process-finish .wps-loading-status .wps-loading-number').html('100%');
								jQuery('#container.wps-page-builder #wps-log-process-finish .wps-loading-status .wps-loading-bar .wps-loader').addClass('done');
								jQuery('#container.wps-page-builder #wps-log-process-finish .wps-loading-status').removeClass('wps-spinner');


								if (reply_data.MEMORY !== undefined) {
									if (reply_data.MEMORY.memory_limit !== undefined) {
										jQuery('#snapshot-memory-info span.memory-limit').html(reply_data.MEMORY.memory_limit);
									}
									if (reply_data.MEMORY.memory_usage_current !== undefined) {
										jQuery('#snapshot-memory-info span.memory-usage').html(reply_data.MEMORY.memory_usage_current);
									}
									if (reply_data.MEMORY.memory_usage_peak !== undefined) {
										jQuery('#snapshot-memory-info span.memory-peak').html(reply_data.MEMORY.memory_usage_peak);
									}
								}

								if (reply_data.responseText !== undefined) {
									//jQuery( "#snapshot-ajax-warning" ).html('<p>'+reply_data['responseText']+'</p>');
									//jQuery( "#snapshot-ajax-warning" ).show();
									jQuery("#wps-build-success").removeClass("hidden").find(".wps-auth-message.success").html('<p>' + reply_data.responseText + '</p>');
									jQuery("#wps-build-success-view").attr('href', jQuery("#wps-build-success .wps-auth-message.success").find('a').attr('href'));
									jQuery("#wps-build-error").addClass("hidden");
									jQuery("#wps-build-progress").addClass("hidden");
								}
							}
						}
					});
				}
			}

			/* Make an AJAX call with 'init' to setup the Session backup filename and other items */
			snapshot_backup_tables_proc('init', 0);

			return true;
		});
	};

}(jQuery));
