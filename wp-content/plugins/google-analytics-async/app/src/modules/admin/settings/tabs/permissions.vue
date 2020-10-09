<template>
	<div class="sui-box" id="beehive-settings-permissions">
		<box-header :title="$i18n.title.permissions" />
		<div class="sui-box-body">
			<div
				class="sui-box-settings-row"
				id="beehive-settings-permissions-statistics"
				v-if="canShowStats"
			>
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						{{ $i18n.label.statistics }}
					</span>
					<span class="sui-description">
						{{ $i18n.desc.statistics }}
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<div class="sui-side-tabs sui-tabs">
						<div data-tabs>
							<div class="active" id="permissions-roles-stats">
								{{ $i18n.label.roles }}
							</div>
							<div id="permissions-capabilities-stats">
								{{ $i18n.label.capabilities }}
							</div>
						</div>
						<div data-panes>
							<div class="active">
								<!-- Stats role selector -->
								<statistics-roles />
							</div>
							<div class="sui-tab-boxed">
								<!-- Capability form -->
								<statistics-capability />
							</div>
						</div>
					</div>
					<!-- Overriding settings -->
					<override-settings
						v-if="isNetwork()"
						id="permissions-statistics-roles-override"
						v-model="overwriteStatsCap"
						:label="$i18n.label.override_permissions"
					/>
				</div>
			</div>

			<div
				class="sui-box-settings-row"
				id="beehive-settings-permissions-settings"
				v-if="canShowSettings"
			>
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						{{ $i18n.label.settings }}
					</span>
					<span class="sui-description" v-if="isMultisite()">
						{{ $i18n.desc.settings_network }}</span
					>
					<span class="sui-description" v-else>
						{{ $i18n.desc.settings }}
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<div class="sui-tabs sui-tabs-overflow">
						<div role="tablist" class="sui-tabs-menu">
							<button
								type="button"
								role="tab"
								id="permissions-settings-roles-tab"
								class="sui-tab-item active"
								aria-controls="permissions-settings-roles-tab-content"
								aria-selected="true"
							>
								{{ $i18n.label.user_role }}
							</button>

							<button
								type="button"
								role="tab"
								id="permissions-settings-users-tab"
								class="sui-tab-item"
								aria-controls="permissions-settings-users-tab-content"
								aria-selected="false"
								tabindex="-1"
							>
								{{ $i18n.label.custom_users }}
							</button>
						</div>

						<div :class="tabsContentClass">
							<div
								role="tabpanel"
								tabindex="0"
								id="permissions-settings-roles-tab-content"
								class="sui-tab-content active"
								aria-labelledby="permissions-settings-roles-tab"
							>
								<p class="sui-description">
									{{ $i18n.desc.user_role }}
								</p>
								<p class="sui-description">
									{{ $i18n.desc.user_role_second }}
								</p>
								<!-- User role selector -->
								<settings-roles />
							</div>

							<div
								role="tabpanel"
								tabindex="0"
								id="permissions-settings-users-tab-content"
								class="sui-tab-content"
								aria-labelledby="permissions-settings-users-tab"
								hidden
							>
								<p class="sui-description">
									{{ $i18n.desc.custom_users }}
								</p>
								<!-- Exclude users section -->
								<settings-users />
							</div>
						</div>
					</div>
					<!-- Overriding settings -->
					<override-settings
						v-if="isNetwork()"
						id="permissions-settings-roles-override"
						v-model="overwriteSettingsCap"
						:label="$i18n.label.override_permissions"
					/>
				</div>
			</div>
		</div>
		<box-footer :processing="processing" @submit="$emit('submit')" />
		<!-- Modals -->
		<users-form />
	</div>
</template>

<script>
import UsersForm from './permissions/modals/users-form'
import SettingsRoles from './permissions/settings-roles'
import SettingsUsers from './permissions/settings-users'
import BoxHeader from '@/components/elements/box-header'
import BoxFooter from '@/components/elements/box-footer'
import StatisticsRoles from './permissions/statistics-roles'
import StatisticsCapability from './permissions/statistics-capability'
import OverrideSettings from './permissions/components/override-settings'

export default {
	name: 'Permissions',

	components: {
		UsersForm,
		BoxHeader,
		BoxFooter,
		SettingsUsers,
		SettingsRoles,
		StatisticsRoles,
		OverrideSettings,
		StatisticsCapability,
	},

	props: {
		processing: {
			type: Boolean,
			default: false,
		},
	},

	mounted() {
		SUI.tabs()
		SUI.suiTabs()
	},

	computed: {
		/**
		 * Check if we can show the settings permissions settings.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		canShowSettings() {
			// Only for mutisite subsite.
			if (this.isNetworkWide() && this.isSubsite()) {
				return this.getOption(
					'overwrite_settings_cap',
					'permissions',
					false,
					true
				)
			} else {
				return true
			}
		},

		/**
		 * Check if we can show the stats permissions settings.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		canShowStats() {
			// Only for mutisite subsite.
			if (this.isNetworkWide() && this.isSubsite()) {
				return this.getOption(
					'overwrite_cap',
					'permissions',
					false,
					true
				)
			} else {
				return true
			}
		},

		/**
		 * Computed model object for the settings permissions overriding.
		 *
		 * @since 3.2.5
		 *
		 * @returns {boolean}
		 */
		overwriteSettingsCap: {
			get() {
				return this.getOption('overwrite_settings_cap', 'permissions')
			},
			set(value) {
				this.setOption('overwrite_settings_cap', 'permissions', value)
			},
		},

		/**
		 * Computed model object for the statistics permissions overriding.
		 *
		 * @since 3.2.5
		 *
		 * @returns {boolean}
		 */
		overwriteStatsCap: {
			get() {
				return this.getOption('overwrite_cap', 'permissions')
			},
			set(value) {
				this.setOption('overwrite_cap', 'permissions', value)
			},
		},

		/**
		 * Get the classes for the tab content.
		 *
		 * @since 3.2.5
		 *
		 * @returns {object}
		 */
		tabsContentClass() {
			return {
				'sui-tabs-content': true,
				'beehive-inactive': this.overwriteSettingsCap,
				'beehive-inactive-bg': this.overwriteSettingsCap,
			}
		},
	},
}
</script>
