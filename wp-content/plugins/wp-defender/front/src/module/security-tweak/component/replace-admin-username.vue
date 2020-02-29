<template>
    <div id="change_admin" class="sui-accordion-item" :class="cssClass">
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
                        {{__("One of the most common methods of gaining access to websites is through brute force attacks on login areas using default/common usernames and passwords. If you're using the default ‘admin' username, you're giving away an important piece of the puzzle hackers need to hijack your website.")}}
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
                            <p v-html="errorReason">
                            </p>
                        </div>
                        <p>
                            {{ __( "Using the default admin username is widely considered bad practice and opens you up to the easiest form of entry to your website. We recommend avoiding generic usernames like admin, administrator, and anything that matches your hostname (mattebutter) as these are the usernames hackers and bots will attempt first." ) }}
                        </p>
                        <strong>
                            {{ __( "How to fix" ) }}
                        </strong>
                        <p>
                            {{ __( "Choose a new admin username name below. Alternately, you can ignore this tweak if you really want to keep the admin username at your own risk." ) }}
                        </p>
                        <div class="sui-border-frame">
                            <div class="sui-form-field">
                                <label class="sui-label">{{__( "New admin username" ) }}</label>
                                <input type="text" v-model="username" class="sui-form-control"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="status==='issues'" class="sui-box-footer">
                    <div class="sui-actions-left">
                        <form method="post" v-on:submit.prevent="ignore">
                            <submit-button :state="state" type="submit" name="ignore"
                                    value="ignore" class="sui-button sui-button-ghost ignore">
                                <span class="sui-loading-text"><i class="sui-icon-eye-hide" aria-hidden="true"></i> {{ __( "Ignore")}}</span>
                                <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                            </submit-button>
                        </form>
                    </div>
                    <div class="sui-actions-right">
                        <form v-on:submit.prevent="process" method="post"
                              class="hardener-frm rule-process hardener-frm-process-xml-rpc">
                            <submit-button :state="state" css-class="sui-button-blue apply" type="submit">
                                <span class="sui-loading-text">{{__( "Update Username" ) }}</span>
                                <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                            </submit-button>
                        </form>
                    </div>
                </div>
                <div class="sui-center-box">
                    <p>
                        {{__( "Ensure you backup your database before performing this tweak.") }}
                    </p>
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
        props: ['status', 'title', 'slug', 'successReason', 'errorReason'],
        data: function () {
            return {
                username: '',
                state: {
                    on_saving:false
                },
                timer: 5
            }
        },
        methods: {
            process: function () {
                let data = {
                    slug: this.slug,
                    username: this.username
                }
                this.state.on_saving = true;
                let self = this;
                this.resolve(data, function (response) {
                    if (response.success === false) {
                        self.state.on_saving = false;
                        Defender.showNotification('error', response.data.message);
                    } else {
                        //show a floating notice and refresh the page
                        Defender.showNotification('success', response.data.message, false);
                        let interval = setInterval(function () {
                            self.timer -= 1;
                            jQuery('.hardener-timer').text(self.timer);
                            if (self.timer <= 0) {
                                clearInterval(interval)
                                window.location = response.data.url;
                            }
                        }, 1000)
                    }
                });
            }
        },

    }
</script>