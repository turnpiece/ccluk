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
                        <li :class="{current:view==='mask-login'}" class="sui-vertical-tab">
                            <a @click.prevent="view='mask-login'" data-tab="notfound_lockout"
                               href="#mask-login">{{__("Mask Login Area")}}</a>
                        </li>
                        <li :class="{current:view==='security-headers'}" class="sui-vertical-tab">
                            <a @click.prevent="view='security-headers'" role="button"
                               href="#">{{__("Security Headers")}}</a>
                        </li>
                    </ul>
                    <div class="sui-sidenav-hide-lg">
                        <select class="sui-mobile-nav">
                            <option value="mask-login" :selected="view==='mask-login'">{{__("Mask Login Area")}}</option>
                            <option value="security-headers" :selected="view==='security-headers'">{{__("Security Headers")}}</option>
                        </select>
                    </div>
                </div>
                <mask-login v-show="view==='mask-login'"></mask-login>
                <security-headers v-show="view==='security-headers'"></security-headers>
            </div>
        </div>
        <app-footer></app-footer>
    </div>
</template>

<script>
    import base_helper from '../../helper/base_hepler';
    import mask_login from './screen/mask-login';
    import security_headers from './screen/security-headers';

    export default {
        mixins: [base_helper],
        components: {
            'mask-login': mask_login,
            'security-headers': security_headers
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
                view = 'mask-login'
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