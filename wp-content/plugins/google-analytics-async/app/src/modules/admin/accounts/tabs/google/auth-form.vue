<template>
	<fragment>
		<!-- When we can show the simple connect form -->
		<simple-connect-form v-if="showSimpleConnect" />
		<!-- Otherwise show auth form -->
		<div v-else class="sui-side-tabs sui-tabs">
			<div data-tabs>
				<div class="active">
					{{ $i18n.label.connect_google }}
				</div>
				<div>{{ $i18n.label.google_api }}</div>
			</div>
			<div data-panes>
				<div class="sui-tab-boxed beehive-google-setup-connect active">
					<!-- When we can show the simple connect form -->
					<default-connect-form @success="" />
				</div>
				<div class="sui-tab-boxed">
					<api-project-form />
				</div>
			</div>
		</div>
	</fragment>
</template>

<script>
import ApiProjectForm from './forms/api-project-form'
import SimpleConnectForm from './forms/simple-connect-form'
import DefaultConnectForm from './forms/default-connect-form'

export default {
	name: 'AuthForm',

	components: {
		ApiProjectForm,
		SimpleConnectForm,
		DefaultConnectForm,
	},

	mounted() {
		// Initialize the SUI tabs.
		SUI.suiTabs()
	},

	computed: {
		/**
		 * Check if we can show simple connect form.
		 *
		 * If it is a subsite and Google account is setup in
		 * network level, we can show a simple connect form.
		 *
		 * @since 3.2.0
		 *
		 * @returns {boolean}
		 */
		showSimpleConnect() {
			// Google vars are required.
			if (!this.$moduleVars.google) {
				return false
			}

			// Required flags.
			let netWorkSetup = this.$moduleVars.google.network_setup
			let netWorkLoggedIn = this.$moduleVars.google.network_logged_in
			let netWorkLoginMethod = this.$moduleVars.google
				.network_login_method

			return (
				this.isSubsite() &&
				netWorkSetup &&
				netWorkLoggedIn &&
				'api' === netWorkLoginMethod
			)
		},
	},
}
</script>
