<template>
	<div class="sui-wrap" :class="maybeHighContrast()">
		<div class="security-tweaks">
			<div class="sui-header">
				<h1 class="sui-header-title">
					{{__("Security Tweaks")}}
				</h1>
				<doc-link link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#security-tweaks"></doc-link>
			</div>
			<summary-box css-class="sui-summary-sm">
				<div class="sui-summary-segment">
					<div class="sui-summary-details issues">

						<span class="sui-summary-large count-issues">{{summary.issues_count}}</span>
						<span v-if="summary.issues_count > 0"
						      class="sui-tooltip sui-tooltip-top-left sui-tooltip-constrained"
						      :data-tooltip="tooltipText">
                            <i aria-hidden="true" class="sui-icon-info sui-warning"></i>
                            </span>
						<span v-else class="sui-tooltip sui-tooltip-top-left sui-tooltip-constrained"
						      :data-tooltip="tooltipText">
                            <i class="sui-icon-check-tick sui-success" aria-hidden="true"></i>
                            </span>
						<span class="sui-summary-sub">{{__("Security issues")}}</span>
					</div>
					<span aria-hidden="true" class="sui-hidden test-tooltip-content" v-html="tooltipText">

					</span>
				</div>

				<div class="sui-summary-segment">
					<ul class="sui-list">

						<li>
							<span class="sui-list-label">{{__("Current PHP version")}}</span>
							<span class="sui-list-detail issues_wp">
                                    {{summary.php_version}}
                                </span>
						</li>

						<li>
							<span class="sui-list-label">{{__("Current WordPress version")}}</span>
							<span class="sui-list-detail vuln_issues">
                                    {{summary.wp_version}}
                                </span>
						</li>

					</ul>
				</div>
			</summary-box>
			<div class="sui-row-with-sidenav">
				<div class="sui-sidenav">
					<ul class="sui-vertical-tabs sui-sidenav-hide-md">
						<li class="sui-vertical-tab" :class="{current:view==='issues'}">
							<a @click.prevent="view='issues'"
							   :href="adminUrl('admin.php?page=wdf-hardener&view=issues')"
							   data-tab="tweaks_issue">
								{{__("Issues")}}</a>
							<span v-show="summary.issues_count>0"
							      class="sui-tag sui-tag-warning count-issues">{{summary.issues_count}}</span>
						</li>
						<li class="sui-vertical-tab" :class="{current:view==='resolved'}">
							<a @click.prevent="view='resolved'"
							   :href="adminUrl('admin.php?page=wdf-hardener&view=resolved')"
							   data-tab="tweaks_fixed">
								{{__("Resolved")}}</a>
							<span v-show="summary.fixed_count > 0"
							      class="sui-tag count-resolved">{{summary.fixed_count}}</span>
						</li>
						<li class="sui-vertical-tab" :class="{current:view==='ignored'}">
							<a @click.prevent="view='ignored'"
							   :href="adminUrl('admin.php?page=wdf-hardener&view=ignored')"
							   data-tab="tweaks_ignored">
								{{__("Ignored")}}</a>
							<span v-show="summary.ignore_count>0"
							      class="sui-tag count-ignored">{{summary.ignore_count}}</span>
						</li>
						<li class="sui-vertical-tab" :class="{current:view==='notification'}">
							<a @click.prevent="view='notification'" data-tab="tweaks_notification"
							   :href="adminUrl('admin.php?page=wdf-hardener&view=notification')">
								{{__("Notifications")}}
							</a>
						</li>
					</ul>
					<div class="sui-sidenav-hide-lg">
						<select class="sui-mobile-nav" style="display: none;">
							<option value="issues">{{__("Issues")}}</option>
							<option value="resolved">{{__("Resolved")}}</option>
							<option value="ignored">{{__("Ignored")}}</option>
							<option value="notification">{{__("Notifications")}}</option>
						</select>
					</div>
				</div>
				<issues :summary="summary" :issues="issueRules" :view="view"></issues>
				<resolved :summary="summary" :fixed="fixedRules" :view="view"></resolved>
				<ignored :summary="summary" :ignored="ignoredRules" :view="view"></ignored>
				<notification :view="view"></notification>
			</div>
		</div>
		<app-footer></app-footer>
	</div>
</template>

<script>
	import base_helper from '../../helper/base_hepler';
	import footer from '../../component/footer';
	import issues from './screen/issues';
	import resolved from './screen/resolve';
	import ignored from './screen/ignore';
	import notification from './screen/notification';

	export default {
		mixins: [base_helper],
		name: "security_tweaks",
		data: function () {
			return {
				whitelabel: defender.whitelabel,
				is_free: defender.is_free,
				summary: this.$root.store.summary,
				issues: this.$root.store.issuesRules,
				fixed: this.$root.store.fixedRules,
				ignored: this.$root.store.ignoreRules,
				accessibility: {
					high_contrast: defender.high_contrast,
				},
				view: '',
			}
		},
		components: {
			'issues': issues,
			'resolved': resolved,
			'ignored': ignored,
			'notification': notification,
			'app-footer': footer
		},
		computed: {
			tooltipText: function () {
				let tooltips = this.__("You don't have any outstanding security issues, nice work!");
				if (this.summary.issues_count === 1) {
					tooltips = this.__("You have one security tweak left to do. We recommend you action it, or ignore it if it's irrelevant.");
				} else if (this.summary.issues_count > 1) {
					tooltips = vsprintf(this.__("You have %s security tweaks left to do. We recommend you take a look and action fixes, or ignore the issues if they are harmless."), [this.summary.issues_count]);
				}
				return tooltips;
			},
			issueRules: function () {
				return this.$root.store.issuesRules
			},
			ignoredRules: function () {
				return this.$root.store.ignoreRules
			},
			fixedRules: function () {
				return this.$root.store.fixedRules
			},
		},
		created: function () {
			//show the current page
			let urlParams = new URLSearchParams(window.location.search);
			let view = urlParams.get('view');
			if (view === null) {
				view = 'issues';
			}
			this.view = view;
		},
		mounted: function () {
			self = this;
			jQuery('.sui-mobile-nav').change(function () {
				self.view = jQuery(this).val()
			})
		},
		watch: {
			'view': function (val, old) {
				history.replaceState({}, null, this.adminUrl("admin.php?page=wdf-hardener&view=" + this.view));
			}
		},
	}
</script>
