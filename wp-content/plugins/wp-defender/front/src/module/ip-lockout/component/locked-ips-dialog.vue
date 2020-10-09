<template>
    <div class="sui-modal sui-modal-md">
        <div
                role="dialog"
                id="ips-modal"
                class="sui-modal-content locked-ips-dialog"
                aria-modal="true"
                aria-labelledby="ips-dialog-title"
                aria-describedby="ips-dialog-desc"
        >
            <div class="sui-box no-padding-bottom" role="document">
                <div class="sui-box-header">
                    <h3 class="sui-box-title"
                        id="ips-dialog-title">{{__("Temporary IP Block List")}}</h3>
                    <div class="sui-actions-right">
                        <button data-modal-close="" class="sui-button-icon"
                                aria-label="Close this dialog window">
                            <i class="sui-icon-close"></i>
                        </button>
                    </div>
                </div>

                <div class="sui-box-body">
                    <p id="ips-dialog-desc">
                        {{__("Here's a list of IP addresses that are currently temporarily blocked for bad behaviour. Select the IPs you want to unblock below.")}}
                    </p>
                </div>
                <div class="sui-box-selectors sui-box-selectors-col-1 no-margin-bottom no-margin-top">
                    <ul class="ul-ips">
                        <li>
                            <div class="sui-with-button sui-with-button-icon">
                                <input type="text" class="sui-form-control" v-model="blacklist.ip"
                                       :placeholder="__('Type IP Address')">
                                <button type="button" class="sui-button-icon">
                                    <i class="sui-icon-magnifying-glass-search"
                                       aria-hidden="true"></i>
                                </button>
                            </div>
                        </li>
                        <li v-for="(ip,index) in filtered_locked_ips">
                            <label class="sui-box-selector" v-if="ip.status==='blocked'">
                                <span>
                                    <i class="sui-icon-lock" aria-hidden="true"></i>
                                    {{ip.ip}}
                                    <button type="button" data-tooltip="Unblock"
                                            @click="ip_action(ip.ip,'unban')"
                                            :class="{'sui-button-onload':state.ip_actioning.indexOf(ip.ip)>-1}"
                                            class="sui-tooltip sui-button-icon">
                                        <span class="sui-loading-text" aria-hidden="true">
                                            <i class="sui-icon-unlock" aria-hidden="true"></i>
                                            <span class="sui-screen-reader-text">{{__("Unlock")}}</span>
                                        </span>
                                        <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                                    </button>
                                </span>
                            </label>
                            <label v-else class="sui-box-selector-selected">
                                <span>
                                    <i class="sui-icon-unlock" aria-hidden="true"></i>
                                    IP <strong>{{ip.ip}}</strong> {{__("is unblocked")}}
                                    <button type="button" data-tooltip="Undo"
                                            @click="ip_action(ip.ip,'ban')"
                                            :class="{'sui-button-onload':state.ip_actioning.indexOf(ip.ip)>-1}"
                                            class="sui-tooltip sui-button-icon">
                                        <span class="sui-loading-text" aria-hidden="true">
                                            <i class="sui-icon-undo" aria-hidden="true"></i>
                                            <span class="sui-screen-reader-text">{{__("Undo")}}</span>
                                        </span>
                                        <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                                    </button>
                                </span>
                            </label>
                        </li>
                    </ul>
                </div>
                <div v-if="blacklist.chunks.length > 0" class="sui-box-body">
                    <div class="sui-pagination-wrap">
                        <span class="sui-tag">{{blacklist.filtered_count}} {{__("results")}}</span>
                        <ul class="sui-pagination" v-if="blacklist.chunks.length > 1">
                            <li>
                                <a href="#" @click.prevent="blacklist.paged-=1" class="prev"
                                   :disabled="blacklist.paged === 1 || state.ip_actioning===true"
                                   data-paged="1">
                                    <i class="sui-icon-chevron-left" aria-hidden="true"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" @click="blacklist.paged+=1" class="next"
                                   :disabled="blacklist.paged === Math.ceil(blacklist.ips_locked.length / per_page ) || state.ip_actioning===true"
                                   data-paged="2">
                                    <i class="sui-icon-chevron-right" aria-hidden="true"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div v-else class="sui-box-body no-padding-top no-padding-bottom text-center">
                    <p v-html="this.no_ip_address" :style="{'marginTop': '20px'}"></p>
                    <img :src="assetUrl('assets/img/dashboard-blacklist.svg')"
                         class="sui-image sui-image-center" aria-hidden="true"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler';
    import {chunk, find} from 'lodash';

    export default {
        mixins: [base_helper],
        name: "locked-ips-dialog",
        data: function () {
            return {
                nonces: iplockout.nonces,
                endpoints: iplockout.endpoints,
                blacklist: {
                    ips_locked: [],
                    chunks: [],
                    ip: '',
                    paged: 1,
                    count: 0,
                    filtered_count: 0
                },
                state: {
                    ip_actioning: [],
                    on_saving: false
                },
                per_page: 20
            }
        },
        methods: {
            query_locked_ips: function () {
                let self = this;
                this.httpPostRequest('queryLockedIps', {}, function (response) {
                    self.blacklist.ips_locked = Object.values(response.data.ips_locked);
                    self.blacklist.chunks = chunk(self.blacklist.ips_locked, self.per_page);
                    self.blacklist.count = self.blacklist.ips_locked.length;
                    self.blacklist.filtered_count = self.blacklist.ips_locked.length;
                    self.$emit('fetched', self.blacklist.ips_locked.length)
                }, true)
            },
            ip_action: function (ip, action) {
                let self = this;
                this.state.ip_actioning.push(ip);
                this.httpPostRequest('ipAction', {
                    'ip': ip,
                    'behavior': action
                }, function (response) {
                    var i = self.state.ip_actioning.indexOf(ip);
                    if (i !== -1) self.state.ip_actioning.splice(i, 1);
                    if (response.success === true) {
                        let status = action === 'unban' ? 'normal' : 'blocked';
                        let blacklistedIp = find(self.blacklist.ips_locked, ['ip', ip]);
                        if (blacklistedIp) {
                            blacklistedIp.status = status;
                        }
                    }
                }, true)
            },
        },
        computed: {
            filtered_locked_ips: function () {
                if (this.blacklist.ip.length > 0) {
                    let ip = this.blacklist.ip;
                    let filteredData = this.blacklist.ips_locked.filter(function (item) {
                        return item.ip.indexOf(ip) > -1;
                    });
                    this.blacklist.filtered_count = filteredData.length;
                    this.blacklist.chunks = chunk(filteredData, this.per_page);
                }
                return this.blacklist.chunks[this.blacklist.paged - 1];
            },
            no_ip_address: function () {
                return this.vsprintf(this.__('Sorry, we couldn\'t find any IP Address matching "<strong>%s</strong>"'), this.blacklist.ip)
            }
        },
        created: function () {
            this.query_locked_ips();
        },
    }
</script>