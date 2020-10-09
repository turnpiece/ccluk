<template>
	<div class="sui-tabs">
		<div role="tablist" class="sui-tabs-menu">
			<button
				v-for="(tab, key) in tabs"
				type="button"
				role="tab"
				class="sui-tab-item"
				aria-selected="true"
				:class="{ active: tab.active }"
				:id="`tab-${key}-variables`"
				:aria-controls="`tab-content-${key}-variables`"
				:key="key"
			>
				{{ tab.title }}
			</button>
		</div>
		<div class="sui-tabs-content">
			<div
				v-for="(tab, key) in tabs"
				role="tabpanel"
				tabindex="0"
				class="sui-tab-content"
				:class="{ active: tab.active }"
				:aria-labelledby="`tab-${key}-variables`"
				:id="`tab-content-${key}-variables`"
				:key="key"
			>
				<component :is="tab.content" />
			</div>
		</div>
	</div>
</template>

<script>
import Integrations from './variables/integrations'
import CustomVariables from './variables/custom-variables'
import DefaultVariables from './variables/default-variables'
import VisitorsVariables from './variables/visitors-variables'

export default {
	name: 'Variables',

	components: {
		Integrations,
		CustomVariables,
		DefaultVariables,
		VisitorsVariables,
	},

	data() {
		return {
			tabs: {
				default: {
					active: true,
					title: this.$i18n.label.default,
					content: 'DefaultVariables',
				},
				visitors: {
					title: this.$i18n.label.visitors,
					content: 'VisitorsVariables',
				},
				integrations: {
					title: this.$i18n.label.integrations,
					content: 'Integrations',
				},
				custom: {
					title: this.$i18n.label.custom,
					content: 'CustomVariables',
				},
			},
		}
	},

	mounted() {
		// Initialize SUI tabs.
		SUI.tabs()
	},
}
</script>
