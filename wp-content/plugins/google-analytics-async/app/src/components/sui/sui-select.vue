<template>
	<select
		:id="id"
		:class="selectClass"
		:aria-labelledby="id + '-label'"
		ref="select"
		v-model="selected"
	>
		<option
			v-for="(label, key) in options"
			:value="key"
			:key="key"
			@change="handleChange"
		>
			{{ label }}
		</option>
	</select>
</template>

<script>
export default {
	name: 'SuiSelect',

	props: {
		id: {
			type: String,
			required: true,
		},
		value: {
			type: String | Number,
			default: null,
		},
		options: {
			type: Object,
			required: true,
		},
		isSmall: {
			type: Boolean,
			default: false,
		},
	},

	mounted() {
		// Initialize select.
		this.select = jQuery(this.$refs.select)

		// Sui Select.
		SUI.suiSelect(this.select)

		// Handle change event.
		this.select.on('change', this.handleChange)
	},

	data() {
		return {
			select: null,
			selected: this.value,
		}
	},

	computed: {
		/**
		 * Get select class based on the props.
		 *
		 * @since 1.8.0
		 *
		 * @returns {object}
		 */
		selectClass() {
			return {
				//'sui-select-inline': false,
				'sui-select-sm': this.isSmall,
			}
		},
	},

	methods: {
		/**
		 * Handle change event of select element.
		 *
		 * @since 1.8.0
		 */
		handleChange() {
			// Update model.
			this.selected = this.select.val()
			// Emit input event.
			this.$emit('input', this.select.val())
		},
	},
}
</script>
