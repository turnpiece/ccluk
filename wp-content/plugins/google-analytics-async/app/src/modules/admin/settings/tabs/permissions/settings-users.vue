<template>
	<div class="sui-side-tabs sui-tabs">
		<div data-tabs>
			<div class="active">{{ $i18n.button.exclude }}</div>
			<div>{{ $i18n.button.include }}</div>
		</div>

		<div data-panes>
			<div class="active">
				<p class="sui-description">
					{{ $i18n.desc.exclude_users }}
				</p>
				<label class="sui-label" v-if="excludeUsers.length">
					{{ $i18n.label.excluded_users }}
				</label>

				<!-- Recipient item -->
				<recipient-item
					type="exclude"
					v-for="user in excludeUsers"
					:key="user"
					:user="user"
					:disabled="shouldDisable"
					@removeUser="removeExcludedUser"
				/>

				<!-- Button to add new user -->
				<button
					role="button"
					class="sui-button sui-button-ghost"
					id="beehive-permissions-settings-users-exclude-open"
					:disabled="shouldDisable"
					@click="openExcludeUserForm"
				>
					<i class="sui-icon-plus" aria-hidden="true"></i>
					{{ $i18n.button.add_user }}
				</button>
			</div>
			<div>
				<p class="sui-description">
					{{ $i18n.desc.include_users }}
				</p>
				<label class="sui-label" v-if="includeUsers.length">
					{{ $i18n.label.include_users }}
				</label>

				<!-- Recipient item -->
				<recipient-item
					type="include"
					v-for="user in includeUsers"
					:key="user"
					:user="user"
					:disabled="shouldDisable"
					@removeUser="removeIncludedUser"
				/>

				<!-- Button to add new user -->
				<button
					role="button"
					class="sui-button sui-button-ghost"
					id="beehive-permissions-settings-users-include-open"
					:disabled="shouldDisable"
					@click="openIncludeUserForm"
				>
					<i class="sui-icon-plus" aria-hidden="true"></i>
					{{ $i18n.button.add_user }}
				</button>
			</div>
		</div>
	</div>
</template>

<script>
import RecipientItem from './components/recipient-item'

export default {
	name: 'SettingsUsers',

	components: { RecipientItem },

	computed: {
		/**
		 * Computed model object for the included users list.
		 *
		 * @since 3.2.5
		 *
		 * @returns {array}
		 */
		includeUsers: {
			get() {
				return this.getOption(
					'settings_include_users',
					'permissions',
					[]
				)
			},
			set(value) {
				this.setOption('settings_include_users', 'permissions', value)
			},
		},

		/**
		 * Computed model object for the excluded users list.
		 *
		 * @since 3.2.5
		 *
		 * @returns {array}
		 */
		excludeUsers: {
			get() {
				return this.getOption(
					'settings_exclude_users',
					'permissions',
					[]
				)
			},
			set(value) {
				this.setOption('settings_exclude_users', 'permissions', value)
			},
		},

		/**
		 * Check if we need to disable the settings.
		 *
		 * If network admin is allowing subsite's to override, we will
		 * have to disable the option.
		 *
		 * @since 3.2.5
		 *
		 * @returns {boolean}
		 */
		shouldDisable() {
			return (
				this.isNetwork() &&
				this.getOption('overwrite_settings_cap', 'permissions')
			)
		},
	},

	methods: {
		/**
		 * Remove a user from the included users list.
		 *
		 * @param {int} id User ID.
		 *
		 * @since 3.2.5
		 */
		removeIncludedUser(id) {
			if (
				this.includeUsers.includes(id) &&
				this.$moduleVars.current_user != id
			) {
				this.includeUsers = this.includeUsers.filter(
					(user) => user != id
				)
			}
		},

		/**
		 * Remove a user from the excluded users list.
		 *
		 * @param {int} id User ID.
		 *
		 * @since 3.2.5
		 */
		removeExcludedUser(id) {
			if (
				this.excludeUsers.includes(id) &&
				this.$moduleVars.current_user != id
			) {
				this.excludeUsers = this.excludeUsers.filter(
					(user) => user != id
				)
			}
		},

		/**
		 * Open the modal to get the users list.
		 *
		 * Don't forget to set the type of the form.
		 *
		 * @since 3.2.5
		 */
		openExcludeUserForm() {
			this.$root.$emit('openSettingsPermissionsUsersForm', {
				type: 'exclude',
				focus: 'beehive-permissions-settings-users-exclude-open',
			})
		},

		/**
		 * Open the modal to get the users list.
		 *
		 * Don't forget to set the type of the form.
		 *
		 * @since 3.2.5
		 */
		openIncludeUserForm() {
			this.$root.$emit('openSettingsPermissionsUsersForm', {
				type: 'include',
				focus: 'beehive-permissions-settings-users-include-open',
			})
		},
	},
}
</script>
