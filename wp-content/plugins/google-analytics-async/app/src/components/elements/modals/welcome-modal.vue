<template>
	<div class="sui-modal sui-modal-md">
		<div
			role="dialog"
			class="sui-modal-content sui-content-fade-out"
			aria-modal="true"
			aria-labelledby="beehive-welcome-modal-title"
			aria-describedby="beehive-welcome-modal-desc"
			:id="modal"
		>
			<div class="sui-box">
				<div
					class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60 sui-spacing-left--60 sui-spacing-right--60"
				>
					<whitelabel-banner
						src="welcome/ga4.png"
						:alt="$i18n.title.new_welcome_title"
					/>

					<button
						class="sui-button-icon sui-button-float--right"
						@click="dismiss"
					>
						<i class="sui-icon-close sui-md" aria-hidden="true"></i>
						<span class="sui-screen-reader-text">
							{{ $i18n.dialog.close }}
						</span>
					</button>
					<h3
						id="beehive-welcome-modal-title"
						class="sui-box-title sui-lg"
					>
						{{ $i18n.title.new_welcome_title }}
					</h3>
					<p id="beehive-welcome-modal-desc" class="sui-description">
						{{ $i18n.desc.new_welcome_desc }}
					</p>
				</div>

				<div class="sui-box-footer sui-flatten sui-content-center">
					<a
						:href="$vars.urls.ga_account"
						type="button"
						class="sui-button sui-button-blue"
						aria-live="polite"
						@click="dismiss"
					>
						<span class="sui-icon-wrench-tool" aria-hidden="true"></span>
						{{ $i18n.button.setup_ga4 }}
					</a>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import Modal from '@/components/mixins/modal'
import WhitelabelBanner from '@/components/elements/whitelabel-banner'

export default {
	name: 'WelcomeModal',

	mixins: [Modal],

	components: { WhitelabelBanner },

	data() {
		return {
			modal: 'beehive-welcome-modal',
			closeFocus: 'beehive-wrap',
		}
	},

	mounted() {
		// Open modal.
		this.openModal()
	},

	methods: {
		/**
		 * Dismiss the welcome modal and set the flag.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		dismiss() {
			// Close the modal.
			this.closeModal()

			// Dismiss flag.
			this.setOption('show_welcome', 'misc', 0)

			// Save options.
			this.saveOptions()
		},
	},
}
</script>
