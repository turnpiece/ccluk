<template>
	<div class="sui-wrap" :class="maybeHighContrast()">
		<div class="file-scanning">
			<div class="sui-header">
				<h1 class="sui-header-title">
					{{__("FILE SCANNING")}}
				</h1>
				<div class="sui-actions-left">
					<submit-button type="button" @click="newScan" css-class="sui-button-blue" :state="state">
						{{__("New Scan")}}
					</submit-button>
				</div>
				<div class="sui-actions-right">
					<doc-link
							link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#security-scans"></doc-link>
				</div>
			</div>
			<summary-box>
				<div class="sui-summary-segment">
					<div class="sui-summary-details">
						<span class="sui-summary-large" v-text="count_total"></span>
						<span class="sui-tooltip sui-tooltip-top-center sui-tooltip-constrained"
						      :data-tooltip="tooltips">
                            <i aria-hidden="true"
                               :class="{'sui-icon-info sui-warning':count_total > 0,'sui-icon-check-tick sui-success':count_total===0}"></i>
                        </span>
						<span class="sui-summary-sub">{{__("File scanning issues")}}</span>
						<span class="sui-summary-detail" v-text="last_scan_date"></span>
						<span class="sui-summary-sub">{{__("Last scan")}}</span>
					</div>
				</div>
				<div class="sui-summary-segment">
					<ul class="sui-list">
						<li>
							<span class="sui-list-label">{{__("Wordpress core")}}</span>
							<span class="sui-list-detail">
                                <span class="sui-tag sui-tag-error" v-if="count_core > 0" v-text="count_core">
                                </span>
                                <i v-else class="sui-icon-check-tick sui-success" aria-hidden="true"></i>
                            </span>
						</li>
						<li>
							<span class="sui-list-label">{{__("Plugins & themes")}}</span>
							<span v-if="is_free===1">
                                 <a :href="campaign_url('defender_dash_filescan_pro_tag')" target="_blank"
                                    class="sui-button sui-button-purple sui-tooltip"
                                    data-tooltip="Try Defender Pro free today">
                                    {{__("Pro Feature")}}
                                </a>
                            </span>
							<span v-else>
                                <span class="sui-tag sui-tag-error" v-if="count_vuln > 0" v-text="count_vuln">
                                </span>
                                <i v-else class="sui-icon-check-tick sui-success" aria-hidden="true"></i>
                            </span>
						</li>
						<li>
							<span class="sui-list-label">{{__("Suspicious code")}}</span>
							<span v-if="is_free===1">
                                 <a :href="campaign_url('defender_dash_filescan_pro_tag')" target="_blank"
                                    class="sui-button sui-button-purple sui-tooltip"
                                    data-tooltip="Try Defender Pro free today">
                                    {{__("Pro Feature")}}
                                </a>
                            </span>
							<span v-else>
                                 <span class="sui-tag sui-tag-error" v-if="count_content > 0" v-text="count_content">
                                 </span>
                                <i v-else class="sui-icon-check-tick sui-success" aria-hidden="true"></i>
                            </span>
						</li>
					</ul>
				</div>
			</summary-box>
			<div class="sui-row-with-sidenav">
				<div class="sui-sidenav">
					<ul class="sui-vertical-tabs sui-sidenav-hide-md">
						<li class="sui-vertical-tab" :class="{current:view==='issues'}">
							<a :href="adminUrl('admin.php?page=wdf-scan')" v-on:click.prevent="view = 'issues'">
								{{__("Issues")}}
							</a>
						</li>
						<li class="sui-vertical-tab" :class="{current:view==='ignored'}">
							<a :href="adminUrl('admin.php?page=wdf-scan&view=ignored')"
							   v-on:click.prevent="view = 'ignored'">
								{{__("Ignored")}}
							</a>
						</li>
						<li class="sui-vertical-tab" :class="{current:view==='settings'}">
							<a :href="adminUrl('admin.php?page=wdf-scan&view=settings')"
							   v-on:click.prevent="view = 'settings'">
								{{__("Settings")}}
							</a>
						</li>
						<li class="sui-vertical-tab" :class="{current:view==='notification'}">
							<a :href="adminUrl('admin.php?page=wdf-scan&view=notification')"
							   v-on:click.prevent="view = 'notification'">
								{{__("Notification")}}
							</a>
						</li>
						<li class="sui-vertical-tab" :class="{current:view==='reporting'}">
							<a :href="adminUrl('admin.php?page=wdf-scan&view=reporting')"
							   v-on:click.prevent="view = 'reporting'">
								{{__("Reporting")}}
							</a>
						</li>
					</ul>
					<div class="sui-sidenav-hide-lg">
						<select class="sui-mobile-nav" style="display: none;">
							<option value="issues">{{__("Issues")}}</option>
							<option value="ignored">{{__("Ignored")}}</option>
							<option value="settings">{{__("Settings")}}</option>
							<option value="notification">{{__("Notification")}}</option>
							<option value="reporting">{{__("Reporting")}}</option>
						</select>
					</div>
				</div>
				<issues v-show="view==='issues'"></issues>
				<ignore v-show="view==='ignored'"></ignore>
				<settings v-if="is_free===0" v-show="view==='settings'"></settings>
				<settings-free v-else v-show="view==='settings'"></settings-free>
				<notification v-show="view==='notification'"></notification>
				<reporting v-if="is_free===0" v-show="view==='reporting'"></reporting>
				<reporting-free v-if="is_free===1" v-show="view==='reporting'"></reporting-free>
			</div>
			<app-footer></app-footer>
		</div>
	</div>
</template>

<script>
	import helper from "../helper/scan-helper";
	import footer from '../../../component/footer'
	import _issues from './_issues'
	import _ignore from './_ignore'
	import _settings from "./_settings";
	import _settings_free from './_settings-free'
	import _notification from "./_notification";
	import _reporting from "./_reporting";
	import _reporting_free from "./_reporting-free";

	export default {
		mixins: [helper],
		name: "scan-result",
		data: function () {
			return {
				view: '',
				accessibility: {
					high_contrast: defender.high_contrast,
				},
				whitelabel: defender.whitelabel,
				state: {
					on_saving: false
				},
				endpoints: scanData.endpoints,
				nonces: scanData.nonces,
				is_free: parseInt(defender.is_free)
			}
		},
		components: {
			'app-footer': footer,
			'issues': _issues,
			'ignore': _ignore,
			'settings': _settings,
			'settings-free': _settings_free,
			'notification': _notification,
			'reporting': _reporting,
			'reporting-free': _reporting_free
		},
		methods: {},
		created: function () {
			//show the current page
			let urlParams = new URLSearchParams(window.location.search);
			let view = urlParams.get('view');
			if (view === null) {
				view = 'issues';
			}
			this.view = view;
		},
		watch: {
			'view': function (val, old) {
				history.replaceState({}, null, this.adminUrl() + "admin.php?page=wdf-scan&view=" + this.view);
			}
		},
		mounted: function () {
			if (this.$root.store.state_changed) {
				this.$nextTick(() => {
					this.rebindSUI()
				})
			}
			self = this;
			jQuery('.sui-mobile-nav').change(function () {
				self.view = jQuery(this).val()
			})
		},
		computed: {
			count_total: function () {
				return this.$root.store.scan.count.total;
			},
			count_core: function () {
				return this.$root.store.scan.count.core;
			},
			count_vuln: function () {
				return this.$root.store.scan.count.vuln;
			},
			count_content: function () {
				return this.$root.store.scan.count.content;
			},
			last_scan_date: function () {
				return this.$root.store.scan.last_scan;
			},
			tooltips: function () {
				let tooltip = this.__("You don't have any outstanding security issues, nice work!");
				if (this.count_total === 1) {
					tooltip = this.__("We've detected a potential security risk in your file system. We recommend you take a look and action a fix, or ignore the file if it's harmless.");
				} else if (this.count_total > 1) {
					tooltip = this.vsprintf(this.__("You have %s potential security risks in your file system. We recommend you take a look and action fixes, or ignore the issues if they are harmless."), this.count_total);
				}
				return tooltip;
			}
		},
	}
</script>