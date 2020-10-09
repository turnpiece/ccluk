<template>
	<table class="beehive-table">
		<tbody>
			<fragment v-for="(item, name) in stats" :key="(item, name)">
				<tr v-if="canShow(name)">
					<td class="beehive-stats-title">{{ getTitle(name) }}</td>

					<td
						class="beehive-stats-trend beehive-green"
						v-if="item.trend < 0 && 'bounce_rates' === name"
					>
						<i
							class="sui-icon-arrow-down sui-sm"
							aria-hidden="true"
						></i>
						{{ Math.abs(item.trend) }}%
					</td>
					<td
						class="beehive-stats-trend beehive-red"
						v-else-if="item.trend < 0"
					>
						<i
							class="sui-icon-arrow-down sui-sm"
							aria-hidden="true"
						></i>
						{{ Math.abs(item.trend) }}%
					</td>
					<td
						class="beehive-stats-trend beehive-red"
						v-else-if="item.trend > 0 && 'bounce_rates' === name"
					>
						<i
							class="sui-icon-arrow-up sui-sm"
							aria-hidden="true"
						></i>
						{{ item.trend }}%
					</td>
					<td
						class="beehive-stats-trend beehive-green"
						v-else-if="item.trend > 0"
					>
						<i
							class="sui-icon-arrow-up sui-sm"
							aria-hidden="true"
						></i>
						{{ item.trend }}%
					</td>
					<td class="beehive-stats-trend" v-else>0%</td>

					<td
						class="beehive-stats-value"
						v-if="name === 'bounce_rates'"
					>
						{{ item.value }}%
					</td>
					<td class="beehive-stats-value" v-else>{{ item.value }}</td>
				</tr>
			</fragment>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<a
						:href="$vars.urls.statistics"
						class="sui-button sui-button-ghost"
					>
						<i class="sui-icon-eye" aria-hidden="true"></i>
						{{ $i18n.label.view_full_report }}
					</a>
				</td>
			</tr>
		</tfoot>
	</table>
</template>

<script>
import { Fragment } from 'vue-fragment'

export default {
	name: 'StatsList',

	props: ['stats'],

	components: {
		Fragment,
	},

	data() {
		return {
			titles: {
				sessions: this.$i18n.label.sessions,
				users: this.$i18n.label.users,
				pageviews: this.$i18n.label.pageviews,
				page_sessions: this.$i18n.label.page_sessions,
				average_sessions: this.$i18n.label.average_sessions,
				bounce_rates: this.$i18n.label.bounce_rates,
			},
		}
	},

	computed: {
		/**
		 * Check if the stats are empty.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		isEmpty() {
			return Object.keys(this.stats).length <= 0
		},
	},

	methods: {
		/**
		 * Check if we can show the stat item
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		canShow(name) {
			return this.titles.hasOwnProperty(name)
		},

		/**
		 * Get the title of the item.
		 *
		 * @since 3.2.4
		 *
		 * @returns {string}
		 */
		getTitle(name) {
			return this.titles[name]
		},
	},
}
</script>
