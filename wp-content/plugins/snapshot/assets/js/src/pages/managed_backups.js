(function ($) {

	// page ID or "slug"
	window.SS_PAGES.snapshot_page_snapshot_pro_managed_backups = function () {

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

		// Fixing dots menu z-index on backup list

		$("#snapshot-edit-listing table tr").each(function(index, el) {
			$(el).find('.msc-info').css('z-index', 1000 - index);
		});


		$('input[name="wps-managed-backups-menu"]').on('change', function (e) {
			var value = $(this).filter(':checked').val();
			$('select[name="wps-managed-backups-menu-mobile"]').val(value).change();
			$('.wps-managed-backups-pages > .wpmud-box').addClass('hidden').filter('.' + value).removeClass('hidden');
		});

		$('select[name="wps-managed-backups-menu-mobile"]').on('change', function (e) {
			var value = $(this).val();
			$('input[name="wps-managed-backups-menu"][value="' + value + '"]').attr('checked', 'checked');
			$('.wps-managed-backups-pages > .wpmud-box').addClass('hidden').filter('.' + value).removeClass('hidden');
		});

		$("#wps-managed-backups-configure").on("click", function (e) {
			e.preventDefault();
			$("[for='wps-managed-backups-menu-config']").click();
			$('html,body').animate({
				scrollTop: $(".wps-managed-backups-configs").offset().top
			}, 'slow');
		});

		$("#my-backup-all").on('change', function () {
			$('input[id^="my-backup"]').attr('checked', $(this).is(':checked'));
		});

		$("#wps-managed-backups-onoff").on('change', function (e) {
			var form = $(this).parents('form');
			var enable = $(this).is(":checked");
			var hidden = $("#wps-managed-backups-onoff-hidden", form);
			if (enable) {
				hidden.attr('name', 'snapshot-enable-cron');
				$('.wps-managed-backups-schedule-form').removeClass('hidden');

			} else {
				hidden.attr('name', 'snapshot-disable-cron');
				$('.wps-managed-backups-schedule-form').addClass('hidden');
			}

			var data = form.serialize();

			//Save new backup setting using ajax
			jQuery.ajax({
				type: 'POST',
				url: form.attr('src'),
				data: data,
				success: function () {
				}
			});
		});

		window.SS_PAGES.snapshot_page_snapshot_pro_managed_backups_create();
	};

})(jQuery);
