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
						{{__("The Referrer-Policy HTTP header tells web-browsers how to handle referrer information that is sent to websites when a user clicks a link that leads to another page or website link. Referrer headers tell website owners inbound visitors came from (like Google Analytics Acquisition Reports), but there are cases where you may want to control or restrict the amount of information present in this header.")}}
					</p>
					<div v-if="status==='fixed'">
						<strong>
							{{ __( "Status" ) }}
						</strong>
						<div class="sui-notice sui-notice-success margin-bottom-30">
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
							{{ __( "There are various types of referrer policies you can choose from to control what information your referrer header includes. Choose a header that matches your requirements from the options box below. Alternately, you can ignore this tweak if it does not apply to your website. Either way, you can easily revert the action at any time." ) }}
						</p>
						<p>
							{{__("Choose which referrer information to send along with requests.")}}
						</p>
					</div>
					<div class="sui-border-frame">
						<label class="sui-label">{{__("Referrer Information")}}</label>
						<select class="sui-select" data-minimum-results-for-search="Infinity" id="referrer-policy" v-model="mode">
							<option value="no-referrer">no-referrer</option>
							<option value="no-referrer-when-downgrade">no-referrer-when-downgrade</option>
							<option value="origin">origin</option>
							<option value="origin-when-cross-origin">origin-when-cross-origin</option>
							<option value="same-origin">same-origin</option>
							<option value="strict-origin">strict-origin</option>
							<option value="strict-origin-when-cross-origin">strict-origin-when-cross-origin</option>
							<option value="unsafe-url">unsafe-url</option>
						</select>
						<p class="sui-description">{{policyDesc}}</p>
					</div>
				</div>
				<div v-if="status==='fixed'" class="sui-box-footer">
					<div class="sui-actions-left">
						<form v-on:submit.prevent="revert" method="post">
							<button :class="{'sui-button-onload':state.isSaving===true}"
							        class="sui-button sui-button-ghost revert" type="submit">
								<span class="sui-loading-text">{{__( "Revert" ) }}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</button>
						</form>
					</div>
					<div class="sui-actions-right">
						<form v-on:submit.prevent="process('update')" method="post"
						      class="hardener-frm rule-process hardener-frm-process-xml-rpc">
							<submit-button css-class="update" :state="state" class="sui-button" type="submit">
								<span class="sui-loading-text">{{__( "Update" ) }}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
				</div>
				<div v-if="status==='issues'" class="sui-box-footer">
					<div class="sui-actions-left">
						<form method="post" v-on:submit.prevent="ignore">
							<submit-button :state="state" type="submit" class="sui-button sui-button-ghost ignore">
								<span class="sui-loading-text"><i class="sui-icon-eye-hide" aria-hidden="true"></i> {{ __( "Ignore")}}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
					<div class="sui-actions-right">
						<form v-on:submit.prevent="process('enforce')" method="post"
						      class="hardener-frm rule-process hardener-frm-process-xml-rpc apply">
							<submit-button :state="state" css-class="sui-button-blue" type="submit">
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
				mode: null,
				values: null,
				policyDesc: null
			}
		},
		created: function () {
			this.mode = this.misc.mode;
			this.values = this.misc.values
		},
		methods: {
			process: function (scenario) {
				let data = {
					slug: this.slug,
					mode: this.mode,
					values: this.values,
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
			},
		},
		mounted: function () {
			let vm = this;
			jQuery('#referrer-policy').change(function () {
				vm.mode = jQuery(this).val();
			})
		},
		watch: {
			mode: function () {
				if (this.mode === 'no-referrer') {
					this.policyDesc = this.__("The Referer header will be omitted entirely. No referrer information is sent along with requests.")
				}
				if (this.mode === 'no-referrer-when-downgrade') {
					this.policyDesc = this.__("This is the user agent's default behavior if no policy is specified. The origin is sent as referrer to a-priori as-much-secure destination (HTTPS->HTTPS), but isn't sent to a less secure destination (HTTPS->HTTP).")
				}
				if (this.mode === 'origin') {
					this.policyDesc = this.__("Only send the origin of the document as the referrer in all cases. The document https://example.com/page.html will send the referrer https://example.com/.")
				}
				if (this.mode === 'origin-when-cross-origin') {
					this.policyDesc = this.__("Send a full URL when performing a same-origin request, but only send the origin of the document for other cases.")
				}
				if (this.mode === 'same-origin') {
					this.policyDesc = this.__("A referrer will be sent for same-site origins, but cross-origin requests will contain no referrer information.")
				}
				if (this.mode === 'strict-origin') {
					this.policyDesc = this.__("Only send the origin of the document as the referrer to a-priori as-much-secure destination (HTTPS->HTTPS), but don't send it to a less secure destination (HTTPS->HTTP).")
				}
				if (this.mode === 'strict-origin-when-cross-origin') {
					this.policyDesc = this.__("Send a full URL when performing a same-origin request, only send the origin of the document to a-priori as-much-secure destination (HTTPS->HTTPS), and send no header to a less secure destination (HTTPS->HTTP).")
				}
				if (this.mode === 'unsafe-url') {
					this.policyDesc = this.__("Send a full URL (stripped from parameters) when performing a a same-origin or cross-origin request.")
				}
			},
			'misc.mode': function () {
				this.mode = this.misc.mode;
			}
		}
	}
</script>