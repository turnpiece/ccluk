<template>
	<div id="beehive-widget-body" class="sui-tabs sui-tabs-overflow">
		<div tabindex="-1" class="sui-tabs-navigation" aria-hidden="true">
			<button
				type="button"
				class="sui-button-icon sui-tabs-navigation--left"
			>
				<i class="sui-icon-chevron-left"></i>
			</button>
			<button
				type="button"
				class="sui-button-icon sui-tabs-navigation--right"
			>
				<i class="sui-icon-chevron-right"></i>
			</button>
		</div>

		<div role="tablist" class="sui-tabs-menu">
			<button
				v-if="canView('general')"
				type="button"
				role="tab"
				id="beehive-widget-tab--general_stats"
				class="sui-tab-item active"
				aria-controls="beehive-widget-content--general_stats"
				aria-selected="true"
			>
				{{ $i18n.label.general_stats }}
			</button>

			<button
				v-if="canView('audience')"
				type="button"
				role="tab"
				id="beehive-widget-tab--audience"
				class="sui-tab-item"
				aria-controls="beehive-widget-content--audience"
				aria-selected="false"
				tabindex="-1"
			>
				{{ $i18n.label.audience }}
			</button>

			<button
				v-if="canView('pages')"
				type="button"
				role="tab"
				id="beehive-widget-tab--top_pages"
				class="sui-tab-item"
				aria-controls="beehive-widget-content--top_pages"
				aria-selected="false"
				tabindex="-1"
			>
				{{ $i18n.label.top_pages }}
			</button>

			<button
				v-if="canView('traffic')"
				type="button"
				role="tab"
				id="beehive-widget-tab--traffic"
				class="sui-tab-item"
				aria-controls="beehive-widget-content--traffic"
				aria-selected="false"
				tabindex="-1"
			>
				{{ $i18n.label.traffic }}
			</button>
		</div>

		<div class="sui-tabs-content">
			<general-stats
				v-if="canView('general')"
				:stats="stats"
				@tabChange="tabChange"
			/>

			<audience
				v-if="canView('audience')"
				:stats="stats"
				:selected-item="audienceDefault"
			/>

			<pages v-if="canView('pages')" :stats="stats" />

			<traffic v-if="canView('traffic')" :stats="stats" />
		</div>
	</div>
</template>

<script>
import Pages from './tabs/pages'
import Traffic from './tabs/traffic'
import Audience from './tabs/audience'
import { canViewStats } from '@/helpers/utils'
import GeneralStats from './tabs/general-stats'

export default {
	name: 'WidgetBody',

	props: ['stats'],

	components: {
		GeneralStats,
		Audience,
		Pages,
		Traffic,
	},

	data() {
		return {
			audienceDefault: 'sessions',
		}
	},

	mounted() {
		const body = jQuery('#beehive-widget-body')
		const navigation = body.find('.sui-tabs-navigation')

		// Initialize tabs.
		SUI.tabs()

		// Initialize overflow tabs.
		navigation.each(function () {
			SUI.tabsOverflow(jQuery(this))
		})
	},

	methods: {
		canView(type) {
			return canViewStats(type, 'dashboard')
		},

		tabChange(tab) {
			this.audienceDefault = tab
		},
	},
}
</script>
