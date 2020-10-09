<template>
	<div class="sui-recipient">
		<span class="sui-recipient-name">{{ getName }}</span>
		<span class="sui-recipient-email">{{ getEmail }}</span>
		<button
			type="button"
			class="sui-button-icon"
			:disabled="disabled || $moduleVars.current_user == user"
			@click="$emit('removeUser', user)"
		>
			<i class="sui-icon-trash" aria-hidden="true"></i>
		</button>
	</div>
</template>

<script>
export default {
	name: 'RecipientItem',

	props: {
		user: {
			type: Number | String,
			required: true,
		},
		type: {
			type: String,
			required: true,
		},
		disabled: {
			type: Boolean,
			default: false,
		},
	},

	computed: {
		/**
		 * Get name of the current recipient.
		 *
		 * @since 3.2.5
		 *
		 * @returns {string}
		 */
		getName() {
			let user = this.$store.getters['helpers/user'](this.user)

			return user.display_name || '-'
		},

		/**
		 * Get email of the current recipient.
		 *
		 * @since 3.2.5
		 *
		 * @returns {string}
		 */
		getEmail() {
			let user = this.$store.getters['helpers/user'](this.user)

			return user.user_email || '-'
		},
	},
}
</script>
