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
						{{__("The WordPress REST API allows your website to communicate with internal and external services and applications. It allows developers to create single pages apps on top of WordPress and unlocks a whole new world of possibilities, including Gutenberg. If you are not using any external services that require public access to the API, it's yet another portal for exploitation from bots and hackers. We recommend only allowing authorized requests.")}}
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
							<!--                            {{ __( "If you don't require external API access from third party apps and software, we recommend you lock the WordPress REST API to authorized requests only. WordPress (including Gutenberg and plugins) will continue to work as normal, but public API requests will be blocked.Alternately, if you have external services that require access to the API, ignore this tweak. Note: This tweak can prevent your website from working properly, only activate this tweak if you know what you're doing." ) }}-->
							{{__("If you don't require external API access from third party apps and software, we recommend you lock the WordPress REST API to authorized requests only. WordPress (including Gutenberg and plugins) will continue to work as normal, but public API requests will be blocked. Alternately, if you have external services that require access to the API, ignore this tweak.")}}
						</p>
						<p>
							{{__("Note: This tweak can prevent your website from working properly, only activate this tweak if you know what you're doing.")}}
						</p>
<!--						<div class="sui-side-tabs">-->
                        <!--							<div class="sui-tabs-menu">-->
                        <!--								<label for="wra-allow-auth" class="sui-tab-item" :class="{active:mode==='allow-all'}">-->
                        <!--									<input type="radio" name="values" value="allow-all" v-model="mode"-->
                        <!--									       id="wra-allow-auth"-->
                        <!--									       data-tab-menu="">-->
                        <!--									{{__("Allow All Requests")}}-->
                        <!--								</label>-->
                        <!--								<label for="wra-block-all" class="sui-tab-item" :class="{active:mode==='allow-auth'}">-->
                        <!--									<input type="radio" name="values" value="allow-auth" v-model="mode"-->
                        <!--									       id="wra-block-all"-->
                        <!--									       data-tab-menu="wra-allow-auth-box">-->
                        <!--									{{__("Allow Authorized Requests Only")}}-->
                        <!--								</label>-->
                        <!--							</div>-->

                        <!--							<div class="sui-tabs-content">-->
                        <!--								<div class="sui-tab-content sui-tab-boxed" id="wra-allow-auth-box"-->
                        <!--								     :class="{active:mode==='allow-auth'}"-->
                        <!--								     data-tab-content="wra-allow-auth-box">-->
                        <!--									<p>-->
                        <!--										{{__("This will only block unauthorized API calls, which effectively prevents anonymous external access.")}}-->
                        <!--									</p>-->
                        <!--								</div>-->
                        <!--							</div>-->
                        <!--						</div>-->
					</div>
				</div>
				<div v-if="status==='issues'" class="sui-box-footer">
					<div class="sui-actions-left">
						<form method="post" v-on:submit.prevent="ignore">
							<submit-button type="submit" :state="state" css-class="sui-button-ghost ignore">
								<span class="sui-loading-text"><i class="sui-icon-eye-hide" aria-hidden="true"></i> {{ __( "Ignore")}}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
					<div class="sui-actions-right">
						<form v-on:submit.prevent="process" method="post">
							<submit-button :state="state"
							               css-class="sui-button-blue apply" type="submit">
								<span class="sui-loading-text">{{__( "Block unauthorized requests" ) }}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
				</div>
				<div v-else class="sui-box-footer">
					<form v-on:submit.prevent="revert" method="post">
						<submit-button :state="state" type="submit" css-class="revert">
							<span class="sui-loading-text">{{__( "Revert" ) }}</span>
							<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
						</submit-button>
					</form>
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
				mode: this.misc.mode,
				state: {
					on_saving: false
				}
			}
		},
		methods: {
			process: function () {
				let data = {
					slug: this.slug,
					//mode: this.mode
				}
				this.state.on_saving = true;
				let self = this;
				this.resolve(data, function (response) {
					if (response.success === false) {
						self.state.on_saving = false;
						Defender.showNotification('error', response.data.message);
					} else {
						Defender.showNotification('success', response.data.message);

					}
				});
			}
		},

	}
</script>