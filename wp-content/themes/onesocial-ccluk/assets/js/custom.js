(function( $ ){

	$( window ).load( function() {
	    $( '#mobile-right-panel .menu-item-has-children' ).each( function () {
	    	/* close submenu */
	        $( this ).find( '.submenu-btn' ).trigger( 'click' );
	    } );

	} );

	/*------------------------------------------------------------------------------------------------------
	 Home page banner
	 --------------------------------------------------------------------------------------------------------*/

	/*
	 * prevent widows in banner
	 *
	 *
	 */
    var path = 'body.home-page .site-content.banner > .section-title-container > .section-title';
    $(path+' p, '+path+' h2').each(function(){
        var string = $(this).html();
        $(this).html(string.replace(/\s(?=[^\s]*$)/g, "&nbsp;").replace(/\s(?=[^\s]*$)/g, "&nbsp;"));
    });

})( jQuery );
