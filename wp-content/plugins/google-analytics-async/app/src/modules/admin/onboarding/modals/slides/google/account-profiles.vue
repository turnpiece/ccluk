<template>
	<div
		class="beehive-box-border-bottom beehive-onboarding-google-profile-section"
	>
		<!-- Show profiles dropdown -->
		<profiles
			id="beehive-onboarding-google-account-id"
			:label="$i18n.label.choose_account"
			:show-desc="false"
		/>
		<div v-if="showAutoTrack" class="sui-form-field">
			<label
				for="beehive-onboarding-google-auto-track"
				class="sui-checkbox sui-checkbox-sm"
			>
				<input
					v-model="autoTrack"
					type="checkbox"
					id="beehive-onboarding-google-auto-track"
					value="1"
				/>
				<span aria-hidden="true"></span>
				<span>
					{{ $i18n.label.auto_detect_id }}
					<span
						class="sui-tooltip sui-tooltip-constrained"
						:data-tooltip="$i18n.tooltip.tracking_id"
					>
						<i class="sui-icon-info" aria-hidden="true"></i>
					</span>
				</span>
			</label>
		</div>
	</div>
</template>

<script>
import Profiles from './../../../../../ga/settings/tabs/account/fields/profiles'

export default {
	name: 'SlideGoogleAccount',

	components: { Profiles },

	computed: {
		/**
		 * Computed model to get the auto tracking flag.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		autoTrack: {
			get() {
				return this.getOption('auto_track', 'google')
			},
			set(value) {
				this.setOption('auto_track', 'google', value)
			},
		},

		/**
		 * Check if auto tracking code can ne shown.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		showAutoTrack() {
			let autoTrack = this.getOption('auto_track', 'misc')

			return autoTrack && autoTrack !== ''
		},
	},
}
</script>
