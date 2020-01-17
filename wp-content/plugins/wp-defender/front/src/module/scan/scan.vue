<template>
	<no-scan v-if="getScan===null"></no-scan>
	<scanning v-else-if="getScan.status==='process' || getScan.status==='init'"></scanning>
	<scan-result v-else-if="getScan.status==='finish'"></scan-result>
</template>

<script>
	import no_scan from './screen/no-scan';
	import scanning from './screen/scanning';
	import scan_result from './screen/scan-result';

	export default {
		name: "scan_module",
		computed: {
			getScan: function () {
				return this.$root.store.scan;
			}
		},
		components: {
			'no-scan': no_scan,
			'scanning': scanning,
			'scan-result': scan_result
		}
	}
</script>