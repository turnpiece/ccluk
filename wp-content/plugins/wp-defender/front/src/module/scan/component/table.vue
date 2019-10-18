<template>
    <div class="sui-accordion sui-accordion-flushed no-border">
        <div class="sui-accordion-header">
            <div>{{__("Suspicious File ")}}</div>
            <div>{{__("Details")}}</div>
            <div></div>
        </div>
        <div v-if="!maybeFilterThisOut(item)" v-for="(item,index) in items" class="sui-accordion-item"
             :class="{'sui-error':scenario==='issue','sui-default':scenario==='ignored'}">
            <div class="sui-accordion-item-header" @click="maybePullSrcCode(index,item)">
                <div class="sui-accordion-item-title">
                    <label class="sui-checkbox">
                        <input type="checkbox" :value="item.id" v-model="bulk_ids"/>
                        <span aria-hidden="true"></span>
                    </label>
                    {{item.file_name}}
                </div>
                <div v-html="item.short_desc">
                </div>
                <div>
                    <button v-if="scenario==='issue'" class="sui-button-icon sui-accordion-open-indicator"
                            aria-label="Open item">
                        <i class="sui-icon-chevron-down" aria-hidden="true"></i>
                    </button>
                    <button v-else data-tooltip="Restore File" @click.prevent="unignoreIssue(item)" type="button"
                            :class="{'sui-button-onload':state.on_saving}" :disabled="state.on_saving"
                            class="sui-button-icon sui-tooltip sui-tooltip-top float-r-position">
                                <span class="sui-loading-text">
                                   <i class="sui-icon-update"></i>
                                </span>
                        <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <component
                    :ref="index+'issue'"
                    :scenario="scenario"
                    :is="'issue_'+item.type"
                    :key="item.full_path"
                    :item="item">
            </component>
        </div>
    </div>
</template>

<script>
    import scan_helper from '../helper/scan-helper';
    import _issue_core from './_issue_core';
    import _issue_vuln from './_issue_vuln';
    import _issue_content from './_issue_content'
    import store from '../store/store';

    export default {
        mixins: [scan_helper],
        props: ['items', 'scenario', 'bulk'],
        name: "issue-table",
        data: function () {
            return {
                state: {
                    on_saving: false
                },
                nonces: scanData.nonces,
                endpoints: scanData.endpoints,
                bulk_ids: []
            }
        },
        components: {
            'issue_core': _issue_core,
            'issue_content': _issue_content,
            'issue_vuln': _issue_vuln
        },
        methods: {
            maybeFilterThisOut: function (item) {
                if (store.state.active_filter === "" || store.state.active_filter === null) {
                    return false;
                }
                if (item.type === store.state.active_filter) {
                    return false;
                }

                return true;
            },
            maybePullSrcCode: function (index, item) {
                if (item.type === 'content' || item.type === 'core') {
                    let child = this.$refs[index+'issue'];
                    child[0].pullSourceCode()
                }
            }
        },
        watch: {
            bulk: function (value) {
                if (value === true) {
                    for (var i = 0; i < this.items.length; i++) {
                        this.bulk_ids.push(this.items[i].id);
                    }
                } else {
                    this.bulk_ids = [];
                }
            },
            bulk_ids: function () {
                this.$root.store.bulk_ids = this.bulk_ids;
            }
        },
    }
</script>