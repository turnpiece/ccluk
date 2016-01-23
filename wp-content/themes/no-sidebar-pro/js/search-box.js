jQuery(function( $ ){

	var placeholder = $( '.site-header .search-form input[type="search"]' ).attr('placeholder');

	$( '.site-header .search-form input[type="search"]' ).on( 'focusin', function() {
		$(this).attr('placeholder', '');
	});

	$( '.site-header .search-form input[type="search"]' ).on( 'focusout', function() {
		$(this).attr('placeholder', placeholder);
	});

});