<template>
	<div class="sui-accordion-item-body" v-if="scenario==='issue'">
		<div class="sui-box">
			<div class="sui-box-body">
				<strong>{{__("Issue Details")}}</strong>
				<p>
					{{ vsprintf(__("We've uncovered suspicious code in %s. The red highlighted code is the flagged code and the green is the cleaned up code. Note that these warnings can be false positives, so consult your developer before taking action."),item.full_path)}}
				</p>
				<div v-for="line in item.tracer">
					<label class="sui-label">
						{{__("Error")}}
					</label>
					<pre><code :id="element_id" :class="detectLanguage(item.full_path)" v-html="line.code"></code></pre>
					<p v-text="line.text"></p>
				</div>
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
			</div>
		</div>
	</div>
</template>

<script>
	import scan_helper from '../helper/scan-helper';

	export default {
		mixins: [scan_helper],
		name: "issue_content",
		props: ['item', 'scenario'],
		data: function () {
			return {
				state: {
					deleting: false,
					on_saving: false
				},
				nonces: scanData.nonces,
				endpoints: scanData.endpoints,
				code: '',
				element_id: '',
				pulled: false
			}
		},
		methods: {
			show: function (event, line) {
				// var targetId = event.currentTarget.id;
				// var parent = jQuery(document.getElementById(targetId)).closest('.sui-box').find('pre').first();
				// var curr = jQuery(this.$root.prism.plugins.lineNumbers.getLine(parent.get(0), line.line));
				// curr.get(0).scrollIntoView({
				//     block: 'center'
				// });
			},
			decoder(str) {
				var textArea = document.createElement('textarea');
				textArea.innerHTML = str;
				return textArea.value;
			},
			pullSourceCode: function () {
				//we do nothing now
				return;
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
						self.$nextTick(() => {
							let element = document.getElementById(self.element_id);
							Prism.highlightElement(element);
						})
					}
				}, true)
			},
			getCode: function () {
				return this.code;
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
