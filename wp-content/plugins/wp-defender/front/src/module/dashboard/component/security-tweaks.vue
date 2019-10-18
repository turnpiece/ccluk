<template>
    <div class="sui-box hardener-widget">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <i class="sui-icon-wrench-tool" aria-hidden="true"></i>
                {{__("Security Tweaks")}}
            </h3>
            <div class="sui-actions-left" v-if="count > 0">
                <div class="sui-tag sui-tag-warning" v-text="count">
                </div>
            </div>
        </div>
        <div class="sui-box-body">
            <p>
                {{__("Defender checks for basic security tweaks you can make to enhance your website’s defense against hackers and bots.")}}
            </p>
            <div v-if="count === 0" class="sui-notice sui-notice-success">
                <p>
                    {{__("You’ve actioned all of the recommended security tweaks.")}}
                </p>
            </div>
        </div>
        <div v-if="count > 0" class="sui-accordion sui-accordion-flushed no-border-bottom">
            <div v-for="rule in rules" class="sui-accordion-item sui-warning" @click="handleRedirect(rule)">
                <div class="sui-accordion-item-header">
                    <div class="sui-accordion-item-title">
                        <i aria-hidden="true" class="sui-icon-warning-alert sui-warning"></i>
                        {{rule.title}}
                        <div class="sui-actions-right">
                            <i class="sui-icon-chevron-right" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sui-box-footer">
            <div class="sui-actions-left">
                <a :href="adminUrl('admin.php?page=wdf-hardener')"
                   class="sui-button sui-button-ghost">
                    <i class="sui-icon-eye" aria-hidden="true"></i>
                    {{__("View All")}}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler'

    export default {
        mixins: [base_helper],
        name: "security-tweaks",
        data: function () {
            return {
                rules: dashboard.security_tweaks.rules,
                count: dashboard.security_tweaks.count.issues,
            }
        },
        methods: {
            handleRedirect: function (item) {
                window.location.href = this.adminUrl('admin.php?page=wdf-hardener#' + item.slug)
            }
        }
    }
</script>