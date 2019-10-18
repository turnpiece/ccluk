<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <i class="sui-icon-eye" aria-hidden="true"></i>
                {{__("Audit Logging")}}
            </h3>
            <div class="sui-actions-left">
                <span class="sui-tag sui-tag-pro">{{__("Pro")}}</span>
            </div>
        </div>
        <div class="sui-box-body sui-upsell-items">
            <div class="sui-box-settings-row no-margin-bottom">
                <p>
                    {{__("Track and log events when changes are made to your website giving you full visibility of what's going on behind the scenes.")}}
                </p>
            </div>
            <div class="sui-box-settings-row sui-upsell-row">
                <img class="sui-image sui-upsell-image"
                     :src="assetUrl('assets/img/audit-presale.svg')">
                <div class="sui-upsell-notice">
                    <p>
                        {{__("Audit Logging is a Pro feature that requires a WPMU DEV monthly membership.")}} <a target='_blank' :href="campaign_url('defender_dash_auditlogging_upsell_link')">{{__("Try it out today")}}</a>!
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler'

    export default {
        mixins: [base_helper],
        name: "audit-free",
    }
</script>