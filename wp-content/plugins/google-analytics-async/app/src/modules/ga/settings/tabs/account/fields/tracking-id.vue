<template>
	<input
		type="text"
		class="sui-form-control"
		:id="id"
		:placeholder="$i18n.placeholder.tracking_id"
		v-model="tracking"
		@input="handleInput"
	/>
</template>

<script>
import { isValidGAID } from '@/helpers/utils'

export default {
	name: 'TrackingId',

	props: {
		id: {
			type: String,
			required: true,
		},
		value: {
			type: String,
			required: true,
		},
		context: {
			type: String,
			default: '',
		},
	},

	data() {
		return {
			tracking: this.value,
		}
	},

	computed: {
		/**
		 * Validate the current tracking ID.
		 *
		 * Check if the given tracking ID matches the GA
		 * tracking ID format.
		 *
		 * @since 3.2.0
		 *
		 * @return {boolean}
		 */
		isValid() {
			return isValidGAID(this.tracking) || !this.tracking
		},
	},

	methods: {
		/**
		 * Handle the input changes in tracking input.
		 *
		 * @since 3.2.0
		 *
		 * @param event
		 */
		handleInput(event) {
			// Emit an input event.
			this.$emit('input', this.tracking)

			// Emit a validation event on input change.
			this.$emit('validation', {
				valid: this.isValid,
				context: this.context,
			})
		},
	},
}
</script>
