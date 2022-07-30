<template>
	<select v-model="selected" :id="id" :class="selectClass" ref="suiSelect" :data-width="width">
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
		id: String,
		value: String | Number,
		options: Object | Array,
		isSmall: Boolean,
		width: Boolean | String
	},

	mounted() {
		// Initialize select.
		this.select = jQuery(this.$refs.suiSelect)

		// Sui Select.
		this.select.SUIselect2({
			minimumResultsForSearch: Infinity,
		})

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
		 * @since 3.2.0
		 *
		 * @returns {object}
		 */
		selectClass() {
			return {
				'sui-select': true,
				'sui-select-sm': this.isSmall,
			}
		},
	},

	methods: {
		/**
		 * Handle change event of select element.
		 *
		 * @since 3.2.0
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
