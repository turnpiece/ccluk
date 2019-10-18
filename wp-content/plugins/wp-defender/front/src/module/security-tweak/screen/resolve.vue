<template>
    <div v-show="view==='resolved'" class="sui-box" data-tab="tweaks_fixed">
        <div class="sui-box-header">
            <h2 class="sui-box-title">{{__("Resolved")}}</h2>
            <div class="sui-actions-left">
                <span v-if="summary.fixed_count > 0" class="sui-tag sui-tag-success">{{summary.fixed_count}}</span>
            </div>
        </div>
        <div class="sui-box-body">
            <p>
                {{__("Excellent work. The following vulnerabilities have been fixed.")}}
            </p>
        </div>
        <div v-if="summary.fixed_count > 0" class="sui-accordion sui-accordion-flushed">
            <transition-group name="list" tag="div">
                <component v-for="tweak in fixed" :is="tweak.slug" :key="tweak.slug"
                           :title="tweak.title" :status="tweak.status" :slug="tweak.slug"
                           :success-reason="tweak.successReason" :error-reason="tweak.errorReason"
                           :misc="tweak.misc"
                >
                </component>
            </transition-group>
        </div>
        <div class="clearfix"></div>
        <div class="padding-bottom-30"></div>
    </div>
</template>

<script>
    import helper from '../../../helper/base_hepler';
    import st_helper from '../helper/security-tweak-helper';
    import tweaks from '../helper/tweaks-list';

    export default {
        mixins: [helper, st_helper],
        name: "resolve",
        props: ['view', 'summary', 'fixed'],
        components: tweaks,
        updated: function () {
            jQuery('.sui-select').SUIselect2({
                dropdownCssClass: 'sui-select-dropdown'
            });
        }
    }
</script>