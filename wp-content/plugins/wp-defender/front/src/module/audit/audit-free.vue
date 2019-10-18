<template>
    <div class="sui-wrap" :class="maybeHighContrast">
        <div class="sui-box">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    {{__("Audit Logging")}}
                </h3>
                <div class="sui-actions-left">
                    <span class="sui-tag sui-tag-pro">Pro</span>
                </div>
            </div>
            <div class="sui-message">
                <img class="sui-image"
                     :src="assetUrl('assets/img/audit-disabled-man.svg')">
                <div class="sui-message-content">
                    <p>
                        {{__("Track and log each and every event when changes are made to your website and get detailed reports on what's going on behind the scenes, including any hacking attempts on your site. This is a pro feature that requires an active WPMU DEV membership. Try it free today!")}}
                    </p>
                    <a :href="campaign_url('defender_auditlogging_upgrade_button')"
                       target="_blank" class="sui-button sui-button-purple">Upgrade to Pro</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import base_hepler from "../../helper/base_hepler";

    export default {
        mixins: [base_hepler],
        name: "audit-free",
    }
</script>