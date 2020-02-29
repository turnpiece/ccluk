<template>
	<div :id="slug" class="sui-accordion-item" :class="cssClass">
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
                            css-class="sui-button-ghost float-r restore" @click="restore">
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
						{{__("When you first install WordPress on a new database, the default settings start with wp_ as the prefix to anything that gets stored in the tables. This makes it easier for hackers to perform SQL injection attacks if they find a code vulnerability.")}}
					</p>
					<div v-if="status==='fixed'">
						<strong>
							{{ __( "Status" ) }}
						</strong>
						<div class="sui-notice sui-notice-success">
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
							{{ __( "You're currently using the default prefix, it's much safer to change this to something random." ) }}
						</p>
						<strong>
							{{ __( "How to fix" ) }}
						</strong>
						<p>
							{{ __( "It's good practice to come up with a unique prefix to protect yourself from this. We've automatically generated a random prefix for you which will make it near impossible for hackers to guess, but feel free to choose your own. Alternately, you can ignore this tweak if you really want to keep the wp_ prefix at your own risk." ) }}
						</p>
						<div class="sui-border-frame">
							<div class="sui-form-field ">
								<label class="sui-label">{{( "New database prefix" )}}</label>
								<input type="text" v-model="prefix" name="dbprefix" id="dbprefix"
								       class="sui-form-control"/>
							</div>
						</div>
					</div>
				</div>
				<div v-if="status==='issues'" class="sui-box-footer">
					<div class="sui-actions-left">
						<form v-on:submit.prevent="ignore" method="post"
						      class="hardener-frm ignore-frm rule-process">
							<input type="hidden" name="action" value="ignoreHardener"/>
							<submit-button type="submit" :state="state" css-class="sui-button-ghost ignore">
								<span class="sui-loading-text"><i class="sui-icon-eye-hide" aria-hidden="true"></i> {{ __( "Ignore")}}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
					<div class="sui-actions-right">
						<form v-on:submit.prevent="process" method="post"
						      class="hardener-frm rule-process hardener-frm-process-xml-rpc">
							<submit-button :state="state" css-class="sui-button-blue apply" type="submit">
								<span class="sui-loading-text">{{__( "Update Prefix" ) }}</span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</submit-button>
						</form>
					</div>
				</div>
				<div class="sui-center-box">
					<p>
						{{__("Ensure you backup your database before performing this tweak.")}}
					</p>
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
				prefix: '',
				state: {
					on_saving: false
				},
			}
		},
		created: function () {
			this.prefix = this.misc.prefix;
		},
		methods: {
			process: function () {
				let data = {
					slug: this.slug,
					dbprefix: this.prefix
				}
				this.state.on_saving = true;
				let self = this;
				this.resolve(data, function (response) {
					//some case the error output because set_transient, reload
					if (response.success === false) {
						self.state.on_saving = false;
						Defender.showNotification('error', response.data.message);
					} else {
						location.reload();
						Defender.showNotification('success', response.data.message);
					}
				});
			}
		},
	}
</script>