<template>
	<fragment>
		<p class="sui-description" v-html="$i18n.desc.google_setup"></p>

		<div
			class="sui-form-field"
			:class="{ 'sui-form-field-error': errors.includes('clientId') }"
			style="margin-bottom: 20px;"
		>
			<label
				for="google-accounts-client-id"
				id="google-accounts-client-id-label"
				class="sui-label"
			>
				{{ $i18n.label.client_id }}
			</label>

			<input
				v-model="clientId"
				type="text"
				id="google-accounts-client-id"
				class="sui-form-control"
				aria-labelledby="google-accounts-client-id-label"
				aria-describedby="google-accounts-client-id-error"
				:placeholder="$i18n.placeholder.client_id"
			/>

			<span
				v-if="errors.includes('clientId')"
				id="google-accounts-client-id-error"
				class="sui-error-message"
			>
				{{ $i18n.error.client_id }}
			</span>
		</div>

		<div
			class="sui-form-field"
			:class="{ 'sui-form-field-error': errors.includes('clientSecret') }"
			style="margin-bottom: 10px;"
		>
			<label
				for="google-accounts-client-secret"
				id="google-accounts-client-secret-label"
				class="sui-label"
			>
				{{ $i18n.label.client_secret }}
			</label>

			<input
				v-model="clientSecret"
				type="text"
				id="google-accounts-client-secret"
				class="sui-form-control"
				aria-labelledby="google-accounts-client-secret-label"
				aria-describedby="google-accounts-client-secret-error"
				:placeholder="$i18n.placeholder.client_secret"
			/>

			<span
				v-if="errors.includes('clientSecret')"
				id="google-accounts-client-secret-error"
				class="sui-error-message"
			>
				{{ $i18n.error.client_secret }}
			</span>
		</div>

		<button
			type="button"
			class="sui-button sui-button-sm sui-button-blue"
			:class="authButtonClass"
			aria-live="polite"
			@click="authorize"
		>
			<span class="sui-button-text-default">
				{{ $i18n.button.authorize }}
			</span>
			<span class="sui-button-text-onload">
				<span
					class="sui-icon-loader sui-loading"
					aria-hidden="true"
				></span>
				{{ $i18n.button.processing }}
			</span>
		</button>
	</fragment>
</template>

<script>
import { restGet } from '@/helpers/api'

export default {
	name: 'ApiProjectForm',

	data() {
		return {
			errors: [],
			processing: false,
			clientId: this.getOption('client_id', 'google', ''),
			clientSecret: this.getOption('client_secret', 'google', ''),
		}
	},

	computed: {
		/**
		 * Check if current form has errors.
		 *
		 * @since 3.3.0
		 *
		 * @returns {boolean}
		 */
		hasError() {
			return this.errors.length > 0
		},

		/**
		 * Computed object to get the button class.
		 *
		 * When the API request is being processed, we need
		 * to show the loading icon.
		 *
		 * @since 3.3.0
		 *
		 * @returns {*}
		 */
		authButtonClass() {
			return {
				'sui-button-onload-text': this.processing,
			}
		},
	},

	methods: {
		/**
		 * Perform form validation.
		 *
		 * Both client ID and client secret fields are required
		 * in the auth form.
		 *
		 * @since 3.3.0
		 */
		validateForm() {
			// Reset rhe errors.
			this.errors = []

			if (!this.clientId) {
				this.errors.push('clientId')
			}

			if (!this.clientSecret) {
				this.errors.push('clientSecret')
			}
		},

		/**
		 * Perform authorization form submit.
		 *
		 * When a valid Client ID and Client secret is provided,
		 * use it to create an authorization URL to redirect to
		 * Google authentication.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		authorize() {
			// Perform form validation first.
			this.validateForm()

			if (!this.hasError) {
				this.processing = true

				// Get the auth url from API.
				restGet({
					path: 'v1/auth/auth-url',
					params: {
						client_id: this.clientId,
						client_secret: this.clientSecret,
						network: this.isNetwork() ? 1 : 0,
						context: 'settings',
					},
				}).then((response) => {
					// If the response is valid, redirect to the auth url.
					if (response.success && response.data.url) {
						window.location.href = response.data.url
					} else {
						this.processing = false
						// Process error.
						this.processError()
					}
				})
			}
		},

		/**
		 * Process the error API response.
		 *
		 * If the API request failed, show a notice.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		processError() {
			// Emit custom event for Google connect.
			this.$root.$emit('googleConnectProcessed', {
				type: 'api',
				success: false,
			})

			// Show notice.
			this.$root.$emit('showTopNotice', {
				type: 'error',
				dismiss: true,
				message: this.$i18n.notice.auth_failed,
			})
		},
	},
}
</script>
