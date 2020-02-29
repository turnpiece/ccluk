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
						{{__("By default, a plugin/theme vulnerability could allow a PHP file to get uploaded into your site's directories and in turn execute harmful scripts that can wreak havoc on your website. Prevent this altogether by disabling direct PHP execution in directories that don't require it.")}}
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
							{{ __( "Currently, all directories can have PHP code executed in them. It’s best to lock this down to only the directories that require, and add any further execeptions you need." ) }}
						</p>
						<strong>
							{{ __( "How to fix" ) }}
						</strong>
						<p>
							{{ __( "We can lock down directories WordPress doesn’t need to protect you from PHP execution attacks. You can also add exceptions for specific files you need to run. Alternately, you can ignore this tweak if you don’t require it. Either way, you can easily revert these actions at any time." ) }}
						</p>
					</div>
					<div class="sui-tabs sui-side-tabs">
						<div class="sui-side-tabs">
							<div class="sui-tabs-menu">
								<label for="pv_apache" :class="{'active':current_server==='apache'}"
								       class="sui-tab-item">
									<input type="radio" id="pv_apache" v-model="current_server" value="apache"
									       data-tab-menu="pi_apache-litespeed-box">
									{{__("Apache")}}
								</label>
								<label for="pv_litespeed" :class="{'active':current_server==='litespeed'}"
								       class="sui-tab-item">
									<input type="radio" id="pv_litespeed" v-model="current_server" value="litespeed"
									       data-tab-menu="pi_apache-litespeed-box">
									{{__("Litespeed")}}
								</label>
								<label for="pv_nginx" :class="{'active':current_server==='nginx'}" class="sui-tab-item">
									<input type="radio" id="pv_nginx" v-model="current_server" value="nginx"
									       data-tab-menu="pi_nginx-box">
									{{__("Nginx")}}
								</label>
								<label for="pv_iis" :class="{'active':current_server==='iis'}" class="sui-tab-item">
									<input type="radio" id="pv_iis" v-model="current_server" value="iis"
									       data-tab-menu="pi_iis-box">
									{{__("IIS")}}
								</label>
								<label for="pv_iis7" :class="{'active':current_server==='iis7'}" class="sui-tab-item">
									<input type="radio" id="pv_iis7" v-model="current_server" value="iis7"
									       data-tab-menu="pi_iis7-box">
									{{__("IIS7")}}
								</label>
							</div>
							<div class="sui-tabs-content">
								<div class="sui-tab-content sui-tab-boxed"
								     :class="{'active':(current_server==='apache' || current_server==='litespeed')}"
								     data-tab-content="pi_apache-litespeed-box">
									<form method="post" v-on:submit.prevent="process">
										<p class="no-margin-bottom">
											{{__("We can automatically add an .htaccess file to your root folder to action this fix.")}}
										</p>
										<button class="sui-button sui-button-blue" type="submit">
											{{__("Update .htaccess file")}}
										</button>
										<div class="sui-form-field margin-top-30">
											<label class="sui-label">{{__("Exceptions")}}</label>
											<textarea name="file_paths" v-model="file_paths"
											          class="sui-form-control"></textarea>
											<span class="sui-description">
                                            {{__("Add exceptions to PHP files you want to continue to run. Include the full paths to the file.")}}
                                        </span>
										</div>
									</form>
								</div>
								<div class="sui-tab-content sui-tab-boxed" :class="{'active':current_server==='nginx'}"
								     data-tab-content="pi_nginx-box">
									<p>
										{{__("We can’t automatically action this fix, but follow the instructions below to patch this up.First, add any exceptions to files you want to allow PHP to be executed from, then follow the instructions below.")}}
									</p>
									<div class="sui-form-field margin-top-30">
										<label class="sui-label">{{__("Exceptions")}}</label>
										<textarea v-model="nginx_files_path" class="sui-form-control"></textarea>
										<span class="sui-description">
                                            {{__("Add exceptions to PHP files you want to continue to run. Include the full paths to the file.")}}
                                        </span>
										<strong>{{__("Instructions")}}</strong>
										<p>
											{{__("1. Copy the generated code into your site specific .conf file usually located in a subdirectory under /etc/nginx/... or /usr/local/nginx/conf/...")}}
										</p>
										<p>
											{{__("2. Add the code above inside the server section in the file, right before the php location block. Looks something like:")}}
											<code>location ~ \.php$ {</code>
										</p>
										<p>
											{{__("3. Reload NGINX")}}
										</p>
										<strong>{{__("Code")}}</strong>
										<p>
											<code>## WP Defender - Prevent PHP Execution ##<br/>{{misc.nginx_rules}}<span
													v-html="nginx_files_path_parsed"></span><br/>## WP Defender - End ##</code>
										</p>
										<p>
											<submit-button @click="reCheck('prevent-php')"
											               data-tooltip="Re-check the status of the tweak"
											               :state="state" type="button"
											               css-class="sui-tooltip sui-button-ghost">
												<i class="sui-icon-update" aria-hidden="true"></i>
												{{ __("Re-check")}}
											</submit-button>
										</p>
										<div class="sui-notice">
											<p v-html="supportUrl"></p>
										</div>
									</div>
								</div>
								<div class="sui-tab-content sui-tab-boxed" :class="{'active':current_server==='iis'}"
								     data-tab-content="pi_iis-box">
									<p v-html="issUrl"></p>
								</div>
								<div class="sui-tab-content sui-tab-boxed" :class="{'active':current_server==='iss7'}"
								     data-tab-content="pi_iis7-box">
									<p v-html="iisText"></p>
									<form method="post" v-on:submit.prevent="process">
										<button class="sui-button sui-button-blue" type="submit">
											{{__("Add web.config file")}}
										</button>
									</form>
								</div>
							</div>
						</div>
					</div>
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
				</div>
			</div>
		</div>
	</div>
</template>
<script>
	import helper from '../../../helper/base_hepler';
	import securityTweakHelper from '../helper/security-tweak-helper';
	import store from "../store/store";


	export default {
		mixins: [helper, securityTweakHelper],
		props: ['status', 'title', 'slug', 'errorReason', 'successReason', 'misc'],
		data: function () {
			return {
				state: {
					on_saving: false
				},
				file_paths: null,
				current_server: this.misc.active_server,
				nginx_files_path: null,
				nginx_files_path_parsed: null
			}
		},
		methods: {
			process: function () {
				let data = {
					slug: this.slug,
					file_paths: this.file_paths,
					current_server: this.current_server
				}
				this.state.on_saving = true;
				let self = this;
				this.resolve(data, function (response) {
					if (response.success === false) {
						self.state.on_saving = false;
						Defender.showNotification('error', response.data.message);
					} else {
						Defender.showNotification('success', response.data.message);
					}
				});
			}
		},
		watch: {
			nginx_files_path: function () {
				let text_val = this.nginx_files_path;
				//We cant allow index.php
				if (text_val.includes('index.php')) {
					text_val = text_val.replace(/index.php/g, '');
					this.nginx_files_path_parsed = text_val;
				}

				//no fancy scripts or html code. We also validate server side
				if (/<[a-z][\s\S]*>/i.test(text_val)) {
					text_val = text_val.replace(/<\/?[^>]+(>|$)/g, "");
					this.nginx_files_path_parsed = text_val;
				}

				//Nginx
				let excludedFiles = text_val.split('\n');
				let newRule = "";
				let $wp_content = this.misc.wp_content_dir;
				jQuery.each(excludedFiles, function (index, file) {
					if (file) {
						newRule += "\n location ~* ^" + $wp_content + "/.*&#92;" + file + "$ {" +
							" \n  allow all;" +
							"\n}";
					}
				});
				this.nginx_files_path_parsed = newRule;
			}
		},
		computed: {
			issUrl: function () {
				return 'For IIS servers, ' + '<a target="_blank" href="https://technet.microsoft.com/en-us/library/cc725855(v=ws.10).aspx">visit Microsoft TechNet</a>';
			},
			supportUrl: function () {
				return "Still having trouble? " + "<a target='_blank' href='https://premium.wpmudev.org/forums/forum/support#question'>Open a support ticket</a>";
			},
			iisText: function () {
				let string = vsprintf(this.__("We will place %s file into the uploads folder to lock down the files and folders inside."), "<strong>web.config</strong>");
				string += vsprintf(this.__("For more information, please <a target='_blank' href='%s'>visit Microsoft TechNet</a>"), "https://technet.microsoft.com/en-us/library/cc725855(v=ws.10).aspx");
				return string;
			}
		}
	}
</script>