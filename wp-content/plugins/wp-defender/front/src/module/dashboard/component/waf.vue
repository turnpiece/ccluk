<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <img :src="assetUrl('/assets/img/waf@3x.svg')"/>&nbsp;
                {{__('Web Application Firewall')}}
            </h3>
        </div>
        <div class="sui-box-body" v-if="on_us===false">
            <p>
                {{__('The new Web Application Firewall (WAF) filters incoming requests against a highly optimized managed ruleset to block hackers and bot attacks before they reach your site. It\'s our basic Firewall with advanced layers of protection.')}}
            </p>
            <a target="_blank" :href="get_migrate_url" class="sui-button sui-button-blue">{{__('Migrate my site')}}</a>
            <p class="sui-description margin-top-30 text-center" v-html="get_footer_text"></p>
        </div>
        <div class="sui-box-body" v-if="on_us===true && status===false">
            <p>
                {{__('The new Web Application Firewall (WAF) filters incoming requests against a highly optimized managed ruleset to block hackers and bot attacks before they reach your site. It\'s our basic Firewall with advanced layers of protection.')}}
            </p>
            <a target="_blank" :href="get_waf_url" class="sui-button sui-button-blue">{{__('Activate WAF')}}</a>
            <p class="sui-description margin-top-30 text-center" v-html="get_footer_text"></p>
        </div>
        <div class="sui-box-body" v-if="on_us===true && status===true">
            <p>
                {{__('The new Web Application Firewall (WAF) filters incoming requests against a highly optimized managed ruleset to block hackers and bot attacks before they reach your site. It\'s our basic Firewall with advanced layers of protection.')}}
            </p>
            <div class="sui-notice sui-notice-info">
                <div class="sui-notice-content">
                    <div class="sui-notice-message">
                        <i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
                        <p>{{__('This site has WAF protection enabled.')}}</p>
                    </div>
                </div>
            </div>
            <p class="text-center sui-description no-margin-top" v-html="get_waf_text"></p>
        </div>
    </div>
</template>

<script>
    import base_hepler from "../../../helper/base_hepler";

    export default {
        name: "waf",
        mixins: [base_hepler],
        data: function () {
            return {
                on_us: dashboard.waf.waf.hosted,
                site_id: dashboard.waf.site_id,
                status: dashboard.waf.waf.status
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
            get_footer_text: function () {
                if (this.on_us === false) {
                    return this.vsprintf(this.__('You can learn more about the WAF <a target="_blank" href="%s">here</a>.'), 'http://premium.wpmudev.org/waf')
                } else if (this.on_us === true && this.status === false) {
                    return this.vsprintf(this.__('Enable this feature via <a target="_blank" href="%s">The Hub</a> today or learn more <a target="_blank" href="%s">here</a>.'), this.get_waf_url, 'http://premium.wpmudev.org/waf')
                } else if (this.on_us === true && this.status === true) {
                    return this.vsprintf(this.__('At this time, you can manage all WAF settings via <a href="%s">The Hub</a>.'), this.get_waf_url)
                }
            }
        }
    }
</script>