<template>
	<!-- Open sui-wrap -->
	<div class="sui-wrap" id="beehive-wrap">
		<black-friday-notice/>

		<sui-header :title="$i18n.title.accounts" />

		<div class="sui-row-with-sidenav">
			<div class="sui-sidenav" role="navigation">
				<div class="sui-sidenav-settings">
					<ul class="sui-vertical-tabs sui-sidenav-hide-md">
						<router-link
							class="sui-vertical-tab"
							tag="li"
							to="/google"
						>
							<a>{{ $i18n.label.google_account }}</a>
						</router-link>
					</ul>

					<mobile-nav :selected="$route.path" :paths="nav" />
				</div>
			</div>
			<router-view />
		</div>

		<sui-footer />
	</div>
	<!-- Close sui-wrap -->
</template>

<script>
import SuiHeader from '@/components/sui/sui-header'
import SuiFooter from '@/components/sui/sui-footer'
import SuiSelect from '@/components/sui/sui-select'
import MobileNav from '@/components/elements/mobile-nav'
import BlackFridayNotice from '@/components/elements/black-friday-notice'

export default {
	name: 'App',

	components: {
		SuiHeader,
		SuiFooter,
		SuiSelect,
		MobileNav,
		BlackFridayNotice
	},

	data() {
		return {
			nav: {
				'/google': this.$i18n.label.google_account,
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
	},

	mounted() {
		// Update API status.
		if (this.$store.state.helpers.google.logged_in) {
			this.$store.dispatch('helpers/updateGoogleApi', {})
		}
	},
}
</script>

<style lang="scss">
@import 'styles/main';
</style>
