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
                        {{__("By default, users who select the 'remember me' option will stay logged in for 14 days. If you and your users don’t need to login to your website backend regularly, it’s good practice to reduce this default time to reduce the risk of someone gaining access to your automatically logged in account.")}}
                    </p>
                    <p>
                        {{__("If you are using the WordPress mobile app, want to make connections to services like IFTTT, or want to access and publish to your blog remotely, then you need XML-RPC enabled, otherwise it’s just another portal for hackers to target and exploit.")}}
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
                        <p v-if="misc.duration > 7">
                            {{ vsprintf(__( "If you don’t need to stay logged in for %d days, we recommend you reduce this duration to 7 days or less." ),misc.duration) }}
                        </p>
                        <strong>
                            {{ __( "How to fix" ) }}
                        </strong>
                        <p>
                            {{ __( "Choose the shortest login duration that most suit your website’s use case." ) }}
                        </p>
                        <div class="sui-form-field">
                            <label class="sui-label">{{__( "Login duration")}}</label>
                            <input type="text" id="duration" v-model="duration"
                                   class="sui-input-sm sui-field-has-suffix sui-form-control"/>
                            <span class="sui-field-suffix">{{__( "Days") }}</span>
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
                        <form v-on:submit.prevent="process" method="post">
                            <submit-button :state="state" :disabled="!state.canSubmit" css-class="sui-button-blue apply" type="submit">
                                <span class="sui-loading-text">{{__( "Update" ) }}</span>
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
        props: ['status', 'title', 'slug', 'errorReason', 'successReason', 'misc'],
        data: function () {
            return {
                state: {
                    on_saving:false,
                    canSubmit: false
                },
                duration: null
            }
        },
        methods: {
            process: function () {
                let data = {
                    slug: this.slug,
                    duration: this.duration
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
        watch: {
            duration: function () {
                if (!isNaN(this.duration) && parseInt(this.duration) > 0) {
                    this.state.canSubmit = true;
                } else {
                    this.state.canSubmit = false;
                }
            }
        },

    }
</script>