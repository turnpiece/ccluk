<template>
    <div id="waf" class="sui-wrap" :class="maybeHighContrast()">
        <div class="sui-header">
            <h1 class="sui-header-title">
                {{__("Web Application Firewall")}}
            </h1>
            <doc-link
                    link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#web-application-firewall-waf"></doc-link>
        </div>
        <div class="sui-box" v-if="on_us===true && status===true">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    {{__("Settings")}}
                </h3>
            </div>
            <div class="sui-box-body">
                <p>{{__('Web Application Firewall (WAF) is a first layer of protection to block hackers and bot attacks before they ever reach your site. The WAF filters request against our highly optimized managed ruleset covering common attacks (OWASP top ten) and performs virtual patching of WordPress core, plugin, and theme vulnerabilities.')}}</p>
                <div class="sui-notice sui-notice-info">
                    <div class="sui-notice-content">
                        <div class="sui-notice-message">
                            <i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
                            <p>{{__('This site has WAF protection enabled. Please keep in mind that the status can be cached for 5 minutes, and it\'s likely the changes you make in the Hub won\'t be updated in-plugin immediately.')}}</p>
                        </div>
                    </div>
                </div>
                <div class="sui-box-settings-row">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label-with-tag">
                            {{__('Configure')}}
                            <span class="sui-tag sui-tag-blue">{{__('Coming Soon')}}</span>
                        </span>
                        <span class="sui-description">{{__('Configure and manage your IP and user agent rules. Note: we’ll honor the rules set in Defender’s basic Firewall too.')}}</span>
                    </div>
                    <div class="sui-box-settings-col-2">
                        <p class="margin-bottom-10" v-html="get_waf_text"></p>
                        <a target="_blank" :href="get_waf_url" class="sui-button sui-button-ghost">
                            <i class="sui-icon-wrench-tool"></i>{{__('Manage Rules')}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="sui-box" v-else>
            <div class="sui-floating-notices">
                <div
                        role="alert"
                        id="status-cached"
                        class="sui-notice sui-notice-info"
                        aria-live="assertive"
                >
                </div>
            </div>
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    {{__("Get Started")}}
                </h3>
                <div class="sui-actions-right">
                    <submit-button @click="recheck_status" type="submit" :state="state"
                                   css-class="sui-button sui-button-ghost">
                        <i class="sui-icon-update"></i>
                        {{__('Re-check Status')}}
                    </submit-button>
                </div>
            </div>
            <div class="sui-message sui-message-lg" v-if="on_us!==true">
                <img class="sui-image"
                     :src="assetUrl('assets/img/lockout-man.svg')">
                <div class="sui-message-content">
                    <p>
                        {{__("The new Web Application Firewall (WAF) is a first layer of protection to block hackers and bot attacks before they reach your site.")}}
                    </p>
                    <p>
                        {{__("It filters requests against a highly optimized managed ruleset covering common attacks (OWASP top ten) and performs virtual patching of WordPress core, plugin, and theme vulnerabilities.")}}
                    </p>
                    <a target="_blank" :href="get_migrate_url"
                       class="sui-button sui-button-blue">{{__('Migrate my site')}}</a>
                    <p class="sui-description text-center margin-top-15" v-html="get_footer_text"></p>
                </div>
            </div>
            <div class="sui-message sui-message-lg" v-if="on_us===true && status===false">
                <img class="sui-image"
                     :src="assetUrl('assets/img/lockout-man.svg')">
                <div class="sui-message-content">
                    <p>
                        {{__("The new Web Application Firewall (WAF) is a first layer of protection to block hackers and bot attacks before they reach your site.")}}
                    </p>
                    <p>
                        {{__('It filters requests against a highly optimized managed ruleset covering common attacks (OWASP top ten) and performs virtual patching of WordPress core, plugin, and theme vulnerabilities.')}}
                    </p>
                    <a target="_blank" data-notice-open="status-cached" :data-notice-message="get_cached_notice_message"
                       :href="get_waf_url"
                       data-notice-dismiss="true"
                       class="sui-button sui-button-blue">{{__('Activate WAF')}}</a>
                    <p class="sui-description text-center margin-top-15" v-html="get_footer_text"></p>
                </div>
            </div>
        </div>
        <app-footer></app-footer>
    </div>
</template>

<script>
    import base_hepler from "../../helper/base_hepler";

    export default {
        mixins: [base_hepler],
        name: "waf",
        data: function () {
            return {
                on_us: waf.waf.hosted,
                site_id: waf.site_id,
                status: waf.waf.status,
                notice: {
                    display: 'none'
                },
                state: {
                    on_saving: false
                },
                nonces: waf.nonces,
                endpoints: waf.endpoints
            }
        },
        computed: {
            get_migrate_url: function () {
                return 'https://premium.wpmudev.org/hub2/site/' + this.site_id + '/hosting';
            },
            get_waf_url: function () {
                return 'https://premium.wpmudev.org/hub2/site/' + this.site_id + '/hosting/tools#update-waf';
            },
            get_waf_text: function () {
                return this.vsprintf(this.__('At this time, you can manage all WAF settings via <a target="_blank" href="%s">The Hub.</a>'), 'https://premium.wpmudev.org/hub2/')
            },
            get_cached_notice_message: function () {
                return '<p>' + this.__("The status can be cached for 5 minutes, and it's likely the changes you make in the Hub won't be updated in-plugin immediately. You can wait a little bit and re-check again to get the updated status.") + '</p>';
            },
            get_footer_text: function () {
                if (this.on_us === false) {
                    return this.vsprintf(this.__('You can learn more about the WAF <a target="_blank" href="%s">here.</a>'), 'http://premium.wpmudev.org/waf')
                } else if (this.on_us === true && this.status === false) {
                    return this.vsprintf(this.__('Enable this feature via <a target="_blank" href="%s">The Hub</a> today or learn more <a target="_blank" href="%s">here</a>.'), this.get_waf_url, 'http://premium.wpmudev.org/waf');
                }
            }
        },
        methods: {
            recheck_status: function () {
                let self = this;
                this.httpPostRequest('recheck', {}, function (response) {
                    self.status = response.data.waf.status;
                })
            },
            close_notice: function () {
                SUI.closeNotice('status-cached');
            }
        },
    }
</script>