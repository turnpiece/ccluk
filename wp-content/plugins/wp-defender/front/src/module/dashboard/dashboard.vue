<template>
	<div class="sui-wrap" :class="maybeHighContrast()">
		<div class="defender-dashboard">
			<div class="sui-header">
				<h1 class="sui-header-title">{{__("Dashboard")}}</h1>
				<div class="sui-actions-right">
					<doc-link link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender"></doc-link>
				</div>
			</div>
			<summary-box>
				<div class="sui-summary-segment">
					<div class="sui-summary-details">
						<span class="sui-summary-large" v-text="countTotalIssues"></span>
						<span class="sui-tooltip sui-tooltip-top-left sui-tooltip-constrained" :data-tooltip="tooltips">
                            <i aria-hidden="true" class="sui-icon-check-tick sui-success"
                               v-if="this.security_tweaks.count.issues === 0 && this.scan.count === 0">
                            </i>
                            <i class="sui-icon-info sui-warning" aria-hidden="true" v-else></i>
                        </span>
						<span class="sui-summary-sub">{{__("Security Issue")}}</span>
					</div>
				</div>
				<div class="sui-summary-segment">
					<ul class="sui-list">
						<li>
							<span class="sui-list-label">{{__("Security Tweaks Actioned")}}</span>
							<span class="sui-list-detail" v-text="securityTweaksIndicator"></span>
						</li>
						<li>
							<span class="sui-list-label">{{__("File Scan Issues")}}</span>
							<span class="sui-list-detail">
                                <submit-button @click="newScan" v-if="scan.scan===null" type="button"
                                               css-class="sui-button-blue"
                                               :state="state">
                                    {{__("New Scan")}}
                                </submit-button>
                                <i v-else-if="scan.scan.status==='init' || scan.scan.status==='progress'"
                                   class="sui-icon-loader sui-loading"></i>
                                <i v-else-if="scan.count === 0" class="sui-icon-check-tick sui-success"></i>
                                <span class="sui-tag sui-tag-error" v-else>{{scan.count}}</span>
                            </span>
						</li>
						<li>
							<span class="sui-list-label">{{__("Last Lockout")}}</span>
							<span class="sui-list-detail">{{ ip_lockout.last_lockout }}</span>
						</li>
					</ul>
				</div>
			</summary-box>
			<div class="sui-row">
				<div class="sui-col-md-6">
					<security-tweaks></security-tweaks>
					<blacklist v-if="is_free===0"></blacklist>
					<blacklist-free v-else-if="is_free===1"></blacklist-free>
					<advanced-tools></advanced-tools>
				</div>
				<div class="sui-col-md-6">
					<file-scanning @scanCanceled="scanCanceled" @scanCompleted="scanCompleted" ref="file-scanning"
					               v-if="is_free===0"></file-scanning>
					<file-scanning-free @scanCanceled="scanCanceled" scanCompleted="scanCompleted" ref="file-scanning"
					                    v-else-if="is_free===1"></file-scanning-free>
					<ip-lockout></ip-lockout>
					<audit v-if="is_free===0"></audit>
					<audit-free v-else-if="is_free===1"></audit-free>
					<report v-if="is_free===0"></report>
					<report-free v-else-if="is_free===1"></report-free>
				</div>
			</div>
			<cross-sale v-if="is_free === 1"></cross-sale>
			<app-footer></app-footer>
		</div>
		<quick-setup v-if="quick_setup===1 && is_free===0"></quick-setup>
		<quick-setup-free v-else-if="quick_setup===1 && is_free===1"></quick-setup-free>
	</div>
</template>

<script>
	import helper from '../../helper/base_hepler';
	import security_tweaks from './component/security-tweaks';
	import file_scanning from './component/file-scanning';
	import file_scanning_free from './component/file-scanning-free';
	import blacklist from './component/blacklist';
	import blacklist_free from './component/blacklist-free';
	import ip_lockout from './component/ip-lockout';
	import audit from './component/audit';
	import audit_free from './component/audit-free';
	import report from './component/report';
	import report_free from './component/report-free'
	import advanced_tools from './component/advanced-tools'
	import quick_setup from './component/quick-setup';
	import quick_setup_free from './component/quick-setup-free';
	import cross_sale from './component/cross-sale';

	export default {
		mixins: [helper],
		name: "dashboard",
		data: function () {
			return {
				quick_setup: parseInt(dashboard.quick_setup.show),
				is_free: parseInt(defender.is_free),
				security_tweaks: {
					count: {
						issues: dashboard.security_tweaks.count.issues,
						resolved: dashboard.security_tweaks.count.resolved,
						total: dashboard.security_tweaks.count.total
					},
				},
				scan: {
					count: 0,
					scan: dashboard.scan.scan,
				},
				ip_lockout: {
					last_lockout: dashboard.ip_lockout.summary.lastLockout
				},
				nonces: dashboard.scan.nonces,
				endpoints: dashboard.scan.endpoints,
				state: {
					on_saving: false
				}
			}
		},
		components: {
			'security-tweaks': security_tweaks,
			'file-scanning': file_scanning,
			'file-scanning-free': file_scanning_free,
			'blacklist': blacklist,
			'blacklist-free': blacklist_free,
			'ip-lockout': ip_lockout,
			'audit': audit,
			'audit-free': audit_free,
			report,
			'report-free': report_free,
			'advanced-tools': advanced_tools,
			'quick-setup': quick_setup,
			'quick-setup-free': quick_setup_free,
			'cross-sale': cross_sale
		},
		methods: {
			countScanIssues: function () {
				let scan = dashboard.scan.scan;
				if (scan === null || scan.status === 'init' || scan.status === 'progress') {
					return 0;
				}
				return scan.count.total;
			},
			newScan: function () {
				let self = this;
				this.httpPostRequest('newScan', {}, function (response) {
					self.$nextTick(() => {
						let child = self.$refs['file-scanning'];
						child.scan = {};
						self.scan.scan = {};
						self.scan.scan.status = response.data.status;
						child.scan.status = response.data.status;
						child.scan.percent = response.data.percent;
						child.scan.status_text = response.data.status_text
						child.polling();
					})
				});
			},
			scanCanceled: function (scan) {
				this.scan.scan = scan;
			},
			scanCompleted: function (scan, total) {
				this.scan.count = total;
				this.scan.scan = scan;
			}
		},
		computed: {
			tooltips: function () {
				let tooltips = this.__("You don't have any outstanding security issues, nice work!");
				if (this.security_tweaks.count.issues === 1 && this.scan.count === 0) {
					tooltips = this.__("You have one security tweak left to do. We recommend you action it, or ignore it if it's irrelevant.")
				} else if (this.security_tweaks.count.issues === 0 && this.scan.count === 1) {
					tooltips = this.__("We've detected a potential security risk in your file system. We recommend you take a look and action a fix, or ignore the file if it's harmless.")
				} else if (this.security_tweaks.count.issues === 1 && this.scan.count === 1) {
					tooltips = this.__("You have one security tweak left to do, and one potential security risk in your file system. We recommend you take a look and action fixes, or ignore the issues if they are harmless.")
				} else if (this.security_tweaks.count.issues === 1 && this.scan.count > 1) {
					tooltips = this.vsprintf(this.__("You have one security tweak left to do, and %s potential security risks in your file system. We recommend you take a look and action fixes, or ignore the issues if they are harmless"), this.scan.count)
				} else if (this.security_tweaks.count.issues > 1 && this.scan.count === 1) {
					tooltips = this.vsprintf(this.__("You have %s security tweaks left to do, and one potential security risk in your file system. We recommend you take a look and action fixes, or ignore the issues if they are harmless."), this.security_tweaks.count.issues)
				} else if (this.security_tweaks.count.issues > 1 && this.scan.count > 1) {
					tooltips = this.vsprintf(this.__("You have %s security tweaks left to do, and %s potential security risks in your file system. We recommend you take a look and action fixes, or ignore the issues if they are harmless."), this.security_tweaks.count.issues, this.scan.count)
				} else if (this.security_tweaks.count.issues > 1 && this.scan.count === 0) {
					tooltips = this.vsprintf(this.__("You have %d security tweaks left to do. We recommend you action it, or ignore it if it's irrelevant."), this.security_tweaks.count.issues)
				} else if (this.security_tweaks.count.issues === 0 && this.scan.count > 1) {
					tooltips = this.vsprintf(this.__("We've detected %d potential security risks in your file system. We recommend you take a look and action a fix, or ignore the file if it's harmless."), this.scan.count)
				}
				return tooltips;
			},
			securityTweaksIndicator: function () {
				return this.security_tweaks.count.resolved + '/' + this.security_tweaks.count.total
			},
			countTotalIssues: function () {
				return this.scan.count + this.security_tweaks.count.issues
			},
		},
		mounted: function () {
			this.$nextTick(() => {
				this.scan.count = this.countScanIssues()
			})
		}
	}
</script>