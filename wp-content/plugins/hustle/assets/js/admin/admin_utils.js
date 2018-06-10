(function( $ ) {
	"use strict";
	
	var Module = window.Module || {};
	
	Module.Validate = {
		validate_module_name: function() {
			var success = true;
			
			if ( $('input[name=module_name]').length ) {
				var elem = $('input[name=module_name]'),
				    error_label = elem.next('.wpmudev-label--notice');
				success = elem.val().length !== 0;
				if ( !success ){
					elem.focus();
					if ( error_label.hasClass('wpmudev-hidden') ) {
						error_label.removeClass('wpmudev-hidden');
					}
				} else {
					if ( !error_label.hasClass('wpmudev-hidden') ) {
						error_label.addClass('wpmudev-hidden');
					}
					
				}
			}
			return success;
		},
		on_change_validate_module_name: function(e) {
			var val = $(e.target).val(),
				error_label = $(e.target).next('.wpmudev-label--notice');
			if(val.length !== 0 ){
				if ( !error_label.hasClass('wpmudev-hidden') ) {
					error_label.addClass('wpmudev-hidden');
				}
			} else{
				if ( error_label.hasClass('wpmudev-hidden') ) {
					error_label.removeClass('wpmudev-hidden');
				}
			}
		}
	};
	
	
}(jQuery));
