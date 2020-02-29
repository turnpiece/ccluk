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
					               css-class="sui-button-ghost restore float-r" @click="restore">
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
						{{__("Pingbacks notify a website when it has been mentioned by another website, like a form of courtesy communication. However, these notifications can be sent to any website willing to receive them, opening you up to DDoS attacks, which can take your website down in seconds and fill your posts with spam comments.")}}
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
						<p>
							{{ __( "Trackbacks and pingbacks can lead to DDos attacks and tons of spam comments. If you don’t require this feature, we recommend turning it off." ) }}
						</p>
						<strong>
							{{ __( "How to fix" ) }}
						</strong>
						<p>
							{{ __( "We can automatically disable pingbacks and trackbacks for you below. Alternately, you can ignore this tweak if you don’t require it. Either way, you can easily revert these actions at any time." ) }}
						</p>
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
						<form v-on:submit.prevent="process" method="post">
							<submit-button :state="state" css-class="sui-button-blue apply" type="submit">
								<span class="sui-loading-text">{{__( "Disable Pingbacks" ) }}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
				</div>
				<div v-else class="sui-box-footer">
					<form v-on:submit.prevent="revert" method="post">
						<submit-button :state="state" css-class="sui-button revert" type="submit">
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
		props: ['status', 'title', 'slug', 'errorReason', 'successReason'],
		data: function () {
			return {
				state: {
					on_saving: false
				}
			}
		},
		methods: {
			process: function () {
				let data = {
					slug: this.slug
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