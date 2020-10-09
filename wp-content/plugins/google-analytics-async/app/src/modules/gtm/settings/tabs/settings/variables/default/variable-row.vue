<template>
	<tr>
		<td class="sui-table-item-title">
			<div class="sui-form-field">
				<label
					:for="`beehive-gtm-${id}-variable-input`"
					class="sui-toggle sui-toggle-sm"
				>
					<input
						type="checkbox"
						:id="`beehive-gtm-${id}-variable-input`"
						:aria-labelledby="`beehive-gtm-${id}-variable-input-label`"
						:value="id"
						v-model="enabled"
					/>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span
						:id="`beehive-gtm-${id}-variable-input-label`"
						class="sui-toggle-label"
					>
						{{ title }}
					</span>
				</label>
			</div>
		</td>
		<td>
			<input
				class="sui-form-control sui-form-control-sm"
				v-model="variable"
				:disabled="!enabled"
			/>
		</td>
	</tr>
</template>

<script>
export default {
	name: 'VariableRow',

	props: {
		id: String,
		title: String,
	},

	computed: {
		/**
		 * Computed object to get the enabled status.
		 *
		 * @since 3.3.0
		 *
		 * @returns {boolean}
		 */
		enabled: {
			get() {
				return this.getOption('enabled', 'gtm', [])
			},
			set(value) {
				this.setOption('enabled', 'gtm', value)
			},
		},

		/**
		 * Computed object to get the variable value.
		 *
		 * @since 3.3.0
		 *
		 * @returns {string}
		 */
		variable: {
			get() {
				let vars = this.getOption('variables', 'gtm', [])

				return vars[this.id] || ''
			},
			set(value) {
				let vars = this.getOption('variables', 'gtm', [])
				vars[this.id] = value

				this.setOption('variables', 'gtm', vars)
			},
		},
	},
}
</script>
