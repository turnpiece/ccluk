<template>
	<select
		class="sui-select"
		:class="{ 'sui-select-sm': isSmall }"
		:id="id"
		:aria-labelledby="labelId"
		:aria-describedby="descriptionId"
		:disabled="disabled"
	></select>
</template>

<script>
export default {
	name: 'SuiSelect2',

	model: {
		event: 'change',
		prop: 'value',
	},

	props: {
		id: {
			type: String,
			required: true,
		},
		options: {
			type: Array,
			required: true,
		},
		value: {
			type: String | Array,
			required: true,
		},
		isSmall: {
			type: Boolean,
			default: false,
		},
		multiple: {
			type: Boolean,
			default: false,
		},
		disabled: {
			type: Boolean,
			default: false,
		},
		labelId: String,
		descriptionId: String,
		placeholder: String,
		parentElement: String,
	},

	data() {
		return {
			select2: null,
		}
	},

	mounted() {
		// Get select2 instance.
		this.select2 = jQuery(this.$el)

		// Initialize select2.
		this.initSelect2()
	},

	beforeDestroy() {
		// Destroy the select2.
		this.destroySelect2()
	},

	watch: {
		/**
		 * On value change the value of select2.
		 *
		 * @since 3.3.6
		 */
		value(value) {
			this.setValue(value)
		},

		/**
		 * On options data change, re-sync.
		 *
		 * @since 3.3.6
		 */
		options(options) {
			this.setOptions(options)
		},
	},

	computed: {
		/**
		 * Get placeholder text from props.
		 *
		 * @since 3.3.6
		 *
		 * @returns {string}
		 */
		getPlaceholder() {
			return this.placeholder || ''
		},

		/**
		 * Get the settings object.
		 *
		 * @since 3.3.6
		 *
		 * @returns {*}
		 */
		getSettings() {
			let settings = {
				placeholder: this.getPlaceholder,
				dropdownCssClass: 'sui-select-dropdown',
				multiple: this.multiple,
			}

			// If parent element id is set.
			if (this.parentElement) {
				settings['dropdownParent'] = jQuery('#' + this.parentElement)
			}

			return settings
		},
	},

	methods: {
		/**
		 * Initialize select2 element.
		 *
		 * Initialize select2 and setup events.
		 *
		 * @since 3.3.6
		 *
		 * @returns {void}
		 */
		initSelect2() {
			this.select2
				.SUIselect2({
					...this.getSettings,
					data: this.options,
				})
				.on('select2:select select2:unselect', () => {
					const selectValue = this.select2.val()

					// Emit a change event.
					this.$emit('change', selectValue)
				})

			// Update the value.
			this.setValue(this.value)
		},

		/**
		 * Set the select options data.
		 *
		 * @since 3.3.6
		 *
		 * @returns {void}
		 */
		setOptions(options) {
			// Empty existing options.
			this.select2.empty()

			// Re-init with new options.
			this.select2.SUIselect2({
				...this.getSettings,
				data: options,
			})

			// Update the value again.
			this.setValue(this.value)
		},

		/**
		 * Update the value of the component.
		 *
		 * @since 3.3.6
		 *
		 * @returns {void}
		 */
		setValue(value) {
			// If value is array.
			if (this.multiple) {
				if (value instanceof Array) {
					this.select2.val([...value])
				} else {
					// If empty.
					this.select2.val([value])
				}
			} else {
				// If single.
				this.select2.val(value)
			}

			// Trigger change event.
			this.select2.trigger('change')
		},

		/**
		 * Destroy current select2 instance.
		 *
		 * @since 3.3.6
		 *
		 * @returns {void}
		 */
		destroySelect2() {
			this.select2.off().SUIselect2('destroy')
		},
	},
}
</script>
