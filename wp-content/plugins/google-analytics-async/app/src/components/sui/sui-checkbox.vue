<template>
	<label :for="id" class="sui-checkbox" :class="checkboxClass">
		<input
			type="checkbox"
			:id="id"
			:aria-labelledby="id + '-label'"
			:disabled="disabled"
			v-model="model"
			@change="handleChange"
		/>
		<span aria-hidden="true"></span>
		<span :for="id" :id="id + '-label'">{{ label }}</span>
	</label>
</template>

<script>
export default {
	name: 'SuiCheckbox',

	props: {
		id: {
			type: String,
			required: true,
		},
		label: {
			type: String,
			required: true,
		},
		type: {
			validator: function (value) {
				return ['small', 'stacked', 'stacked-sm'].indexOf(value) !== -1
			},
			default: null,
		},
		disabled: {
			type: Boolean,
			default: false,
		},
		value: Boolean,
	},

	data() {
		return {
			model: this.value,
		}
	},

	computed: {
		/**
		 * Get the classes for checkbox.
		 *
		 * @since 3.2.3
		 *
		 * @return {*}
		 */
		checkboxClass() {
			return {
				'sui-checkbox-sm': this.type === 'small',
				'sui-checkbox-stacked': this.type === 'stacked',
				'sui-checkbox-stacked sui-checkbox-sm':
					this.type === 'stacked-sm',
			}
		},
	},

	methods: {
		/**
		 * Emit the input event.
		 *
		 * @since 3.2.3
		 *
		 * @returns {void}
		 */
		handleChange() {
			this.$emit('input', this.model)
		},
	},
}
</script>
