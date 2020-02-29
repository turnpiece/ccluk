<template>
	<div class="sui-box">
		<div class="sui-box-header">
			<h3 class="sui-box-title">
				{{__("Notification")}}
			</h3>
		</div>
		<form method="post" @submit.prevent="updateSettings">
			<div class="sui-box-body">
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("Scheduled Reports")}}
                        </span>
						<span class="sui-description">
                            {{__("Schedule Defender to automatically email you a summary of all your website events.")}}
                        </span>
					</div>
					<div class="sui-box-settings-col-2">
						<label class="sui-toggle">
							<input type="checkbox" name="notification" v-model="model.notification"
							       id="toggle_notification"/>
							<span class="sui-toggle-slider"></span>
						</label>
						<label for="toggle_notification" class="sui-toggle-label">
							{{__("Send regular email report")}}
						</label>
						<div class="sui-border-frame sui-toggle-content">
							<div class="margin-top-30">
								<recipients id="report_dialog" @update:recipients="updateRecipients"
								            v-bind:recipients="model.receipts"></recipients>
							</div>
							<div class="sui-form-field margin-top-30 schedule-box">
								<label class="sui-label">
									{{__("Frequency")}}
								</label>
								<div class="sui-row">
									<div class="sui-col">
										<select class="report-select" name="frequency" v-model="model.frequency">
											<option value="1">{{__("Daily")}}</option>
											<option value="7">{{__("Weekly")}}</option>
											<option value="30">{{__("Monthly")}}</option>
										</select>
									</div>
								</div>
								<div class="sui-row">
									<div class="sui-col days-container" v-show="state.show_day">
										<label class="sui-label">{{__("Day of the week")}}</label>
										<select class="report-select" name="day" v-model="model.day">
											<option v-for="day in misc.days_of_week"
											        :value="day.toLowerCase()">{{day}}
											</option>
										</select>
									</div>
									<div class="sui-col">
										<label class="sui-label">{{__("Time of day")}}</label>
										<select class="report-select" name="time" v-model="model.time">
											<option v-for="(display,time) in misc.times_of_day"
											        :value="time">{{display}}
											</option>
										</select>
									</div>
									<div class="sui-col-md-12">
                                                    <span class="sui-p-small" v-html="timezone_text">
                                                    </span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="sui-box-footer">
				<div class="sui-actions-right">
					<submit-button type="submit" css-class="sui-button-blue save-changes" :state="state">
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
		name: "report",
		data: function () {
			return {
				model: auditData.model.report,
				misc: auditData.misc,
				nonces: auditData.nonces,
				endpoints: auditData.endpoints,
				state: {
					on_saving: false,
					show_day: true
				},
			}
		},
		components: {
			recipients: recipients
		},
		methods: {
			updateRecipients: function (recipients) {
				this.model.receipts = recipients;
			},
			updateSettings: function () {
				let data = this.model;
				let self = this;
				this.httpPostRequest('updateSettings', {
					'data': JSON.stringify(data)
				}, function (response) {
					self.$parent.$emit('update_report_time', response.data.summary)
				});
			},
		},
		mounted: function () {
			let self = this;
			jQuery('.report-select').change(function () {
				let attr = jQuery(this).attr('name');
				self.model[attr] = jQuery(this).val()
			})
			this.model.day = this.model.day.toLowerCase();
		},
		watch: {
			'model.frequency': function () {
				this.state.show_day = this.model.frequency > 1;
			}
		},
		created: function () {
			this.state.show_day = this.model.frequency > 1;
		},
		computed: {
			timezone_text: function () {
				return this.vsprintf(this.__("Your timezone is set to UTC %s, so your current time is %s."), this.misc.tz, this.misc.current_time)
			}
		}
	}
</script>