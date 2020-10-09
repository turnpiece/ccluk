<template>
	<sui-box
		:title="$i18n.label.visitors"
		title-icon="profile-male"
		aria-live="polite"
		:loading="loading"
		body-class="beehive-spacing-bottom--0"
	>
		<template v-slot:body>
			<sui-notice v-if="canGetStats && isEmpty" type="info">
				<p>{{ $i18n.notice.google_no_data }}</p>
			</sui-notice>

			<sui-notice v-else-if="!canGetStats && !isLoggedIn" type="error">
				<p
					v-html="
						sprintf(
							$i18n.notice.google_not_linked,
							$vars.urls.accounts
						)
					"
				></p>
			</sui-notice>
		</template>

		<template v-slot:outside>
			<p class="beehive-loading-text" v-if="loading">
				<span
					class="sui-icon-loader sui-loading"
					aria-hidden="true"
				></span>
				{{ $i18n.label.fetching_data }}
			</p>

			<chart-visitors
				:stats="stats"
				:periods="periods"
				:compare="compare"
			/>
		</template>
	</sui-box>
</template>

<script>
import SuiBox from '@/components/sui/sui-box'
import SuiNotice from '@/components/sui/sui-notice'
import ChartVisitors from './../components/chart-visitors'

export default {
	name: 'Visitors',

	props: ['stats', 'compare', 'loading', 'periods'],

	data() {
		return {
			bodyClass: 'test',
		}
	},

	components: {
		SuiBox,
		SuiNotice,
		ChartVisitors,
	},

	computed: {
		isLoggedIn() {
			return this.$store.state.helpers.google.logged_in
		},

		isEmpty() {
			return Object.keys(this.stats).length <= 0
		},

		canGetStats() {
			return this.$moduleVars.can_get_stats > 0
		},
	},

	methods: {},
}
</script>
