<template>
	<div class="beehive-google-setup-connect">
		<div class="sui-form-field">
			<sui-notice type="info">
				<p>
					{{
						sprintf(
							$i18n.notice.google_already_connected,
							$vars.urls.statistics
						)
					}}
				</p>
			</sui-notice>
			<a
				type="button"
				:href="$moduleVars.google.login_url"
				class="sui-button sui-button-lg beehive-connect-google-btn"
			>
				<i class="sui-icon-google-connect" aria-hidden="true"></i>
				{{ $i18n.label.connect_google }}
			</a>
		</div>
	</div>
</template>

<script>
import SuiNotice from '@/components/sui/sui-notice'

export default {
	name: 'SimpleConnectForm',

	components: { SuiNotice },
}
</script>
