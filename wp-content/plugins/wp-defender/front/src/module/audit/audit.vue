<template>
    <div class="sui-wrap" :class="maybeHighContrast()">
        <div class="auditing" v-if="enabled">
            <div class="sui-header">
                <h1 class="sui-header-title">
                    {{__("Audit Logging")}}
                </h1>
                <doc-link
                        link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#audit-logging"></doc-link>
            </div>
            <summary-box css-class="sui-summary-sm">
                <div class="sui-summary-segment">
                    <div class="sui-summary-details">
                        <span class="sui-summary-large" v-text="summary.events_in_7_days">
                        </span>
                        <span class="sui-summary-sub">
                            {{__("Events logged in the past 7 days")}}
                        </span>
                    </div>
                </div>
                <div class="sui-summary-segment">
                    <ul class="sui-list">
                        <li>
                            <span class="sui-list-label">{{__("Reporting")}}</span>
                            <span class="sui-list-detail" v-text="summary.report_time"></span>
                        </li>
                    </ul>
                </div>
            </summary-box>
            <div class="sui-row-with-sidenav">
                <div class="sui-sidenav">
                    <ul class="sui-vertical-tabs sui-sidenav-hide-md">
                        <li id="tab_log" class="sui-vertical-tab" :class="{current:view==='logs'}">
                            <a @click.prevent="view='logs'"
                               :href="adminUrl('admin.php?page=wdf-logging')">{{__("Event Logs")}}</a>
                        </li>
                        <li id="tab_settings" class="sui-vertical-tab" :class="{current:view==='settings'}">
                            <a @click.prevent="view='settings'"
                               :href="adminUrl('admin.php?page=wdf-logging&view=settings')">{{__("Settings")}}</a>
                        </li>
                        <li id="tab_report" class="sui-vertical-tab" :class="{current:view==='report'}">
                            <a @click.prevent="view='report'"
                               :href="adminUrl('admin.php?page=wdf-logging&view=report')">{{__("Reports")}}</a>
                        </li>
                    </ul>
                    <div class="sui-sidenav-hide-lg">
                        <select class="sui-mobile-nav" style="display: none;">
                            <option value="logs">{{__("Event Logs")}}</option>
                            <option value="settings">{{__("Settings")}}</option>
                            <option value="report">{{__("Reports")}}</option>
                        </select>
                    </div>
                </div>
                <logs v-show="view==='logs'"></logs>
                <settings v-show="view==='settings'"></settings>
                <report v-show="view==='report'"></report>
            </div>
            <app-footer></app-footer>
        </div>
        <div class="auditing" v-else>
            <div class="sui-header">
                <h1 class="sui-header-title">
                    {{__("Audit Logging")}}
                </h1>
                <div class="sui-actions-right">
                    <doc-link
                            link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#audit-logging"></doc-link>
                </div>
            </div>
            <div class="sui-box">
                <div class="sui-box-header">
                    <h3 class="sui-box-title">
                        {{__("Activate")}}
                    </h3>
                </div>
                <div class="sui-message">
                    <img v-if="maybeHideBranding()===false" :src="assetUrl('assets/img/audit-disabled-man.svg')"
                         class="sui-image"
                         aria-hidden="true">

                    <div class="sui-message-content">
                        <p>
                            {{__("Track and log each and every event when changes are made to your website and getdetailed reports on what's going on behind the scenes, including any hacking attempts onyour site.")}}
                        </p>
                        <submit-button type="button" css-class="sui-button-blue activate" :state="state" @click="toggle(true)">
                            {{__("Activate")}}
                        </submit-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import base_hepler from "../../helper/base_hepler";
    import footer from '../../component/footer';
    import logs from "./screen/logs";
    import settings from "./screen/settings";
    import report from './screen/report';

    export default {
        mixins: [base_hepler],
        name: "audit",
        data: function () {
            return {
                view: '',
                summary: {
                    report_time: auditData.summary.report_time,
                    events_in_7_days: '-'
                },
                enabled: auditData.enabled,
                state: {
                    on_saving: false
                },
                nonces: auditData.nonces,
                endpoints: auditData.endpoints,
            }
        },
        components: {
            'app-footer': footer,
            'logs': logs,
            'settings': settings,
            'report': report
        },
        methods: {
            updateSummary: function (count) {
                this.summary.events_in_7_days = count;
            },
            toggle: function (value, type = 'enabled') {
                let that = this;
                let envelope = {};
                envelope[type] = value;
                this.httpPostRequest('updateSettings', {
                    data: JSON.stringify(envelope)
                }, function () {
                    that.enabled = value;
                    that.$nextTick(() => {
                        that.rebindSUI();
                    })
                })
            },
        },
        created: function () {
            //show the current page
            let urlParams = new URLSearchParams(window.location.search);
            let view = urlParams.get('view');
            if (view === null) {
                view = 'logs';
            }
            this.view = view;

            this.$on('events_in_7_days', function (value) {
                this.summary.events_in_7_days = value;
            });
            this.$on('update_report_time', function (value) {
                this.summary.report_time = value.report_time;
            });
            this.$on('enable_state', function (value) {
                this.enabled = value;
            })
        },
        watch: {
            'view': function (val, old) {
                history.replaceState({}, null, this.adminUrl() + "admin.php?page=wdf-logging&view=" + this.view);
            }
        },
        mounted: function () {
            self = this;
            jQuery('.sui-mobile-nav').change(function () {
                self.view = jQuery(this).val()
            })
        },
    }
</script>