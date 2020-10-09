<template>
	<fragment>
		<p class="sui-description">{{ $i18n.desc.integrations }}</p>

		<!-- Forminator form integration -->
		<integration-item
			id="forminator_forms"
			:active="forminatorActive"
			:title="$i18n.integration.forminator_forms"
			:desc="forminatorFormDesc"
		/>

		<!-- Forminator polls integration -->
		<integration-item
			id="forminator_polls"
			:active="forminatorSupported"
			:title="$i18n.integration.forminator_polls"
			:desc="forminatorPollDesc"
		/>

		<!-- Forminator quiz integration -->
		<integration-item
			id="forminator_quizzes"
			:active="forminatorSupported"
			:title="$i18n.integration.forminator_quizzes"
			:desc="forminatorQuizDesc"
		/>

		<!-- Hustle form integration -->
		<integration-item
			id="hustle_leads"
			:active="hustleActive"
			:title="$i18n.integration.hustle_leads"
			:desc="hustleDesc"
		/>
	</fragment>
</template>

<script>
import IntegrationItem from './integrations/integration-item'

export default {
	name: 'Integrations',

	components: { IntegrationItem },

	computed: {
		/**
		 * Get Hustle integration's description.
		 *
		 * @since 3.3.0
		 *
		 * @return {string}
		 */
		hustleDesc() {
			return this.hustleActive
				? this.$i18n.desc.hustle_leads
				: this.$i18n.desc.hustle_leads_install
		},

		/**
		 * Get Forminator form integration's description.
		 *
		 * @since 3.3.0
		 *
		 * @return {string}
		 */
		forminatorFormDesc() {
			return this.forminatorActive
				? this.$i18n.desc.forminator_forms
				: this.$i18n.desc.forminator_forms_install
		},

		/**
		 * Get Forminator poll integration's description.
		 *
		 * @since 3.3.0
		 *
		 * @return {string}
		 */
		forminatorPollDesc() {
			if (this.forminatorActive && !this.forminatorSupported) {
				return this.$i18n.desc.forminator_polls_update
			} else {
				return this.forminatorActive
					? this.$i18n.desc.forminator_polls
					: this.$i18n.desc.forminator_polls_install
			}
		},

		/**
		 * Get Forminator quiz integration's description.
		 *
		 * @since 3.3.0
		 *
		 * @return {string}
		 */
		forminatorQuizDesc() {
			if (this.forminatorActive && !this.forminatorSupported) {
				return this.$i18n.desc.forminator_quizzes_update
			} else {
				return this.forminatorActive
					? this.$i18n.desc.forminator_quizzes
					: this.$i18n.desc.forminator_quizzes_install
			}
		},

		/**
		 * Check if Hustle plugin is active.
		 *
		 * @since 3.3.0
		 *
		 * @return {boolean}
		 */
		hustleActive() {
			return this.$moduleVars.integrations.hustle_active
		},

		/**
		 * Check if Forminator plugin is active.
		 *
		 * @since 3.3.0
		 *
		 * @return {boolean}
		 */
		forminatorActive() {
			return this.$moduleVars.integrations.forminator_active
		},

		/**
		 * Check if Forminator plugin is supported version.
		 *
		 * @since 3.3.0
		 *
		 * @return {boolean}
		 */
		forminatorSupported() {
			return (
				this.forminatorActive &&
				this.$moduleVars.integrations.forminator_supported
			)
		},
	},
}
</script>
