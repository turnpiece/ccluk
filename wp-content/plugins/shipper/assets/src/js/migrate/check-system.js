/**
 * Hub checks and welcome modals scripts
 */
;(function($) {

	/**
	 * Handles dialog closing button click
	 *
	 * @param {Object} e Event object
	 */
	function handle_close_dialog( e ) {
		if (e && e.preventDefault) e.preventDefault();

		$.post(ajaxurl, {
			action: 'shipper_modal_closed',
			target: 'system',
			_wpnonce: $('.sui-dialog.shipper-system-check').attr('data-wpnonce')
		}).always( function () {
			$.post( ajaxurl, {
				action: 'shipper_clear_cache'
			} ).always( function () {
				window.location.reload();
			} );
		} );

		return false;
	}

	/**
	 * Handles skip link click
	 *
	 * @param {Object} e Event object
	 */
	function handle_recheck_click( e ) {
		if (e && e.preventDefault) e.preventDefault();

		window.location.reload();

		return false;
	}


	function boot_system_dialog() {
		$('.sui-dialog.shipper-system-check').attr('aria-hidden', false);

		$('.shipper-system-check a[href="#recheck"]').on('click', handle_recheck_click);
		$('.shipper-system-check a[href="#override"]').on('click', handle_close_dialog);
	}

	$(function() {
		if ($('.sui-dialog.shipper-system-check').length) {
			$(window).on('load', boot_system_dialog);
		}
	});
})(jQuery);
