<template>
    <div class="sui-box sui-summary" :style="rebrand_img" :class="[summary_class, css_class]">
        <div class="sui-summary-image-space" aria-hidden="true"></div>
        <slot></slot>
    </div>
</template>

<script>
    import base_helper from '../helper/base_hepler'

    export default {
        mixins: [base_helper],
        props: ['css-class'],
        name: "summary-box",
        data: function () {
            return {
                whitelabel: defender.whitelabel
            }
        },
        computed: {
            summary_class: function () {
                return {
                    'sui-unbranded': this.whitelabel.hide_branding === true && this.whitelabel.hero_image.length === 0,
                    'sui-rebranded': this.whitelabel.hide_branding === true && this.whitelabel.hero_image.length > 0,
                }
            },
            css_class: function () {
                return this.cssClass
            },
            rebrand_img: function () {
                if (this.whitelabel.hero_image.length > 0) {
                    return {
                        'background-image': "url('" + this.whitelabel.hero_image + "')"
                    }
                }
            }
        }
    }
</script>
