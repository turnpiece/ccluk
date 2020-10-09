<template>
	<sui-field
		:label="label"
		:labelType="labelClass"
		:topDescription="topDescription"
		:btmDescription="btmDescription"
	>
		<sui-checkbox
			v-for="(option, index) in options"
			:id="id + '-option-' + index"
			:label="option"
			:type="checkClass"
			:key="index"
		/>
	</sui-field>
</template>

<script>
import SuiField from './sui-field'
import SuiCheckbox from './sui-checkbox'

export default {
	name: 'SuiCheckboxes',

	components: {
		SuiField,
		SuiCheckbox,
	},

	props: {
		id: {
			type: String,
			required: true,
		},
		options: {
			type: Array,
			default: null,
		},
		checkClass: String,
		label: String,
		labelClass: String,
		topDescription: String,
		btmDescription: String,
	},
}
</script>
