<template>
	<div class="sui-box">
		<box-header :title="$i18n.label.account" />
		<div class="sui-box-body">
			<div class="sui-box-settings-row">
				<div class="sui-box-settings-col-2">
					<p>{{ $i18n.desc.account_desc }}</p>
					<sui-notice
						type="info"
						v-if="showNotice && duplicateContainer"
					>
						<p>{{ $i18n.notice.duplicate_connected }}</p>
					</sui-notice>
					<sui-notice type="info" v-else-if="showNotice">
						<p>{{ $i18n.notice.account_connected }}</p>
					</sui-notice>
				</div>
			</div>

			<div class="sui-box-settings-row">
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						{{ $i18n.label.container_id }}
					</span>
					<span class="sui-description">
						{{ $i18n.desc.container_id }}
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<!-- Container ID -->
					<container-id :error="error" />
					<sui-notice-alert
						id="beehive-gtm-invalid-gtm-id-error-notice"
						type="error"
						:show="error"
						:message="$i18n.notice.gtm_invalid_id"
					/>
				</div>
			</div>
		</div>
		<footer-area @submit="submit" />

		<!-- Deactivate confirmation modal -->
		<deactivate-modal v-if="isActive" />
	</div>
</template>

<script>
import ContainerId from './account/container-id'
import SuiNotice from '@/components/sui/sui-notice'
import SuiSelect from '@/components/sui/sui-select'
import FooterArea from './../components/footer-area'
import BoxHeader from '@/components/elements/box-header'
import SuiNoticeAlert from '@/components/sui/sui-notice-alert'
import DeactivateModal from './../components/modals/deactivate'

export default {
	name: 'AccountBox',

	components: {
		SuiNotice,
		SuiSelect,
		BoxHeader,
		FooterArea,
		ContainerId,
		SuiNoticeAlert,
		DeactivateModal,
	},

	data() {
		return {
			error: false,
			accounts: {
				test: 'Test',
				test2: 'Test2',
			},
		}
	},

	computed: {
		/**
		 * Check if container ID is setup.
		 *
		 * @since 3.3.0
		 *
		 * @return {boolean}
		 */
		showNotice() {
			return this.isValid && !!this.getOption('container', 'gtm', '')
		},

		/**
		 * Check if container ID is duplicate with network.
		 *
		 * @since 3.3.0
		 *
		 * @return {boolean}
		 */
		duplicateContainer() {
			// Only when subsite.
			if (this.isSubsite()) {
				let subsite = this.getOption('container', 'gtm', '')
				let network = this.getOption('container', 'gtm', '', true)

				return subsite === network
			} else {
				return false
			}
		},

		/**
		 * Validate the current container ID.
		 *
		 * Check if the given container ID matches the GTM
		 * container ID format.
		 *
		 * @since 3.3.0
		 *
		 * @return {boolean}
		 */
		isValid() {
			let id = this.getOption('container', 'gtm', '')

			return /^GTM-[A-Z0-9]{1,7}$/i.test(id) || !id
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

	methods: {
		/**
		 * Process the submit event.
		 *
		 * Process only if the validation is success.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		submit() {
			this.error = !this.isValid

			if (this.isValid) {
				this.$emit('submit')
			}
		},
	},
}
</script>
