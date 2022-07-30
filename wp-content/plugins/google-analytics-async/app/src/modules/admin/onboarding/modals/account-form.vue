<template>
	<div class="sui-modal" :class="modalSizeClass">
		<div
			role="dialog"
			class="sui-modal-content sui-content-fade-out"
			aria-live="polite"
			aria-modal="true"
			:id="modal"
		>
			<!-- Show account selection if connected -->
			<slide-google-account
				:can-continue="showAdminTracking"
				v-if="isConnected"
				@dismiss="dismiss"
				@submit="submit"
			/>

			<!-- Show auth form if not connected yet -->
			<slide-google-auth @dismiss="dismiss" v-else />

			<!-- Admin tracking settings modal -->
			<slide-admin-tracking
				v-if="showAdminTracking"
				@dismiss="dismiss"
				@submit="submit"
			/>

			<!-- Finishing modal -->
			<slide-finishing />
		</div>
	</div>
</template>

<script>
import Modal from '@/components/mixins/modal'
import SlideFinishing from './slides/slide-finishing'
import SlideGoogleAuth from './slides/slide-google-auth'
import SlideAdminTracking from './slides/slide-admin-tracking'
import SlideGoogleAccount from './slides/slide-google-account'

export default {
	name: 'OnboardingAccount',

	mixins: [Modal],

	components: {
		SlideFinishing,
		SlideGoogleAuth,
		SlideGoogleAccount,
		SlideAdminTracking,
	},

	data() {
		return {
			modal: 'beehive-onboarding-setup-account',
			closeFocus: 'beehive-wrap',
		}
	},

	mounted() {
		// Open modal.
		this.openModal()
	},

	updated() {
		// Initialize modal again to setup actions.
		SUI.modalDialog()

		// Open again.
		this.openModal()
	},

	computed: {
		/**
		 * Get the modal size.
		 *
		 * @since 3.2.4
		 *
		 * @returns {*}
		 */
		modalSizeClass() {
			return {
				'sui-modal-md': this.isConnected,
				'sui-modal-lg': !this.isConnected,
			}
		},

		/**
		 * Check if current user is logged in with Google.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		isConnected() {
			return this.$store.state.helpers.google.logged_in
		},

		/**
		 * Check if we can show Admin Tracking option.
		 *
		 * @since 3.3.3
		 *
		 * @returns {boolean}
		 */
		showAdminTracking() {
			return (
				!this.isMultisite() ||
				!this.isNetworkWide() ||
				(this.isNetworkWide() && this.isNetwork())
			)
		},
	},

	methods: {
		/**
		 * Dismiss the current onboarding modal.
		 *
		 * @since 3.3.0
		 */
		dismiss() {
			// Emit dismiss event.
			this.$emit('dismiss')

			this.closeModal()
		},

		/**
		 * Submit the current onboarding modal.
		 *
		 * @since 3.3.0
		 */
		submit() {
			// Close modal after 2 seconds.
			setTimeout(() => {
				// Emit submit event.
				this.$emit('submit')

				// Close the modal.
				this.closeModal()
			}, 2000)
		},
	},
}
</script>
