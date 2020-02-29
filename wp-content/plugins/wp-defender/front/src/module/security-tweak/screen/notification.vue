<template>
    <div v-show="view==='notification'" class="sui-box" @on:update_recipients="updateRecipients">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                {{__("Notifications")}}
            </h3>
        </div>
        <form method="post" @submit.prevent="formSubmission">
            <div class="sui-box-body">
                <p>
                    {{__("Get email notifications if/when a security tweak needs fixing.")}}
                </p>

                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">{{__("Enable notifications")}}</span>
                        <span class="sui-description">
                            {{__("Enabling this option will ensure you don’t need to check in to see that all your security tweaks are still active.")}}
                        </span>
                    </div>
                    <div class="sui-box-settings-col-2">
                        <div class="sui-form-field">
                            <div class="sui-side-tabs">
                                <div class="sui-tabs-menu">
                                    <label for="notification_on"
                                           :class="{active:model.notification===true}"
                                           class="sui-tab-item">
                                        <input type="radio" value="true" id="notification_on"
                                               data-tab-menu="notification-box" :value="true"
                                               v-model="model.notification">
                                        {{__("On")}}
                                    </label>
                                    <label for="notification_off"
                                           :class="{active:model.notification===false}"
                                           class="sui-tab-item">
                                        <input type="radio" :value="false"
                                               data-tab-menu=""
                                               id="notification_off" v-model="model.notification">
                                        {{__("Off")}}
                                    </label>
                                </div>
                                <div class="sui-tabs-content">
                                    <div class="sui-tab-content sui-tab-boxed"
                                         :class="{active:model.notification===true}"
                                         id="notification-box"
                                         data-tab-content="notification-box">
                                        <p class="sui-p-small">
                                            {{__("By default, we will only notify the recipients below when a security tweak hasn’t been actioned for 7 days.")}}
                                        </p>
                                        <recipients id="tweaks_recipients" @update:recipients="updateRecipients"
                                                    v-bind:recipients="model.recipients"></recipients>
                                        <label for="notification_repeat" class="sui-checkbox">
                                            <input v-model="model.notification_repeat" type="checkbox"
                                                   id="notification_repeat" name="notification_repeat"
                                                   :true-value="true"
                                                   :false-value="false"/>
                                            <span aria-hidden="true"></span>
                                            <span>{{__("Send reminders every 24 hours if fixes still hasn’t been actioned.")}}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sui-box-footer">
                <div class="sui-actions-right">
                    <submit-button type="submit" css-class="sui-button-blue save-changes" :state="state">
                        <i class="sui-icon-save" aria-hidden="true"></i>
                        {{__("Save Changes")}}
                    </submit-button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import helper from '../../../helper/base_hepler';
    import recipients from '../../../component/recipients';

    export default {
        mixins: [helper],
        name: "notification",
        props: ['view'],
        components: {
            'recipients': recipients
        },
        data: function () {
            return {
                model: security_tweaks.model,
                nonces: security_tweaks.nonces,
                endpoints: security_tweaks.endpoints,
                state: {
                    on_saving: false
                },
            }
        },
        methods: {
            formSubmission: function () {
                this.state.on_saving = true;
                let data = this.model;
                let that = this;
                let url = ajaxurl + '?action=' + this.endpoints['updateSettings'] + '&_wpnonce=' + this.nonces['updateSettings'];
                jQuery.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        'data': JSON.stringify(data)
                    },
                    success: function (data) {
                        that.state.on_saving = false;
                        if (data.data.message !== undefined) {
                            if (data.success) {
                                Defender.showNotification('success', data.data.message);
                            } else {
                                Defender.showNotification('error', data.data.message);
                            }
                        }
                    }
                })
            },
            updateRecipients: function (recipients) {
                this.model.report_receipts = recipients;
            },
        }
    }
</script>