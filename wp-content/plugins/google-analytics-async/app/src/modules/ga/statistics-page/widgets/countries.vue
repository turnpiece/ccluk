<template>
	<sui-box
		:title="$i18n.label.top_countries"
		titleIcon="web-globe-world"
		aria-live="polite"
		:loading="loading"
	>
		<template v-slot:body>
			<p class="beehive-loading-text" v-if="true === loading">
				<span
					class="sui-icon-loader sui-loading"
					aria-hidden="true"
				></span>
				{{ $i18n.label.fetching_data }}
			</p>

			<p class="sui-description" v-if="!isLoggedIn || isEmpty">
				{{ $i18n.label.no_information }}
			</p>

			<fragment v-else>
				<GChart
					type="GeoChart"
					:settings="{ packages: ['geochart'] }"
					:data="chartData"
					:options="chartOptions"
					@ready="onChartReady"
				/>

				<table class="beehive-chart-map-legend">
					<thead>
						<tr>
							<th>{{ $i18n.label.country_code }}</th>
							<th>{{ $i18n.label.country_name }}</th>
							<th>{{ $i18n.label.visits_percentage }}</th>
							<th>{{ $i18n.label.total_visits }}</th>
						</tr>
					</thead>

					<tbody>
						<tr
							class="beehive-legend-item"
							v-for="(item, name) in getCountries"
							:key="name"
						>
							<td class="beehive-legend-item-flag">
								<span class="sui-screen-reader-text">{{
									item[1]
								}}</span>
								<span
									:class="
										'beehive-flag beehive-flag-' + item[1]
									"
									aria-hidden="true"
								></span>
							</td>

							<td class="beehive-legend-item-name">
								{{ item[0] }}
							</td>

							<td class="beehive-legend-item-bar">
								<span class="sui-screen-reader-text">{{
									sprintf(
										$i18n.desc.percentage_visits,
										visitsPercent(item[2]) + '%'
									)
								}}</span>
								<span aria-hidden="true">
									<span
										:style="
											'width: ' +
											visitsPercent(item[2]) +
											'%;'
										"
									></span>
								</span>
							</td>

							<td class="beehive-legend-item-value">
								{{ item[2] }}
							</td>
						</tr>
					</tbody>
				</table>
			</fragment>
		</template>
	</sui-box>
</template>

<script>
import { GChart } from 'vue-google-charts'
import SuiBox from '@/components/sui/sui-box'

export default {
	name: 'Countries',

	props: ['stats', 'loading'],

	components: {
		GChart,
		SuiBox,
	},

	data() {
		return {
			chartData: {},
			chartOptions: {
				chart: {
					title: this.$i18n.label.country_sessions,
				},
				colorAxis: {
					colors: ['#6DD5FF', '#49BFEF', '#17A8E3', '#0582B5'],
				},
				backgroundColor: {
					fill: '#FFFFFF',
					strokeWidth: 0,
				},
				datalessRegionColor: '#DDDDDD',
				tooltip: {
					isHtml: true,
					showTitle: false,
					ignoreBounds: true,
					textStyle: {
						color: '#FFFFFF',
						fontName: 'Roboto',
						fontSize: 13,
					},
				},
			},
			chart: null,
			chartApi: null,
		}
	},

	watch: {
		stats(newStats) {
			this.setupList()
		},
	},

	methods: {
		visitsPercent(value) {
			const self = this

			let topValue = 0

			Object.keys(this.stats.countries).forEach(function (key) {
				topValue += parseInt(self.stats.countries[key][2])
			})

			return (parseInt(value) * 100) / topValue
		},

		setupList() {
			// Data and map api should be available.
			if (this.isEmpty || !this.chartApi) {
				this.chartData = {}

				return
			}

			let vm = this

			let chartData = []

			const dataTable = new this.chartApi.visualization.DataTable()

			dataTable.addColumn('string', this.$i18n.label.country)
			dataTable.addColumn('number', this.$i18n.label.sessions)
			dataTable.addColumn({
				type: 'string',
				role: 'tooltip',
				p: { html: true },
			})

			Object.keys(vm.stats.countries).forEach(function (key) {
				chartData.push([
					vm.stats.countries[key][0],
					vm.stats.countries[key][2],
					vm.geoToolTip(
						vm.stats.countries[key][0],
						vm.stats.countries[key][1],
						vm.stats.countries[key][2]
					), // Custom tooltip.
				])
			})

			dataTable.addRows(chartData)

			this.chartData = dataTable
		},

		/**
		 * Create custom tooltip html.
		 *
		 * @param {string} countryName
		 * @param {string} countryCode
		 * @param {integer} sessions
		 *
		 * @since 3.2.0
		 *
		 * @returns {string}
		 */
		geoToolTip(countryName, countryCode, sessions) {
			return (
				'<span class="beehive-charts-geotooltip">' +
				'<span class="beehive-flag beehive-flag-unframed beehive-flag-' +
				countryCode +
				'" aria-hidden="true"></span>' +
				'<span class="beehive-country-sessions">' +
				'<span class="beehive-country-name">' +
				countryName +
				'</span>' +
				'<span class="sui-screen-reader-text">' +
				this.$i18n.label.has +
				'</span> ' +
				'<strong>' +
				sessions +
				'</strong> ' +
				this.$i18n.label.sessions +
				'</span>' +
				'</span>'
			)
		},

		/**
		 * On chart ready, setup chart API object.
		 *
		 * @param chart
		 * @param api
		 *
		 * @since 3.2.4
		 */
		onChartReady(chart, api) {
			this.chartApi = api

			if (this.isEmpty) {
				return
			}

			// Setup list if ready.
			this.setupList()
		},
	},

	computed: {
		isLoggedIn() {
			return this.$store.state.helpers.google.logged_in
		},

		isEmpty() {
			return Object.keys(this.stats).length <= 0 || !this.stats.countries
		},

		getCountries() {
			let countries = []

			if (this.stats.countries) {
				countries = this.stats.countries.slice(0, 5)
			}

			return countries
		},
	},
}
</script>
