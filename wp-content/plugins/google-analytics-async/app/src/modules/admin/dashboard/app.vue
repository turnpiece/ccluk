<template>
	<div class="sui-wrap" id="beehive-wrap">
		<black-friday-notice/>

		<sui-notice type="warning" v-if="showGA4Notice">
			<p v-html="$i18n.notice.ga4_not_setup"></p>
		</sui-notice>

		<sui-header :title="$i18n.title.dashboard">
			<template v-slot:left>
				<sui-select
					v-model="statisticsType"
					id="beehive-statics-type-select"
					width="175px"
					:options="$moduleVars.stats_types"
					:is-small="true"
				/>
			</template>
			<template v-slot:right>
				<!-- Button to clear the cached data -->
				<refresh-button
					:loading.sync="loading"
					:notice="false"
					@refreshed="getSummary"
				/>
				<sui-calendar-range
					id="beehive-stats-datepicker"
					:periods="$vars.dates.periods"
					:start-date="$vars.dates.start_date"
					:end-date="$vars.dates.end_date"
					:selected-label="$vars.dates.selected_label"
					@periodChange="changePeriod"
				/>
			</template>
		</sui-header>

		<summary-widget :stats="stats.summary" :loading="loading"/>

		<tutorial-slider/>

		<div class="sui-row">
			<div class="sui-col-lg-6">
				<statistics-widget :stats="stats.list" :loading="loading"/>
			</div>
			<div class="sui-col-lg-6">
				<div class="sui-row">
					<div class="sui-col-md-12">
						<gtm-widget/>
					</div>
				</div>
				<div class="sui-row">
					<div class="sui-col">
						<ga-widget/>
					</div>
				</div>
			</div>
		</div>

		<sui-footer/>

		<!-- Onboarding start -->
		<onboarding v-if="showOnboarding"/>
		<!-- Onboarding end -->
		<!-- Welcome modal -->
		<welcome-modal v-else-if="showWelcome"/>
		<!-- welcome modal end -->
	</div>
</template>

<script>
import {restGetStats} from '@/helpers/api'
import GaWidget from './widgets/ga-widget'
import GtmWidget from './widgets/gtm-widget'
import Onboarding from './../onboarding/onboarding'
import SuiHeader from '@/components/sui/sui-header'
import SuiFooter from '@/components/sui/sui-footer'
import SuiNotice from '@/components/sui/sui-notice'
import SuiSelect from '@/components/sui/sui-select'
import SummaryWidget from './widgets/summary-widget'
import StatisticsWidget from './widgets/statistics-widget'
import RefreshButton from '@/components/elements/refresh-button'
import SuiCalendarRange from '@/components/sui/sui-calendar-range'
import WelcomeModal from '@/components/elements/modals/welcome-modal'
import BlackFridayNotice from '@/components/elements/black-friday-notice'
import TutorialSlider from '@/components/elements/tutorial-slider.vue'

export default {
	name: 'App',

	components: {
		GaWidget,
		GtmWidget,
		SuiHeader,
		SuiFooter,
		SuiSelect,
		SuiNotice,
		Onboarding,
		WelcomeModal,
		SummaryWidget,
		RefreshButton,
		StatisticsWidget,
		SuiCalendarRange,
		TutorialSlider,
		BlackFridayNotice,
	},

	data() {
		return {
			loading: false,
			periods: [],
			dateStart: this.$vars.dates.start_date,
			dateEnd: this.$vars.dates.end_date,
			stats: {
				summary: {
					pageviews: {},
					page: {},
					searchEngine: {},
					medium: {},
					newUsers: {},
				},
				list: {},
			},
		}
	},

	mounted() {
		this.getSummary()
	},

	created() {
		// On Google login.
		this.$root.$on('googleLoginUpdate', (success) => {
			this.setOption('google_auth_redirect_success', 'misc', 0)

			this.$store.dispatch('helpers/updateGoogleLogin', {
				reInit: true,
				status: success,
			})
		})

		// On onboarding complete.
		this.$root.$on('onboardingComplete', (success) => {
			// Show account setup notice if all ok.
			if ('' !== this.getOption('account_id', 'google', '') && success) {
				this.$root.$emit('showTopNotice', {
					dismiss: true,
					message: this.sprintf(
						this.$i18n.notice.account_setup,
						this.$vars.urls.statistics
					),
				})
			}

			// Update the stats.
			this.getSummary()
		})

		// On Google login.
		this.$root.$on('googleConnectProcessed', (data) => {
			// Update profiles.
			if (data.success) {
				this.$store.dispatch('helpers/updateGoogleStreams', {})
				this.$store.dispatch('helpers/updateGoogleProfiles', {
					reInit: true, // Re load settings.
				})
			}
		})
	},

	computed: {
		/**
		 * Check if we can get the stats.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		canGetStats() {
			return this.$moduleVars.can_get_stats
		},

		/**
		 * Check if we current user is logged in with Google.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		isConnected() {
			return this.$store.state.helpers.google.logged_in
		},

		/**
		 * Check if we can show the onboarding.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		showOnboarding() {
			return !this.getOption('onboarding_done', 'misc')
		},

		/**
		 * Check if we can show welcome modal.
		 *
		 * @since 3.2.5
		 *
		 * @returns {boolean}
		 */
		showWelcome() {
			// Do not conflict with onboarding.
			if (this.showOnboarding) {
				return false
			}

			if (
				this.isMultisite() &&
				this.isNetworkWide() &&
				this.isNetwork()
			) {
				return this.getOption('show_welcome', 'misc', 0, true) > 0
			} else if (!this.isMultisite() || !this.isNetworkWide()) {
				return this.getOption('show_welcome', 'misc') > 0
			} else {
				return false
			}
		},

		/**
		 * Computed object to get the selected type.
		 *
		 * @since 3.4.0
		 *
		 * @returns {string}
		 */
		statisticsType: {
			get() {
				return this.getOption('statistics_type', 'google', 'ua')
			},
			set(value) {
				this.setOption('statistics_type', 'google', value)
				// Save right away.
				this.saveOptions()
				// Emit type change event.
				this.$root.$emit('statsTypeChanged')

				// Get new stats.
				this.getSummary()
			},
		},

		/**
		 * Check if we should show GA4 notice.
		 *
		 * @since 3.4.0
		 *
		 * @returns {boolean}
		 */
		showGA4Notice() {
			return !this.getOption('stream', 'google')
		},
	},

	methods: {
		/**
		 * Get the summary data from the API.
		 *
		 * @since 3.2.4
		 */
		getSummary() {
			if (!this.canGetStats && !this.isConnected) {
				return
			}

			this.loading = true

			restGetStats({
				path: 'stats/summary',
				params: {
					from: this.dateStart,
					to: this.dateEnd,
					network: this.isNetwork() ? 1 : 0,
				},
			}).then((response) => {
				if (response.success && response.data && response.data.stats) {
					if (response.data.stats.summary) {
						this.stats.list = response.data.stats.summary
						this.setupSummary(response.data.stats)
					}
				}

				this.loading = false
			})
		},

		/**
		 * Setup the summary stats based on the response.
		 *
		 * @since 3.2.4
		 */
		setupSummary(stats) {
			this.stats.summary = {
				pageviews: stats.summary.pageviews,
				page: stats.page,
				searchEngine: stats.search_engine,
				medium: stats.medium,
				newUsers: stats.summary.new_users,
			}
		},

		/**
		 * Change the period and update the stats.
		 *
		 * @since 3.2.4
		 */
		changePeriod(data) {
			// Set from and to dates.
			this.dateStart = data.startDate
			this.dateEnd = data.endDate

			// Make the API request.
			this.getSummary()
		},
	},
}
</script>

<style lang="scss">
@import 'styles/main';
</style>
