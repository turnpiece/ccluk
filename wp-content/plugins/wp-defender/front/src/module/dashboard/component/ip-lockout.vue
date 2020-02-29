<template>
	<div id="ip-lockout" class="sui-box">
		<div class="sui-box-header">
			<h3 class="sui-box-title">
				<i class="sui-icon-lock" aria-hidden="true"></i>
				{{__("IP Lockouts")}}
			</h3>
		</div>
		<div class="sui-box-body" :class="{'no-padding-bottom':enabled===true}">
			<p>
				{{__("Protect to your login area and have Defender automatically lockout any suspicious behaviour.")}}
			</p>
			<form method="post" @submit.prevent="updateSettings" v-if="enabled===false">
				<submit-button type="submit" css-class="sui-button-blue activate" :state="state">
					{{__("Activate")}}
				</submit-button>
			</form>
			<div v-else class="sui-field-list sui-flushed no-border">
				<div class="sui-field-list-body">
					<div class="sui-field-list-item">
						<label class="sui-field-list-item-label">
							<strong>{{__("Last lockout")}}</strong>
						</label>
						<span v-text="summary.lastLockout"></span>
					</div>
					<div class="sui-field-list-item">
						<label class="sui-field-list-item-label">
							<strong>{{__("Login lockouts this week")}}</strong>
						</label>
						<span v-text="summary.ip.week"></span>
					</div>
					<div class="sui-field-list-item">
						<label class="sui-field-list-item-label">
							<strong>{{__("404 lockouts this week")}}</strong>
						</label>
						<span v-text="summary.nf.week"></span>
					</div>
				</div>
			</div>
		</div>
		<div class="sui-box-footer" v-if="enabled===true">
			<div class="sui-actions-left">
				<a :href="adminUrl('admin.php?page=wdf-ip-lockout&amp;view=logs')"
				   class="sui-button sui-button-ghost">
					<i class="sui-icon-eye" aria-hidden="true"></i>
					{{__("View logs")}}
				</a>
			</div>
			<div class="sui-actions-right">
				<p class="sui-p-small" v-text="notificationText">
				</p>
			</div>
		</div>
	</div>
</template>

<script>
	import base_helper from '../../../helper/base_hepler'

	export default {
		mixins: [base_helper],
		name: "ip-lockout",
		data: function () {
			return {
				state: {
					on_saving: false,
				},
				nonces: dashboard.ip_lockout.nonces,
				endpoints: dashboard.ip_lockout.endpoints,
				summary: dashboard.ip_lockout.summary,
				notification: dashboard.ip_lockout.notification,
				enabled: dashboard.ip_lockout.enabled
			}
		},
		methods: {
			updateSettings: function () {
				let self = this;
				this.httpPostRequest('updateSettings', {
					data: JSON.stringify({
						login_protection: true,
						detect_404: true
					})
				}, function () {
					self.enabled = true;
				})
			}
		},
		computed: {
			notificationText: function () {
				if (this.notification) {
					return this.__("Lockout notifications are enabled")
				} else {
					return this.__("Lockout notifications are disabled")
				}
			}
		}
	}
</script>