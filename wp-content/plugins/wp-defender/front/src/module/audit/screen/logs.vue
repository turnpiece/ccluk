<template>
	<div class="sui-box">
		<div class="sui-box-header">
			<h3 class="sui-box-title">
				{{__("Event Logs")}}
			</h3>
			<div class="sui-actions-right">
				<a :href="get_export_url" class="sui-button sui-button-ghost">
					<i class="sui-icon-upload-cloud" aria-hidden="true"></i>
					{{__("Export CSV")}}
				</a>
			</div>
		</div>
		<div class="sui-box-body">
			<p>
				{{__("Here are your latest event logs showing what's been happening behind the scenes.")}}
			</p>
			<div class="sui-row">
				<div class="sui-col-md-5">
					<div class="inline-form">
						<label>{{__("Date range")}}</label>
						<div class="sui-date">
							<i class="sui-icon-calendar" aria-hidden="true"></i>
							<input id="date-range-picker" name="date_from" type="text" class="sui-form-control"
							       :value="filter.date_range">
						</div>
					</div>
				</div>
				<div class="sui-col-md-7">
					<div class="sui-pagination-wrap">
						<span class="sui-pagination-results" v-text="get_count"></span>
						<pagination v-if="data.total_items > 0" :page-count="data.total_pages"
						            :click-handler="paging"
						            :prev-text="prev_icon"
						            :next-text="next_icon"
						            :value="data.paged"
						            :container-class="'sui-pagination'">
						</pagination>
						<button @click="filter.is_open=!filter.is_open"
						        class="sui-button-icon sui-button-outlined sui-tooltip" data-tooltip="Filter">
							<i class="sui-icon-filter" aria-hidden="true"></i>
							<span class="sui-screen-reader-text">Open search filter</span>
						</button>
					</div>
				</div>
			</div>
			<div class="sui-pagination-filter" :class="{'sui-open':filter.is_open}">
				<div class="sui-row">
					<div class="sui-col-md-4">
						<div class="sui-form-field">
							<label class="sui-label">{{__("Username")}}</label>
							<div class="sui-control-with-icon sui-right-icon">
								<input type="text" v-model="filter.username" class="sui-form-control"/>
								<i class="sui-icon-magnifying-glass-search" aria-hidden="true"></i>
							</div>
						</div>
					</div>
					<div class="sui-col-md-3">
						<div class="sui-form-field">
							<label class="sui-label">{{__("IP Address")}}</label>
							<input type="text" data-name="ip" v-model="filter.ip_address"
							       placeholder="E.g. 192.168.1.1"
							       class="sui-form-control"/>
						</div>
					</div>
				</div>
				<div class="sui-row">
					<div class="sui-col">
						<div class="sui-form-field">
							<div class="sui-side-tabs">
								<div class="sui-tabs-menu">
									<label for="event_filter_all"
									       :class="{active:filter.event_all===true}"
									       class="sui-tab-item">
										<input type="radio" :value="true" id="event_filter_all"
										       data-tab-menu="" v-model="filter.event_all">
										{{__("All")}}
									</label>
									<label for="event_filter"
									       :class="{active:filter.event_all===false}"
									       class="sui-tab-item">
										<input type="radio" :value="false" data-tab-menu="events-box"
										       id="event_filter" v-model="filter.event_all">
										{{__("Specific")}}
									</label>
								</div>

								<div class="sui-tabs-content">
									<div class="sui-tab-content sui-tab-boxed"
									     :class="{active:filter.event_all===false}"
									     id="events-box"
									     data-tab-content="events-box">
										<div class="sui-row">
											<label v-for="event in event_types" :for="'chk_'+event"
											       class="sui-checkbox">
												<input :id="'chk_'+event" type="checkbox"
												       class="filterable" v-model="filter.events"
												       :value="event">
												<span aria-hidden="true"></span>
												<span>{{event}}</span>
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<hr/>
				<div class="float-r">
					<button type="submit" @click="do_filter" class="sui-button sui-button-blue">
						{{__("Apply")}}
					</button>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="sui-accordion sui-accordion-flushed no-border-top">
			<div class="sui-accordion-header">
				<div>{{__("Event summary")}}</div>
				<div>{{__("Date")}}</div>
				<div></div>
			</div>
			<div class="sui-accordion-item sui-default" v-for="item in get_logs">
				<div class="sui-accordion-item-header">
					<div class="sui-accordion-item-title" v-text="xss(item.msg)"></div>
					<div v-html="format_time(item.timestamp)"></div>
					<div>
						<button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item"><i
								class="sui-icon-chevron-down" aria-hidden="true"></i></button>
					</div>
				</div>
				<div class="sui-accordion-item-body">
					<div class="sui-box">
						<div class="sui-box-body">
							<strong>{{__("Description")}}</strong>
							<p v-text="item.msg"></p>
							<div class="sui-row">
								<div class="sui-col">
									<strong class="block">{{__("Context")}}</strong>
									<a class="block" :href="build_filter_url(item.context)"
									   v-text="xss(item.context)"></a>
								</div>
								<div class="sui-col">
									<strong class="block">{{__("Type")}}</strong>
									<a class="block" :href="build_filter_url(item.event_type)"
									   v-text="xss(item.event_type)"></a>
								</div>
								<div class="sui-col">
									<strong class="block">{{__("Ip Address")}}</strong>
									<a class="block" :href="build_filter_url(item.ip)"
									   v-text="xss(item.ip)"></a>
								</div>
								<div class="sui-col">
									<strong class="block">{{__("User")}}</strong>
									<a class="block" :href="build_filter_url(item.user)" v-text="xss(item.user)"></a>
								</div>
								<div class="sui-col">
									<strong class="block">{{__("Date / Time")}}</strong>
									<a class="block" :href="build_filter_url(item.timestamp)">
										{{ new Date(item.timestamp * 1000) | moment(misc.date_format) }}
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="sui-row" v-if="data.chunks.length === 0">
			<div class="sui-col">
				<div class="sui-notice">
					<p v-if="state.is_fetching === true">
						{{__("Loading events...")}}
					</p>
					<p v-else>
						{{__("There have been no events logged in the selected time period.")}}
					</p>
				</div>
			</div>
		</div>
		<div class="sui-center-box">
			<div class="sui-pagination-wrap">
				<pagination v-if="data.total_items > 0" :page-count="data.total_pages"
				            :click-handler="paging"
				            :prev-text="prev_icon"
				            :next-text="next_icon"
				            :value="data.paged"
				            :container-class="'sui-pagination'">
				</pagination>
			</div>
		</div>
		<overlay v-if="state.is_fetching"></overlay>
	</div>
</template>

<script>
	import base_hepler from "../../../helper/base_hepler";
	import pagination from '../../../component/pagination';
	import {chunk} from 'lodash';
	import * as moment from 'moment'

	export default {
		mixins: [base_hepler],
		name: "logs",
		data: function () {
			return {
				filter: {
					date_range: null,
					username: '',
					ip_address: '',
					events: [],
					event_all: true,
					is_open: false,
					date_from: null,
					date_to: null
				},
				event_types: auditData.filters.types,
				data: {
					logs: [],
					chunks: [],
					total_items: 0,
					total_pages: 0,
					paged: 1
				},
				misc: auditData.misc,
				endpoints: auditData.endpoints,
				nonces: auditData.nonces,
				state: {
					on_saving: false,
					is_fetching: false
				},
			}
		},
		methods: {
			date_range: function () {

			},
			build_filter_url(value) {

			},
			paging: function (num) {
				this.data.paged = num;
			},
			/**
			 * Filtering the data, since we have the logs with the date-range cached, we can do it on client side
			 * If the date range changed, then we have to fetch new data from src
			 */
			do_filter: function () {
				//can filter by client
				let self = this;
				let filteredData = this.data.logs.filter(function (item) {
					if (self.filter.username !== ''
						&& item.user.indexOf(self.filter.username) === -1) {
						//we have the username but this one doesn't what we want
						return false;
					}

					if (self.filter.ip_address !== null
						&& item.ip.indexOf(self.filter.ip_address) === -1) {
						return false;
					}

					if (self.filter.event_all === false
						&& self.filter.events.indexOf(item.event_type) === -1) {
						return false;
					}

					return true;
				})
				self.data.chunks = chunk(filteredData, 40);
				self.data.total_items = filteredData.length;
				self.data.total_pages = Math.ceil(self.data.total_items / 40);
				self.data.paged = 1;
			},
			fetch_data: function (callback) {
				let self = this;
				this.state.is_fetching = true;
				let filter = JSON.parse(JSON.stringify(this.filter));
				delete filter.is_open;
				delete filter.event_all;
				delete filter.date_range;
				this.httpGetRequest('loadData', filter, function (response) {
					if (response.success === true) {
						self.data.logs = Object.values(response.data.logs);
						self.data.total_items = response.data.total_items;
						self.data.total_pages = response.data.total_pages;
						self.data.chunks = chunk(self.data.logs, 40);
						self.data.paged = 1;
						self.state.is_fetching = false;
						if (callback !== undefined) {
							callback();
						}
					} else {
						Defender.showNotification('error', response.message)
					}
				}, false)
			},
			format_time: function (timestamp) {
				if (Array.isArray(timestamp)) {
					return this.$options.filters.moment(new Date(timestamp[1] * 1000), this.misc.date_format);
				} else {
					return this.$options.filters.moment(new Date(timestamp * 1000), this.misc.date_format);
				}
			}
		},
		computed: {
			get_logs: function () {
				let logs = [];
				if (this.data.chunks.length > 0 && this.data.chunks[this.data.paged - 1] !== undefined) {
					logs = this.data.chunks[this.data.paged - 1];
				}
				return logs;
			},
			get_count: function () {
				return this.vsprintf(this.__("%s results"), this.data.total_items)
			},
			next_icon: function () {
				return '<i class="sui-icon-chevron-right" aria-hidden="true"></i>';
			},
			prev_icon: function () {
				return '<i class="sui-icon-chevron-left" aria-hidden="true"></i>';
			},
			min_date: function () {
				return moment().format();
			},
			max_date: function () {
				return moment().subtract(30, 'days').format()
			},
			get_export_url: function () {
				let url = ajaxurl + '?action=' + this.endpoints.exportAsCvs + '&_wpnonce=' + this.nonces.exportAsCvs;
				url += '&date_from=' + this.filter.date_from;
				url += '&date_to=' + this.filter.date_to;
				this.filter.events.forEach(function (value) {
					url += '&event_type[]=' + value;
				})
				url += '&term=' + this.filter.username;
				url += '&ip=' + this.filter.ip_address
				return url;
			}
		},
		watch: {
			'filter.date_range': function (value, old) {
				if (value !== null && old !== null && value !== old) {
					//need to check the old as if it is null, then the page just loaded
					this.fetch_data();
				}
			}
		},
		components: {
			'pagination': pagination,
		},
		created: function () {
			let urlParams = new URLSearchParams(window.location.search);
			let date_from = urlParams.get('date_from') !== null ? urlParams.get('date_from') : moment().subtract(7, 'day').format('MM/DD/YYYY')
			let date_to = urlParams.get('date_to') !== null ? urlParams.get('date_to') : moment().format('MM/DD/YYYY');
			this.filter.date_range = date_from + ' - ' + date_to;
			this.filter.date_from = date_from;
			this.filter.date_to = date_to;
			let self = this;
			this.fetch_data(function () {
				//the default range is 7 days, so we can use the first logs for summary count
				self.$parent.$emit('events_in_7_days', self.data.logs.length)
			});
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
					self.filter.date_range = picker.startDate.format('MM/DD/YYYY') + '-' + picker.endDate.format('MM/DD/YYYY');
					self.filter.date_from = picker.startDate.format('MM/DD/YYYY');
					self.filter.date_to = picker.endDate.format('MM/DD/YYYY');
				});
			})
		}
	}
</script>