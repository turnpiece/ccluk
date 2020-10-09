<template>
    <label
            v-if="label"
            :for="showFor && id ? id : null"
            :id="labelId"
            :class="labelClass"
    >
        {{ label }}
        <slot></slot>
    </label>
</template>

<script>
	export default {
		name: 'SuiLabel',

		props: {
			id: {
				type: String,
				default: null
			},

			showFor: {
				type: Boolean,
				default: true
			},

			label: {
				type: String,
				default: null
			},

			labelSuffix: {
				type: String,
				default: '-field-label'
			},

			type: {
				validator: function ( value ) {
					return [ 'default', 'alt', 'alt-dark', 'hidden' ].indexOf( value ) !== -1
				},
				default: 'default'
			},
		},

		components: {},

		computed: {
			labelClass() {
				return {
					'sui-label': this.type === 'default',
					'sui-settings-label': this.type === 'alt',
					'sui-settings-label sui-dark': this.type === 'alt-dark',
					'sui-screen-reader-text': this.type === 'hidden',
				}
			},

			labelId() {
				if ( this.id && this.labelSuffix ) {
					return this.id + this.labelSuffix
				}

				return null
			}
		},
	}
</script>