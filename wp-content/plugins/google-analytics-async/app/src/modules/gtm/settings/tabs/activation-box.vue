<template>
	<div class="sui-box sui-message sui-message-lg">
		<image-tag
			src="gtm/activate.png"
			class="sui-image"
			v-if="!$vars.whitelabel.hide_branding"
		/>

		<div class="sui-message-content">
			<p>{{ $i18n.desc.activate_gtm }}</p>

			<p>
				<button
					type="button"
					aria-live="polite"
					:class="btmClass"
					@click="activateGTM"
				>
					<span class="sui-button-text-default">
						{{ $i18n.button.activate }}
					</span>
					<span class="sui-button-text-onload">
						<i
							class="sui-icon-loader sui-loading"
							aria-hidden="true"
						></i>
						{{ $i18n.button.activating }}
					</span>
				</button>
			</p>
		</div>
	</div>
</template>

<script>
import ImageTag from '@/components/elements/image-tag'

export default {
	name: 'ActivationBox',

	components: { ImageTag },

	data() {
		return {
			processing: false,
		}
	},

	computed: {
		/**
		 * Get the class for the activation button.
		 *
		 * @since 3.3.0
		 *
		 * @returns {*}
		 */
		btmClass() {
			return {
				'sui-button': true,
				'sui-button-blue': true,
				'sui-button-onload-text': this.processing,
			}
		},
	},

	methods: {
		/**
		 * Activate the GTM integration.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		activateGTM() {
			this.processing = true

			// Set the flag.
			this.setOption('active', 'gtm', true)

			// Save options.
			this.saveOptions()
		},
	},
}
</script>
