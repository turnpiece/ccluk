<template>
	<sui-box
		:title="$i18n.label.social_networks"
		titleIcon="share"
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
				<pie-chart
					class="beehive-chart-pie"
					:chart-data="chartData"
					:options="chartOptions"
				/>
				<ul class="beehive-chart-pie-legend">
					<li
						class="beehive-legend-item"
						v-for="(item, name) in stats.social_networks"
						:key="name"
					>
						<span
							class="beehive-legend-item-color"
							aria-hidden="true"
						></span>
						<span class="beehive-legend-item-name">
							<span>{{ item[0] }}</span>
							<strong>{{ item[1] }}</strong>
						</span>
					</li>
				</ul>
			</fragment>
		</template>
	</sui-box>
</template>

<script>
import SuiBox from '@/components/sui/sui-box'
import PieChart from '@/components/charts/pie-chart'

export default {
	name: 'SocialNetworks',

	props: ['stats', 'loading'],

	components: {
		SuiBox,
		PieChart,
	},

	data() {
		return {
			chartData: {},

			chartOptions: {
				legend: {
					display: false,
				},
				tooltips: {
					xPadding: 15,
					yPadding: 15,
					backgroundColor: 'rgba(51,51,51,0.85)',
					titleSpacing: 0,
					titleMarginBottom: 0,
					bodyFontColor: '#FFFFFF',
					bodyFontSize: 14,
					bodyFontFamily: 'Roboto',
					bodyFontStyle: 'bold',
					bodyAlign: 'left',
					cornerRadius: 4,
					displayColors: false,
				},
				responsive: true,
				maintainAspectRatio: false,
			},
		}
	},

	watch: {
		// Whenever stats changes.
		stats: function (newStats) {
			this.setupChartData()
		},
	},

	computed: {
		isLoggedIn() {
			return this.$store.state.helpers.google.logged_in
		},

		isEmpty() {
			return (
				Object.keys(this.stats).length <= 0 ||
				!this.stats.social_networks
			)
		},
	},

	methods: {
		setupChartData() {
			if (this.isEmpty) {
				this.chartData = {}

				return
			}

			let vm = this

			let chartData = {
				labels: [],
				datasets: [
					{
						label: 'Sessions', // Get the dataset label based on selected option.
						data: [],
						borderWidth: 2,
						borderColor: '#FFFFFF',
						backgroundColor: [],
						hoverBorderWidth: '#FFFFFF',
					},
				],
			}

			let chartLabels = []
			let currentData = []
			let chartColors = []

			Object.keys(vm.stats.social_networks).forEach(function (key) {
				chartLabels.push(vm.stats.social_networks[key][0])
				currentData.push(vm.stats.social_networks[key][1])

				if (key % 2) {
					chartColors.push('#0582B5')
				} else {
					chartColors.push('#17A8E3')
				}
			})

			// Update the data.
			chartData.labels = chartLabels
			chartData.datasets[0].data = currentData
			chartData.datasets[0].backgroundColor = chartColors

			this.chartData = chartData
		},
	},
}
</script>
