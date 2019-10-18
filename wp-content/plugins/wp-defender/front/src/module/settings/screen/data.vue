<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                {{__("Data & Settings")}}
            </h3>
        </div>
        <form method="post" @submit.prevent="updateSettings">
            <div class="sui-box-body">
                <p>
                    {{__("Control what to do with your settings and data. Settings are each module's configuration options, Data includes the stored information like logs, statistics other pieces of information stored over time.")}}
                </p>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("Uninstallation")}}
                        </span>
                        <span class="sui-description">
                        {{__("When you uninstall this plugin, what do you want to do with your settings and stored data?")}}
                        </span>
                    </div>

                    <div class="sui-box-settings-col-2">
                        <div class="sui-form-field">
                            <label class="sui-label">
                                {{__("Settings")}}
                            </label>
                            <div class="sui-side-tabs">
                                <div class="sui-tabs-menu">
                                    <label for="preserve"
                                           :class="{active:model.uninstall_settings==='preserve'}"
                                           class="sui-tab-item">
                                        <input type="radio" name="uninstall_settings" value="preserve"
                                               id="preserve"
                                               data-tab-menu=""
                                               v-model="model.uninstall_settings">
                                        {{__("Preserve")}}
                                    </label>
                                    <label for="reset"
                                           :class="{active:model.uninstall_settings==='reset'}"
                                           class="sui-tab-item">
                                        <input type="radio" name="uninstall_settings" value="reset"
                                               data-tab-menu=""
                                               id="reset" v-model="model.uninstall_settings">
                                        {{__("Reset")}}
                                    </label>
                                </div>
                            </div>
                            <label class="sui-label">
                                {{__("Data")}}
                            </label>
                            <div class="sui-side-tabs">
                                <div class="sui-tabs-menu">
                                    <label for="keep"
                                           :class="{active:model.uninstall_data==='keep'}"
                                           class="sui-tab-item">
                                        <input type="radio" name="uninstall_data" value="keep"
                                               id="keep"
                                               data-tab-menu=""
                                               v-model="model.uninstall_data">
                                        {{__("Keep")}}
                                    </label>
                                    <label for="remove"
                                           :class="{active:model.uninstall_data==='remove'}"
                                           class="sui-tab-item">
                                        <input type="radio" name="uninstall_data" value="remove"
                                               data-tab-menu=""
                                               id="remove" v-model="model.uninstall_data">
                                        {{__("Remove")}}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("Reset Settings")}}
                        </span>
                        <span class="sui-description">
                        {{__("Needing to start fresh? Use this button to roll back to the default settings.")}}
                        </span>
                    </div>

                    <div class="sui-box-settings-col-2">
                        <button type="button" data-a11y-dialog-show="reset-data-confirm"
                                class="sui-button-ghost sui-button">
                            <i class="sui-icon-undo" aria-hidden="true"></i>
                            {{__("Reset Settings")}}
                        </button>
                        <span class="sui-description">
                        {{__("Note: This will instantly revert all settings to their default states but will leave your data intact.")}}
                        </span>
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
        <div class="sui-dialog sui-dialog-sm" aria-hidden="true" tabindex="-1" id="reset-data-confirm">
            <div class="sui-dialog-overlay" data-a11y-dialog-hide></div>
            <div class="sui-dialog-content" aria-labelledby="dialogTitle" aria-describedby="dialogDescription"
                 role="dialog">
                <div class="sui-box" role="document">
                    <div class="sui-box-header">
                        <h3 class="sui-box-title">
                            {{__("Reset Settings")}}
                        </h3>
                        <div class="sui-actions-right">
                            <button data-a11y-dialog-hide class="sui-dialog-close"
                                    aria-label="Close this dialog window"></button>
                        </div>
                    </div>

                    <div class="sui-box-body">
                        <p>
                            {{__("Are you sure you want to reset Defender's settings back to the factory defaults?")}}
                        </p>
                    </div>
                    <form method="post" @submit.prevent="resetSettings">
                        <div class="sui-box-footer sui-space-between">
                            <button type="button" class="sui-button sui-button-ghost"
                                    data-a11y-dialog-hide="reset-data-confirm">
                                {{__("Cancel")}}
                            </button>
                            <submit-button type="submit" css-class="sui-button-ghost" :state="state">
                                <i class="sui-icon-undo" aria-hidden="true"></i>
                                {{__("Reset Settings")}}
                            </submit-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler';

    export default {
        mixins: [base_helper],
        name: "data-settings",
        data: function () {
            return {
                model: wdSettings.model.data,
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
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify(data)
                });
            },
            resetSettings: function () {
                let self = this;
                this.httpGetRequest('resetSettings', {}, function (response) {
                    if (response.success === true) {
                        self.$nextTick(() => {
                            SUI.dialogs['reset-data-confirm'].hide();
                        })
                    }
                });
            }
        }
    }
</script>