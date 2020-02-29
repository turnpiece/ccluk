<template>
	<div class="sui-box">
		<div class="sui-box-header">
			<h3 class="sui-box-title">
				{{__("Reporting")}}
			</h3>
		</div>
		<form method="post" @submit.prevent="updateSettings">
			<div class="sui-box-body">
				<p>
					{{__("Defender can automatically run regular scans of your website and email you reports.")}}
				</p>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">{{__("Enable reporting")}}</span>
						<span class="sui-description">
                            {{__("Enabling this option will ensure you're always the first to know when something suspicious is detected on your site.")}}
                        </span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-side-tabs">
							<div class="sui-tabs-menu">
								<label for="report_on"
								       :class="{active:model.report===true}"
								       class="sui-tab-item">
									<input type="radio" :value="true"
									       id="report_on"
									       data-tab-menu="report-box"
									       v-model="model.report">
									{{__("On")}}
								</label>
								<label for="report_off"
								       :class="{active:model.report===false}"
								       class="sui-tab-item">
									<input type="radio" :value="false" data-tab-menu=""
									       id="report_off" v-model="model.report">
									{{__("Off")}}
								</label>
							</div>
							<div class="sui-tabs-content">
								<div class="sui-tab-content sui-tab-boxed" :class="{active:model.report===true}"
								     id="report-box" data-tab-content="report-box">
									<p class="sui-p-small">
										{{__("By default, we will only notify the recipients below when there is an issue from your file scan. Enable this option to send emails even when no issues are detected.")}}
									</p>
									<label class="sui-toggle">
										<input role="presentation" type="checkbox" name="always_send"
										       class="toggle-checkbox" v-model="model.always_send"
										       id="always_send"/>
										<span class="sui-toggle-slider"></span>
									</label>
									<label for="always_send" class="sui-toggle-label">
										{{__("Also send notifications when no issues are detected.")}}
									</label>
									<div class="margin-top-30">
										<recipients id="report_dialog" @update:recipients="updateRecipients"
										            v-bind:recipients="model.recipients"></recipients>
									</div>
									<div class="margin-bottom-20">
										<h3 class="sui-field-list-title">
											{{__("Reporting")}}
										</h3>
									</div>
									<div class="sui-form-field">
										<label class="sui-label">{{__("Frequency")}}</label>
										<select class="report-selector" v-model="model.frequency" name="frequency">
											<option value="1">{{__("Daily")}}</option>
											<option value="7">{{__("Weekly")}}</option>
											<option value="30">{{__("Monthly")}}</option>
										</select>
									</div>
									<div class="sui-form-field" v-show="model.frequency > 1">
										<label class="sui-label">{{__("Day of the week")}}</label>
										<select class="report-selector" name="day" v-model="model.day">
											<option v-for="day in days_of_week" :value="day.toLowerCase()">{{day}}
											</option>
										</select>
									</div>
									<div class="sui-form-field">
										<label class="sui-label">{{__("Time of day")}}</label>
										<select class="sui-select report-selector" name="time" v-model="model.time">
											<option v-for="(display,time) in times_of_day"
											        :value="time">{{display}}
											</option>
										</select>
									</div>
								</div>
							</div>
						</div>
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
	import recipients from "../../../component/recipients"

	export default {
		mixins: [base_hepler],
		name: "reporting",
		data: function () {
			return {
				model: scanData.model.reporting,
				days_of_week: scanData.misc.days_of_week,
				times_of_day: scanData.misc.times_of_day,
				state: {
					on_saving: false,

				},
				nonces: scanData.nonces,
				endpoints: scanData.endpoints,
			}
		},
		methods: {
			updateRecipients: function (recipients) {
				this.model.recipients = recipients;
			},
			updateSettings: function () {
				let data = this.model;
				this.httpPostRequest('updateSettings', {
					'data': JSON.stringify(data)
				});
			},
		},
		components: {
			recipients: recipients
		},
		mounted: function () {
			let self = this;
			jQuery('.report-selector').change(function () {
				let val = jQuery(this).val();
				let attr = jQuery(this).attr('name');
				self.model[attr] = val;
			})
			self.model.day = self.model.day.toLowerCase();
		}
	}
</script>