<template>
	<fragment>
		<table class="sui-table beehive-outside-table">
			<tbody>
				<tr>
					<th class="content-title">
						{{ $i18n.label.gtm_account }}
					</th>
					<td class="content-center">
						<span class="sui-tag sui-tag-blue sui-tag-sm">
							{{ $i18n.label.item_active }}
						</span>
					</td>
					<td class="content-right">
						<a
							role="button"
							class="sui-button-icon"
							:href="$vars.urls.gtm_account"
						>
							<i
								class="sui-icon-widget-settings-config"
								aria-hidden="true"
							></i>
							<span class="sui-screen-reader-text">
								{{ $i18n.button.configure }}
							</span>
						</a>
					</td>
				</tr>
				<tr>
					<th class="content-title">
						{{ $i18n.label.gtm_variables }}
					</th>
					<td class="content-center">{{ varsCount }}</td>
					<td class="content-right">
						<a
							role="button"
							class="sui-button-icon"
							:href="$vars.urls.gtm_settings"
						>
							<i
								class="sui-icon-widget-settings-config"
								aria-hidden="true"
							></i>
							<span class="sui-screen-reader-text">
								{{ $i18n.button.configure }}
							</span>
						</a>
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3">
						<a
							:href="$vars.urls.gtm_settings"
							class="sui-button sui-button-ghost"
						>
							<i class="sui-icon-eye" aria-hidden="true"></i>
							{{ $i18n.button.configure }}
						</a>
					</td>
				</tr>
			</tfoot>
		</table>
	</fragment>
</template>

<script>
export default {
	name: 'AccountActive',

	computed: {
		/**
		 * Check if the container ID is setup.
		 *
		 * @since 3.3.0
		 *
		 * @returns {int}
		 */
		varsCount() {
			let enabled = this.getOption('enabled', 'gtm')
			let custom = this.getOption('custom', 'gtm')

			return Object.keys(enabled).length + Object.keys(custom).length
		},
	},
}
</script>
