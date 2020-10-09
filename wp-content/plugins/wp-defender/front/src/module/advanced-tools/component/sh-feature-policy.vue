<template>
  <div class="sui-toggle-content">
        <span class="sui-description toogle-content-description">
            {{ __("Choose an option that matches your requirements from the options below to prevent unwanted actions when your webpages are embedded elsewhere.") }}
        </span>
    <sidetab slug="sh_feature_policy_mode" :active="model.sh_feature_policy_mode"
             @selected="model.sh_feature_policy_mode = $event"
             :labels="[
                 {
                   text:__('On site & iframe'),
                   mute:false,
                   value:'self'
                 },
                 {
                   text:__('All'),
                   mute:false,
                   value:'allow'
                 },
                 {
                   text:__('Specific Origins'),
                   mute:false,
                   value:'origins'
                 },
                 {
                   text:__('None'),
                   mute:false,
                   value:'none'
                 },
             ]"
    >
      <template v-slot:self>
        <p class="sui-p-small">
          {{ __("The page can only be displayed in a frame on the same origin as the page itself. The spec leaves it up to browser vendors to decide whether this option applies to the top level, the parent, or the whole chain.") }}
        </p>
      </template>
      <template v-slot:allow>
        <p class="sui-p-small">
          {{ __("The feature will be allowed in this document, and all nested browsing contexts (iframes) regardless of their origin.") }}
        </p>
      </template>
      <template v-slot:origins>
        <div class="sui-form-field">
          <label class="sui-label">{{ __("Origin URL") }}</label>
          <textarea class="sui-form-control"
                    name="sh_feature_policy_urls"
                    v-model="model.sh_feature_policy_urls"
                    :placeholder="__('Place URLs here, one per line')"></textarea>
          <span class="sui-description" v-html="tabUrlsText"></span>
        </div>
      </template>
      <template v-slot:none>
        <p class="sui-p-small">
          {{ __("The feature is disabled in top-level and nested browsing contexts.") }}
        </p>
      </template>
    </sidetab>
  </div>
</template>
<script>
import helper from '../../../helper/base_hepler';
import Sidetab from "../../../component/sidetab";

export default {
  components: {Sidetab},
  mixins: [helper],
  props: ['misc', 'model'],
  name: "sh-feature-policy",
  data: function () {
    return {
      state: {
        on_saving: false
      },
      mode: this.misc.mode,
      values: this.misc.values,
      tabUrlsText: ''
    }
  },
  created: function () {
    this.tabUrlsText = vsprintf(this.__('The feature is allowed for specific origins. Place URLs here %s, one per line.'), '<strong>https://example.com</strong>');
  }
}
</script>