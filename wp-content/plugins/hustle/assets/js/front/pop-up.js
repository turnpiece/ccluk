(function( $, doc, win ) {
	"use strict";
	if( inc_opt.is_upfront ) return;

	Optin = window.Optin || {};
	
	Optin.PopUp = Optin.Module.extend({
		className: 'wph-modal',
		type: 'popup'
	});
}(jQuery, document, window));
