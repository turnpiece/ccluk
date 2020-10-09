<template>
	<div id="security-headers" class="sui-box">

		<div class="sui-box-header">
			<h3 class="sui-box-title">
				{{__("Security Headers")}}
			</h3>
		</div>
		<form method="post" @submit.prevent="updateSettings">
			<div class="sui-box-body">
				<p>{{ __("Add extra security to your website by enabling and configuring the security headers.") }}</p>

				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">
							{{misc.sh_xframe.title}}
						</span>
						<span class="sui-description">
							{{misc.sh_xframe.misc.intro_text}}
						</span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-form-field" id="sh_xframe_wrap">
							<label for="sh_xframe" class="sui-toggle">
								<input role="presentation" type="checkbox" name="sh_xframe"
									class="toggle-checkbox" id="sh_xframe"
									v-model="model.sh_xframe"
									aria-labelledby="sh_xframe_label"
									:false-value="false" :true-value="true"/>
								<span class="sui-toggle-slider" aria-hidden="true"></span>
								<span id="sh_xframe_label" class="sui-toggle-label">{{ header_label(misc.sh_xframe.title) }}</span>
							</label>
							<sh-xframe
								v-show="true === model.sh_xframe"
								:misc="misc.sh_xframe"
								:model="model"
							></sh-xframe>
						</div>
					</div>
				</div>

				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">
							{{misc.sh_xss_protection.title}}
						</span>
						<span class="sui-description">
							{{misc.sh_xss_protection.misc.intro_text}}
						</span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-form-field" id="sh_xss_protection_wrap">
							<label for="sh_xss_protection" class="sui-toggle">
								<input role="presentation" type="checkbox" name="sh_xss_protection"
									class="toggle-checkbox" id="sh_xss_protection"
									v-model="model.sh_xss_protection"
									aria-labelledby="sh_xss_protection_label"
									:false-value="false" :true-value="true"/>
								<span class="sui-toggle-slider" aria-hidden="true"></span>
								<span id="sh_xss_protection_label" class="sui-toggle-label">{{ header_label(misc.sh_xss_protection.title) }}</span>
							</label>
							<sh-xss-protection
								v-show="true === model.sh_xss_protection"
								:misc="misc.sh_xss_protection"
								:model="model"
							></sh-xss-protection>
						</div>
					</div>
				</div>

				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">
							{{misc.sh_content_type_options.title}}
						</span>
						<span class="sui-description">
							{{misc.sh_content_type_options.misc.intro_text}}
						</span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-form-field" id="sh_content_type_options_wrap">
							<label for="sh_content_type_options" class="sui-toggle">
								<input role="presentation" type="checkbox" name="sh_content_type_options"
									class="toggle-checkbox" id="sh_content_type_options"
									v-model="model.sh_content_type_options"
									aria-labelledby="sh_content_type_options_label"
									:false-value="false" :true-value="true"/>
								<span class="sui-toggle-slider" aria-hidden="true"></span>
								<span id="sh_content_type_options_label" class="sui-toggle-label">{{ header_label(misc.sh_content_type_options.title) }}</span>
							</label>
							<sh-content-type v-show="true === model.sh_content_type_options"></sh-content-type>
						</div>
					</div>
				</div>

				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">
							{{misc.sh_strict_transport.title}}
						</span>
						<span class="sui-description">
							{{misc.sh_strict_transport.misc.intro_text}}
						</span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-form-field" id="sh_strict_transport_wrap">
							<label for="sh_strict_transport" class="sui-toggle">
								<input role="presentation" type="checkbox" name="sh_strict_transport"
									class="toggle-checkbox" id="sh_strict_transport"
									v-model="model.sh_strict_transport"
									aria-labelledby="sh_strict_transport_label"
									:false-value="false" :true-value="true"/>
								<span class="sui-toggle-slider" aria-hidden="true"></span>
								<span id="sh_strict_transport_label" class="sui-toggle-label">{{ header_label(misc.sh_strict_transport.title) }}</span>
							</label>
							<sh-strict-transport
								v-show="true === model.sh_strict_transport"
								:misc="misc.sh_strict_transport"
								:model="model"
							></sh-strict-transport>
						</div>
					</div>
				</div>

				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">
							{{misc.sh_referrer_policy.title}}
						</span>
						<span class="sui-description">
							{{misc.sh_referrer_policy.misc.intro_text}}
						</span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-form-field" id="sh_referrer_policy_wrap">
							<label for="sh_referrer_policy" class="sui-toggle">
								<input role="presentation" type="checkbox" name="sh_referrer_policy"
									class="toggle-checkbox" id="sh_referrer_policy"
									v-model="model.sh_referrer_policy"
									aria-labelledby="sh_referrer_policy_label"
									:false-value="false" :true-value="true"/>
								<span class="sui-toggle-slider" aria-hidden="true"></span>
								<span id="sh_referrer_policy_label" class="sui-toggle-label">{{ header_label(misc.sh_referrer_policy.title) }}</span>
							</label>
							<sh-referrer-policy
								v-show="true === model.sh_referrer_policy"
								:misc="misc.sh_referrer_policy"
								:model="model"
							></sh-referrer-policy>
						</div>
					</div>
				</div>

				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">
							{{misc.sh_feature_policy.title}}
						</span>
						<span class="sui-description">
							{{misc.sh_feature_policy.misc.intro_text}}
						</span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-form-field" id="sh_feature_policy_wrap">
							<label for="sh_feature_policy" class="sui-toggle">
								<input role="presentation" type="checkbox" name="sh_feature_policy"
									class="toggle-checkbox" id="sh_feature_policy"
									v-model="model.sh_feature_policy"
									aria-labelledby="sh_feature_policy_label"
									:false-value="false" :true-value="true"/>
								<span class="sui-toggle-slider" aria-hidden="true"></span>
								<span id="sh_feature_policy_label" class="sui-toggle-label">{{ header_label(misc.sh_feature_policy.title) }}</span>
							</label>
							<sh-feature-policy
								v-show="true === model.sh_feature_policy"
								:misc="misc.sh_feature_policy"
								:model="model"
							></sh-feature-policy>
						</div>
					</div>
				</div>

			</div>
			<div class="sui-box-footer">
				<div class="sui-actions-right">
					<submit-button type="submit" :state="state" css-class="sui-button-blue save-changes">
						<i class="sui-icon-save" aria-hidden="true"></i>
						{{__("Save Changes")}}
					</submit-button>
				</div>
			</div>
		</form>
	</div>
</template>

<script>
	import base_helper from '../../../helper/base_hepler';
	import sh_xframe from '../component/sh-xframe.vue';
	import sh_xss_protection from '../component/sh-xss-protection.vue';
	import sh_content_type from '../component/sh-content-type-options.vue';
	import sh_strict_transport from '../component/sh-strict-transport.vue';
	import sh_referrer_policy from '../component/sh-referrer-policy.vue';
	import sh_feature_policy from '../component/sh-feature-policy.vue';

	export default {
		mixins: [base_helper],
		components: {
			'sh-xframe': sh_xframe,
			'sh-xss-protection': sh_xss_protection,
			'sh-content-type': sh_content_type,
			'sh-strict-transport': sh_strict_transport,
			'sh-referrer-policy': sh_referrer_policy,
			'sh-feature-policy': sh_feature_policy
		},
		name: "security-headers",
		data: function (){
			return {
				misc: advanced_tools.misc.security_headers,
				model: advanced_tools.model.security_headers,
				nonces: advanced_tools.nonces,
				endpoints: advanced_tools.endpoints,
				state: {
					on_saving: false,
					original_state: false
				}
			}
		},
		methods: {
			updateSettings: function () {
				let data = this.model;
				let that = this;
				this.httpPostRequest('updateSettings', {
					data: JSON.stringify({
						settings: data,
						module: 'security-headers'
					})
				}, function () {
					that.state.original_state = true
				});
			},
			header_label: function (header_title) {
				return this.vsprintf(this.__('Enable %s'), header_title);
			}
		},
		created: function () {
			this.$on('mode_referrer_policy', function(value){
				this.model.sh_referrer_policy_mode = value;
			});
			this.$on('hsts_maximum_age', function(value){
				this.model.hsts_cache_duration = value;
			});
		}
	}
</script>