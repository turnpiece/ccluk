(function( $ ){

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

	function checkRadioLabels() {
		$('div.radio').find('label').each( function() {
			if ($(this).find('input').is(':checked'))
				$(this).addClass('selected');
			else
				$(this).removeClass('selected');
		})
	}

	$('div.radio').find('label').find('input').on('click', function() {
		checkRadioLabels();
	});

	checkRadioLabels();

})( jQuery );
