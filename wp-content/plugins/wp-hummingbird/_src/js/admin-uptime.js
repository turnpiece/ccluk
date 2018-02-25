( function( $ ) {
    WPHB_Admin.uptime = {
        module: 'uptime',
        $dataRangeSelector: null,
        chartData: null,
        downtimeChartData: null,
        timer:null,
        $spinner: null,
        dataRange: null,
        dateFormat: 'MMM d',
        init: function() {
            this.$spinner = $('.spinner');
            this.strings = wphbUptimeStrings;
            this.$dataRangeSelector = $( '#wphb-uptime-data-range' );
            this.chartData = $('#uptime-chart-json').val();
            this.downtimeChartData = $('#downtime-chart-json').val();
            this.$disableUptime = $('#wphb-disable-uptime');
            this.dataRange = this.getUrlParameter( 'data-range' );

            this.$dataRangeSelector.change( function() {
                window.location.href = $(this).find( ':selected' ).data( 'url' );
            });

            let self = this;
            this.$disableUptime.click( function(e) {
                e.preventDefault();
                self.$spinner.css( 'visibility', 'visible' );
                var value = $(this).is(':checked');
                if ( value && self.timer ) {
                    clearTimeout( self.timer );
                    self.$spinner.css( 'visibility', 'hidden' );
                }
                else {
                    // you have 3 seconds to change your mind
                    self.timer = setTimeout( function() {
                        location.href = self.strings.disableUptimeURL;
                    }, 3000 );
                }
            });
            /* If data range has been selected change the tab urls to retain the chosen range */
            if ( undefined !== this.dataRange ) {
                $('.wrap-wphb-uptime .wphb-tab a').each( function () {
                    this.href += '&data-range=' + self.dataRange;
                });
            }

            if ( 'day' === this.dataRange) {
                this.dateFormat = 'h:mma';
            }

            if ( null !== document.getElementById('uptime-chart') ) {
                this.drawResponseTimeChart();
            }
            if ( null !== document.getElementById('downtime-chart') ) {
                this.drawDowntimeChart();
            }

            /* Re-check Uptime status */
            $('#uptime-re-check-status').on( 'click', function(e){
                e.preventDefault();
                location.reload();
            });
        },

        drawResponseTimeChart: function() {
            let data = new google.visualization.DataTable();
            data.addColumn('datetime', 'Day');
            data.addColumn('number', 'Response Time (ms)');
            data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
            let chart_array = JSON.parse( this.chartData );
            for (let i = 0; i < chart_array.length; i++) {
                chart_array[i][0] = new Date( chart_array[i][0] );
                chart_array[i][1] = Math.round( chart_array[i][1] );
                chart_array[i][2] = this.createUptimeTooltip( chart_array[i][0], chart_array[i][1] );

                /* brings the graph below the x axis */
                if ( Math.round( chart_array[i][1] ) == 0 ) {
                    chart_array[i][1] = -100;
                }

            }

            data.addRows(chart_array);

            let options = {
                chartArea: {
                    left: 80,
                    top: 20,
                    width: '90%',
                    height: '90%'
                },
                colors: ['#24ADE5'],
                curveType: 'function',
                /*interpolateNulls: true,*/
                legend: { position: 'none' },
                vAxis: {
                    format: '#### ms',
                    viewWindow: { min: 0 } /* don't display negative values */
                },
                hAxis: {
                    format: this.dateFormat
                },
                tooltip: { isHtml: true },
                series: {
                    0: { axis: 'Resp' }
                },
                axes: {
                    y: {
                        Resp: { label: 'Response Time (ms)' }
                    }
                }
            };

            var chart = new google.visualization.AreaChart(document.getElementById('uptime-chart'));
            chart.draw(data, options);

            $(window).resize(function(){
                chart.draw(data, options);
            });
        },

        drawDowntimeChart: function() {
            var container = document.getElementById( 'downtime-chart' );
            var chart = new google.visualization.Timeline(container);
            var dataTable = new google.visualization.DataTable();
            dataTable.addColumn({ type: 'string' });
            dataTable.addColumn({ type: 'string', id: 'Status' });
            dataTable.addColumn({ type: 'string', role: 'tooltip', p: { 'html': true } });
            dataTable.addColumn({ type: 'datetime', id: 'Start Period' });
            dataTable.addColumn({ type: 'datetime', id: 'End Period' });
            let chart_array = JSON.parse( this.downtimeChartData );
            for (let i = 0; i < chart_array.length; i++) {
                chart_array[i][0] = chart_array[i][0];
                chart_array[i][1] = chart_array[i][1];
                chart_array[i][2] = chart_array[i][2];
                chart_array[i][3] = new Date( chart_array[i][3] );
                chart_array[i][4] = new Date( chart_array[i][4] );
            }
            dataTable.addRows(chart_array);
            var colors = [];
            var colorMap = {
                // should contain a map of category -> color for every category
                Down: '#FF6D6D',
                Unknown: '#F8F8F8',
                Up: '#D1F1EA'
            };
            for (var i = 0; i < dataTable.getNumberOfRows(); i++) {
                colors.push(colorMap[dataTable.getValue(i, 1)]);
            }
            var options = {
                timeline: {
                    showBarLabels: false,
                    showRowLabels: false,
                    barLabelStyle: {
                        fontSize: 33
                    },
                    avoidOverlappingGridLines: false
                },
                hAxis: {
                    format: this.dateFormat

                },
                colors: colors,
                height: 170
            };
            var origColors = [];
            google.visualization.events.addListener(chart, 'ready', function () {
                var bars = container.getElementsByTagName('rect');
                Array.prototype.forEach.call(bars, function(bar, index) {
                    if (parseFloat(bar.getAttribute('x')) > 0) {
                        origColors.push(bar.getAttribute('fill'));
                    }
                });
            });
            google.visualization.events.addListener(chart, 'onmouseover', function (e) {
                // set original color
                var bars = container.getElementsByTagName('rect');
                bars[bars.length - 1].setAttribute('fill', origColors[e.row]);
                var width = bars[bars.length - 1].getAttribute('width');
                if ( width > 3 ) {
                    bars[bars.length - 1].setAttribute('width', (width - 1) + 'px');
                }
            });
            chart.draw(dataTable, options);

            $(window).resize(function() {
                chart.draw(dataTable, options);
            });
        },
        createUptimeTooltip: function ( date, responseTime ) {
            let formatted_date = this.formatTooltipDate(date);
            return '<span class="response-time-tooltip">' + responseTime + 'ms</span>' +
                '<span class="uptime-date-tooltip">' + formatted_date + '</span>';
        },
        formatTooltipDate: function ( date ) {
            let monthNames = [
                "Jan", "Feb", "Mar",
                "Apr", "May", "Jun",
                "Jul", "Aug", "Sep",
                "Oct", "Nov", "Dec"
            ];

            let day        = date.getDate();
            let monthIndex = date.getMonth();
            let hh         = date.getHours();
            let h = hh;
            let minutes    = ( date.getMinutes() < 10 ? '0' : '' ) + date.getMinutes();
            let dd = "AM";
            if (h >= 12) {
                h = hh - 12;
                dd = "PM";
            }
            if (h == 0) {
                h = 12;
            }
            return monthNames[monthIndex] + ' ' + day + ' @ ' + h + ':' + minutes + dd;
        },
        getUrlParameter: function getUrlParameter( sParam ) {
            let sPageURL = decodeURIComponent(window.location.search.substring(1)),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

            for ( i = 0; i < sURLVariables.length; i++ ) {
                sParameterName = sURLVariables[i].split('=');

                if ( sParameterName[0] === sParam ) {
                    return sParameterName[1] === undefined ? true : sParameterName[1];
                }
            }
        }
    };
}(jQuery));