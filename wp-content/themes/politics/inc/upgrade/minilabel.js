/**
 * Upgrade notice
 */
( function( $ ) {

	// Add Upgrade Message to section
	if ('undefined' !== typeof politicsMiniUpgrade) {
		upsellMini = $('<span class="politics-upgrade-link"></span>')
			.text(politicsMiniUpgrade.politicsMiniUpgradeLabel)
			.css({
				'display' : 'inline-block',
				'background-color' : '#93b800',
				'color' : '#fff',
				'text-transform' : 'uppercase',
				'margin-top' : '1px',
				'padding' : '3px 6px',
				'font-size': '9px',
				'letter-spacing': '1px',
				'line-height': '1.5',
				'clear' : 'both',
				'float' : 'right',
				'margin-right' : '30px'
			});

		setTimeout(function () {
			$('#accordion-section-plus-home-control h3').append(upsellMini);
		}, 300);

	}

} )( jQuery );
