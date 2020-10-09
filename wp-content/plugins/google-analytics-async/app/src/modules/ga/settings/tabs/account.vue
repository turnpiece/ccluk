<template>
	<div class="sui-box">
		<box-header :title="$i18n.label.account" />
		<div class="sui-box-body">
			<p>{{ $i18n.desc.account }}</p>

			<!-- API status notice -->
			<sui-notice v-if="!isApiUp" type="error">
				<p>{{ $store.state.helpers.googleApi.error }}</p>
			</sui-notice>

			<!-- Account setup notice -->
			<sui-notice type="info" v-else-if="showFullNotice">
				<p
					v-html="
						sprintf(
							$i18n.notice.account_setup_both,
							$vars.urls.statistics
						)
					"
				></p>
			</sui-notice>
			<sui-notice type="info" v-else-if="showAccountNotice">
				<p
					v-html="
						sprintf(
							$i18n.notice.account_setup_login,
							$vars.urls.statistics
						)
					"
				></p>
			</sui-notice>
			<sui-notice type="info" v-else-if="showTrackingNotice">
				<p
					v-html="
						sprintf(
							$i18n.notice.account_setup_tracking,
							'&#60;head&#62;',
							$vars.urls.statistics
						)
					"
				></p>
			</sui-notice>

			<div class="sui-box-settings-row">
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						{{ $i18n.label.analytics_profile }}
					</span>
					<span class="sui-description">
						{{ $i18n.desc.analytics_profile }}
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<!-- Profile/View selection -->
					<profiles-connected v-if="isConnected" />
					<profiles-disconnected v-else />
				</div>
			</div>

			<div class="sui-box-settings-row">
				<div class="sui-box-settings-col-1">
					<span v-if="isNetwork()" class="sui-settings-label">
						{{ $i18n.label.network_tracking }}
					</span>
					<span v-else class="sui-settings-label">
						{{ $i18n.label.tracking_statistics }}
					</span>
					<span v-if="isNetwork()" class="sui-description">
						{{ $i18n.desc.network_tracking }}
					</span>
					<span v-else class="sui-description">
						{{ $i18n.desc.tracking_statistics }}
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<!-- Show automatic tracking -->
					<tracking-automatic v-if="showAutoTracking" />
					<!-- Show manual tracking -->
					<tracking-manual
						:error="error"
						v-else
						@validation="validation"
					/>
				</div>
			</div>
		</div>
		<box-footer :processing="processing" @submit="formSubmit" />
	</div>
</template>

<script>
import { isValidGAID } from '@/helpers/utils'
import SuiNotice from '@/components/sui/sui-notice'
import TrackingManual from './account/tracking-manual'
import BoxHeader from '@/components/elements/box-header'
import BoxFooter from '@/components/elements/box-footer'
import TrackingAutomatic from './account/tracking-automatic'
import ProfilesConnected from './account/profiles-connected'
import ProfilesDisconnected from './account/profiles-disconnected'

export default {
	name: 'Account',

	components: {
		BoxHeader,
		BoxFooter,
		SuiNotice,
		TrackingManual,
		TrackingAutomatic,
		ProfilesConnected,
		ProfilesDisconnected,
	},

	props: {
		processing: {
			type: Boolean,
			default: false,
		},
	},

	data() {
		return {
			error: false,
			valid: true,
		}
	},

	computed: {
		/**Eh
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
		 * Check if Analytics API is up and running.
		 *
		 * @since 3.2.0
		 *
		 * @returns {boolean}
		 */
		isApiUp() {
			return this.$store.state.helpers.googleApi.status
		},

		/**
		 * Get the API error message text.
		 *
		 * @since 3.2.0
		 *
		 * @returns {string}
		 */
		apiErrorMessage() {
			return this.$store.state.helpers.googleApi.error
		},

		/**
		 * Check if we can show automatic tracking ID.
		 *
		 * @since 3.3.0
		 *
		 * @return {boolean}
		 */
		showAutoTracking() {
			let account = this.getOption('account_id', 'google')
			let autoTrack = this.getOption('auto_track', 'google')
			let autoTrackId = this.getOption('auto_track', 'misc')

			return account && autoTrack && autoTrackId && this.isConnected
		},

		/**
		 * Check if we can show the connection notice.
		 *
		 * When everything is setup, appreciate the user.
		 *
		 * @since 3.2.7
		 */
		showFullNotice() {
			return this.showAccountNotice && this.showTrackingNotice
		},

		/**
		 * Check if Google account is connected.
		 *
		 * @since 3.2.7
		 */
		showAccountNotice() {
			let account = this.getOption('account_id', 'google', '')
			let loggedIn = this.$store.state.helpers.google.logged_in

			return loggedIn && '' !== account
		},

		/**
		 * Check if tracking ID is setup.
		 *
		 * @since 3.2.7
		 */
		showTrackingNotice() {
			let trackId = this.getOption('code', 'tracking', '')
			let autoTrack = this.getOption('auto_track', 'google')
			let autoTrackId = this.getOption('auto_track', 'misc', '')

			return (
				('' !== trackId && isValidGAID(trackId)) ||
				(autoTrack && '' !== autoTrackId && isValidGAID(autoTrackId))
			)
		},
	},

	methods: {
		/**
		 * On tracking code validation process.
		 *
		 * @param {object} data Custom data.
		 *
		 * @since 3.2.4
		 */
		validation(data) {
			this.valid = data.valid

			if (this.valid) {
				this.error = false
			}
		},

		/**
		 * Save settings values using API.
		 *
		 * @param {string} tab Current tab.
		 *
		 * @since 3.2.4
		 */
		formSubmit(tab) {
			if (this.valid) {
				this.error = false

				// Save settings.
				this.$emit('submit')
			} else {
				this.error = true
			}
		},
	},
}
</script>
