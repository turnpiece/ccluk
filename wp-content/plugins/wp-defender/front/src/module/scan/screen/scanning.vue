<template>
	<div class="sui-wrap" :class="maybeHighContrast()">
		<div class="file-scanning">
			<div class="sui-header">
				<h1 class="sui-header-title">
					{{__("File Scanning")}}
				</h1>
				<div class="sui-actions-left">
					<button type="button" class="sui-button sui-button-blue">
						{{__("New Scan")}}
					</button>
				</div>
				<div class="sui-actions-right">
					<div class="sui-actions-right">
						<a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/" target="_blank"
						   class="sui-button sui-button-ghost">
							<i class="sui-icon-academy"></i> {{__("View Documentation")}}
						</a>
					</div>
				</div>
			</div>
			<div class="sui-dialog" aria-hidden="true" tabindex="-1" id="scanning-dialog">
				<div class="sui-dialog-overlay"></div>

				<div class="sui-dialog-content" aria-labelledby="scanning-dialog" aria-describedby="scanning"
				     role="dialog">
					<div class="sui-box" role="document">
						<div class="sui-box-header">
							<h3 class="sui-box-title">
								{{__("Scan in progress")}}
							</h3>
						</div>
						<div class="sui-box-body" :class="body_css">
							<p>
								{{__("Defender is currently scanning your files for malicious code, please be patient this should on take a few minutes depending on the size of your website.")}}
							</p>
							<div class="sui-progress-block">
								<div class="sui-progress">
                                <span class="sui-progress-icon" aria-hidden="true">
                                    <i class="sui-icon-loader sui-loading"></i>
                                </span>
									<span class="sui-progress-text">
                                    <span v-text="percent+'%'"></span>
                                </span>
									<div class="sui-progress-bar" aria-hidden="true">
										<span :style="{'width':percent+'%'}"></span>
									</div>
								</div>
								<button @click="cancelScan" type="button" :disabled="state.canceling"
								        class="sui-button-icon sui-tooltip" data-tooltip="Cancel">
									<i class="sui-icon-close" aria-hidden="true"></i>
								</button>
							</div>
							<div class="sui-progress-state">
								<span v-text="statusText"></span>
							</div>
							<div v-if="is_free===1" class="sui-box-settings-row sui-upsell-row">
								<img class="sui-image sui-upsell-image"
								     :src="assetUrl('assets/img/scanning-upsell.svg')"
								>
								<div class="sui-upsell-notice">
									<p>{{__("Did you know the Pro version of Defender comes with advanced full code scanning and automated reporting? Get enhanced security protection as part of a WPMU DEV membership with 24/7 support and lots of handy site management tools.")}}
										<br/><a target='_blank'
										        :href="campaign_url('defender_filescanning_modal_inprogress_upsell_link')"
										        class="sui-button-purple sui-button premium-button">{{__("Try Pro Free Today")}}</a>
									</p>
								</div>
							</div>
							<div v-else-if="maybeHideBranding() === false" class="scanning-man">

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import helper from "../helper/scan-helper";
	import store from '../store/store';

	export default {
		mixins: [helper],
		name: "scanning",
		data: function () {
			return {
				state: {
					on_saving: false,
					canceling: false
				},
				endpoints: scanData.endpoints,
				nonces: scanData.nonces,
				polling_state: null,
				is_free: parseInt(defender.is_free)
			}
		},
		mounted: function () {
			if (this.$root.store.state_changed) {
				this.$nextTick(() => {
					var mainEl = jQuery('.sui-wrap');
					var el = document.getElementById('scanning-dialog');
					SUI.dialogs['scanning-dialog'] = new A11yDialog(el, mainEl);
					SUI.dialogs['scanning-dialog'].show();
					this.polling();
				})
			} else {
				document.onreadystatechange = () => {
					if (document.readyState === "complete") {
						if (SUI.dialogs['scanning-dialog'] !== undefined) {
							//this is refresh case
							SUI.dialogs['scanning-dialog'].show();
							this.polling();
						}
					}
				}
			}
		},
		computed: {
			statusText: function () {
				return store.state.scan.status_text;
			},
			percent: function () {
				return store.state.scan.percent;
			},
			body_css: function () {
				if (this.is_free === 1) {
					return 'scanning-free'
				}
				if (this.maybeHideBranding() === true) {
					return 'scanning-blank'
				}
			}
		},
		methods: {
			refreshStatus: function () {
				let self = this;
				this.processScan(function (response) {
					if (response.success !== true) {
						store.updateScan(response.data);
						self.polling();
					} else {
						SUI.dialogs['scanning-dialog'].hide();
						self.$nextTick(() => {
							store.updateScan(response.data.scan);
						})
					}
				})
			},
			polling: function () {
				if (this.state.canceling === false) {
					this.polling_state = setTimeout(this.refreshStatus(), 2000)
				}
			},
			cancelScan: function () {
				if (this.state.canceling === true) {
					//a request in process
					return;
				}
				//abort all ajax request, as we can have the process can ongoing
				this.abortAllRequests();
				let self = this;
				clearTimeout(this.polling_state);
				this.state.canceling = true;
				this.httpPostRequest('cancelScan', {}, function (response) {
					SUI.dialogs['scanning-dialog'].hide();
					self.$nextTick(() => {
						store.updateScan(response.data.scan);
					})
				})
			}
		}
	}
</script>