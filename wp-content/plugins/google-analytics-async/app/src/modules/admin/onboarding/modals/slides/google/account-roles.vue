<template>
	<div class="sui-form-field">
		<span class="sui-label">
			{{ $i18n.label.display_statistics }}
		</span>
		<label
			v-for="(title, role) in getRoles"
			class="sui-checkbox sui-checkbox-stacked"
			:key="role"
			:for="`beehive-onboarding-permissions-roles-${role}`"
		>
			<input
				v-model="roles"
				type="checkbox"
				class="google-modal-roles"
				:value="role"
				:id="`beehive-onboarding-permissions-roles-${role}`"
			/>
			<span aria-hidden="true"></span>
			<span>{{ title }}</span>
		</label>
	</div>
</template>

<script>
export default {
	name: 'AccountRoles',

	computed: {
		/**
		 * Computed model to get the enabled roles.
		 *
		 * @since 3.2.4
		 *
		 * @returns {array}
		 */
		roles: {
			get() {
				return this.getOption('roles', 'permissions', [])
			},
			set(value) {
				this.setOption('roles', 'permissions', value)
			},
		},

		/**
		 * Get the available roles list.
		 *
		 * @since 3.3.0
		 *
		 * @return {array}
		 */
		getRoles() {
			return this.$moduleVars.roles || []
		},
	},
}
</script>
