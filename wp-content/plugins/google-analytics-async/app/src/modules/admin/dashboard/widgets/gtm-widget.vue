<template>
	<sui-box
		class="beehive-widget"
		titleIcon="gtm"
		:title="$i18n.title.gtm_box"
	>
		<template v-slot:body>
			<p>{{ $i18n.desc.gtm_box }}</p>

			<account-inactive v-if="!isActive" />
			<sui-notice type="warning" v-if="isActive && !isSetup">
				<p>{{ $i18n.notice.gtm_not_setup }}</p>
				<p>
					<a
						role="button"
						class="sui-button"
						:href="$vars.urls.gtm_account"
					>
						{{ $i18n.button.finish_setup }}
					</a>
				</p>
			</sui-notice>
		</template>
		<template v-slot:outside v-if="isActive && isSetup">
			<account-active />
		</template>
	</sui-box>
</template>

<script>
import SuiBox from '@/components/sui/sui-box'
import AccountActive from './gtm/account-active'
import SuiNotice from '@/components/sui/sui-notice'
import AccountInactive from './gtm/account-inactive'

export default {
	name: 'GtmWidget',

	props: ['stats', 'loading'],

	components: {
		SuiBox,
		SuiNotice,
		AccountActive,
		AccountInactive,
	},

	computed: {
		/**
		 * Check if the GTM module is active.
		 *
		 * @since 3.3.0
		 *
		 * @returns {boolean}
		 */
		isActive() {
			return this.getOption('active', 'gtm')
		},

		/**
		 * Check if the container ID is setup.
		 *
		 * @since 3.3.0
		 *
		 * @returns {boolean}
		 */
		isSetup() {
			return !!this.getOption('container', 'gtm')
		},
	},
}
</script>
