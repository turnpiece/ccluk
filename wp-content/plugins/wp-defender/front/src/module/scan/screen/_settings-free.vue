<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                {{__("Settings")}}
            </h3>
        </div>
        <form method="post" @submit.prevent="updateSettings">
            <div class="sui-box-body sui-upsell-items">
                <div class="sui-box-settings-row no-border no-padding-bottom no-margin-bottom">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">{{__("Scan Types")}}</span>
                        <span class="sui-description">
                        {{__("Choose the scan types you would like to include in your default scan. It's recommended you enable all types.")}}
                        </span>
                    </div>
                    <div class="sui-box-settings-col-2">
                        <div class="sui-form-field">
                            <label class="sui-toggle">
                                <input role="presentation" v-model="model.scan_core" type="checkbox" name="scan_core"
                                       class="toggle-checkbox"
                                       id="core-scan"/>
                                <span class="sui-toggle-slider"></span>
                            </label>
                            <label for="core-scan" class="sui-toggle-label">
                                {{__("WordPress Core")}}
                            </label>
                            <p class="sui-description sui-toggle-content">
                                {{__("Defender checks for any modifications or additions to WordPress core files.")}}
                            </p>
                        </div>
                        <div class="sui-form-field">
                            <div class="relative">
                                <label class="sui-toggle">
                                    <input disabled role="presentation" type="checkbox"
                                           class="toggle-checkbox"/>
                                    <span class="sui-toggle-slider"></span>
                                </label>
                                <label class="sui-toggle-label">
                                    {{__("Plugins & Themes")}}
                                </label>
                                <span class="sui-tag sui-tag-pro">Pro</span>
                            </div>
                            <p class="sui-description sui-toggle-content">
                                {{__("Defender looks for publicly reported vulnerabilities in your installed plugins and themes.")}}
                            </p>
                        </div>
                        <div class="sui-form-field">
                            <div class="relative">
                                <label class="sui-toggle">
                                    <input role="presentation" type="checkbox" disabled
                                           class="toggle-checkbox"
                                    />
                                    <span class="sui-toggle-slider"></span>
                                </label>
                                <label class="sui-toggle-label">
                                    {{__("Suspicious Code")}}
                                </label>
                                <span class="sui-tag sui-tag-pro">Pro</span>
                            </div>
                            <p class="sui-description sui-toggle-content">
                                {{__("Defender looks inside all of your files for suspicious and potentially harmful code.")}}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="sui-box-settings-row sui-upsell-row">
                    <img class="sui-image sui-upsell-image" :src="assetUrl('/assets/img/scanning-free-man.svg')">
                    <div class="sui-upsell-notice">
                        <p>
                            {{__("Defender Pro allows you to scan your entire file structure for malicious code, including non-WordPress files. This feature is included in a WPMU DEV monthly membership along with 24/7 support and lots of handy site management tools")}}  – <a target="_blank" :href="campaign_url('defender_filescanning_settings_upsell_link')">{{__("Try it all FREE today!")}}</a>
                        </p>
                    </div>
                </div>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">{{__("Maximum included file size")}}</span>
                        <span class="sui-description">
                            {{__("Defender will skip any files larger than this size. The smaller the number, the faster Defender will scan your website.")}}
                        </span>
                    </div>

                    <div class="sui-box-settings-col-2">
                        <div class="sui-form-field">
                            <div class="sui-form-field">
                                <input type="number" size="4" class="sui-form-control sui-input-sm sui-field-has-suffix"
                                       v-model="model.max_filesize" name="max_filesize">
                                <span class="sui-field-suffix">Mb</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sui-box-footer">
                <div class="sui-actions-right">
                    <submit-button type="submit" css-class="sui-button-blue" :state="state">
                        <i class="sui-icon-save" aria-hidden="true"></i>
                        {{__("Save Changes")}}
                    </submit-button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import base_hepler from "../../../helper/base_hepler";

    export default {
        mixins: [base_hepler],
        name: "settings",
        data: function () {
            return {
                model: scanData.model.settings,
                nonces: scanData.nonces,
                endpoints: scanData.endpoints,
                state: {
                    on_saving: false
                },
            }
        },
        methods: {
            updateSettings: function () {
                let data = this.model;
                this.httpPostRequest('updateSettings', {
                    'data': JSON.stringify(data)
                });
            },
        }
    }
</script>