window.SS_UTILS = {};
window.SS_PAGES = {};

(function ($) {
	$(document).ready(function () {

		// Place WPMUDEV Notice above the Title box
		var wdpunNotice = $('.wdpun-notice').detach();
		wdpunNotice.appendTo('section#header');

		// Fire page-specific init JS
		$.each(document.body.className.replace(/-/g, '_').split(/\s+/), function (i, classnm) {
			if (typeof(SS_PAGES[classnm]) === 'function') {
				SS_PAGES[classnm]();
			}
		});

		// Events for global/shared elements like popups
		$('#view-snapshot-key,#view-snapshot-key-2').on('click', function (e) {
			e.preventDefault();
			var $me = $(this),
				content = $me.html(),
				working = ((window.snapshot_messages || {}).working) || content
			;

			if ($me.is(".has-key")) return show_key_popup();

			$me.html(working);

			$.post(ajaxurl, {
				action: "snapshot-full_backup-exchange_key"
			})
				.done(function (r) {
					if (!(r || {}).success) return show_key_popup((r || {}).data);
					return window.location.reload();
				})
				.fail(show_key_popup)
				.always(function () {
					$me.html(content);
				})
			;
		});

		function show_key_popup () {
			$("#wps-snapshot-key").addClass("show");
			$("body").addClass("wps-popup-modal-active");
		}

		// Events for the specific Activate Managed Backups button the Backups page and the retry text.
		$('#view-snapshot-key-automatic,#wps-error-connecting a').on('click', function (e) {
			e.preventDefault();
			// Hide the notice again, in case we have hit the retry option.
			$("#wps-snapshot-key-notice").removeClass("show");
			$("#view-snapshot-key-automatic").removeClass("hidden");
			var $me = $('#view-snapshot-key-automatic'),
				content = $me.html(),
				working = ((window.snapshot_messages || {}).working) || content
			;

			$me.html(working);

			$.post(ajaxurl, {
				action: "snapshot-full_backup-exchange_key"
			})
				.done(function (r) {
					if (!(r || {}).success) return show_key_notice((r || {}).data);
					return window.location.reload();
				})
				.fail(show_key_notice)
				.always(function () {
					$me.html(content);
				})
			;
		});

		function show_key_notice () {
			$("#wps-snapshot-key-notice").addClass("show");
			$("#wps-snapshot-key-notice .wps-automatic-try.error").removeClass("hidden");
			$("#view-snapshot-key-automatic").addClass("hidden");
		}

		$('#secret-key-notice form').on('submit', function (e) {
			e.preventDefault();
			var data = $(this).serializeArray();
			data.push({name: 'action', value: 'snapshot_save_key'});
			data.push({name: 'security', value: $(this).data('security')});
			$('#get-snapshot-key-notice').addClass('disabled-get-key');
			$('#secret-key-notice').addClass('wps-processing');
			$('#wps-snapshot-key-notice .wps-automatic-try.error').addClass('hidden');

			// Prevent the disabled Save Key button from being clicked on. Needs separate styling.
			$('.disabled-get-key').on('click', function (e) {
				e.preventDefault();
			});

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				dataType: 'json',
				success: function (result) {
					$('#secret-key-notice').removeClass('wps-processing');
					if (!result.success) {
						$('.wps-get-snapshot-key').addClass('hidden');
						$('#secret-key-notice form button[type="submit"]').html("Try Again");
						$("#wps-snapshot-key-notice .wps-manual-try.error").removeClass("hidden");
					} else {
						window.location.reload(false);
					}
				},
				error: function () {
					$('#secret-key-notice').removeClass('wps-processing');
					$('.wps-get-snapshot-key').addClass('hidden');
					$('#secret-key-notice form button[type="submit"]').html("Try Again");
					$("#wps-snapshot-key-notice .wps-manual-try.error").removeClass("hidden");
				}
			});
		});

		$('.wps-icon.i-close').on('click', function (e) {
			$("#wps-snapshot-key").removeClass("show");
			$("body").removeClass("wps-popup-modal-active");
		});

		$('.wps-popup-close').on('click', function (e) {
			$(this).parents(".wps-popup-modal").removeClass('show');
			$("body").removeClass("wps-popup-modal-active");
		});
		
		if ($('.wps-popup-modal').hasClass("show")){
			$('body').addClass("wps-popup-modal-active");
		} else {
			$('body').removeClass("wps-popup-modal-active");
		}

		$('select.bulk-action-selector-top').on('change', function (e) {
			var selected_bulk_option = $(this).val();
			$('select.bulk-action-selector-top').not(this).val(selected_bulk_option).trigger('wpmu:change');
		});

		var secretKeyPopin = $('#wps-snapshot-key');
		$('#wps-snapshot-key form').on('submit', function (e) {
			e.preventDefault();
			var data = $(this).serializeArray();
			data.push({name: 'action', value: 'snapshot_save_key'});
			data.push({name: 'security', value: $(this).data('security')});
			$('.wps-snapshot-popin-content', secretKeyPopin).addClass('hidden');
			$('.wps-snapshot-popin-content-step-2', secretKeyPopin).removeClass('hidden');
			$('.wps-snapshot-key.wpmud-box-gray', secretKeyPopin).addClass('wps-processing');

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				dataType: 'json',
				success: function (result) {
					$('.wps-snapshot-key.wpmud-box-gray', secretKeyPopin).removeClass('wps-processing');
					$('.wps-snapshot-popin-content', secretKeyPopin).addClass('hidden');
					if (!result.success) {
						$('.wps-snapshot-popin-content-step-3', secretKeyPopin).removeClass('hidden');
					} else {
						$('.wps-snapshot-popin-content-step-4', secretKeyPopin).removeClass('hidden');
						//reload and display floating message
						var loc = location.href;
						loc += loc.indexOf("?") === -1 ? "?" : "&";
						location.href = loc + $.param({message: 'success-snapshot-key'});
					}
				},
				error: function () {
					$('.wps-snapshot-key.wpmud-box-gray', secretKeyPopin).removeClass('wps-processing');
					$('.wps-snapshot-popin-content', secretKeyPopin).addClass('hidden');
					$('.wps-snapshot-popin-content-step-3', secretKeyPopin).removeClass('hidden');
				}
			});
		});

		// Remove 'try managed backups' box.
		$('#disable-notif').on('click', function (e) {
			e.preventDefault();
			var data = {
				security: $(this).data('security'),
				action: 'snapshot_disable_notif_ajax'
			};

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				dataType: 'json',
				success: function () {
					$('.try-managed-backups-box').remove();
				}
			});
		});

		// Dismiss floating notifications when they are clicked or after two seconds
		$('.wps-message').on('click', function (e) {
			$(this).fadeOut();
		}).delay(2000).fadeOut();

		// Prevent disabled buttons from being clicked on
		$('.button-disabled').on('click', function (e) {
			e.preventDefault();
		});


		$('.wpmud-box-tab-title.can-toggle').on('click',function(e){
			e.preventDefault();
			$(this).parents('.wpmud-box-tab').toggleClass('open');
		});

		$('.snapshot-install-v4-button').on('click', function () {
			var form = $(this).closest('form');
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				beforeSend: function () {
					form.find('.button').prop('disabled', true);
					form.find('.button').addClass('button-disabled');
				},
				complete: function () {
					form.find('.button').prop('disabled', false);
					form.find('.button').removeClass('button-disabled');
				},
				data: {
					action: 'snapshot_admin_notice_v4_install',
					_wpnonce: $('#_wpnonce-snapshot_admin_notice').val()
				},
				success: function (response) {
					if (response.success) {
						if (response.data.redirect_to) {
							window.location.href = response.data.redirect_to;
						}
					} else {
						if (response.data && typeof response.data === 'object') {
							for (var key in response.data) {
								console.error(key, response.data[key]);
							}
						}
					}
				}
			});

			return false;
		});

		$('.snapshot-upgrade-to-v4-modal-dismiss').on('click', function () {
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'snapshot_upgrade_to_v4_modal_dismiss',
					_wpnonce: $('#_wpnonce-snapshot_admin_notice').val()
				}
			});
			$(this).closest('.wps-popup-modal').removeClass('show');
			return false;
		});

		$('.snapshot-upgrade-to-v4-notice-dismiss').on('click', function () {
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'snapshot_upgrade_to_v4_notice_dismiss',
					_wpnonce: $('#_wpnonce-snapshot_admin_notice').val()
				}
			});
			$('.upgrade-to-v4-notice').fadeOut('fast');
		});

	});

})(jQuery);
