/**
 * Smallscreen navigation functionality for settings.
 */
;(function ($) {

	function reset_settings( nonce ) {
		return $.post( ajaxurl, {
			action: 'shipper_reset_settings',
			_wpnonce: nonce,
		}, function () {
			window.location.reload();
		});

	}

	function handle_settings_reset( e ) {
		if ( e && e.preventDefault ) e.preventDefault();
		if ( e && e.stopPropagation ) e.stopPropagation();

		var $me = $( this ),
			nonce = $me.attr( 'data-wpnonce' ),
			$dlg = $( '#shipper-settings-reset-dialog' ),
			dlg = new A11yDialog( $dlg.get( 0 ) )
		;
		dlg.show();

		$dlg.find( '.shipper-goback' )
			.off( 'click' )
			.on( 'click', function () { dlg.hide(); } );
		$dlg.find( '.shipper-reset' )
			.off( 'click' )
			.on( 'click', function( e ) {
				if ( e && e.preventDefault ) e.preventDefault();
				if ( e && e.stopPropagation ) e.stopPropagation();

				reset_settings( nonce );

				return false;
			} )
		;
		return false;
	}

	function handle_data_toggle( e ) {
		var $radio = $( this ),
			my_state = $radio.is( ':checked' ) ? 1 : 0,
			other_state = $radio.is( ':checked' ) ? 0 : 1,
			$toggle = $radio.closest( '[data-active]' ),
			$toggles = $radio.closest( '.shipper-data-toggles' ).find( '[data-active]' )
		;
		$toggles.attr( 'data-active', other_state );
		$toggle.attr( 'data-active', my_state );
	}

	function boot_data_toggles() {
		$( '.shipper-data-toggles :radio' )
			.off( 'change' )
			.on( 'change', handle_data_toggle )
		;
	}

	function boot_settings_reset() {
		$( '.shipper-reset-settings[data-wpnonce]' )
			.off( 'click' )
			.on( 'click', handle_settings_reset )
		;
	}

	$(function () {
		if ( $( '.shipper-page-settings' ).length ) {
			var boot = (window._shipper || {}).navbar;
			if (boot) boot( '.shipper-page-settings' );

			if ( $( '.shipper-data-toggles' ).length ) {
				boot_data_toggles();
			}
			if ( $( '.shipper-reset-settings[data-wpnonce]' ).length ) {
				boot_settings_reset();
			}
		}
	});
})(jQuery);
