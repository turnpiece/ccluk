<template>
	<a
		role="button"
		class="sui-button sui-button-blue"
		:href="$vars.urls.gtm_account"
	>
		{{ $i18n.button.activate }}
	</a>
</template>

<script>
export default {
	name: 'AccountInactive',
}
</script>
