( function( $ ) {
    WPHB_Admin.uptime = {
        module: 'uptime',
        $dataRangeSelector: null,
        chartData: null,
        timer:null,
        $spinner: null,
        init: function() {
            this.$spinner = $('.spinner');
            this.strings = wphbUptimeStrings;
            this.$dataRangeSelector = $( '#wphb-uptime-data-range' );
            this.chartData = $('#uptime-chart-json').val();
            this.$disableUptime = $('#wphb-disable-uptime');

            this.$dataRangeSelector.change( function() {
                window.location.href = $(this).find( ':selected' ).data( 'url' );
            });

            var self = this;
            this.$disableUptime.change( function() {
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

                return;
            });

            this.drawChart();

            /* Re-check Uptime status */
            $('#uptime-re-check-status').on( 'click', function(e){
                e.preventDefault();
                location.reload();
            });
        },

        drawChart: function() {
            var data = new google.visualization.DataTable();
            data.addColumn('datetime', 'Day');
            data.addColumn('number', 'Response Time (ms)');

            var chart_array = JSON.parse( this.chartData );
            for (var i = 0; i < chart_array.length; i++) {
                chart_array[i][0] = new Date( chart_array[i][0] );
                chart_array[i][1] = Math.round( chart_array[i][1] );

                /* brings the graph below the x axis */
                if ( Math.round( chart_array[i][1] ) == 0 ) {
                    chart_array[i][1] = -100;
                }

            }

            data.addRows(chart_array);

            var options = {
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
                    format: '#,### ms',
                    viewWindow: { min: 0 } /* don't display negative values */
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

            var chart = new google.visualization.LineChart(document.getElementById('uptime-chart'));
            chart.draw(data, options);

            $(window).resize(function(){
                chart.draw(data, options);
            });
        }
    };
}(jQuery));