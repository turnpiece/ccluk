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
                        {{__("Developers often use the built-in PHP and scripts error debugging feature, which displays code errors on the frontend of your website. It's useful for active development, but on live sites provides hackers yet another way to find loopholes in your site's security.")}}
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
                            {{ __( "While it may not be in use, we haven't found any code stopping debugging information being output. It's best to remove all doubt and disable error reporting completely." ) }}
                        </p>
                        <strong>
                            {{ __( "How to fix" ) }}
                        </strong>
                        <p>
                            {{ __( "We can automatically disable all error reporting for you below. Alternately, you can ignore this tweak if you don't require it. Either way, you can easily revert these actions at any time." ) }}
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
                            <submit-button :state="state" css-class="sui-button-blue" type="submit">
                                <span class="sui-loading-text">{{__( "Disable error debugging" ) }}</span>
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
        props: ['status', 'title', 'slug', 'errorReason', 'successReason'],
        data: function () {
            return {
                state: {
                    on_saving:false
                }
            }
        },
        methods: {
            process: function () {
                let data = {
                    slug: this.slug,
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