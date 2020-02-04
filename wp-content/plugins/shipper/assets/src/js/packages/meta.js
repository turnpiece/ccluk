;( function( $ ) {

	function stop_prop( e ) {
		if ( e && e.stopPropagation ) e.stopPropagation();
		if ( e && e.preventDefault ) e.preventDefault();
		return false;
	}

	function get_modal( modal ) {
		return $( '.shipper-package-create-' + modal );
	}

	function show_modal( modal ) {
		var $modal = get_modal( modal );
		if ( $modal.length ) {
			$( '.sui-dialog' ).attr( 'aria-hidden', true );
			$modal.attr( 'aria-hidden', false )
		}
		return $modal;
	}

	function insert_quicklink( e ) {
		var path = $( this ).attr( 'data-path' ),
			$target = $( this ).closest( '.sui-form-field' ).find( 'textarea' ),
			paths = $target.val().split( "\n" );
		paths.push( path );
		$target.val( $.trim( paths.join( "\n" ) ) );
		return stop_prop( e );
	}

	function gather_files() {
		return $( '.shipper-file-exclusions textarea' )
			.val().split( "\n" );
	}

	function gather_database() {
		var $selected = $( '.sui-tree [aria-selected="true"] [data-table]' ),
			sel = [];

		$selected.each( function() {
			sel.push( $( this ).attr( 'data-table' ) );
		} );

		return sel;
	}

	function gather_advanced() {
		var $els = $( '.sui-checkbox-stacked :checkbox' ),
			opts = [];
		$els.each( function() {
			if ( ! $( this ).is( ':checked' ) ) return true;
			opts.push( $( this ).attr( 'name' ) );
		} );
		return opts;
	}

	function gather_meta() {
		var $modal = get_modal( 'meta' );
		return {
			name: $modal.find( 'input[name="package-name"]' ).val(),
			password: $modal.attr( 'data-password' ),
			_wpnonce: $modal.find( 'input[name="shipper-create-package"]' ).val()
		};
	}

	function start_preflight( e ) {
		$( document ).trigger( 'shipper-package-preflight' );
		return stop_prop( e );
	}

	function gather_all_settings( e ) {
		send_request( 'create', _.extend(
			gather_meta(),
			{ 'exclude_files': gather_files() },
			{ 'exclude_tables': gather_database() },
			{ 'exclude_extra': gather_advanced() }
		) ).done( start_preflight );
		return stop_prop( e );
	}

	function send_request( action, obj ) {
		obj = obj || {};
		obj.action = 'shipper_packages_meta_' + action;
		return $.post( ajaxurl, obj );
	}

	function handle_package_rewrite( e ) {
		var nonce = $( 'input[name="shipper-reset-package"]' ).val();
		send_request( 'reset', { _wpnonce: nonce } )
			.done( show_package_meta );
		return stop_prop( e );
	}

	function show_package_settings( e ) {
		show_modal( 'settings' )
			.find( '.shipper-next' )
				.off( 'click' )
				.on( 'click', gather_all_settings ).end()
			.find( '.shipper-previous' )
				.off( 'click' )
				.on( 'click', show_package_meta )
		;
		return stop_prop( e );
	}

	function set_password_and_show_package_settings( e ) {
		var $modal = get_modal( 'meta' ),
			$password = $modal.find( '[data-state]:visible input[name="installer-password"]' );
		$modal.attr( 'data-password', $password.val() );
		return show_package_settings( e );
	}

	function show_package_meta( e ) {
		show_modal( 'meta' )
			.attr( 'data-password', '' )
			.find( '.shipper-next' )
				.off( 'click' )
				.on( 'click', set_password_and_show_package_settings );
		return stop_prop( e );
	}

	function show_package_rewrite_confirm() {
		show_modal( 'confirm' )
			.find( '.shipper-next' )
				.off( 'click' )
				.on( 'click', handle_package_rewrite );
	}

	function create_new_package( e ) {
		if ( $( '.shipper-packages-migration' ).is( '.shipper-has-packages' ) ) {
			show_package_rewrite_confirm();
		} else show_package_meta();

		return stop_prop( e );
	}

	function delete_package( e ) {
		var nonce = $( 'input[name="shipper-reset-package"]' ).val();
		send_request( 'reset', { _wpnonce: nonce } )
			.done( function() {
				window.location.reload();
			} );
		return stop_prop( e );
	}

	function download_package( e ) {
		var nonce = $( this ).closest( '.shipper-download' )
			.find( ':hidden[name="_wpnonce"]' ).val();
		window.location = ajaxurl +
			'?action=shipper_packages_meta_download_package&_wpnonce=' +
			nonce;
		return stop_prop( e );
	}

	function download_installer( e ) {
		var nonce = $( this ).closest( '.shipper-download' )
			.find( ':hidden[name="_wpnonce"]' ).val();
		window.location = ajaxurl +
			'?action=shipper_packages_meta_download_installer&_wpnonce=' +
			nonce;
		return stop_prop( e );
	}

	function init() {
		if ( ! $( '.shipper-packages-migration-main' ).length ) {
			return false;
		}
		if ( ( window._shipper || {} ).navbar ) {
			window._shipper.navbar( '.shipper-page-packages' );
		}
		$( document ).on(
			'click',
			'.shipper-new-package',
			create_new_package
		);
		$( document ).on(
			'click',
			'.shipper-delete',
			delete_package
		);
		$( document ).on(
			'click',
			'.shipper-quicklinks a[data-path]',
			insert_quicklink
		);
		$( document ).on(
			'click',
			'#shipper-package-create .shipper-cancel, #shipper-package-create .sui-dialog-close',
			function( e ) {
				$( '.sui-dialog' ).attr( 'aria-hidden', true );
				return stop_prop( e );
			}
		);

		$( document ).on(
			'click',
			'.shipper-download-item.archive',
			download_package
		);
		$( document ).on(
			'click',
			'.shipper-download-item.installer',
			download_installer
		);
	}

	$( init );

} )( jQuery );
