<template>
  <div class="sui-box">
    <div class="sui-box-header">
      <h3 class="sui-box-title">
        {{ __("Data & Settings") }}
      </h3>
    </div>
    <form method="post" @submit.prevent="updateSettings">
      <div class="sui-box-body">
        <p>
          {{ __("Control what to do with your settings and data. Settings are each module's configuration options, Data includes the stored information like logs, statistics other pieces of information stored over time.") }}
        </p>
        <div class="sui-box-settings-row">
          <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{ __("Uninstallation") }}
                        </span>
            <span class="sui-description">
                        {{ __("When you uninstall this plugin, what do you want to do with your settings and stored data?") }}
                        </span>
          </div>

          <div class="sui-box-settings-col-2">
            <div class="sui-form-field">
              <label class="sui-label">
                {{ __("Settings") }}
              </label>
              <sidetab :active="model.uninstall_settings" slug="uninstall_settings"
                       @selected="model.uninstall_settings = $event" :labels="[
                  {
                    value:'preserve',
                    mute:true,
                    text:__('Preserve')
                  },
                  {
                    value:'reset',
                    mute:true,
                    text:__('Reset')
                  },
              ]"></sidetab>
              <label class="sui-label">
                {{ __("Data") }}
              </label>
              <sidetab :active="model.uninstall_data" slug="uninstall_data"
                       @selected="model.uninstall_data = $event"
                       :labels="[
                           {
                             value:'keep',
                             text:__('Keep'),
                             mute:true
                           },
                           {
                             value:'remove',
                             text:__('Remove'),
                             mute:true
                           },
                       ]">
              </sidetab>
            </div>
          </div>
        </div>
        <div class="sui-box-settings-row">
          <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{ __("Reset Settings") }}
                        </span>
            <span class="sui-description">
                        {{ __("Needing to start fresh? Use this button to roll back to the default settings.") }}
                        </span>
          </div>

          <div class="sui-box-settings-col-2">
            <button
                class="sui-button sui-button-ghost"
                data-modal-open="reset-data-confirm"
                data-modal-mask="true"
                data-esc-close="true"
            >
              <i class="sui-icon-undo" aria-hidden="true"></i>
              {{ __("Reset Settings") }}
            </button>
            <span class="sui-description">
                        {{ __("Note: This will instantly revert all settings to their default states but will leave your data intact.") }}
                        </span>
          </div>
        </div>
      </div>
      <div class="sui-box-footer">
        <div class="sui-actions-right">
          <submit-button type="submit" class="sui-button-blue" :state="state">
            <i class="sui-icon-save" aria-hidden="true"></i>
            {{ __("Save Changes") }}
          </submit-button>
        </div>
      </div>
    </form>
    <div class="sui-modal sui-modal-md">
      <div
          role="dialog"
          id="reset-data-confirm"
          class="sui-modal-content"
          aria-modal="true"
          aria-labelledby="Reset Data"
      >
        <div class="sui-box" role="document">
          <div class="sui-box-header">
            <h3 class="sui-box-title">
              {{ __("Reset Settings") }}
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
              {{ __("Are you sure you want to reset Defender's settings back to the factory defaults?") }}
            </p>
          </div>
          <form method="post" @submit.prevent="resetSettings">
            <div class="sui-box-footer sui-space-between">
              <button type="button" class="sui-button sui-button-ghost" data-modal-close="">
                {{ __("Cancel") }}
              </button>
              <submit-button type="submit" css-class="sui-button-ghost" :state="state">
                <i class="sui-icon-undo" aria-hidden="true"></i>
                {{ __("Reset Settings") }}
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
import Sidetab from "../../../component/sidetab";

export default {
  components: {Sidetab},
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
            SUI.closeModal()
          })
        }
      });
    }
  }
}
</script>