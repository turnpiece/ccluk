<template>
	<div
		:id="`${$parent.modal}-google-account`"
		class="sui-modal-slide sui-active sui-loaded"
		data-modal-size="md"
	>
		<div class="sui-box">
			<div
				class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60"
			>
				<whitelabel-banner
					src="onboarding/tracking.png"
					:alt="$i18n.label.add_measurement_id"
				/>

				<button
					class="sui-button-icon sui-button-float--right"
					@click="$emit('dismiss')"
				>
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
					<span class="sui-screen-reader-text">
						{{ $i18n.dialog.close }}
					</span>
				</button>
				<button
					data-modal-replace="beehive-onboarding-setup-account"
					data-modal-close-focus="beehive-wrap"
					data-modal-replace-mask="false"
					class="sui-button-icon sui-button-float--left"
				>
					<i
						class="sui-icon-chevron-left sui-md"
						aria-hidden="true"
					></i>
					<span class="sui-screen-reader-text">
						{{ $i18n.dialog.go_back }}
					</span>
				</button>
				<h3 class="sui-box-title sui-lg">
					{{ $i18n.label.add_measurement_id }}
				</h3>
				<p
					class="sui-description"
					v-html="
						sprintf(
							$i18n.desc.measurement_id,
							'https://support.google.com/analytics/answer/9539598?hl=en'
						)
					"
				></p>
			</div>
			<div class="sui-box-body">
				<div
					:class="{ 'sui-form-field-error': error }"
					class="sui-form-field beehive-margin-bottom--10"
				>
					<label
						for="beehive-settings-tracking-code-onboarding"
						class="sui-label"
					>
						{{ $i18n.label.measurement_id }}
					</label>
					<!-- Tracking ID input -->
					<measurement-id
						id="beehive-settings-tracking-code-onboarding"
						v-model="measurementId"
						context="onboarding"
						@validation="handleValidation"
					/>
					<span
						id="beehive-settings-tracking-code-onboarding-error"
						class="sui-error-message"
						role="alert"
						v-if="error"
					>
						{{ $i18n.error.measurement_id }}
					</span>
				</div>
			</div>
			<div
				class="sui-box-footer sui-flatten sui-content-center sui-spacing-bottom--50"
			>
				<button
					role="button"
					class="sui-button"
					:disabled="error || !measurementId"
					@click="saveCode"
				>
					{{ $i18n.button.save_code }}
				</button>
			</div>
		</div>
	</div>
</template>

<script>
import WhitelabelBanner from '@/components/elements/whitelabel-banner'
import MeasurementId from '@/modules/ga/admin/tabs/account/fields/measurement-id'

export default {
	name: 'SlideTrackingId',

	components: {
		MeasurementId,
		WhitelabelBanner,
	},

	props: {
		canContinue: {
			type: Boolean,
			default: true,
		},
	},

	data() {
		return {
			error: false,
		}
	},

	computed: {
		/**
		 * Computed model object to get measurement ID.
		 *
		 * @since 3.2.4
		 *
		 * @returns {string}
		 */
		measurementId: {
			get() {
				return this.getOption('measurement', 'tracking', '')
			},
			set(value) {
				this.setOption('measurement', 'tracking', value)
			},
		},
	},

	methods: {
		/**
		 * Handle the validation event from the component.
		 *
		 * @param {object} data Custom data.
		 *
		 * @since 3.2.4
		 */
		handleValidation(data) {
			this.error = !data.valid
		},

		/**
		 * Save the code or show the validation error.
		 *
		 * @since 3.2.4
		 */
		saveCode() {
			if (!this.error) {
				this.slideNext()
			}
		},

		/**
		 * Slide to next slide.
		 *
		 * @since 3.2.4
		 */
		slideNext() {
			// Get next slide ID.
			let next = this.canContinue ? '-admin-tracking' : '-finishing'

			// Slide to next slide.
			SUI.slideModal(this.$parent.modal + next, null, 'next')

			if (!this.canContinue) {
				this.$emit('submit')
			}
		},
	},
}
</script>
