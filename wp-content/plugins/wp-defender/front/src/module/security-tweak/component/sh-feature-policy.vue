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
                        {{__("The Feature-Policy response header provides control over what browser features can be used when web pages are embedded in iframes. Examples of this include.")}}
                    </p>
                    <ol>
                        <li>{{__("Embedding an iframe where you you don't want the embedded site to be able to access the visitor's camera.")}}</li>
                        <li>{{__("To catch situations where unoptimized images are output to your website from a CMS;")}}</li>
                        <li>{{__("Multiple developers are working on a single project and you want to know if they're using outdated APIs.")}}</li>
                    </ol>
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
                        <strong>
                            {{ __( "How to fix" ) }}
                        </strong>
                        <p>
                            {{ __( "Choose an option that matches your requirements from the options below to prevent unwanted actions when your webpages are embedded elsewhere. Alternately, you can ignore this tweak if it does not apply to your website. Either way, you can easily revert the action at any time." ) }}
                        </p>
                    </div>
                    <div class="sui-side-tabs">
                        <div class="sui-tabs-menu">
                            <label for="fp-site" class="sui-tab-item" :class="{active:mode==='self'}">
                                <input type="radio" name="mode" value="self" v-model="mode"
                                       id="fp-site"
                                       data-tab-menu="fp-site-box">
                                {{__("Allow on site & iframe")}}
                            </label>
                            <label for="fp-allow" class="sui-tab-item" :class="{active:mode==='allow'}">
                                <input type="radio" name="mode" value="allow" v-model="mode"
                                       id="fp-allow"
                                       data-tab-menu="fp-allow-box">
                                {{__("Allow all")}}
                            </label>
                            <label for="fp-origins" class="sui-tab-item" :class="{active:mode==='origins'}">
                                <input type="radio" name="mode" value="origins" v-model="mode"
                                       id="fp-origins"
                                       data-tab-menu="fp-origins-box">
                                {{__("Specific Origins")}}
                            </label>
                            <label for="fp-none" class="sui-tab-item" :class="{active:mode==='none'}">
                                <input type="radio" name="mode" value="none" v-model="mode"
                                       id="fp-none">
                                {{__("None")}}
                            </label>
                        </div>

                        <div class="sui-tabs-content">
                            <div class="sui-tab-content sui-tab-boxed" id="fp-site-box"
                                 :class="{active:mode==='self'}"
                                 data-tab-content="fp-site-box">
                                <p>
                                    {{__("The page can only be displayed in a frame on the same origin as the page itself. The spec leaves it up to browser vendors to decide whether this option applies to the top level, the parent, or the whole chain.")}}
                                </p>
                            </div>
                            <div class="sui-tab-content sui-tab-boxed" id="fp-allow-box"
                                 :class="{active:mode==='allow'}"
                                 data-tab-content="fp-allow-box">
                                <p>
                                    {{__("The page can’t be displayed in a frame, regardless of the site attempting to do so.")}}
                                </p>
                            </div>
                            <div class="sui-tab-content sui-tab-boxed" id="fp-origins-box"
                                 :class="{active:mode==='origins'}"
                                 data-tab-content="fp-origins-box">
                                <div class="sui-form-field">
                                    <label class="sui-label">{{__("Origin URL")}}</label>
                                    <textarea class="sui-form-control" v-model="values"
                                              :placeholder="__('Place URLs here, one per line')"></textarea>
                                    <span class="sui-description">
                                            {{__("The feature is allowed for specific origins. Place URLs here https://example.com, one per line.")}}
                                        </span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div v-if="status==='fixed'" class="sui-box-footer">
                    <div class="sui-actions-left">
                        <form v-on:submit.prevent="revert" method="post">
                            <submit-button :state="state" css-class="sui-button-ghost revert" type="submit">
                                <span class="sui-loading-text">{{__( "Revert" ) }}</span>
                                <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                            </submit-button>
                        </form>
                    </div>
                    <div class="sui-actions-right">
                        <form v-on:submit.prevent="process('update')" method="post"
                              class="hardener-frm rule-process hardener-frm-process-xml-rpc">
                            <submit-button :state="state" type="submit" css-class="update">
                                <span class="sui-loading-text">{{__( "Update" ) }}</span>
                                <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                            </submit-button>
                        </form>
                    </div>
                </div>
                <div v-if="status==='issues'" class="sui-box-footer">
                    <div class="sui-actions-left">
                        <form method="post" v-on:submit.prevent="ignore">
                            <submit-button :state="state" type="submit" name="ignore"
                                           value="ignore" class="sui-button sui-button-ghost ignore">
                                <span class="sui-loading-text"><i class="sui-icon-eye-hide" aria-hidden="true"></i> {{ __( "Ignore")}}</span>
                                <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                            </submit-button>
                        </form>
                    </div>
                    <div class="sui-actions-right">
                        <form v-on:submit.prevent="process('enforce')" method="post"
                              class="hardener-frm apply rule-process hardener-frm-process-xml-rpc">
                            <submit-button :state="state" css-class="sui-button-blue" type="submit">
                                <span class="sui-loading-text">{{__( "Enforce" ) }}</span>
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


	export default {
		mixins: [helper, securityTweakHelper],
		props: ['status', 'title', 'slug', 'errorReason', 'successReason', 'misc'],
		data: function () {
			return {
				prefix: '',
				state: {
					on_saving: false
				},
				mode: null,
				values: null
			}
		},
		created: function () {
			this.mode = this.misc.mode;
			this.values = this.misc.values
		},
		methods: {
			process: function (scenario) {
				let data = {
					slug: this.slug,
					mode: this.mode,
					values: this.values,
					scenario: scenario
				}
				this.state.on_saving = true;
				let self = this;
				this.resolve(data, function (response) {
                    self.state.on_saving = false;
					if (response.success === false) {
						Defender.showNotification('error', response.data.message);
					} else {
						Defender.showNotification('success', response.data.message);
					}
				});
			}
		},

	}
</script>