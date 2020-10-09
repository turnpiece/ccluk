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
					</label>
					<span>
						{{ $i18n.label.network_administrator }}
					</span>
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
					</label>
					<span>{{ $i18n.label.administrator }}</span>
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
		<div
			class="sui-accordion-item"
			:class="accordionClass(role)"
			:key="role"
			v-for="(title, role) in roles"
		>
			<div class="sui-accordion-item-header">
				<div class="sui-accordion-item-title">
					<label
						:for="`beehive-settings-permissions-roles-${role}`"
						class="sui-toggle sui-accordion-item-action"
					>
						<input
							v-model="enabledRoles"
							type="checkbox"
							:id="`beehive-settings-permissions-roles-${role}`"
							:value="role"
							:disabled="overwriteCap"
						/>
						<span
							aria-hidden="true"
							class="sui-toggle-slider"
						></span>
						<span class="sui-screen-reader-text">{{ title }}</span>
					</label>
					<span>{{ title }}</span>
				</div>
				<div class="sui-accordion-col-auto">
					<button
						class="sui-button-icon sui-accordion-open-indicator"
						:aria-label="$i18n.accordion.open"
					>
						<i class="sui-icon-chevron-down" aria-hidden="true"></i>
					</button>
				</div>
			</div>
			<div class="sui-accordion-item-body">
				<div class="sui-box">
					<div class="sui-box-body">
						<div
							class="sui-form-field"
							v-for="(report, type) in getReportItems"
							:key="type"
						>
							<report-tree
								:role="role"
								:type="report.name"
								:title="report.title"
								:items="report.children"
							/>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import ReportTree from './components/report-tree'

export default {
	name: 'StatisticsRoles',

	components: { ReportTree },

	mounted() {
		// Initialize accordian.
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

		/**
		 * Get the report tree object.
		 *
		 * @since 3.2.5
		 *
		 * @returns {object}
		 */
		getReportItems() {
			return this.$moduleVars.report_tree || {}
		},
	},

	methods: {
		/**
		 * Set the accordion class based on the role.
		 *
		 * @since 3.2.5
		 *
		 * @returns {object}
		 */
		accordionClass(role) {
			// Check if overriden.
			let override = this.getOption('overwrite_cap', 'permissions')

			return {
				'sui-accordion-item--disabled':
					!this.enabledRoles.includes(role) || this.overwriteCap,
			}
		},
	},
}
</script>
