var ga_chart_visitors = 0;
var ga_chart_countries = 0;
var ga_type = 0;
var ga_load_mode = 0;
var ga_width = 0;
var ga_date_range = 0;
var ga_retry = 0;

function gaa_load_callback () {
	if( jQuery('#google-analytics-statistics-page').length > 0 || jQuery('#google-analytics-widget').length > 0  ) {
		if(typeof ga != 'undefined' && typeof ga.type != 'undefined')
			ga_type = ga.type;
		if(typeof ga != 'undefined' && typeof ga.load_mode != 'undefined')
			ga_load_mode = ga.load_mode;

		if(ga_type && ga_load_mode){
			ga_date_range = ga.date_range;

			if(typeof ga != 'undefined' && typeof ga.chart_visitors != 'undefined')
				ga_chart_visitors = ga.chart_visitors;
			if(typeof ga != 'undefined' && typeof ga.chart_countries != 'undefined')
				ga_chart_countries = ga.chart_countries;

			if(ga_chart_visitors || ga_chart_countries)
				ga_load_charts();
			else
				if(ga_type == 'post' && ga.load_mode == 'soft') {
					jQuery("#load-post-stats").click(function(event) {
						event.preventDefault()
						jQuery('#google-analytics-widget #load-post-stats').hide();
						jQuery('#google-analytics-widget .loading').show();
						ga_load_google_analytics();
					});
				}
				else
					ga_load_google_analytics();
		}
	}
}

google.load("visualization", "1", {packages:["corechart"], callback: gaa_load_callback});
google.setOnLoadCallback(gaa_load_callback);

jQuery(document).ready(function() {
	setInterval(ga_check_width, 200);
});
function ga_check_width() {
	var current_width = jQuery("#google-analytics-chart-visitors").width();
	if(ga_type && current_width != 100 && ga_width != current_width)
		ga_load_charts('chart_countries');
}

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

	jQuery.post(ajaxurl, {action: 'load_google_analytics', type: ga_type, post: post, network_admin: network_admin, date_range: ga_date_range }, function(data){ //post data to specified action trough special WP ajax page
		var data = jQuery.parseJSON(data);

		if(data['error'] != true || ga_retry == 5) {
			jQuery('#google-analytics-widget, #google-analytics-statistics-page').html(data['html']);

			ga_chart_visitors = data['chart_visitors'];
			ga_chart_countries = data['chart_countries'];
			ga_load_charts();
		}
		else
			if(ga_retry < 5)
				ga_load_google_analytics();
			else
				if(data['type'] == 'statistics_page')
					window.location.reload();

		if(window.console && data['error'] == true){
			console.log(data['html']);
		}
	});
}
function ga_load_charts(disable) {
	if(typeof(disable)==='undefined') disable = '';

	if(ga_chart_visitors && ga_chart_visitors.length > 1 && disable != 'chart_visitors') {
		console.log(ga_chart_visitors);
		var data = google.visualization.arrayToDataTable(ga_chart_visitors);

		var options = {
			hAxis: {title: ga.chart_visitors_title,  titleTextStyle: {color: '#333'}},
			vAxis: {minValue: 0},
			backgroundColor: 'none',
			legend: {position: 'bottom'},
			chartArea: {top: 10, left: 60, width: '95%'},
			focusTarget: 'category'
		};

		var chart_visitors = new google.visualization.AreaChart(document.getElementById('google-analytics-chart-visitors'));
		chart_visitors.draw(data, options);
		ga_width = jQuery("#google-analytics-chart-visitors").width();
	}
	if(ga_chart_countries && disable != 'chart_countries') {
		var data = google.visualization.arrayToDataTable(ga_chart_countries);

		var options = {
			legend: {position: 'bottom'},
			colorAxis: {colors: ['#EEEEEE', '#52ACCC']},
			enableRegionInteractivity: true,
		};

		var chart_countries = new google.visualization.GeoChart(document.getElementById('google-analytics-chart-countries'));
		chart_countries.draw(data, options);
		jQuery("#google-analytics-statistics-page .google-analytics-countries .inside").scrollLeft( 135 );
	}
}
