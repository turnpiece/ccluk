<template>
	<div v-if="isProSitesReady" class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				{{ $i18n.label.prosites_settings }}
			</span>
			<span class="sui-description">
				{{ $i18n.desc.prosites_settings }}
			</span>
		</div>

		<div class="sui-box-settings-col-2">
			<span class="sui-settings-label">
				{{ $i18n.label.analytics_settings }}
			</span>
			<span class="sui-description">
				{{ $i18n.desc.analytics_settings }}
			</span>
			<label
				v-for="(level, index) in levels"
				:for="`beehive-settings-ps-level-${index}`"
				:key="index"
				class="sui-checkbox sui-checkbox-sm"
			>
				<input
					v-model="psSettings"
					type="checkbox"
					:id="`beehive-settings-ps-level-${index}`"
					:value="index"
				/>
				<span aria-hidden="true"></span>
				<span>{{ level.name }}</span>
			</label>
			<hr />
			<span class="sui-settings-label">
				{{ $i18n.label.dashboard_analytics }}
			</span>
			<span class="sui-description">
				{{ $i18n.desc.dashboard_analytics }}
			</span>
			<label
				v-for="(level, index) in levels"
				:for="`beehive-dashboard-ps-level-${index}`"
				:key="index"
				class="sui-checkbox sui-checkbox-sm"
			>
				<input
					v-model="psDashboard"
					type="checkbox"
					:id="`beehive-dashboard-ps-level-${index}`"
					:value="index"
				/>
				<span aria-hidden="true"></span>
				<span>{{ level.name }}</span>
			</label>
		</div>
	</div>
</template>

<script>
export default {
	name: 'ProSites',

	computed: {
		/**
		 * Check if we have Pro Sites levels available.
		 *
		 * If no levels created yet, we don't need this settings.
		 *
		 * @since 3.2.0
		 *
		 * @returns {boolean}
		 */
		isProSitesReady() {
			return Object.keys(this.levels).length > 0
		},

		/**
		 * Get the available Pro Site levels.
		 *
		 * @since 3.2.0
		 *
		 * @returns {boolean}
		 */
		levels() {
			return this.$moduleVars.ps_levels || {}
		},

		/**
		 * Computed model object to get Pro Sites settings permission.
		 *
		 * @since 3.2.0
		 *
		 * @returns {array}
		 */
		psSettings: {
			get() {
				return this.getOption(
					'prosites_settings_level',
					'general',
					[],
					true
				)
			},
			set(value) {
				this.setOption(
					'prosites_settings_level',
					'general',
					value,
					true
				)
			},
		},

		/**
		 * Computed model object to get Pro Sites dashboard permission.
		 *
		 * @since 3.2.0
		 *
		 * @returns {array}
		 */
		psDashboard: {
			get() {
				return this.getOption(
					'prosites_analytics_level',
					'general',
					[],
					true
				)
			},
			set(value) {
				this.setOption(
					'prosites_analytics_level',
					'general',
					value,
					true
				)
			},
		},
	},
}
</script>
