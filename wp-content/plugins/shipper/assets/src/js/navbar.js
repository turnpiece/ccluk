/**
 * Shims in the smallscreen navigation functionality.
 */
;(function ($) {

	/**
	 * Boots the method listeners
	 *
	 * @param {String} selector_fragment Selector fragment
	 */
	function boot ( selector_fragment ) {
		var selector = selector_fragment + ' .sui-sidenav select.sui-mobile-nav';
		$(document).on('change', selector, function (e) {
			var tool = $(this).val(),
				href = window.location.search,
				rx = new RegExp('([?&])tool[^&]+')
			;
			if (!tool) {
				return false;
			}
			tool = encodeURIComponent(tool);
			window.location.search = href.match(rx)
				? href.replace(rx, '$1tool=' + tool)
				: href + '&tool=' + tool
			;
			return true;
		});
	}

	window._shipper.navbar = window._shipper.navbar || boot;

})(jQuery);
