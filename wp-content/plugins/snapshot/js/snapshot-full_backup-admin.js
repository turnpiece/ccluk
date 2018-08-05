;(function ($) {


var Sfb = window.Sfb || {
	value: function (key) {
		return (window._snp_vars || {})[key];
	},
	l10n: function (key) {
		return (this.value('l10n') || {})[key] || key;
	}
};

var ElementSelector = {
	/**
	 * Used to get all applicable element anchors
	 *
	 * @type {String}
	 */
	element_selector: '',

	/**
	 * Gets applicable elements for targeting
	 *
	 * @uses this.element_selector
	 *
	 * @return {Object} jQuery nodes object
	 */
	get_elements: function () {
		return $(this.element_selector);
	},

	/**
	 * Initializes event listeners
	 *
	 * @return {Boolean}
	 */
	initialize_listeners: function () {
		var me = this;
		this.get_elements().each(function () {
			me.initialize_listener($(this));
		});
		return true;
	},

	/**
	 * Initializes individual event listener for a node
	 *
	 * @param {object} $el jQuery node
	 */
	initialize_listener: function ($el) {}
};

/**
 * Shared utilities
 *
 * @type {Object}
 */
Sfb.Util = {
	/**
	 * Stops event propagation
	 *
	 * @param {Object} e Event
	 *
	 * @return {Boolean} False
	 */
	stop_prop: function (e) {
		if (e && e.preventDefault) e.preventDefault();
		if (e && e.stopPropagation) e.stopPropagation();
		return false;
	}
};

/**
 * Notice actions
 *
 * @type {Object}
 */
Sfb.Notice = $.extend({}, ElementSelector, {

	/**
	 * Used to get all applicable element anchors
	 *
	 * @type {String}
	 */
	element_selector: '.wp-admin .notice .button.snapshot',

	/**
	 * Used as a fallback if anchor doesn't have `data-target` attribute
	 *
	 * @type {String}
	 */
	target_selector: '.backup-list td.name .location.local:first',

	/**
	 * Used as a fallback if anchor doesn't have `data-subtarget` attribute
	 *
	 * @type {String}
	 */
	subtarget_selector: 'td',

	/**
	 * Handles target scroll clicks
	 *
	 * @param {Object} e Event
	 *
	 * @return {Boolean}
	 */
	handle_target_scroll: function (e) {
		var target = $(this).attr("data-target") || Sfb.Notice.target_selector,
			subtarget = $(this).is('[data-subtarget]') ? $(this).attr("data-target") : Sfb.Notice.subtarget_selector,
			$target = $(target),
			top = 0
		;
		if (subtarget) $target = $target.closest(subtarget);

		if ($target.length) {
			top = ($target.offset() || {}).top - $("#wpadminbar").height();
			if (top > 0) {
				$(window).scrollTop(top);
				$target.fadeOut(300).fadeIn(300);
			}
		}
		return Sfb.Util.stop_prop(e);
	},

	initialize_listener: function ($el) {
		$el
			.off("click.snapshot")
			.on("click.snapshot", this.handle_target_scroll)
		;
	},

	run: function () {
		this.initialize_listeners();
		return true;
	}
});

var noop = function () {};

/**
 * Manual backup button handler
 *
 * @type {Object}
 */
Sfb.ManualBackup = $.extend({}, ElementSelector, {

	element_selector: '.wp-admin .snapshot-wrap button[name="backup"]',

	/**
	 * Current progress step
	 *
	 * @type {Number}
	 */
	current: 0,

	/**
	 * Total estimated steps for this backup
	 *
	 * @type {Number}
	 */
	total: 0,

	/**
	 * Target node for output
	 *
	 * @type {Object} jQuery node reference
	 */
	$target: false,

	/**
	 * Skip estimate step flag
	 *
	 * If set to true, the overall processing estimation
	 * step will be skipped and, as a consequence, the
	 * progress will be expressed as steps rather than percentages.
	 *
	 * @type {Bool}
	 */
	skip_estimate: true,

	/**
	 * Reports error response message and reinitiates listeners
	 *
	 * @param {Object} rsp Optional response object
	 *
	 * @return {Bool}
	 */
	error_handler: function (rsp) {
		var msg = $.trim((rsp || {}).responseText) || '&lt;empty response&gt;';
		Sfb.ManualBackup.$target.html(
			'<p>' + Sfb.l10n('generic_error') + '</p>' +
			'<pre>' + msg + '</pre>'
		);
		return !!Sfb.ManualBackup.initialize_listeners();
	},

	/**
	 * Updates progress info message
	 *
	 * The info message will be expressed in percentages, or alternatively
	 * simple step increments, depending on whether we performed the size estimate.
	 *
	 * @return {Bool}
	 */
	update_progress: function () {
		if (this.total > 0 && this.current && this.current <= this.total) {
			var current = this.current > 1 ? this.current - 1 : 1,
				percentage = (current * 100) / this.total
			;
			if (percentage >= 100) percentage = 100;
			this.$target.text(Sfb.l10n('processing_percent').replace(/%d/, parseInt(percentage, 10)));
		} else {
			this.$target.text(Sfb.l10n('processing').replace(/%d/, this.current));
		}
		return true;
	},

	/**
	 * Starts backup
	 *
	 * Sends out backup start request
	 *
	 * @return {Object} jQuery promise
	 */
	start_backup: function () {
		return $.post(ajaxurl, {
			action: "snapshot-full_backup-start"
		}, noop, 'json').fail(Sfb.ManualBackup.error_handler);
	},

	/**
	 * Finishes backup
	 *
	 * Sends out backup finish request
	 *
	 * @return {Object} jQuery promise
	 */
	finish_backup: function () {
		this.$target.text(Sfb.l10n('finishing'));
		var prm = $.post(ajaxurl, {
			action: "snapshot-full_backup-finish"
		}, noop, 'json');
		prm.then(function (data) {
			if (!data.status) return Sfb.ManualBackup.finish_backup();
			if (data.msg) Sfb.ManualBackup.$target.text(data.msg);
			Sfb.ManualBackup.run();
			window.location.reload(); // reload when we're done
		}).fail(Sfb.ManualBackup.error_handler);
		return prm;
	},

	/**
	 * Processes files
	 *
	 * Sends out backup files processing request
	 *
	 * @return {Object} jQuery promise
	 */
	process_files: function () {
		this.update_progress();
		$.post(ajaxurl, {
			action: "snapshot-full_backup-process"
		}, noop, 'json').then(function (data) {
			Sfb.ManualBackup.current++;
			var is_done = false;
			try {
				is_done = !!data.done;
			} catch (e) {
				return error_handler();
			}
			if (!is_done) Sfb.ManualBackup.process_files();
			else Sfb.ManualBackup.finish_backup();
		}).fail(Sfb.ManualBackup.error_handler);
	},

	/**
	 * Estimates backup processing size
	 *
	 * Sends out an estimate request
	 *
	 * @return {Object} jQuery promise
	 */
	estimate_backup: function () {
		this.$target.text(Sfb.l10n('estimating'));
		var prm = $.post(ajaxurl, {
			action: "snapshot-full_backup-estimate"
		}, noop, 'json');
		prm.then(function (data) {
			var total = parseInt((data || {}).total || '0', 10);
			if (total) Sfb.ManualBackup.total = total;
		}).fail(Sfb.ManualBackup.error_handler);
		return prm;
	},

	/**
	 * Handles button click
	 *
	 * @param {Object} e Event
	 *
	 * @return {Bool}
	 */
	handle_start_click: function (e) {
		Sfb.ManualBackup.$target.parent(":hidden").show();
		Sfb.ManualBackup.current = 1;

		Sfb.ManualBackup.$target.show().text(Sfb.l10n('starting'));
		$('button[name="backup"]').off("click.snapshot", Sfb.ManualBackup.handle_start_click);

		Sfb.ManualBackup.start_backup().then(function (data) {
			var backup = (data || {}).id;
			if (!backup) return Sfb.ManualBackup.error_handler(data);

			if (Sfb.ManualBackup.skip_estimate) {
				return Sfb.ManualBackup.process_files();
			} else {
				return Sfb.ManualBackup.estimate_backup().then(function () {
					return Sfb.ManualBackup.process_files();
				});
			}
		});

		return Sfb.Util.stop_prop(e);
	},

	/**
	 * Initializes the button click listener
	 *
	 * @param {Object} $el jQuery node reference
	 *
	 * @return {Bool}
	 */
	initialize_listener: function ($el) {
		$el
			.off("click.snapshot", this.handle_start_click)
			.on("click.snapshot", this.handle_start_click)
		;
		return true;
	},

	/**
	 * Starts up the handler
	 *
	 * Boots markup and initializes the listeners
	 *
	 * @return {Bool}
	 */
	run: function () {
		if (!this.get_elements().length) return false;

		var $target_root = $("#snapshot-full_backups-panel header"),
			out_tpl = '<div id="snapshot-ajax-out"><div class="out"></div></div>'
		;

		$("#snapshot-ajax-out").remove();
		if ($target_root.length) {
			$target_root.after(out_tpl);
		} else {
			this.get_elements().first().after(out_tpl);
		}
		this.$target = $("#snapshot-ajax-out").find(".out");
		this.initialize_listeners();
		return true;
	}
});

/*
Sfb.BackupItem = $.extend({}, ElementSelector, {

	element_selector: '.backup-list .row-actions a[href="#upload"]',

	handle_upload: function () {
		var $row = $(this).closest('tr'),
			timestamp = $row.find('input[name="delete-bulk[]"]').val()
		;
		if (timestamp) {
			console.log(timestamp);
		}
		return Sfb.Util.stop_prop();
	},

	initialize_listener: function ($el) {
		$el
			.off("click.snapshot")
			.on("click.snapshot", this.handle_upload)
		;
	},

	run: function () {
		this.initialize_listeners();
		return true;
	}
});
*/

/**
 * Deals with the logging section
 *
 * @type {Object}
 */
Sfb.Logs = {

	/**
	 * Log enable/disable toggle
	 *
	 * @type {ElementSelector}
	 */
	Toggler: $.extend({}, ElementSelector, {

		element_selector: '#log-enable',

		/**
		 * Toggles logging level sections on log enable/disable
		 *
		 * Also sets log levels to default on logging disable
		 */
		handle_enable: function () {
			var $toggle = Sfb.Logs.Toggler.get_elements(),
				state = $toggle.is(":checked"),
				dflt = $toggle.attr('data-default')
			;

			if (state) $(".snapshot-settings.log-levels").show();
			else {
				$(".snapshot-settings.log-levels")
					.hide()
					.find('input[type="radio"]')
						.attr("checked", false)
					.end()
					.find('input[type="radio"][value="' + dflt + '"]')
						.attr("checked", true)
				;
			}
		},

		/**
		 * Initializes toggler click listener
		 *
		 * @param {object} $el jQuery node
		 */
		initialize_listener: function ($el) {
			$el
				.off("click.snapshot")
				.on("click.snapshot", this.handle_enable)
			;
		},

		/**
		 * Initializes log enable/disable toggle
		 *
		 * @return {Boolean}
		 */
		run: function () {
			this.initialize_listeners();
			this.handle_enable();
			return true;
		}
	}),

	/**
	 * Log viewer toggle
	 *
	 * @type {ElementSelector}
	 */
	Viewer: $.extend({}, ElementSelector, {

		element_selector: 'a[href="#view-log-file"]',

		/**
		 * Refreshing period.
		 *
		 * This is how long the results will stay before we attempt reload.
		 * Set to false-ish value in integer context to disable
		 * results refreshing altogether.
		 *
		 * @type {Number}
		 */
		ttl: 120000,

		/**
		 * Handles log viewer spawning clicks
		 *
		 * @param {Object} e Event
		 *
		 * @return {Boolean}
		 */
		handle_view: function (e) {
			var $trigger = Sfb.Logs.Viewer.get_elements(),
				title = $trigger.attr("title") || Sfb.l10n('snapshot_logs'),
				tmout = false, ttl = 0
			;

			tb_show(title, ajaxurl + '?action=snapshot-full_backup-get_log&TB_iframe=true');

			ttl = Sfb.Logs.Viewer.ttl ? parseInt(Sfb.Logs.Viewer.ttl, 10) : 0;

			if (ttl) {
				// First `setTimeout` is to move this off of exec stack
				setTimeout(function () {
					$("#TB_window")
						.off('thickbox:iframe:loaded.snapshot')
						.on('thickbox:iframe:loaded.snapshot', function () {
							// When the content loads, set up a ticker to reload after a bit
							tmout = setTimeout(function () {
								var frm = $("#TB_iframeContent").get(0) || {};
								if (!frm.contentWindow) return false;

								frm.contentWindow.location.reload(true);
							}, ttl);
						})
					;
					$('body')
						.off('thickbox:removed.snapshot')
						.on('thickbox:removed.snapshot', function () {
						clearTimeout(tmout);
					});
				});
			}

			return Sfb.Util.stop_prop(e);
		},

		/**
		 * Initializes log viewer click listener
		 *
		 * @param {object} $el jQuery node
		 */
		initialize_listener: function ($el) {
			$el
				.off("click.snapshot")
				.on("click.snapshot", this.handle_view)
			;
		},

		/**
		 * Initializes log enable/disable toggle
		 *
		 * @return {Boolean}
		 */
		run: function () {
			this.initialize_listeners();
			return true;
		}
	}),

	/**
	 * Initializes the whole log section JS
	 *
	 * @return {Boolean}
	 */
	run: function () {
		Sfb.Logs.Toggler.run();
		Sfb.Logs.Viewer.run();

		return true;
	}
};

window.Sfb = Sfb;

$(function () {
	Sfb.Notice.run();
	Sfb.Logs.run();
});

})(jQuery);
