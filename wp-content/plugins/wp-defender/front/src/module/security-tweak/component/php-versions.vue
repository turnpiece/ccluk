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
                        {{__("PHP is the software that powers WordPress. It interprets the WordPress code and generates web pages people view. Naturally, PHP comes in different versions and is regularly updated. As newer versions are released, WordPress drops support for older PHP versions in favour of newer, faster versions with fewer bugs.")}}
                    </p>
                    <strong>
                        {{ __( "Status" ) }}
                    </strong>
                    <div class="sui-border-frame">
                        <div class="sui-row">
                            <div class="sui-col">
                                <strong>{{__( "Current PHP version")}}</strong>
                                <span :class="{'sui-tag-success':status==='fixed','sui-tag-warning':status==='issues'}"
                                      class="sui-tag">{{misc.php_version}}</span>
                            </div>
                            <div class="sui-col">
                                <strong>{{__( "Recommended")}}</strong>
                                <span class="sui-tag">{{ vsprintf( __( "%s or above"),misc.min_php_version)}}</span>
                            </div>
                        </div>
                    </div>
                    <p>
                        {{ vsprintf( __( "PHP versions older than %s are no longer supported. For security and stability we strongly recommend you upgrade your PHP version to version %s or newer as soon as possible. " ), misc.min_php_version, misc.min_php_version )}}
                    </p>
                    <p v-html="phpUrl">
                    </p>
                    <strong>
                        {{ __( "How to fix" ) }}
                    </strong>
                    <p>
                        {{ vsprintf(__( "Upgrade your PHP version to %s or above. Currently the latest stable version of PHP is %s."), misc.min_php_version, misc.stable_php_version ) }}
                    </p>
                    <div v-if="status==='fixed'" class="sui-notice sui-notice-success">
                        <p>{{__( "You have the latest version of PHP installed, good stuff!" ) }}</p>
                    </div>
                    <div v-if="status==='issues'" class="sui-notice">
                        <p>
                            {{ __("We can't update PHP for you, contact your hosting provider or developer to help you upgrade.")}}
                        </p>
                    </div>
                </div>
                <div v-if="status==='issues'" class="sui-box-footer">
                    <div class="sui-actions-left">
                        <form method="post" v-on:submit.prevent="ignore">
                            <submit-button :state="state" type="submit" class="sui-button-ghost ignore">
                                <span class="sui-loading-text"><i class="sui-icon-eye-hide" aria-hidden="true"></i> {{ __( "Ignore")}}</span>
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
                state: {
                    on_saving:false
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
        computed: {
            phpUrl: function () {
                return this.__("For more information visit ") + "<a target='_blank' href='http://php.net/supported-versions.php'>http://php.net/supported-versions.php</a>";
            }
        }
    }
</script>