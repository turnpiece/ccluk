<template>
	<div class="sui-box">
		<box-header :title="$i18n.label.settings" />
		<div class="sui-box-body">
			<p>{{ $i18n.desc.settings_desc }}</p>

			<sui-notice type="warning" v-if="noAccount">
				<p
					v-html="
						sprintf(
							$i18n.notice.container_id_missing,
							$vars.urls.gtm_account
						)
					"
				></p>
			</sui-notice>

			<div class="sui-box-settings-row">
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						{{ $i18n.label.variables }}
					</span>
					<span class="sui-description">
						{{ $i18n.desc.variables }}
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<!-- Display variables -->
					<variables />
				</div>
			</div>
		</div>
		<footer-area @submit="$emit('submit')" />

		<!-- Deactivate confirmation modal -->
		<deactivate-modal v-if="isActive" />
	</div>
</template>

<script>
import Variables from './settings/variables'
import SuiNotice from '@/components/sui/sui-notice'
import FooterArea from './../components/footer-area'
import BoxHeader from '@/components/elements/box-header'
import DeactivateModal from './../components/modals/deactivate'

export default {
	name: 'SettingsBox',

	components: {
		SuiNotice,
		Variables,
		BoxHeader,
		FooterArea,
		DeactivateModal,
	},

	computed: {
		/**
		 * Check if no container ID is setup.
		 *
		 * @since 3.3.0
		 *
		 * @return {boolean}
		 */
		noAccount() {
			return !this.getOption('container', 'gtm', '')
		},

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
}
</script>
