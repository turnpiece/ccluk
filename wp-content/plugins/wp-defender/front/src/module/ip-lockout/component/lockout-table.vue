<template>
	<div>
		<div class="sui-box-body no-padding-bottom">
			<div class="sui-row">
				<div class="sui-col-md-5">
					<div class="inline-form">
						<label>{{__("Date range")}}</label>
						<div class="sui-date">
							<i class="sui-icon-calendar" aria-hidden="true"></i>
							<input id="date-range-picker" name="date_from" type="text" class="sui-form-control"
							       :value="dateRange">
						</div>
					</div>
				</div>
				<div class="sui-col">
					<div class="sui-pagination-wrap">
                    <span class="sui-pagination-results"
                          v-if="countAll > 0">{{vsprintf(__("%s results"),countAll)}}</span>
						<paginate v-if="countAll > 0" :page-count="totalPages"
						          :click-handler="paging"
						          :prev-text="prevIcon"
						          :next-text="nextIcon"
						          :container-class="'sui-pagination'">
						</paginate>
						<button v-on:click="state.show_filter = !state.show_filter"
						        class="sui-button-icon sui-button-outlined sui-pagination-open-filter">
							<i class="sui-icon-filter" aria-hidden="true"></i>
							<span class="sui-screen-reader-text">Open search filters</span>
						</button>
					</div>
				</div>
			</div>
			<div class="sui-row">
				<div class="sui-col-md-5">
					<form class="inline-form" @submit.prevent="bulkUpdate" method="post">
						<label class="sui-checkbox apply-all">
							<input type="checkbox" @change="bulkSelect" :true-value="true" :false-value="false"
							       v-model="allSelected"/>
							<span aria-hidden="true"></span>
						</label>
						<select id="bulk-action" class="sui-select-sm">
							<option value="">{{__("Bulk action")}}</option>
							<option value="ban">{{__("Ban")}}</option>
							<option value="whitelist">{{__("Whitelist")}}</option>
							<option value="delete">{{__("Delete")}}</option>
						</select>
						<button type="submit" class="sui-button" :class="{'sui-button-onload':state.on_saving}">
                            <span class="sui-loading-text">
                            {{__("Bulk Update")}}
                            </span>
							<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
						</button>
					</form>
				</div>
			</div>
			<div class="sui-pagination-filter" :class="{'sui-open':state.show_filter}">
				<div class="sui-row">
					<div class="sui-col">
						<div class="sui-form-field">
							<label class="sui-label">
								{{__("Lockout Type")}}
							</label>
							<select id="filter_type" name="type">
								<option value="">{{__("All")}}</option>
								<option value="auth_fail">{{__("Failed login attempts")}}</option>
								<option value="auth_lock">{{__("Login lockout")}}</option>
								<option value="404_error">{{__("404 error")}}</option>
								<option value="404_lockout">{{__("404 lockout")}}</option>
							</select>
						</div>
					</div>
					<div class="sui-col">
						<div class="sui-form-field">
							<label class="sui-label">
								{{__("IP Address")}}
							</label>
							<input v-model="filters.ip" type="text" class="sui-form-control"
							       placeholder="Enter an IP address">
						</div>
					</div>
					<div class="sui-col"></div>
				</div>
				<hr/>
				<div class="sui-row">
					<div class="sui-col">

					</div>
					<div class="sui-col">
						<button type="button" v-on:click="doFilter" :class="{'sui-button-onload':state.on_saving}"
						        :disabled="state.on_saving"
						        class="sui-button float-r">
                                    <span class="sui-loading-text">
                                        {{__("Apply")}}
                                    </span>
							<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
						</button>
					</div>
				</div>
			</div>
		</div>
		<div class="sui-box-body" v-if="state.show_empty_logs_text">
			<table class="sui-table no-border margin-bottom-20">
				<tr>
					<td>
						<div class="sui-notice">
							<p>
								{{__("No lockout events have been logged within the selected time period.")}}
							</p>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div class="sui-box-body" v-if="state.show_init_text">
			<table class="sui-table no-border margin-bottom-20">
				<tr>
					<td>
						<div class="sui-notice">
							<p>
								{{__("Loading logs....")}}
							</p>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div v-show="state.show_init_text===false && state.show_empty_logs_text===false">
			<overlay v-if="state.querying"></overlay>
			<table :id=id class="sui-table sui-accordion no-border">
				<thead>
				<tr>
					<th v-for="value in headers">
						{{value}}
					</th>
				</tr>
				</thead>

				<tbody>
				<template v-for="(log,index) in logs">
					<tr class="sui-accordion-item" :class="logClass(log)">
						<td class="sui-table-item-title">
							<label class="sui-checkbox">
								<input type="checkbox" class="single-select" v-model="ids"
								       :value="log.id"/>
								<span aria-hidden="true"></span>
							</label>
							<span class="badge" :class="badgeClass(log)">{{badgeText(log)}}</span>
							{{log.log}}
						</td>
						<td>
							{{log.date}}
						</td>
						<td>
							<button class="sui-button-icon sui-accordion-open-indicator">
								<i class="sui-icon-chevron-down" aria-hidden="true"></i>
							</button>
						</td>
					</tr>
					<tr class="sui-accordion-item-content">
						<td colSpan="3">
							<div class="sui-box">
								<div class="sui-box-body margin-bottom-30">
									<div class="sui-row">
										<div class="sui-col">
											<p><strong>{{__('Description')}}</strong></p>
											<p>{{log.log}}</p>
										</div>
										<div class="sui-col">
											<p><strong>{{__('Type')}}</strong></p>
											<p>
												<a href="" v-text="eventType(log.type)">
												</a>
											</p>
										</div>
									</div>
									<div class="sui-row">
										<div class="sui-col">
											<p><strong>{{__('IP Address')}}</strong></p>
											<p><a href="">{{log.ip}}</a></p>
										</div>
										<div class="sui-col">
											<p><strong>{{__('Date/Time')}}</strong></p>
											<p>{{log.date}}</p>
										</div>
										<div class="sui-col">
											<p><strong>{{__('Ban Status')}}</strong></p>
											<p>{{log.statusText}}</p>
										</div>
									</div>
									<div class="sui-border-frame">
										<button @click="addIpToList(log.ip,'whitelist',index)"
										        :class="{'sui-button-onload':state.on_saving}"
										        v-if="log.ip_status==='na'"
										        type="button" class="sui-button sui-button-ghost">
                                            <span class="sui-loading-text">
                                                <i class="sui-icon-check-tick" aria-hidden="true"></i>
                                                {{__("Add whitelist")}}
                                            </span>
											<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
										</button>
										<button @click="addIpToList(log.ip,'unwhitelist',index)"
										        :class="{'sui-button-onload':state.on_saving}"
										        v-if="log.ip_status==='whitelist'" type="button"
										        class="sui-button sui-button-ghost">
                                            <span class="sui-loading-text">
                                            {{__("Unwhitelist")}}
                                            </span>
											<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
										</button>
										<button @click="addIpToList(log.ip,'blacklist',index)"
										        :class="{'sui-button-onload':state.on_saving}"
										        v-if="log.is_mine===false && log.ip_status==='na'" type="button"
										        class="sui-button sui-button-red">
                                            <span class="sui-loading-text">
                                            <i class="sui-icon-cross-close" aria-hidden="true"></i>
                                            {{__("Ban IP")}}
                                            </span>
											<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
										</button>
										<button @click="addIpToList(log.ip,'unblacklist',index)"
										        :class="{'sui-button-onload':state.on_saving}"
										        v-if="log.ip_status==='blacklist'" type="button"
										        class="sui-button sui-button-blue">
                                            <span class="sui-loading-text">
                                            {{__("Unban IP")}}
                                            </span>
											<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
										</button>
										<p>
											{{__( "Note: Make sure this IP is not a legitimate operation, banning the IP will result in being permanently locked out from accessing your website.")}}
										</p>
									</div>
								</div>
							</div>
						</td>
					</tr>
				</template>
				</tbody>
			</table>
		</div>
	</div>
</template>
<script>
	import base_helper from '../../../helper/base_hepler';
	import Paginate from '../../../component/pagination.vue'

	export default {
		mixins: [base_helper],
		props: ['id', 'headers', 'table', 'sort'],
		data: function () {
			return {
				logs: [],
				cached: [],
				countAll: 0,
				totalPages: 0,
				ranges: {
					'Today': [moment(), moment()],
					'7 Days': [moment().subtract(6, 'days'), moment()],
					'30 Days': [moment().subtract(29, 'days'), moment()]
				},
				nonces: iplockout.nonces,
				endpoints: iplockout.endpoints,
				filters: {
					date: this.table.date_from + '-' + this.table.date_to,
					paged: 1,
					type: '',
					ip: ''
				},
				state: {
					show_filter: false,
					on_saving: false,
					on_querying: false,
					show_empty_logs_text: false,
					show_init_text: true,
					querying: false
				},
				ids: [],
				allSelected: false
			}
		},
		components: {
			paginate: Paginate
		},
		methods: {
			paging: function (num) {
				this.filters.paged = num;
			},
			clearFilter: function () {
				this.filters.type = '';
				this.filters.ip = '';
			},
			doFilter: function () {
				this.state.on_saving = true;
				let date = this.filters['date'].split('-');
				this.filters.date_from = date[0];
				this.filters.date_to = date[1];
				this._queryLogs(this.filters, 1);
			},
			bulkSelect: function () {
				if (this.allSelected === false) {
					this.ids = [];
				} else {
					for (let i in this.logs) {
						if (this.ids.indexOf(this.logs[i].id) === -1) {
							this.ids.push(this.logs[i].id);
						}
					}
				}
			},
			addIpToList: function (ip, list, index) {
				let self = this;
				self.state.on_saving = true;
				this.httpPostRequest('toggleIpAction', {
					ip: ip,
					type: list
				}, function () {
					self.state.on_saving = false;
					let status = '';
					if (list === 'unblacklist' || list === 'unwhitelist') {
						status = 'na';
					} else {
						status = list;
					}
					self.logs[index].ip_status = status;
				})

			},
			bulkUpdate: function () {
				let value = jQuery('#bulk-action').val();
				let self = this;
				self.state.on_saving = true;
				this.httpPostRequest('bulkAction', {
					type: value,
					ids: self.ids
				}, function () {
					self.state.on_saving = false;
					if (value === 'delete') {
						self._queryLogs(self.filters, self.filters.paged)
					}
				});
			},
			_queryLogs: function (filters, paged) {
				var self = this;

				let date = this.filters['date'].split('-');
				filters.date_from = date[0];
				filters.date_to = date[1];
				filters.paged = paged;
				self.state.querying = true;
				return this.httpPostRequest('queryLogs', filters, function (response) {
					let data = response.data;
					self.state.on_saving = false;
					self.logs = data.logs;
					self.countAll = data.countAll;
					self.totalPages = data.totalPages;
					self.state.show_init_text = false;
					self.state.querying = false;
					if (self.logs.length === 0) {
						self.state.show_empty_logs_text = true;
					} else {
						self.state.show_empty_logs_text = false;
					}
				}, true);
			}
		},
		watch: {
			'filters.date': {
				handler() {
					this._queryLogs(this.filters, this.filters.paged);
				},
				//deep: true
			},
			'filters.paged': function () {
				this._queryLogs(this.filters, this.filters.paged);
			},
			'sort': function (val) {
				let filter;
				if (val === 'latest') filter = {'orbder': 'DESC', 'orderBy': 'date'}
				if (val === 'oldest') filter = {'order': 'ASC', 'orderBy': 'date'}
				if (val === 'ip') filter = {'order': 'DESC', 'orderBy': 'ip'}
				this._queryLogs(filter, 1)
			}
		},
		computed: {
			dateRange: function () {
				return this.filters.date;
			},
			nextIcon: function () {
				return '<i class="sui-icon-chevron-right" aria-hidden="true"></i>';
			},
			prevIcon: function () {
				return '<i class="sui-icon-chevron-left" aria-hidden="true"></i>';
			},
			badgeClass: function () {
				return (log) => {
					let ret = '';

					if (log.type === 'auth_lock' || log.type === '404_lockout' || log.type === '404_lockout_ignore') ret = 'locked';
					if (log.type === 'auth_lock' || log.type === 'auth_fail') ret += ' login';
					if (log.type === '404_error' || log.type === '404_lockout' || log.type === '404_lockout_ignore') ret += ' 404';
					return ret;
				}
			},
			badgeText: function () {
				return (log) => {
					if (log.type === 'auth_lock' || log.type === 'auth_fail') {
						return 'login';
					} else {
						return '404';
					}
				}
			},
			eventType: function () {
				return (log) => {
					if (log !== undefined) {
						if (log.indexOf('404') > -1) {
							return this.__('404 error');
						}
						return this.__('Login failed')
					}
				}
			},
			logClass: function () {
				return (log) => {
					if (log !== undefined) {
						if (log.type === '404_error' || log.type === 'auth_fail') {
							return 'sui-warning';
						}
						return 'sui-error';
					}
				}
			}
		},
		created: function () {
			var self = this;
			this._queryLogs({
				'date_from': self.table.date_from,
				'date_to': self.table.date_to
			}, 1);
		},
		mounted() {
			var self = this;
			let template = '<div class="daterangepicker wd-calendar">' +
				'<div class="ranges"></div>' +
				'<div class="drp-calendar left">' +
				'<div class="calendar-table"></div>' +
				'<div class="calendar-time"></div>' +
				'</div>' +
				'<div class="drp-calendar right">' +
				'<div class="calendar-table"></div>' +
				'<div class="calendar-time"></div>' +
				'</div>' +
				'</div>';
			this.$nextTick(() => {
				jQuery('#date-range-picker').daterangepicker({
					autoApply: true,
					maxDate: moment().format('MM/DD/YYYY'),
					minDate: moment().subtract(1, 'year').format('MM/DD/YYYY'),
					locale: {
						"format": "MM/DD/YYYY",
						"separator": "-"
					},
					ranges: {
						'Today': [moment(), moment()],
						'7 Days': [moment().subtract(6, 'days'), moment()],
						'30 Days': [moment().subtract(29, 'days'), moment()]
					},
					template: template,
					showCustomRangeLabel: false,
					alwaysShowCalendars: true,
				});
				jQuery('#date-range-picker').on('apply.daterangepicker', function (ev, picker) {
					self.filters.date = picker.startDate.format('MM/DD/YYYY') + '-' + picker.endDate.format('MM/DD/YYYY');
				});
				jQuery('#filter_type').change(function () {
					self.filters.type = jQuery(this).val()
				})
			})
		}
	}
</script>