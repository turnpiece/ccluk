<template>
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				{{ $i18n.label.display_ad }}
			</span>
			<span
				class="sui-description"
				v-html="
					sprintf(
						$i18n.desc.display_ad,
						'https://support.google.com/analytics/answer/3450482?hl=en&ref_topic=3413645&rd=1'
					)
				"
			></span>
		</div>
		<div class="sui-box-settings-col-2">
			<label for="beehive-settings-advertising" class="sui-toggle">
				<input
					v-model="advertising"
					type="checkbox"
					id="beehive-settings-advertising"
					value="1"
				/>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="beehive-settings-advertising" class="sui-toggle-label">
				{{ $i18n.label.display_ad_enable }}
			</label>
			<sui-notice type="default" class="sui-toggle-content">
				<p
					v-html="
						sprintf(
							$i18n.notice.privacy_policy,
							'https://support.google.com/analytics/answer/2700409'
						)
					"
				></p>
			</sui-notice>
		</div>
	</div>
</template>

<script>
import SuiNotice from '@/components/sui/sui-notice'

export default {
	name: 'Advertising',

	components: { SuiNotice },

	computed: {
		/**
		 * Computed object to get advertising option.
		 *
		 * @since 3.2.0
		 *
		 * @returns {boolean}
		 */
		advertising: {
			get() {
				return this.getOption('advertising', 'general')
			},
			set(value) {
				this.setOption('advertising', 'general', value)
			},
		},
	},
}
</script>
