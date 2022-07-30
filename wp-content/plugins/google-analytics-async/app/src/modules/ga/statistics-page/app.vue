<template>
	<!-- Open sui-wrap -->
	<div class="sui-wrap" id="beehive-wrap">
		<black-friday-notice/>

		<sui-header
			:title="$i18n.title.google_analytics"
			:show-doc-link="false"
		>
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
					:notice="false"
					:loading.sync="loading"
					@refreshed="$root.$emit('statsRefreshCompleted')"
				/>
				<div id="beehive-statics-period">
					<sui-calendar-range
						id="beehive-stats-datepicker"
						:periods="$vars.dates.periods"
						:start-date="$vars.dates.start_date"
						:end-date="$vars.dates.end_date"
						:selected-label="$vars.dates.selected_label"
						@periodChange="changePeriods"
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

		<statistics :compare="compare" :loading.sync="loading"/>

		<sui-footer/>
	</div>
	<!-- Close sui-wrap -->
</template>

<script>
import SuiHeader from '@/components/sui/sui-header'
import SuiFooter from '@/components/sui/sui-footer'
import SuiSelect from '@/components/sui/sui-select'
import Statistics from './../admin/tabs/statistics'
import SuiCheckbox from '@/components/sui/sui-checkbox'
import RefreshButton from '@/components/elements/refresh-button'
import SuiCalendarRange from '@/components/sui/sui-calendar-range'
import BlackFridayNotice from '@/components/elements/black-friday-notice'

export default {
	name: 'App',

	components: {
		SuiHeader,
		SuiFooter,
		SuiSelect,
		Statistics,
		SuiCheckbox,
		RefreshButton,
		SuiCalendarRange,
		BlackFridayNotice,
	},

	data() {
		return {
			compare: false,
			loading: false,
		}
	},

	computed: {
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
				this.$root.$emit('statsTypeChanged', true)
			},
		},
	},

	methods: {
		/**
		 * Handle period change for updating stats.
		 *
		 * @since 3.3.7
		 *
		 * @param {object} data Period data.
		 */
		changePeriods(data) {
			this.$root.$emit('statsPeriodChanged', data)
		},
	},
}
</script>

<style lang="scss">
@import 'styles/main';
</style>
