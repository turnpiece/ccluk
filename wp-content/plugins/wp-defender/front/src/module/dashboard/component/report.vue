<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <i class="sui-icon-graph-line" aria-hidden="true"></i>
                {{__("Reporting")}}
            </h3>
        </div>
        <div class="sui-box-body no-padding-bottom">
            <p>{{__("Get tailored security reports delivered to your inbox so you don't have to worry about checking in.")}}</p>
            <div class="sui-field-list sui-flushed no-border">
                <div class="sui-field-list-body">
                    <div class="sui-field-list-item">
                        <label class="sui-field-list-item-label">
                            <small><strong>{{__("File Scanning")}}</strong></small>
                        </label>
                        <span v-html="statusText(scan)"></span>
                    </div>
                    <div class="sui-field-list-item">
                        <label class="sui-field-list-item-label">
                            <small><strong>{{__("IP Lockouts")}}</strong></small>
                        </label>
                        <span v-html="statusText(ip_lockout)"></span>
                    </div>
                    <div class="sui-field-list-item">
                        <label class="sui-field-list-item-label">
                            <small><strong>{{__("Audit Logging")}}</strong></small>
                        </label>
                        <span v-html="statusText(audit)"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="sui-box-footer">
            <p class="sui-p-small text-center">
                {{__("You can also")}} <a target="_blank"
                                          href="https://premium.wpmudev.org/reports/">{{__("create PDF reports")}}</a> {{__("to send to your clients via The Hub.")}}
            </p>
        </div>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler'

    export default {
        mixins: [base_helper],
        name: "report",
        data: function () {
            return {
                scan: dashboard.report.scan,
                ip_lockout: dashboard.report.ip_lockout,
                audit: dashboard.report.audit
            }
        },
        methods: {
            statusText: function (frequency) {
                if (frequency === -1) {
                    return '<span class="sui-tag sui-tag-disabled">' + this.__("Inactive") + '</span>';
                } else {
                    let text;
                    switch (parseInt(frequency)) {
                        case 1:
                            text = this.__('Daily');
                            break;
                        case 7:
                            text = this.__('Weekly');
                            break;
                        case 30:
                            text = this.__('Monthly');
                            break;
                    }
                    return '<span class="sui-tag sui-tag-blue">' + text + '</span>';
                }
            }
        }
    }
</script>