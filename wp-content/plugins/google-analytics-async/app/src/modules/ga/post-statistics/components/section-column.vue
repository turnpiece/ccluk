<template>
	<div :id="`beehive-post-status-${id}`" class="sui-col-md-4">
		<div class="beehive-block">
			<h3 tabindex="0" class="sui-screen-reader-text">
				{{ sprintf(screenReader, value, periodText) }}
			</h3>
			<div tabindex="-1" class="beehive-block-wrapper" aria-hidden="true">
				<span class="beehive-tooltip" :data-tooltip="desc">
					<i class="sui-icon-info sui-md"></i>
				</span>
				<div class="beehive-block-content">
					<div class="beehive-block-content-top">
						<span class="beehive-name">{{ label }}</span>
						<span class="beehive-value">{{ value }}</span>
					</div>
					<div class="beehive-block-content-bottom">
						<span class="beehive-date">{{ periodText }}</span>
						<span
							v-if="'none' === trendType"
							class="beehive-trend"
							:data-item="id"
							:data-trend="trendType"
						>
							<i class="sui-icon-pause"></i>
						</span>
						<span
							v-else
							class="beehive-trend"
							:data-item="id"
							:data-trend="trendType"
							>{{ trendValue }}
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'SectionColumn',

	props: {
		id: String,
		value: {},
		label: String,
		desc: String,
		screenReader: String,
		trendType: String,
		trendValue: {},
	},

	computed: {
		/**
		 * Get the period text for the section.
		 *
		 * @since 3.2.4
		 *
		 * @return {string}
		 */
		periodText() {
			return (
				this.$moduleVars.dates.start_date +
				' - ' +
				this.$moduleVars.dates.end_date
			)
		},
	},
}
</script>
