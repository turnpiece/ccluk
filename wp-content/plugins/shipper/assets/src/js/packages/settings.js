;(function ($) {

	function stop_prop(e) {
		if (e && e.stopPropagation) e.stopPropagation();
		if (e && e.preventDefault) e.preventDefault();
		return false;
	}

	function handle_tab_option_selection() {
		$(this)
			.find(':radio').attr('checked', false).end()
			.find('.sui-tab-boxed.active')
			.find(':radio').attr('checked', true);
		maybe_prevent_submit()
	}

	function maybe_prevent_submit() {
		var is_mysqldump_error = $('.shipper-tab-boxed-error input[name="database-use-binary"]').is(':checked');
		var is_shellarchive_error = $('.shipper-tab-boxed-error input[name="archive-use-binary"]').is(':checked');
		if (is_mysqldump_error || is_shellarchive_error) {
			$('.sui-button.shipper-save').attr('disabled', true)
		} else {
			$('.sui-button.shipper-save').removeAttr('disabled');
		}
	}

	function init() {
		if (!$('.shipper-packages-settings').length) {
			return false;
		}
		if ((window._shipper || {}).navbar) {
			window._shipper.navbar('.shipper-page-packages');
		}
		$(".sui-tabs")
			.on('click', handle_tab_option_selection)
			.each(handle_tab_option_selection);
	}

	$(init);

})(jQuery);
