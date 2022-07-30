<template>
	<div class="sui-modal sui-modal-md">
		<div
			role="dialog"
			class="sui-modal-content sui-content-fade-out"
			aria-live="polite"
			aria-modal="true"
			:id="modal"
		>
			<!-- Tracking form modal -->
			<slide-tracking-id
				:can-continue="showAdminTracking"
				@dismiss="dismiss"
				@submit="submit"
			/>

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
import SlideTrackingId from './slides/slide-tracking-id'
import SlideAdminTracking from './slides/slide-admin-tracking'

export default {
	name: 'OnboardingTracking',

	components: {
		SlideFinishing,
		SlideTrackingId,
		SlideAdminTracking,
	},

	mixins: [Modal],

	data() {
		return {
			modal: 'beehive-onboarding-setup-tracking',
			closeFocus: 'beehive-wrap',
		}
	},

	computed: {
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

			// Close the modal.
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
