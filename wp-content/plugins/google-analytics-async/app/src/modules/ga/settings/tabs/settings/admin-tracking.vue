<template>
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				{{ $i18n.label.admin_tracking }}
			</span>
			<span class="sui-description">
				{{ $i18n.desc.admin_tracking }}
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<label for="beehive-settings-track-admin" class="sui-toggle">
				<input
					v-model="trackAdmin"
					type="checkbox"
					id="beehive-settings-track-admin"
					value="1"
				/>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="beehive-settings-track-admin" class="sui-toggle-label">
				{{ $i18n.label.admin_tracking_enable }}
			</label>
		</div>
	</div>
</template>

<script>
export default {
	name: 'AdminTracking',

	computed: {
		/**
		 * Computed object to get the admin tracking option.
		 *
		 * @since 3.2.0
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
}
</script>
