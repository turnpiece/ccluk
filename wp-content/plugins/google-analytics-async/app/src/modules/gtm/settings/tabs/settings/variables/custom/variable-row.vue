<template>
	<tr>
		<td>
			<div class="sui-form-field">
				<input
					class="sui-form-control sui-form-control-sm"
					aria-labelledby="beehive-gtm-custom-variable-name-title"
					:placeholder="$i18n.label.name"
					:id="`beehive-gtm-${id}-custom-variable-name`"
					v-model="name"
				/>
			</div>
		</td>
		<td>
			<div class="sui-form-field">
				<input
					class="sui-form-control sui-form-control-sm"
					aria-labelledby="beehive-gtm-custom-variable-value-title"
					:placeholder="$i18n.label.value"
					:id="`beehive-gtm-${id}-custom-variable-value`"
					v-model="value"
				/>
			</div>
		</td>
		<td>
			<button
				role="button"
				class="sui-button-icon"
				:class="hoverClass"
				@mouseover="hover = true"
				@mouseleave="hover = false"
				@click="$emit('remove', id)"
			>
				<i class="sui-icon-cross-close" aria-hidden="true"></i>
				<span class="sui-screen-reader-text">
					{{ $i18n.button.remove_variable }}
				</span>
			</button>
		</td>
	</tr>
</template>

<script>
export default {
	name: 'VariableRow',

	props: {
		id: Number,
	},

	data() {
		return {
			hover: false,
		}
	},

	computed: {
		/**
		 * Colorize the button on hover.
		 *
		 * @since 3.3.0
		 *
		 * @returns {*}
		 */
		hoverClass() {
			return {
				'sui-button-red': this.hover,
			}
		},

		/**
		 * Computed object to get the variable name.
		 *
		 * @since 3.3.0
		 *
		 * @returns {string}
		 */
		name: {
			get() {
				let vars = this.getOption('custom', 'gtm', [])

				return vars[this.id].name || ''
			},
			set(name) {
				let vars = this.getOption('custom', 'gtm', [])
				vars[this.id].name = name

				this.setOption('custom', 'gtm', vars)
			},
		},

		/**
		 * Computed object to get the variable value.
		 *
		 * @since 3.3.0
		 *
		 * @returns {string}
		 */
		value: {
			get() {
				let vars = this.getOption('custom', 'gtm', [])

				return vars[this.id].value || ''
			},
			set(value) {
				let vars = this.getOption('custom', 'gtm', [])
				// Set the new value.
				vars[this.id].value = value

				this.setOption('custom', 'gtm', vars)
			},
		},
	},
}
</script>
