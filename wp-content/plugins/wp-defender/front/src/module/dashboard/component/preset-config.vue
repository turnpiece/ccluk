<template>
    <div class="sui-box preset-config">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <i class="sui-icon-wrench-tool" aria-hidden="true"></i>
                {{__("Preset Configs")}}
            </h3>
            <div class="sui-actions-left" v-if="count_configs > 0">
              <div class="sui-tag" v-text="count_configs"></div>
            </div>
        </div>
        <div class="sui-box-body">
            <p>
                {{__('Configs bundle your Defender settings and make them available to download and apply on your other sites. You can have unlimited preset configs.')}}
            </p>
        </div>
<!--          Start Config list-->
        <div class="sui-field-list sui-accordion sui-accordion-flushed">
            <div class="sui-accordion-item" v-for="(config,key) in configs">
                <div class="sui-accordion-item-header"
                    @mouseenter="current_config = key; new_config_name = config.name; new_config_description = config.description">
                    <div class="sui-accordion-item-title">
                        <span class="defender-container">
                            <i class="sui-icon-defender" aria-hidden="true"></i>
                        </span>
                        <strong v-text="config.name"></strong>
                        <span v-if="config.immortal" :data-tooltip="__('Recommended config')" class="sui-tooltip">
                            <i class="sui-icon-check-tick" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="sui-accordion-col-auto">
                        <span class="sui-tag sui-tag-blue sui-tag-sm" v-show="config.is_active===true">{{ __('Active') }}</span>
                        <div class="sui-dropdown sui-accordion-item-action">
                            <a href="#" class="sui-dropdown-anchor sui-icon-widget-settings-config" aria-label="Open Item Settings"></a>
<!--                      Start Actions-->
                            <ul>
                                <li v-if="config.is_active!==true"><a href=""
                                       data-modal-open="apply-config"
                                       data-modal-open-focus="configs"
                                       data-modal-close-focus="wpwrap"
                                       data-modal-mask="false"
                                       data-esc-close="true"
                                >
                                    <i class="sui-icon-check" aria-hidden="true"></i> {{__('Apply')}}</a>
                                </li>
                                <li>
                                    <a :href="download_config_url">
                                        <i class="sui-icon-download" aria-hidden="true"></i> {{__('Download')}}
                                    </a>
                                </li>
                                <li v-if="config.immortal == false">
                                    <a href=""
                                       data-modal-open="rename-config"
                                       data-modal-open-focus="configs"
                                       data-modal-close-focus="wpwrap"
                                       data-modal-mask="false"
                                       data-esc-close="true"
                                    >
                                    <i class="sui-icon-pencil" aria-hidden="true"></i> {{__('Name & Description')}}</a>
                                </li>
                                <li v-if="config.immortal == false">
                                    <a href="" class="sui-option-red"
                                       data-modal-open="delete-config"
                                       data-modal-open-focus="configs"
                                       data-modal-close-focus="wpwrap"
                                       data-modal-mask="false"
                                       data-esc-close="true"
                                    >
                                    <i class="sui-icon-trash" aria-hidden="true"></i> {{__('Delete')}}</a>
                                </li>
                            </ul>
<!--                      End Actions-->
                        </div>
                        <button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open Item">
                            <i class="sui-icon-chevron-down" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
<!--                Start config features-->
                <div class="sui-accordion-item-body">
                    <div class="sui-box">
                        <div class="sui-box-body">
                            <div class="sui-box-settings-row">
                                <span class="sui-description">
                                  {{ __(config.description) }}
                                </span>
                            </div>
                            <div class="config-detail">
                                <table class="table-fixed w-full">
                                    <tbody>
                                        <tr>
                                            <td class="px-4 py-2 font-medium">{{ __('Security Recommendations') }}</td>
                                            <td class="px-4 py-2 font-medium">
                                                <div v-for="item in config.strings.security_tweaks">
                                                    <span v-text="item"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="odd">
                                            <td class="px-4 py-2 font-medium">{{ __('Malware Scanning') }}</td>
                                            <td class="px-4 py-2 font-medium">
                                                <div v-for="item in config.strings.scan">
                                                    <span v-html="item"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="">
                                            <td class="px-4 py-2 font-medium">{{ __('Audit Logs') }}</td>
                                            <td class="px-4 py-2 font-medium">
                                                <div v-for="item in config.strings.audit">
                                                    <span v-html="item"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="odd">
                                            <td class="px-4 py-2 font-medium">{{ __('Firewall') }}</td>
                                            <td class="px-4 py-2 font-medium">
                                                <div v-for="item in config.strings.iplockout">
                                                    <span v-html="item"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="">
                                            <td class="px-4 py-2 font-medium">{{ __('Two-Factor Authentication') }}</td>
                                            <td class="px-4 py-2 font-medium">
                                                <div v-for="item in config.strings.two_factor">
                                                    <span v-text="item"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="odd">
                                            <td class="px-4 py-2 font-medium">{{ __('Mask Login Area') }}</td>
                                            <td class="px-4 py-2 font-medium">
                                                <div v-for="item in config.strings.mask_login">
                                                    <span v-text="item"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-medium">{{ __('Security Headers') }}</td>
                                            <td class="px-4 py-2 font-medium">
                                                <div v-for="item in config.strings.security_headers">
                                                    <span v-text="item"></span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
    <!--                    Apply button-->
                        <div class="sui-box-footer" v-show="config.is_active!==true">
                            <div class="sui-actions-right">
                                <button type="button"
                                        data-modal-open="apply-config"
                                        data-modal-mask="false"
                                        data-esc-close="true"
                                        class="sui-button quick-apply sui-button-ghost sui-accordion-item-action">
                                    <i class="sui-icon-check" aria-hidden="true"></i>
                                    {{ __('Apply') }}
                                </button>
                            </div>
                        </div>
    <!--                    End Apply button-->
                    </div>
                </div>
<!--                End config features-->
            </div>
        </div>
<!--          End Config list-->

<!--            <div class="sui-notice sui-notice-info">-->
<!--                <div class="sui-notice-content">-->
<!--                    <div class="sui-notice-message">-->
<!--                        <i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>-->
<!--                        <p>{{__("Use configs to save preset configurations of Defender's settings, then upload and apply them to your other sites in just a few clicks! P.s. Save as many of them as you like - you can have unlimited preset configs.")}}</p>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->

        <div class="sui-box-footer no-border">
            <div class="sui-actions-left">
                <a :href="adminUrl('admin.php?page=wdf-setting&view=configs')" class="sui-button sui-button-ghost">
                    <i class="sui-icon-wrench-tool" aria-hidden="true"></i>
                    {{__('Manage Configs')}}
                </a>
            </div>
          <div class="sui-actions-right">
              <button
                  class="sui-button sui-button-blue"
                  data-modal-open="new-config"
                  data-modal-mask="false"
                  data-esc-close="true"
              >
                  <i class="sui-icon-save" aria-hidden="true"></i> {{ __('Save new') }}
              </button>
          </div>
        </div>
        <div class="sui-modal sui-modal-sm">
            <div
                    role="dialog"
                    id="new-config"
                    class="sui-modal-content"
                    aria-modal="true"
                    aria-labelledby="save-new-config"
                    aria-describedby="save-new-config"
            >
                <div class="sui-box">
                    <div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
                        <button class="sui-button-icon sui-button-float--right" data-modal-close="">
                            <i class="sui-icon-close sui-md" aria-hidden="true"></i>
                            <span class="sui-screen-reader-text">{{textClose}}</span>
                        </button>
                        <h3 class="sui-box-title sui-lg">{{__('Save Current Config')}}</h3>
                        <p class="sui-description">
                            {{__('Save your current Defender settings configuration. You’ll be able to then download and apply it to your other sites with Defender installed.')}}
                        </p>
                    </div>
                    <div class="sui-box-body no-padding-bottom">
                        <div class="sui-form-field">
                            <label class="sui-label">{{__('Config name')}}</label>
                            <input type="text" v-model="config_name" class="sui-form-control">
                        </div>
                        <div class="sui-form-field">
                          <label class="sui-label"
                                 for="save_config_description"
                                 id="label_save_config_description">{{__('Config description')}}</label>
                          <textarea
                              v-model="config_description"
                              id="save_config_description"
                              class="sui-form-control"
                              aria-labelledby="label_save_config_description"
                              aria-describedby="text_save_config_description"
                          ></textarea>
                          <span id="text_save_config_description" class="sui-description">
                            {{__('You can edit the description to distinguish from other configs. Changing the description won’t exclude the feature from the preset.')}}
                          </span>
                        </div>
                    </div>
                    <div class="sui-box-footer sui-content-right no-border">
                        <button class="sui-button sui-button-ghost" data-modal-close="">
                          {{textCancel}}
                        </button>
                        <submit-button @click="new_config" :state="state" css-class="sui-button sui-button-blue"
                                       :disabled="!config_name.length">
                            <i class="sui-icon-save" aria-hidden="true"></i> {{__('Save')}}
                        </submit-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="sui-modal sui-modal-sm">
            <div
                    role="dialog"
                    id="rename-config"
                    class="sui-modal-content"
                    aria-modal="true"
                    aria-labelledby="rename-config"
                    aria-describedby="rename-config"
            >
                <div class="sui-box">
                    <div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
                        <button class="sui-button-icon sui-button-float--right" data-modal-close="">
                            <i class="sui-icon-close sui-md" aria-hidden="true"></i>
                            <span class="sui-screen-reader-text">{{textClose}}</span>
                        </button>
                        <h3 class="sui-box-title sui-lg">{{__('Edit Name & Description')}}</h3>
                        <p class="sui-description">
                            {{__('Change your config name and your description to something recognizable.')}}
                        </p>
                    </div>
                    <div class="sui-box-body no-padding-bottom">
                        <div class="sui-form-field">
                            <label class="sui-label">{{__('Config name')}}</label>
                            <input type="text" v-model="new_config_name" class="sui-form-control">
                        </div>
                        <div class="sui-form-field">
                          <label class="sui-label"
                                 for="edit_config_description"
                                 id="label_edit_config_description">{{__('Config description')}}</label>
                          <textarea
                              v-model="new_config_description"
                              id="edit_config_description"
                              class="sui-form-control"
                              aria-labelledby="label_edit_config_description"
                          ></textarea>
                        </div>
                    </div>
                    <div class="sui-box-footer sui-content-right no-border">
                        <button class="sui-button sui-button-ghost" data-modal-close="">
                          {{textCancel}}
                        </button>
                        <submit-button @click="rename_config" :state="state" css-class="sui-button sui-button-blue"
                                       :disabled="!new_config_name.length">
                            <i class="sui-icon-save" aria-hidden="true"></i> {{__('Save')}}
                        </submit-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="sui-modal sui-modal-sm">
            <div
                    role="dialog"
                    id="apply-config"
                    class="sui-modal-content"
                    aria-modal="true"
                    aria-labelledby="apply-config"
                    aria-describedby="apply-config"
            >
                <div class="sui-box">
                    <div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
                        <button class="sui-button-icon sui-button-float--right" data-modal-close="">
                            <i class="sui-icon-close sui-md" aria-hidden="true"></i>
                            <span class="sui-screen-reader-text">{{textClose}}</span>
                        </button>
                        <h3 class="sui-box-title sui-lg">{{__('Apply config')}}</h3>
                        <p class="sui-description" v-html="apply_text">
                        </p>
                    </div>
                    <div class="sui-box-footer sui-flatten sui-content-center sui-spacing-bottom--60">
                        <button class="sui-button sui-button-ghost" data-modal-close="">
                          {{textCancel}}
                        </button>
                        <submit-button @click="apply_config" :state="state" css-class="sui-button sui-button-blue"
                                       :disabled="!new_config_name.length">
                            <i class="sui-icon-check" aria-hidden="true"></i> {{__('Apply')}}
                        </submit-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="sui-modal sui-modal-sm">
            <div
                    role="dialog"
                    id="delete-config"
                    class="sui-modal-content"
                    aria-modal="true"
                    aria-labelledby="delete-config"
                    aria-describedby="delete-config"
            >
                <div class="sui-box">
                    <div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
                        <button class="sui-button-icon sui-button-float--right" data-modal-close="">
                            <i class="sui-icon-close sui-md" aria-hidden="true"></i>
                            <span class="sui-screen-reader-text">{{textClose}}</span>
                        </button>
                        <h3 class="sui-box-title sui-lg">{{__('Delete Configuration File')}}</h3>
                        <p class="sui-description" v-html="delete_text"></p>
                    </div>
                    <div class="sui-box-footer sui-flatten sui-content-center sui-spacing-bottom--60">
                        <button class="sui-button sui-button-ghost" data-modal-close="">
                            {{textCancel}}
                        </button>
                        <submit-button css-class="sui-button sui-button-red" :state="state" @click="delete_config">
                            {{__('Delete')}}
                        </submit-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler'

    export default {
        name: "preset-config",
        mixins: [base_helper],
        data: function () {
            return {
                endpoints: dashboard.settings.endpoints,
                nonces: dashboard.settings.nonces,
                configs: dashboard.settings.configs,
                config_name: '',
                config_description: '',
                new_config_name: '',
                new_config_description: '',
                current_config: '',
                state: {
                    on_saving: false
                },
                textCancel: '',
                textClose: ''
            }
        },
        created: function () {
            this.textCancel = this.__('Cancel');
            this.textClose = this.__('Close this dialog.');
        },
        computed: {
            download_config_url: function () {
                return ajaxurl + '?action=' + this.endpoints['downloadConfig'] + '&_wpnonce=' + this.nonces['downloadConfig'] + '&key=' + this.current_config;
            },
            config: function () {
                return this.configs[this.current_config]
            },
            hub_text: function () {
                return this.vsprintf(this.__('Did you know you can apply your configs to any connected website in <a href="%s">The Hub</a>'), 'https://premium.wpmudev.org/hub/')
            },
            apply_text: function () {
                if (this.config !== undefined)
                    return this.vsprintf(this.__('Are you sure you want to apply the <span class="text-gray-500 font-semibold">%s</span> settings config to <span class="text-gray-500 font-semibold">%s</span>? We recommend you have a backup available as your existing settings configuration will be overridden.'), this.config.name, this.siteUrl)
            },
            delete_text: function () {
                if (this.config !== undefined) {
                    return this.vsprintf(this.__('Are you sure you want to delete the <span class="text-gray-500 font-semibold">%s</span> config file? You will no longer be able to apply it to this or other connected sites.'), this.config.name)
                }
            },
            count_configs: function () {
              return Object.keys(this.configs).length > 0 ? Object.keys(this.configs).length : 0;
            }
        },
        methods: {
            apply_config: function () {
                let self = this;
                this.httpPostRequest('applyConfig', {
                    key: self.current_config,
                    screen: 'dashboard'
                }, function (response) {
                    if (response.success === true) {
                        if (response.data.login_url !== undefined) {
                            setTimeout(function () {
                                location.href = response.data.login_url;
                            }, 2000)
                        } else {
                            self.configs = response.data.configs
                            self.$nextTick(() => {
                                self.rebindSUI();
                                self.config_name = '';
                                self.config_description = '';
                                SUI.closeModal()
                            })
                        }
                    }
                })
            },
            new_config: function () {
                let self = this;
                this.httpPostRequest('newConfig', {
                    name: self.config_name,
                    desc: self.config_description
                }, function (response) {
                    if (response.success === true) {
                        self.configs = response.data.configs
                        self.$nextTick(() => {
                            self.config_name = '';
                            self.config_description = '';
                            SUI.closeModal()
                        })
                    } else {
                        SUI.closeModal();
                        self.$nextTick(() => {
                            self.rebindSUI();
                        })
                    }
                })
            },
            rename_config: function () {
                let self = this;
                this.httpPostRequest('updateConfig', {
                    key: self.current_config,
                    name: self.new_config_name,
                    description: self.new_config_description
                }, function (response) {
                    if (response.success === true) {
                        self.configs = response.data.configs
                        self.$nextTick(() => {
                            SUI.closeModal()
                        })
                    }
                })
            },
            delete_config: function () {
                let self = this;
                this.httpPostRequest('deleteConfig', {
                    key: self.current_config
                }, function (response) {
                    if (response.success === true) {
                        self.configs = response.data.configs
                        self.$nextTick(() => {
                            SUI.closeModal()
                        })
                    }
                })
            },
        }
    }
</script>