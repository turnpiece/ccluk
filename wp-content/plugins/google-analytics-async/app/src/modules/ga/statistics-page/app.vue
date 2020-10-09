<template>
	<div id="beehive-page-statics" class="sui-wrap">
		<sui-header :title="$i18n.title.google_analytics" :showDocLink="false">
			<template v-slot:right>
				<!-- Button to clear the cached data -->
				<refresh-button
					:processing.sync="loading"
					:notice="false"
					@refreshed="getStats"
				/>

				<div id="beehive-statics-period">
					<sui-calendar-range
						id="beehive-stats-datepicker"
						:periods="$vars.dates.periods"
						:start-date="$vars.dates.start_date"
						:end-date="$vars.dates.end_date"
						:selected-label="$vars.dates.selected_label"
						@periodChange="periodChange"
					/>
					<sui-checkbox
						id="beehive-stats-compare-period"
						type="small"
						:label="$i18n.label.compare_periods"
						v-model="compare"
					/>
				</div>
			</template>
		</sui-header>
		<visitors
			v-if="canView('visitors')"
			:stats="stats"
			:compare="compare"
			:periods="periods"
			:loading="loading"
		/>
		<div class="sui-row">
			<div v-if="canView('mediums')" class="sui-col-lg-4">
				<mediums :stats="stats" :loading="loading" />
			</div>
			<div v-if="canView('social_networks')" class="sui-col-lg-4">
				<social-networks :stats="stats" :loading="loading" />
			</div>
			<div v-if="canView('search_engines')" class="sui-col-lg-4">
				<search-engines :stats="stats" :loading="loading" />
			</div>
		</div>
		<div class="sui-row">
			<div v-if="canView('countries')" class="sui-col-lg-6">
				<countries :stats="stats" :loading="loading" />
			</div>
			<div v-if="canView('pages')" class="sui-col-lg-6">
				<pages :stats="stats" :loading="loading" />
			</div>
		</div>
	</div>
</template>

<script>
import Pages from './widgets/pages.vue'
import Mediums from './widgets/mediums'
import { restGet } from '@/helpers/api'
import Visitors from './widgets/visitors'
import Countries from './widgets/countries'
import { canViewStats } from '@/helpers/utils'
import SuiHeader from '@/components/sui/sui-header'
import SearchEngines from './widgets/search-engines'
import SocialNetworks from './widgets/social-networks'
import SuiCheckbox from '@/components/sui/sui-checkbox'
import RefreshButton from '@/components/elements/refresh-button'
import SuiCalendarRange from '@/components/sui/sui-calendar-range'

export default {
	name: 'App',

	components: {
		Pages,
		Mediums,
		Visitors,
		Countries,
		SuiHeader,
		SuiCheckbox,
		RefreshButton,
		SearchEngines,
		SocialNetworks,
		SuiCalendarRange,
	},

	data() {
		return {
			stats: {},
			dateStart: this.$vars.dates.start_date,
			dateEnd: this.$vars.dates.end_date,
			compare: false,
			loading: false,
			startDate: this.$vars.dates.start_date,
			endDate: this.$vars.dates.end_date,
			selectedDate: this.$vars.dates.selected_label,
			periods: {
				current: {
					from: '',
					to: '',
				},
				previous: {
					from: '',
					to: '',
				},
			},
		}
	},

	mounted() {
		// Update the stats.
		this.getStats()
	},

	computed: {
		/**
		 * Check if we can get the stats data.
		 *
		 * @since 3.2.4
		 *
		 * @return {boolean}
		 */
		canGetStats() {
			return this.$moduleVars.can_get_stats > 0
		},

		/**
		 * Check if we can show the comparison checkbox.
		 *
		 * @since 3.2.4
		 *
		 * @return {boolean}
		 */
		showComparison() {
			let allowed = Object.keys(this.$vars.dates.periods)

			return allowed.includes(this.selectedDate)
		},

		/**
		 * Check if we can compare the data.
		 *
		 * @since 3.2.4
		 *
		 * @return {boolean|boolean}
		 */
		canCompare() {
			return this.showComparison && this.compare
		},
	},

	methods: {
		/**
		 * Get the stats using the API.
		 *
		 * Setup the period comparison also.
		 *
		 * @since 3.2.4
		 *
		 * @returns {void}
		 */
		async getStats() {
			if (!this.canGetStats) {
				return
			}

			this.loading = true

			await restGet({
				path: 'stats/statistics',
				params: {
					from: this.startDate,
					to: this.endDate,
					network: this.isNetwork() ? 1 : 0,
				},
			}).then((response) => {
				if (response.success && response.data && response.data.stats) {
					if (Object.keys(response.data.stats).length > 0) {
						this.stats = response.data.stats
					} else {
						this.stats = {} // Empty data.
					}

					if (response.data.periods) {
						const periods = response.data.periods
						this.periods = {
							current: {
								from: periods.current.from,
								to: periods.current.to,
							},
							previous: {
								from: periods.previous.from,
								to: periods.previous.to,
							},
						}
					} else {
						this.periods = {
							current: {
								from: this.startDate,
								to: this.endDate,
							},
							previous: {
								from: this.startDate,
								to: this.endDate,
							},
						}
					}
				} else {
					this.stats = {} // Error.
				}

				this.loading = false
			})
		},

		/**
		 * Process the period change action.
		 *
		 * On period change, update the dates and then
		 * reload the stats.
		 *
		 * @since 3.2.4
		 *
		 * @returns {void}
		 */
		async periodChange(data) {
			// Set from and to dates.
			this.startDate = data.startDate
			this.endDate = data.endDate
			this.selectedDate = data.seelcted

			// Make the API request.
			await this.getStats()
		},

		/**
		 * Check if we can view the stats.
		 *
		 * @param {string} type Stats type.
		 *
		 * @since 3.2.4
		 *
		 * @return {string|boolean}
		 */
		canView(type) {
			return canViewStats(type, 'statistics')
		},
	},
}
</script>

<style lang="scss">
@import 'styles/main';
</style>
