var ga_type = 0;
var ga_load_mode = 0;
var ga_date_range = 0;
var ga_retry = 0;

jQuery(document).ready(function() {
	if( jQuery('.google-analytics-frontend-widget').length > 0 ) {
		if(typeof ga != 'undefined' && typeof ga.type != 'undefined')
			ga_type = ga.type;
		if(typeof ga != 'undefined' && typeof ga.load_mode != 'undefined')
			ga_load_mode = ga.load_mode;

		if(ga_type && ga_load_mode){
			ga_date_range = ga.date_range;

			ga_load_google_analytics();
		}
	}
});

function ga_load_google_analytics() {
	if(typeof ga != 'undefined' && typeof ga.post != 'undefined')
		post = ga.post;
	else
		post = 0;
	if(typeof ga != 'undefined' && typeof ga.network_admin != 'undefined')
		network_admin = ga.network_admin;
	else
		network_admin = 0;

	ga_retry = ga_retry + 1;

	jQuery.post(ga.ajax_url, {action: 'load_google_analytics', type: ga_type, post: post, network_admin: network_admin, date_range: ga_date_range }, function(data){ //post data to specified action trough special WP ajax page
		var data = jQuery.parseJSON(data);

		if(data['error'] != true || ga_retry == 5) {
			jQuery('.google-analytics-frontend-widget').html(data['html']);
		}
		else
			if(ga_retry < 5)
				ga_load_google_analytics();
			else
				jQuery('.google-analytics-frontend-widget li').text(ga.problem_loading_data);

		if(window.console && data['error'] == true){
			console.log(data['html']);
		}
	});
}