<template>
	<div class="beehive-users-resume">
		<pie-chart
			:id="chart"
			:chart-data="chartData"
			:options="chartOptions"
			class="beehive-charts beehive-charts-pie"
			:width="width"
			:height="height"
		/>

		<div class="beehive-users-stats">
			<p class="beehive-visitors-empty">
				{{ $i18n.desc.no_donut_data }}
			</p>
			<p class="beehive-visitors-old">
				<span>{{ newVisitors }}%</span>
				{{ $i18n.desc.new_visitors }}
			</p>
			<p class="beehive-visitors-new">
				<span>{{ returnVisitors }}%</span>
				{{ $i18n.desc.returning_visitors }}
			</p>
		</div>
	</div>
</template>

<script>
import PieChart from '@/components/charts/pie-chart'

export default {
	name: 'UsersDonut',

	props: ['stats'],

	components: { PieChart },

	data() {
		return {
			chart: 'beehive-audience-pie-chart',
			chartData: {},
			width: 40,
			height: 40,
			chartOptions: {
				legend: {
					display: false,
				},
				tooltips: {
					enabled: false,
				},
				responsive: false,
				maintainAspectRatio: false,
				cutoutPercentage: 50,
			},
		}
	},

	mounted() {
		// Setup chart.
		this.setupChartData()
	},

	watch: {
		// Whenever stats changes.
		stats(newStats) {
			this.setupChartData()
		},
	},

	computed: {
		/**
		 * Check if current user is logged in.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		isLoggedIn() {
			return this.$store.state.helpers.google.logged_in
		},

		/**
		 * Check if the stats are empty.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		isEmpty() {
			return Object.keys(this.stats).length <= 0 || !this.stats.summary
		},

		/**
		 * Get the new visitors count.
		 *
		 * @since 3.2.4
		 *
		 * @returns {int}
		 */
		newVisitors() {
			if (this.isEmpty) {
				return 0
			}

			return Math.round(this.stats.summary.user_sessions.new)
		},

		/**
		 * Get the return visitors count.
		 *
		 * @since 3.2.4
		 *
		 * @returns {int}
		 */
		returnVisitors() {
			if (this.isEmpty) {
				return 0
			}

			return Math.round(this.stats.summary.user_sessions.returning)
		},
	},

	methods: {
		/**
		 * Set the users chart data.
		 *
		 * @since 3.2.4
		 */
		setupChartData() {
			if (this.isEmpty) {
				this.chartData = {}
			} else {
				this.chartData = {
					datasets: [
						{
							label: this.$i18n.label.sessions,
							data: [this.newVisitors, this.returnVisitors],
							borderWidth: 0,
							backgroundColor: ['#17A8E3', '#0073AA'],
						},
					],
					labels: [
						this.$i18n.label.new_visitors,
						this.$i18n.label.returning_visitors,
					],
				}
			}
		},
	},
}
</script>
