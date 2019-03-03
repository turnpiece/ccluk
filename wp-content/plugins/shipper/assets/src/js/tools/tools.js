/**
 * Smallscreen navigation functionality for tools page
 */
;(function ($) {
	$(function () {
		if ($('.shipper-page-tools').length) {
			var boot = (window._shipper || {}).navbar;
			if (boot) boot( '.shipper-page-tools' );
		}
	});
})(jQuery);
