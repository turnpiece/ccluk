<template>
	<div class="sui-box" v-if="(model.detect_404===false || model.detect_404===0)">
		<div class="sui-box-header">
			<h3 class="sui-box-title">
				{{__("404 Detection")}}
			</h3>
		</div>
		<div class="sui-message">
			<img v-if="maybeHideBranding()===false" :src="assetUrl('assets/img/lockout-man.svg')" class="sui-image"/>
			<div class="sui-message-content">
				<p>
					{{__("With 404 detection enabled, Defender will keep an eye out for IP addresses that repeatedly request pages on your website that don't exist and then temporarily block them from accessing your site.")}}
				</p>
				<form method="post" @submit.prevent="toggle(true,'detect_404')">
					<submit-button type="submit" class="sui-button-blue" :state="state">
						{{__("Activate")}}
					</submit-button>
				</form>
			</div>
		</div>
	</div>
	<div class="sui-box" v-else-if="(model.detect_404===true || model.detect_404===1)" data-tab="notfound_lockout">
		<form method="post" id="settings-frm" class="ip-frm" @submit.prevent="updateSettings">
			<div class="sui-box-header">
				<h3 class="sui-box-title">
					{{__("404 Detection")}}
				</h3>
			</div>
			<div class="sui-box-body">
				<p>
					{{__("With 404 detection enabled, Defender will keep an eye out for IP addresses that repeatedly request pages on your website that don't exist and then temporarily block them from accessing your site.")}}
				</p>
				<div :class="{'sui-notice-error':summary_data.nf.day > 0,'sui-notice-info':summary_data.nf.day===0}"
				     class="sui-notice">
					<p v-html="notification">
					</p>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">{{__("Threshold")}}</span>
						<span class="sui-description">
                            {{__("Specify how many 404 errors within a specific time period will trigger a lockout.")}}
                        </span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-row">
							<div class="sui-col-md-2">
								<label class="sui-label">{{__("404 hits")}}</label>
								<input size="8" v-model="model.detect_404_threshold" type="text"
								       class="sui-form-control sui-input-sm sui-field-has-suffix"
								       id="detect_404_threshold"
								       name="detect_404_threshold"/>
							</div>
							<div class="sui-col">
								<label class="sui-label">{{__("Timeframe")}}</label>
								<input size="8" v-model="model.detect_404_timeframe"
								       id="detect_404_timeframe"
								       name="detect_404_timeframe" type="text"
								       class="sui-form-control sui-input-sm sui-field-has-suffix">
								<span class="sui-field-suffix">{{__("seconds")}}</span>
							</div>
						</div>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">{{__("Duration")}}</span>
						<span class="sui-description">{{__("Choose how long you'd like to ban the locked out user for.")}}</span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-side-tabs">
							<div class="sui-tabs-menu">
								<label for="nf_timeframe"
								       :class="{active:model.detect_404_lockout_ban===false}"
								       class="sui-tab-item">
									<input type="radio" name="detect_404_lockout_ban" :value="false"
									       id="nf_timeframe"
									       data-tab-menu="nf-timeframe-box"
									       v-model="model.detect_404_lockout_ban">
									{{__("Timeframe")}}
								</label>
								<label for="nf_permanent"
								       :class="{active:model.detect_404_lockout_ban===true}"
								       class="sui-tab-item">
									<input type="radio" name="detect_404_lockout_ban" :value="true"
									       data-tab-menu=""
									       id="nf_permanent" v-model="model.detect_404_lockout_ban">
									{{__("Permanent")}}
								</label>
							</div>

							<div class="sui-tabs-content">
								<div class="sui-tab-content sui-tab-boxed"
								     :class="{active:model.detect_404_lockout_ban===false}"
								     id="nf-timeframe-box"
								     data-tab-content="nf-timeframe-box">
									<div class="sui-row">
										<div class="sui-col-md-3">
											<input v-model="model.detect_404_lockout_duration"
											       size="4"
											       name="detect_404_lockout_duration"
											       id="detect_404_lockout_duration" type="text"
											       class="sui-form-control"/>
										</div>
										<div class="sui-col-md-4">
											<select
													id="detect_404_lockout_duration_unit"
													name="detect_404_lockout_duration_unit"
													class="jquery-select sui-select"
													data-minimum-results-for-search="Infinity"
													v-model="model.detect_404_lockout_duration_unit">
												<option value="seconds">{{__("Seconds")}}</option>
												<option value="minutes">{{__("Minutes")}}</option>
												<option value="hours">{{__("Hours")}}</option>
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">{{__("Message")}}</span>
						<span class="sui-description">{{__("Customize the message locked out users will see.")}}</span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-form-field">
                    <textarea name="detect_404_lockout_message" class="sui-form-control"
                              id="detect_404_lockout_message"
                              v-model="model.detect_404_lockout_message"></textarea>
							<span class="sui-description" v-html="demo_link">
                            </span>
						</div>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        {{__("Files & Folders")}}
                    </span>
						<span class="sui-description">
                        {{__("Choose specific files and folders that you want to automatically ban users/bots from accessing, or whitelist access to.")}}
                        </span>
					</div>
					<div class="sui-box-settings-col-2">
						<strong>{{__("Blacklist")}}</strong>
						<p class="sui-description">
							{{__("Add file or folder URLs you want to automatically ban. Users or bots who request blacklisted them will be locked out as per your 404 rules above.")}}
						</p>
						<div class="sui-border-frame">
							<label class="sui-label">{{__("Blaclisted files & folders")}}</label>
							<textarea class="sui-form-control"
							          name="detect_404_blacklist"
							          v-model="model.detect_404_blacklist"
							          rows="8"></textarea>
							<span class="sui-description">
                                {{__("One URL per line. You must list the full path beginning with a /.")}}
                            </span>
						</div>
						<strong>{{__("Whitelist")}}</strong>
						<p class="sui-description">
							{{__("If you know a common file or folder on your website is missing, you can record it here so it doesn't count towards a lockout record.")}}
						</p>
						<div class="sui-border-frame">
							<label class="sui-label">{{__("Whitelisted files & folders")}}</label>
							<textarea class="sui-form-control"
							          id="detect_404_whitelist" name="detect_404_whitelist"
							          v-model="model.detect_404_whitelist"
							          rows="8"></textarea>
							<span class="sui-description">
                                {{__("One URL per line. You must list the full path beginning with a /.")}}
                            </span>
						</div>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                        {{__("Filetypes & Extensions")}}
                        </span>
						<span class="sui-description">
                        {{__("Choose which types of files or extentions you want to auto-ban or whitelist.")}}
                        </span>
					</div>
					<div class="sui-box-settings-col-2">
						<strong>{{__("Blacklist")}}</strong>
						<p class="sui-description">
							{{__("Add a common filetype or extention you want to auto-ban. Users or bots who request blacklisted filetypes or extensions will be locked out as per your 404 rules above.")}}
						</p>
						<div class="sui-border-frame">
							<label class="sui-label">{{__("Blaclisted filetypes & extensions")}}</label>
							<textarea class="sui-form-control"
							          name="detect_404_filetypes_blacklist"
							          v-model="model.detect_404_filetypes_blacklist"
							          rows="8"></textarea>
						</div>
						<strong>{{__("Whitelist")}}</strong>
						<p class="sui-description">
							{{__("Defender will log the 404 error, but won't lockout the user for these filetypes.")}}
						</p>
						<div class="sui-border-frame">
							<label class="sui-label">{{__("Whitelisted filetypes & extentions")}}</label>
							<textarea class="sui-form-control"
							          id="detect_404_blacklist" name="detect_404_ignored_filetypes"
							          v-model="model.detect_404_ignored_filetypes"
							          rows="8"></textarea>
						</div>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                        {{__("Exclusions")}}
                        </span>
						<span class="sui-description">
                            {{__("By default, Defender will monitor all interactions with your website but you can choose to disable 404 detection for specific areas of your site.")}}
                        </span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-form-field">
							<label class="sui-toggle">
								<input id="detect_404_logged" v-model="model.detect_404_logged"
								       type="checkbox"
								       true-value="true"
								       false-value="false"
								       name="detect_404_logged">
								<span class="sui-toggle-slider"></span>
							</label>
							<label for="detect_404_logged" class="sui-toggle-label">
								{{__("Monitor 404s from logged in users")}}
							</label>
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
						<submit-button type="button" @click="toggle(false,'detect_404')"
						               css-class="sui-button-ghost" :state="state">
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
	import base_heper from '../../../helper/base_hepler';

	export default {
		mixins: [base_heper],
		name: "nf-lockout",
		props: ['view'],
		data: function () {
			return {
				model: iplockout.model.nf_lockout,
				summary_data: iplockout.summaryData,
				state: {
					on_saving: false
				},
				nonces: iplockout.nonces,
				endpoints: iplockout.endpoints,
				misc: iplockout.misc
			}
		},
		methods: {
			toggle: function (value, type = 'login_protection') {
				let that = this;
				let envelope = {};
				envelope[type] = value;
				this.httpPostRequest('updateSettings', {
					data: JSON.stringify(envelope)
				}, function () {
					that.model[type] = value;
					if (value === true) {
						that.$nextTick(() => {
							that.rebindSUI()
						})
					}
				})
			},
			updateSettings: function () {
				let data = this.model;
				this.httpPostRequest('updateSettings', {
					data: JSON.stringify(data)
				});
			}
		},
		computed: {
			notification: function () {
				return this.summary_data.nf.day === 0 ?
					this.__("404 detection is enabled. There are no lockouts logged yet.") :
					this.vsprintf(this.__("There have been %s lockouts in the last 24 hours. <a href=\"%s\"><strong>View log</strong></a>."), this.summary_data.nf.day, this.adminUrl('admin.php?page=wdf-ip-lockout&view=logs'))
			},
			demo_link: function () {
				return this.vsprintf(this.__("This message will be displayed across your website during the lockout period. See a quick preview <a href=\"%s\">here</a>."), this.siteUrl('?def-lockout-demo=1&type=404'));
			}
		},
		mounted: function () {
			let self = this;
			jQuery('.jquery-select').change(function () {
				let value = jQuery(this).val();
				let key = jQuery(this).attr('name');
				self.model[key] = value;
			})
		}
	}
</script>