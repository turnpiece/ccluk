/**
 * Hub checks and welcome modals scripts
 */
;(function($) {

	/**
	 * Actually closes dialog and stores the dialog close choice
	 */
	function close_dialog() {
		return $.post(ajaxurl, {
			action: 'shipper_modal_closed',
			target: 'welcome',
			_wpnonce: $('.sui-dialog.shipper-welcome').attr('data-wpnonce')
		}).always(function() {
			$('.shipper-welcome.sui-dialog').attr('aria-hidden', true);
		});
	}

	/**
	 * Handles dialog closing button click
	 *
	 * @param {Object} e Event object
	 */
	function handle_close_dialog( e ) {
		if (e && e.preventDefault) e.preventDefault();

		close_dialog();

		return false;
	}

	/**
	 * Handles skip link click
	 *
	 * @param {Object} e Event object
	 */
	function handle_skip_click( e ) {
		if (e && e.preventDefault) e.preventDefault();

		window.location.reload();

		return false;
	}


	function boot_welcome_dialogs() {
		$('.shipper-welcome.sui-dialog').attr('aria-hidden', false);

		$('.shipper-welcome-continue').on('click', handle_close_dialog);
		$('.shipper-welcome a[href="#skip"]').on('click', handle_skip_click);
	}

	$(function() {
		if ($('.shipper-welcome.sui-dialog').length) {
			$(window).on('load', boot_welcome_dialogs);
		}
	});
})(jQuery);
