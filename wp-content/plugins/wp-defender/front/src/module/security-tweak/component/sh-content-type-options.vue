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
                        {{__("The X-Content-Type-Options header is used to protect against MIME sniffing attacks. The most common example of this is when a website allows users to upload content to a website, however the user disguises a particular file type as something else. This can give them the opportunity to perform cross-site scripting and compromise the website.")}}
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
                            {{ __( "We highly recommend you enforce the 'nosniff' X-Content-Type-Options header to help prevent MIME type sniffing and XSS attacks. Alternately, you can ignore this tweak if it does not apply to your website. Either way, you can easily revert the action at any time." ) }}
                        </p>
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
                              class="hardener-frm rule-process hardener-frm-process-xml-rpc">
                            <submit-button :state="state"
                                           class="sui-button" type="submit">
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
				values: null
			}
		},
		created: function () {
			this.mode = this.misc.mode;
			this.values = this.misc.values
		},
		methods: {
			process: function (scenario) {
				//we have to validate the values abit
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

					}
				});
			}
		},

	}
</script>