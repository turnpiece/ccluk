<template>
	<div class="sui-form-field">
		<label v-if="label" :for="id" class="sui-label">{{ label }}</label>
		<sui-select2
			:id="id"
			:options="getAccounts"
			:placeholder="getPlaceholder"
			:disabled="isEmpty"
			v-model="account"
		/>
		<span v-if="showDesc" class="sui-description">
			{{ $i18n.desc.account_not_here }}
		</span>
	</div>
</template>

<script>
import SuiSelect2 from '@/components/sui/sui-select2'

export default {
	name: 'Profiles',

	components: { SuiSelect2 },

	props: {
		id: {
			type: String,
			required: true,
		},
		label: {
			type: String,
			required: false,
		},
		showDesc: {
			type: Boolean,
			default: true,
		},
	},

	computed: {
		/**
		 * Get the selected profile ID.
		 *
		 * @since 3.2.0
		 *
		 * @returns {string}
		 */
		account: {
			get() {
				return this.getOption('account_id', 'google', '')
			},
			set(value) {
				this.setOption('account_id', 'google', value)
			},
		},

		/**
		 * Get the formatted profile data for the select2 options.
		 *
		 * @since 3.2.0
		 *
		 * @return {[]}
		 */
		getAccounts() {
			let options = []

			// Loop and format profile data.
			this.getProfiles.forEach((profile) => {
				options.push({
					id: profile.id,
					text:
						profile.url +
						' (' +
						profile.name +
						' - ' +
						profile.property +
						')',
				})
			})

			return options
		},

		/**
		 * Get the placeholder text based on the profile data.
		 *
		 * If there is no profiles found, show that message in placeholder.
		 *
		 * @since 3.2.0
		 *
		 * @return {string}
		 */
		getPlaceholder() {
			if (this.isEmpty) {
				return this.$i18n.placeholder.no_website
			} else {
				return this.$i18n.placeholder.select_website
			}
		},

		/**
		 * Check if the profile list is empty.
		 *
		 * @since 3.2.0
		 *
		 * @return {boolean}
		 */
		isEmpty() {
			return this.getProfiles.length <= 0
		},

		/**
		 * Get profiles from the Vuex state.
		 *
		 * @since 3.2.0
		 *
		 * @return {Object}
		 */
		getProfiles() {
			return this.$store.state.helpers.google.profiles
		},
	},
}
</script>
