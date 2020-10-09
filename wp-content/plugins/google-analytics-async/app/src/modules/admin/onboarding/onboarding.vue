<template>
	<fragment>
		<!-- Onboarding start -->
		<account-form @submit="submit" @dismiss="dismiss" />
		<tracking-form @submit="submit" @dismiss="dismiss" />
		<!-- Onboarding end -->
	</fragment>
</template>

<script>
import AccountForm from './modals/account-form'
import TrackingForm from './modals/tracking-form'

export default {
	name: 'Onboarding',

	components: {
		AccountForm,
		TrackingForm,
	},

	methods: {
		/**
		 * Simply set the dismissal flag.
		 *
		 * @since 3.2.4
		 */
		dismiss() {
			// Dismiss onboarding.
			this.setOption('onboarding_done', 'misc', 1)

			this.saveOptions()
		},

		/**
		 * Submit the onboarding modal and set the flag.
		 *
		 * Update the dismissal flag and update the settings.
		 *
		 * @since 3.2.4
		 */
		submit() {
			// Dismiss onboarding.
			this.setOption('onboarding_done', 'misc', 1)

			// Save settings.
			let success = this.saveOptions()

			// Emit onboarding complete event.
			this.$root.$emit('onboardingComplete', success)
		},
	},
}
</script>
