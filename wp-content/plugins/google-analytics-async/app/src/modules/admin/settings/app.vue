<template>
	<!-- Open sui-wrap -->
	<div class="sui-wrap" id="beehive-wrap">
		<black-friday-notice/>

		<sui-header :title="$i18n.title.settings"/>

		<div class="sui-row-with-sidenav">
			<div class="sui-sidenav" role="navigation">
				<div class="sui-sidenav-settings">
					<ul class="sui-vertical-tabs sui-sidenav-hide-md">
						<router-link
							class="sui-vertical-tab"
							tag="li"
							to="/general"
						>
							<a>{{ $i18n.title.general }}</a>
						</router-link>
						<router-link
							class="sui-vertical-tab"
							tag="li"
							to="/data"
						>
							<a>{{ $i18n.title.data_settings }}</a>
						</router-link>
						<router-link
							class="sui-vertical-tab"
							tag="li"
							to="/permissions"
							v-if="showPermissions"
						>
							<a>{{ $i18n.menus.permissions }}</a>
						</router-link>
					</ul>

					<mobile-nav :selected="$route.path" :paths="getNavs"/>
				</div>
			</div>

			<router-view :processing="processing" @submit="saveSettings"/>
		</div>

		<sui-footer/>

		<!-- Onboarding start -->
		<onboarding v-if="showOnboarding"/>
		<!-- Onboarding end -->
		<!-- Welcome modal -->
		<welcome-modal v-else-if="showWelcome"/>
		<!-- welcome modal end -->

		<!-- Reset confirmation modal -->
		<reset-confirmation/>
	</div>
	<!-- Close sui-wrap -->
</template>

<script>
import Onboarding from './../onboarding/onboarding'
import SuiHeader from '@/components/sui/sui-header'
import SuiFooter from '@/components/sui/sui-footer'
import {hasPermissionsAccess} from '@/helpers/utils'
import MobileNav from '@/components/elements/mobile-nav'
import WelcomeModal from '@/components/elements/modals/welcome-modal'
import ResetConfirmation from './tabs/data/modals/reset-confirmation'
import BlackFridayNotice from '@/components/elements/black-friday-notice'

export default {
	name: 'App',

	components: {
		SuiHeader,
		SuiFooter,
		MobileNav,
		Onboarding,
		WelcomeModal,
		ResetConfirmation,
		BlackFridayNotice,
	},

	data() {
		return {
			processing: false,
		}
	},

	created() {
		// On Google login.
		this.$root.$on('googleLoginUpdate', (success) => {
			this.$store.dispatch('helpers/updateGoogleLogin', {
				reInit: true,
				status: success,
			})
		})

		// On Google login.
		this.$root.$on('googleConnectProcessed', (data) => {
			// Update profiles.
			if (data.success) {
				this.$store.dispatch('helpers/updateGoogleProfiles', {
					reInit: true, // Re load settings.
				})
			}
		})
	},

	mounted() {
		// Update API status.
		if (this.$store.state.helpers.google.logged_in) {
			this.$store.dispatch('helpers/updateGoogleApi', {})
		}
	},

	computed: {
		/**
		 * Check if we can show onboarding modal.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		showOnboarding() {
			return !this.getOption('onboarding_done', 'misc')
		},

		/**
		 * Get the navigation items.
		 *
		 * @since 3.3.5
		 *
		 * @returns {*}
		 */
		getNavs() {
			let navs = {
				'/general': this.$i18n.title.general,
				'/data': this.$i18n.title.data_settings,
			}

			if (this.showPermissions) {
				navs['/permissions'] = this.$i18n.menus.permissions
			}

			return navs
		},

		/**
		 * Check if we can show welcome modal.
		 *
		 * @since 3.2.5
		 *
		 * @returns {boolean}
		 */
		showWelcome() {
			// Do not conflict with onboarding.
			if (this.showOnboarding) {
				return false
			}

			if (
				this.isMultisite() &&
				this.isNetworkWide() &&
				this.isNetwork()
			) {
				return this.getOption('show_welcome', 'misc', false, true)
			} else if (!this.isMultisite() || !this.isNetworkWide()) {
				return this.getOption('show_welcome', 'misc')
			} else {
				return false
			}
		},

		/**
		 * Check if we can show the permissions tab.
		 *
		 * If statistics and permissions settings are not allowed
		 * by network admin on multisite, hide permissions tab.
		 *
		 * @since 3.2.4
		 * @since 3.2.5 Added settings permissions.
		 *
		 * @returns {boolean}
		 */
		showPermissions() {
			return hasPermissionsAccess()
		},
	},

	methods: {
		/**
		 * Save settings values using API.
		 *
		 * @since 3.2.4
		 */
		async saveSettings() {
			// Disable processing.
			this.processing = true

			// Save settings.
			let success = await this.saveOptions()

			if (success) {
				this.$root.$emit('showTopNotice', {
					message: this.$i18n.notice.changes_saved,
				})
			} else {
				this.$root.$emit('showTopNotice', {
					dismiss: true,
					type: 'error',
					message: this.$i18n.notice.changes_failed,
				})
			}

			// Disable processing.
			this.processing = false
		},
	},
}
</script>

<style lang="scss">
@import 'styles/main';
</style>
