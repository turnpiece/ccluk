<template>
	<img :src="get1X" :srcset="getSrcSet" :alt="alt" aria-hidden="true" />
</template>

<script>
import { imageUrl } from '@/helpers/utils'

export default {
	name: 'ImageTag',

	props: {
		src: {
			type: String,
			required: true,
		},
		srcset: {
			type: Boolean,
			default: true,
		},
		alt: {
			type: String,
			default: '',
		},
		fullPath: {
			type: Boolean,
			default: false,
		},
	},

	computed: {
		/**
		 * Get the default image url.
		 *
		 * @since 1.8.0
		 *
		 * @returns {string}
		 */
		getSrcSet() {
			return this.srcset
				? this.get1X + ' 1x, ' + this.get2X + ' 2x'
				: false
		},

		/**
		 * Get the default image url.
		 *
		 * @since 1.8.0
		 *
		 * @returns {string}
		 */
		get1X() {
			return this.fullPath ? this.src : imageUrl(this.src)
		},

		/**
		 * Get the 2x image path.
		 *
		 * @since 1.8.0
		 *
		 * @returns {string|boolean}
		 */
		get2X() {
			if (this.srcset) {
				let path = this.src.replace(/(\.[\w\d_-]+)$/i, '@2x$1')

				return this.fullPath ? path : imageUrl(path)
			} else {
				return false
			}
		},
	},
}
</script>
