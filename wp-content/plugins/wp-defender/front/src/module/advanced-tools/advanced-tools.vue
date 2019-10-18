<template>
    <div class="sui-wrap" :class="[maybeHighContrast()]">
        <div class="advanced-tools">
            <div class="sui-header">
                <h1 class="sui-header-title">{{__("Advanced Tools")}}</h1>
                <doc-link link="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#advanced-tools"></doc-link>
            </div>
            <div class="sui-row-with-sidenav">
                <div class="sui-sidenav">
                    <ul class="sui-vertical-tabs sui-sidenav-hide-md">
                        <li :class="{current:view==='two-factor-auth'}" class="sui-vertical-tab">
                            <a @click.prevent="view='two-factor-auth'" data-tab="login_lockout"
                               href="#2factor">{{__("Two-Factor Auth")}}</a>
                        </li>
                        <li :class="{current:view==='mask-login'}" class="sui-vertical-tab">
                            <a @click.prevent="view='mask-login'" data-tab="notfound_lockout"
                               href="#mask-login">{{__("Mask Login Area")}}</a>
                        </li>
                    </ul>
                    <div class="sui-sidenav-hide-lg">
                        <select class="sui-mobile-nav">
                            <option value="two-factor-auth">{{__("Two-Factor Auth")}}</option>
                            <option value="mask-login">{{__("Mask Login Area")}}</option>
                        </select>
                    </div>
                </div>
                <two-factors v-show="view==='two-factor-auth'"></two-factors>
                <mask-login v-show="view==='mask-login'"></mask-login>
            </div>
        </div>
        <app-footer></app-footer>
    </div>
</template>

<script>
    import base_helper from '../../helper/base_hepler';
    import two_factors from './screen/two-factors';
    import mask_login from './screen/mask-login';

    export default {
        mixins: [base_helper],
        components: {
            'two-factors': two_factors,
            'mask-login': mask_login,
        },
        data: function () {
            return {
                state: {
                    on_saving: false,
                },
                whitelabel: defender.whitelabel,
                is_free: defender.is_free,
                view: '',
            }
        },
        created: function () {
            //show the current page
            let urlParams = new URLSearchParams(window.location.search);
            let view = urlParams.get('view');
            if (view === null) {
                view = "two-factor-auth";
            }
            this.view = view;
        },
        watch: {
            'view': function (val, old) {
                history.replaceState({}, null, this.adminUrl() + "admin.php?page=wdf-advanced-tools&view=" + this.view);
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