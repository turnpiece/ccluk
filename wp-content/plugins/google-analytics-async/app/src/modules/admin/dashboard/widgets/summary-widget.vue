<template>
	<div :class="summaryClass">
		<div
			:style="reBrandedStyle"
			class="sui-summary-image-space"
			aria-hidden="true"
		></div>

		<div class="sui-summary-segment">
			<p class="beehive-loading-text" v-if="loading">
				<span
					class="sui-icon-loader sui-loading"
					aria-hidden="true"
				></span>
				{{ $i18n.label.fetching_data }}
			</p>

			<div class="sui-summary-details">
				<span class="sui-summary-large">
					{{ stats.pageviews.value || '-' }}
				</span>
				<span class="sui-summary-percent">
					<span
						class="beehive-stats-trend beehive-red"
						v-if="pageViewsTrend < 0"
					>
						<i
							class="sui-icon-arrow-down sui-sm"
							aria-hidden="true"
						></i>
						{{ Math.abs(pageViewsTrend) }}%
					</span>
					<span
						class="beehive-stats-trend beehive-green"
						v-else-if="pageViewsTrend > 0"
					>
						<i
							class="sui-icon-arrow-up sui-sm"
							aria-hidden="true"
						></i>
						{{ Math.abs(pageViewsTrend) }}%
					</span>
					<span v-else-if="pageViewsTrend === 0">0%</span>
				</span>
				<span class="sui-summary-sub">{{ $i18n.label.pageviews }}</span>
				<span class="sui-summary-detail">
					{{ stats.newUsers.trend || '' }}
					<span
						class="beehive-stats-trend beehive-red"
						v-if="newUsersTrend < 0"
					>
						<i
							class="sui-icon-arrow-down sui-sm"
							aria-hidden="true"
						></i>
						{{ Math.abs(newUsersTrend) }}%
					</span>
					<span
						class="beehive-stats-trend beehive-green"
						v-else-if="newUsersTrend > 0"
					>
						<i
							class="sui-icon-arrow-up sui-sm"
							aria-hidden="true"
						></i>
						{{ Math.abs(newUsersTrend) }}%
					</span>
					<span v-else-if="newUsersTrend === 0">0%</span>
				</span>
				<span class="sui-summary-sub">{{ $i18n.label.new_users }}</span>
			</div>
		</div>

		<div class="sui-summary-segment">
			<ul class="sui-list">
				<li>
					<span class="sui-list-label">
						{{ $i18n.label.top_page }}
					</span>
					<span
						class="sui-list-detail"
						v-html="stats.page.anchor || $i18n.label.none"
					></span>
				</li>
				<li>
					<span class="sui-list-label">
						{{ $i18n.label.top_search_engine }}
					</span>
					<span class="sui-list-detail">
						{{ stats.searchEngine.name || $i18n.label.none }}
					</span>
				</li>
				<li>
					<span class="sui-list-label">
						{{ $i18n.label.top_medium }}
					</span>
					<span class="sui-list-detail">
						{{ stats.medium.name || $i18n.label.none }}
					</span>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
export default {
	name: 'SummaryWidget',

	props: ['stats', 'loading'],

	computed: {
		/**
		 * Get the pageviews trend value.
		 *
		 * @since 3.2.4
		 *
		 * @returns {string}
		 */
		pageViewsTrend() {
			return this.stats.pageviews.trend || ''
		},

		/**
		 * Get the new users trend value.
		 *
		 * @since 3.2.4
		 *
		 * @returns {string}
		 */
		newUsersTrend() {
			return this.stats.newUsers.trend || ''
		},

		/**
		 * Get the summary box class.
		 *
		 * @since 3.2.4
		 *
		 * @returns {*}
		 */
		summaryClass() {
			return {
				'sui-box': true,
				'sui-summary': true,
				'sui-unbranded': this.$vars.whitelabel.is_unbranded,
				'sui-rebranded': this.$vars.whitelabel.is_rebranded,
				'beehive-loading': this.loading,
			}
		},

		/**
		 * Get the background image if whitelabelled.
		 *
		 * @since 3.2.4
		 *
		 * @returns {*}
		 */
		reBrandedStyle() {
			if (this.$vars.whitelabel.is_rebranded) {
				return {
					'background-image':
						'url(' + this.$vars.whitelabel.custom_image + ')',
				}
			}
		},
	},
}
</script>
