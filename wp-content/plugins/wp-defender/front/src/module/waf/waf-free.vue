<template>
    <div id="waf" class="sui-wrap" :class="maybeHighContrast()">
        <div class="sui-header">
            <h1 class="sui-header-title">
                {{__("Web Application Firewall")}}
            </h1>
            <doc-link
                    link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#web-application-firewall-waf"></doc-link>
        </div>
        <div class="sui-box">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    {{__("Get Started")}}
                </h3>
                <div class="sui-actions-left">
                    <span class="sui-tag sui-tag-pro">Pro</span>
                </div>
            </div>
            <div class="sui-message sui-message-lg">
                <img class="sui-image"
                     :src="assetUrl('assets/img/lockout-man.svg')">
                <div class="sui-message-content">
                    <p>
                        {{__("The new Web Application Firewall (WAF) is a first layer of protection to block hackers and bot attacks before they reach your site.")}}
                    </p>
                    <p>
                        {{__('It filters requests against a highly optimized managed ruleset covering common attacks (OWASP top ten) and performs virtual patching of WordPress core, plugin, and theme vulnerabilities.')}}
                    </p>
                    <a :href="get_url"
                       target="_blank" class="sui-button sui-button-purple">{{__('Try pro free today')}}</a>
                    <p class="sui-description text-center margin-top-15">
                        {{__('This feature is available to members who host their sites with WPMU DEV.')}}
                    </p>
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
        name: "waf_free",
        computed: {
            get_url: function () {
                if (defender.is_membership) {
                    return 'http://premium.wpmudev.org/waf';
                } else {
                    return this.campaignUrl('waf', 'defender_waf_upgrade_button');
                }
            }
        }
    }
</script>