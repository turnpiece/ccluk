<template>
	<div class="sui-box-footer">
		<!-- Left actions area -->
		<slot name="left"></slot>
		<!-- left actions end -->

		<div class="sui-actions-right">
			<button
				type="button"
				aria-live="polite"
				class="sui-button sui-button-blue"
				:disabled="disableSubmit"
				:class="loadingClass"
				@click="$emit('submit')"
			>
				<span class="sui-button-text-default">
					<i class="sui-icon-save" aria-hidden="true"></i>
					{{ getSaveText }}
				</span>
				<span class="sui-button-text-onload">
					<i
						class="sui-icon-loader sui-loading"
						aria-hidden="true"
					></i>
					{{ getSavingText }}
				</span>
			</button>

			<!-- Right actions area -->
			<slot name="right"></slot>
			<!-- right actions end -->
		</div>
	</div>
</template>

<script>
export default {
	name: 'BoxFooter',

	props: {
		saveText: {
			type: String,
		},
		savingText: {
			type: String,
		},
		processing: {
			type: Boolean,
			default: false,
		},
		disableSubmit: {
			type: Boolean,
			default: false,
		},
	},

	computed: {
		/**
		 * Get the button loading class if request is processing.
		 *
		 * @since 3.2.0
		 *
		 * @returns {*}
		 */
		loadingClass() {
			return {
				'sui-button-onload-text': this.processing,
			}
		},

		/**
		 * Get the submit button default text.
		 *
		 * @since 3.2.0
		 *
		 * @returns {string}
		 */
		getSaveText() {
			return this.saveText || this.$i18n.button.save_changes
		},

		/**
		 * Get the submit button loading state text.
		 *
		 * @since 3.2.0
		 *
		 * @returns {string}
		 */
		getSavingText() {
			return this.savingText || this.$i18n.button.saving_changes
		},
	},
}
</script>
