<template>
    <div id="audit-logging" class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <i class="sui-icon-eye" aria-hidden="true"></i>
                {{__("Audit Logging")}}
            </h3>
        </div>
        <div class="sui-box-body" :class="{'no-padding-bottom':enabled}">
            <p>
                {{__("Track and log events when changes are made to your website, giving you full visibility over what's going on behind the scenes.")}}
            </p>
            <form method="post" @submit.prevent="updateSettings" v-if="enabled===false">
                <submit-button type="submit" css-class="sui-button-blue activate" :state="state">
                    {{__("Activate")}}
                </submit-button>
            </form>
            <div v-else>
                <div class="sui-notice">
                    <p>
                        {{summary.weekCount}} {{__(" events logged in the past 7 days.")}}
                    </p>
                </div>
                <div class="sui-field-list sui-flushed no-border">
                    <div class="sui-field-list-body">
                        <div class="sui-field-list-item">
                            <label class="sui-field-list-item-label">
                                <strong>{{__("Last event logged")}}</strong>
                            </label>
                            <span>
                                {{summary.lastEvent}}
                            </span>
                        </div>
                        <div class="sui-field-list-item">
                            <label class="sui-field-list-item-label">
                                <strong>{{__("Events logged this month")}}</strong>
                            </label>
                            <span>{{summary.monthCount}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="enabled===true" class="sui-box-footer">
            <div class="sui-actions-left">
                <a :href="adminUrl('admin.php?page=wdf-logging')"
                   class="sui-button sui-button-ghost">
                    <i class="sui-icon-eye" aria-hidden="true"></i>
                    {{__("View Logs")}}
                </a>
            </div>
            <div class="sui-actions-right">
                <p class="sui-p-small" v-text="reportText">
                </p>
            </div>
        </div>
        <overlay v-if="state.on_saving"></overlay>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler'

    export default {
        mixins: [base_helper],
        name: "audit",
        data: function () {
            return {
                state: {
                    on_saving: false,
                },
                nonces: dashboard.audit.nonces,
                endpoints: dashboard.audit.endpoints,
                enabled: dashboard.audit.enabled,
                report: dashboard.audit.report,
                summary: {
                    monthCount: '-',
                    dayCount: '-',
                    weekCount: 'n/a',
                    lastEvent: '-'
                }
            }
        },
        methods: {
            updateSettings: function () {
                let self = this;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify({
                        enabled: true,
                    })
                }, function () {
                    self.enabled = true;
                    self.$nextTick(() => {
                        self.loadData()
                    })
                })
            },
            loadData: function () {
                let self = this;
                this.httpGetRequest('summary', {}, function (response) {
                    self.summary = response.data
                })
            }
        },
        computed: {
            reportText: function () {
                if (this.report) {
                    return this.__("Audit log reports are enabled")
                } else {
                    return this.__("Audit log reports are disabled")
                }
            }
        },
        mounted: function () {
            if (this.enabled === true) {
                this.loadData();
            }
        }
    }
</script>