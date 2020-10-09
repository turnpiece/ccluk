import { Doughnut, mixins } from 'vue-chartjs'

export default {
	extends: Doughnut,

	mixins: [mixins.reactiveProp],

	props: {
		width: {
			type: Number,
			default: 60,
		},
		height: {
			type: Number,
			default: 60,
		},
		chartData: {
			type: Object,
			default: null,
		},
		options: {
			type: Object,
			default: null,
		},
	},

	mounted() {
		// Render chart.
		this.renderChart(this.chartData, this.options)
	},
}
