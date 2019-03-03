;(function ($) {

	/**
	 * Toggles info section visibility, based on the select box value
	 */
	function toggle_section_display() {
		var sect = $('#shipper-sysinfo-section').val(),
			$target = $('.shipper-info-section-' + sect)
		;
		if (!$target.length) return false;

		$('.shipper-info-section').hide();
		$target.show();
	}

	/**
	 * Dispatches event listeners and boots the UI
	 */
	function bootstrap_sysinfo_page() {
		$("#shipper-sysinfo-section").on('change', toggle_section_display);
		toggle_section_display();
	}

	$(function() {
		if ($("#shipper-sysinfo-section").length) {
			bootstrap_sysinfo_page();
		}
	});
})(jQuery);
