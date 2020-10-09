<template>
	<sui-box
		:title="$i18n.label.top_pages"
		titleIcon="page-multiple"
		aria-live="polite"
		:loading="loading"
	>
		<template v-slot:body>
			<p class="beehive-loading-text" v-if="true === loading">
				<span
					class="sui-icon-loader sui-loading"
					aria-hidden="true"
				></span>
				{{ $i18n.label.fetching_data }}
			</p>

			<p class="sui-description" v-if="!isLoggedIn || isEmpty">
				{{ $i18n.label.no_information }}
			</p>

			<table class="beehive-table-pages" v-else>
				<thead>
					<tr>
						<th colspan="2">
							{{ $i18n.label.top_pages_most_visited }}
						</th>
						<th>{{ $i18n.label.average_sessions }}</th>
						<th>{{ $i18n.label.views }}</th>
						<th>{{ $i18n.label.trend }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(item, key) in getPages" :key="key">
						<td colspan="2" v-html="item[0]"></td>
						<td>{{ item[1] }}</td>
						<td>{{ item[2] }}</td>
						<td class="beehive-red" v-if="item[3] < 0">
							<i
								class="sui-icon-arrow-down sui-sm"
								aria-hidden="true"
							></i>
							{{ item[3] }}%
						</td>
						<td class="beehive-green" v-else-if="item[3] > 0">
							<i
								class="sui-icon-arrow-up sui-sm"
								aria-hidden="true"
							></i>
							{{ item[3] }}%
						</td>
						<td class="beehive-green" v-else>0%</td>
					</tr>
				</tbody>
			</table>
		</template>
	</sui-box>
</template>

<script>
import SuiBox from '@/components/sui/sui-box'

export default {
	name: 'Pages',

	props: ['stats', 'loading'],

	components: {
		SuiBox,
	},

	computed: {
		isLoggedIn() {
			return this.$store.state.helpers.google.logged_in
		},

		isEmpty() {
			return Object.keys(this.stats).length <= 0 || !this.stats.pages
		},

		getPages() {
			let pages = []

			if (this.stats.pages) {
				pages = this.stats.pages.slice(0, 9)
			}

			return pages
		},
	},
}
</script>
