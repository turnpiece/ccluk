<template>
	<div class="beehive-widget-wrap" :class="wrapClass">
		<p class="beehive-loading-text" v-if="loading">
			<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
			{{ $i18n.label.fetching_data }}
		</p>

		<widget-header @refreshStats="getStats" @periodChange="periodChange" />

		<widget-body :stats="stats" />
	</div>
</template>

<script>
import { restGetStats } from '@/helpers/api'
import WidgetBody from './views/widget-body'
import WidgetHeader from './views/widget-header'

export default {
	name: 'App',

	components: {
		WidgetHeader,
		WidgetBody,
	},

	data() {
		return {
			stats: {},
			dateStart: this.$vars.dates.start_date,
			dateEnd: this.$vars.dates.end_date,
			loading: false,
			small: false,
		}
	},

	created() {
		// On page resize.
		window.addEventListener('resize', this.smallClass)
	},

	mounted() {
		this.getStats()
		this.smallClass()
	},

	destroyed() {
		window.removeEventListener('resize', this.smallClass)
	},

	computed: {
		wrapClass() {
			return {
				'beehive-widget-small': this.small,
				'beehive-loading': this.loading,
			}
		},

		canGetStats() {
			return this.$moduleVars.can_get_stats
		},
	},

	methods: {
		getStats() {
			if (!this.canGetStats) {
				return
			}

			this.loading = true

			restGetStats({
				path: 'stats/dashboard',
				params: {
					from: this.dateStart,
					to: this.dateEnd,
					network: this.isNetwork() ? 1 : 0,
				},
			}).then((response) => {
				if (response.success && response.data && response.data.stats) {
					if (Object.keys(response.data.stats).length > 0) {
						this.stats = response.data.stats
					} else {
						this.stats = {} // Empty data.
					}
				} else {
					this.stats = {} // Error.
				}

				this.loading = false
			})
		},

		periodChange(data) {
			// Set from and to dates.
			this.dateStart = data.startDate
			this.dateEnd = data.endDate

			// Make the API request.
			this.getStats()
		},

		smallClass() {
			const body = jQuery('#beehive-widget-body')
			const postbox = body.closest('.postbox')

			if (!body.length) {
				return
			}

			this.small = postbox.outerWidth(true) < 500
		},
	},
}
</script>

<style lang="scss">
@import 'styles/main';
</style>
