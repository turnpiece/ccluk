<template>
    <div class="sui-dialog sui-dialog-sm locked-ips-dialog" aria-hidden="true" tabindex="-1" id="ips-modal">
        <div class="sui-dialog-overlay" data-a11y-dialog-hide></div>

        <div class="sui-dialog-content"
             role="dialog">

            <div class="sui-box" role="document">
                <div class="sui-box-header">
                    <h3 class="sui-box-title"
                        id="dialogTitle">{{__("Temporary IP Block List")}}</h3>
                    <div class="sui-actions-right">
                        <button type="button" data-a11y-dialog-hide class="sui-dialog-close"
                                aria-label="Close this dialog window"></button>
                    </div>
                </div>

                <div class="sui-box-body no-padding-bottom">
                    <p>
                        {{__("Here's a list of IP addresses that are currently temporarily blocked for bad behaviour. Select the IPs you want to unblock below.")}}
                    </p>
                </div>
                <div class="sui-box-selectors sui-box-selectors-col-1">
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
                                                        @click="ip_action(ip.ip,'unban', index)"
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
                                                IP <strong>{{ip.ip}}</strong> is unblocked
                                                <button type="button" data-tooltip="Undo"
                                                        @click="ip_action(ip.ip,'ban',index)"
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
                <div class="sui-box-body">
                    <div class="sui-pagination-wrap">
                        <span class="sui-tag">{{blacklist.ips_locked.length}} {{__("results")}}</span>
                        <ul class="sui-pagination">
                            <li>
                                <a href="#" @click.prevent="blacklist.paged-=1" class="prev"
                                   :disabled="blacklist.paged === 1 || state.ip_actioning===true"
                                   data-paged="1">
                                    <i class="sui-icon-chevron-left" aria-hidden="true"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" @click="blacklist.paged+=1" class="next"
                                   :disabled="blacklist.paged === Math.ceil(blacklist.ips_locked.length/20 ) || state.ip_actioning===true"
                                   data-paged="2">
                                    <i class="sui-icon-chevron-right" aria-hidden="true"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import base_helper from '../../../helper/base_hepler';
    import {chunk} from 'lodash';

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
                    count: 0
                },
                state: {
                    ip_actioning: [],
                    on_saving: false
                }
            }
        },
        methods: {
            query_locked_ips: function () {
                let self = this;
                this.httpPostRequest('queryLockedIps', {}, function (response) {
                    self.blacklist.ips_locked = Object.values(response.data.ips_locked);
                    self.blacklist.chunks = chunk(self.blacklist.ips_locked, 20);
                    self.blacklist.count = self.blacklist.ips_locked.length;
                    self.$emit('fetched', self.blacklist.ips_locked.length)
                }, true)
            },
            ip_action: function (ip, action, index) {
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
                        self.blacklist.ips_locked[index].status = status;
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
                    })
                    this.blacklist.chunks = chunk(filteredData, 20);

                }
                return this.blacklist.chunks[this.blacklist.paged - 1];
            }
        },
        created: function () {
            this.query_locked_ips();
        },
    }
</script>