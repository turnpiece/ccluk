<template>
	<div class="sui-box" :class="getLoadingClass">
		<div class="sui-box-header" v-if="title">
			<h2 class="sui-box-title">
				<i
					:class="`sui-icon-${titleIcon}`"
					aria-hidden="true"
					v-if="titleIcon"
				></i>
				{{ title }}
			</h2>
			<div class="sui-actions-left" v-if="hasLeftActions">
				<slot name="headerLeft"></slot>
			</div>
			<div class="sui-actions-right" v-if="hasRightActions">
				<slot name="headerRight"></slot>
			</div>
		</div>
		<div :class="getBodyClass" v-if="hasBodySlot">
			<slot name="body"></slot>
		</div>
		<slot name="outside"></slot>
		<div class="sui-box-footer" v-if="hasFooterSlot">
			<slot name="footer"></slot>
		</div>
		<img
			class="sui-image sui-image-center"
			aria-hidden="true"
			v-if="image1x"
			:src="image1x"
			:srcset="getSrcSet"
			:alt="imageAlt"
		/>
	</div>
</template>

<script>
export default {
	name: 'SuiBox',

	props: {
		title: {
			type: String,
			required: false,
		},

		titleIcon: {
			type: String,
			default: '',
		},

		bodyClass: {
			type: String,
			default: '',
		},

		image1x: {
			type: String,
			default: '',
		},

		image2x: {
			type: String,
			default: '',
		},

		imageAlt: {
			type: String,
			default: '',
		},

		loading: {
			type: Boolean,
			default: false,
		},
	},

	computed: {
		/**
		 * Check if left actions slot is set.
		 *
		 * @since 3.2.3
		 *
		 * @return {boolean}
		 */
		hasLeftActions() {
			return !!this.$slots.headerLeft
		},

		/**
		 * Check if the right actions slot is set.
		 *
		 * @since 3.2.3
		 *
		 * @return {boolean}
		 */
		hasRightActions() {
			return !!this.$slots.headerRight
		},

		/**
		 * Check if the body slot is set.
		 *
		 * @since 3.2.3
		 *
		 * @return {boolean}
		 */
		hasBodySlot() {
			return !!this.$slots.body
		},

		/**
		 * Check if the footer slot is set.
		 *
		 * @since 3.2.3
		 *
		 * @return {boolean}
		 */
		hasFooterSlot() {
			return !!this.$slots.footer
		},

		/**
		 * Get the class object for the box.
		 *
		 * If the data is being processed, add loading class.
		 *
		 * @since 3.2.3
		 *
		 * @return {*}
		 */
		getLoadingClass() {
			return {
				'beehive-loading': this.loading,
			}
		},

		/**
		 * Get the class object for the body.
		 *
		 * @since 3.2.3
		 *
		 * @return {*}
		 */
		getBodyClass() {
			return {
				'sui-box-body': true,
				[this.bodyClass]: this.bodyClass,
			}
		},

		/**
		 * Get the image srcset tag value.
		 *
		 * If 2x image is found, get the srcset value.
		 *
		 * @since 3.2.3
		 *
		 * @return {string}
		 */
		getSrcSet() {
			let tag = ''

			if (this.image1x && this.image2x) {
				tag = this.image1x + ' 1x, ' + this.image2x + ' 2x'
			}

			return tag
		},
	},
}
</script>
