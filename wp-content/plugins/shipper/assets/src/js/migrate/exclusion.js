;(function ($) {
	function init() {
		if ($('.shipper-migration-exclusion').length) {
			//open modal
			$(window).on('load', function () {
				$('.shipper-migration-exclusion').attr('aria-hidden', false);
			});
			$('.shipper-migration-exclusion .sui-dialog-close,' +
				' .shipper-migration-exclusion .shipper-cancel').on('click', handle_preflight_cancel);
			$(document).on(
				'click',
				'.shipper-quicklinks a[data-path]',
				insert_quicklink
			);
			$(document).on('click', '.shipper-update-exclusion', update_exclusion);
		}
	}

	function insert_quicklink(e) {
		var path = $(this).attr('data-path'),
			$target = $(this).closest('.sui-form-field').find('textarea'),
			paths = $target.val().split("\n");
		paths.push(path);
		$target.val($.trim(paths.join("\n")));
		return stop_prop(e);
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

	function update_exclusion(e) {
		var data = _.extend(
			{action: 'shipper_migration_exclusion'},
			{'exclude_files': gather_files()},
			{'exclude_tables': gather_database()},
			{'exclude_extra': gather_advanced()}
		)
		var el = $(e.target);
		console.log(el);
		console.log(el.data('url'));
		$.post(ajaxurl, data, function (data) {
			if (data.success === true) {
				location.href = el.data('url');
			}
		});
	}

	function gather_files() {
		return $('.shipper-file-exclusions textarea')
			.val().split("\n");
	}

	function gather_database() {
		var $selected = $('.sui-tree [aria-selected="true"] [data-table]'),
			sel = [];

		$selected.each(function () {
			sel.push($(this).attr('data-table'));
		});

		return sel;
	}

	function gather_advanced() {
		var $els = $('.sui-checkbox-stacked :checkbox'),
			opts = [];
		$els.each(function () {
			if (!$(this).is(':checked')) return true;
			opts.push($(this).attr('name'));
		});
		return opts;
	}

	$(init());
}(jQuery))