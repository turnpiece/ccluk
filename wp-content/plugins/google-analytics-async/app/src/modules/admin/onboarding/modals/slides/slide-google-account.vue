<template>
	<div
		:id="`${$parent.modal}-google-account`"
		class="sui-modal-slide sui-active sui-loaded"
		data-modal-size="md"
	>
		<div class="sui-box">
			<div
				class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60 sui-spacing-top--30 sui-spacing-left--60 sui-spacing-right--60"
			>
				<whitelabel-banner
					src="onboarding/setup.png"
					:alt="$i18n.label.google_account_setup"
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
					{{ $i18n.label.google_account_setup }}
				</h3>
				<p class="sui-description">
					{{ $i18n.desc.google_connect_success }}
				</p>
			</div>
			<div
				class="sui-box-body sui-spacing-left--60 sui-spacing-right--60"
			>
				<!-- Google account selection -->
				<account-profiles />

				<!-- Role permissions -->
				<account-roles />
			</div>
			<div
				class="sui-box-footer sui-flatten sui-content-center sui-spacing-bottom--50"
			>
				<button
					role="button"
					class="sui-button"
					data-modal-slide-intro="next"
					:data-modal-slide="`${$parent.modal}-admin-tracking`"
				>
					{{ $i18n.dialog.continue }}
				</button>
			</div>
		</div>
	</div>
</template>

<script>
import AccountRoles from './google/account-roles'
import AccountProfiles from './google/account-profiles'
import WhitelabelBanner from '@/components/elements/whitelabel-banner'

export default {
	name: 'SlideGoogleAccount',

	components: {
		AccountRoles,
		AccountProfiles,
		WhitelabelBanner,
	},
}
</script>
