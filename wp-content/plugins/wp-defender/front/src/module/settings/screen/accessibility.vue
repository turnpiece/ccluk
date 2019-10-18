<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                {{__("Accessibility")}}
            </h3>
        </div>
        <form method="post" @submit.prevent="updateSettings">
            <div class="sui-box-body">
                <p>
                    {{__("Enable support for any accessibility enhancements available in the plugin interface.")}}
                </p>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("High Contrast Mode")}}
                        </span>
                        <span class="sui-description">
                        {{__("Increase the visibility and accessibility of elements and components of this plugin's interface to meet WCAG AAA requirements.")}}
                        </span>
                    </div>
                    <div class="sui-box-settings-col-2">
                        <div class="sui-form-field">
                            <label class="sui-toggle">
                                <input role="presentation" type="checkbox" name="high_contrast_mode"
                                       class="toggle-checkbox"
                                       id="high_contrast_mode" v-model="model.high_contrast_mode"/>
                                <span class="sui-toggle-slider"></span>
                            </label>
                            <label for="high_contrast_mode" class="sui-toggle-label">
                                {{__("Enable high contrast mode")}}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sui-box-footer">
                <div class="sui-actions-right">
                    <submit-button type="submit" class="sui-button-blue" :state="state">
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
        name: "accessibility",
        data: function () {
            return {
                model: wdSettings.model.accessibility,
                state: {
                    on_saving: false
                },
                nonces: wdSettings.nonces,
                endpoints: wdSettings.endpoints
            }
        },
        methods: {
            updateSettings: function () {
                let data = this.model;
                let self = this;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify(data)
                }, function () {
                    self.$nextTick(() => {
                        self.$root.high_contrast = data.high_contrast_mode
                    })
                });
            }
        }
    }
</script>