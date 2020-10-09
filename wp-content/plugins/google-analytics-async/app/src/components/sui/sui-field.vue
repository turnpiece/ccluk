<template>
	<div class="sui-form-field">
		<sui-label
			:id="id"
			:label="label"
			:type="labelClass"
			:show-for="labelFor"
		/>
		<p :id="topDescId" class="sui-description" v-if="topDescription">
			{{ topDescription }}
		</p>
		<slot></slot>
		<p :id="bottomDescId" v-if="btmDescription">
			{{ btmDescription }}
		</p>
	</div>
</template>

<script>
import SuiLabel from './sui-label'

export default {
	name: 'SuiField',

	components: { SuiLabel },

	props: {
		id: String,
		label: String,
		labelClass: String,
		labelFor: String,
		topDescription: String,
		btmDescription: String,
	},

	computed: {
		/**
		 * Get top description ID.
		 *
		 * @since 3.2.3
		 *
		 * @return {string|null}
		 */
		topDescId() {
			if (this.id) {
				return this.id + '-field-top-description'
			}

			return null
		},

		/**
		 * Get bottom description ID.
		 *
		 * @since 3.2.3
		 *
		 * @return {string|null}
		 */
		bottomDescId() {
			if (this.id) {
				return this.id + '-field-btm-description'
			}

			return null
		},
	},
}
</script>
