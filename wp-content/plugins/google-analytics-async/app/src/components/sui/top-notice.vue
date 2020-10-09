<template>
	<div role="alert" :id="id" :class="noticeClass" aria-live="assertive"></div>
</template>

<script>
export default {
	name: 'TopNotice',

	data() {
		return {
			id: 'beehive-top-notice',
			message: '',
			options: {
				type: 'success',
				autoclose: {
					show: true,
					timeout: 5000,
				},
				dismiss: {
					show: false,
					label: this.$i18n.notice.dismiss,
				},
			},
		}
	},

	mounted() {
		SUI.notice()

		this.$root.$on('showTopNotice', (data) => {
			// Setup notice options.
			this.setupNotice(data)

			// Now open the notice.
			SUI.openNotice(this.id, this.message, this.options)
		})
	},

	computed: {
		noticeClass() {
			return {
				'sui-notice': true,
				'sui-notice-info': this.type === 'info',
				'sui-notice-error': this.type === 'error',
				'sui-notice-success': this.type === 'success',
				'sui-notice-warning': this.type === 'warning',
			}
		},
	},

	methods: {
		getMessage(data) {
			if (data.message) {
				return '<p>' + data.message + '</p>'
			} else {
				return ''
			}
		},

		setupNotice(data) {
			// Notice type.
			this.type = data.type || 'success'

			// Set type.
			this.options.type = this.type

			// Is message dismissible.
			this.options.dismiss.show = data.dismiss || false

			// If not dismissible, auto close.
			this.options.autoclose.show = !this.options.dismiss.show

			// Set notice text.
			this.message = this.getMessage(data)
		},
	},
}
</script>
