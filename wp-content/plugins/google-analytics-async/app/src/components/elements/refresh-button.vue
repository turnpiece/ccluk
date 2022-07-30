<template>
	<button
		type="button"
		class="sui-button sui-button-ghost sui-tooltip"
		aria-live="polite"
		:class="loadingClass"
		:data-tooltip="$i18n.tooltip.refresh"
		@click="refreshData"
	>
		<span class="sui-button-text-default">
			<i class="sui-icon-refresh" aria-hidden="true"></i>
			{{ $i18n.button.refresh }}
		</span>
		<span class="sui-button-text-onload">
			<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
			{{ $i18n.button.refreshing }}
		</span>
	</button>
</template>

<script>
import { restGet } from '@/helpers/api'

export default {
	name: 'RefreshButton',

	props: {
		notice: {
			type: Boolean,
			default: true,
		},
		loading: {
			type: Boolean,
			default: false,
		},
	},

	computed: {
		/**
		 * Get button loading class if processing.
		 *
		 * @since 3.3.0
		 *
		 * @returns {*}
		 */
		loadingClass() {
			return {
				'sui-button-onload-text': this.loading,
			}
		},
	},

	methods: {
		/**
		 * Process the refresh button click.
		 *
		 * Use the API to clear the stats cache so the stats
		 * will be refreshed automatically on next request.
		 *
		 * @since 3.2.4
		 *
		 * @returns {void}
		 */
		refreshData() {
			// For 2 way sync.
			this.$emit('update:loading', true)

			restGet({
				path: 'v1/actions',
				params: {
					action: 'refresh',
					network: this.isNetwork() ? 1 : 0,
				},
			}).then((response) => {
				// For 2 way sync.
				this.$emit('update:loading', false)

				// Emit refresh event.
				this.$emit('refreshed')

				// Show the notice.
				this.showNotice(response)
			})
		},

		/**
		 * Show notice if required.
		 *
		 * If the notice property is set to false,
		 * do not show the notice.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		showNotice(response) {
			if (!this.notice) {
				return
			}

			// Show the success notice.
			if (response.success && response.data) {
				this.$root.$emit('showTopNotice', {
					message: response.data.message,
				})
			} else if (response.data) {
				// Show the error notice.
				this.$root.$emit('showTopNotice', {
					type: 'error',
					message: response.data.message,
				})
			}
		},
	},
}
</script>
