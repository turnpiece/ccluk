<template>
    <div class="sui-modal sui-modal-md">
        <div role="dialog"
             id="waf-modal"
             aria-modal="true"
             class="sui-modal-content"
             aria-label="waf-modal-label">

            <div class="sui-box">
                <div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
                    <figure class="sui-box-banner" aria-hidden="true">
                        <img :src="assetUrl('assets/img/waf-modal.png')">
                    </figure>
                    <button @click="hide" class="modal-close-button sui-button-icon sui-button-float--right">
                        <i class="sui-icon-close sui-md" aria-hidden="true"></i>
                        <span class="sui-screen-reader-text">{{__('Close this dialog.')}}</span>
                    </button>

                    <h3 class="sui-box-title sui-lg" id="waf-modal-label">
                        {{__('New Web Application Firewall')}}
                    </h3>

                    <p class="sui-description">
                        {{__('The new Web Application Firewall (WAF) is a first layer of protection to block hackers and bot attacks before they reach your site.')}}
                    </p>
                    <div class="text-left waf-description">
                        <p class="sui-description how-does-it-work">{{__('How Does it Work?')}}</p>
                        <p class="sui-description">{{__('The WAF filters requests against a highly optimized managed ruleset covering common attacks (OWASP top ten) and performs virtual patching of WordPress core, plugin, and theme vulnerabilities.')}}</p>
                        <p class="sui-description" v-html="get_link_line"></p>
                    </div>
                </div>
                <div class="sui-box-footer sui-flatten sui-content-center sui-spacing-bottom--50">
                    <submit-button @click="hide" type="submit" :state="state"
                                   css-class="sui-button quicksetup-apply">
                        {{__('Got it')}}
                    </submit-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler'

    export default {
        name: "waf-modal",
        mixins: [base_helper],
        data: function () {
            return {
                nonces: dashboard.new_features.nonces,
                endpoints: dashboard.new_features.endpoints,
                state: {
                    on_saving: false
                }
            }
        },
        methods: {
            hide: function () {
                this.httpPostRequest('hide', this.model, function (response) {
                    SUI.closeModal()
                })
            }
        },
        computed: {
            get_link_line: function () {
                let featureLink = '';
                let wafLink = 'http://premium.wpmudev.org/waf';
                if ( ! dashboard.waf.waf.hosted || parseInt(defender.is_free) === 1 ) {
                    featureLink = this.vsprintf(
                        this.__('This feature is available to members who host their sites with WPMU DEV. You can learn more about WAF <a target="_blank" href="%s">here</a>.'),
                        wafLink
                    );
                } else if ( dashboard.waf.waf.hosted && ! dashboard.waf.waf.whitelabel_enable ) {
                    featureLink = this.vsprintf(
                        this.__('Enable this feature via <a target="_blank" href="%s"">The Hub</a> today or learn more <a target="_blank" href="%s">here</a>.'),
                        'https://premium.wpmudev.org/hub2/site/' + dashboard.waf.site_id + '/hosting/tools#update-waf',
                        wafLink
                    );
                }
                return featureLink;
            }
        },
        mounted() {
            document.onreadystatechange = () => {
                if (document.readyState === "complete") {
                    const modalId = 'waf-modal',
                        focusAfterClosed = 'wpbody',
                        focusWhenOpen = 'waf-modal',
                        hasOverlayMask = false,
                        isCloseOnEsc = false
                    ;

                    SUI.openModal(
                        modalId,
                        focusAfterClosed,
                        focusWhenOpen,
                        hasOverlayMask,
                        isCloseOnEsc
                    );
                }
            }
        }
    }
</script>