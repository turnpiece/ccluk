(function( $ ){

	$( window ).load( function() {
	    $( '#mobile-right-panel .menu-item-has-children' ).each( function () {
	    	/* close submenu */
	        $( this ).find( '.submenu-btn.fa-angle-down' ).trigger( 'click' );
	    } );

	} );

})( jQuery );
