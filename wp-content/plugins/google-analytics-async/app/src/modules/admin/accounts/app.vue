<template>
	<!-- Open sui-wrap -->
	<div class="sui-wrap" id="beehive-wrap">
		<sui-header :title="$i18n.title.accounts">
			<template v-slot:right>
				<!-- Button to clear the cached data -->
				<refresh-button />
			</template>
		</sui-header>

		<div class="sui-row-with-sidenav">
			<div class="sui-sidenav">
				<ul class="sui-vertical-tabs sui-sidenav-hide-md">
					<router-link class="sui-vertical-tab" tag="li" to="/google">
						<a>{{ $i18n.label.google_account }}</a>
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
import RefreshButton from '@/components/elements/refresh-button'

export default {
	name: 'App',

	components: {
		SuiHeader,
		SuiFooter,
		SuiSelect,
		RefreshButton,
	},

	data() {
		return {
			selectedPage: '#' + this.$route.path,
			navigation: {
				'#/google': this.$i18n.label.google_account,
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
