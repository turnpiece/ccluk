<template>
  <div class="sui-toggle-content">
        <span class="sui-description toogle-content-description">
            {{ __("Choose what level of protection X-XSS protection you would like to apply when XSS attacks are detected.") }}
        </span>
    <sidetab slug="sh_xss_protection_mode" @selected="model.sh_xss_protection_mode = $event" :active="model.sh_xss_protection_mode" :labels="[
        {
          text:__('Sanitize'),
          mute:false,
          value:'sanitize'
        },
        {
          text:__('Block'),
          mute:false,
          value:'block'
        },
    ]">
      <template v-slot:sanitize>
        <p class="sui-p-small">
          {{ __("If a cross-site scripting attack is detected, the browser will sanitize the page (remove the unsafe parts).") }}
        </p>
      </template>
      <template v-slot:block>
        <span class="sui-description">
          {{ __("Enables XSS filtering. Rather than sanitizing the page, the browser will prevent rendering of the page if an attack is detected.") }}
        </span>
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
  name: "sh_xss_protection",
  data: function () {
    return {
      state: {
        on_saving: false
      },
      mode: this.misc.mode,
    }
  }
}
</script>