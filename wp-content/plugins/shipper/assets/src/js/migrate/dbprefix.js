;(function ($) {
	function init() {
		if ($('.shipper-db-prefix-modal').length) {
			//open modal
			$(window).on('load', function () {
				$('.shipper-db-prefix-modal').attr('aria-hidden', false);
				$('.shipper-db-prefix-modal').find('.sui-tab-item.active input[name="migrate_dbprefix"]').click()
			});
			$('.shipper-db-prefix-modal .sui-dialog-close,' +
				' .shipper-db-prefix-modal .shipper-cancel').on('click', handle_preflight_cancel);
			$('.shipper-update-prefix').on('click', handle_update_prefix)
		}
	}

	function stop_prop(e) {
		if (e && e.preventDefault) e.preventDefault();
		if (e && e.stopPropagation) e.stopPropagation();
		return false;
	}

	/**
	 * Copy the code form preflight as the process much same
	 * @param e
	 * @returns {boolean}
	 */
	function handle_preflight_cancel(e) {
		$.post(ajaxurl, {action: 'shipper_preflight_cancel'}, function () {
			window.location.search = '?page=shipper';
		});
		return stop_prop(e);
	}

	/**
	 * Saving the prefix option to current migration
	 */
	function handle_update_prefix(e) {
		var option = $('input[name="migrate_dbprefix"]:checked').val()
		var value = $('input[name="migrate_dbprefix_value"]').val()

		$.post(ajaxurl, {
			action: 'shipper_dbprefix_update',
			option: option,
			value: value
		}, function (data) {
			console.log(data);
			if (data.success === true) {
				console.log('updated db prefix');
				location.reload();
			} else {
				//show error
			}
		});
	}

	$(init());
}(jQuery))