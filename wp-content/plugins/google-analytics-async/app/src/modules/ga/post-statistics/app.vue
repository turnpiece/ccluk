<template>
	<div class="beehive-post-stats-wrap">
		<div class="sui-row">
			<section-column
				v-for="section in sections1"
				:key="section.key"
				:id="section.key"
				:label="section.label"
				:desc="section.desc"
				:screen-reader="section.screenReader"
				:stats="stats[section.key]"
				:trend-type="getTrendType(stats[section.key])"
				:value="getValue(stats[section.key], section.key)"
			/>
		</div>
		<div class="sui-row">
			<section-column
				v-for="section in sections2"
				:key="section.key"
				:id="section.key"
				:label="section.label"
				:desc="section.desc"
				:screen-reader="section.screenReader"
				:trend-type="getTrendType(stats[section.key], section.key)"
				:trend-value="getTrendValue(stats[section.key], section.key)"
				:value="getValue(stats[section.key], section.key)"
			/>
		</div>
	</div>
</template>

<script>
import { restGetStats } from '@/helpers/api'
import SectionColumn from './components/section-column'

export default {
	name: 'App',

	components: { SectionColumn },

	data() {
		return {
			stats: {
				users: {},
				pageviews: {},
				sessions: {},
				page_sessions: {},
				average_sessions: {},
				bounce_rates: {},
			},
			sections1: [
				{
					key: 'users',
					label: this.$i18n.label.users,
					desc: this.$i18n.desc.users,
					screenReader: this.$i18n.desc.screen_users,
				},
				{
					key: 'pageviews',
					label: this.$i18n.label.pageviews,
					desc: this.$i18n.desc.pageviews,
					screenReader: this.$i18n.desc.screen_pageviews,
				},
				{
					key: 'sessions',
					label: this.$i18n.label.sessions,
					desc: this.$i18n.desc.sessions,
					screenReader: this.$i18n.desc.screen_sessions,
				},
			],
			sections2: [
				{
					key: 'page_sessions',
					label: this.$i18n.label.page_sessions,
					desc: this.$i18n.desc.page_sessions,
					screenReader: this.$i18n.desc.screen_page_sessions,
				},
				{
					key: 'average_sessions',
					label: this.$i18n.label.average_sessions,
					desc: this.$i18n.desc.average_sessions,
					screenReader: this.$i18n.desc.screen_average_sessions,
				},
				{
					key: 'bounce_rates',
					label: this.$i18n.label.bounce_rates,
					desc: this.$i18n.desc.bounce_rates,
					screenReader: this.$i18n.desc.screen_bounce_rates,
				},
			],
		}
	},

	mounted() {
		// Get the stats.
		if (this.$moduleVars.post > 0 && this.canGetStats) {
			this.getStats()
		}
	},

	computed: {
		/**
		 * Check if we can get the statistics data.
		 *
		 * @since 3.2.4
		 *
		 * @return {boolean}
		 */
		canGetStats() {
			return this.$moduleVars.can_get_stats
		},
	},

	methods: {
		/**
		 * Get the stats from the API.
		 *
		 * @since 3.2.4
		 *
		 * @returns {void}
		 */
		getStats() {
			restGetStats({
				path: 'stats/post',
				params: {
					id: this.$moduleVars.post,
				},
			}).then((response) => {
				if (response.success && response.data && response.data.stats) {
					this.stats = response.data.stats
				} else {
					this.stats = false
				}
			})
		},

		/**
		 * Get the trend type for a section.
		 *
		 * @param {object} stats Stats data.
		 * @param {string} type Section type.
		 *
		 * @since 3.2.4
		 *
		 * @return {string}
		 */
		getTrendType(stats, type) {
			let trendType = 'none'

			if (stats.trend) {
				const trend = stats.trend

				if (trend === 0) {
					trendType = 'equal'
				} else if (trend < 0) {
					trendType = 'down'
				} else if (trend > 0) {
					trendType = 'up'
				}

				// Bounce rate is opposite.
				if ('bounce_rates' === type) {
					if (trend < 0) {
						trendType = 'up'
					} else if (trend > 0) {
						trendType = 'down'
					}
				}
			}

			return trendType
		},

		/**
		 * Get the value of the section.
		 *
		 * @param {object} stats Stats data.
		 * @param {string} type Section type.
		 *
		 * @since 3.2.4
		 *
		 * @return {number}
		 */
		getValue(stats, type) {
			let value = 0

			if (stats.value) {
				value = stats.value
			}

			// Bounce rate require %.
			if ('bounce_rates' === type) {
				value = value + '%'
			}

			return value
		},

		/**
		 * Get the section's trend value.
		 *
		 * @param {object} stats Stats data.
		 * @param {string} type Section type.
		 *
		 * @since 3.2.4
		 *
		 * @return {string|number}
		 */
		getTrendValue(stats, type) {
			if (stats.trend) {
				return Math.abs(this.stats[type].trend)
			}

			return '-'
		},
	},
}
</script>

<style lang="scss">
@import 'styles/main';
</style>
