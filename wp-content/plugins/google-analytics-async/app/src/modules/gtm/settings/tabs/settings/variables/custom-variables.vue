<template>
	<fragment>
		<p
			class="sui-description"
			v-html="
				sprintf(
					$i18n.desc.custom_variables,
					'https://developers.google.com/tag-manager/reference'
				)
			"
		></p>
		<table class="sui-table">
			<thead>
				<tr>
					<th id="beehive-gtm-custom-variable-name-title">
						{{ $i18n.label.variable_name }}
					</th>
					<th id="beehive-gtm-custom-variable-value-title">
						{{ $i18n.label.value }}
					</th>
					<th id="beehive-gtm-custom-variable-name-remove"></th>
				</tr>
			</thead>
			<tbody>
				<variable-row
					v-for="(variable, index) in variables"
					:key="index"
					:id="index"
					@remove="removeVariable"
				/>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3">
						<button
							role="button"
							class="sui-button sui-button-ghost"
							:disabled="isLastEmpty"
							@click="addVariable"
						>
							<i class="sui-icon-plus" aria-hidden="true"></i>
							{{ $i18n.button.add_variable }}
						</button>
					</td>
				</tr>
			</tfoot>
		</table>
	</fragment>
</template>

<script>
import VariableRow from './custom/variable-row'

export default {
	name: 'CustomVariables',

	components: { VariableRow },

	computed: {
		/**
		 * Computed object to get the custom variables.
		 *
		 * @since 3.3.0
		 *
		 * @returns {string}
		 */
		variables: {
			get() {
				return this.getOption('custom', 'gtm', [])
			},
			set(vars) {
				this.setOption('custom', 'gtm', vars)
			},
		},

		/**
		 * Check if the custom variables are empty.
		 *
		 * @since 3.3.0
		 *
		 * @returns {boolean}
		 */
		isEmpty() {
			return this.variables.length <= 0
		},

		/**
		 * Check if the last item is empty.
		 *
		 * Both name and value of the variable should not be empty.
		 *
		 * @since 3.3.0
		 *
		 * @returns {boolean}
		 */
		isLastEmpty() {
			// Treat it as not empty if the list is empty.
			if (this.isEmpty) {
				return false
			}

			// Get the last item.
			let last = this.variables[this.variables.length - 1]

			// Check if name or value is empty.
			return last.name.length <= 0 || last.value.length <= 0
		},
	},

	methods: {
		/**
		 * Add new variable to the list.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		addVariable() {
			// Do not add the last one is not filled.
			if (this.isLastEmpty) {
				return
			}

			// Get the available variables.
			let vars = this.variables

			// Add an empty item.
			vars.push({
				name: '',
				value: '',
			})

			// Update the vuex.
			this.variables = vars
		},

		/**
		 * Remove a specific custom variable row from the list.
		 *
		 * @param {int} id Index of the item.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		removeVariable(id) {
			let vars = this.variables

			// Remove the item from the array.
			vars = vars.filter((value, index) => {
				return index !== id
			})

			// Update the list.
			this.setOption('custom', 'gtm', vars)
		},
	},
}
</script>
