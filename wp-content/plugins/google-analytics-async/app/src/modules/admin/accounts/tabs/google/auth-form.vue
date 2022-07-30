<template>
	<fragment>
		<!-- Show already setup notice -->
		<sui-notice
			type="info"
			v-if="showSetupNotice"
			class="sui-notice-spacing-bottom--10"
		>
			<p>
				{{
					sprintf(
						$i18n.notice.google_already_connected,
						$vars.urls.statistics
					)
				}}
			</p>
		</sui-notice>

		<!-- When we can show the simple connect form -->
		<simple-connect-form v-if="showSimpleConnect" />
		<!-- Otherwise show auth form -->
		<div class="sui-side-tabs sui-tabs" v-else>
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
					<api-project-form-uri />
				</div>
			</div>
		</div>
	</fragment>
</template>

<script>
import SuiNotice from '@/components/sui/sui-notice'
import ApiProjectForm from './forms/api-project-form'
import SimpleConnectForm from './forms/simple-connect-form'
import ApiProjectFormUri from './forms/api-project-form-uri'
import DefaultConnectForm from './forms/default-connect-form'

export default {
	name: 'AuthForm',

	components: {
		SuiNotice,
		ApiProjectForm,
		SimpleConnectForm,
		ApiProjectFormUri,
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

		/**
		 * Check if we can show already connected notice.
		 *
		 * If it is a subsite and Google account is setup in
		 * network level, we can show a notice.
		 *
		 * @since 3.3.2
		 *
		 * @returns {boolean}
		 */
		showSetupNotice() {
			// Google vars are required.
			if (!this.$moduleVars.google) {
				return false
			}

			// Check if network is already logged in.
			return this.isSubsite() && this.$moduleVars.google.network_logged_in
		},
	},
}
</script>
