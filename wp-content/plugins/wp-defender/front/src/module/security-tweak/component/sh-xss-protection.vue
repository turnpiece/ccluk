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
                        {{__("The HTTP X-XSS-Protection response header that stops pages from loading when they detect reflected cross-site scripting (XSS) attacks on Chrome, IE and Safari. These headers are largely unnecessary in modern browsers when websites have a strong Content-Security-Policy that disables the use of inline JavaScript. However, this header still provides protection for users of older web browsers that don't support CSP.")}}
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
                        <div class="sui-notice sui-notice-warning">
                            <p v-html="errorReason"></p>
                        </div>
                        <strong>
                            {{ __( "How to fix" ) }}
                        </strong>
                        <p>
                            {{ __( "Choose what level of protection X-XSS protection you would like to apply when XSS attacks are detected. Alternately, you can ignore this tweak if it does not apply to your website. Either way, you can easily revert the action at any time." ) }}
                        </p>
                    </div>
                    <div class="sui-side-tabs">
                        <div class="sui-tabs-menu">
                            <label for="xss-sanitize" class="sui-tab-item" :class="{active:mode==='sanitize'}">
                                <input type="radio" name="values" value="sanitize" v-model="mode"
                                       id="xss-sanitize"
                                       data-tab-menu="xss-sanitize-box">
                                {{__("Sanitize")}}
                            </label>
                            <label for="xss-block" class="sui-tab-item" :class="{active:mode==='block'}">
                                <input type="radio" name="values" value="block" v-model="mode"
                                       id="xss-block"
                                       data-tab-menu="xss-block-box">
                                {{__("Block")}}
                            </label>
                        </div>

                        <div class="sui-tabs-content">
                            <div class="sui-tab-content sui-tab-boxed" id="xss-sanitize-box"
                                 :class="{active:mode==='sanitize'}"
                                 data-tab-content="xss-sanitize-box">
                                <p>
                                    {{__("If a cross-site scripting attack is detected, the browser will sanitize the page (remove the unsafe parts).")}}
                                </p>
                            </div>
                            <div class="sui-tab-content sui-tab-boxed" id="xss-block-box"
                                 :class="{active:mode==='block'}"
                                 data-tab-content="xss-allow-from-box">
                                <p>
                                    {{__("Enables XSS filtering. Rather than sanitizing the page, the browser will prevent rendering of the page if an attack is detected.")}}
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
                              class="hardener-frm rule-process hardener-frm-process-xml-rpc">
                            <submit-button :state="state" css-class="sui-button update" type="submit">
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
                    on_saving:false
                },
                mode: null,
            }
        },
        created: function () {
            this.mode = this.misc.mode;
        },
        methods: {
            process: function (scenario) {
                let data = {
                    slug: this.slug,
                    mode: this.mode,
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