<template>
    <div id="two-fa" class="sui-wrap" :class="[maybeHighContrast()]">
        <div v-if="model.enabled===false" class="sui-box">
            <div class="sui-box">
                <div class="sui-box-header">
                    <h3 class="sui-box-title">
                        {{__("Two Factor Authentication")}}
                    </h3>
                </div>
                <div class="sui-message">
                    <img v-if="!maybeHideBranding()" :src="assetUrl('assets/img/2factor-disabled.svg')"
                         class="sui-image"
                         aria-hidden="true">
                    <div class="sui-message-content">
                        <p>
                            {{__("Beef up your website’s security with two-factor authentication. Add an extra step in"
                            +" the login process so that users are required to enter a password and an app-generated"
                            +" passcode using their phone – the best protection against brute force attacks.")}}
                        </p>
                        <form method="post" @submit.prevent="toggle(true)">
                            <submit-button type="submit" :state="state" css-class="sui-button-blue activate">
                                {{__("Activate")}}
                            </submit-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <div class="sui-header">
                <h1 class="sui-header-title">
                    {{__("Two-Factor Authentication")}}
                </h1>
                <doc-link link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#two-factor-authentication"></doc-link>
            </div>
            <summary-box css-class="sui-summary-sm">
                <div class="sui-summary-segment">
                    <div class="sui-summary-details">
                        <span class="sui-summary-large" v-text="total"></span>
                        <span class="sui-summary-sub">
						{{__("Users enabled authentication")}}
					</span>
                    </div>
                </div>
                <div class="sui-summary-segment">
                    <ul class="sui-list">
                        <li id="lostphone-tag">
                            <span class="sui-list-label">{{__("Lost Phone")}}</span>
                            <span class="sui-list-detail">
							    <span v-if="state.old_lostphone_value === false"
                                      class="sui-tag sui-tag-disabled sui-tag-sm">{{__("Disabled")}}</span>
                                <span v-else class="sui-tag sui-tag-blue sui-tag-sm">{{__("Active")}}</span>
						    </span>
                        </li>
                        <li id="customgraphic-tag">
                            <span class="sui-list-label">{{__("Custom Graphic")}}</span>
                            <span class="sui-list-detail">
                                <span class="sui-tag sui-tag-pro">{{__('Pro')}}</span>
						    </span>
                        </li>

                    </ul>
                </div>
            </summary-box>
            <div class="sui-box">
                <div class="sui-box-header">
                    <h3 class="sui-box-title">
                        {{__("Two Factor Authentication")}}
                    </h3>
                </div>
                <form method="post" id="advanced-settings-frm" @submit.prevent="updateSettings">
                    <div class="sui-box-body">
                        <p>
                            {{__("Configure your two-factor authentication settings. Our recommendations are enabled by "
                            +"default.")}}
                        </p>
                        <div class="sui-notice sui-notice-error" v-if="compatibility!==false">
                            <div class="sui-notice-content">
                                <div class="sui-notice-message">
                                    <i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
                                    <p>
                                        <span v-for="issue in compatibility">
                                            {{issue}}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-if="state.origin_state" class="sui-notice sui-notice-info">
                            <div class="sui-notice-content">
                                <div class="sui-notice-message">
                                    <i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
                                    <p>
                                        <strong>{{__("Two-factor authentication is now active.")}}</strong> {{__("User roles with this feature enabled must visit their ")}}
                                        <a :href="adminUrl('profile.php')">{{__("Profile page")}}</a> {{__("to complete setup and sync their account with the Authenticator app.")}}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="sui-notice sui-notice-warning">
                            <div class="sui-notice-content">
                                <div class="sui-notice-message">
                                    <i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
                                    <p>
                                        <strong>{{__("Two-factor authentication is currently inactive.")}}</strong> {{__("Configure and save your settings to complete setup.")}}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="sui-box-settings-row">
                            <div class="sui-box-settings-col-1">
                                <span class="sui-settings-label">{{__("User Roles")}}</span>
                                <span class="sui-description">
									{{__("Choose the user roles you want to enable two-factor authentication for. Users with those roles will then be required to use the Google Authenticator app to login.")}}
								</span>
                            </div>
                            <div class="sui-box-settings-col-2">
                                <div class="sui-field-list">
                                    <div class="sui-field-list-header">
                                        <h3 class="sui-field-list-title">
                                            {{__("User role")}}
                                        </h3>
                                    </div>
                                    <div class="sui-field-list-body">
                                        <div class="sui-field-list-item" v-for="(detail, role) in all_roles">
                                            <label v-html="detail.name" class="sui-field-list-item-label"
                                                   :for="'toggle_'+role"></label>
                                            <label class="sui-toggle">
                                                <input type="checkbox" v-model="model.user_roles" :value="role"
                                                       :id="'toggle_'+role">
                                                <span class="sui-toggle-slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sui-box-settings-row">
                            <div class="sui-box-settings-col-1">
								<span class="sui-settings-label">
									{{__("Lost Phone")}}
								</span>
                                <span class="sui-description">
									{{__("If a user is unable to access their phone, you can allow an option to send the one time password to their registered email.")}}
								</span>
                            </div>
                            <div class="sui-box-settings-col-2">
                                <div class="sui-form-field">
                                    <label class="sui-toggle">
                                        <input role="presentation" type="checkbox" v-model="model.lost_phone"
                                               id="lost_phone"
                                               class="toggle-checkbox">
                                        <span class="sui-toggle-slider"></span>
                                    </label>
                                    <label for="lost_phone" class="sui-toggle-label">
                                        {{__("Enable lost phone option")}}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="sui-box-settings-row">
                            <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("Force Authentication")}}
                        </span>
                                <span class="sui-description">
                            {{__("By default, two-factor authentication is optional for users. This setting forces users to activate two-factor.")}}
                        </span>
                            </div>
                            <div class="sui-box-settings-col-2">
                                <div class="sui-form-field">
                                    <label class="sui-toggle">
                                        <input role="presentation" v-model="model.force_auth" type="checkbox"
                                               name="force_auth"
                                               class="toggle-checkbox"
                                               id="force_auth"/>
                                        <span class="sui-toggle-slider"></span>
                                    </label>
                                    <label for="force_auth" class="sui-toggle-label">
                                        {{__("Force users to log in with two-factor authentication")}}
                                    </label>
                                    <span class="sui-description sui-toggle-content">
                                {{__("Note: Users will be forced to set up two-factor when they next login.")}}
                            </span>
                                    <div v-show="model.force_auth===true" id="force_auth_roles"
                                         class="sui-border-frame sui-toggle-content">
                                        <strong>{{__("User Roles")}}</strong>
                                        <ul>
                                            <li v-for="(detail,role) in all_roles">
                                                <label class="sui-checkbox" :for="'toggle_force_'+role">
                                                    <input type="checkbox" v-model="model.force_auth_roles"
                                                           :value="role"
                                                           :id="'toggle_force_'+role"/>
                                                    <span aria-hidden="true"></span>
                                                    <span>{{detail.name}}</span>
                                                </label>
                                            </li>
                                        </ul>
                                        <strong>{{__("Custom warning message")}}</strong>
                                        <textarea class="sui-form-control" v-model="model.force_auth_mess"
                                                  name="force_auth_mess"></textarea>
                                        <span class="sui-description">
                                    {{__("Note: This is shown in the users Profile area indicating they must use two-factor authentication.")}}
                                </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sui-box-settings-row sui-disabled">
                            <div class="sui-box-settings-col-1">
								<span class="sui-settings-label-with-tag">
									{{__("Custom Graphic")}} <span class="sui-tag sui-tag-pro">{{__("Pro")}}</span>
								</span>
                                <span class="sui-description">
									{{__("By default, Defender’s icon appears above the login fields. You can upload your own branding, or turn this feature off.")}}
								</span>
                            </div>
                            <div class="sui-box-settings-col-2">
                                <div class="sui-form-field">
                                    <label class="sui-toggle">
                                        <input role="presentation" type="checkbox" disabled="disabled"
                                               aria-labelledby="custom_graphic"/>
                                        <span aria-hidden="true" class="sui-toggle-slider"></span>
                                    </label>
                                    <label id="custom_graphic"
                                           class="sui-toggle-label">{{__("Enable custom graphics above login fields")}}</label>
                                </div>
                            </div>
                        </div>
                        <div class="sui-box-settings-row sui-upsell-row">
                            <img class="sui-image sui-upsell-image" :src="assetUrl('assets/img/graphic-defender.svg')"
                                 alt=''>
                            <div class="sui-upsell-notice">
                                <p>
                                    {{__("Remove our branding and whitelabel two-factor authentication with Defender Pro. This feature is included in a WPMU DEV membership with 24/7 support and lots of handy site management tools.")}}<br/>
                                    <a class="premium-button sui-button sui-button-purple" target='_blank'
                                       :href="campaign_url('defender_2fa_customgraphic_upsell')">{{__("Try Pro Free Today")}}</a>
                                </p>
                            </div>
                        </div>
                        <div class="sui-box-settings-row">
                            <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("Emails")}}
                        </span>
                                <span class="sui-description">
                            {{__("Customize the default copy for emails the two-factor feature sends to users.")}}
                        </span>
                            </div>
                            <div class="sui-box-settings-col-2">
                                <div class="sui-field-list">
                                    <div class="sui-field-list-header">
                                        <h3 class="sui-field-list-title">
                                            {{__("Email")}}
                                        </h3>
                                    </div>
                                    <div class="sui-field-list-body">
                                        <div class="sui-field-list-item">
                                            <label class="sui-field-list-item-label">
                                                {{__("Lost phone one time password")}}
                                            </label>
                                            <button
                                                    class="sui-button-icon"
                                                    data-modal-open="edit-one-time-password-email"
                                                    data-modal-mask="false"
                                                    data-esc-close="true"
                                            >
                                                <i class="sui-icon-pencil" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sui-box-settings-row">
                            <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("App Download")}}
                        </span>
                                <span class="sui-description">
                            {{__("Need the app? Here’s links to the official Google Authenticator iOS and Android apps.")}}
                        </span>
                            </div>
                            <div class="sui-box-settings-col-2">
                                <a href="https://itunes.apple.com/vn/app/google-authenticator/id388497605?mt=8">
                                    <img :src="assetUrl('assets/img/ios-download.svg')"/>
                                </a>
                                <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2">
                                    <img :src="assetUrl('assets/img/android-download.svg')"/>
                                </a>
                            </div>
                        </div>
                        <div class="sui-box-settings-row">
                            <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("Active Users")}}
                        </span>
                                <span class="sui-description">
                            {{__("Here’s a quick link to see which of your users have enabled two-factor authentication.")}}
                        </span>
                            </div>
                            <div class="sui-box-settings-col-2">
                                <a :href="adminUrl('users.php')">{{__("View users")}}</a>
                                {{__("who have enabled this feature.")}}
                            </div>
                        </div>
                        <div class="sui-box-settings-row">
                            <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("Deactivate")}}
                        </span>
                                <span class="sui-description">
                            {{__("Disable two-factor authentication on your website.")}}
                        </span>
                            </div>
                            <div class="sui-box-settings-col-2">
                                <submit-button css-class="sui-button-ghost" @click="toggle(false)" :state="state">
                                    {{__("Deactivate")}}
                                </submit-button>
                            </div>
                        </div>
                    </div>
                    <div class="sui-box-footer">
                        <div class="sui-actions-right">
                            <submit-button css-class="sui-button-blue save-changes" type="submit" :state="state">
                                <i class="sui-icon-save" aria-hidden="true"></i>
                                {{__("Save Changes")}}
                            </submit-button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="sui-modal sui-modal-lg">
                <div
                        role="dialog"
                        id="edit-one-time-password-email"
                        class="sui-modal-content"
                        aria-modal="true"
                        aria-labelledby="Edit passcode email content"
                >
                    <div class="sui-box" role="document">
                        <div class="sui-box-header">
                            <h3 class="sui-box-title" id="dialogTitle">
                                {{__("Edit Email")}}
                            </h3>
                            <div class="sui-actions-right">
                                <button data-modal-close="" class="sui-button-icon"
                                        aria-label="Close this dialog window">
                                    <i class="sui-icon-close"></i>
                                </button>
                            </div>
                        </div>
                        <form method="post">
                            <div class="sui-box-body">
                                <p id="dialogDescription">
                                    {{__("This email sends a temporary passcode when the user can’t access their phone.")}}
                                </p>
                                <div class="sui-row">
                                    <div class="sui-col">
                                        <div class="sui-form-field">
                                            <label class="sui-label">
                                                {{__("Subject")}}
                                            </label>
                                            <input name="subject" v-model="model.email_subject" class="sui-form-control"
                                                   type="text" id="email_subject"/>
                                        </div>
                                    </div>
                                    <div class="sui-col">
                                        <div class="sui-form-field">
                                            <label class="sui-label">
                                                {{__("Sender")}}
                                            </label>
                                            <input name="sender" v-model="model.email_sender" class="sui-form-control"
                                                   type="text" id="email_sender"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="sui-row">
                                    <div class="sui-col">
                                        <label class="sui-label">
                                            {{__("Body")}}
                                        </label>
                                        <textarea class="sui-form-control" v-model="model.email_body" name="body"
                                                  rows="8"
                                                  id="email_body"></textarea>
                                    </div>
                                </div>
                                <div class="sui-row">
                                    <div class="sui-col">
                                        <label class="sui-label">
                                            {{__("Available variables")}}
                                        </label>
                                        <span class="sui-tag"><strong v-text="__('{{passcode}}')"></strong></span>
                                        <span class="sui-tag"><strong v-text="__('{{display_name}}')"></strong></span>
                                    </div>
                                </div>
                            </div>

                            <div class="sui-box-footer">
                                <div class="sui-flex-child-right">
                                    <button type="button" class="sui-button sui-button-ghost" data-modal-close="">
                                        {{__("Cancel")}}
                                    </button>
                                </div>
                                <div class="sui-actions-right">
                                    <submit-button type="button" @click="saveEmailTemplate" :state="state"
                                                   class="sui-button">
                                        {{__("Save Template")}}
                                    </submit-button>
                                    <submit-button type="button" @click="sendTestEmail" :state="state"
                                                   class="sui-button sui-button-blue">
                                        {{__("Send Test")}}
                                    </submit-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import base_helper from '../../helper/base_hepler';

    export default {
        name: "two-fa",
        mixins: [base_helper],
        data: function () {
            return {
                all_roles: two_fa.misc.all_roles,
                compatibility: two_fa.misc.compatibility,
                model: two_fa.model,
                nonces: two_fa.nonces,
                endpoints: two_fa.endpoints,
                total: two_fa.misc.total,
                state: {
                    on_saving: false,
                    waiting_save: false,
                    origin_state: false,
                    old_lostphone_value: false,
                }
            }
        },
        watch: {
            "model.lost_phone": function () {
                this.state.waiting_save = true;
            },
            "model.custom_graphic": function () {
                this.state.waiting_save = true;
            }
        },
        methods: {
            toggle: function (value) {
                let that = this;
                let envelope = {};
                envelope['enabled'] = value;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify({
                        settings: envelope,
                        module: 'auth'
                    })
                }, function () {
                    that.model['enabled'] = value;
                    if (value === true) {
                        that.$nextTick(() => {
                            that.rebindSUI();
                            that.bindUploader();
                            that.state.waiting_save = false;
                        })
                    }
                })
            },
            updateSettings: function () {
                let data = this.model;
                //unset email subject as we dont use it on this function
                delete data['email_subject'];
                delete data['email_sender'];
                delete data['email_body'];
                let self = this;
                this.state.origin_state = this.model.user_roles.length > 0;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify({
                        settings: data,
                        module: 'auth'
                    })
                }, function () {
                    self.state.old_lostphone_value = self.model.lost_phone;
                });
            },
            saveEmailTemplate: function () {
                let data = {
                    email_subject: this.model.email_subject,
                    email_sender: this.model.email_sender,
                    email_body: this.model.email_body,
                };
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify({
                        module: 'auth',
                        settings: data
                    })
                }, function (response) {
                    if (response.success === true) {
                        SUI.closeModal()
                    }
                });
            },
            sendTestEmail: function () {
                let data = {
                    email_subject: this.model.email_subject,
                    email_sender: this.model.email_sender,
                    email_body: this.model.email_body,
                };
                this.httpPostRequest('sendTestEmail', data);
            },
            bindUploader: function () {
                let mediaUploader;
                let self = this;
                jQuery('.file-picker').click(function () {
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }
                    // Extend the wp.media object
                    mediaUploader = wp.media.frames.file_frame = wp.media({
                        title: 'Choose an image file',
                        button: {
                            text: 'Choose File'
                        }, multiple: false,
                        library: {
                            type: ['image']
                        }
                    });

                    // When a file is selected, grab the URL and set it as the text field's value
                    mediaUploader.on('select', function () {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        if (jQuery.inArray(attachment.mime, ["image/jpeg", "image/png", "image/gif"]) > -1) {
                            self.model.custom_graphic_url = attachment.url;
                        } else {
                            Defender.showNotification('error', 'Invalid image file type');
                        }
                    });
                    // Open the uploader dialog
                    mediaUploader.open();
                });
            }
        },
        mounted: function () {
            let that = this;
            this.$nextTick(() => {
                that.bindUploader();
            });
            this.state.origin_state = this.model.user_roles.length > 0;
        },
        beforeMount() {
            this.state.old_lostphone_value = this.model.lost_phone
        }
    }
</script>