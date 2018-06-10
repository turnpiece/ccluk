Hustle.define("Dashboard.View", function($, doc, win){
	"use strict";

	if( pagenow !== 'toplevel_page_hustle' || _.isTrue( optin_vars.is_free ) ) return;

	var dashboard_view = Backbone.View.extend({
		el: ".wph-dashboard",
		conversions_chart: null,
		chart_data: null,
		chart_options : null,
		empty_chart: true,
		default_dataset_options: {
				fill: false,
				cubicInterpolationMode: 'monotone',
				borderCapStyle: 'butt',
				borderDash: [],
				borderWidth: 1,
				borderDashOffset: 0.0,
				borderJoinStyle: 'miter',
				pointBackgroundColor: "#fff",
				pointBorderWidth: 3,
				pointHoverRadius: 5,
				pointHoverBorderColor: "rgba(220,220,220,1)",
				pointHoverBorderWidth: 2,
				pointRadius: 1,
				pointHitRadius: 10,
				spanGaps: false
		},
		events: {
			"click .wph-icon.i-close": "close",
			"click .wpmudev-box-action": "hide"
		},
		initialize: function( opts ){

			if ( !_.isEmpty( optin_vars.top_active_modules ) && typeof google !== 'undefined' ) {
				google.charts.load('current', {packages: ['corechart', 'line']});
				google.charts.setOnLoadCallback(this.drawLineStyles);
			}

			return this.render();
		},
		drawLineStyles: function() {
			var data = new google.visualization.DataTable();
			data.addColumn('date', 'Day');

			var colors_arr = [];

			_.each( optin_vars.top_active_modules, function( module, key ) {
				data.addColumn( 'number', module.module_name );
				colors_arr.push(module.color);
			} );

			if ( _.isEmpty( optin_vars.graph_date_conversions ) ) {
				return;
			}

			_.each( optin_vars.graph_date_conversions, function( date_conversion, key ) {
				var date_data = [ new Date( date_conversion['formatted'] ) ];
				_.each( date_conversion['conversions'], function( conversion ) {
					date_data.push(conversion);
				} );
				data.addRows([date_data]);
			} );

			var options = {
			  curveType: 'function',
			  legend: { position: 'none' },
			  animation: {
				startup: true,
				duration: 500,
				easing: 'in',
			  },
			  colors: colors_arr,
			  pointSize: 5,
			  lineWidth: 1,
			  interpolateNulls: true,
			  chartArea: {
					top: 10,
					right: 10,
					bottom: 40,
					left: 40
				},
				hAxis: {
					format: 'd MMM',
					viewWindow: {
						min: new Date(optin_vars.graph_min_date),
						max: new Date(optin_vars.graph_max_date)
					},
					baseline: new Date(optin_vars.graph_min_date),
					baselineColor: '#D8D8D8',
					gridlines: {
						color: 'transparent',
						count: 5
					},
					textStyle: {
						color: '#666666',
						fontSize: 14,
						fontName: 'Roboto Condensed'
					},
				},
				vAxis: {
					viewWindow: {
						min: 0
					},
					gridlines: {
						color: '#D8D8D8',
						count: 5
					},
					baseline: 0,
					baselineColor: '#D8D8D8',
					textStyle: {
						color: '#666666',
						fontSize: 14,
						fontName: 'Roboto Condensed'
					}
				},
			  tooltip: {showColorCode: true }
			};

			var chart = new google.visualization.LineChart(document.getElementById('hustle_chart'));
			chart.draw(data, options);
		},
		render: function(){
			$(".tabs-header li label").on('click', this.toggle_overview);
			$(".can-close .wph-icon.i-close").on('click', this.close);
			$(".can-hide .wpmudev-box-action").on('click', this.hide);
			$("a#sshare_view_all_stats").on('click', this.toggle_sshare_modal);
			$(".wpmudev-box-head .wpmudev-icon.wpmudev-i_close").on('click', this.close);
			$("#wpmudev-dashboard-widget-modules .wpmudev-box-head").on('click', this.toggle_module_accordion);
			this.handle_sshare_modal_pagination();

			var canvas = $("#conversions_chart");
			if( !canvas.length ) return;

			if(!this.empty_chart){
				// setting canvas height
				var $module_table = canvas.closest('#wph-module-stats').find('table.wph-table.wph-module--stats'),
					module_table_height = $module_table.outerHeight();

				if ( module_table_height > 230 ) {
					canvas.attr('height', module_table_height);
				} else {
					canvas.attr('height', 230);
				}

				// sort the dates properly
				for( var key in this.chart_data.datasets ) {
					if ( this.chart_data.datasets[key].data ) {
						this.chart_data.datasets[key].data = _.sortBy(this.chart_data.datasets[key].data, "x");
					}
				}

				// rendering the chart
				this.conversions_chart = new Chart(canvas, {
					type: 'line',
					data: this.chart_data,
					options: this.chart_options
				});

			} else {
				canvas.parent()
					.css('height', '100%')
					.css('width', '100%')
					.css('display', 'table')
				;

				var $no_data = $('<div class="graph-no-data">' + optin_vars.messages.dashboard.not_enough_data + '</div>');
				$no_data
					.css('display', 'table-cell')
					.css('text-align', 'center')
					.css('vertical-align', 'middle')
				;
				canvas.replaceWith($no_data);
			}
		},
		toggle_module_accordion: function(e) {
			var $this = $(e.target),
				$body = $this.parents('.wpmudev-box').find(".wpmudev-box-body"),
				$head = $this.parents('.wpmudev-box').find(".wpmudev-box-head")
			;

			$body.slideToggle( 'fast', function(){
				$head.toggleClass('wpmudev-collapsed');
				$body.toggleClass('wpmudev-hidden');
			} );
		},
		close: function(e){
			e.preventDefault();
			// var $parent_section = $(e.target).closest('.content-box').remove();
			var $parent_container = $(e.target).closest('.wpmudev-row'),
				$parent_section = $(e.target).closest('#wph-welcome'),
				nonce = $parent_section.data("nonce")
			;
			$parent_container.slideToggle(300, function(){
				$.ajax({
					url: ajaxurl,
					type: "POST",
					data: {
						action: "persist_new_welcome_close",
						_ajax_nonce: nonce
					},
					complete: function(d){
						$parent_container.remove();
					}
				});
			});
		},
		hide: function(e){
			e.preventDefault();
			var $parent_body = $(e.target).closest(".wpmudev-box-head").next(".wpmudev-box-body");
			$parent_body.toggleClass(".wpmudev-hide");
		},
		toggle_overview: function(e){
			e.preventDefault();
			var $this = $(e.target),
				value = $this.find('input').val(),
				$target = $("#wph-"+ value +"-overview"),
				$li = $this.parent();

			$(".wph-modules-overview").not($target).removeClass("current");
			$target.addClass("current");
			$(".tabs-header li").not($li).removeClass("current");
			$li.addClass("current");
		},
		toggle_sshare_modal: function(e) {
			e.preventDefault();
			var $stats_modal = $('#wph-comulative-shares-modal');
			$stats_modal.toggleClass('wpmudev-modal-active');
			$stats_modal.find('.wpmudev-box-modal').toggleClass('wpmudev-show');
		},
		handle_sshare_modal_pagination: function(){
			$("li.wph-sshare--prev_page a").on('click', $.proxy(this.sshare_modal_prev, this));
			$("li.wph-sshare--next_page a").on('click', $.proxy(this.sshare_modal_next, this));
			$("li.wph-sshare--page_number a").on('click', $.proxy(this.sshare_modal_goto, this));
		},
		sshare_modal_prev: function(e) {
			e.preventDefault();
			e.stopImmediatePropagation();

			var $li = $(e.target).closest('li'),
				$ul = $li.parent(),
				nonce = $ul.data('nonce'),
				$current = $ul.find('li.wph-sshare--current_page'),
				$page_number = $ul.find('li.wph-sshare--page_number'),
				$next = $ul.find('li.wph-sshare--next_page'),
				total = parseInt($ul.data('total')),
				current_page = parseInt($current.data('page')),
				prev_target = parseInt($li.data('page'));

			// update current page
			$current.data('page', prev_target);

			// update next link
			var $new_next_html = $next.find('svg');
			$next.data( 'page', current_page );
			if ( $new_next_html.length ) {
				$next.html('<a href="#">'+ $new_next_html[0].outerHTML +'</a>');
			}

			if ( prev_target == 1 ) {
				// disable prev button
				var $new_html = $li.find('svg');
				if ( $new_html.length ) {
					$li.html('<span>' + $new_html[0].outerHTML + '</span>');
				}
			}

			// update page number
			if ( $page_number.length ) {
				$page_number.data('page', current_page);
				$page_number.find('a').text(current_page);
			} else {
				var page_number_html = '<li class="wph-link wph-sshare--page_number" data-page="'+ current_page +'"><a href="#">'+ current_page +'</a></li>';
				$(page_number_html).insertAfter($current);
			}

			$current.find('span').text(prev_target);
			$li.data('page', prev_target - 1);
			this.handle_sshare_modal_pagination();
			this.sshare_show_page_content(prev_target, nonce);
		},
		sshare_modal_next: function(e) {
			e.preventDefault();
			e.stopImmediatePropagation();

			var $li = $(e.target).closest('li'),
				$ul = $li.parent(),
				nonce = $ul.data('nonce'),
				$current = $ul.find('li.wph-sshare--current_page'),
				$page_number = $ul.find('li.wph-sshare--page_number'),
				$prev = $ul.find('li.wph-sshare--prev_page'),
				total = parseInt($ul.data('total')),
				current_page = parseInt($current.data('page')),
				next_target = parseInt($li.data('page'));

			// update current page
			$current.data('page', next_target);

			// update prev link
			var $new_prev_html = $prev.find('svg');
			$prev.data( 'page', current_page );
			if ( $new_prev_html.length ) {
				$prev.html('<a href="#">'+ $new_prev_html[0].outerHTML +'</a>');
			}

			if ( next_target < total ) {
				// update page number
				if ( $page_number.length ) {
					var next_next_page = next_target + 1;
					$page_number.data('page', next_next_page);
					$page_number.find('a').text(next_next_page);
					$li.data('page', next_next_page);
				}
			} else {
				// remove page number and disable next button
				if ( $page_number.length ) $page_number.remove();
				var $new_html = $li.find('svg');
				if ( $new_html.length ) {
					$li.html('<span>' + $new_html[0].outerHTML + '</span>');
				}
			}

			$current.find('span').text(next_target);
			this.handle_sshare_modal_pagination();
			this.sshare_show_page_content(next_target, nonce);
		},
		sshare_modal_goto: function(e) {
			e.preventDefault();
			e.stopImmediatePropagation();

			var $li = $(e.target).closest('li'),
				$ul = $li.parent();

			// fire the next button click event
			$ul.find("li.wph-sshare--next_page a").click();
		},
		sshare_show_page_content: function(page, nonce){
			var ss_modal_template = Optin.template('wpmudev-hustle-sshare-stats-modal-tpl'),
				$table_items = $('#wph-comulative-shares-modal table.wpmudev-table-paginated tbody');

			$table_items.html('<div class="wph-sshare--loading_stats"><span class="on-action">Loading...</span></div>');

			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: {
					action: "sshare_show_page_content",
					page_id: page,
					_ajax_nonce: nonce
				},
				complete: function(resp){
					var data = resp.responseJSON.data,
						items_html = ss_modal_template( _.extend( {}, data ) );

					$table_items.html(items_html);
				}
			});
		}
	});

	var dash_view = new dashboard_view();
});
