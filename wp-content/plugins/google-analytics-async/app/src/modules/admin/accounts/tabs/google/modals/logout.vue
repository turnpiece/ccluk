<template>
	<div class="sui-modal sui-modal-sm">
		<div
			role="dialog"
			class="sui-modal-content sui-content-fade-out"
			aria-modal="true"
			aria-labelledby="beehive-google-logout-confirm-title"
			aria-describedby="beehive-google-logout-confirm-description"
			:id="modal"
		>
			<div class="sui-box">
				<div
					class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60"
				>
					<button
						class="sui-button-icon sui-button-float--right"
						data-modal-close
					>
						<i class="sui-icon-close sui-md" aria-hidden="true"></i>
						<span class="sui-screen-reader-text">
							{{ $i18n.dialog.close }}
						</span>
					</button>
					<h3
						id="beehive-google-logout-confirm-title"
						class="sui-box-title sui-lg"
					>
						{{ $i18n.label.logout }}
					</h3>
					<p
						id="beehive-google-logout-confirm-description"
						class="sui-description"
					>
						{{ $i18n.desc.logout_first }}
						<br />
						{{ $i18n.desc.logout_second }}
					</p>
				</div>
				<div class="sui-box-footer sui-flatten sui-content-center">
					<button
						class="sui-button sui-button-ghost"
						data-modal-close
					>
						{{ $i18n.dialog.cancel }}
					</button>
					<button
						type="button"
						class="sui-button sui-button-red"
						aria-live="polite"
						id="beehive-google-logout-confirm-button"
						:class="buttonClass"
						@click="logoutAccount"
					>
						<span class="sui-button-text-default">
							<i class="sui-icon-logout" aria-hidden="true"></i>
							{{ $i18n.label.logout }}
						</span>
						<span class="sui-button-text-onload">
							<i
								class="sui-icon-loader sui-loading"
								aria-hidden="true"
							></i>
							{{ $i18n.button.logging_out }}
						</span>
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { restGet } from '@/helpers/api'
import Modal from '@/components/mixins/modal'

export default {
	name: 'LogoutModal',

	mixins: [Modal],

	data() {
		return {
			modal: 'beehive-google-logout-confirm',
			processing: false,
		}
	},

	computed: {
		/**
		 * Get button class for the submit button.
		 *
		 * @since 3.2.0
		 *
		 * @returns {*}
		 */
		buttonClass() {
			return {
				'sui-button-ghost': !this.processing,
				'sui-button-onload-text': this.processing,
			}
		},
	},

	methods: {
		logoutAccount() {
			this.processing = true

			restGet({
				path: 'v1/auth/logout',
				params: {
					network: this.isNetwork() ? 1 : 0,
				},
			}).then((response) => {
				// Emit Google logout.
				this.$root.$emit('googleLoginUpdate', false)

				// Disable processing state.
				this.processing = false
				// Close the modal.
				this.closeModal()
				// Show success notice.
				if (response.success) {
					this.$root.$emit('showTopNotice', {
						message: this.$i18n.notice.logged_out,
					})
				}
			})
		},
	},
}
</script>
