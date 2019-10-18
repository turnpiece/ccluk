<template>
    <div id="defender-app" class="sui-wrap" :class="maybeHighContrast()">
        <div class="wp-defender" id="wp-defender">
            <div class="iplockout">
                <div class="sui-header">
                    <h1 class="sui-header-title">{{__("IP Lockout")}}</h1>
                    <doc-link link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#ip-lockouts"></doc-link>
                </div>
                <summary-box>
                    <div class="sui-summary-segment">
                        <div class="sui-summary-details">
                            <span class="sui-summary-large lockoutToday">{{summary_data.day}}</span>
                            <span class="sui-summary-sub">{{__("Lockouts in the past 24 hours")}}</span>

                            <span class="sui-summary-detail lockoutThisMonth">{{summary_data.month}}</span>
                            <span class="sui-summary-sub">{{__("Total lockouts in the past 30 days")}}</span>
                        </div>
                    </div>
                    <div class="sui-summary-segment">
                        <ul class="sui-list">
                            <li>
                                <span class="sui-list-label">{{__("Last lockout")}}</span>
                                <span class="sui-list-detail"> {{summary_data.lastLockout}}</span>
                            </li>
                            <li>
                                <span class="sui-list-label">{{__("Login lockouts in the past 7 days")}}</span>
                                <span class="sui-list-detail">{{summary_data.ip.week}}</span>
                            </li>
                            <li>
                                <span class="sui-list-label">{{__("404 lockouts in the past 7 days")}}</span>
                                <span class="sui-list-detail">{{summary_data.nf.week}}</span>
                            </li>
                        </ul>
                    </div>
                </summary-box>
                <div class="sui-row-with-sidenav">
                    <div class="sui-sidenav">
                        <ul class="sui-vertical-tabs sui-sidenav-hide-md">
                            <li :class="{current:view==='login'}" class="sui-vertical-tab">
                                <a @click.prevent="view='login'" data-tab="login_lockout"
                                   :href="adminUrl('admin.php?page=wdf-ip-lockout')">{{__("Login Protection")}}</a>
                            </li>
                            <li :class="{current:view==='404'}" class="sui-vertical-tab">
                                <a @click.prevent="view='404'" data-tab="notfound_lockout"
                                   :href="adminUrl('admin.php?page=wdf-ip-lockout&view=404')">{{__("404 Detection")}}</a>
                            </li>
                            <li :class="{current:view==='blacklist'}" class="sui-vertical-tab">
                                <a @click.prevent="view='blacklist'" data-tab="blacklist"
                                   :href="adminUrl('admin.php?page=wdf-ip-lockout&view=blacklist')">{{__("IP Banning")}}</a>
                            </li>
                            <li :class="{current:view==='logs'}" class="sui-vertical-tab">
                                <a @click.prevent="view='logs'" data-tab="logs"
                                   :href="adminUrl('admin.php?page=wdf-ip-lockout&view=logs')">{{__("Logs")}}</a>
                            </li>
                            <li :class="{current:view==='notification'}" class="sui-vertical-tab">
                                <a @click.prevent="view='notification'" data-tab="notification"
                                   :href="adminUrl('admin.php?page=wdf-ip-lockout&view=notification')">{{__("Notifications")}}</a>
                            </li>
                            <li :class="{current:view==='settings'}" class="sui-vertical-tab">
                                <a @click.prevent="view='settings'" data-tab="settings"
                                   :href="adminUrl('admin.php?page=wdf-ip-lockout&view=settings')">{{__("Settings")}}</a>
                            </li>
                            <li :class="{current:view==='reporting'}" class="sui-vertical-tab">
                                <a @click.prevent="view='reporting'" data-tab="reporting"
                                   :href="adminUrl('admin.php?page=wdf-ip-lockout&view=reporting')">{{__("Reporting")}}</a>
                            </li>
                        </ul>
                        <div class="sui-sidenav-hide-lg">
                            <select class="sui-mobile-nav" style="display: none;">
                                <option value="login">{{__("Login Protection")}}</option>
                                <option value="404">{{__("404 Detection")}}</option>
                                <option value="blacklist">{{__("IP Banning")}}</option>
                                <option value="logs">{{__("Logs")}}</option>
                                <option value="notification">{{__("Notifications")}}</option>
                                <option value="settings">{{__("Settings")}}</option>
                                <option value="reporting">{{__("Reporting")}}</option>
                            </select>
                        </div>
                    </div>
                    <lockout v-show="view==='login'"></lockout>
                    <nf-lockout v-show="view==='404'"></nf-lockout>
                    <ip-blacklist v-show="view==='blacklist'"></ip-blacklist>
                    <logs v-show="view==='logs'"></logs>
                    <notification v-show="view==='notification'"></notification>
                    <settings v-show="view==='settings'"></settings>
                    <report_free v-show="view==='reporting'" v-if="is_free===1"></report_free>
                    <report v-show="view==='reporting'" v-else></report>
                </div>
            </div>
            <app-footer></app-footer>
        </div>
    </div>
</template>

<script>
    import base_helper from '../../helper/base_hepler';
    import lockout from './screen/lockout.vue';
    import nf_lockout from './screen/notfound-lockout.vue';
    import ip_blacklist from './screen/ip-blacklist.vue';
    import logs from './screen/logs.vue';
    import notification from './screen/notification';
    import settings from './screen/settings';
    import report from './screen/report';
    import report_free from './screen/report-free';

    export default {
        name: 'ip-lockout',
        mixins: [base_helper],
        data: function () {
            return {
                state: {
                    on_saving: false,
                },
                summary_data: iplockout.summaryData,
                is_free: parseInt(defender.is_free),
                view: '',
            }
        },
        components: {
            'lockout': lockout,
            'nf-lockout': nf_lockout,
            'ip-blacklist': ip_blacklist,
            'logs': logs,
            'notification': notification,
            'settings': settings,
            'report': report,
            'report_free': report_free
        },
        created: function () {
            //show the current page
            let urlParams = new URLSearchParams(window.location.search);
            let view = urlParams.get('view');
            if (view === null) {
                view = 'login';
            }
            this.view = view;
        },
        watch: {
            'view': function () {
                history.replaceState({}, null, this.adminUrl() + "admin.php?page=wdf-ip-lockout&view=" + this.view);
            },
        },
        mounted: function () {
            self = this;
            jQuery('.sui-mobile-nav').change(function () {
                self.view = jQuery(this).val()
            })
        },
    }
</script>
