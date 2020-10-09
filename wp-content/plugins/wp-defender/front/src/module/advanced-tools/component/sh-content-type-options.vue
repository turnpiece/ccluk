<template>
	<div class="sui-toggle-content">
			<span class="sui-description toogle-content-description">
				{{ __( "Defender will automatically enforce the 'nosniff' X-Content-Type-Options header to help prevent MIME type sniffing and XSS attacks." ) }}
			</span>
	</div>
</template>
<script>
	import helper from '../../../helper/base_hepler';

	export default {
		mixins: [helper],
		name: "sh-content-type-options"
	}
</script>