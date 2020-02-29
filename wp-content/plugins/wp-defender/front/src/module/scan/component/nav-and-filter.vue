<template>
	<div class="sui-row">
		<div class="sui-col-md-5">
			<div class="inline-form">
				<label class="sui-checkbox">
					<input type="checkbox" v-model="filter.bulk">
					<span aria-hidden="true"></span>
				</label>
				<select class="sui-select-sm bulk-select" v-model="filter.bulk_action">
					<option value="">{{__("Bulk action")}}</option>
					<option v-if="scenario==='issue'" value="ignore">{{__("Ignore")}}</option>
					<option v-if="scenario==='ignored'" value="unignore">{{__("Restore")}}</option>
				</select>
				<!--                <button :disabled="bulkable" class="sui-button sui-button-ghost" type="button">-->
				<!--                    {{__("Bulk Update")}}-->
				<!--                </button>-->
				<button type="button" @click="bulkAction"
				        :class="[{'sui-button-onload':state.on_saving}]" :disabled="bulkable() === false"
				        class="sui-button sui-button-ghost">
                     <span class="sui-loading-text">
                         {{__("Bulk Update")}}
                     </span>
					<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
				</button>
			</div>
		</div>
	</div>
</template>

<script>
	import base_helper from '../../../helper/base_hepler';
	import store from '../store/store';

	export default {
		mixins: [base_helper],
		name: "nav-and-filter",
		props: ['scenario'],
		data: function () {
			return {
				filter: {
					bulk: false,
					bulk_action: ''
				},
				state: {
					on_saving: false
				},
				nonces: scanData.nonces,
				endpoints: scanData.endpoints,
			}
		},
		methods: {
			bulkAction: function () {
				let self = this;
				this.httpPostRequest('bulkAction', {
					'items': self.$root.store.bulk_ids,
					'bulk': self.filter.bulk_action
				}, function (response) {
					self.filter.bulk = false;
					self.$nextTick(() => {
						store.updateScan(response.data.scan);
						self.$nextTick(() => {
							self.rebindSUI();
						})
					})
				})
			},
			bulkable: function () {
				return this.$root.store.bulk_ids.length > 0 && this.filter.bulk_action !== ''
			}
		},
		watch: {
			'filter.bulk': function (value, old) {
				this.$emit('bulk:selected', value)
			}
		},
		mounted: function () {
			let self = this;
			jQuery('.bulk-select').change(function () {
				self.filter.bulk_action = jQuery(this).val()
			})
		}
	}
</script>