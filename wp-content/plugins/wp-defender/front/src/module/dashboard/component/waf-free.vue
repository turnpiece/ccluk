<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <img :src="assetUrl('/assets/img/waf@3x.svg')"/>&nbsp;
                {{__("Web Application Firewall")}}
            </h3>
            <div class="sui-actions-left">
                <span class="sui-tag sui-tag-pro">{{__("Pro")}}</span>
            </div>
        </div>
        <div class="sui-box-body">
            <p>
                {{__("The new Web Application Firewall (WAF) filters incoming requests against a highly optimized managed ruleset to block hackers and bot attacks before they reach your site. It's our basic Firewall with advanced layers of protection.")}}
            </p>
            <div class="sui-box-settings-row sui-upsell-row">
                <div class="sui-upsell-notice no-padding">
                    <p>
                        {{__("This feature is available to members who host their sites with WPMU DEV.")}}<br/>
                        <a class="premium-button sui-button sui-button-purple" target='_blank'
                           :href="get_url">{{__("Try Pro Free Today")}}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import base_hepler from "../../../helper/base_hepler";

    export default {
        name: "waf_free",
        mixins: [base_hepler],
        computed: {
            get_url: function () {
                if (defender.is_membership) {
                    return 'http://premium.wpmudev.org/waf';
                } else {
                    return this.campaignUrl('waf', 'defender_dash_waf_upgrade_button');
                }
            }
        }
    }
</script>