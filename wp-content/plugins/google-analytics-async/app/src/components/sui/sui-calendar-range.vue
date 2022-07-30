<template>
	<!-- This component will be hidden for screenreader until we work on its accessibility. -->
	<div
		role="button"
		class="beehive-range-picker"
		aria-hidden="true"
		ref="selector"
		:id="id"
	>
		<span class="sui-icon-calendar sui-sm" aria-hidden="true"></span>
		<span class="beehive-range-picker-value">{{ label }}</span>
		<span class="sui-icon-chevron-down sui-sm beehive-range-picker-button" aria-hidden="true"></span>
	</div>
</template>

<script>
import $ from 'jquery'
import daterangepicker from 'daterangepicker'

export default {
	name: 'CalendarRange',

	props: {
		id: {
			type: String,
			default: null,
		},
		periods: {
			type: Object,
			required: true,
		},
		startDate: {
			type: String,
			required: true,
		},
		endDate: {
			type: String,
			required: true,
		},
		selectedLabel: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			label: this.selectedLabel,
		}
	},

	mounted() {
		const vm = this
		const select = $(this.$refs.selector)

		// Init date range picker.
		select
			.daterangepicker(
				{
					autoApply: true,
					startDate: vm.startDate,
					endDate: vm.endDate,
					ranges: vm.periods,
					alwaysShowCalendars: false,
					opens: 'left',
					drops: 'down',
					locale: {
						format: 'YYYY-MM-DD',
					},
				},
				(start, end, label) => {
					// If already found in periods.
					if (vm.periods.hasOwnProperty(label)) {
						vm.label = label
					} else {
						// If start and end dates are same.
						if (
							start.format('MMM D, YYYY') ===
							end.format('MMM D, YYYY')
						) {
							vm.label = end.format('MMM D, YYYY')
						} else {
							// Custom format for the label.
							vm.label =
								start.format('MMM D, YYYY') +
								' - ' +
								end.format('MMM D, YYYY')
						}
					}
				}
			)
			.on('show.daterangepicker', (ev, picker) => {
				picker.container
					.addClass('beehive-range-calendar')
					.css({
						'min-width': picker.element.outerWidth(),
					})
					.attr('aria-hidden', true)
			})
			.on('hide.daterangepicker', (ev, picker) => {
				picker.container
					.removeClass('beehive-range-calendar')
					.css({
						'min-width': '',
					})
					.attr('aria-hidden', true)
			})
			.on('apply.daterangepicker', (ev, picker) => {
				// Emit new event on date period change.
				vm.$emit('periodChange', {
					startDate: picker.startDate.format('YYYY-MM-DD'),
					endDate: picker.endDate.format('YYYY-MM-DD'),
					selected: picker.chosenLabel,
				})
			})
	},
}
</script>
