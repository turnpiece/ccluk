<template>
	<!-- Open sui-wrap -->
	<div class="sui-wrap" id="beehive-wrap">
		<sui-header :title="$i18n.title.settings">
			<template v-slot:right>
				<!-- Button to clear the cached data -->
				<refresh-button />
			</template>
		</sui-header>

		<div class="sui-row-with-sidenav">
			<div class="sui-sidenav">
				<ul class="sui-vertical-tabs sui-sidenav-hide-md">
					<router-link
						class="sui-vertical-tab"
						tag="li"
						to="/permissions"
					>
						<a>{{ $i18n.menus.permissions }}</a>
					</router-link>
				</ul>
				<div class="sui-sidenav-hide-lg">
					<sui-select
						class="sui-mobile-nav"
						style="display: none;"
						id="settings-nav"
						:options="navigation"
						v-model="selectedPage"
					/>
				</div>
			</div>
			<router-view :processing="processing" @submit="saveSettings" />
		</div>

		<sui-footer />

		<!-- Onboarding start -->
		<onboarding v-if="showOnboarding" />
		<!-- Onboarding end -->
		<!-- Welcome modal -->
		<welcome-modal v-else-if="showWelcome" />
		<!-- welcome modal end -->
	</div>
	<!-- Close sui-wrap -->
</template>

<script>
import Onboarding from './../onboarding/onboarding'
import SuiHeader from '@/components/sui/sui-header'
import SuiFooter from '@/components/sui/sui-footer'
import SuiSelect from '@/components/sui/sui-select'
import RefreshButton from '@/components/elements/refresh-button'
import WelcomeModal from '@/components/elements/modals/welcome-modal'

export default {
	name: 'App',

	components: {
		SuiHeader,
		SuiFooter,
		SuiSelect,
		Onboarding,
		WelcomeModal,
		RefreshButton,
	},

	data() {
		return {
			processing: false,
			selectedPage: '#' + this.$route.path,
			navigation: {
				'#/permissions': this.$i18n.menus.permissions,
			},
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
