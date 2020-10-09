<template>
	<div class="sui-toggle-content">
		<div class="sui-border-frame">
			<label for="hsts_preload" class="sui-checkbox">
				<input type="checkbox" name="model.hsts_preload"
					true-value="1" false-value="0" v-model="model.hsts_preload"
					aria-labelledby="label_hsts_preload"
					id="hsts_preload"/>
				<span aria-hidden="true"></span>
				<span id="label_hsts_preload">{{__("HSTS Preload")}}</span>
			</label>
			<span class="sui-description margin-bottom-10">{{__("Google maintains an HSTS preload service. By following the guidelines and successfully submitting your domain, browsers will never connect to your domain using an insecure connection.")}}</span>
			<div v-show="show_hsts_warning" class="sui-notice sui-notice-warning">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<i aria-hidden="true" class="sui-notice-icon sui-icon-info sui-md"></i>
						<p v-html="hsts_warning_text"></p>
					</div>
				</div>
			</div>

			<div v-if="allow_subdomain === true" class="margin-bottom-30">
				<label for="include_subdomain" class="sui-checkbox">
					<input type="checkbox" v-model="model.include_subdomain" true-value="1" false-value="0"
						aria-labelledby="label_include_subdomain"
						id="include_subdomain"/>
					<span aria-hidden="true"></span>
					<span id="label_include_subdomain">{{__("Include Subdomains")}}</span>
				</label>
				<span class="sui-description margin-bottom-10">{{__("If this optional parameter is specified, this rule applies to all of the site's subdomains as well.")}}</span>
			</div>
			<div class="toggle-content-header" :style="{'fontWeight': 500}">{{__("Browser Caching")}}</div>
			<span class="sui-description" v-html="text_browser_caching"></span>
			<div class="sui-form-field">
				<div class="sui-row">
					<div class="sui-col-md-5">
						<label for="hsts-cache-duration" id="label-hsts-cache-duration" class="sui-label">{{__("HSTS Maximum Age")}}</label>
						<select id="hsts-cache-duration" class="sui-select-sm"
								name="hsts_cache_duration" data-module="sh-strict-transport"
								aria-labelledby="label-hsts-cache-duration"
								v-model="model.hsts_cache_duration"
								data-key="hsts_cache_duration">
							<option value="1 hour">{{__("1 hour")}}</option>
							<option value="24 hours">{{__("24 hours")}}</option>
							<option value="7 days">{{__("7 days")}}</option>
							<option value="30 days">{{__("30 days")}}</option>
							<option value="3 months">{{__("3 months")}}</option>
							<option value="6 months">{{__("6 months")}}</option>
							<option value="1 year">{{__("1 year")}}</option>
							<option value="2 years">{{__("2 years")}}</option>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
	import helper from '../../../helper/base_hepler';

	export default {
		mixins: [helper],
		name: 'sh-strict-transport',
		props: ['misc', 'model'],
		data: function () {
			return {
				state: {
					on_saving: false
				},
				hsts_preload: this.misc.misc.hsts_preload,
				allow_subdomain: this.misc.misc.allow_subdomain,
				include_subdomain: this.misc.misc.include_subdomain,
				hsts_cache_duration: this.misc.misc.hsts_cache_duration,
			}
		},
		created: function () {
			if (this.allow_subdomain === false) {
				this.include_subdomain = false;
			}
		},
		mounted: function () {
			let self = this;
			jQuery('#hsts-cache-duration').change(function () {
				let value = jQuery(this).val();
				self.hsts_cache_duration = value;
				self.$parent.$emit('hsts_maximum_age', value);
			})
		},
		computed: {
			show_hsts_warning: function () {
				return parseInt(this.model.hsts_preload) === 1;
			},
			hsts_warning_text: function () {
				return vsprintf(this.__('Note: Do not include the preload directive by default if you maintain a project that provides HTTPS' +
						' configuration advice or provides an option to enable HSTS. Be aware that inclusion in the preload list cannot' +
						' easily be undone. Domains can be removed, but it takes months for a change.' +
						' Check <a target="_blank" href="%s">here</a> for more information.'), 'https://hstspreload.org/');
			},
			text_browser_caching: function () {
				return vsprintf(this.__('Choose when the browser should cache and apply the Strict Transport Security policy for.' +
						' The recommended value for HSTS Maximum age is at least 30 days.' +
						' You can learn more about max-age value differences <a target="_blank" href="%s">here</a>.'),
						'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security#Examples');
			}
		},
		watch: {
			'misc.hsts_cache_duration': function () {
				this.hsts_cache_duration = this.misc.hsts_cache_duration;
			}
		}
	}
</script>