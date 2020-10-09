<template>
	<div
		role="tabpanel"
		tabindex="0"
		id="beehive-widget-content--traffic"
		class="sui-tab-content"
		aria-labelledby="beehive-widget-tab--traffic"
		hidden
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

		<fragment v-else>
			<table class="beehive-table">
				<thead>
					<tr>
						<th colspan="3" class="beehive-column-country">
							{{ $i18n.label.top_countries }}
						</th>
						<th class="beehive-column-views">
							{{ $i18n.label.views }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(item, key) in stats.countries" :key="key">
						<td colspan="3" class="beehive-column-country">
							<div class="beehive-country-content">
								<span
									:class="flagClass(item[1])"
									aria-hidden="true"
								></span>
								<p class="beehive-country-name">
									{{ item[0] }}
								</p>
								<div
									class="beehive-country-percent"
									aria-hidden="true"
								>
									<span
										:style="percentageStyle(item[2])"
									></span>
								</div>
							</div>
						</td>
						<td class="beehive-column-views">{{ item[2] }}</td>
					</tr>
				</tbody>
			</table>
			<div class="beehive-row">
				<fragment v-for="(item, key) in summary" :key="key">
					<div class="beehive-col">
						<div class="beehive-box">
							<h3
								class="beehive-box-title"
								v-html="getBoxTitle(item)"
							></h3>
							<div class="beehive-box-content">
								<p class="beehive-box-stat-value">
									{{ getStatValue(key, '%') }}
								</p>
								<p class="beehive-box-stat-name">
									{{ getStatName(key) }}
								</p>
								<sui-score
									:value="parseInt(getStatValue(key, ''))"
								/>
							</div>
						</div>
					</div>
					<span
						class="beehive-separator"
						aria-hidden="true"
						v-if="'social_networks' !== key"
					/>
				</fragment>
			</div>
		</fragment>
	</div>
</template>

<script>
import SuiScore from '@/components/sui/sui-score'
import SuiNotice from '@/components/sui/sui-notice'

export default {
	name: 'Traffic',

	props: ['stats'],

	components: {
		SuiScore,
		SuiNotice,
	},

	data() {
		return {
			summary: {
				search_engines: {
					title: this.$i18n.label.top_search_engine,
					icon: 'magnifying-glass-search',
				},
				mediums: {
					title: this.$i18n.label.top_medium,
					icon: 'update',
				},
				social_networks: {
					title: this.$i18n.label.top_social_network,
					icon: 'community-people',
				},
			},
		}
	},

	computed: {
		isEmpty() {
			return Object.keys(this.stats).length <= 0
		},

		canGetStats() {
			return this.$moduleVars.can_get_stats
		},

		isLoggedIn() {
			return this.$store.state.helpers.google.logged_in
		},

		flagClass() {
			return function (country) {
				let countryClass = 'beehive-flag-' + country

				return {
					'beehive-flag': true,
					'beehive-country-flag': true,
					[countryClass]: true,
				}
			}
		},

		percentageStyle() {
			return function (value) {
				return {
					width: this.visitsPercent(value) + '%',
				}
			}
		},
	},

	methods: {
		visitsPercent(value) {
			const self = this

			let topValue = 0

			Object.keys(this.stats.countries).forEach(function (key) {
				topValue += parseInt(self.stats.countries[key][2])
			})

			return (parseInt(value) * 100) / topValue
		},

		showBox(key) {
			let show = false

			if ((null !== typeof key || '' !== key) && this.stats[key]) {
				show = true
			}

			return show
		},

		getBoxTitle(item) {
			let html = '',
				title = '',
				icon = ''

			if (!item.title || '' === item.title) {
				return
			} else {
				title = item.title
			}

			if (item.icon && '' !== item.icon) {
				icon =
					'<i class="sui-icon-' +
					item.icon +
					' sui-md" aria-hidden="true"></i>'
			}

			html = icon + title

			return html
		},

		getStatName(key) {
			let value = this.$i18n.label.none

			if ((null !== typeof key || '' !== key) && this.stats[key]) {
				value = this.stats[key][0][0]
			}

			return value
		},

		getStatValue(key, symbol) {
			const self = this

			let percent = 0
			let value = 0

			const sign = percent >= 0 ? 1 : -1
			const decimal = 0

			if ((null !== typeof key || '' !== key) && this.stats[key]) {
				Object.keys(self.stats[key]).forEach(function (k) {
					value += parseInt(self.stats[key][k][1])
				})

				percent = (parseInt(self.stats[key][0][1]) * 100) / value

				if (percent >= 0) {
					percent = (
						Math.round(
							percent * Math.pow(10, decimal) + sign * 0.0001
						) / Math.pow(10, decimal)
					).toFixed(decimal)
				}
			}

			if (null === typeof symbol || '' === symbol) {
				symbol = ''
			}

			percent = percent + symbol

			return percent
		},
	},
}
</script>
