<template>
	<div class="sui-accordion-item" :class="cssClass">
		<div class="sui-accordion-item-header">
			<div class="sui-accordion-item-title">
				<i aria-hidden="true" :class="titleIcon"></i>
				{{title}}
				<div class="sui-actions-right">
					<button v-if="status!=='ignore'" class="sui-button-icon sui-accordion-open-indicator"
					        aria-label="Open item">
						<i class="sui-icon-chevron-down" aria-hidden="true"></i>
					</button>
					<submit-button v-else type="button" :state="state"
					               css-class="sui-button-ghost float-r" @click="restore">
                        <span class="sui-loading-text">
                        <i class="sui-icon-undo" aria-hidden="true"></i>{{__("Restore")}}
                        </span>
						<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
					</submit-button>
				</div>
			</div>
		</div>
		<div class="sui-accordion-item-body" v-if="status!=='ignore'">
			<div class="sui-box">
				<div class="sui-box-body">
					<strong>{{__("Overview")}}</strong>
					<p>
						{{__("WordPress uses security keys to improve the encryption of informtion stores in user cookies making it harder to crack passwords. A non-encrypted password like “username” or “wordpress” can be easily broken, but a random, unpredictable, encrypted password such as “88a7da62429ba6ad3cb3c76a09641fc” takes years to come up with the right combination.")}}
					</p>
					<div v-if="status==='fixed'">
						<strong>
							{{ __( "Status" ) }}
						</strong>
						<div class="sui-notice sui-notice-success margin-bottom-30">
							<p v-html="successReason"></p>
						</div>
					</div>
					<div v-else>
						<strong>
							{{ __( "Status" ) }}
						</strong>
						<div class="sui-notice sui-notice-warning">
							<p v-html="errorReason"></p>
						</div>
						<p>
							{{ __( "Currently you have old security keys, it pays to keep them updated - we recommend every 60 days or less." ) }}
						</p>
						<strong>
							{{ __( "How to fix" ) }}
						</strong>
						<p>
							{{ __( "We can regenerate your key salts instantly for you and they will be good for another 60 days. Note that this will log all users out of your site. You can also choose how often we should notify you to change them." ) }}
						</p>
					</div>
					<form method="post" v-on:submit.prevent="updateReminder"
					      id="reminder-date">
						<div class="sui-form-field">
							<label class="sui-label">{{__("Reminder frequency")}}</label>
							<div class="sui-row">
								<div class="sui-col-md-3">
									<select id="reminder-selector" v-model="reminder" class="sui-select-sm">
										<option value="30 days">{{__("30 days")}}</option>
										<option value="60 days">{{__("60 days")}}</option>
										<option value="90 days">{{__("90 days")}}</option>
										<option value="6 months">{{__("6 months")}}</option>
										<option value="1 year">{{__("1 year")}}</option>
									</select>
								</div>
								<div class="sui-col">
									<submit-button :state="state" css-class="sui-button-ghost" type="submit">
										<span class="sui-loading-text">{{__("Update")}}</span>
										<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
									</submit-button>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div v-if="status==='issues'" class="sui-box-footer">
					<div class="sui-actions-left">
						<form method="post" v-on:submit.prevent="ignore">
							<submit-button :state="state" type="submit" css-class="sui-button-ghost">
								<span class="sui-loading-text"><i class="sui-icon-eye-hide" aria-hidden="true"></i> {{ __( "Ignore")}}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
					<div class="sui-actions-right">
						<form v-on:submit.prevent="process" method="post">
							<submit-button :state="state"
							               css-class="sui-button-blue" type="submit">
								<span class="sui-loading-text">{{__( "Regenerate Keys" ) }}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
	import helper from '../../../helper/base_hepler';
	import securityTweakHelper from '../helper/security-tweak-helper';


	export default {
		mixins: [helper, securityTweakHelper],
		props: ['status', 'title', 'slug', 'errorReason', 'successReason', 'misc'],
		data: function () {
			return {
				state: {
					on_saving: false
				},
				reminder: null
			}
		},
		created: function () {
			this.reminder = this.misc.reminder;
		},
		methods: {
			process: function () {
				let data = {
					slug: this.slug
				}
				this.state.on_saving = true;
				let self = this;
				this.resolve(data, function (response) {
					if (response.success === false) {
						self.state.on_saving = false;
						Defender.showNotification('error', response.data.message);
					} else {
						Defender.showNotification('success', response.data.message);
						setTimeout(function () {
							location.href = response.data.url
						}, 3000)
					}
				});
			},
			updateReminder: function () {
				this.state.on_saving = true;
				let url = ajaxurl + '?action=' + security_tweaks.endpoints['updateSecurityReminder'];
				let self = this;
				jQuery.ajax({
					url: url,
					type: 'POST',
					data: {
						remind_date: this.reminder
					},
					success: function (response) {

						self.state.on_saving = false;
					}
				})
			}
		},
		mounted: function () {
			var self = this;
			this.$nextTick(function () {
				jQuery('body').on('change', '#reminder-selector', function () {
					self.reminder = jQuery(this).val();
				})
			})
		}
	}
</script>