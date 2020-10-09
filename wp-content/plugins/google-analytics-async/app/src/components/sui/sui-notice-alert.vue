<template>
	<div role="alert" aria-live="assertive" class="sui-notice" :id="id"></div>
</template>

<script>
export default {
	name: 'SuiNoticeAlert',

	props: {
		id: {
			type: String,
			required: true,
		},
		message: {
			type: String,
			required: true,
		},
		type: {
			type: String,
			default: 'success',
		},
		icon: {
			type: String,
			default: 'info',
		},
		autoClose: {
			type: Boolean,
			default: false,
		},
		timeout: {
			type: Number,
			default: 5000,
		},
		show: {
			type: Boolean,
			default: false,
		},
	},

	watch: {
		/**
		 * Get notice classes based of different conditions.
		 *
		 * @since 3.2.3
		 *
		 * @return {*}
		 */
		show(current) {
			if (current) {
				this.showNotice()
			} else {
				this.hideNotice()
			}
		},
	},

	computed: {
		/**
		 * Get the notice options based on the props.
		 *
		 * @since 3.3.0
		 *
		 * @return {*}
		 */
		noticeOptions() {
			return {
				type: this.type,
				icon: this.icon,
				autoclose: {
					show: this.autoClose,
					timeout: this.timeout,
				},
			}
		},

		/**
		 * Get the notice content.
		 *
		 * Currently we add the <p> tag automatically.
		 *
		 * @since 3.3.0
		 *
		 * @return {string}
		 */
		noticeContent() {
			return '<p>' + this.message + '</p>'
		},
	},

	methods: {
		/**
		 * Show the inline alert notice using SUI.
		 *
		 * @since 3.3.0
		 */
		showNotice() {
			SUI.openNotice(this.id, this.noticeContent, this.noticeOptions)
		},

		/**
		 * Hide the inline alert notice using SUI.
		 *
		 * @since 3.3.0
		 */
		hideNotice() {
			SUI.closeNotice(this.id)
		},
	},
}
</script>
