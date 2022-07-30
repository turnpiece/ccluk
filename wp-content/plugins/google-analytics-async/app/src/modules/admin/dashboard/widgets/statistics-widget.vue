<template>
	<sui-box
		id="beehive-widget-stats"
		class="beehive-widget"
		titleIcon="graph-line"
		aria-live="polite"
		:loading="loading"
		:title="$i18n.title.statistics_box"
	>
		<template v-slot:body>
			<p class="beehive-loading-text" v-if="loading">
				<span
					class="sui-icon-loader sui-loading"
					aria-hidden="true"
				></span>
				{{ $i18n.label.fetching_data }}
			</p>

			<p>{{ $i18n.desc.statistics_box }}</p>

			<fragment v-if="!isConnected && !canGetStats">
				<sui-notice v-if="isNetwork()" type="info">
					<p>{{ $i18n.notice.auth_required_network }}</p>
				</sui-notice>
				<sui-notice v-else type="info">
					<p>{{ $i18n.notice.auth_required }}</p>
				</sui-notice>

				<a
					role="button"
					class="sui-button sui-button-blue"
					:href="$vars.urls.accounts"
				>
					<i class="sui-icon-wrench-tool" aria-hidden="true"></i>
					{{ $i18n.label.configure_account }}
				</a>

				<a
					role="button"
					class="sui-button sui-button-ghost"
					:href="$vars.urls.ga_account"
				>
					{{ $i18n.label.add_tracking_id }}
				</a>
			</fragment>

			<sui-notice v-else-if="isEmpty" type="info">
				<p>{{ $i18n.notice.no_data }}</p>
			</sui-notice>
		</template>

		<template v-slot:outside>
			<stats-list :stats="stats" v-if="canGetStats && !isEmpty" />
		</template>
	</sui-box>
</template>

<script>
import SuiBox from '@/components/sui/sui-box'
import StatsList from './statistics/stats-list'
import SuiNotice from '@/components/sui/sui-notice'

export default {
	name: 'StatisticsWidget',

	props: ['stats', 'loading'],

	components: {
		SuiBox,
		SuiNotice,
		StatsList,
	},

	computed: {
		/**
		 * Check if the current user is logged in with Google.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		isConnected() {
			return this.$store.state.helpers.google.logged_in
		},

		/**
		 * Check if current site can get stats.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		canGetStats() {
			return this.$moduleVars.can_get_stats || this.isConnected
		},

		/**
		 * Check if stats are empty.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		isEmpty() {
			return Object.keys(this.stats).length <= 0
		},
	},

	methods: {},
}
</script>
