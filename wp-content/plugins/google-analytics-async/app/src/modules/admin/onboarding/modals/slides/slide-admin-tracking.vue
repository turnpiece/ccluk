<template>
	<div
		class="sui-modal-slide"
		data-modal-size="md"
		:id="`${$parent.modal}-admin-tracking`"
	>
		<div class="sui-box">
			<div
				class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60"
			>
				<whitelabel-banner
					src="onboarding/setup.png"
					:alt="$i18n.label.admin_tracking"
				/>

				<button
					class="sui-button-icon sui-button-float--right onboarding-dismiss"
					@click="$emit('dismiss')"
				>
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
					<span class="sui-screen-reader-text">
						{{ $i18n.dialog.close }}
					</span>
				</button>
				<button
					class="sui-button-icon sui-button-float--left"
					data-modal-slide-intro="back"
					:data-modal-slide="`${$parent.modal}-google-account`"
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
					{{ $i18n.label.admin_tracking }}
				</h3>
				<p class="sui-description">
					{{ $i18n.desc.admin_tracking }}
				</p>
			</div>
			<div class="sui-box-body sui-box-body-toggle">
				<div class="beehive-onboarding-toggle">
					<label
						for="beehive-onboarding-admin-tracking"
						class="sui-toggle"
					>
						<input
							v-model="trackAdmin"
							type="checkbox"
							id="beehive-onboarding-admin-tracking"
							value="1"
						/>
						<span class="sui-toggle-slider"></span>
					</label>
					<label
						for="beehive-onboarding-admin-tracking"
						class="sui-toggle-label"
					>
						{{ $i18n.label.admin_tracking_enable }}
					</label>
				</div>
			</div>
			<div
				class="sui-box-footer sui-flatten sui-content-center sui-spacing-bottom--50"
			>
				<button role="button" class="sui-button" @click="slideSlide">
					{{ $i18n.dialog.continue }}
				</button>
			</div>
		</div>
	</div>
</template>

<script>
import WhitelabelBanner from '@/components/elements/whitelabel-banner'

export default {
	name: 'SlideAdminTracking',

	components: { WhitelabelBanner },

	computed: {
		/**
		 * Computed model object to get admin tracking option.
		 *
		 * @since 3.2.4
		 *
		 * @returns {boolean}
		 */
		trackAdmin: {
			get() {
				return this.getOption('track_admin', 'general')
			},
			set(value) {
				this.setOption('track_admin', 'general', value)
			},
		},
	},

	methods: {
		/**
		 * Slide to next slide and emit submit event.
		 *
		 * Show a finishing slide for the onboarding.
		 *
		 * @since 3.3.0
		 */
		slideSlide() {
			// Slide to finish slide.
			SUI.slideModal(this.$parent.modal + '-finishing', null, 'next')

			// Submit event.
			this.$emit('submit')
		},
	},
}
</script>
