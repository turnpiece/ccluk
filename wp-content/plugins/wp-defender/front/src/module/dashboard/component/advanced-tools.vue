<template>
    <div class="sui-box advanced-tools">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <i class="sui-icon-wand-magic" aria-hidden="true"></i>
                {{__("Advanced Tools")}}
            </h3>
        </div>
        <div class="sui-box-body no-padding-bottom">
            <p>
                {{__("Enable advanced tools for enhanced protection against even the most aggressive of hackers and bots.")}}
            </p>
        </div>
        <hr/>
        <table class="sui-table sui-table-flushed margin-top-30">
            <tbody>
            <tr>
                <td>
                    <small><strong>{{__("Two-Factor Authentication")}}</strong></small>
                    <br/>
                    <small>
                        {{__("Add an extra layer of security to your WordPress account to ensure that you're the only person who can log in, even if someone else knows your password.")}}
                    </small>
                    <form method="post" v-if="two_factor.enabled === false" class="margin-top-10 margin-bottom-10"
                          @submit.prevent="updateSettings('auth')">
                        <submit-button type="submit" css-class="sui-button-blue" :state="state">
                            {{__("Activate")}}
                        </submit-button>
                    </form>
                    <div class="sui-notice sui-notice-warning margin-bottom-30 margin-top-10"
                         v-else-if="two_factor.useable===false">
                        <p>
                            {{__("Two-factor authentication is currently inactive. Configure and save your settings to finish setup.")}}
                            <br/>
                            <a class="sui-button margin-top-10" :href="adminUrl('admin.php?page=wdf-advanced-tools')">
                                {{__("Finish Setup")}}
                            </a>
                        </p>
                    </div>
                    <div class="sui-notice sui-notice-success margin-top-10 margin-bottom-30"
                         v-else-if="two_factor.useable===true">
                        <p>
                            {{__("Two-factor authentication is now active. User roles with this feature enabled must visit their Profile page to complete setup and sync their account with the Authenticator app.")}}
                        </p>
                    </div>
                    <small v-if="two_factor.useable===true">{{__("Note: Each user on your website must individually enable two-factor authentication via their user profile in order to enable and use this security feature.")}}</small>
                </td>
            </tr>
            <tr>
                <td>
                    <small class="margin-top-30"><strong>{{__("Mask Login Area")}}</strong></small>
                    <br/>
                    <small>
                        {{__("Change the location of WordPress's default login area.")}}
                    </small>
                    <form method="post" class="margin-top-10 margin-bottom-30"
                          @submit.prevent="updateSettings('mask-login')" v-if="mask_login.enabled===false">
                        <submit-button type="submit" css-class="sui-button-blue" :state="state">
                            {{__("Activate")}}
                        </submit-button>
                    </form>
                    <div class="sui-notice sui-notice-warning margin-bottom-30 margin-top-10"
                         v-else-if="mask_login.useable===false">
                        <p>
                            {{__("Masking is currently inactive. Choose your URL and save your settings to finish setup.")}}
                            <br/>
                            <a class="sui-button margin-top-10"
                               :href="adminUrl('admin.php?page=wdf-advanced-tools&view=mask-login')">
                                {{__("Finish Setup")}}
                            </a>
                        </p>
                    </div>
                    <div class="sui-notice sui-notice-success margin-top-10 margin-bottom-30"
                         v-else-if="mask_login.useable===true">
                        <p>
                            {{__("Masking is currently active at ")}} <a target="_blank"
                                :href="mask_login.login_url">{{mask_login.login_url}}</a>
                        </p>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler'

    export default {
        mixins: [base_helper],
        name: "advanced-tools",
        data: function () {
            return {
                state: {
                    on_saving: false,
                },
                nonces: dashboard.advanced_tools.nonces,
                endpoints: dashboard.advanced_tools.endpoints,
                two_factor: dashboard.advanced_tools.two_factors,
                mask_login: dashboard.advanced_tools.mask_login
            }
        },
        methods: {
            updateSettings: function (module) {
                let self = this;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify({
                        settings: {
                            enabled: true
                        },
                        module: module
                    })
                }, function () {
                    if (module === 'auth') {
                        self.two_factor.enabled = true;
                    } else {
                        self.mask_login.enabled = true;
                    }
                });
            }
        }
    }
</script>
