<template>
    <div class="sui-box" data-tab="notification">
        <div class="sui-box-header">
            <h3 class="sui-box-title">{{__("Notification")}}</h3>
        </div>
        <form method="post" @submit.prevent="updateSettings">
            <div class="sui-box-body">
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        {{__("Email Notifications")}}
                    </span>
                        <span class="sui-description">
                            {{__("Choose which lockout notifications you wish to be notified about. These are sent instantly.")}}
                        </span>
                    </div>

                    <div class="sui-box-settings-col-2">
                        <div class="sui-form-field">
                            <label class="sui-toggle">
                                <input role="presentation" type="checkbox"
                                       class="toggle-checkbox" v-model="model.login_lockout_notification"
                                       id="login_lockout_notification" :true-value="true" :false-value="false"/>
                                <span class="sui-toggle-slider"></span>
                            </label>
                            <label for="login_lockout_notification" class="sui-toggle-label">
                                {{__("Login Protection Lockout")}}
                            </label>
                            <p class="sui-description">
                                {{__("When a user or IP is locked out for trying to access your login area.")}}
                            </p>
                        </div>
                        <div class="sui-form-field">
                            <label class="sui-toggle">
                                <input role="presentation" type="checkbox" v-model="model.ip_lockout_notification"
                                       class="toggle-checkbox"
                                       id="ip_lockout_notification" :true-value="true" :false-value="false"/>
                                <span class="sui-toggle-slider"></span>
                            </label>
                            <label for="ip_lockout_notification" class="sui-toggle-label">
                                {{__("404 Detection Lockout")}}
                            </label>
                            <p class="sui-description">
                                {{__("When a user or IP is locked out for repeated hits on non-existent files.")}}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                        {{__("Email Recipients")}}
                        </span>
                        <span class="sui-description">
                        {{__("Choose which of your website’s users will receive lockout notifications via email.")}}
                        </span>
                    </div>

                    <div class="sui-box-settings-col-2">
                        <recipients id="notification_dialog" v-bind:recipients="model.receipts"
                                    @update:recipients="updateRecipients"></recipients>
                    </div>
                </div>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                        {{__("Repeat Lockouts")}}
                        </span>
                        <span class="sui-description">
                        {{__("If you’re getting too many emails from IPs who are repeatedly being locked out you can turn them off for a period of time.")}}
                        </span>
                    </div>

                    <div class="sui-box-settings-col-2">
                        <label class="sui-toggle">
                            <input role="presentation" type="checkbox" :true-value="true" :false-value="false"
                                   class="toggle-checkbox" v-model="model.cooldown_enabled"
                                   id="cooldown_enabled"/>
                            <span class="sui-toggle-slider"></span>
                        </label>
                        <label for="cooldown_enabled" class="sui-toggle-label">
                            {{__("Limit email notifications for repeat lockouts")}}
                        </label>
                        <div class="sui-border-frame sui-toggle-content">
                            <div class="sui-form-field">
                                <label class="sui-label"><strong>{{__("Threshold")}}</strong> {{__("- The number of lockouts before we turn off emails")}}</label>
                                <select class="jquery-select sui-select" id="cooldown_number_lockout"
                                        name="cooldown_number_lockout"
                                        data-minimum-results-for-search="Infinity"
                                        v-model="model.cooldown_number_lockout">
                                    <option value="1">1</option>
                                    <option value="3">3</option>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                </select>
                            </div>
                            <div class="sui-form-field">
                                <label class="sui-label"><strong>{{__("Cool Off Period")}}</strong> {{__("- For how long should we turn them off?")}}</label>
                                <select class="jquery-select sui-select" id="cooldown_period" name="cooldown_period"
                                        data-minimum-results-for-search="Infinity"
                                        v-model="model.cooldown_period">
                                    <option value="1">{{__("1 hour")}}</option>
                                    <option value="2">{{__("2 hours")}}</option>
                                    <option value="6">{{__("6 hours")}}</option>
                                    <option value="12">{{__("12 hours")}}</option>
                                    <option value="24">{{__("24 hours")}}</option>
                                    <option value="36">{{__("36 hours")}}</option>
                                    <option value="48">{{__("48 hours")}}</option>
                                    <option value="168">{{__("7 days")}}</option>
                                    <option value="720">{{__("30 days")}}</option>
                                </select>
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
    import base_helper from '../../../helper/base_hepler';
    import recipients from '../../../component/recipients';

    export default {
        mixins: [base_helper],
        name: "notification",
        props: ['view'],
        data: function () {
            return {
                model: iplockout.model.notification,
                nonces: iplockout.nonces,
                endpoints: iplockout.endpoints,
                state: {
                    on_saving: false
                },
            }
        },
        components: {
            'recipients': recipients
        },
        methods: {
            updateSettings: function () {
                let data = this.model;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify(data)
                });
            },
            updateRecipients: function (recipients) {
                this.model.receipts = recipients;
            }
        },
        computed: {},
        mounted: function () {
            let self = this;
            jQuery('.jquery-select').change(function () {
                let value = jQuery(this).val();
                let key = jQuery(this).attr('name');
                self.model[key] = value;
            })
        }
    }
</script>
