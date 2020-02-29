<template>
    <div v-show="view==='ignored'" class="sui-box" data-tab="tweaks_ignored">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                {{__("Ignored")}}
            </h3>
            <div class="sui-actions-left">
                <span v-if="summary.ignore_count > 0" class="sui-tag">{{summary.ignore_count}}</span>
            </div>
        </div>
        <div class="sui-box-body">
            <p>
                {{__("You have chosen to ignore these fixes. You can restore and action them at any time.")}}
            </p>
            <div v-if="summary.ignore_count === 0" class="sui-notice">
                <p>
                    {{__("Well, turns out you haven't ignored anything yet - keep up the good fight!")}}
                </p>
            </div>
        </div>
        <div v-if="summary.ignore_count > 0" class="sui-accordion sui-accordion-flushed">
            <transition-group name="list" tag="div">
                <component v-for="tweak in ignored" :is="tweak.slug" :key="tweak.slug"
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
        name: "ignore",
        props: ['view', 'ignored', 'summary'],
        components: tweaks
    }
</script>