<template>
	<div class="beehive-google-setup-connect">
		<div class="sui-form-field">
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
export default {
	name: 'SimpleConnectForm',
}
</script>
