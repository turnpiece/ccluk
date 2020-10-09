<template>
	<div class="sui-border-frame google-account-selector">
		<profiles
			id="beehive-settings-google-account-id"
			:label="$i18n.label.choose_account"
			:show-desc="isConnected"
		/>
		<!-- When there is no profiles found -->
		<sui-notice v-if="emptyProfiles" type="error">
			<p
				v-html="
					sprintf(
						$i18n.notice.no_accounts,
						'https://analytics.google.com/analytics/web/'
					)
				"
			></p>
		</sui-notice>
		<div v-if="showAutoTrack" class="sui-form-field">
			<label
				for="beehive-settings-google-auto-track"
				class="sui-checkbox sui-checkbox-sm"
			>
				<input
					v-model="autoTrack"
					type="checkbox"
					id="beehive-settings-google-auto-track"
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
import Profiles from './fields/profiles'
import SuiNotice from '@/components/sui/sui-notice'

export default {
	name: 'ProfilesConnected',

	components: { Profiles, SuiNotice },

	computed: {
		/**
		 * Computed model object to get auto tracking ID.
		 *
		 * @since 3.2.0
		 *
		 * @returns {string}
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
		 * Computed method to check if auto tracking is enabled.
		 *
		 * @since 3.2.0
		 *
		 * @returns {boolean}
		 */
		showAutoTrack() {
			let autoTrack = this.getOption('auto_track', 'misc')

			return autoTrack && autoTrack !== ''
		},

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
		 * Check if Google profile list is empty.
		 *
		 * @since 3.3.0
		 *
		 * @returns {boolean}
		 */
		emptyProfiles() {
			return this.$store.state.helpers.google.profiles.length <= 0
		},
	},
}
</script>
