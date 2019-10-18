<template>
    <div class="sui-box" data-tab="reporting">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                {{__("Reporting")}}
            </h3>
        </div>
        <div class="sui-box-body sui-upsell-items">
            <div class="sui-box-settings-row sui-disabled no-padding-bottom no-margin-bottom">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">{{__("Lockouts Report")}}</span>
                    <span class="sui-description">
                        {{__("Configure Defender to automatically email you a lockout report for this website.")}}
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-side-tabs sui-tabs">
                        <div data-tabs>
                            <div>{{__("On")}}</div>
                            <div class="active">{{__("Off")}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row sui-upsell-row">
                <img class="sui-image sui-upsell-image"
                     :src="upsell_img">
                <div class="sui-upsell-notice">
                    <p v-html="upsell_text">
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import base_heper from '../../../helper/base_hepler';

    export default {
        name: "report-free",
        mixins: [base_heper],
        props: ['view'],
        computed: {
            upsell_img: function () {
                return this.assetUrl('assets/img/scanning-free-man.svg')
            },
            upsell_text: function () {
                return this.vsprintf(this.__("Schedule daily, weekly or monthly lockout summary reports for all your websites. This feature is included in a WPMU DEV membership along with 100+ plugins & themes, 24/7 support and lots of handy site management tools  – <a href='%s'>Try it all FREE today</a>!"),this.campaign_url('defender_iplockout_reports_upsell_link'))
            }
        }
    }
</script>