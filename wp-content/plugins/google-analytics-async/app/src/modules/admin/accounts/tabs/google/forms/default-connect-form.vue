<template>
	<!-- Show the simple form parts -->
	<default-connection-parts :context="context" @notify="showNotification" />
</template>

<script>
import DefaultConnectionParts from './../../../components/default-connection-parts'

export default {
	name: 'DefaultConnectForm',

	components: { DefaultConnectionParts },

	props: {
		context: {
			type: String,
			default: 'settings',
		},
	},

	methods: {
		/**
		 * Show success or error notification.
		 *
		 * @param {boolean} success Success or error.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		showNotification(success) {
			if (success) {
				// Show success notice.
				this.$root.$emit('showTopNotice', {
					message: this.$i18n.notice.auth_success,
				})
			} else {
				// Show error message.
				this.$root.$emit('showTopNotice', {
					dismiss: true,
					type: 'error',
					message: this.$i18n.notice.google_connect_error,
				})
			}
		},
	},
}
</script>
