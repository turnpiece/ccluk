<template>
	<!-- Open sui-wrap -->
	<div class="sui-wrap" id="beehive-wrap">
		<sui-header :title="$i18n.title.tag_manager">
			<template v-slot:right>
				<!-- Button to clear the cached data -->
				<refresh-button />
			</template>
		</sui-header>

		<div class="sui-row-with-sidenav" v-if="isActive">
			<div role="navigation" class="sui-sidenav">
				<ul class="sui-vertical-tabs sui-sidenav-hide-md">
					<router-link
						class="sui-vertical-tab"
						tag="li"
						to="/account"
						exact
					>
						<a>{{ $i18n.label.account }}</a>
					</router-link>
					<router-link
						class="sui-vertical-tab"
						tag="li"
						to="/settings"
					>
						<a>{{ $i18n.label.settings }}</a>
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
			<router-view @submit="saveSettings" />
		</div>
		<activation-box v-else />

		<sui-footer />
	</div>
	<!-- Close sui-wrap -->
</template>

<script>
import ActivationBox from './tabs/activation-box'
import SuiHeader from '@/components/sui/sui-header'
import SuiFooter from '@/components/sui/sui-footer'
import SuiSelect from '@/components/sui/sui-select'
import RefreshButton from '@/components/elements/refresh-button'

export default {
	name: 'App',

	components: {
		SuiHeader,
		SuiFooter,
		SuiSelect,
		RefreshButton,
		ActivationBox,
	},

	data() {
		return {
			selectedPage: '#' + this.$route.path,
			navigation: {
				'#/account': this.$i18n.label.account,
				'#/settings': this.$i18n.label.settings,
			},
		}
	},

	computed: {
		/**
		 * Check if the integration is active.
		 *
		 * @since 3.3.0
		 *
		 * @returns {boolean}
		 */
		isActive() {
			return this.getOption('active', 'gtm')
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
				// Show success notice.
				this.$root.$emit('showTopNotice', {
					message: this.$i18n.notice.changes_saved,
				})
			} else {
				// Show error notice.
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
