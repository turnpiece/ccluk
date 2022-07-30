<template>
	<div class="sui-accordion" id="beehive-settings-permissions-roles">
		<div
			v-if="isNetwork()"
			class="sui-accordion-item sui-accordion-item--disabled"
		>
			<div class="sui-accordion-item-header">
				<div class="sui-accordion-item-title">
					<label
						for="beehive-settings-permissions-roles-network-administrator"
						class="sui-toggle sui-accordion-item-action"
					>
						<input
							type="checkbox"
							id="beehive-settings-permissions-roles-network-administrator"
							checked
							disabled
						/>
						<span
							aria-hidden="true"
							class="sui-toggle-slider"
						></span>
						<span class="sui-screen-reader-text">
							{{ $i18n.label.network_administrator }}
						</span>
						<span class="sui-toggle-label">{{ $i18n.label.network_administrator }}</span>
					</label>
				</div>
				<div
					class="sui-accordion-col-auto"
					:style="{ 'pointer-events': 'all' }"
				>
					<span
						class="sui-tooltip sui-tooltip-constrained"
						:data-tooltip="$i18n.tooltip.network_administrator"
					>
						<i class="sui-icon-info" aria-hidden="true"></i>
					</span>
					<button
						class="sui-button-icon sui-accordion-open-indicator"
						:aria-label="$i18n.accordion.open"
					>
						<i class="sui-icon-chevron-down" aria-hidden="true"></i>
					</button>
				</div>
			</div>
		</div>
		<div v-else class="sui-accordion-item sui-accordion-item--disabled">
			<div class="sui-accordion-item-header">
				<div class="sui-accordion-item-title">
					<label
						for="beehive-settings-permissions-roles-administrator"
						class="sui-toggle sui-accordion-item-action"
					>
						<input
							type="checkbox"
							id="beehive-settings-permissions-roles-administrator"
							checked
							disabled
						/>
						<span
							aria-hidden="true"
							class="sui-toggle-slider"
						></span>
						<span class="sui-screen-reader-text">
							{{ $i18n.label.administrator }}
						</span>
						<span class="sui-toggle-label">{{ $i18n.label.administrator }}</span>
					</label>
				</div>
				<div
					class="sui-accordion-col-auto"
					:style="{ 'pointer-events': 'all' }"
				>
					<span
						class="sui-tooltip sui-tooltip-constrained"
						:data-tooltip="$i18n.tooltip.administrator"
					>
						<i class="sui-icon-info" aria-hidden="true"></i>
					</span>
					<button
						class="sui-button-icon sui-accordion-open-indicator"
						:aria-label="$i18n.accordion.open"
					>
						<i class="sui-icon-chevron-down" aria-hidden="true"></i>
					</button>
				</div>
			</div>
		</div>
		<role-accordion-item
			:key="role"
			:role="role"
			:title="title"
			:overwrite="overwriteCap"
			v-for="(title, role) in roles"
		/>
	</div>
</template>

<script>
import ReportTree from './components/report-tree'
import RoleAccordionItem from './components/role-accordion-item'

export default {
	name: 'StatisticsRoles',

	components: {
		ReportTree,
		RoleAccordionItem,
	},

	mounted() {
		// Initialize accordion.
		SUI.suiAccordion(jQuery('#beehive-settings-permissions-roles'))
	},

	data() {
		return {
			roles: this.$moduleVars.roles,
		}
	},

	computed: {
		/**
		 * Computed object to habdle enabled roles permissions.
		 *
		 * @since 3.2.5
		 *
		 * @returns {array}
		 */
		enabledRoles: {
			get() {
				return this.getOption('roles', 'permissions', [])
			},
			set(value) {
				this.setOption('roles', 'permissions', value)
			},
		},

		/**
		 * Check if we can override the settings.
		 *
		 * @since 3.2.5
		 *
		 * @returns {boolean}
		 */
		overwriteCap() {
			return this.getOption('overwrite_cap', 'permissions')
		},
	},
}
</script>
