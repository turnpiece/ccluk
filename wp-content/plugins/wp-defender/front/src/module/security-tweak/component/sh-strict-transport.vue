<template>
	<div :id="slug" class="sui-accordion-item" :class="cssClass">
		<div class="sui-accordion-item-header">
			<div class="sui-accordion-item-title">
				<i aria-hidden="true" :class="titleIcon"></i>
				{{title}}
				<div class="sui-actions-right">
					<button v-if="status!=='ignore'" class="sui-button-icon sui-accordion-open-indicator"
					        aria-label="Open item">
						<i class="sui-icon-chevron-down" aria-hidden="true"></i>
					</button>
					<submit-button v-else type="button" :state="state"
					               css-class="sui-button-ghost float-r restore" @click="restore">
                        <span class="sui-loading-text">
                        <i class="sui-icon-undo" aria-hidden="true"></i>{{__("Restore")}}
                        </span>
						<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
					</submit-button>
				</div>
			</div>
		</div>
		<div class="sui-accordion-item-body" v-if="status!=='ignore'">
			<div class="sui-box">
				<div class="sui-box-body">
					<strong>{{__("Overview")}}</strong>
					<p>
						{{__("The HTTP Strict-Transport-Security response header (HSTS) lets a web site tell browsers that it should only be accessed using HTTPS, instead of using HTTP. This is extremely important for websites that store and process sensitive information like ECommerce stores and helps prevent Protocol Downgrade and Clickjacking attacks.")}}
					</p>
					<div v-if="status==='fixed'">
						<strong>
							{{ __( "Status" ) }}
						</strong>
						<div class="sui-notice sui-notice-success">
							<p v-html="successReason"></p>
						</div>
					</div>
					<div v-else>
						<strong>
							{{ __( "Status" ) }}
						</strong>
						<div class="sui-notice sui-notice-warning">
							<p v-html="errorReason"></p>
						</div>
						<strong>
							{{ __( "How to fix" ) }}
						</strong>
						<p>
							{{ __( "Choose your requirements below to generate a Strict Transport Security header for your site. This will convert all non-https links to https. It will also block any insecure connections coming into your website, such as assets loaded via the http:// protocol that cannot be loaded via https://. Alternately, you can ignore this tweak if it does not apply to your website. Either way, you can easily revert the action at any time." ) }}
						</p>
					</div>
					<h5>{{__("HSTS Preload")}}</h5>
					<p>{{__("Google maintains an HSTS preload service. By following the guidelines and successfully submitting your domain, browsers will never connect to your domain using an insecure connection.")}}</p>
					<label for="hsts_preload" class="sui-checkbox">
						<input type="checkbox" true-value="1" false-value="0" v-model="hsts_preload"
						       id="hsts_preload"/>
						<span aria-hidden="true"></span>
						<span>{{__("Preload")}}</span>
					</label>
					<div v-show="show_hsts_warning" class="sui-notice sui-notice-warning">
						<p v-html="hsts_warning_text">
						</p>
					</div>
					<div v-if="misc.allow_subdomain === true">
						<h5>{{__("Subdomains")}}</h5>
						<p>{{__("If this optional parameter is specified, this rule applies to all of the site's subdomains as well.")}}</p>
						<label for="include_subdomain" class="sui-checkbox">
							<input type="checkbox" v-model="include_subdomain" true-value="1" false-value="0"
							       id="include_subdomain"/>
							<span aria-hidden="true"></span>
							<span>{{__("Include Subdomains")}}</span>
						</label>
					</div>
					<h5>{{__("Browser Caching")}}</h5>
					<p>{{__("Choose when the browser should cache and apply the Strict Transport Security policy for.")}}</p>
					<label class="sui-label">{{__("HSTS Maximum Age")}}</label>
					<select data-minimum-results-for-search="Infinity" class="sui-select select-need-update"
					        id="hsts-cache-duration" data-module="sh-strict-transport"
					        v-model="hsts_cache_duration"
					        data-key="hsts_cache_duration">
						<option value="1 hour">{{__("1 hour")}}</option>
						<option value="24 hours">{{__("24 hours")}}</option>
						<option value="7 days">{{__("7 days")}}</option>
						<option value="3 months">{{__("3 months")}}</option>
						<option value="6 months">{{__("6 months")}}</option>
						<option value="12 months">{{__("12 months")}}</option>
					</select>
				</div>
				<div v-if="status==='fixed'" class="sui-box-footer">
					<div class="sui-actions-left" v-if="misc.somewhere===false">
						<form v-on:submit.prevent="revert" method="post">
							<submit-button :state="state" css-class="sui-button-ghost revert" type="submit">
								<span class="sui-loading-text">{{__( "Revert" ) }}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
					<div class="sui-actions-right">
						<form v-on:submit.prevent="process('update')" method="post"
						      class="hardener-frm rule-process hardener-frm-process-xml-rpc update">
							<submit-button :state="state" type="submit">
								<span class="sui-loading-text">{{__( "Update" ) }}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
				</div>
				<div v-if="status==='issues'" class="sui-box-footer">
					<div class="sui-actions-left">
						<form method="post" v-on:submit.prevent="ignore">
							<submit-button :state="state" type="submit" css-class="sui-button-ghost ignore">
								<span class="sui-loading-text"><i class="sui-icon-eye-hide" aria-hidden="true"></i> {{ __( "Ignore")}}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
					<div class="sui-actions-right">
						<form v-on:submit.prevent="process('enforce')" method="post"
						      class="hardener-frm rule-process hardener-frm-process-xml-rpc">
							<submit-button :state="state" css-class="sui-button-blue apply" type="submit">
								<span class="sui-loading-text">{{__( "Enforce" ) }}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
	import helper from '../../../helper/base_hepler';
	import securityTweakHelper from '../helper/security-tweak-helper';

	export default {
		mixins: [helper, securityTweakHelper],
		props: ['status', 'title', 'slug', 'errorReason', 'successReason', 'misc'],
		data: function () {
			return {
				prefix: '',
				state: {
					on_saving: false
				},
				hsts_preload: null,
				include_subdomain: null,
				hsts_cache_duration: null
			}
		},
		created: function () {
			this.hsts_preload = this.misc.hsts_preload;
			this.include_subdomain = this.misc.include_subdomain;
			this.hsts_cache_duration = this.misc.hsts_cache_duration
			if (this.misc.allow_subdomain === false) {
				this.include_subdomain = false;
			}
		},
		mounted: function () {
			let self = this;
			jQuery('#hsts-cache-duration').change(function () {
				let value = jQuery(this).val();
				self.hsts_cache_duration = value;
			})
		},
		methods: {
			process: function (scenario) {
				let data = {
					slug: this.slug,
					hsts_preload: this.hsts_preload,
					include_subdomain: this.include_subdomain,
					hsts_cache_duration: this.hsts_cache_duration,
					scenario: scenario
				}
				this.state.on_saving = true;
				let self = this;
				this.resolve(data, function (response) {
					self.state.on_saving = false;
					if (response.success === false) {
						Defender.showNotification('error', response.data.message);
					} else {
						Defender.showNotification('success', response.data.message);
						this.rebindSUI();
					}
				});
			}
		},
		computed: {
			show_hsts_warning: function () {
				return parseInt(this.hsts_preload) === 1
			},
			hsts_warning_text: function () {
				return this.__('Note: Do not include the preload directive by default if you maintain a project that provides HTTPS configuration advice or provides an option to enable HSTS. Be aware that inclusion in the preload list cannot easily be undone. Domains can be removed, but it takes months for a change. Check <a target="_blank" href="https://hstspreload.org/">here</a> for more information.')
			}
		},
		watch: {
			'misc.hsts_cache_duration': function () {
				this.hsts_cache_duration = this.misc.hsts_cache_duration
			}
		}
	}
</script>