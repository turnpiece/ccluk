<template>
	<fragment>
		<sui-notice-alert
			id="beehive-onboarding-google-auth-error"
			type="error"
			:show="error"
			:message="this.$i18n.notice.google_connect_error"
		/>
		<div class="sui-border-frame beehive-google-setup-connect">
			<!-- Show the simple form parts -->
			<default-connection-parts
				:context="context"
				@notify="showNotification"
			/>
		</div>
	</fragment>
</template>

<script>
import SuiNoticeAlert from '@/components/sui/sui-notice-alert'
import DefaultConnectionParts from '@/modules/admin/accounts/components/default-connection-parts'

export default {
	name: 'DefaultConnectForm',

	components: {
		SuiNoticeAlert,
		DefaultConnectionParts,
	},

	props: {
		context: {
			type: String,
			default: 'onboarding',
		},
	},

	data() {
		return {
			error: false,
		}
	},

	methods: {
		/**
		 * Show error notification.
		 *
		 * @param {boolean} success Success or error.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		showNotification(success) {
			this.error = !success
		},
	},
}
</script>
