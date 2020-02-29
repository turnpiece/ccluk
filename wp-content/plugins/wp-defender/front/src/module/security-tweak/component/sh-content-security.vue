<template>
    <div class="sui-accordion-item" :class="[cssClass,{'sui-accordion-item--open':misc.is_opened}]">
        <div v-show="!maybeResetValues" class="sui-notice-top sui-notice-info sui-can-dismiss">
            <div class="sui-notice-content">
                <p>
                    {{__("Please note, misconfiguring the directives can cause some issues. We highly recommend to test each directive before enforcing it.")}}
                </p>
            </div>
        </div>
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
        <div class="sui-accordion-item-body"
             v-if="status!=='ignore'">
            <div class="sui-box">
                <div class="sui-box-body">
                    <strong>{{__("Overview")}}</strong>
                    <p>
                        {{__("The Content-Security-Policy response header allows website admins to control resources the user-agent is allowed to load and use. The primary goal of a CSP is to mitigate and report XSS attacks. XSS attacks exploit the browser's trust of the content received from the server. Malicious scripts are executed by the victim's browser because the browser trusts the source of the content, even when it's not coming from where it seems to be coming from.")}}
                    </p>
                    <div v-if="status==='fixed'">
                        <strong>
                            {{ __( "Status" ) }}
                        </strong>
                        <div class="sui-notice sui-notice-success">
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
                            {{ __( "Choose which directives you want to enable for your website. You can test each setting before enforcing it using the test button at the bottom of this section. Alternately, you can ignore this tweak if it does not apply to your website. Either way, you can easily revert the action at any time." ) }}
                        </p>
                    </div>
                    <table class="sui-table">
                        <thead>
                        <tr>
                            <th>
                                <span class="float-l">{{__("Dierctives")}}</span>
                                <a href="https://content-security-policy.com/"
                                   target="_blank" class="sui-button sui-button-ghost float-r">
                                    <i class="sui-icon-academy"></i>{{__("View Documentation")}}
                                </a>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <label for="base-uri" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox"
                                           id="base-uri" true-value="1" false-value="0" v-model="model.base_uri"/>
                                    <span aria-hidden="true"></span>
                                    <span>base-uri <a target="_blank"
                                                      href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/base-uri"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p v-html="text.base_uri_text"></p>
                                <div v-show="parseInt(model.base_uri)===1">
                                    <select data-name="base_uri_values"
                                            class="sui-select content-security-select sui-form-field-error"
                                            multiple
                                            v-model="model.base_uri_values">
                                        <option value="*">*</option>
                                        <option value="'self'">'self'</option>
                                        <option value="'none'">'none'</option>
                                    </select>
                                    <p v-html="text.base_uri_desc"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="child-src" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="child-src" v-model="model.child_src"/>
                                    <span aria-hidden="true"></span>
                                    <span>child-src <a target="_blank"
                                                       href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/child-src"><i
                                            class="sui-icon-open-new-window"
                                            aria-hidden="true"></i></a></span>
                                </label>
                                <p v-html="text.child_src_text">
                                </p>
                                <div v-show="parseInt(model.child_src)===1">
                                    <select data-name="child_src_values" class="sui-select content-security-select"
                                            multiple
                                            v-model="model.child_src_values">
                                        <option value="*">*</option>
                                        <option value="'none'">'none'</option>
                                        <option value="'self'">'self'</option>
                                        <option value="http:">http:</option>
                                        <option value="https:">https:</option>
                                    </select>
                                    <p v-html="text.child_src_desc"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="default-src" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="default-src" v-model="model.default_src"/>
                                    <span aria-hidden="true"></span>
                                    <span>default-src <a target="_blank"
                                                         href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/default-src"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p>
                                    {{__("Serves as a fallback for the other CSP fetch directives. For each of the following directives that are absent, the user agent will look for the default-src directive and will use the value for it. You can read more about this directive")}}
                                </p>
                                <div v-show="parseInt(model.default_src)===1">
                                    <select data-name="default_src_values" class="sui-select content-security-select"
                                            multiple
                                            v-model="model.default_src_values">
                                        <option value="*">*</option>
                                        <option value="'self'">'self'</option>
                                        <option value="http:">http:</option>
                                        <option value="https:">https:</option>
                                        <option value="data:">data:</option>
                                        <option value="'unsafe-inline'">'unsafe-inline'</option>
                                        <option value="'unsafe-eval'">'unsafe-eval'</option>
                                    </select>
                                    <p v-html="text.default_src_desc"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="font-src" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="font-src" v-model="model.font_src"/>
                                    <span aria-hidden="true"></span>
                                    <span>font-src <a target="_blank"
                                                      href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/font-src"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p>
                                    {{__("Directive specifies valid sources for fonts loaded using @font-face. You can read more about this directive")}}
                                </p>
                                <div v-show="parseInt(model.font_src)===1">
                                    <select data-name="font_src_values" class="sui-select content-security-select"
                                            multiple
                                            v-model="model.font_src_values">
                                        <option value="*">*</option>
                                        <option value="'none'">'none'</option>
                                        <option value="'self'">'self'</option>
                                        <option value="data:">data:</option>
                                        <option value="https:">https:</option>
                                    </select>
                                    <p v-html="text.font_src_desc"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="form-action" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="form-action" v-model="model.form_action"/>
                                    <span aria-hidden="true"></span>
                                    <span>form-action <a target="_blank"
                                                         href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/form-action"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p>
                                    {{__("Restricts the URLs which can be used as the target of a form submissions from a given context. You can read more about this directive")}}
                                </p>
                                <div v-show="parseInt(model.form_action)===1">
                                    <select data-name="form_action_values" class="sui-select content-security-select"
                                            multiple
                                            v-model="model.form_action_values">
                                        <option value="*">*</option>
                                        <option value="'self'">'self'</option>
                                        <option value="data:">data:</option>
                                        <option value="https:">https:</option>
                                    </select>
                                    <p v-html="text.form_action_desc"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="frame-ancestors" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="frame-ancestors" v-model="model.frame_ancestors"/>
                                    <span aria-hidden="true"></span>
                                    <span>frame-ancestors <a target="_blank"
                                                             href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-ancestors"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p v-html="text.frame_ancestors_text">
                                </p>
                                <div v-show="parseInt(model.frame_ancestors)===1">
                                    <select data-name="frame_ancestors_values"
                                            class="sui-select content-security-select"
                                            multiple
                                            v-model="model.frame_ancestors_values">
                                        <option value="*">*</option>
                                        <option value="'none'">'none'</option>
                                        <option value="'self'">'self'</option>
                                        <option value="data:">data:</option>
                                        <option value="http:">http:</option>
                                        <option value="https:">https:</option>
                                    </select>
                                    <p v-html="text.frame_ancestors_desc"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="img-src" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="img-src" v-model="model.img_src"/>
                                    <span aria-hidden="true"></span>
                                    <span>img-src <a target="_blank"
                                                     href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/img-src"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p>
                                    {{__("img-src directive specifies valid sources of images and favicons. You can read more about this directive")}}
                                </p>
                                <div v-show="parseInt(model.img_src)===1">
                                    <select data-name="img_src_values" class="sui-select content-security-select"
                                            multiple
                                            v-model="model.img_src_values">
                                        <option value="*">*</option>
                                        <option value="'self'">'self'</option>
                                        <option value="data:">data:</option>
                                        <option value="https:">http:</option>
                                        <option value="https:">https:</option>
                                    </select>
                                    <p v-html="text.img_src_desc"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="media-src" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="media-src" v-model="model.media_src"/>
                                    <span aria-hidden="true"></span>
                                    <span>media-src <a target="_blank"
                                                       href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/media-src"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p v-html="text.media_src_text">
                                    {{__("Specifies valid sources for loading media using the &#x3C;frame&#x3E; , &#x3C;frame&#x3E; and &#x3C;frame&#x3E; elements. You can read more about this directive here.")}}
                                </p>
                                <div v-show="parseInt(model.media_src)===1">
                                    <select data-name="media_src_values" class="sui-select content-security-select"
                                            multiple
                                            v-model="model.media_src_values">
                                        <option value="*">*</option>
                                        <option value="'self'">'self'</option>
                                        <option value="'none'">'none'</option>
                                        <option value="data:">data:</option>
                                        <option value="https:">http:</option>
                                        <option value="https:">https:</option>
                                    </select>
                                    <p v-html="text.media_src_desc"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="object-src" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="object-src" v-model="model.object_src"/>
                                    <span aria-hidden="true"></span>
                                    <span>object-src <a target="_blank"
                                                        href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/object-src"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p v-html="text.object_src_text">
                                </p>
                                <div v-show="parseInt(model.object_src)===1">
                                    <select data-name="object_src_values" class="sui-select content-security-select"
                                            multiple
                                            v-model="model.object_src_values">
                                        <option value="*">*</option>
                                        <option value="'none'">'none'</option>
                                        <option value="'self'">'self'</option>
                                        <option value="data:">data:</option>
                                        <option value="https:">https:</option>
                                        <option value="'unsafe-inline'">'unsafe-inline'</option>
                                        <option value="'unsafe-eval'">'unsafe-eval'</option>
                                    </select>
                                    <p v-html="text.object_src_desc"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="plugin-types" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="plugin-types" v-model="model.plugin_types"/>
                                    <span aria-hidden="true"></span>
                                    <span>plugin-types <a target="_blank"
                                                          href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/plugin-type"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p>
                                    {{__("Restricts the set of plugins that can be embedded into a document by limiting the types of resources which can be loaded. You can read more about this directive")}}
                                </p>
                                <div v-show="parseInt(model.plugin_types)===1">
                                    <select data-name="plugin_types_values" class="sui-select content-security-select"
                                            multiple
                                            v-model="model.plugin_types_values">
                                        <option value="*">*</option>
                                        <option value="'none'">'none'</option>
                                        <option value="'self'">'self'</option>
                                        <option value="data:">data:</option>
                                        <option value="https:">https:</option>
                                        <option value="'unsafe-inline'">'unsafe-inline'</option>
                                        <option value="'unsafe-eval'">'unsafe-eval'</option>
                                    </select>
                                    <p v-html="text.plugin_types_desc"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="sandbox" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="sandbox" v-model="model.sandbox"/>
                                    <span aria-hidden="true"></span>
                                    <span>sandbox <a target="_blank"
                                                     href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/sandbox"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p v-html="text.sandbox_text">
                                    {{__("Enables a sandbox for the requested resource similar to the &#x3C;iframe&#x3E;, sandbox attribute. You can read more about this directive here.")}}
                                </p>
                                <div v-show="parseInt(model.sandbox)===1">
                                    <select data-name="sandbox_values" class="sui-select content-security-select"
                                            multiple
                                            v-model="model.sandbox_values">
                                        <option value="*">*</option>
                                        <option value="'none'">'none'</option>
                                        <option value="'self'">'self'</option>
                                        <option value="data:">data:</option>
                                        <option value="https:">https:</option>
                                        <option value="'unsafe-inline'">'unsafe-inline'</option>
                                        <option value="'unsafe-eval'">'unsafe-eval'</option>
                                    </select>
                                    <p v-html="text.sandbox_desc"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="script-src" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="script-src" v-model="model.script_src"/>
                                    <span aria-hidden="true"></span>
                                    <span>script-src <a target="_blank"
                                                        href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p>
                                    {{__("Specifies valid sources for JavaScript. You can read more about this directive")}}
                                </p>
                                <div v-show="parseInt(model.script_src)===1">
                                    <select data-name="script_src_values" class="sui-select content-security-select"
                                            multiple
                                            v-model="model.script_src_values">
                                        <option value="*">*</option>
                                        <option value="'none'">'none'</option>
                                        <option value="'self'">'self'</option>
                                        <option value="data:">data:</option>
                                        <option value="https:">https:</option>
                                        <option value="'unsafe-inline'">'unsafe-inline'</option>
                                        <option value="'unsafe-eval'">'unsafe-eval'</option>
                                    </select>
                                    <p v-html="text.script_src_desc"></p>
                                    <div class="sui-notice sui-notice-info">
                                        <p>
                                            {{__("Please note: We added the following 'unsafe-inline, 'unsafe-eval' 'self' values for you as it may cause some issues in case you misconfigure it. Please make sure to test how the directive effects on your website after changing any of these.")}}
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="style-src" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="style-src" v-model="model.style_src"/>
                                    <span aria-hidden="true"></span>
                                    <span>style-src <a target="_blank"
                                                       href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/style-src"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p>
                                    {{__("Specifies valid sources for stylesheets. You can read more about this directive")}}
                                </p>
                                <div v-show="parseInt(model.style_src)===1">
                                    <select data-name="style_src_values" class="sui-select content-security-select"
                                            multiple
                                            v-model="model.style_src_values">
                                        <option value="*">*</option>
                                        <option value="'none'">'none'</option>
                                        <option value="'self'">'self'</option>
                                        <option value="data:">data:</option>
                                        <option value="https:">https:</option>
                                        <option value="'unsafe-inline'">'unsafe-inline'</option>
                                        <option value="'unsafe-eval'">'unsafe-eval'</option>
                                    </select>
                                    <p v-html="text.style_src_desc"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="worker-src" class="sui-checkbox sui-checkbox-sm">
                                    <input type="checkbox" true-value="1" false-value="0"
                                           id="worker-src" v-model="model.worker_src"/>
                                    <span aria-hidden="true"></span>
                                    <span>worker-src <a target="_blank"
                                                        href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/worker-src"><i
                                            class="sui-icon-open-new-window" aria-hidden="true"></i></a></span>
                                </label>
                                <p>
                                    {{__("Specifies valid sources for Worker, SharedWorker, or ServiceWorker scripts. You can read more about this directive")}}
                                </p>
                                <div v-show="parseInt(model.worker_src)===1">
                                    <select data-name="worker_src_values" class="sui-select content-security-select"
                                            multiple
                                            v-model="model.worker_src_values">
                                        <option value="*">*</option>
                                        <option value="'none'">'none'</option>
                                        <option value="'self'">'self'</option>
                                        <option value="data:">data:</option>
                                        <option value="https:">https:</option>
                                        <option value="'unsafe-inline'">'unsafe-inline'</option>
                                        <option value="'unsafe-eval'">'unsafe-eval'</option>
                                    </select>
                                    <p v-html="text.worker_src_desc"></p>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td>
                                <button :disabled="maybeResetValues" type="button" @click="clearValues"
                                        class="sui-button sui-button-ghost">
                                    <i class="sui-icon-trash" aria-hidden="true"></i> {{__("Clear all values")}}
                                </button>
                                <submit-button @click="saveTempDirectives('temp')" :state="state" type="button"
                                               css-class="float-r">
                                    {{__("Test Directives")}}
                                </submit-button>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div v-if="status==='fixed'" class="sui-box-footer">
                    <div class="sui-actions-left">
                        <form v-on:submit.prevent="revert" method="post">
                            <submit-button :state="state"
                                           css-class="sui-button-ghost" type="submit">
                                <span class="sui-loading-text">{{__( "Revert" ) }}</span>
                                <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                            </submit-button>
                        </form>
                    </div>
                    <div class="sui-actions-right">
                        <form v-on:submit.prevent="process('update')" method="post"
                              class="hardener-frm rule-process hardener-frm-process-xml-rpc">
                            <submit-button :state="state" css-class="sui-button" type="submit">
                                <span class="sui-loading-text">{{__( "Update" ) }}</span>
                                <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                            </submit-button>
                        </form>
                    </div>
                </div>
                <div v-if="status==='issues'" class="sui-box-footer">
                    <div class="sui-actions-left">
                        <form method="post" v-on:submit.prevent="ignore">
                            <submit-button :state="state" type="submit" class="sui-button sui-button-ghost">
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
	import equal from 'fast-deep-equal';

	export default {
		mixins: [helper, securityTweakHelper],
		props: ['status', 'title', 'slug', 'errorReason', 'successReason', 'misc'],
		data: function () {
			return {
				state: {
					on_saving: false,
				},
				model: {},
				text: {},
				errors: {}
			}
		},
		methods: {
			process: function (scenario, callback) {
				let data = this.model;
				data.slug = this.slug;
				data.scenario = scenario;
				this.state.on_saving = true;
				let self = this;
				this.resolve(data, function (response) {
					self.state.on_saving = false;
					if (response.data.message !== undefined) {
						if (response.success === false) {
							Defender.showNotification('error', response.data.message);
						} else {
							Defender.showNotification('success', response.data.message);
							//rebind the new data
							self.model = response.data.fixed['sh-content-security'].misc.data;
						}
					}
					if (callback !== undefined) {
						callback(response);
					}
				});
			},
			clearValues: function () {
				//reset the values
				this.model = JSON.parse(JSON.stringify(this.misc.data));
				this.$nextTick(() => {
					jQuery('.content-security-select').trigger('change');
				})
			},
			saveTempDirectives: function () {
				this.process('temp', function (data) {
					//location.href = data.data.url;
                    location.reload();
				});
			}
		},
		computed: {
			maybeResetValues: function () {
				return equal(this.model, this.misc.data);
			}
		},
		created: function () {
			let data = this.model = JSON.parse(JSON.stringify(this.misc.data));
			this.text = this.misc.text;
			this.$nextTick(() => {
				Object.keys(data).forEach(function (key) {
					if (key.indexOf('_values') > -1) {
						if (data[key].length > 0) {
							let el = jQuery('select[data-name="' + key + '"]');
							for (var i = 0; i < data[key].length; i++) {
								if (el.find('option[value="' + data[key][i] + '"]').length === 0) {
									let newOption = new Option(data[key][i], data[key][i], false, false);
									el.append(newOption).trigger('change');
								}
							}
							el.val(data[key]).trigger('change');
						}
					}
				});
			})
		},
		mounted: function () {
			var self = this;
			document.onreadystatechange = () => {
				if (document.readyState === "complete") {
					jQuery('.content-security-select').SUIselect2({
						dropdownCssClass: 'sui-select-dropdown',
						tags: true,
					});
				}
			}
			jQuery('.content-security-select').on("select2:select select2:unselect", function (e) {
				var attribute = jQuery(this).data('name');
				let value = jQuery(this).SUIselect2('data');
				let data = [];
				for (let i = 0; i < value.length; i++) {
					data.push(value[i].text)
				}
				self.model[attribute] = data;
			})
		},
	}
</script>