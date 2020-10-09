<template>
	<div class="sui-box">
		<box-header :title="$i18n.label.settings" />
		<div class="sui-box-body">
			<admin-tracking v-if="showAdminTracking" />
			<anonymize-i-p v-if="showAnonymizeIP" />
			<advertising />
			<pro-sites v-if="showProSites" />
		</div>
		<box-footer :processing="processing" @submit="$emit('submit')" />
	</div>
</template>

<script>
import ProSites from './settings/pro-sites'
import Advertising from './settings/advertising'
import AnonymizeIP from './settings/anonymize-ip'
import AdminTracking from './settings/admin-tracking'
import BoxHeader from '@/components/elements/box-header'
import BoxFooter from '@/components/elements/box-footer'

export default {
	name: 'Settings',

	components: {
		ProSites,
		BoxHeader,
		BoxFooter,
		Advertising,
		AnonymizeIP,
		AdminTracking,
	},

	props: {
		processing: {
			type: Boolean,
			default: false,
		},
	},

	computed: {
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
		 * Check if we can show admin tracking settings.
		 *
		 * @since 3.2.0
		 *
		 * @returns {boolean}
		 */
		showAdminTracking() {
			return this.isNetwork() || !this.isMultisite()
		},

		/**
		 * Check if we can show Anonymize IP settings.
		 *
		 * @since 3.2.0
		 *
		 * @returns {boolean}
		 */
		showAnonymizeIP() {
			if (this.isNetworkWide() && !this.isNetwork()) {
				if (
					this.getOption('anonymize', 'general', false, true) &&
					this.getOption('force_anonymize', 'general', false, true)
				) {
					return false
				}
			}

			return true
		},

		/**
		 * Check if we can show Pro Sites settings.
		 *
		 * @since 3.2.0
		 *
		 * @returns {boolean}
		 */
		showProSites() {
			return this.isNetwork() && this.isMultisite()
		},
	},
}
</script>
