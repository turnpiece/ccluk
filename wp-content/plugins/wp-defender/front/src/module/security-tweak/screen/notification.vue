<template>
  <div v-show="view==='notification'" class="sui-box" @on:update_recipients="updateRecipients">
    <div class="sui-box-header">
      <h3 class="sui-box-title">
        {{ __("Notifications") }}
      </h3>
    </div>
    <form method="post" @submit.prevent="formSubmission">
      <div class="sui-box-body">
        <p>
          {{ __("Get email notifications if/when a security tweak needs fixing.") }}
        </p>

        <div class="sui-box-settings-row">
          <div class="sui-box-settings-col-1">
            <span class="sui-settings-label">{{ __("Enable notifications") }}</span>
            <span class="sui-description">
                            {{ __("Enabling this option will ensure you don’t need to check in to see that all your security tweaks are still active.") }}
                        </span>
          </div>
          <div class="sui-box-settings-col-2">
            <div class="sui-form-field">
              <sidetab slug="notification" :active="model.notification" @selected="model.notification = $event"
                       :labels="[
                                   {
                                      text:__('On'),
                                      value:true,
                                      mute:false
                                   },
                                   {
                                      text:__('Off'),
                                      value:false,
                                      mute:true
                                   }
                               ]"
              >
                <template v-slot:true>
                  <p class="sui-p-small">
                    {{ __("By default, we will only notify the recipients below when a security tweak hasn’t been actioned for 7 days.") }}
                  </p>
                  <recipients id="tweaks_recipients" @update:recipients="updateRecipients"
                              v-bind:recipients="model.recipients"></recipients>
                  <label for="notification_repeat" class="sui-checkbox">
                    <input v-model="model.notification_repeat" type="checkbox"
                           id="notification_repeat" name="notification_repeat"
                           :true-value="true"
                           :false-value="false"/>
                    <span aria-hidden="true"></span>
                    <span>{{ __("Send reminders every 24 hours if fixes still hasn’t been actioned.") }}</span>
                  </label>
                </template>
              </sidetab>
            </div>
          </div>
        </div>
      </div>
      <div class="sui-box-footer">
        <div class="sui-actions-right">
          <submit-button type="submit" css-class="sui-button-blue save-changes" :state="state">
            <i class="sui-icon-save" aria-hidden="true"></i>
            {{ __("Save Changes") }}
          </submit-button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import helper from '../../../helper/base_hepler';
import recipients from '../../../component/recipients';
import Sidetab from "../../../component/sidetab";

export default {
  mixins: [helper],
  name: "notification",
  props: ['view'],
  components: {
    Sidetab,
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