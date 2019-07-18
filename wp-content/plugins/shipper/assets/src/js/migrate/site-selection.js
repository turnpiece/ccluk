;(function( $ ) {

	function handle( callback ) {
		return function( e ) {
			if ( e && e.preventDefault ) e.preventDefault();
			if ( e && e.stopPropagation ) e.stopPropagation();

			callback();

			return false;
		}
	}

	function get_modals_class() {
		return '.shipper-site_selection';
	}

	function get_modal( modal ) {
		var modal_class = [ get_modals_class(), modal ].join( '-' ),
			$modal = $( modal_class );
		return $modal;
	}

	function open_modal( modal ) {
		var $modal = get_modal( modal );
		if ( ! $modal.length ) {
			console.log( 'No such modal', modal );
			return false;
		}

		$( get_modals_class() ).attr( 'aria-hidden', true );
		$modal.attr( 'aria-hidden', false );
	}

	function send_request( act ) {
		var selection = get_modal( 'destination' ).find( '[name="site"] :selected' ),
			promise = $.post(
				ajaxurl,
				{
					action: act,
					_wpnonce: get_modal( 'destination' ).find( ':hidden[name="_wpnonce"]' ).val(),
					site: selection.val(),
					domain: selection.text()
				},
				function () {}
			);
		promise.fail( show_install_fail )
		return promise;
	}

	function activate_prepare_step( step ) {
		var $modal = get_modal( 'prepare' ),
			$steps = $modal.find( '.shipper-progress-steps [data-step]' ),
			$target = $steps.filter( '[data-step="' + step + '"]' ),
			$previous = $target.prevAll( '[data-step]' ),
			progress = parseInt( ( ( $previous.length + 1 ) / $steps.length ) * 100, 10 );

		$steps
			.filter( '.shipper-step-active' )
				.removeClass( 'shipper-step-active' )
				.find( 'i' ).remove();
		$target
			.addClass( 'shipper-step-active' )
			.append( '<i class="sui-icon-loader sui-loading"></i>' );

		$modal
			.find( '.sui-progress-text' )
				.text( progress + '%' )
			.end()
			.find( '.sui-progress-bar span' )
				.width( progress + '%' )
			.end()
		;
	}

	function show_site_prepare() {
		var destination = get_modal( 'destination' ).find( '[name="site"]' ).val();
		set_site_urls_in_modal( 'prepare' );
		open_modal( 'prepare' );

		activate_prepare_step( 'install' );
		send_request( 'shipper_install_activate' )
			.done( function ( resp ) {
				activate_prepare_step( 'activate' );
				setTimeout( function() {
					activate_prepare_step( 'add-to-api' );
					send_request( 'shipper_add_to_api' )
						.done( function( resp ) {
							if ( !! ( resp || {} ).success ) {
								move_to_preflight( destination );
							} else {
								show_install_fail();
							}
						} )
					;
				}, 1000 );
			} )
		;
	}

	function set_site_urls_in_modal( modal, domain ) {
		var dmn = domain || get_modal( 'destination' ).find( '[name="site"] :selected' ).text(),
			$modal = get_modal( modal ),
			$targets = $modal.find( '.shipper-site-domain' );
		$targets.text( dmn );
	}

	function show_install_fail() {
		set_site_urls_in_modal( 'install-fail' );
		open_modal( 'install-fail' );
	}

	function move_to_preflight( site_id ) {
		window.location.search += '&site=' + site_id;
		return false;
	}

	function prepare_selected_site() {
		var destination = get_modal( 'destination' ).find( '[name="site"]' ).val(),
			$button = get_modal( 'destination' ).find( 'button[type="submit"]' ),
			button = $button.html();
		if ( ! destination ) {
			return false;
		}
		$button
			.addClass( 'sui-button-onload' )
			.html(
				'<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>'
			);
		send_request( 'shipper_is_shippable' )
			.done( function ( resp ) {
				var status = ( resp || {} ).success;
				if ( status ) {
					return move_to_preflight( destination );
				} else {
					return show_site_prepare();
				}
			} )
			.always( function () {
				$button.html( button ).removeClass( 'sui-button-onload' );
			} )
		;
	}

	function show_hub_sites( resp ) {
		open_modal( 'destination' );
		var $target = get_modal( 'destination' ).find( '.shipper-selection.select-name' ),
			success = !! ( resp || {} ).success,
			sites = ( resp || {} ).data || [],
			content = '';
		if ( success ) {
			$.each( sites, function( idx, site ) {
				content += '<option value="' + idx + '">' + site + '</option>';
			} );
			$target.html(
				'<select name="site" class="sui-select">' + content + '</select>'
			);
			get_modal( 'destination' )
				.find( '[href="#refresh-locations"]' )
					.off( 'click' )
					.on( 'click', handle( load_hub_sites ) )
					.end()
				.find( 'button[type="submit"]' )
					.off( 'click' )
					.on( 'click', handle( prepare_selected_site ) )
					.end()
				.find( 'select[name="site"]' ).SUIselect2({dropdownCssClass: 'sui-select-dropdown'})
			;
		} else {
			open_modal( 'loading-error' );
		}
	}

	function load_hub_sites() {
		open_modal( 'loading' );
		$.post( ajaxurl, {
			action: 'shipper_list_hub_sites',
			_wpnonce: get_modal( 'loading' ).find( '[name="_wpnonce"]' ).val()
		} )
			.done( function( data ) {
				show_hub_sites( data );
			} )
			.error( function() {
				show_hub_sites();
			} );
	}

	function init() {
		if ( ! $( '.sui-box.shipper-select-site' ).length ) {
			return false;
		}
		setTimeout( load_hub_sites );
	}
	$( init );

})( jQuery );
