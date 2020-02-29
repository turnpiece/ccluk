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
                        {{__("One of the more common methods for bots and hackers to gain access to your website is to find out login usernames and brute force the login area with tons of dummy passwords. The hope is that one the username and password combos will match, and viola - they have access (you'd be surprised how common weak passwords are!). ")}}
                    </p>
                    <p>
                        {{__("There are two sides to this hacking method - the username and the password. The passwords are random guesses, but (unfortunately) the username is easy to get. Simply typing the query string ?author=1, ?author=2 and so on, will redirect the page to /author/username/ - bam, the bot now has your usernames to begin brute force attacks with.")}}
                    </p>
                    <p>
                        {{__("This security tweak locks down your website by preventing the redirect, making it much harder for bots to get your usernames. We highly recommend actioning this tweak.")}}
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
                        <div class="sui-notice sui-notice-warning margin-bottom-30">
                            <p v-html="errorReason"></p>
                        </div>
                    </div>
                    <strong>{{__("How to fix")}}</strong>
                    <p>
                        {{__("Action this tweak to prevent the redirection from ?author=1 to /author/username/ and make it much harder for bots to find your usernames. Alternately, you can ignore this tweak if you want to allow this behaviour. Either way, you can easily revert the action at any time. ")}}
                    </p>
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
                                <span class="sui-loading-text">{{__( "Prevent" ) }}</span>
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
		name: "prevent-enum-users",
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