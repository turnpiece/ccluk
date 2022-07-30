<template>
	<div class="sui-box">
		<box-header :title="$i18n.label.google_account" />
		<div class="sui-box-body">
			<!-- API status notice -->
			<sui-notice v-if="!isApiUp" type="error">
				<p>{{ apiErrorMessage }}</p>
			</sui-notice>
			<sui-notice type="info" v-if="isConnected">
				<p>{{ $i18n.notice.account_setup }}</p>
			</sui-notice>
			<div class="sui-box-settings-row">
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						{{ $i18n.label.account }}
					</span>
					<span class="sui-description" v-if="isNetwork()">
						{{ $i18n.desc.google_account_network }}
					</span>
					<span class="sui-description" v-else-if="isSubsite()">
						{{ $i18n.desc.google_account_subsite }}
					</span>
					<span class="sui-description" v-else>
						{{ $i18n.desc.google_account_single }}
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<account-details v-if="isConnected" />
					<auth-form v-else />
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import AuthForm from './google/auth-form'
import SuiNotice from '@/components/sui/sui-notice'
import AccountDetails from './google/account-details'
import BoxHeader from '@/components/elements/box-header'

export default {
	name: 'GoogleAccount',

	components: {
		AuthForm,
		BoxHeader,
		SuiNotice,
		AccountDetails,
	},

	mounted() {
		// Show auth notice if required.
		this.authRedirectNotice()
	},

	computed: {
		/**
		 * Check if Google account is connected.
		 *
		 * @since 3.3.0
		 *
		 * @returns {boolean}
		 */
		isConnected() {
			return this.$store.state.helpers.google.logged_in
		},

		/**
		 * Check if Analytics API is up and running.
		 *
		 * @since 3.2.0
		 *
		 * @returns {boolean}
		 */
		isApiUp() {
			return this.$store.state.helpers.googleApi.status
		},

		/**
		 * Get the API error message text.
		 *
		 * @since 3.2.0
		 *
		 * @returns {string}
		 */
		apiErrorMessage() {
			return this.$store.state.helpers.googleApi.error
		},
	},

	methods: {
		/**
		 * Show the API form authentication failure or success notice.
		 *
		 * When the authentication redirect happens, we will store a flag
		 * in settings so that we can show the success or failure notice
		 * accordingly.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		authRedirectNotice() {
			let authSuccess = this.getOption(
				'google_auth_redirect_success',
				'misc'
			)

			// No need to do anything if flag is false.
			if (!authSuccess) {
				return
			}

			if ('success' === authSuccess) {
				// Show success notice.
				this.$root.$emit('showTopNotice', {
					message: this.$i18n.notice.auth_success,
				})
			} else if ('error' === authSuccess) {
				// Show error notice.
				this.$root.$emit('showTopNotice', {
					dismiss: true,
					type: 'error',
					message: this.$i18n.notice.auth_failed,
				})
			}

			// Reset the flag.
			this.setOption('google_auth_redirect_success', 'misc', 0)
			// Save settings.
			this.saveOptions()
		},
	},
}
</script>
