<template>
	<div class="sui-accordion-item-body" v-if="scenario==='issue'">
		<div class="sui-box">
			<div class="sui-box-body">
				<strong>{{__("Issue Details")}}</strong>
				<p v-if="item.scenario==='modified'">
					{{__("Compare your file with the original file in the WordPress repository. Pieces highlighted in red will be removed when you patch the file, and pieces highlighted in green will be added.")}}
				</p>
				<p v-else-if="item.scenario==='unknown'">
					{{__("Defender found this stray file in your WordPress site directory. The current version of WordPress doesn't require it and as far as we can tell it's harmless (maybe even from an older WordPress install), so you can delete it or ignore it. Before deleting any files, be sure to back up your website.")}}
				</p>
				<p v-else>
					{{__("We found this folder in your WordPress file list. Your current version of WordPress doesn't use this folder so it might belong to another application. If you don't recognize it, you can delete this folder (don't forget to back up your website first!) or get in touch with the WPMU DEV support team for more information.")}}
				</p>
				<strong>
					{{__("Current code")}}
				</strong>
				<pre v-if="pulled===true" class=""><code :id="element_id" :class="detectLanguage(item.full_path)"
				                                         v-html="getCode"></code></pre>
				<i class="sui-icon-loader sui-loading" aria-hidden="true" v-else></i>
				<table class="sui-table">
					<tbody>
					<tr>
						<td>
							<i class="sui-icon-folder-open" aria-hidden="true"></i>
							<strong>{{__("Location")}}</strong>
						</td>
						<td v-text="item.full_path">
						</td>
					</tr>
					<tr>
						<td>
							<i class="sui-icon-download-cloud" aria-hidden="true"></i>
							<strong>
								{{__("Size")}} </strong>
						</td>
						<td v-text="item.size"></td>
					</tr>
					<tr>
						<td>
							<i class="sui-icon-calendar" aria-hidden="true"></i>
							<strong>{{__("Date added")}}</strong>
						</td>
						<td v-text="item.date_added">
						</td>
					</tr>
					</tbody>
				</table>
			</div>
			<div class="sui-box-footer">
				<div class="sui-actions-left">
					<button @click.prevent="ignoreIssue(item)" type="button"
					        :class="{'sui-button-onload':state.on_saving}" :disabled="state.on_saving"
					        class="sui-button sui-button-ghost">
                                <span class="sui-loading-text">
                                    <i class="sui-icon-save" aria-hidden="true"></i>
                                    {{__("Ignore")}}
                                </span>
						<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
					</button>
				</div>
				<div class="sui-actions-right">
					<div v-if="item.scenario==='unknown' || item.scenario==='dir'">
						<button v-show="state.deleting===false" type="button" @click="state.deleting=true"
						        class="sui-button sui-button-red">
							{{__("Delete")}}
						</button>
						<div v-show="state.deleting===true">
							<p>{{__("This will permanently remove the selected file/folder. Are you sure you want to continue?")}}</p>
							<button @click.prevent="deleteIssue(item)" type="button"
							        :class="{'sui-button-onload':state.on_saving}" :disabled="state.on_saving"
							        class="sui-button sui-button-red">
                                    <span class="sui-loading-text">
                                       {{__("Yes")}}
                                    </span>
								<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
							</button>
							<button @click="state.deleting=false" type="button"
							        class="sui-button sui-button-ghost">
								{{__("No")}}
							</button>
						</div>
					</div>
					<div v-else>
						<button @click.prevent="solveIssue(item)" type="button"
						        :class="{'sui-button-onload':state.on_saving}" :disabled="state.on_saving"
						        class="sui-button sui-button-blue">
                                <span class="sui-loading-text">
                                    {{__("Restore")}}
                                </span>
							<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

</template>

<script>
	import scan_helper from '../helper/scan-helper';

	export default {
		mixins: [scan_helper],
		name: "issue_core",
		props: ['item', 'scenario'],
		data: function () {
			return {
				state: {
					deleting: false,
					on_saving: false
				},
				code: '',
				nonces: scanData.nonces,
				endpoints: scanData.endpoints,
				element_id: '',
				pulled: false
			}
		},
		computed: {
			getCode: function () {
				return this.code;
				if (this.item.scenario !== 'dir') {
					//console.log(this.$root.prism.highlight(this.code, Prism.languages.html, 'html'));
					//return this.$root.prism.highlight(this.code, Prism.languages.markup, 'markup');
				} else {
					return this.code;
				}
			}
		},
		methods: {
			decoder(str) {
				var textArea = document.createElement('textarea');
				textArea.innerHTML = str;
				return textArea.value;
			},
			pullSourceCode: function () {
				if (this.scenario !== 'issue') {
					return;
				}
				if (this.pulled === true) {
					return;
				}
				let self = this;
				this.httpPostRequest('getFileSrcCode', {'id': this.item.id}, function (response) {
					if (response.success === true) {
						self.code = response.data.code;
						self.pulled = true;
						// self.$nextTick(() => {
						// 	let element = document.getElementById(self.element_id);
						// 	//Prism.highlightElement(element);
						// })
					}
				}, true)
			},
			detectLanguage: function (file) {
				let ext = file.split('.').pop();
				ext = ext.toLowerCase();
				if (ext === 'php') {
					return 'language-php';
				} else if (ext === 'js') {
					return 'language-js';
				}

				return 'language-markup';
			}
		},
		created: function () {
			this.element_id = Math.random()
		},
		updated: function () {

		}
	}
</script>
