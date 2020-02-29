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
					<p v-html="misc.intro_text"></p>
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
							{{ __( "Choose whether or not you want to allow your webpages to be embedded inside iframes. Unless you have a specific reason to allow this, we recommend locking this down. Alternately, you can ignore this tweak if it does not apply to your website. Either way, you can easily revert the action at any time." ) }}
						</p>
					</div>
					<div class="sui-side-tabs">
						<div class="sui-tabs-menu">
							<label for="xf-sameorigin" class="sui-tab-item" :class="{active:mode==='sameorigin'}">
								<input type="radio" name="values" value="sameorigin" v-model="mode"
								       id="xf-sameorigin"
								       data-tab-menu="xf-sameorigin-box">
								{{__("Sameorigin")}}
							</label>
							<label for="xf-allow-from" class="sui-tab-item" :class="{active:mode=='allow-from'}">
								<input type="radio" name="values" value="allow-from" v-model="mode"
								       id="xf-allow-from"
								       data-tab-menu="xf-allow-from-box">
								{{__("Allow-from")}}
							</label>
							<label for="xf-deny" class="sui-tab-item" :class="{active:mode==='deny'}">
								<input type="radio" name="values" value="deny" v-model="mode"
								       id="xf-deny"
								       data-tab-menu="xf-deny-box">
								{{__("Deny")}}
							</label>
						</div>

						<div class="sui-tabs-content">
							<div class="sui-tab-content sui-tab-boxed" id="xf-sameorigin-box"
							     :class="{active:mode==='sameorigin'}"
							     data-tab-content="xf-sameorigin-box">
								<p>
									{{__("The page can only be displayed in a frame on the same origin as the page itself. The spec leaves it up to browser vendors to decide whether this option applies to the top level, the parent, or the whole chain.")}}
								</p>
							</div>
							<div class="sui-tab-content sui-tab-boxed" id="xf-allow-from-box"
							     :class="{active:mode==='allow-from'}"
							     data-tab-content="xf-allow-from-box">
								<div class="sui-form-field">
									<label class="sui-label">{{__("Allow from URLs")}}</label>
									<textarea class="sui-form-control" v-model="values"
									          :placeholder="__('Place allowed page URLs, one per line')"></textarea>
									<span class="sui-description">
                                            {{vsprintf(__("The page %s will only be displayed in a frame on the specified origin. One per line."),siteUrl)}}
                                        </span>
								</div>
							</div>
							<div class="sui-tab-content sui-tab-boxed" id="xf-deny-box" :class="{active:mode==='deny'}"
							     data-tab-content="xf-deny-box">
								<p>
									{{__("The page can’t be displayed in a frame, regardless of the site attempting to do so.")}}
								</p>
							</div>
						</div>
					</div>
				</div>
				<div v-if="status==='fixed'" class="sui-box-footer">
					<div class="sui-actions-left">
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
							<submit-button :state="state" type="submit" css-class="update">
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
						      class="hardener-frm apply rule-process hardener-frm-process-xml-rpc">
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
				values: []
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
				this.state.isSaving = true;
				let self = this;
				this.resolve(data, function (response) {
					self.state.isSaving = false;
					if (response.success === false) {
						Defender.showNotification('error', response.data.message);
					} else {
						Defender.showNotification('success', response.data.message);

					}
				});
			}
		},

	}
</script>