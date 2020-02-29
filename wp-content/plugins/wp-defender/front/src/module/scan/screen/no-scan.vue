<template>
    <div class="sui-wrap" :class="maybeHighContrast()">
        <div class="file-scanning">
            <div class="sui-header">
                <h1 class="sui-header-title">
                    {{__("File Scanning")}}
                </h1>
                <div class="sui-actions-right">
                    <doc-link link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#security-scans"></doc-link>
                </div>
            </div>
            <div class="sui-box sui-message">
                <img v-if="maybeHideBranding() === false" :src="assetUrl('/assets/img/no-scan-man.svg')" class="sui-image" aria-hidden="true">
                <div class="sui-message-content">
                    <p>
                        {{__("Scan your website for file changes, vulnerabilities and injected code and get notified about anything suspicious. Defender will keep an eye on your code without you having to worry.")}}
                    </p>
                    <form method="post" @submit.prevent="newScan">
                        <submit-button type="submit" css-class="sui-button-blue" :state="state">
                            {{__("Run Scan")}}
                        </submit-button>
                    </form>
                </div>
            </div>
            <app-footer></app-footer>
        </div>
    </div>
</template>

<script>
    import helper from "../helper/scan-helper";
    import footer from '../../../component/footer'
    import store from '../store/store';

    export default {
        mixins: [helper],
        name: "no-scan",
        data: function () {
            return {
                state: {
                    on_saving: false
                },
                endpoints: scanData.endpoints,
                nonces: scanData.nonces
            }
        },
        components: {
            'app-footer': footer
        },
    }
</script>