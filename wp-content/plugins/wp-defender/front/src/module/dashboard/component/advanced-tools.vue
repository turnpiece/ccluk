<template>
    <div class="sui-box advanced-tools">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <i class="sui-icon-wand-magic" aria-hidden="true"></i>
                {{__("Advanced Tools")}}
            </h3>
        </div>
        <div class="sui-box-body">
            <p>
                {{__("Enable advanced tools for enhanced protection against even the most aggressive of hackers and bots.")}}
            </p>
            <hr class="sui-flushed">
            <strong>{{__("Security Headers")}}</strong>
            <span class="sui-description">
                {{__("Add extra security to your website by enabling and configuring the security headers.")}}
            </span>
            <div v-if="Object.keys(security_headers).length">
                <div class="sui-field-list sui-flushed margin-top-30 no-border">
                    <div class="sui-field-list-body">
                        <div class="sui-field-list-item" v-for="item in security_headers">
                            <label class="sui-field-list-item-label">
                                <strong v-text="item.title"></strong>
                            </label>
                            <span class="sui-tag sui-tag-success">{{__('Enabled')}}</span>
                        </div>
                    </div>
                </div>
                <hr class="sui-flushed no-margin-bottom no-margin-top"/>
            </div>
            <a :href="adminUrl('admin.php?page=wdf-advanced-tools&view=security-headers')"
               class="sui-button margin-top-10">
                <i class="sui-icon-wrench-tool"></i>
                {{__('Configure')}}
            </a>
            <hr class="sui-flushed"/>
            <strong>{{__("Mask Login Area")}}</strong>
            <span class="sui-description">
                {{__("Change the location of WordPress's default login area.")}}
            </span>
            <form method="post" class="margin-top-10"
                  @submit.prevent="updateSettings" v-if="mask_login.enabled===false">
                <submit-button type="submit" css-class="sui-button-blue" :state="state">
                    {{__("Activate")}}
                </submit-button>
            </form>
            <div class="sui-notice sui-notice-warning margin-top-10"
                 v-else-if="mask_login.useable===false">
                <div class="sui-notice-content">
                    <div class="sui-notice-message">
                        <p>
                            {{__("Masking is currently inactive. Choose your URL and save your settings to finish setup.")}}
                            <br/>
                            <a class="sui-button margin-top-10"
                               :href="adminUrl('admin.php?page=wdf-advanced-tools&view=mask-login')">
                                {{__("Finish Setup")}}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="sui-notice sui-notice-success margin-top-10"
                 v-else-if="mask_login.useable===true">
                <div class="sui-notice-content">
                    <div class="sui-notice-message">
                        <i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>

                        <p>
                            {{__("Masking is currently active at ")}}
                            <a target="_blank"
                               :href="mask_login.login_url">{{mask_login.login_url}}</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
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
                mask_login: dashboard.advanced_tools.mask_login,
                security_headers: dashboard.advanced_tools.security_headers
            }
        },
        methods: {
            updateSettings: function () {
                let self = this;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify({
                        settings: {
                            enabled: true
                        },
                        module: 'mask-login'
                    })
                }, function () {
                    self.mask_login.enabled = true;
                });
            }
        }
    }
</script>
