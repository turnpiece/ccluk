(function ($) {
	window.SS_PAGES.snapshot_page_snapshot_pro_managed_backups_upload = function () {
		var ManagedBackupUpload = {
			aborted: false,
			el: {
				form: $('form#managed-backup-upload'),
				progress_box: $('#managed-backup-upload-progress'),
				abort_state: $('#wps-upload-abort'),
				success_state: $('#wps-upload-success'),
				error_state: $('#wps-upload-error'),
				progress_state: $('#wps-upload-progress'),
				retry_upload_button: $('[href="#retry-upload-after-error"]'),
				cancel_upload_button: $('#wps-cancel')
			},
			set_state: function ($state_el) {
				ManagedBackupUpload.el.progress_state.addClass('hidden');
				ManagedBackupUpload.el.success_state.addClass('hidden');
				ManagedBackupUpload.el.error_state.addClass('hidden');
				ManagedBackupUpload.el.abort_state.addClass('hidden');

				$state_el.removeClass('hidden');
			},
			show_error_message: function (error) {
				ManagedBackupUpload.set_state(ManagedBackupUpload.el.error_state);
				ManagedBackupUpload.el.error_state.find('p').html(error);
			},
			show_success_message: function () {
				ManagedBackupUpload.set_state(ManagedBackupUpload.el.success_state);
			},
			update_progress: function (completed_steps, total_steps) {
				if (
					!$.isNumeric(completed_steps)
					|| !$.isNumeric(total_steps)
					|| parseInt(total_steps) < 1
				) {
					return;
				}
				ManagedBackupUpload.set_state(ManagedBackupUpload.el.progress_state);

				var $progress_container = ManagedBackupUpload.el.progress_box,
					percentage = Math.ceil((completed_steps / total_steps) * 100);

				if (percentage > 100) {
					percentage = 100;
				}

				$progress_container.find('.wps-loading-number').html(percentage + '%');
				$progress_container.find('.wps-loader span').css('width', percentage + '%');
			},
			get_request_data: function (action) {
				var serialized = ManagedBackupUpload.el.form.serializeArray(),
					request_data = {action: action};

				$.each(serialized, function (index, form_field) {
					request_data[form_field['name']] = form_field['value'];
				});
				return request_data;
			},
			upload_file: function () {
				var request_data = ManagedBackupUpload.get_request_data('snapshot-full_backup-upload');

				return $.post(ajaxurl, request_data, function (data) {
					var response = data || {};
					if (response.error) {
						ManagedBackupUpload.show_error_message(response.error);
						return;
					}

					if (response.is_done) {
						ManagedBackupUpload.update_progress(1, 1);
						setTimeout(function () {
							ManagedBackupUpload.show_success_message();
						}, 1000);
					} else if (ManagedBackupUpload.aborted) {
						ManagedBackupUpload.abort_upload();
					} else {
						ManagedBackupUpload.update_progress(response.completed, response.total);
						ManagedBackupUpload.upload_file();
					}
				}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
					ManagedBackupUpload.show_error_message(errorThrown);
				});
			},
			abort_upload: function () {
				return $.post(ajaxurl, ManagedBackupUpload.get_request_data('snapshot-full_backup-abort-upload'), function (data) {
					ManagedBackupUpload.show_error_message(data.error);
				});
			},
			initiate_abort: function () {
				ManagedBackupUpload.set_state(ManagedBackupUpload.el.abort_state);
				ManagedBackupUpload.aborted = true;
			},
			retry_upload: function () {
				ManagedBackupUpload.aborted = false;
				ManagedBackupUpload.update_progress(0, 1);
				ManagedBackupUpload.upload_file();
			},
			handle_form: function (event) {
				event.preventDefault();

				ManagedBackupUpload.el.form.addClass('hidden');
				ManagedBackupUpload.el.progress_box.removeClass('hidden');
				ManagedBackupUpload.upload_file();
			},
			init: function () {
				ManagedBackupUpload.el.form
					.off('submit.snapshot')
					.on('submit.snapshot',
						ManagedBackupUpload.handle_form
					);

				ManagedBackupUpload.el.retry_upload_button
					.off('click.snapshot')
					.on('click.snapshot', function (e) {
						e.preventDefault();
						//ManagedBackupUpload.retry_upload();
						// Reload instead, to null out old upload.
						window.location.reload();
					});

				ManagedBackupUpload.el.cancel_upload_button
					.off('click.snapshot')
					.on('click.snapshot', function (e) {
						e.preventDefault();
						ManagedBackupUpload.initiate_abort();
					});
			}
		};
		ManagedBackupUpload.init();
	};
})(jQuery);
