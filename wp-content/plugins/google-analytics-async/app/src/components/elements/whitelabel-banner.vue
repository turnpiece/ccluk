<template>
	<figure class="sui-box-banner" aria-hidden="true" v-if="canShow">
		<image-tag :src="src" :alt="alt" :class="imgClass" />
	</figure>
</template>

<script>
import ImageTag from '@/components/elements/image-tag'

export default {
	name: 'WhitelabelBanner',

	components: { ImageTag },

	props: {
		src: {
			type: String,
			default: '',
		},
		alt: {
			type: String,
			default: '',
		},
		imgClass: {
			type: String,
			default: '',
		},
	},

	computed: {
		/**
		 * Check if we can show the branding image.
		 *
		 * @since 1.8.0
		 *
		 * @returns {boolean}
		 */
		canShow() {
			return !this.$vars.whitelabel.hide_branding
		},
	},
}
</script>
