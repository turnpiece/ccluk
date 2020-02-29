<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <i class="sui-icon-layers" aria-hidden="true"></i>
                {{__("File Scanning")}}
            </h3>
            <div class="sui-actions-left" v-if="scan !== null && scan.status === 'finish'">
                <span class="sui-tag sui-tag-error"
                      v-if="scan !==null && scan.count.total > 0">{{scan.count.total}}</span>
            </div>
        </div>
        <div class="sui-box-body" :class="{'no-padding-bottom':scan!==null && scan.status==='finish'}">
            <p>
                {{__("Scan your website for file changes, vulnerabilities and injected code and get notified about anything suspicious.")}}
            </p>

            <div v-if="scan===null">
                <submit-button @click="newScan" type="button" css-class="sui-button-blue" :state="state">
                    {{__("Run scan")}}
                </submit-button>
            </div>
            <div v-else-if="scan.status==='process' || scan.status==='init'">
                <div class="sui-progress-block">
                    <div class="sui-progress">
                        <span class="sui-progress-icon" aria-hidden="true">
                            <i class="sui-icon-loader sui-loading"></i>
                        </span>
                        <span class="sui-progress-text">
                            <span v-text="percent+'%'"></span>
                        </span>
                        <div class="sui-progress-bar" aria-hidden="true">
                            <span :style="{'width':percent+'%'}"></span>
                        </div>
                    </div>
                    <button @click="cancelScan" type="button" :disabled="state.canceling"
                            class="sui-button-icon sui-tooltip" data-tooltip="Cancel">
                        <i class="sui-icon-close" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="sui-progress-state">
                    <span v-text="statusText"></span>
                </div>
            </div>
            <div v-else class="sui-field-list sui-flushed no-border">
                <div class="sui-field-list-body">
                    <div class="sui-field-list-item">
                        <label class="sui-field-list-item-label">
                            <strong>
                                {{__("WordPress Core")}}
                            </strong>
                        </label>
                        <span v-html="resultIndicator(scan.count.core)"></span>
                    </div>
                    <div class="sui-field-list-item">
                        <label class="sui-field-list-item-label">
                            <strong>
                                {{__("Plugins &amp; Themes")}}
                            </strong>
                        </label>
                        <span v-html="resultIndicator(scan.count.vuln)"></span>
                    </div>
                    <div class="sui-field-list-item">
                        <label class="sui-field-list-item-label">
                            <strong>{{__("Suspicious Code")}}</strong>
                        </label>
                        <span v-html="resultIndicator(scan.count.content)"></span>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="scan!==null && scan.status==='finish'" class="sui-box-footer">
            <div class="sui-actions-left">
                <a :href="adminUrl('admin.php?page=wdf-scan')"
                   class="sui-button sui-button-ghost">
                    <i class="sui-icon-eye" aria-hidden="true"></i>
                    {{__("View Report")}}
                </a>
            </div>
            <div class="sui-actions-right">
                <p class="sui-p-small" v-text="reportText">
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler'

    export default {
        mixins: [base_helper],
        name: "file-scanning",
        data: function () {
            return {
                scan: dashboard.scan.scan,
                state: {
                    on_saving: false,
                    canceling: false
                },
                nonces: dashboard.scan.nonces,
                endpoints: dashboard.scan.endpoints,
                polling_state: null,
                report: dashboard.scan.report,
            };
        },
        methods: {
            newScan: function () {
                let self = this;
                this.httpPostRequest('newScan', {}, function (response) {
                    self.$nextTick(() => {
                        self.scan = {};
                        self.scan.status = response.data.status;
                        self.scan.percent = response.data.percent;
                        self.scan.status_text = response.data.status_text
                        self.polling();
                    })
                });
            },
            cancelScan: function () {
                if (this.state.canceling === true) {
                    //a request in process
                    return;
                }
                //abort all ajax request, as we can have the process can ongoing
                this.abortAllRequests();
                let self = this;
                clearTimeout(this.polling_state);
                this.state.canceling = true;
                this.httpPostRequest('cancelScan', {}, function (response) {
                    self.$nextTick(() => {
                        self.scan = response.data.scan;
                        self.state.canceling = false;
                        self.$emit('scanCanceled', self.scan)
                    })
                })
            },
            refreshStatus: function () {
                let self = this;
                this.httpPostRequest('processScan', {}, function (response) {
                    if (response.success === false) {
                        self.scan = response.data;
                        self.polling();
                    } else {
                        self.scan = response.data.scan
                        self.$emit('scanCompleted', self.scan, response.data.scan.count.total)
                    }
                })
            },
            polling: function () {
                if (this.state.canceling === false) {
                    this.polling_state = setTimeout(this.refreshStatus(), 500)
                }
            },
            resultIndicator: function (count) {
                if (count > 0) {
                    return '<span class="sui-tag sui-tag-error">' + count + '</span>';
                }

                return '<i aria-hidden="true" class="sui-icon-check-tick sui-success"></i>';
            }
        },
        computed: {
            statusText: function () {
                return this.scan.status_text;
            },
            reportText: function () {
                if (this.report.enabled === false) {
                    return;
                }
                let frequency;
                switch (parseInt(this.report.frequency)) {
                    case 1:
                        frequency = 'daily';
                        break;
                    case 7:
                        frequency = 'weekly';
                        break;
                    case 30:
                        frequency = 'monthly';
                }

                let text = this.vsprintf(this.__("Automatic scans are running %s"), frequency);
                return text;
            },
            percent: function () {
                return this.scan.percent;
            }
        },
        mounted: function () {
            if (this.scan !== null && (this.scan.status === 'process' || this.scan.status === 'init')) {
                this.polling();
            }
        }
    }
</script>