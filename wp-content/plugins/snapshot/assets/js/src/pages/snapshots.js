;(function ($) {

	// page ID or "slug"
	window.SS_PAGES.snapshot_page_snapshot_pro_snapshots = function () {

		// Available Snapshots
		// Menu options
		$('.wps-menu-dots').each(function () {
			var $dots = $(this),
				$menu = $dots.parent('.wps-menu'),
				$all_menu = $('.wps-menu').not($menu);

			$dots.on('click', function () {
				$all_menu.removeClass('open');
				$menu[$menu.hasClass('open') ? 'removeClass' : 'addClass']('open');
			});
		});

		$("#my-snapshot-all").on('change', function () {
			$('input[id^="my-snapshot"]').attr('checked', $(this).is(':checked'));
		});

		$('#wps-snapshot-log').find('.wps-popup-close').on('click', function (e) {
			e.preventDefault();

			$('#wps-snapshot-log').removeClass('show');
		});

		$("#wps-snapshot-log-view").on('click', function (e) {
			e.preventDefault();

			$('#wps-snapshot-log .wps-log-box').html(snapshot_messages.loading + '<br />');
			$('#wps-snapshot-log').addClass('show');
			var snapshot_href_params = tb_parseQuery($("#wps-snapshot-log").attr('data-ajax-src'));

			var snapshot_log_position = 0;
			var snapshot_log_viewer_polling = setInterval(function () {
				if ($('#wps-snapshot-log').length && $('#wps-snapshot-log').is('.show')) {

					var data = {
						action: 'snapshot_view_log_ajax',
						'snapshot-item': snapshot_href_params['snapshot-item'],
						'snapshot-data-item': snapshot_href_params['snapshot-data-item'],
						'snapshot-log-position': snapshot_log_position,
						'snapshot-noonce-field': snapshot_href_params['snapshot-noonce-field']
					};

					snapshot_ajax_hdl_xhr = jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						cache: false,
						data: data,
						dataType: 'json',
						error: function (jqXHR, textStatus, errorThrown) {
							clearInterval(snapshot_log_viewer_polling);
						},
						success: function (reply_data) {
							if ( ( 0 === reply_data.payload.length ) || ( reply_data.position >= reply_data.filesize ) ) {
								clearInterval(snapshot_log_viewer_polling);
							} else {
								if (reply_data !== undefined && reply_data.payload !== undefined) {
									if (snapshot_log_position === 0) {
										$('#wps-snapshot-log .wps-log-box').html(reply_data.payload);
									} else {
										$('#wps-snapshot-log .wps-log-box').append(reply_data.payload);
									}

									if (snapshot_href_params.live == '1') {
										if ($('#wps-snapshot-log').length) {
											$('#wps-snapshot-log .wps-log-box').scrollTop($('#wps-snapshot-log .wps-log-box')[0].scrollHeight);
										}
									} else {
										//clearInterval(snapshot_log_viewer_polling);
									}
								}

								if (reply_data !== undefined && reply_data.position !== undefined) {
									if (snapshot_log_position != reply_data.position) {
										snapshot_log_position = reply_data.position;
									}
								}								
							}

						}
					});
				} else {
					clearInterval(snapshot_log_viewer_polling);
				}
			}, 1000);
		});

		if (!$('body').is('.snapshot_page_snapshot_pro_snapshot_create')) {
			window.SS_PAGES.snapshot_page_snapshot_pro_snapshot_create();
		}
	};

})(jQuery);