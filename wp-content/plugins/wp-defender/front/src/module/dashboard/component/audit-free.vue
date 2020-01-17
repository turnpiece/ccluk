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
            <div class="sui-box-settings-row no-margin-bottom no-padding-bottom">
                <p>
                    {{__("Track and log events when changes are made to your website giving you full visibility of what's going on behind the scenes.")}}
                </p>
            </div>
            <div class="sui-box-settings-row sui-upsell-row">
                <img class="sui-image sui-upsell-image"
                     :src="assetUrl('assets/img/audit-presale.svg')">
                <div class="sui-upsell-notice">
                    <p>
                        {{__("Get an automatic report about the changes made on your website with Audit Logging. Get Audit Logging as part of a WPMU DEV membership.")}}<br/> <a class="premium-button sui-button sui-button-purple" target='_blank' :href="campaign_url('defender_dash_auditlogging_upsell_link')">{{__("Try Pro Free Today")}}</a>
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