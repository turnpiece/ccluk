<template>
	<div id="mask-login" class="sui-box" v-if="model.enabled===false">
		<div class="sui-box-header">
			<h3 class="sui-box-title">
				{{__("Mask Login Area")}}
			</h3>
		</div>
		<div class="sui-message">
			<img v-if="!maybeHideBranding()" :src="assetUrl('assets/img/2factor-disabled.svg')" class="sui-image"
			     aria-hidden="true">
			<div class="sui-message-content">
				<p>
					{{__("Change the location of WordPress's default login area, making it harder for automated bots to find and also more convenient for your users.")}}
				</p>
				<form method="post">
					<submit-button type="button" @click="toggle(true)" css-class="sui-button-blue activate"
					               :state="state">
						{{__("Activate")}}
					</submit-button>
				</form>
			</div>
		</div>
	</div>
	<div class="sui-box" v-else>
		<div class="sui-box-header">
			<h3 class="sui-box-title">
				{{__("Mask Login Area")}}
			</h3>
		</div>
		<form method="post" @submit.prevent="updateSettings">
			<div class="sui-box-body">
				<p>
					{{__("Change your default WordPress login URL to hide your login area from hackers and bots.")}}
				</p>
				<div class="sui-notice sui-notice-error" v-if="misc.compatibility!==false">
					<p>
                        <span v-for="issue in misc.compatibility">
                            {{issue}}
                        </span>
					</p>
				</div>
				<div class="sui-notice sui-notice-warning" v-if="state.original_state === false">
					<p>
						{{__("Masking is currently inactive. Choose your URL and save your settings to finish setup.")}}
					</p>
				</div>
				<div class="sui-notice sui-notice-info" v-else>
					<p>
						{{__("Masking is currently active at ")}} <strong v-text="misc.new_login_url"></strong>
					</p>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        {{__("Masking URL")}}
                    </span>
						<span class="sui-description">
                            {{__("Choose the new URL slug where users of your website will now navigate to log in or register.")}}
                        </span>
					</div>

					<div class="sui-box-settings-col-2">
                    <span class="sui-description">
                        {{__("You can specify any URLs. For security reasons, less obvious URLs are recommended as they are harder for bots to guess.")}}
                    </span>
						<div class="sui-form-field">
							<label class="sui-label">
								{{__("New Login URL")}}
							</label>
							<input type="text" class="sui-form-control" name="mask_url" v-model="model.mask_url"
							       placeholder="E.g. dashboard"/>
							<span class="sui-description">
                                {{__("Users will login at")}} <a :href='new_mask_login'>{{new_mask_login}}</a>. {{__("Note: Registration and Password Reset emails have hardcoded URLs in them. We will update them automatically to match your new login URL")}}
                            </span>
						</div>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("Redirect traffic")}}
                        </span>
						<span class="sui-description">
                            {{__("With this feature you can send visitors and bots who try to visit the default Wordpress login URLs to a separate URL to avoid 404s.")}}
                        </span>
					</div>

					<div class="sui-box-settings-col-2">
						<label class="sui-toggle">
							<input role="presentation" type="checkbox" name="redirect_traffic" class="toggle-checkbox"
							       id="redirect_traffic" v-model="model.redirect_traffic" :true-value="true"
							       :false-value="false"/>
							<span class="sui-toggle-slider"></span>
						</label>
						<label for="redirect_traffic" class="sui-toggle-label">
							{{__("Enable 404 redirection")}}
						</label>
						<div id="redirectTrafficContainer" class="sui-border-frame sui-toggle-content"
						     v-show="model.redirect_traffic===true">
							<label class="sui-label">{{__("Redirection URL")}}</label>
							<input placeholder="E.g. 404-error" type="text" class="sui-form-control"
							       name="redirect_traffic_url"
							       v-model="model.redirect_traffic_url"/>
							<span class="sui-description">
                                {{__("Visitors who visit the default login URLs will be redirected to")}} <a
									:href="login_redirect_url">{{login_redirect_url}}</a>
                            </span>
						</div>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                            {{__("Deactivate")}}
                        </span>
						<span class="sui-description">
                        {{__("Disable login area masking and return to the default wp-admin and wp-login URLS.")}}
                        </span>
					</div>
					<div class="sui-box-settings-col-2">
						<submit-button type="button" css-class="sui-button-ghost" :state="state" @click="toggle(false)">
							{{__('Deactivate')}}
						</submit-button>
					</div>
				</div>
			</div>
			<div class="sui-box-footer">
				<submit-button type="submit" :state="state" css-class="sui-button-blue save-changes">
					<i class="sui-icon-save" aria-hidden="true"></i>
					{{__("Save Changes")}}
				</submit-button>
			</div>
		</form>
	</div>

</template>

<script>
	import base_helper from '../../../helper/base_hepler'

	export default {
		mixins: [base_helper],
		name: "mask-login",
		data: function () {
			return {
				misc: advanced_tools.misc,
				model: advanced_tools.model.mask_login,
				nonces: advanced_tools.nonces,
				endpoints: advanced_tools.endpoints,
				state: {
					on_saving: false,
					original_state: false
				}
			}
		},
		watch: {
			'model.mask_url': function (value) {
				value = this.convertToSlug(value);
				this.model.mask_url = value;
				this.misc.new_login_url = this.misc.home_url + value;
				this.state.waiting_save = true;
			},
			'model.redirect_traffic_url': function (value) {
				value = this.convertToSlug(value);
				this.model.redirect_traffic_url = value;
				this.misc.login_redirect_url = this.misc.home_url + value;
			},
		},
		mounted: function () {
			this.state.original_state = this.model.mask_url.length > 0
		},
		methods: {
			toggle: function (value) {
				let that = this;
				let envelope = {};
				envelope['enabled'] = value;
				this.httpPostRequest('updateSettings', {
					data: JSON.stringify({
						settings: envelope,
						module: 'mask-login'
					})
				}, function () {
					that.model['enabled'] = value;
				})
			},
			updateSettings: function () {
				let data = this.model;
				let that = this;
				//unset email subject as we dont use it on this function
				this.httpPostRequest('updateSettings', {
					data: JSON.stringify({
						settings: data,
						module: 'mask-login'
					})
				}, function () {
					that.state.original_state = that.model.mask_url.length > 0
				});
			},
			convertToSlug: function (text) {
				return text
					.toLowerCase()
					//.replace(/ /g, '-')
					.replace(/[^\w-/.]+/g, '')
					;
			}
		},
		computed: {
			new_mask_login: function () {
				return this.misc.new_login_url;
			},
			login_redirect_url: function () {
				return this.misc.login_redirect_url;
			}
		}
	}
</script>
