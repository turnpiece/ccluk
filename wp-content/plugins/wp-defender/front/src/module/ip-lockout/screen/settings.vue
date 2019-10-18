<template>
    <div class="sui-box" data-tab="settings">
        <div class="sui-box-header">
            <h3 class="sui-box-title">{{__("Settings")}}</h3>
        </div>
        <form method="post" @submit.prevent="updateSettings">
            <div class="sui-box-body">
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">{{__("Storage")}}</span>
                        <span class="sui-description">
                        {{__("Event logs are cached on your local server to speed up load times. You can choose how many days to keep logs for before they are removed.")}}
                        </span>
                    </div>

                    <div class="sui-box-settings-col-2">
                        <div class="sui-form-field">
                            <input size="8" v-model="model.storage_days" type="text"
                                   class="sui-form-control sui-field-has-suffix" id="storage_days"
                                   name="storage_days"/>
                            <span class="sui-field-suffix">{{__("days")}}</span>
                            <span class="sui-description">
                            {{__("Choose how many days of event logs you'd like to store locally.")}}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">{{__("Delete logs")}}</span>
                        <span class="sui-description">
                        {{__("If you wish to delete your current logs simply hit delete and this will wipe your logs clean.")}}
                        </span>
                    </div>

                    <div class="sui-box-settings-col-2">
                        <submit-button type="button" @click="emptyLogs" css-class="sui-button-ghost" :state="state">
                            <i class="sui-icon-save" aria-hidden="true"></i>
                            {{__("Delete Logs")}}
                        </submit-button>
                        <span class="sui-description">
                        {{__("Note: Defender will instantly remove all past event logs, you will not be able to get them back.")}}
                        </span>
                    </div>
                </div>
            </div>
            <div class="sui-box-footer">
                <div class="sui-actions-right">
                    <submit-button type="submit" :state="state" css-class="sui-button-blue">
                        <i class="sui-icon-save" aria-hidden="true"></i>
                        {{__("Save Changes")}}
                    </submit-button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import base_heper from '../../../helper/base_hepler';

    export default {
        mixins: [base_heper],
        name: "settings",
        props: ['view'],
        data: function () {
            return {
                model: iplockout.model.settings,
                state: {
                    on_saving: false,
                    ip_actioning: []
                },
                nonces: iplockout.nonces,
                endpoints: iplockout.endpoints,
            }
        },
        methods: {
            updateSettings: function () {
                let data = this.model;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify(data)
                });
            },
            emptyLogs: function () {
                let that = this;
                that.state.on_saving = true;
                let loading = false;
                let interval = setInterval(function () {
                    if (loading === false) {
                        loading = true;
                        that.httpPostRequest('emptyLogs', {}, function (response) {
                            if (response.success === false) {
                                loading = false;
                            } else {
                                clearInterval(interval);
                                that.state.on_saving = false;
                            }
                        })
                    }
                }, 1000)
            },
        }
    }
</script>