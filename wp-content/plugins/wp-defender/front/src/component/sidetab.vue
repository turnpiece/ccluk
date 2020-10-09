<template>
  <div class="sui-side-tabs">
    <div class="sui-tabs-menu">
      <label @click="$emit('selected',label.value)" :for="getId(label.value)" class="sui-tab-item"
             :class="getClass(label.value)"
             v-for="label in labels">
        <input type="radio" :name="slug" :value="label.value" :id="getId(label.value)"
               :data-tab-menu="getBoxId(label.key)">
        {{ label.text }}
      </label>
    </div>

    <div class="sui-tabs-content">
      <div v-if="label.mute !== true" class="sui-tab-content sui-tab-boxed" :id="getBoxId(label.key)"
           :class="getClass(label.value)"
           v-for="label in labels">
        <slot :name="label.value"></slot>
      </div>
      <slot name="shared"></slot>
    </div>
  </div>
</template>

<script>
export default {
  name: "sidetab",
  props: ['labels', 'slug', 'active'],
  methods: {
    getBoxId(value) {
      return this.slug + value + '_box';
    },
    getId(value) {
      return this.slug + value;
    },
    getClass(value) {
      if (this.active === value) {
        return 'active'
      }
    }
  }
}
</script>