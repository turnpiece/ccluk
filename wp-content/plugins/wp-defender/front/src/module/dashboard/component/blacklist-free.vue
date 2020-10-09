<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <i class="sui-icon-target" aria-hidden="true"></i>
                {{__("Blocklist Monitor")}}
            </h3>
            <div class="sui-actions-left">
                <span class="sui-tag sui-tag-pro">{{__("Pro")}}</span>
            </div>
        </div>
        <div class="sui-box-body sui-upsell-items">
            <div class="sui-padding-left sui-padding-right sui-padding-top">
                <p>
                    {{__("Automatically check if you’re on Google’s blocklist every 6 hours. If something’s wrong, we’ll let you know via email.")}}
                </p>
            </div>
            <div class="sui-box-settings-row sui-upsell-row">
                <img class="sui-image sui-upsell-image"
                     :src="assetUrl('assets/img/dashboard-blacklist.svg')">
                <div class="sui-upsell-notice">
                    <div>
                        <p>
                            {{__("Defender will warn you if your site has been flagged as unsafe. Get blocklist Monitor as part of a WPMU DEV membership.")}}<br/> <a class="premium-button sui-button sui-button-purple" target='_blank' :href="campaign_url('defender_dash_blocklist_upgrade_button')">{{__("Try Pro Free Today")}}</a>.
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
        name: "blacklist-free"
    }
</script>