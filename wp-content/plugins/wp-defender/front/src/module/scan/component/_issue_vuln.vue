<template>
    <div class="sui-accordion-item-body" v-if="scenario==='issue'">
        <div class="sui-box">
            <div class="sui-box-body">
                    <pre v-html="item.detail">
                    </pre>
            </div>
            <div class="sui-box-footer">
                <div class="sui-actions-left">
                    <button @click.prevent="ignoreIssue(item)" type="button"
                            :class="{'sui-button-onload':state.on_saving}" :disabled="state.on_saving"
                            class="sui-button sui-button-ghost">
                                <span class="sui-loading-text">
                                    <i class="sui-icon-save" aria-hidden="true"></i>
                                    {{__("Ignore")}}
                                </span>
                        <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="sui-actions-right">
                    <button @click.prevent="solveIssue(item)" type="button"
                            :class="{'sui-button-onload':state.on_saving}" :disabled="state.on_saving"
                            class="sui-button sui-button-blue">
                                <span class="sui-loading-text">
                                    {{__("Update")}}
                                </span>
                        <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import scan_helper from '../helper/scan-helper';

    export default {
        mixins: [scan_helper],
        name: "issue_vuln",
        props: ['item', 'scenario'],
        data: function () {
            return {
                state: {
                    on_saving: false
                },
                nonces: scanData.nonces,
                endpoints: scanData.endpoints,
            }
        },
    }
</script>
