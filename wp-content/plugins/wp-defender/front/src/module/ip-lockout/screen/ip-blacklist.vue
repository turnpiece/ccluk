<template>
	<div class="sui-box">
		<form method="post" @submit.prevent="update_settings">
			<div class="sui-box-header">
				<h3 class="sui-box-title">
					{{__("IP Banning")}}
				</h3>
			</div>
			<div class="sui-box-body">
				<p>
					{{__("Choose which IP addresses you wish to permanently ban from accessing your website.")}}
				</p>
				<hr/>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        {{__("IP Addresses")}}
                    </span>
						<span class="sui-description">
                        {{__("Add IP addresses you want to permanently ban from, or always allow access to your website.")}}
                    </span>
					</div>
					<div class="sui-box-settings-col-2">
						<strong>{{__("Blacklist")}}</strong>
						<p class="sui-description">
							{{__("Any IP addresses you list here will be completely blocked from accessing your website, including admins.")}}
						</p>
						<div class="sui-border-frame">
							<label class="sui-label">{{__("Blacklisted IPs")}}</label>
							<textarea class="sui-form-control"
							          id="ip_blacklist" name="ip_blacklist"
							          :placeholder="__('Add IP addresses here, one per line')"
							          v-model="model.ip_blacklist"
							          rows="8"></textarea>
							<span class="sui-description">
                                {{__("Both IPv4 and IPv6 are supported. IP ranges are also accepted in format xxx.xxx.xxx.xxx-xxx.xxx.xxx.xxx.")}}
                            </span>
						</div>
						<strong>{{__("Whitelist")}}</strong>
						<p class="sui-description">
							{{__("Any IP addresses you list here will be exempt any existing or new ban rules outlined in login protection, 404 detection or IP ban lists.")}}
						</p>
						<div class="sui-border-frame">
							<label class="sui-label">{{__("Allowed IPs")}}</label>
							<textarea class="sui-form-control"
							          id="ip_whitelist" name="ip_whitelist"
							          :placeholder="__('Add IP addresses here, one per line')"
							          v-model="model.ip_whitelist"
							          rows="8"></textarea>
							<span class="sui-description">
                                {{__("One IP address per line. Both IPv4 and IPv6 are supported. IP ranges are also accepted in format xxx.xxx.xxx.xxx-xxx.xxx.xxx.xxx.")}}
                            </span>
						</div>
						<div class="sui-notice">
							<p v-html="user_ip_notice"></p>
						</div>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">{{__("Active Lockouts")}}</span>
						<span class="sui-description">
                            {{__("View IP addresses that are temporarily blocked from accessing your site according to your lockout rules. You can release IP addresses from the temporarily block here.")}}
                        </span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-notice sui-notice-info margin-bottom-10">
							<p v-html="log_status"></p>
						</div>
						<button v-show="blacklist.count > 0" type="button" class="sui-button sui-button-gray"
						        data-a11y-dialog-show="ips-modal">
							{{__("Unlock ips")}}
						</button>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">{{__("Locations")}}</span>
						<span class="sui-description">{{__("Use this feature to ban any countries you don't expect/want traffic from to protect your site entirely from unwanted hackers and bots.")}}</span>
					</div>
					<div class="sui-box-settings-col-2 geo-ip-block">
						<div v-if="misc.geo_db_downloaded===false">
							<div class="sui-notice sui-notice-info sui-notice-global">
								<p>
									{{__("To use this feature you must follow the steps described below to download the latest Geo IP Database.")}}
								</p>
							</div>
							<div class="sui-border-frame">
								<div class="download-instruction" v-html="this.geodb_download_instruction"></div>
								<div class="sui-form-field">
									<label class="sui-label">
										{{__("API Key")}}
									</label>
									<div>
										<span class="sui-field-prefix"><i class="sui-icon-key"></i></span>
										<input placeholder="Place the API key here"
										       class="sui-form-control sui-field-has-prefix" type="text"
										       v-model="api_key"/>
									</div>
								</div>
								<div class="sui-notice-buttons">
									<submit-button :disabled="!geo_downloadable" id="download-geodb" type="button"
									               css-class="sui-button-ghost"
									               @click="download_geodb"
									               :state="state">
										<i class="sui-icon-download-cloud" aria-hidden="true"></i>
										<i class="sui-screen-reader-text">{{__("Download")}}</i>
										{{__("Download")}}
									</submit-button>
								</div>
							</div>
						</div>
						<div v-else>
							<div v-if="misc.current_country===false">
								<div class="sui-notice sui-notice-warning">
									<p>
										{{__("Can't detect current country, it seem your site setup in localhost environment")}}
									</p>
								</div>
							</div>
							<div v-else>
								<strong>{{__("Blacklist")}}</strong>
								<p class="sui-description no-margin-bottom">
									{{__("Any countries you select will not be able to access any area of your website.")}}
								</p>
								<div class="sui-border-frame">
									<div class="sui-control-with-icon">
										<select class="sui-select jquery-select sui-form-control"
										        name="country_blacklist" id="country_blacklist"
										        :placeholder="__('Type country name')"
										        v-model="model.country_blacklist"
										        multiple>
											<option v-for="(value,index) in misc.blacklist_countries"
											        v-bind:value="index">
												{{value}}
											</option>
										</select>
										<i class="sui-icon-web-globe-world" aria-hidden="true"></i>
									</div>
								</div>
								<strong>{{__("Whitelist")}}</strong>
								<p class="sui-description no-margin-bottom">
									{{__("Any countries you select will always be able to view your website. Note: We've added your default country by default.")}}
								</p>
								<div class="sui-border-frame">
									<div class="sui-control-with-icon">
										<select class="sui-select sui-select jquery-select sui-form-control"
										        name="country_whitelist" id="country_whitelist"
										        :placeholder="__('Type country name')"
										        v-model="model.country_whitelist" multiple>
											<option v-for="(value,index) in misc.whitelist_countries"
											        v-bind:value="index">
												{{value}}
											</option>
										</select>
										<i class="sui-icon-web-globe-world" aria-hidden="true"></i>
									</div>
									<p class="sui-description">
										{{__("Note: your whitelist will override any country ban, but will still follow your 404 and login lockout rules.")}}
									</p>
								</div>
								<p class="sui-description">
									This product includes GeoLite2 data created by MaxMind, available from
									<a href="https://www.maxmind.com">https://www.maxmind.com</a>.
								</p>
							</div>
						</div>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label">{{__("Message")}}</span>
						<span class="sui-description">{{__("Customize the message locked out users will see.")}}</span>
					</div>
					<div class="sui-box-settings-col-2">
						<label class="sui-label">
							{{__("Custom message")}}
						</label>
						<div class="sui-form-field">
                    <textarea name="ip_lockout_message" class="sui-form-control"
                              :placeholder="__('The administrator has blocked your IP from accessing this website.')"
                              v-model="model.ip_lockout_message"
                              id="ip_lockout_message"></textarea>
							<span class="sui-description" v-html="demo_link">
                            </span>
						</div>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                        {{__("Import")}}
                        </span>
						<span class="sui-description">
                        {{__("Use this tool to import both your blacklist and whitelist from another website.")}}
                        </span>
					</div>
					<div class="sui-box-settings-col-2">
						<div class="sui-form-field">
							<span>{{__("Upload your exported blacklist.")}}</span>
							<div class="upload-input sui-upload" :class="{'sui-has_file':ip_import.id!==false}">
								<div class="sui-upload-file">
									<span>{{ip_import.name}}</span>
									<button aria-label="Remove file" type="button" class="file-picker-remove"
									        v-on:click="remove_import_file">
										<i class="sui-icon-close" aria-hidden="true"></i>
									</button>
								</div>
								<button type="button" class="sui-upload-button file-picker">
									<i class="sui-icon-upload-cloud"
									   aria-hidden="true"></i> {{__("Upload file")}}
								</button>
							</div>
							<div class="clear margin-top-10"></div>
							<submit-button type="button" @click="import_ip"
							               css-class="sui-button-ghost" :state="state">
								<i class="sui-icon-download-cloud" aria-hidden="true"></i>
								{{__("Import")}}
							</submit-button>
							<span class="sui-description">
                            {{__("Note: Existing IPs will not be removed - only new IPs added.")}}
                            </span>
						</div>
					</div>
				</div>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
                        <span class="sui-settings-label">
                        {{__("Export")}}
                        </span>
						<span class="sui-description">
                            {{__("Export both your blacklist and whitelist to use on another website.")}}
                        </span>
					</div>
					<div class="sui-box-settings-col-2">
						<a :href="export_url" class="sui-button sui-button-outlined export">
							<i class="sui-icon-upload-cloud" aria-hidden="true"></i>
							{{__("Export")}}
						</a>
						<span class="sui-description">
                        {{__("The export will include both the blacklist and whitelist.")}}
                        </span>
					</div>
				</div>
			</div>
			<div class="sui-box-footer">
				<div class="sui-actions-right">
					<submit-button type="submit" css-class="sui-button-blue" :state="state">
						<i class="sui-icon-save" aria-hidden="true"></i>
						{{__("Save Changes")}}
					</submit-button>
				</div>
			</div>
		</form>
		<locked-ips-dialog @fetched="blacklist.count = $event"></locked-ips-dialog>
	</div>
</template>

<script>
	import base_heper from '../../../helper/base_hepler';
	import locked_ips_dialog from '../component/locked-ips-dialog';
	export default {
		mixins: [base_heper],
		name: "ip_blacklist",
		props: ['view'],
		data: function () {
			return {
				model: iplockout.model.blacklist,
				state: {
					on_saving: false,
				},
				geo_downloadable: false,
				nonces: iplockout.nonces,
				endpoints: iplockout.endpoints,
				misc: iplockout.misc,
				ip_import: {
					id: false,
					name: null
				},
				blacklist: {
					count: null
				},
				api_key: null
			}
		},
		components: {
			'locked-ips-dialog': locked_ips_dialog
		},
		methods: {
			download_geodb: function () {
				let that = this;
				this.httpPostRequest('downloadGeoDB', {
					api_key: that.api_key
				}, function (response) {
					that.$nextTick(function () {
						if (response.success === true) {
							that.misc.geo_db_downloaded = true;
							location.reload();
						} else {
							Defender.showNotification('error', response.data.message);
						}
					})
				})
			},
			import_ip: function () {
				let that = this;
				this.httpPostRequest('importIPs', {
					'id': that.ip_import.id
				}, function () {
					that.ip_import = {
						id: false,
						name: null
					}
				})
			},
			remove_import_file: function () {
				this.ip_import.id = false;
				this.ip_import.name = null;
			},
			update_settings: function () {
				let data = this.model;
				this.httpPostRequest('updateSettings', {
					data: JSON.stringify(data)
				});
			},
		},
		computed: {
			user_ip_notice: function () {
				return this.vsprintf(this.__("We recommend you add your own IP to avoid getting locked out accidentally! Your current IP is <span class='admin-ip'>%s</span>"), this.misc.user_ip)
			},
			log_status: function () {
				if (this.blacklist.count === null) {
					return this.__("Loading data...")
				} else if (this.blacklist.count === 0) {
					return this.__("There are no IP addresses being blocked at the moment.")
				} else if (this.blacklist.count === 1) {
					return this.__("There is one IP address being blocked temporary.")
				} else {
					return this.vsprintf(this.__("There are %d IP address being blocked temporary."), this.blacklist.count)
				}
			},
			demo_link: function () {
				return this.vsprintf(this.__("This message will be displayed across your website during the lockout period. See a quick preview <a href=\"%s\">here</a>."), this.siteUrl('?def-lockout-demo=1&type=blacklist'));
			},
			ip_block_count: function () {
				return this.vsprintf(this.__("%s results"), this.blacklist.count)
			},
			export_url: function () {
				return this.adminUrl('admin.php?page=wdf-ip-lockout&view=export&_wpnonce=' + this.nonces['exportIps'])
			},
			geodb_download_instruction: function () {
				let strings = '<span class="sui-description">' + this.vsprintf(this.__("1. <a target='_blank' href='%s'>Sign up</a> for GeoLite2 Downloadable Databases."), "https://www.maxmind.com/en/geolite2/signup") + '</span>'
				strings += '<span class="sui-description">' + this.vsprintf(this.__('2. Login to your account and follow <a target="_blank" href="%s">this link</a> to copy the API key.'), "https://www.maxmind.com/en/accounts/current/license-key") + '</span>'
				strings += '<span class="sui-description">' + this.__('3. Place the API key in the input below and click the download button.') + '</span>'
				return strings;
			}
		},
		watch: {
			'api_key': function (val, old) {
				if (val.trim().length > 0) {
					this.geo_downloadable = true;
				} else {
					this.geo_downloadable = false;
				}
			}
		},
		mounted: function () {
			let mediaUploader;
			let vm = this;
			jQuery('.file-picker').click(function () {
				if (mediaUploader) {
					mediaUploader.open();
					return;
				} // Extend the wp.media object

				mediaUploader = wp.media.frames.file_frame = wp.media({
					title: 'Choose an Import file',
					button: {
						text: 'Choose File'
					},
					multiple: false
				}); // When a file is selected, grab the URL and set it as the text field's value

				mediaUploader.on('select', function () {
					var attachment = mediaUploader.state().get('selection').first().toJSON();
					vm.ip_import.id = attachment.id;
					vm.ip_import.name = attachment.filename;
					jQuery('.upload-input').addClass('sui-has_file');
					jQuery('.upload-input .sui-upload-file span').text(attachment.filename);
				}); // Open the uploader dialog

				mediaUploader.open();
			});
			let self = this;
			jQuery('.jquery-select').change(function () {
				let value = jQuery(this).val();
				let key = jQuery(this).attr('name');
				self.model[key] = value;
			})

			if (typeof this.model.country_blacklist === "string") {
				if (this.model.country_blacklist.length) {
					this.model.country_blacklist = this.model.country_blacklist.split(',');
				} else {
					this.model.country_blacklist = [];
				}
			}

			if (typeof this.model.country_whitelist === "string") {
				if (this.model.country_whitelist.length) {
					this.model.country_whitelist = this.model.country_whitelist.split(',');
				} else {
					this.model.country_whitelist = [];
				}
			}
		}
	}
</script>
