<template>
    <div class="sui-wrap" :class="[high_contrast]">
        <div class="settings">
            <div class="sui-header">
                <h1 class="sui-header-title">
                    {{__("Settings")}}
                </h1>
                <doc-link link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#settings"></doc-link>
            </div>
            <div class="sui-row-with-sidenav">
                <div class="sui-sidenav">
                    <ul class="sui-vertical-tabs sui-sidenav-hide-md">
                        <li :class="{current:view==='general'}" class="sui-vertical-tab">
                            <a @click.prevent="view='general'" :href="adminUrl('admin.php?page=wdf-setting')">
                                {{__("General")}}
                            </a>
                        </li>
                        <li :class="{current:view==='data'}" class="sui-vertical-tab">
                            <a @click.prevent="view='data'" :href="adminUrl('admin.php?page=wdf-setting&view=data')">
                                {{__("Data & Settings")}}
                            </a>
                        </li>
                        <li :class="{current:view==='accessibility'}" class="sui-vertical-tab">
                            <a @click.prevent="view='accessibility'"
                               :href="adminUrl('admin.php?page=wdf-setting&view=accessibility')">
                                {{__("Accessibility")}}
                            </a>
                        </li>
                    </ul>
                    <div class="sui-sidenav-hide-lg">
                        <select class="sui-mobile-nav">
                            <option value="general">{{__("General")}}</option>
                            <option value="data">{{__("Data & Settings")}}</option>
                            <option value="accessibility">{{__("Accessibility")}}</option>
                        </select>
                    </div>
                </div>
                <general v-show="view==='general'"></general>
                <data-settings v-show="view==='data'"></data-settings>
                <accessibility v-show="view==='accessibility'"></accessibility>
            </div>
        </div>
        <app-footer></app-footer>
    </div>
</template>

<script>
    import base_helper from '../../helper/base_hepler';
    import general from './screen/general';
    import data from './screen/data';
    import accessibility from './screen/accessibility'

    export default {
        mixins: [base_helper],
        name: "settings",
        data: function () {
            return {
                view: ''
            }
        },
        components: {
            general, 'data-settings': data, accessibility,
        },
        created: function () {
            //show the current page
            let urlParams = new URLSearchParams(window.location.search);
            let view = urlParams.get('view');
            if (view === null) {
                view = "general";
            }
            this.view = view;
        },
        watch: {
            'view': function (val, old) {
                history.replaceState({}, null, this.adminUrl() + "admin.php?page=wdf-setting&view=" + this.view);
            }
        },
        computed: {
            high_contrast: function () {
                return {'sui-color-accessible': this.$root.high_contrast === true};
            }
        },
        mounted: function () {
            self = this;
            jQuery('.sui-mobile-nav').change(function () {
                self.view = jQuery(this).val()
            })
        },
    }
</script>