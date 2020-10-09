<template>
	<div class="sui-form-field">
		<label :for="`beehive-gtm-${id}-integration`" class="sui-toggle">
			<input
				type="checkbox"
				:id="`beehive-gtm-${id}-integration`"
				:aria-labelledby="`beehive-gtm-${id}-integration-label`"
				:aria-describedby="`beehive-gtm-${id}-integration-desc`"
				:value="id"
				:disabled="!active"
				v-model="enabled"
			/>
			<span class="sui-toggle-slider" aria-hidden="true"></span>
			<span
				:id="`beehive-gtm-${id}-integration-label`"
				class="sui-toggle-label"
			>
				{{ title }}
			</span>
			<span
				class="sui-description"
				:id="`beehive-gtm-${id}-integration-desc`"
				v-html="desc"
			>
			</span>
		</label>
	</div>
</template>

<script>
export default {
	name: 'IntegrationItem',

	props: {
		id: String,
		title: String,
		desc: String,
		active: {
			type: Boolean,
			default: true,
		},
	},

	created() {
		// Deactivate if integration is not active.
		if (!this.active) {
			this.deactivate()
		}
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
			set(enabled) {
				this.setOption('enabled', 'gtm', enabled)
			},
		},
	},

	methods: {
		/**
		 * Deactivate the current integration.
		 *
		 * @since 3.3.0
		 *
		 * @return {*}
		 */
		deactivate() {
			// Get the status.
			let enabled = this.enabled

			// Make sure it's excluded from enabled list.
			this.enabled = enabled.filter((id) => id !== this.id)
		},
	},
}
</script>
