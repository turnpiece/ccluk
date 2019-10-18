<template>
	<div class="sui-box audit-settings">
		<div class="sui-box-header">
			<h3 class="sui-box-title">
				{{__("Settings")}}
			</h3>
		</div>
		<form method="post" @submit.prevent="updateSettings">
			<div class="sui-box-body">
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">{{__("Storage")}}</span>
						<span class="sui-description">
                            {{__("Events are stored in our API. You can choose how many days to keep logs for before they are removed.")}}
                        </span>
					</div>

					<div class="sui-box-settings-col-2">
						<div class="sui-form-field">
							<select class="" v-model="model.storage_days" name="storage_days" id="storage_days">
								<option value="24 hours">{{__("24 hours")}}</option>
								<option value="7 days">{{__("7 days")}}</option>
								<option value="30 days">{{__("30 days")}}</option>
								<option value="3 months">{{__("3 months")}}</option>
								<option value="6 months">{{__("6 months")}}</option>
								<option value="12 months">{{__("12 months")}}</option>
							</select>
							<span class="sui-description">
                                {{__("Choose how long you'd like to store your event logs locally before wiping the oldest.")}}
                            </span>
						</div>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                        {{__("Deactivate")}}
                        </span>
						<span class="sui-description">
                       {{__("If you no longer want to use this feature you can turn it off at any time.")}}
                        </span>
					</div>
					<div class="sui-box-settings-col-2">
						<submit-button type="button" css-class="sui-button-ghost" @click="toggle(false)" :state="state">
							<i class="sui-icon-save" aria-hidden="true"></i>
							{{__("Deactivate")}}
						</submit-button>
					</div>
				</div>
			</div>
			<div class="sui-box-footer">
				<div class="sui-actions-right">
					<submit-button type="submit" css-class="sui-button-blue" :state="state">
						<i class="sui-icon-save" aria-hidden="true"></i>
						{{__("Save Changes")}}
					</submit-button>
				</div>
			</div>
		</form>
	</div>

</template>

<script>
	import base_hepler from "../../../helper/base_hepler";

	export default {
		mixins: [base_hepler],
		name: "settings",
		data: function () {
			return {
				model: auditData.model.settings,
				state: {
					on_saving: false
				},
				nonces: auditData.nonces,
				endpoints: auditData.endpoints,
			}
		},
		methods: {
			toggle: function (value, type = 'enabled') {
				let that = this;
				let envelope = {};
				envelope[type] = value;
				this.httpPostRequest('updateSettings', {
					data: JSON.stringify(envelope)
				}, function () {
					//that.model[type] = value;
					that.$parent.$emit('enable_state', value)
				})
			},
			updateSettings: function () {
				let data = this.model;
				this.httpPostRequest('updateSettings', {
					'data': JSON.stringify(data)
				});
			},
		},
		mounted:function () {
			let vm = this;
			jQuery('#storage_days').change(function () {
				vm.model.storage_days = jQuery(this).val()
			})
		}
	}
</script>