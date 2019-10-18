<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                {{__("General")}}
            </h3>
        </div>
        <form method="post" @submit.prevent="updateSettings">
            <div class="sui-box-body">
                <p>
                    {{__("Configure general settings for this plugin.")}}
                </p>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("Translations")}}
                        </span>
                        <span class="sui-description">
                            {{__("By default, Defender will use the language you'd set in your")}} <a
                                :href="settingsUrl">{{__("WordPress Admin Settings")}}</a> {{__("if a matching translation is available.")}}
                        </span>
                    </div>
                    <div class="sui-box-settings-col-2">
                        <div class="sui-form-field">
                            <label class="sui-label">
                                {{__("Active translation")}}
                            </label>
                            <input type="text" :value="model.translate" disabled class="sui-form-control">
                            <p class="sui-description">
                                {{__("Not using your language, or have improvements? Help us improve translations by providing your own improvements here.")}}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("Usage Tracking")}}
                        </span>
                        <span class="sui-description">
                        {{__("Help make Defender better by letting our designers learn how you're using the plugin.")}}
                        </span>
                    </div>

                    <div class="sui-box-settings-col-2">
                        <div class="sui-form-field">
                            <label class="sui-toggle">
                                <input role="presentation" v-model="model.usage_tracking" type="checkbox"
                                       name="usage_tracking" class="toggle-checkbox"
                                       id="usage_tracking"/>
                                <span class="sui-toggle-slider"></span>
                            </label>
                            <label for="usage_tracking" class="sui-toggle-label">
                                {{__("Allow usage tracking")}}
                            </label>
                            <p class="sui-description sui-toggle-content">
                                {{__("Note: Usage tracking is completely anonymous. We are only tracking what features you are/aren't using to make our feature decisions more informed.")}}
                            </p>
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
    import base_helper from '../../../helper/base_hepler';

    export default {
        mixins: [base_helper],
        name: "general",
        data: function () {
            return {
                model: wdSettings.model.general,
                state: {
                    on_saving: false
                },
                nonces: wdSettings.nonces,
                endpoints: wdSettings.endpoints,
                misc: wdSettings.misc
            }
        },
        methods: {
            updateSettings: function () {
                let data = this.model;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify(data)
                });
            }
        },
        computed: {
            settingsUrl: function () {
                return this.misc.setting_url
            }
        }
    }
</script>