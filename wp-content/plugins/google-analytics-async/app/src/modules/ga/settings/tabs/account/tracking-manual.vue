<template>
	<fragment>
		<div
			:class="{ 'sui-form-field-error': error }"
			class="sui-form-field beehive-margin-bottom--10"
		>
			<label
				for="beehive-settings-tracking-code-manual"
				class="sui-label"
			>
				{{ $i18n.label.tracking_id }}
			</label>
			<!-- Tracking ID input -->
			<tracking-id
				id="beehive-settings-tracking-code-manual"
				v-model="tracking"
				context="settings"
				@validation="handleValidation"
			/>
			<span class="sui-description" v-if="trackingIdFromNetwork">
				{{ $i18n.desc.tracking_id_inherited }}
			</span>
			<span
				class="sui-description"
				v-html="
					sprintf(
						this.$i18n.desc.tracking_id_help,
						'https://support.google.com/analytics/answer/1032385?rd=1'
					)
				"
			></span>
		</div>
		<sui-notice v-if="error" type="error">
			<p
				v-html="
					sprintf(
						$i18n.notice.invalid_tracking_id,
						'https://support.google.com/analytics/answer/1032385?rd=1'
					)
				"
			></p>
		</sui-notice>
	</fragment>
</template>

<script>
import TrackingId from './fields/tracking-id'
import SuiNotice from '@/components/sui/sui-notice'

export default {
	name: 'TrackingManual',

	components: { SuiNotice, TrackingId },

	props: {
		error: {
			type: Boolean,
			default: false,
		},
	},

	computed: {
		/**
		 * Computed model object for tracking id input.
		 *
		 * @since 3.3.0
		 *
		 * @returns {string}
		 */
		tracking: {
			get() {
				return this.getOption('code', 'tracking', '')
			},
			set(value) {
				this.setOption('code', 'tracking', value)
			},
		},

		/**
		 * Check if we need to show the tracking ID inheritance description.
		 *
		 * When subsites doesn't have tracking id added, we can always inherit
		 * the tracking ID from network admin.
		 *
		 * @since 3.2.0
		 *
		 * @returns {boolean}
		 */
		trackingIdFromNetwork() {
			// Tracking IDs.
			let trackingId = this.getOption('code', 'tracking', '')
			let networkTrackingId = this.getOption('code', 'tracking', '', true)
			// Automatic tracking IDs.
			let networkAutoTrackingId = this.getOption(
				'auto_track',
				'misc',
				'',
				true
			)
			// Auto tracking flag.
			let networkAutoTracking = this.getOption(
				'auto_track',
				'google',
				false,
				true
			)

			// If tracking is already set.
			if (trackingId || !this.isSubsite() || !this.isNetworkWide()) {
				return false
			} else {
				return (
					// If tracking ID is taken from network setup.
					networkTrackingId ||
					(networkAutoTracking && networkAutoTrackingId)
				)
			}
		},
	},

	methods: {
		/**
		 * Handle the input validation event.
		 *
		 * @param {object} data Validation data.
		 *
		 * @since 3.3.0
		 */
		handleValidation(data) {
			// Emit to parent.
			this.$emit('validation', data)
		},
	},
}
</script>
