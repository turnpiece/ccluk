<template>
	<div
		role="tabpanel"
		tabindex="0"
		id="beehive-widget-content--general_stats"
		class="sui-tab-content active"
		aria-labelledby="beehive-widget-tab--general_stats"
	>
		<sui-notice v-if="canGetStats && isEmpty" type="info">
			<p>{{ $i18n.notice.empty_data }}</p>
		</sui-notice>

		<sui-notice v-else-if="!canGetStats && !isLoggedIn" type="error">
			<p
				v-html="
					sprintf($i18n.notice.auth_required, $vars.urls.accounts)
				"
			></p>
		</sui-notice>

		<div class="beehive-buttons-wrapper">
			<fragment v-for="(item, key) in summarySections" :key="key">
				<div class="beehive-button-holder">
					<button
						class="beehive-button"
						@click="openTab(item.tab, key)"
						v-if="!isLoggedIn || isEmpty || '' === getValue(key)"
					>
						<span class="beehive-button-name">
							{{ item.title }}
						</span>
						<span class="beehive-button-value">-</span>
						<i
							class="beehive-button-icon sui-icon-chevron-right sui-lg"
							aria-hidden="true"
						></i>
					</button>

					<button
						class="beehive-button"
						@click="openTab(item.tab, key)"
						v-else
					>
						<span class="beehive-button-name">
							{{ item.title }}
						</span>
						<span class="beehive-button-stats">
							<span
								class="beehive-button-value beehive-blue beehive-lg"
							>
								{{ getValue(key) }}
							</span>
							<span
								class="beehive-button-trend"
								:class="getTrendClass(key)"
								v-html="getTrendHtml(key)"
							></span>
						</span>
						<i
							class="beehive-button-icon sui-icon-chevron-right sui-lg"
							aria-hidden="true"
						></i>
					</button>
				</div>
			</fragment>
			<fragment v-for="(item, key) in topSections" :key="key">
				<div class="beehive-button-holder">
					<button
						class="beehive-button"
						@click="openTab(item.tab)"
						v-if="!isLoggedIn || isEmpty || '' === getValue(key)"
					>
						<span class="beehive-button-name">
							{{ item.title }}
						</span>
						<span class="beehive-button-value">-</span>
						<i
							class="beehive-button-icon sui-icon-chevron-right sui-lg"
							aria-hidden="true"
						></i>
					</button>
					<button
						class="beehive-button"
						@click="openTab(item.tab)"
						v-else
					>
						<span class="beehive-button-name">
							{{ item.title }}
						</span>
						<span class="beehive-button-value beehive-blue">
							{{ getValue(key) }}
						</span>
						<i
							class="beehive-button-icon sui-icon-chevron-right sui-lg"
							aria-hidden="true"
						></i>
					</button>
				</div>
			</fragment>
		</div>
	</div>
</template>

<script>
import SuiNotice from '@/components/sui/sui-notice'

export default {
	name: 'GeneralStats',

	props: ['stats'],

	components: { SuiNotice },

	data() {
		return {
			summarySections: {
				users: {
					title: this.$i18n.label.users,
					tab: 'audience',
				},
				pageviews: {
					title: this.$i18n.label.pageviews,
					tab: 'audience',
				},
			},

			topSections: {
				page: {
					title: this.$i18n.label.top_page,
					tab: 'top_pages',
				},
				country: {
					title: this.$i18n.label.top_country,
					tab: 'traffic',
				},
				medium: {
					title: this.$i18n.label.top_referral,
					tab: 'traffic',
				},
				search_engine: {
					title: this.$i18n.label.top_search_engine,
					tab: 'traffic',
				},
			},
		}
	},

	computed: {
		isEmpty() {
			return Object.keys(this.stats).length <= 0
		},

		isLoggedIn() {
			return this.$store.state.helpers.google.logged_in
		},

		canGetStats() {
			return this.$moduleVars.can_get_stats
		},

		getTrendClass() {
			return function (section) {
				let trend = this.getTrendValue(section)

				return {
					'beehive-red': trend < 0,
					'beehive-green': trend >= 0,
				}
			}
		},
	},

	methods: {
		getValue(key) {
			let value = ''

			if (
				this.stats.summary &&
				this.stats.summary[key] &&
				this.stats.summary[key].value
			) {
				value = this.stats.summary[key].value
			}

			return value
		},

		getTrendValue(key) {
			let value = 0

			if (
				this.stats.summary &&
				this.stats.summary[key] &&
				this.stats.summary[key].trend
			) {
				value = this.stats.summary[key].trend
			}

			return value
		},

		getTrendHtml(key) {
			let value = this.getTrendValue(key)
			let actual = Math.abs(value)

			if (value < 0) {
				return (
					actual +
					'% <i class="sui-icon-arrow-down sui-sm" aria-hidden="true"></i>'
				)
			} else if (value > 0) {
				return (
					'<i class="sui-icon-arrow-up sui-sm" aria-hidden="true"></i>' +
					actual +
					'%'
				)
			} else {
				return '0%'
			}
		},

		openTab(tab, item) {
			// Emit tab change.
			this.$emit('tabChange', item)

			// Get tab unique ID to open.
			tab = jQuery('#beehive-widget-tab--' + tab)

			// Simulate click on tab to open it.
			tab.click()
		},
	},
}
</script>
