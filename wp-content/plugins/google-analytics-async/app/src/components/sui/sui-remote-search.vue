<template>
	<select
		class="sui-select"
		data-theme="search"
		multiple="multiple"
		ref="select"
		:id="id"
		:aria-labelledby="labelId"
		:aria-describedby="descId"
		:disabled="disabled"
	></select>
</template>

<script>
export default {
	name: 'SuiRemoteSearch',

	props: {
		id: {
			type: String,
			required: true,
		},
		value: {
			type: String | Number,
			required: true,
		},
		labelId: {
			type: String,
			required: false,
		},
		descId: {
			type: String,
			required: false,
		},
		placeholder: {
			type: String,
			required: false,
		},
		disabled: {
			type: Boolean,
			default: false,
		},
		ajax: {
			type: Object,
			required: true,
		},
		parentElement: {
			type: String,
			required: false,
		},
	},

	data() {
		return {
			select2: null,
		}
	},

	mounted() {
		// Get the select2 element.
		this.select2 = jQuery(this.$refs.select)

		// Init select2.
		this.initSelect2()
	},

	watch: {
		// On value change trigger change.
		value(value) {
			this.select2.val(value).trigger('change')
		},
	},

	destroyed() {
		// Destroy select2.
		this.select2.off().SUIselect2('destroy')
	},

	methods: {
		/**
		 * Initialize remote search select2.
		 *
		 * @since 3.2.4
		 *
		 * @returns {void}
		 */
		initSelect2() {
			const vm = this

			let settings = {
				minimumInputLength: 2,
				maximumSelectionLength: 1,
				dropdownCssClass: 'sui-search-dropdown',
				placeholder: this.placeholder,
				ajax: this.ajax,
				theme: 'search',
			}

			// If parent element id is set.
			if (this.parentElement) {
				settings['dropdownParent'] = jQuery('#' + this.parentElement)
			}

			this.select2
				.SUIselect2(settings)
				.on('change', function () {
					// Emit change event.
					vm.$emit('input', this.value)
				})
				.val(this.value) // Set value.
				.trigger('change') // Trigger change.
		},
	},
}
</script>
