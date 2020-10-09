<template>
	<div class="sui-border-frame">
		<div class="sui-form-field">
			<label :for="id" class="sui-checkbox sui-checkbox-sm">
				<input
					type="checkbox"
					v-model="override"
					:id="id"
					:aria-labelledby="`${id}-label`"
					@change="handleInput"
				/>
				<span aria-hidden="true"></span>
				<span :id="`${id}-label`">{{ label }}</span>
			</label>
		</div>
	</div>
</template>

<script>
export default {
	name: 'OverrideSettings',

	props: {
		id: {
			type: String,
			required: true,
		},
		label: {
			type: String,
			required: true,
		},
		value: {
			type: Boolean,
			required: true,
		},
	},

	data() {
		return {
			override: this.value,
		}
	},

	methods: {
		/**
		 * Handle input event on the option.
		 *
		 * @since 3.2.5
		 *
		 * @returns {void}
		 */
		handleInput() {
			this.$emit('input', this.override)
		},
	},
}
</script>
