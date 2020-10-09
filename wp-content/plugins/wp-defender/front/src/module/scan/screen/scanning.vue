<template>
	<div class="sui-wrap" :class="maybeHighContrast()">
		<div class="file-scanning">
			<div class="sui-header">
				<h1 class="sui-header-title">
					{{__("Malware Scanning")}}
				</h1>
				<div class="sui-actions-left">
					<button type="button" class="sui-button sui-button-blue">
						{{__("New Scan")}}
					</button>
				</div>
				<doc-link link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#malware-scanning"></doc-link>
			</div>
			<div class="sui-modal sui-modal-lg">

				<div
						role="dialog"
						id="scanning-dialog"
						class="sui-modal-content"
						aria-modal="true"
						aria-labelledby="Scanning dialog"
				>
					<div class="sui-box" role="document">
						<div class="sui-box-header">
							<h3 class="sui-box-title">
								{{__("Scan in progress")}}
							</h3>
						</div>
						<div class="sui-box-body" :class="body_css">
							<p>
								{{__("Defender is currently scanning your files for malicious code. Please be patient, this should only take a few minutes depending on the size of your website.")}}
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
			let self = this;
			this.$nextTick(() => {
				self.showDialog()
				self.polling();
			})
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
						SUI.closeModal()
						self.$nextTick(() => {
							store.updateScan(response.data.scan);
						})
					}
				})
			},
			polling: function () {
				if (this.state.canceling === false) {
					this.polling_state = setTimeout(this.refreshStatus, 2000)
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
					SUI.closeModal()
					self.$nextTick(() => {
						store.updateScan(response.data.scan);
					})
				})
			},
			showDialog: function () {
				const modalId = 'scanning-dialog',
						focusAfterClosed = jQuery('body'),
						focusWhenOpen = undefined,
						hasOverlayMask = false
				;
				if (typeof SUI === 'undefined') {
					document.onreadystatechange = () => {
						if (document.readyState === "complete") {
							SUI.openModal(
									modalId,
									focusAfterClosed,
									focusWhenOpen,
									hasOverlayMask
							);
						}
					}
				} else {
					SUI.openModal(
							modalId,
							focusAfterClosed,
							focusWhenOpen,
							hasOverlayMask
					);
				}

			}
		}
	}
</script>