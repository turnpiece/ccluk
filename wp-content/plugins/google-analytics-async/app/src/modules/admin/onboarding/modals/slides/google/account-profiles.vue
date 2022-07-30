<template>
	<div
		class="beehive-box-border-bottom beehive-onboarding-google-stream-section"
	>
		<!-- Show streams dropdown -->
		<streams
			id="beehive-onboarding-google-stream-id"
			parent-element="beehive-onboarding-setup-account"
			:label="$i18n.label.choose_stream"
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
					{{ $i18n.label.auto_detect_measurement }}
					<span
						class="sui-tooltip sui-tooltip-constrained"
						:data-tooltip="$i18n.tooltip.measurement_id"
					>
						<i class="sui-icon-info" aria-hidden="true"></i>
					</span>
				</span>
			</label>
		</div>
	</div>
</template>

<script>
import Streams from './../../../../../ga/admin/tabs/account/fields/streams'

export default {
	name: 'SlideGoogleAccount',

	components: { Streams },

	computed: {
		/**
		 * Computed model to get the auto tracking flag.
		 *
		 * @since 3.2.4
		 * @since 3.4.0 Changed to GA4.
		 *
		 * @returns {boolean}
		 */
		autoTrack: {
			get() {
				return this.getOption('auto_track_ga4', 'google')
			},
			set(value) {
				this.setOption('auto_track_ga4', 'google', value)
			},
		},

		/**
		 * Check if auto tracking code can ne shown.
		 *
		 * @since 3.2.4
		 * @since 3.4.0 Changed to GA4.
		 *
		 * @returns {boolean}
		 */
		showAutoTrack() {
			let autoTrack = this.getOption('auto_track_ga4', 'misc')

			return autoTrack && autoTrack !== ''
		},
	},
}
</script>
