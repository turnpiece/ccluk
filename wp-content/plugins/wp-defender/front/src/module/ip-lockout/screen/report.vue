<template>
	<div class="sui-box">
		<div class="sui-box-header">
			<h3 class="sui-box-title">
				{{__("Reporting")}}
			</h3>
		</div>
		<form method="post" @submit.prevent="updateSettings">
			<div class="sui-box-body">
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        {{__("Lockouts Report")}}
                    </span>
						<span class="sui-description">
                        {{__("Configure Defender to automatically email you a lockout report for this website.")}}
                    </span>
					</div>

					<div class="sui-box-settings-col-2">
						<div class="sui-form-field">
							<label class="sui-toggle">
								<input role="presentation" type="checkbox" name="report"
								       class="toggle-checkbox" v-model="model.report"
								       id="report" :true-value="true" :false-value="false"/>
								<span class="sui-toggle-slider"></span>
							</label>
							<label for="report" class="sui-toggle-label">
								{{__("Send regular email report")}}
							</label>
							<div class="sui-border-frame sui-toggle-content" v-show="model.report===true">
								<strong>
									{{__("Recipients")}}
								</strong>
								<recipients id="report_receipts" @update:recipients="updateRecipients"
								            v-bind:recipients="model.report_receipts"></recipients>
								<div class="sui-form-field schedule-box">
									<strong>
										{{__("Schedule")}}
									</strong><br/>
									<label class="sui-label">
										{{__("Frequency")}}
									</label>
									<div class="sui-side-tabs">
										<div class="sui-tabs-menu">
											<label for="report-daily"
											       :class="{active:parseInt(model.report_frequency)===1}"
											       class="sui-tab-item">
												<input type="radio" name="report_frequency"
												       data-attribute="feature_image_fit" :value="1"
												       id="report-daily" v-model="model.report_frequency"
												       data-tab-menu="report-config-box">
												{{__("Daily")}}
											</label>
											<label for="report-weekly"
											       :class="{active:parseInt(model.report_frequency)===7}"
											       class="sui-tab-item">
												<input type="radio" name="report_frequency"
												       data-attribute="feature_image_fit" :value="7"
												       data-tab-menu="report-config-box"
												       v-model="model.report_frequency"
												       id="report-weekly">
												{{__("Weekly")}}
											</label>
											<label for="report-monthly"
											       :class="{active:parseInt(model.report_frequency)===30}"
											       class="sui-tab-item">
												<input type="radio" name="report_frequency"
												       data-attribute="feature_image_fit" :value="30"
												       v-model="model.report_frequency"
												       id="report-monthly" data-tab-menu="report-config-box">
												{{__("Monthly")}}
											</label>
										</div>

										<div class="sui-tabs-content">
											<div class="sui-tab-content sui-tab-boxed active" id="report-config-box"
											     data-tab-content="report-config-box">
												<div class="sui-row">
													<div class="sui-col"
													     v-show="parseInt(model.report_frequency) !== 1">
														<label class="sui-label">{{__("Day of the week")}}</label>
														<select class="jquery-select sui-select" name="report_day" id="report_day"
														        v-model="model.report_day">
															<option v-for="day in misc.days_of_weeks"
															        :value="day.toLowerCase()">{{day}}
															</option>
														</select>
													</div>
													<div class="sui-col">
														<label class="sui-label">{{__("Time of day")}}</label>
														<select class="jquery-select sui-select" name="report_time" id="report_time"
														        v-model="model.report_time">
															<option v-for="(time,index) in misc.times_of_days"
															        :value="index">{{time}}
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
	import base_heper from '../../../helper/base_hepler';
	import recipients from '../../../component/recipients';

	export default {
		mixins: [base_heper],
		name: "report",
		props: ['view'],
		data: function () {
			return {
				model: iplockout.model.report,
				state: {
					on_saving: false,
					ip_actioning: []
				},
				nonces: iplockout.nonces,
				endpoints: iplockout.endpoints,
				misc: iplockout.misc,
			}
		},
		components: {
			'recipients': recipients
		},
		methods: {
			updateRecipients: function (recipients) {
				this.model.report_receipts = recipients;
			},
			updateSettings: function () {
				let data = this.model;
				this.httpPostRequest('updateSettings', {
					data: JSON.stringify(data)
				});
			},
		},
		mounted: function () {
			let self = this;
			jQuery('.jquery-select').change(function () {
				let value = jQuery(this).val();
				let key = jQuery(this).attr('name');
				self.model[key] = value;
			})
			self.model.report_day = self.model.report_day.toLowerCase();
		},
		computed: {
			timezone_text: function () {
				return this.vsprintf(this.__("Your timezone is set to UTC %s, so your current time is %s."), this.misc.tz, this.misc.current_time)
			}
		}
	}
</script>