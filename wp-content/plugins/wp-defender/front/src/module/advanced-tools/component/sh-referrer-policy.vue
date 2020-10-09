<template>
	<div class="sui-toggle-content">
		<span class="sui-description toogle-content-description">
			{{ __( "Choose which referrer information to send along with requests." ) }}
		</span>
		<div class="sui-border-frame">
			<div class="sui-row">
				<div class="sui-col-md-7">
					<label for="referrer-policy" id="label-referrer-policy" class="sui-label">{{__("Referrer Information")}}</label>
					<select class="sui-select-sm" id="referrer-policy"
							name="sh_referrer_policy_mode"
							aria-labelledby="label-referrer-policy"
							v-model="model.sh_referrer_policy_mode">
						<option value="no-referrer">no-referrer</option>
						<option value="no-referrer-when-downgrade">no-referrer-when-downgrade</option>
						<option value="origin">origin</option>
						<option value="origin-when-cross-origin">origin-when-cross-origin</option>
						<option value="same-origin">same-origin</option>
						<option value="strict-origin">strict-origin</option>
						<option value="strict-origin-when-cross-origin">strict-origin-when-cross-origin</option>
						<option value="unsafe-url">unsafe-url</option>
					</select>
				</div>
				<div class="sui-col-md-12" :style="{'marginTop': '10px'}">
					<p class="sui-description" v-html="policyDesc"></p>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
	import helper from '../../../helper/base_hepler';

	export default {
		mixins: [helper],
		name: 'sh-referrer-policy',
		props: ['misc', 'model'],
		data: function () {
			return {
				state: {
					on_saving: false
				},
				mode: null,
				policyDesc: '',
			}
		},
		created: function () {
			this.mode = this.misc.misc.mode;
		},
		mounted: function () {
			let self = this;
			jQuery('#referrer-policy').change(function () {
				self.mode = jQuery(this).val();
			})
		},
		watch: {
			mode: function () {
				if (this.mode === 'no-referrer') {
					this.policyDesc = this.__("The Referer header will be omitted entirely. No referrer information is sent along with requests.")
				}
				if (this.mode === 'no-referrer-when-downgrade') {
					this.policyDesc = this.__("This is the user agent's default behavior if no policy is specified. The origin is sent as referrer to a-priori as-much-secure destination (HTTPS->HTTPS), but isn't sent to a less secure destination (HTTPS->HTTP).")
				}
				if (this.mode === 'origin') {
					this.policyDesc = this.__("Only send the origin of the document as the referrer in all cases. The document https://example.com/page.html will send the referrer https://example.com/.")
				}
				if (this.mode === 'origin-when-cross-origin') {
					this.policyDesc = this.__("Send a full URL when performing a same-origin request, but only send the origin of the document for other cases.")
				}
				if (this.mode === 'same-origin') {
					this.policyDesc = this.__("A referrer will be sent for same-site origins, but cross-origin requests will contain no referrer information.")
				}
				if (this.mode === 'strict-origin') {
					this.policyDesc = this.__("Only send the origin of the document as the referrer to a-priori as-much-secure destination (HTTPS->HTTPS), but don't send it to a less secure destination (HTTPS->HTTP).")
				}
				if (this.mode === 'strict-origin-when-cross-origin') {
					this.policyDesc = this.__("Send a full URL when performing a same-origin request, only send the origin of the document to a-priori as-much-secure destination (HTTPS->HTTPS), and send no header to a less secure destination (HTTPS->HTTP).")
				}
				if (this.mode === 'unsafe-url') {
					this.policyDesc = this.__("Send a full URL (stripped from parameters) when performing a a same-origin or cross-origin request.")
				}
				this.$parent.$emit('mode_referrer_policy', this.mode);
			}
		}
	}
</script>