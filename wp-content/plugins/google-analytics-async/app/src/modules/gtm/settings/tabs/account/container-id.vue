<template>
	<div class="sui-form-field" :class="{ 'sui-form-field-error': error }">
		<label
			for="beehive-gtm-container-id"
			id="beehive-gtm-container-id-label"
			class="sui-label"
		>
			{{ $i18n.label.gtm_id }}
		</label>

		<input
			placeholder="GTM-XXXXXXX"
			id="beehive-gtm-container-id"
			class="sui-form-control sui-input-md"
			aria-labelledby="beehive-gtm-container-id-label"
			aria-describedby="beehive-gtm-container-id-desc"
			v-model="container"
		/>

		<span
			id="beehive-gtm-container-id-empty-desc"
			class="sui-description"
			v-if="!container"
			v-html="
				sprintf(
					$i18n.desc.gtm_id,
					'https://support.google.com/tagmanager/answer/6103696'
				)
			"
		>
		</span>
		<span
			id="beehive-gtm-container-id-set-desc"
			class="sui-description"
			v-else
			v-html="sprintf($i18n.desc.gtm_id_output, '&#60;head&#62;')"
		>
		</span>
	</div>
</template>

<script>
export default {
	name: 'ContainerId',

	props: {
		error: {
			type: Boolean,
			default: false,
		},
	},

	computed: {
		/**
		 * Computed object to get the container ID.
		 *
		 * @since 3.3.0
		 *
		 * @returns {string}
		 */
		container: {
			get() {
				return this.getOption('container', 'gtm', '')
			},
			set(value) {
				this.setOption('container', 'gtm', value)
			},
		},
	},
}
</script>
