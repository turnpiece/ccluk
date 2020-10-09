<template>
  <div class="sui-box">
    <div class="sui-box-header">
      <h3 class="sui-box-title">
        {{ __("Notifications") }}
      </h3>
    </div>
    <form method="post" @submit.prevent="updateSettings">
      <div class="sui-box-body">
        <p>
          {{ __("Get email notifications when Defender has finished manual files scans.") }}
        </p>
        <div class="sui-box-settings-row">
          <div class="sui-box-settings-col-1">
            <span class="sui-settings-label">{{ __("Enable notifications") }}</span>
            <span class="sui-description">
                            {{ __("Enabling this option will ensure you get the results of every scan once they're completed.") }}
                        </span>
          </div>
          <div class="sui-box-settings-col-2">
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
                  {{ __("By default, we will only notify the recipients below when there is an issue from your file scan. Enable this option to send emails even when no issues are detected.") }}
                </p>
                <label class="sui-toggle">
                  <input role="presentation" type="checkbox" class="toggle-checkbox"
                         id="alwaysSendNotification" v-model="model.always_send_notification"/>
                  <span class="sui-toggle-slider"></span>
                </label>
                <label for="alwaysSendNotification" class="sui-toggle-label">
                  {{ __("Also send notifications when no issues are detected.") }}
                </label>
                <div class="margin-top-30">
                  <recipients id="notification_dialog" @update:recipients="updateRecipients"
                              v-bind:recipients="model.recipients_notification"></recipients>
                </div>
                <div class="sui-field-list sui-flushed no-border">
                  <div class="sui-field-list-header">
                    <h3 class="sui-field-list-title">{{ __("Email Templates") }}</h3>
                  </div>
                  <div class="sui-field-list-body">
                    <div class="sui-field-list-item">
                      <label class="sui-field-list-item-label">
                        <strong>
                          {{ __("When an issue is found") }}
                        </strong>
                      </label>
                      <button
                          data-modal-open="issue-found"
                          data-modal-mask="true"
                          data-esc-close="false"
                          class="sui-button-icon"
                      >
                        <i class="sui-icon-pencil" aria-hidden="true"></i>
                      </button>
                    </div>
                    <div class="sui-field-list-item">
                      <label class="sui-field-list-item-label">
                        <strong>
                          {{ __("When no issues are found") }}
                        </strong>
                      </label>
                      <button
                          data-modal-open="all-ok"
                          data-modal-mask="true"
                          data-esc-close="false"
                          class="sui-button-icon"
                      >
                        <i class="sui-icon-pencil" aria-hidden="true"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </template>
            </sidetab>
          </div>
        </div>
      </div>
      <div class="sui-box-footer">
        <div class="sui-actions-right">
          <submit-button type="submit" css-class="sui-button-blue" :state="state">
            <i class="sui-icon-save" aria-hidden="true"></i>
            {{ __("Save Changes") }}
          </submit-button>
        </div>
      </div>
    </form>
    <div class="sui-modal sui-modal-lg">
      <div
          role="dialog"
          id="all-ok"
          class="sui-modal-content"
          aria-modal="true"
          aria-labelledby="modal-title-unique-id"
          aria-describedby="modal-description-unique-id"
      >
        <div class="sui-box" role="document">
          <form method="post" @submit.prevent="updateEmailTemplate(['email_subject','email_all_ok'])">
            <div class="sui-box-header">
              <h3 class="sui-box-title">
                {{ __("Edit Template") }}
              </h3>
              <div class="sui-actions-right">
                <button data-modal-close="" class="sui-button-icon"
                        aria-label="Close this dialog window">
                  <i class="sui-icon-close"></i>
                </button>
              </div>
            </div>

            <div class="sui-box-body">
              <p>
                {{ __("Edit the email copy for when Defender finishes a scan and sends an email summary report.") }}
              </p>
              <div class="sui-row">
                <div class="sui-col">
                  <div class="sui-form-field">
                    <label class="sui-label">
                      {{ __("Subject") }}
                    </label>
                    <input type="text" class="sui-form-control" name="email_subject"
                           v-model="model.email_subject">
                  </div>
                </div>
              </div>
              <div class="sui-row">
                <div class="sui-col">
                  <div class="sui-form-field">
                    <label class="sui-label">
                      {{ __("Body") }}
                    </label>
                    <textarea rows="12" class="sui-form-control" name="email_all_ok"
                              v-model="model.email_all_ok"></textarea>
                  </div>
                </div>
              </div>
              <div class="sui-form-field">
                <label class="sui-label">
                  {{ __("Available variables") }}
                </label>
                <span class="sui-tag">{USER_NAME}</span>
                <span class="sui-tag">{SITE_URL}</span>
                <span class="sui-tag">{ISSUES_COUNT}</span>
                <span class="sui-tag">{ISSUES_LIST}</span>
              </div>
            </div>

            <div class="sui-box-footer">
              <div class="sui-actions-left">
                <button class="sui-button" type="button" data-modal-close="">
                  {{ __("Cancel") }}
                </button>
              </div>
              <div class="sui-actions-right">
                <submit-button type="submit" css-class="sui-button-blue" :state="state">
                  <i class="sui-icon-save" aria-hidden="true"></i>
                  {{ __("Save Changes") }}
                </submit-button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="sui-modal sui-modal-lg">

      <div
          role="dialog"
          id="issue-found"
          class="sui-modal-content"
          aria-modal="true"
          aria-labelledby="modal-title-unique-id"
          aria-describedby="modal-description-unique-id"
      >
        <div class="sui-box" role="document">
          <form method="post" @submit.prevent="updateEmailTemplate(['email_subject_issue','email_has_issue'])">
            <div class="sui-box-header">
              <h3 class="sui-box-title">
                {{ __("Edit Template") }}
              </h3>
              <div class="sui-actions-right">
                <button data-modal-close="" class="sui-button-icon"
                        aria-label="Close this dialog window">
                  <i class="sui-icon-close"></i>
                </button>
              </div>
            </div>

            <div class="sui-box-body">
              <p>
                {{ __("Edit the email copy for when Defender finishes a scan and sends an email summary report.") }}
              </p>
              <div class="sui-row">
                <div class="sui-col">
                  <div class="sui-form-field">
                    <label class="sui-label">
                      {{ __("Subject") }}
                    </label>
                    <input type="text" class="sui-form-control" name="email_subject_issue"
                           v-model="model.email_subject_issue">
                  </div>
                </div>
              </div>
              <div class="sui-row">
                <div class="sui-col">
                  <div class="sui-form-field">
                    <label class="sui-label">
                      {{ __("Subject") }}
                    </label>
                    <textarea rows="12" class="sui-form-control" v-model="model.email_has_issue"
                              name="email_has_issue"></textarea>
                  </div>
                </div>
              </div>
              <div class="sui-form-field">
                <label class="sui-label">
                  {{ __("Available variables") }}
                </label>
                <span class="sui-tag">{USER_NAME}</span>
                <span class="sui-tag">{SITE_URL}</span>
                <span class="sui-tag">{ISSUES_COUNT}</span>
                <span class="sui-tag">{ISSUES_LIST}</span>
              </div>
            </div>

            <div class="sui-box-footer">
              <div class="sui-actions-left">
                <button type="button" class="sui-button" data-modal-close="">
                  {{ __("Cancel") }}
                </button>
              </div>
              <div class="sui-actions-right">
                <submit-button type="submit" css-class="sui-button-blue" :state="state">
                  <i class="sui-icon-save" aria-hidden="true"></i>
                  {{ __("Save Changes") }}
                </submit-button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import base_hepler from "../../../helper/base_hepler";
import recipients from "../../../component/recipients"
import Sidetab from "../../../component/sidetab";

export default {
  mixins: [base_hepler],
  name: "notification",
  data: function () {
    return {
      model: scanData.model.notification,
      state: {
        on_saving: false
      },
      nonces: scanData.nonces,
      endpoints: scanData.endpoints,
    }
  },
  methods: {
    updateRecipients: function (recipients) {
      this.model.receiptsNotification = recipients;
    },
    updateSettings: function () {
      let data = this.model;
      delete data.email_subject;
      delete data.email_subject_issue;
      delete data.email_all_ok;
      delete data.email_has_issue;

      this.httpPostRequest('updateSettings', {
        'data': JSON.stringify(data)
      });
    },
    updateEmailTemplate: function (fields) {
      let data = {};
      let self = this;
      for (var i = 0; i < fields.length; i++) {
        data[fields[i]] = this.model[fields[i]];
      }
      this.httpPostRequest('updateSettings', {
        'data': JSON.stringify(data)
      }, function () {
        self.$nextTick(() => {
          SUI.closeModal()
        })
      })
    }
  },
  components: {
    Sidetab,
    recipients: recipients
  }
}
</script>