<template>
    <div class="sui-box two_fa">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <i class="sui-icon-lock" aria-hidden="true"></i>
                {{__("Two-Factor Authentication")}}
            </h3>
        </div>
        <div class="sui-box-body">
            <p>{{__('Add an extra layer of security to your WordPress account to ensure that you’re the only person who'
                +' can log in, even if someone else knows your password.')}}
            </p>
            <form method="post" v-if="enabled === false" class="margin-top-10 margin-bottom-10"
                  @submit.prevent="updateSettings('auth')">
                <submit-button type="submit" css-class="sui-button-blue" :state="state">
                    {{__("Activate")}}
                </submit-button>
            </form>
            <div class="sui-notice sui-notice-warning margin-bottom-30 margin-top-10"
                 v-else-if="useable===false">
                <div class="sui-notice-content">
                    <div class="sui-notice-message">
                        <i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
                        <p v-text='__("Two-factor authentication is currently inactive. Configure and save your settings to finish setup.")'>
                        </p>
                        <p>
                            <a :href="adminUrl('admin.php?page=wdf-2fa')" class="sui-button">
                                {{__("Finish Setup")}}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="sui-notice sui-notice-success margin-top-10 margin-bottom-30"
                 v-else-if="useable===true">
                <div class="sui-notice-message">
                    <div class="sui-notice-content">
                        <div class="sui-notice-message">
                            <i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>

                            <p v-text='__("Two-factor authentication is now active. User roles with this feature enabled must visit their Profile page to complete setup and sync their account with the Authenticator app.")'>
                            </p>

                        </div>
                    </div>
                </div>
            </div>
            <small v-if="useable===true"
                   v-text='__("Note: Each user on your website must individually enable two-factor authentication via their user profile in order to enable and use this security feature.")'></small>
        </div>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler'

    export default {
        mixins: [base_helper],
        name: "two-fa",
        data: function () {
            return {
                state: {
                    on_saving: false
                },
                enabled: dashboard.two_fa.enabled,
                useable: dashboard.two_fa.useable,
                nonces: dashboard.two_fa.nonces,
                endpoints: dashboard.two_fa.endpoints
            }
        },
        methods: {
            updateSettings: function (module) {
                let that = this;
                let envelope = {};
                envelope['enabled'] = true;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify({
                        settings: envelope,
                        module: 'auth'
                    })
                }, function () {
                    that.enabled = true;
                })
            }
        }
    }
</script>