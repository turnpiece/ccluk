<template>
    <div class="sui-box" data-tab="login_lockout" v-if="(model.login_protection===false || model.login_protection===0)">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                {{__("Login Protection")}}
            </h3>
        </div>
        <div class="sui-message">
            <img v-if="!maybeHideBranding()" :src="assetUrl('assets/img/lockout-man.svg')" class="sui-image"/>
            <div class="sui-message-content">
                <p>
                    {{__("Put a stop to hackers trying to randomly guess your login credentials. Defender will lock out users after a set number of failed login attempts.")}}
                </p>
                <form method="post" @submit.prevent="toggle(true,'login_protection')" class="ip-frm">
                    <submit-button type="submit" css-class="sui-button-blue" :state="state">
                        {{__("Activate")}}
                    </submit-button>
                </form>
            </div>
        </div>
    </div>
    <div class="sui-box" v-else-if="(model.login_protection===true || model.login_protection===1)">
        <form method="post" id="settings-frm" class="ip-frm" @submit.prevent="updateSettings">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    {{__("Login Protection")}}
                </h3>
            </div>
            <div class="sui-box-body">
                <p>
                    {{__("Put a stop to hackers trying to randomly guess your login credentials. Defender will lock out users after a set number of failed login attempts.")}}
                </p>
                <div v-if="summary_data.ip.day > 0" class="sui-notice sui-notice-error">
                    <p v-html="notification"></p>
                </div>
                <div v-else class="sui-notice sui-notice-info">
                    <p v-html="notification"></p>
                </div>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">{{__("Threshold")}}</span>
                        <span class="sui-description">{{__("Specify how many failed login attempts within a specific time period will trigger a lockout.")}}</span>
                    </div>
                    <div class="sui-box-settings-col-2">
                        <div class="sui-form-field">
                            <div class="sui-row">
                                <div class="sui-col-md-2">
                                    <label class="sui-label">{{__("Failed logins")}}</label>
                                    <input size="8" v-model="model.login_protection_login_attempt"
                                           type="text"
                                           class="sui-form-control sui-input-sm sui-field-has-suffix"
                                           id="login_protection_login_attempt"
                                           name="login_protection_login_attempt"/>
                                </div>
                                <div class="sui-col-md-3">
                                    <label class="sui-label">
                                        {{__("Timeframe")}}
                                    </label>
                                    <input size="8" v-model="model.login_protection_lockout_timeframe"
                                           id="login_lockout_timeframe"
                                           name="login_protection_lockout_timeframe" type="text"
                                           class="sui-form-control sui-input-sm sui-field-has-suffix">
                                    <span class="sui-field-suffix">{{__("seconds")}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">{{__("Duration")}}</span>
                        <span class="sui-description">{{__("Choose how long you'd like to ban the locked out user for.")}}</span>
                    </div>
                    <div class="sui-box-settings-col-2">
                        <div class="sui-side-tabs">
                            <div class="sui-tabs-menu">
                                <label for="timeframe"
                                       :class="{active:model.login_protection_lockout_ban===false}"
                                       class="sui-tab-item">
                                    <input type="radio" name="login_protection_lockout_ban" :value="false"
                                           id="timeframe"
                                           data-tab-menu="timeframe-box"
                                           v-model="model.login_protection_lockout_ban">
                                    {{__("Timeframe")}}
                                </label>
                                <label for="permanent"
                                       :class="{active:model.login_protection_lockout_ban===true}"
                                       class="sui-tab-item">
                                    <input type="radio" name="login_protection_lockout_ban" :value="true"
                                           data-tab-menu=""
                                           id="permanent" v-model="model.login_protection_lockout_ban">
                                    {{__("Permanent")}}
                                </label>
                            </div>

                            <div class="sui-tabs-content">
                                <div class="sui-tab-content sui-tab-boxed"
                                     :class="{active:model.login_protection_lockout_ban===false}"
                                     id="timeframe-box"
                                     data-tab-content="timeframe-box">
                                    <div class="sui-row">
                                        <div class="sui-col-md-3">
                                            <input v-model="model.login_protection_lockout_duration"
                                                   size="4"
                                                   name="login_protection_lockout_duration"
                                                   id="login_protection_lockout_duration" type="text"
                                                   class="sui-form-control"/>
                                        </div>
                                        <div class="sui-col-md-4">
                                            <select id="lockout-duration-unit" name="login_protection_lockout_duration_unit"
                                                    class="jquery-select sui-select"
                                                    data-minimum-results-for-search="Infinity"
                                                    v-model="model.login_protection_lockout_duration_unit">
                                                <option value="seconds">{{__("Seconds")}}</option>
                                                <option value="minutes">{{__("Minutes")}}</option>
                                                <option value="hours">{{__("Hours")}}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">{{__("Message")}}</span>
                        <span class="sui-description">{{__("Customize the message locked out users will see.")}}</span>
                    </div>
                    <div class="sui-box-settings-col-2">
                        <div class="sui-form-field">
                            <label class="sui-label">{{__("Custom message")}}</label>
                            <textarea name="login_protection_lockout_message"
                                      v-model="model.login_protection_lockout_message" class="sui-form-control"
                                      id="login_protection_lockout_message"></textarea>
                            <span class="sui-description" v-html="demo_link">
                            </span>
                        </div>
                    </div>
                </div>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">{{__("Banned usernames")}}</span>
                        <span class="sui-description">
                            {{__("It is highly recommended you avoid using the default username ‘admin'. Use this tool to automatically lockout and ban users who try to login with common usernames.")}}
                        </span>
                    </div>
                    <div class="sui-box-settings-col-2">
                        <div class="sui-form-field">
                            <label class="sui-label">{{__("Banned usernames")}}</label>
                            <textarea class="sui-form-control" v-model="model.username_blacklist"
                                      :placeholder="__('Type usernames, one per line')"
                                      id="username_blacklist" name="username_blacklist"
                                      rows="8"></textarea>
                            <span class="sui-description" v-html="banned_username">
                            </span>
                        </div>
                    </div>
                </div>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        {{__("Deactivate")}}
                    </span>
                        <span class="sui-description">
                            {{__("If you no longer want to use this feature you can turn it off at any time.")}}
                        </span>
                    </div>
                    <div class="sui-box-settings-col-2">
                        <submit-button type="button" css-class="sui-button-ghost" :state="state"
                                       @click="toggle(false,'login_protection')">
                            {{__("Deactivate")}}
                        </submit-button>
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
        name: "ip-lockout",
        props: ['view'],
        data: function () {
            return {
                model: iplockout.model.ip_lockout,
                summary_data: iplockout.summaryData,
                state: {
                    on_saving: false
                },
                nonces: iplockout.nonces,
                endpoints: iplockout.endpoints,
                misc: iplockout.misc
            }
        },
        methods: {
            toggle: function (value, type = 'login_protection') {
                let that = this;
                let envelope = {};
                envelope[type] = value;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify(envelope)
                }, function () {
                    that.model[type] = value;
                    if (value === true) {
                        that.$nextTick(() => {
                            that.rebindSUI();
                        })
                    }
                })
            },
            updateSettings: function () {
                let data = this.model;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify(data)
                });
            }
        },
        computed: {
            notification: function () {
                return this.summary_data.ip.day === 0 ?
                    this.__("Login protection is enabled. There are no lockouts logged yet.") :
                    this.vsprintf(this.__("There have been %s lockouts in the last 24 hours. <a href=\"%s\"><strong>View log</strong></a>."), this.summary_data.ip.day, this.adminUrl('admin.php?page=wdf-ip-lockout&view=logs'))
            },
            banned_username: function () {
                return this.vsprintf(this.__("We recommend adding the usernames <strong>admin</strong>, <strong>administrator</strong> and your hostname <strong>%s</strong> as these are common for bots to try logging in with. One username per line"), this.misc.host)
            },
            demo_link: function () {
                return this.vsprintf(this.__("This message will be displayed across your website during the lockout period. See a quick preview <a href=\"%s\">here</a>."), this.siteUrl('?def-lockout-demo=1&type=demo'));
            }
        },
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