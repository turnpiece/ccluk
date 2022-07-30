export default {
	data() {
		return {
			modal: 'beehive-modal-' + Date.now(),
			closeFocus: null,
			openFocus: null,
			hasOverlayMask: true,
		}
	},

	mounted() {
		// Initialize modal.
		SUI.modalDialog()
	},

	methods: {
		/**
		 * Open the current modal.
		 *
		 * Open the SUI initialized modal using the modal ID.
		 *
		 * @since 3.3.0
		 */
		openModal() {
			SUI.openModal(
				this.modal,
				this.closeFocus,
				this.openFocus,
				this.hasOverlayMask
			)

			// Emit modal open event.
			this.$emit('modal:open', this.modal)
		},

		/**
		 * Close the current modal.
		 *
		 * SUI will close the active modal.
		 *
		 * @since 3.3.0
		 */
		closeModal() {
			SUI.closeModal()

			// Emit modal close event.
			this.$emit('modal:close', this.modal)

			// Temporary fix to remove non scrollable class from the html.
			document.body.parentNode.classList.remove('sui-has-modal')
		},
	},
}
