<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">{{__("Issues")}}</h3>
            <div class="sui-actions-right">
                <div class="inline-form">
                    <label>{{__("Type")}}</label>
                    <select class="sui-select-sm issue-filter">
                        <option value="">{{__("All")}}</option>
                        <option value="core">{{__("Core")}}</option>
                        <option value="vuln">{{__("Plugins/Themes Vulnerability")}}</option>
                        <option value="content">{{__("Suspicious code")}}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="sui-box-body">
            <p>
                {{__("Here's a list of potentially harmful files Defender thinks could be suspicious. In a lot of cases the scan will pick up harmless files, but in some cases you may wish to remove files that look suspicious.")}}
            </p>
            <div v-if="issuesItem.length===0" class="sui-notice sui-notice-success">
                <p>
                    {{__("Your code is currently clean! There were no issues found during the last scan, though you can always perform a new scan anytime.")}}
                </p>
            </div>
            <nav_and_filter scenario="issue" @bulk:selected="bulk = $event" v-else></nav_and_filter>
        </div>
        <issues_table :bulk="isBulk" scenario="issue" :items="issuesItem" v-if="issuesItem.length"></issues_table>
    </div>
</template>

<script>
	import base_hepler from "../../../helper/base_hepler";
	import table from '../component/table';
	import nav_and_filter from '../component/nav-and-filter';
	import store from '../store/store';

	export default {
		mixins: [base_hepler],
		name: "issues",
		data: function () {
			return {
				bulk: false
			}
		},
		components: {
			'nav_and_filter': nav_and_filter,
			'issues_table': table
		},
		computed: {
			issuesItem: function () {
				//return this.$root.store.scan.issues_items;
				return store.state.scan.issues_items;
			},
			isBulk: function () {
				return this.bulk;
			}
		},
		methods: {
			filter: function (type) {
				if (type !== null) {
					store.state.active_filter = type;
				}
			},
		},
		mounted: function () {
			let self = this;
			jQuery('.issue-filter').change(function () {
				self.filter(jQuery(this).val())
			})
		},
	}
</script>
