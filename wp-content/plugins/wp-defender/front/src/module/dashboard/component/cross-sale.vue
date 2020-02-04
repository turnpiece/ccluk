<template xmlns="http://www.w3.org/1999/html">
    <section>
        <div id="sui-cross-sell-footer" class="sui-row">
            <div><span class="sui-icon-plugin-2"></span></div>
            <h3>{{__("Check out our other free wordpress.org plugins!")}}</h3>

        </div>

        <!-- Cross-Sell Modules -->
        <div class="sui-row sui-cross-sell-modules">
            <div class="sui-col-md-4">
                <!-- Cross-Sell Banner #1 -->
                <div aria-hidden="true" class="sui-cross-1">
                    <span></span>
                </div>

                <div class="sui-box">
                    <div class="sui-box-body">
                        <h3>{{__("Smush Image Compression and Optimization")}}</h3>
                        <p>{{__("Resize, optimize and compress all of your images with the incredibly powerful and award-winning, 100% free WordPress image optimizer.")}}</p>
                        <a href="https://wordpress.org/plugins/wp-smushit/"
                           target="_blank"
                           class="sui-button sui-button-ghost">
                            {{__("View features")}} <i aria-hidden="true" class="sui-icon-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="sui-col-md-4">
                <!-- Cross-Sell Banner #2 -->
                <div aria-hidden="true" class="sui-cross-2">
                    <span></span>
                </div>
                <div class="sui-box">
                    <div class="sui-box-body">
                        <h3>{{__("Hummingbird Page Speed Optimization")}}</h3>
                        <p>{{__("Performance Tests, File Optimization & Compression, Page, Browser & Gravatar Caching, GZIP Compression, CloudFlare Integration & more.")}}</p>
                        <a href="https://wordpress.org/plugins/hummingbird-performance/"
                           target="_blank"
                           class="sui-button sui-button-ghost">
                            {{__("View features")}} <i aria-hidden="true" class="sui-icon-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="sui-col-md-4">
                <!-- Cross-Sell Banner #3 -->
                <div aria-hidden="true" class="sui-cross-3">
                    <span></span>
                </div>

                <div class="sui-box">
                    <div class="sui-box-body">
                        <h3>{{__("SmartCrawl Search Engine Optimization")}}</h3>
                        <p>{{__("Customize Titles & Meta Data, OpenGraph, Twitter & Pinterest Support, Auto-Keyword Linking, SEO & Readability Analysis, Sitemaps, URL Crawler & more.")}}</p>
                        <a href="https://wordpress.org/plugins/smartcrawl-seo/"
                           target="_blank"
                           class="sui-button sui-button-ghost">
                            {{__("View features")}} <i aria-hidden="true" class="sui-icon-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="sui-cross-sell-bottom">
            <h3>{{__("WPMU DEV - Your All-in-One WordPress Platform")}}</h3>
            <p>{{__("Pretty much everything you need for developing and managing WordPress based websites, and then some")}}</p>

            <a href="https://premium.wpmudev.org/?utm_source=defender&utm_medium=plugin&utm_campaign=defender_dash_footer_upsell_notice" target="_blank" role="button"
               class="sui-button sui-button-green">{{__("Learn more")}}
            </a>

            <img class="sui-image" :src="assetUrl('assets/img/dev-team.png')" :srcset="assetUrl('assets/img/dev-team@2x.png 2x')"
                 aria-hidden="true">

        </div>
    </section>
</template>

<script>
    import base_helper from '../../../helper/base_hepler'

    export default {
        mixins: [base_helper],
        name: "cross-sale"
    }
</script>