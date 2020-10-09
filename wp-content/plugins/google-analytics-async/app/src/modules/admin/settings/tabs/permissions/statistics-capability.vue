<template>
	<div class="sui-form-field">
		<label for="beehive-settings-custom-cap" class="sui-label">
			{{ $i18n.label.custom_capability }}
		</label>
		<input
			v-model="customCap"
			type="text"
			id="beehive-settings-custom-cap"
			class="sui-form-control"
			:placeholder="$i18n.placeholder.custom_capability"
		/>
		<span
			class="sui-description"
			v-html="
				sprintf(
					$i18n.desc.custom_capability,
					'https://wordpress.org/support/article/roles-and-capabilities/'
				)
			"
		></span>
	</div>
</template>

<script>
export default {
	name: 'StatisticsCapability',

	computed: {
		/**
		 * Computed model object to handle the custom cap settings.
		 *
		 * @since 3.2.5
		 *
		 * @returns {string}
		 */
		customCap: {
			get() {
				return this.getOption('custom_cap', 'permissions', '')
			},
			set(value) {
				this.setOption('custom_cap', 'permissions', value)
			},
		},
	},
}
</script>
