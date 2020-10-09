<template>
	<div class="sui-modal sui-modal-sm">
		<div
			role="dialog"
			class="sui-modal-content sui-content-fade-out"
			aria-modal="true"
			aria-labelledby="beehive-gtm-deactivate-confirm-title"
			aria-describedby="beehive-gtm-deactivate-confirm-description"
			:id="modal"
		>
			<div class="sui-box">
				<div
					class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60"
				>
					<button
						class="sui-button-icon sui-button-float--right"
						data-modal-close
					>
						<i class="sui-icon-close sui-md" aria-hidden="true"></i>
						<span class="sui-screen-reader-text">
							{{ $i18n.dialog.close }}
						</span>
					</button>
					<h3
						id="beehive-gtm-deactivate-confirm-title"
						class="sui-box-title sui-lg"
					>
						{{ $i18n.title.deactivate }}
					</h3>
					<p
						id="beehive-gtm-deactivate-confirm-description"
						class="sui-description"
					>
						{{ $i18n.desc.deactivate }}
					</p>
				</div>
				<div class="sui-box-footer sui-flatten sui-content-center">
					<button
						class="sui-button sui-button-ghost"
						data-modal-close
					>
						{{ $i18n.dialog.cancel }}
					</button>
					<button
						type="button"
						class="sui-button sui-button-red"
						aria-live="polite"
						:class="buttonClass"
						@click="deactivate"
					>
						<span class="sui-button-text-default">
							<i
								class="sui-icon-power-on-off"
								aria-hidden="true"
							></i>
							{{ $i18n.button.deactivate }}
						</span>
						<span class="sui-button-text-onload">
							<i
								class="sui-icon-loader sui-loading"
								aria-hidden="true"
							></i>
							{{ $i18n.button.deactivating }}
						</span>
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import Modal from '@/components/mixins/modal'

export default {
	name: 'DeactivateModal',

	mixins: [Modal],

	data() {
		return {
			modal: 'beehive-gtm-deactivate-confirm',
			processing: false,
		}
	},

	computed: {
		/**
		 * Get button class for the deactivate button.
		 *
		 * @since 3.3.0
		 *
		 * @returns {*}
		 */
		buttonClass() {
			return {
				'sui-button-ghost': !this.processing,
				'sui-button-onload-text': this.processing,
			}
		},
	},

	methods: {
		/**
		 * Deactivate the GTM module.
		 *
		 * Set the flag and save the settings.
		 *
		 * @since 3.3.0
		 *
		 * @return {Promise<void>}
		 */
		async deactivate() {
			this.processing = true

			// Make sure to close the modal.
			this.closeModal()

			// Set the flag.
			this.setOption('active', 'gtm', false)
			// Save options.
			await this.saveOptions()

			this.processing = false
		},
	},
}
</script>
