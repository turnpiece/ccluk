(function( $ ){

	$( window ).load( function() {
		var $panel = $( '#mobile-right-panel' );

		if ($panel.length) {
			$panel.find( '.menu-item-has-children' ).each( function () {
				/* close submenu */
				if ($( this ).hasClass( 'current-menu-item' ) ||
					$( this ).hasClass( 'current-menu-parent') ||
					$( this ).hasClass( 'current-menu-ancestor' )) {
					
					$( this ).find( '.submenu-btn.fa-angle-left' ).trigger( 'click' );
				} else
					$( this ).find( '.submenu-btn.fa-angle-down' ).trigger( 'click' );
			} );
		}
	} );

})( jQuery );
