<template>
	<div class="sui-box-header">
		<h2 class="sui-box-title">{{ title }}</h2>
	</div>
</template>

<script>
export default {
	name: 'BoxHeader',

	props: {
		title: {
			type: String,
			required: true,
		},
	},
}
</script>
