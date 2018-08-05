;(function ($) {
	// page ID or "slug"
	window.SS_PAGES.snapshot_page_snapshot_pro_settings = function () {

		$('[name="files"]').on('change', function () {
			if ($('[name="files"]:checked').is('#common_files')) {
				$('#wps-settings-localdir .wpmud-box-gray').removeClass('hidden');
			} else {
				$('#wps-settings-localdir .wpmud-box-gray').addClass('hidden');
			}
		}).change();

		// Page: Settings
		// Add or remove "hidden" from error reporting options
		$('#wps-settings--error .wps-input--parent > .wps-input--checkbox > input').each(function () {
			$(this).on('change', function () {
				var input = $(this);
				var box_child = input.parents(".wps-input--parent").next('.wpmud-box-gray');

				if ( input.is(':checked') ) {
					box_child.removeClass('hidden');
				} else {
					box_child.addClass('hidden');
				}
			}).change();
		});

	};
})(jQuery);
