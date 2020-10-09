<template>
	<fragment>
		<div class="sui-form-field beehive-margin-bottom--10">
			<label for="beehive-settings-tracking-code-auto" class="sui-label">
				{{ $i18n.label.tracking_id }}
				<span
					class="beehive-icon-tooltip sui-tooltip sui-tooltip-constrained"
					:data-tooltip="$i18n.tooltip.tracking_only"
				>
					<i class="sui-icon-info" aria-hidden="true"></i>
				</span>
				<a
					role="button"
					href="#"
					class="sui-label-link"
					@click.prevent="showManualForm"
				>
					{{ $i18n.label.use_different_tracking }}
				</a>
			</label>
			<input
				v-model="trackingId"
				type="text"
				id="beehive-settings-tracking-code-auto"
				class="sui-form-control"
				:placeholder="$i18n.placeholder.tracking_id"
				disabled
			/>
		</div>
		<sui-notice type="info">
			<p
				v-html="
					sprintf(
						$i18n.notice.automatic_tracking_enabled,
						'&lt;',
						'&gt;'
					)
				"
			></p>
		</sui-notice>
	</fragment>
</template>

<script>
import SuiNotice from '@/components/sui/sui-notice'

export default {
	name: 'TrackingAutomatic',

	components: { SuiNotice },

	computed: {
		/**
		 * Computed model object to get auto tracking flag.
		 *
		 * @since 3.3.0
		 *
		 * @returns {boolean}
		 */
		trackingId: {
			get() {
				return this.getOption('auto_track', 'misc', '')
			},
			set(value) {
				this.setOption('auto_track', 'misc', value)
			},
		},
	},

	methods: {
		/**
		 * Show the manual tracking ID input field.
		 *
		 * @since 3.3.0
		 *
		 * @returns {void}
		 */
		showManualForm() {
			this.setOption('auto_track', 'google', false)
		},
	},
}
</script>
