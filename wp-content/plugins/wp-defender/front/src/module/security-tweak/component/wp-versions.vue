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
                        {{__("WordPress is an extremely popular platform, and with that popularity comes hackers that increasingly want to exploit WordPress based websites. Leaving your WordPress installation out of date is an almost guaranteed way to get hacked as you’re missing out on the latest security patches.")}}
                    </p>
                    <div v-if="status==='issues'">
                        <strong>
                            {{ __( "Status" ) }}
                        </strong>
                        <div class="sui-border-frame">
                            <div class="sui-row">
                                <div class="sui-col">
                                    <strong>{{__( "Current WordPress version")}}</strong>
                                    <span :class="{'sui-tag-success':status==='fixed','sui-tag-warning':status==='issues'}"
                                          class="sui-tag">{{store.state.summary.wp_version}}</span>
                                </div>
                                <div class="sui-col">
                                    <strong>{{__( "Recommended")}}</strong>
                                    <span class="sui-tag">{{ misc.latest_wp}}</span>
                                </div>
                            </div>
                        </div>
                        <p>
                            {{ vsprintf( __( "Your current WordPress version is out of date, which means you could be missing out on the latest security patches in v%s" ), misc.latest_wp )}}
                        </p>
                        <strong>
                            {{ __( "How to fix" ) }}
                        </strong>
                        <p>
                            {{ __( "We recommend you update your version to the latest stable release, and maintain updating it regularly. Alternately, you can ignore this upgrade if you don’t require the latest version.") }}
                        </p>
                    </div>
                    <div v-else>
                        <strong>
                            {{ __( "Status" ) }}
                        </strong>
                        <div class="sui-notice sui-notice-success">
                            <p>
                                {{__("You have the latest version of WordPress installed, good stuff!")}}
                            </p>
                        </div>
                    </div>
                </div>
                <div v-if="status==='issues'" class="sui-box-footer">
                    <div class="sui-actions-left">
                        <form method="post" v-on:submit.prevent="ignore">
                            <submit-button :state="state" type="submit" css-class="sui-button sui-button-ghost ignore">
                                <span class="sui-loading-text"><i class="sui-icon-eye-hide" aria-hidden="true"></i> {{ __( "Ignore")}}</span>
                                <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                            </submit-button>
                        </form>
                    </div>
                    <div class="sui-actions-right">
                        <a :href="misc.core_update_url"
                           class="sui-button sui-button-ghost">
                            {{__("Update WordPress")}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import helper from '../../../helper/base_hepler';
    import securityTweakHelper from '../helper/security-tweak-helper';
    import store from '../store/store';

    export default {
        mixins: [helper, securityTweakHelper],
        props: ['status', 'title', 'slug', 'errorReason', 'successReason', 'misc'],
        data: function () {
            return {
                state: {
                    on_saving:false
                },
                store: store
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