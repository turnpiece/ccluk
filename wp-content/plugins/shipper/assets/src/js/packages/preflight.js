;(function ($) {

	/**
	 * Preflight state flag
	 *
	 * @var {Boolean}
	 */
	var _preflight_paused = false;

	var issues_count = 0;

	function stop_prop(e) {
		if (e && e.stopPropagation) e.stopPropagation();
		if (e && e.preventDefault) e.preventDefault();
		return false;
	}

	function get_modal(modal) {
		return $('.shipper-package-preflight-' + modal);
	}

	function show_modal(modal) {
		var $modal = get_modal(modal);
		if ($modal.length) {
			$('.sui-dialog').attr('aria-hidden', true);
			$modal.attr('aria-hidden', false)
		}
		return $modal;
	}

	function send_request(action, obj) {
		var dfr = new $.Deferred();
		obj = obj || {};
		obj.action = 'shipper_packages_preflight_' + action;
		$.post(ajaxurl, obj)
			.done(function (resp) {
				var status = (resp || {}).success,
					data = (resp || {}).data;
				if (status) {
					return dfr.resolveWith(this, [data]);
				}
				return dfr.rejectWith(this, [data]);
			})
			.fail(function () {
				dfr.reject();
			});
		return dfr.promise();
	}

	function start_building(e) {
		$(document).trigger('shipper-package-build');
	}

	function update_countable_files_status($item) {
		var $total = $item.find('.shipper-paginated'),
			$excluded = $total.filter('.shipper-file-excluded'),
			severity = $total.length > $excluded.length ? 'warning' : 'success';

		if (!$total.length) {
			$item.hide(); // Nothing to show here.
		}

		$item.find('.shipper-issue-summary-count').remove();
		$item.find('.shipper-issue-summary').after(
			'<div class="shipper-issue-summary-count">' +
			'<span class="sui-tag sui-tag-' + severity + '">' +
			($total.length - $excluded.length) +
			'</span>' +
			'</div>'
		);
		$item.find('.shipper-issue-severity')
			.removeClass('shipper-severity-success')
			.removeClass('shipper-severity-warning')
			.addClass('shipper-severity-' + severity);
	}

	function update_package_size($item) {
		if ($item.length === 0) {
			return;
		}
		var size = $item.find('[data-size]').attr('data-size'),
			num = size.split(/(\d+)/)[1],
			unit = size.split(/(\d+)/)[2] || "b"
		multiplier = 1,
			severity = 'success',
			cutoff = 200 * 1024 * 1024;
		if (unit.match(/^\s*k/i)) multiplier = 1024;
		if (unit.match(/^\s*m/i)) multiplier = 1024 * 1024;
		if (unit.match(/^\s*g/i)) multiplier = 1024 * 1024 * 1024;
		if (unit.match(/^\s*t/i)) multiplier = 1024 * 1024 * 1024 * 1024;

		if ((parseInt(num, 10) || 1) * multiplier > cutoff) {
			severity = 'warning';
		}

		$item.find('.shipper-issue-summary-count').remove();
		$item.find('.shipper-issue-summary').after(
			'<div class="shipper-issue-summary-count">' +
			'<span class="sui-tag sui-tag-' + severity + '">' +
			(size.replace(/ /, '&nbsp;')) +
			'</span>' +
			'</div>'
		);
		$item.find('.shipper-issue-severity')
			.removeClass('shipper-severity-success')
			.removeClass('shipper-severity-warning')
			.addClass('shipper-severity-' + severity);
	}

	function update_preflight_state() {
		var $modal = get_modal('issues'),
			$large = $modal.find('.shipper-issue-file_sizes'),
			$names = $modal.find('.shipper-issue-file_names'),
			$size = $modal.find('.shipper-issue-package_size');

		update_countable_files_status($large);
		update_countable_files_status($names);
		update_package_size($size);

		$modal.find('.shipper-next').attr(
			'disabled',
			!!$modal.find('.shipper-severity-error').length
		);
	}

	function handle_update_package_size(e, package_size) {
		console.log('new size', package_size);
		$('.shipper-issue-package_size .shipper-package_size')
			.attr('data-size', package_size);
		update_preflight_state();
	}

	function show_results() {
		if (_preflight_paused) {
			return true;
		}
		//we will check if no preflight issues, we moving forward to build screen
		console.log(issues_count);
		if (issues_count === 0) {
			start_building();
			return;
		}

		update_preflight_state();
		var $modal = show_modal('issues')
			.find('.shipper-next')
			.off('click')
			.on('click', start_building).end()
			.find('.shipper-restart')
			.off('click')
			.on('click', start_preflight).end()
		;
		$('.shipper-wizard-result-files').each(function () {
			new window._shipper.PaginatedFilterArea($(this));
		});
		$(document).on(
			'shipper:preflight-files:package_size',
			handle_update_package_size
		);

		$modal.find('select').each(function () {
			SUI.suiSelect(this);
		});
		SUI.suiTabs();
	}

	function check_database() {
		if (_preflight_paused) {
			return true;
		}
		var $active = show_modal('check')
			.find('.shipper-progress-label').text('99%').end()
			.find('.shipper-progress-bar').css('width', '99%').end()
			.find('.shipper-progress-check')
			.removeClass('active')
			.last().addClass('active');
		get_modal('check').find('.shipper-progress-status').text(
			$active.find('.shipper-progress-title').attr('data-active')
		);
		setTimeout(show_results, 1000);
	}

	function check_files() {
		if (_preflight_paused) {
			return true;
		}
		var $active = show_modal('check')
			.find('.shipper-progress-label').text('66%').end()
			.find('.shipper-progress-bar').css('width', '66%').end()
			.find('.shipper-progress-check')
			.removeClass('active')
			.eq(1).addClass('active');
		get_modal('check').find('.shipper-progress-status').text(
			$active.find('.shipper-progress-title').attr('data-active')
		);
		send_request('files')
			.done(function (data) {
				var $issues = get_modal('issues').find('.shipper-issues'),
					is_done = (data || {}).is_done,
					issues = (data || {}).issues || [];
				if (!is_done) {
					return setTimeout(check_files, 100);
				}

				$.each(issues, function (idx, issue) {
					$issues.append(issue);
				});
				//set the counter
				console.log(issues);
				issues_count += issues.length;
				setTimeout(check_database, 100);
			});
	}

	function check_system() {
		if (_preflight_paused) {
			return true;
		}
		var $active = show_modal('check')
			.find('.shipper-progress-label').text('33%').end()
			.find('.shipper-progress-bar').css('width', '33%').end()
			.find('.shipper-progress-check')
			.removeClass('active')
			.first().addClass('active');
		get_modal('check').find('.shipper-progress-status').text(
			$active.find('.shipper-progress-title').attr('data-active')
		);
		send_request('system')
			.done(function (issues) {
				var $issues = get_modal('issues').find('.shipper-issues');
				$.each(issues, function (idx, issue) {
					$issues.append(issue);
				});
				issues_count += issues.length;
				setTimeout(check_files, 100);
			});
	}

	function start_preflight(e) {
		_preflight_paused = false;
		issues_count = 0;
		show_modal('check');
		get_modal('issues').find('.shipper-issues').html('');
		check_system();
		return stop_prop(e);
	}

	function cancel_preflight(e) {
		_preflight_paused = true;
		$('.sui-dialog').attr('aria-hidden', true);
		return stop_prop(e);
	}

	function init() {
		if (!$('.shipper-packages-migration-main').length) {
			return false;
		}
		$(document).on(
			'shipper-package-preflight',
			start_preflight
		);
		$(document).on(
			'click',
			'.shipper-issue-title',
			function (e) {
				$(this).closest('.shipper-issue').toggleClass('shipper-issue-open');
				return stop_prop(e);
			}
		);
		$(document).on(
			'click',
			'#shipper-package-preflight .shipper-cancel, #shipper-package-preflight .sui-dialog-close',
			cancel_preflight
		);
		$(document).on(
			'click',
			'.shipper-issue .shipper-recheck',
			start_preflight
		);
	}

	$(init);

})(jQuery);
