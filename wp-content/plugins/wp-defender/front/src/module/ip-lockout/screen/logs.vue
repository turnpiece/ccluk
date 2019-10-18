<template>
	<div class="sui-box" data-tab="logs">
		<div class="sui-box-header">
			<h3 class="sui-box-title">{{__("Logs")}}</h3>
			<div class="sui-actions-right">
				<div class="box-filter">
                    <span>
                    {{__("Sort by")}}
                    </span>
					<select v-model="sort" class="sui-select-sm jquery-select" name="sort">
						<option value="latest">{{__("Latest")}}</option>
						<option value="oldest">{{__("Oldest")}}</option>
						<option value="ip">{{__("IP Address")}}</option>
					</select>
				</div>
				<a :href="adminUrl('admin-ajax.php?action=lockoutExportAsCsv')"
				   class="sui-button sui-button-outlined">
					{{__("Export CSV")}}
				</a>
			</div>
		</div>
		<div class="sui-box-body">
			<p>
				{{__("Here's your comprehensive IP lockout log. You can whitelist and ban IPs from there.")}}
			</p>
		</div>
		<lockout-table
				id="iplockout-table"
				v-bind:headers="['Details','Time','']"
				:table="table"
				:sort="sort"
		>
		</lockout-table>
	</div>
</template>

<script>
	import lockoutTable from '../component/lockout-table.vue';
	import base_helper from '../../../helper/base_hepler';

	export default {
		mixins: [base_helper],
		name: "logs",
		props: ['view'],
		data: function () {
			return {
				table: iplockout.table,
				sort: 'latest'
			};
		},
		components: {
			'lockout-table': lockoutTable
		},
		mounted: function () {
			let self = this;
			jQuery('.jquery-select').change(function () {
				let value = jQuery(this).val();
				let key = jQuery(this).attr('name');
				self[key] = value;
			})
		}
	}
</script>