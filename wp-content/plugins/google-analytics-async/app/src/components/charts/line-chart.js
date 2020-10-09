import { Line, mixins } from 'vue-chartjs'

export default {
	extends: Line,

	mixins: [mixins.reactiveProp],

	props: {
		id: {
			type: String,
			required: true,
		},
		chartData: {
			type: Object,
			default: {},
		},
		options: {
			type: Object,
			default: {},
		},
	},

	created() {
		// On chart update event, update the chart object.
		this.$root.$on('updateLineChart', (data) => {
			if (data.chart === this.id) {
				// Update the options.
				this.$data._chart.options = this.options
				// Update the chart for new data.
				return this.$data._chart.update()
			}
		})
	},

	mounted() {
		// Render the chart.
		this.renderChart(this.chartData, this.options)
	},
}
