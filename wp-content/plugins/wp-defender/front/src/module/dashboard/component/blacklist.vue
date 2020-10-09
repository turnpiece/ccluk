<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
                <i class="sui-icon-target" aria-hidden="true"></i>
                {{__("Blocklist Monitor")}}
            </h3>
            <div class="sui-actions-right" v-if="status==='blacklisted' || status === 'good'">
                <label class="sui-toggle">
                    <input type="checkbox" checked="checked" class="toggle-checkbox" @click="toggle">
                    <span class="sui-toggle-slider"></span>
                </label>
            </div>
        </div>
        <div class="sui-box-body">
            <p>
                {{__("Automatically check if you’re on Google’s blocklist every 6 hours. If something’s wrong, we’ll let you know via email.")}}
            </p>
            <div class="sui-notice sui-notice-info" v-if="status==='fetching'">
                <div class="sui-notice-content">
                    <div class="sui-notice-message">
                        <i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
                        <p>
                            {{__("Fetching your domain info...")}}
                        </p>
                    </div>
                </div>
            </div>
            <form method="post" class="margin-top-30" v-else-if="status==='new'">
                <submit-button type="button" css-class="sui-button-blue" :state="state" @click="toggle(true)">
                    {{__("Activate")}}
                </submit-button>
            </form>
            <div v-else-if="status==='blacklisted'" class="sui-notice sui-notice-error">
                <div class="sui-notice-content">
                    <div class="sui-notice-message">
                        <i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
                        <p>
                            {{__("Your domain is currently on Google’s blocklist. Check out the article below to find out how to fix up your domain.")}}
                        </p>
                    </div>
                </div>
            </div>
            <div v-else-if="status==='good'" class="sui-notice sui-notice-success">
                <div class="sui-notice-content">
                    <div class="sui-notice-message">
                        <i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
                        <p>
                            {{__("Your domain is currently clean.")}}
                        </p>
                    </div>
                </div>

            </div>
            <div class="sui-center-box no-padding-bottom" v-if="status!=='new'">
                <p class="sui-p-small">
                    {{__("Want to know more about blocklisting?")}} <a target="_blank"
                                                                       href="https://premium.wpmudev.org/blog/get-off-googles-blacklist/">{{__("Read this article.")}}</a>
                </p>
            </div>
        </div>
        <overlay v-show="state.on_saving===true"></overlay>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler'

    export default {
        mixins: [base_helper],
        name: "blacklist",
        data: function () {
            return {
                state: {
                    on_saving: false,
                },
                status: 'fetching',
                nonces: dashboard.blacklist.nonces,
                endpoints: dashboard.blacklist.endpoints
            }
        },
        methods: {
            toggle: function () {
                let self = this;
                this.httpGetRequest('toggleBlacklistWidget', {}, function (response) {
                    let status = parseInt(response.data.status)
                    switch (status) {
                        case -1:
                            self.status = 'new';
                            break;
                        case 0:
                            self.status = 'blacklisted';
                            break
                        case 1:
                            self.status = 'good';
                            break;
                    }
                })
            }
        },
        mounted: function () {
            let self = this;
            this.httpGetRequest('blacklistWidgetStatus', {}, function (response) {
                let status = parseInt(response.data.status)
                switch (status) {
                    case -1:
                        self.status = 'new';
                        break;
                    case 0:
                        self.status = 'blacklisted';
                        break
                    case 1:
                        self.status = 'good';
                        break;
                }
            })
        }
    }
</script>