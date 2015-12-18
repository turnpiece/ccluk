( function( $ ) {

	$( document ).ready(function() {

		if ( mailbag_js_vars.ajaxURL ) {

			// Get the MailChimp form URL
			var url = mailbag_js_vars.ajaxURL;

			// Add the json string
			var ajaxURL = url.replace('/subscribe', '/subscribe/post-json');

			// Use the json string to submit
			$( "#mailbag_mailchimp" ).ajaxChimp({
			    url: ajaxURL
			});

		}

	});

} )( jQuery );