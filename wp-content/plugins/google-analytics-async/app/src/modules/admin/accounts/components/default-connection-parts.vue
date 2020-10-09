<template>
	<fragment>
		<p class="sui-description" style="margin: 0 0 10px;">
			{{ $i18n.desc.connect_google }}
		</p>

		<p style="margin-top: 0;">
			<a
				type="button"
				target="_blank"
				class="sui-button sui-button-lg beehive-connect-google-btn"
				:href="$moduleVars.google.login_url"
			>
				<i class="sui-icon-google-connect" aria-hidden="true"></i>
				{{ $i18n.label.connect_google }}
			</a>
		</p>

		<div :class="inputErrorClass" class="sui-form-field">
			<label
				:for="`google-${context}-access-code`"
				:id="`google-${context}-access-code-label`"
				class="sui-label"
				>{{ $i18n.label.access_code }}</label
			>

			<input
				v-model="accessCode"
				type="text"
				class="sui-form-control"
				:placeholder="$i18n.placeholder.access_code"
				:id="`google-${context}-access-code`"
				:aria-labelledby="`google-${context}-access-code-label`"
				:aria-describedby="`google-${context}-access-code-error`"
			/>

			<p
				v-if="error"
				:id="`google-${context}-access-code-error`"
				class="sui-error-message"
			>
				{{ $i18n.error.access_code }}
			</p>

			<p style="margin: 10px 0 0;">
				<button
					type="button"
					aria-live="polite"
					class="sui-button sui-button-blue"
					:class="authButtonClass"
					@click="exchangeCode"
				>
					<span class="sui-button-text-default">
						{{ $i18n.button.authorize }}
					</span>
					<span class="sui-button-text-onload">
						<i
							class="sui-icon-loader sui-loading"
							aria-hidden="true"
						></i>
						{{ $i18n.button.authorizing }}
					</span>
				</button>
			</p>
		</div>
	</fragment>
</template>

<script>
import { restGet } from '@/helpers/api'

export default {
	name: 'DefaultConnectForm',

	props: {
		context: {
			type: String,
			default: 'settings',
		},
	},

	data() {
		return {
			error: false,
			processing: false,
			accessCode: '',
		}
	},

	computed: {
		/**
		 * Computed object to get the input field class.
		 *
		 * @since 3.3.0
		 *
		 * @returns {*}
		 */
		inputErrorClass() {
			return {
				'sui-form-field-error': this.error,
			}
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
		 * Perform the form validation.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		validateForm() {
			// Set error state.
			this.error = !this.accessCode
		},

		/**
		 * Exchange the access code with Google and get the token.
		 *
		 * After getting access code from Google, exchange it with
		 * Google using the API and get the access token to complete
		 * the authentication process.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		exchangeCode() {
			// Perform the form validation first.
			this.validateForm()

			// Only if valid.
			if (!this.error) {
				this.processing = true

				// Perform the API request.
				restGet({
					path: 'auth/exchange-code',
					params: {
						access_code: this.accessCode,
						client_id: this.$moduleVars.google.client_id,
						network: this.isNetwork() ? 1 : 0,
					},
				}).then((response) => {
					// Process the API response.
					this.processResponse(response.success)

					this.processing = false
				})
			}
		},

		/**
		 * Process the API request response.
		 *
		 * Show success or error notice, fire custom events
		 * on success.
		 *
		 * @param {boolean} success Success or error.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		processResponse(success) {
			// Emit custom event.
			this.$root.$emit('googleConnectProcessed', {
				type: 'simple',
				success: success,
				context: this.context,
			})

			if (success) {
				// Emit Google login event.
				this.$root.$emit('googleLoginUpdate', true)
			}

			// Emit notify event event.
			this.$emit('notify', success)
		},
	},
}
</script>
