<template>
  <div class="sui-toggle-content">
		<span class="sui-description toogle-content-description">
			{{ __('Choose whether or not you want to allow your webpages to be embedded inside iframes.') }}
		</span>
    <sidetab :active="model.sh_xframe_mode" slug="sh_xframe_mode" :labels="[
        {
          text:__('Sameorigin'),
          value:'sameorigin',
          mute:false
        },
        {
          text:__('Allow-from'),
          value:'allow-from',
          mute:false
        },
        {
          text:__('Deny'),
          value:'deny',
          mute:false
        },
    ]" @selected="model.sh_xframe_mode = $event">
      <template v-slot:sameorigin>
        <p class="sui-p-small">
          {{ __("The page can only be displayed in a frame on the same origin as the page itself. The spec leaves it up to browser vendors to decide whether this option applies to the top level, the parent, or the whole chain.") }}
        </p>
      </template>
      <template v-slot:allow-from>
        <div class="sui-form-field">
          <label class="sui-label">{{ __("Allow from URLs") }}</label>
          <textarea class="sui-form-control"
                    name="sh_xframe_urls"
                    v-model="model.sh_xframe_urls"
                    :placeholder="__('Place allowed page URLs, one per line')"></textarea>
          <span class="sui-description" v-html="tabUrlsText"></span>
        </div>
      </template>
      <template v-slot:deny>
        <p class="sui-p-small">
          {{ __("The page can’t be displayed in a frame, regardless of the site attempting to do so.") }}
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
  name: "sh-xframe",
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
    this.tabUrlsText = vsprintf(this.__('The page <strong>%s</strong> will only be displayed in a frame on the specified origin. One per line.'), this.siteUrl);
  }
}
</script>