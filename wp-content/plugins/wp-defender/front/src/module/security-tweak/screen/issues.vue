<template>
    <div v-show="view==='issues'" class="sui-box" data-tab="tweaks_issue">
        <div class="sui-box-header">
            <h2 class="sui-box-title">{{__("Issues")}}</h2>
            <div class="sui-actions-left">
                <span v-if="summary.issues_count > 0" class="sui-tag sui-tag-warning">{{summary.issues_count}}</span>
            </div>
        </div>
        <div class="sui-box-body">
            <p>
                {{__("Activate security tweaks to strengthen your website against harmful hackers and bots who try to break in. We recommend you action as many tweaks as possible, some may require your server provider to help.")}}
            </p>
            <div v-if="summary.issues_count === 0" class="sui-notice sui-notice-success">
                <p>
                    {{__("You have actioned all available security tweaks, great work!")}}
                </p>
            </div>
        </div>
        <div v-if="summary.issues_count > 0" class="sui-accordion sui-accordion-flushed">
            <transition-group name="list" tag="div">
                <component v-for="tweak in issues" :is="tweak.slug" :key="tweak.slug"
                           :title="tweak.title" :status="tweak.status" :slug="tweak.slug"
                           :success-reason="tweak.successReason" :error-reason="tweak.errorReason"
                           :misc="tweak.misc"
                >
                </component>
            </transition-group>
        </div>
        <div class="clearfix"></div>
        <div v-if="summary.issues_count > 0" class="padding-bottom-30"></div>
    </div>
</template>

<script>
    import helper from '../../../helper/base_hepler';
    import st_helper from '../helper/security-tweak-helper';
    import tweaks from '../helper/tweaks-list';

    export default {
        mixins: [helper, st_helper],
        name: "issues",
        props: ['view', 'issues', 'summary'],
        components: tweaks,
        updated: function () {
            jQuery('.sui-select').SUIselect2({
                dropdownCssClass: 'sui-select-dropdown'
            });
        }
    }
</script>