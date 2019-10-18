<template>
    <div class="sui-dialog" aria-hidden="true" tabindex="-1" id="activator">
        <div class="sui-dialog-overlay" data-a11y-dialog-hide></div>
        <div class="sui-dialog-content" aria-labelledby="Quick setup" aria-describedby="" role="dialog">
            <div class="sui-box" role="document" v-if="status==='normal'">
                <div class="sui-box-header">
                    <h3 class="sui-box-title">
                        {{__("Quick Setup")}}
                    </h3>
                    <div class="sui-actions-right">
                        <form method="post" @submit.prevent="skip">
                            <submit-button type="submit" class="sui-button-ghost" :state="state">
                                {{__("Skip")}}
                            </submit-button>
                        </form>
                    </div>
                </div>
                <form method="post" @submit.prevent="activate">
                    <div class="sui-box-body">
                        <p>
                            {{__("Welcome to Defender, the hottest security plugin for WordPress! Let’s quickly set up the basics for you, then you can fine tweak each setting as you go – our recommendations are on by default.")}}
                        </p>
                        <hr class="sui-flushed"/>
                        <div class="sui-row">
                            <div class="sui-col-md-10">
                                <span class="sui-settings-label">
                                    {{__("File Scanning")}}
                                </span>
                                <span class="sui-description">
                                {{__("Scan your website for file changes, vulnerabilities and injected code and get notified about anything suspicious.")}}
                                </span>
                            </div>

                            <div class="sui-col-md-2">
                                <div class="sui-form-field tr">
                                    <label class="sui-toggle">
                                        <input type="checkbox"
                                               name="activator[]" checked
                                               class="toggle-checkbox" v-model="model.activate_scan" id="active_scan"
                                               value="activate_scan"/>
                                        <span class="sui-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <hr class="sui-flushed"/>
                        <div class="sui-row">
                            <div class="sui-col-md-10">
                                <span class="sui-settings-label">
                                   {{__("IP Lockouts")}}
                                </span>
                                <span class="sui-description">
                                    {{__("Protect your login area and have Defender automatically lockout any suspicious behaviour.")}}
                                </span>
                            </div>

                            <div class="sui-col-md-2">
                                <div class="sui-form-field tr">
                                    <label class="sui-toggle">
                                        <input type="checkbox" checked
                                               name="activator[]" v-model="model.activate_lockout"
                                               class="toggle-checkbox" id="activate_lockout"
                                               value="activate_lockout"/>
                                        <span class="sui-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sui-box-footer">
                        <div class="sui-row">
                            <div class="sui-col-md-9">
                                <small>
                                    {{__("Note: These services will be configured with our recommended settings. You can change these at any time.")}}
                                </small>
                            </div>
                            <div class="sui-col-md-3">
                                <submit-button type="submit" :state="state" class="sui-button sui-button-blue">
                                    {{__("Get Started")}}
                                </submit-button>
                            </div>
                        </div>
                    </div>
                </form>
                <img v-if="maybeHideBranding" :src="assetUrl('/assets/img/defender-activator.svg')"
                     class="sui-image sui-image-center"/>
            </div>
            <div class="sui-box" v-else>
                <div class="sui-box-body">
                    <p>
                        {{__("Just a moment while Defender activates those services for you..")}}
                    </p>
                    <div class="sui-progress-block">
                        <div class="sui-progress">
                            <div class="sui-progress-text scan-progress-text sui-icon-loader sui-loading">
                                <span>0%</span>
                            </div>
                            <div class="sui-progress-bar scan-progress-bar">
                                <span style="width: 0%"></span>
                            </div>
                        </div>
                    </div>
                    <div class="sui-progress-state">
                        <span class="status-text"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler'

    export default {
        mixins: [base_helper],
        name: "quick-setup",
        data: function () {
            return {
                state: {
                    on_saving: false
                },
                model: {
                    activate_scan: true,
                    activate_lockout: true,
                },
                status: 'normal',
                nonces: dashboard.quick_setup.nonces,
                endpoints: dashboard.quick_setup.endpoints
            }
        },
        methods: {
            activate: function () {
                let self = this;
                this.httpPostRequest('activate', this.model, function (response) {
                    window.location.reload();
                })
            },
            skip: function () {
                this.httpPostRequest('skip', this.model, function (response) {
                    SUI.dialogs['activator'].hide();
                })
            }
        },
        mounted: function () {
            document.onreadystatechange = () => {
                if (document.readyState === "complete") {
                    if (SUI.dialogs['activator'] !== undefined) {
                        //this is refresh case
                        SUI.dialogs['activator'].show();
                    }
                }
            }
        }
    }
</script>