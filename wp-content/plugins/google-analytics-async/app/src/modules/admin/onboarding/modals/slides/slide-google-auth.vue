<template>
	<div
		:id="`${$parent.modal}-google-account`"
		class="sui-modal-slide sui-active sui-loaded"
		data-modal-size="md"
	>
		<div class="sui-box">
			<div
				class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60"
			>
				<whitelabel-banner
					src="onboarding/welcome.png"
					:alt="$i18n.label.auth_form_alt"
				/>

				<button
					class="sui-button-icon sui-button-float--right"
					@click="$emit('dismiss')"
				>
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
					<span class="sui-screen-reader-text">
						{{ $i18n.dialog.close }}
					</span>
				</button>
				<h3 class="sui-box-title sui-lg">
					{{ sprintf($i18n.label.welcome, $vars.plugin.name) }}
				</h3>
				<p class="sui-description" v-if="isNetwork()">
					{{ $i18n.desc.welcome_network }}
				</p>
				<p class="sui-description" v-else>
					{{ $i18n.desc.welcome_single }}
				</p>
			</div>
			<div
				class="sui-box-body"
				:class="{ 'sui-content-center': showSimpleConnect }"
			>
				<!-- When we can show the simple connect form -->
				<simple-connect-form v-if="showSimpleConnect" />
				<!-- Show Google auth form -->
				<default-connect-form v-else />
			</div>
			<div
				class="sui-box-footer sui-flatten sui-content-center sui-spacing-bottom--50"
			>
				<span
					class="beehive-modal-forward-link sui-block-content-center"
				>
					<a
						href="#"
						@click.prevent
						data-modal-replace="beehive-onboarding-setup-tracking"
						data-modal-close-focus="beehive-wrap"
						data-modal-replace-mask="false"
					>
						{{ $i18n.label.google_tracking_id }}
					</a>
				</span>
			</div>
		</div>
	</div>
</template>

<script>
import SimpleConnectForm from './google/simple-connect-form'
import DefaultConnectForm from './google/default-connect-form'
import WhitelabelBanner from '@/components/elements/whitelabel-banner'

export default {
	name: 'SlideGoogleAuth',

	components: {
		WhitelabelBanner,
		SimpleConnectForm,
		DefaultConnectForm,
	},

	data() {
		return {}
	},

	computed: {
		/**
		 * Check if we can show simple connect form.
		 *
		 * If it is a subsite and Google account is setup in
		 * network level, we can show a simple connect form.
		 *
		 * @since 3.2.4
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
				this.isMultisite() &&
				this.isSubsite() &&
				netWorkSetup &&
				netWorkLoggedIn &&
				'api' === netWorkLoginMethod
			)
		},
	},
}
</script>
