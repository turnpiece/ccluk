<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                {{__("Reporting")}} <span class="sui-tag sui-tag-pro">{{__("Pro")}}</span>
            </h3>
        </div>
        <form method="post">
            <div class="sui-box-body sui-upsell-items">
                <div class="sui-box-settings-row sui-disabled no-margin-bottom">
                    <p>
                        {{__("Defender can automatically run regular scans of your website and email you reports.")}}
                    </p>
                </div>
                <div class="sui-box-settings-row sui-disabled no-margin-bottom">
                    <div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">{{__("Enable reporting")}}</span>
                        <span class="sui-description">
                            {{__("Enabling this option will ensure you're always the first to know when something suspicious is detected on your site.")}}
                        </span>
                    </div>
                    <div class="sui-box-settings-col-2">
                        <div class="sui-side-tabs">
                            <div class="sui-tabs-menu">
                                <label class="sui-tab-item">
                                    <input type="radio">
                                    {{__("On")}}
                                </label>
                                <label class="sui-tab-item active">
                                    <input type="radio">
                                    {{__("Off")}}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sui-box-settings-row sui-upsell-row no-border">
                    <img class="sui-image sui-upsell-image" :src="assetUrl('/assets/img/scanning-free-man.svg')">
                    <div class="sui-upsell-notice">
                        <p>
                            {{__("Schedule automated file scanning and email reporting for all your websites. Get reporting as part of a WPMU DEV membership.")}}<br/> <a class="premium-button sui-button sui-button-purple" :href="campaign_url('defender_filescanning_reports_upsell_link')">{{__("Try Pro Free Today")}}</a>
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import base_hepler from "../../../helper/base_hepler";

    export default {
        mixins: [base_hepler],
        name: "reporting",
    }
</script>