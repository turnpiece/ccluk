/**
 * Initial web site addition
 */
;(function($) {

	var MSG_SHOW_INTERVAL = 5 * 1000;

	function show_state( state ) {
		$('.shipper-destination-state').hide();
		get_state( state ).show();
	}

	function get_state( state ) {
		return $('.shipper-destination-state-' + state);
	}

	function hide_messages() {
		$('.shipper-destination-added-message .sui-notice').hide();
	}

	function show_message( msg ) {
		hide_messages();
		var $msg = $('.shipper-destination-added-message .status-' + msg);

		setTimeout(function () {
			$msg.hide();
		}, MSG_SHOW_INTERVAL);

		return $msg.show();
	}

	function handle_dismiss_notice( e ) {
		if (e && e.preventDefault) e.preventDefault();

		hide_messages();

		return false;
	}

	/**
	 * Handles add link click
	 *
	 * @param {Object} e Event object
	 */
	function handle_show_click( e ) {
		if (e && e.preventDefault) e.preventDefault();

		handle_dismiss_notice();
		$('.shipper-destination-add.sui-dialog').attr('aria-hidden', false);
		show_state('add');

		return false;
	}

	/**
	 * Handles back link click
	 *
	 * @param {Object} e Event object
	 */
	function handle_back_click( e ) {
		if (e && e.preventDefault) e.preventDefault();

		show_state('add');

		return false;
	}

	/**
	 * Handles close dialog click
	 *
	 * @param {Object} e Event object
	 */
	function handle_close_click( e ) {
		if (e && e.preventDefault) e.preventDefault();

		hide_messages();
		$('.shipper-destination-add.sui-dialog').attr('aria-hidden', true);

		return false;
	}

	/**
	 * Handles connection check click
	 *
	 * @param {Object} e Event object
	 */
	function handle_check_click( e ) {
		if (e && e.preventDefault) e.preventDefault();

		show_state('check');
		$.post(ajaxurl, { action: 'shipper_check_connection' })
			.fail(function() { show_state('fail'); })
			.done(function( data ) {
				var success = !!(data || {}).success;
				if (success) {
					return window.location.reload();
				} else {
					show_state('fail');
				}
			})
		;

		return false;
	}

	function handle_hub_click( e ) {
		if (e && e.preventDefault) e.preventDefault();

		hide_messages();
		show_state('hub');

		return false;
	}

	function handle_connect_click( e ) {
		if (e && e.preventDefault) e.preventDefault();

		hide_messages();
		show_state('refresh');
		$.post(ajaxurl, {
			action: 'shipper_list_hub_sites',
			_wpnonce: get_state('connect').find('[name="_wpnonce"]').val()
		}, function ( resp ) {
			show_state('connect');
			show_message( 'refresh' );
			var $target = get_state('connect')
					.find('.shipper-selection.select-name'),
				sites = ( resp || {} ).data || [],
				content = ''
			;
			$.each( sites, function( idx, site ) {
				content += '<option value="' + site + '">' + site + '</option>';
			});
			$target.html(
				'<select name="site">' + content + '</select>'
			);
			SUI.suiSelect($target.find('select').get());
			get_state('connect').find('a[href="#refresh"]')
				.off('click')
				.on('click', handle_connect_click)
			;
			get_state('connect').find('a[href="#connect-new-site"]')
				.off('click')
				.on('click', handle_hub_click)
			;
			get_state('connect').find('button[type="submit"]')
				.off('click')
				.on('click', function( e ) {
					if (e && e.preventDefault) e.preventDefault();

					var to_prepare = get_state('connect').find('select[name="site"]').val();
					if ( to_prepare ) {
						show_state('prepare');
						$.post(ajaxurl, {
							action: 'shipper_prepare_hub_site',
							site: to_prepare,
							_wpnonce: get_state('prepare').find('[name="_wpnonce"]').val()
						}).always(function ( data ) {
							var done = ( data || {} ).success,
								msg = ( done ? 'success' : 'failure' )
							;
							handle_close_click();
							show_message( msg );

							if ( done ) {
								// If this was a success, we will want to
								// reload the page to pick up the new stuff.
								window.location.reload();
							}
						});
					}

					return false;
				})
			;
		});

		return false;
	}

	function boot_dialog() {
		$('.shipper-add-website').on('click', handle_show_click);
		$('.sui-dialog-close').on('click', handle_close_click);

		$(document).on('click', '.shipper-connection-check', handle_check_click);
		$(document).on('click', '.shipper-dialog-back', handle_back_click);
		$(document).on('click', 'a[href="#connect"].shipper-connect', handle_connect_click);

		$(document).on('click', '.sui-notice-dismiss a', handle_dismiss_notice);
	}

	$(function() {
		if ($('.shipper-destination-add.sui-dialog').length) {
			$(window).on('load', boot_dialog);
		}
	});
})(jQuery);
