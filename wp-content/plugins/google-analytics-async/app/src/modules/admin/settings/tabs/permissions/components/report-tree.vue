<template>
	<sui-tree
		:id="`beehive-permissions-tree-${type}-${role}`"
		:items="tree"
		:selected-items="getReports"
		:data="data"
		@itemSelect="handleChange"
	/>
</template>

<script>
import SuiTree from '@/components/sui/sui-tree/tree'

export default {
	name: 'ReportTree',

	components: { SuiTree },

	props: {
		role: String,
		type: String,
		title: String,
		items: {
			type: Array,
			required: true,
		},
	},

	data() {
		return {
			data: {
				type: this.type,
				role: this.role,
			},
			tree: [
				{
					name: this.type,
					title: this.title,
					children: this.items,
				},
			], // It should be an array.
		}
	},

	computed: {
		/**
		 * Get report items from the settings.
		 *
		 * @since 3.2.5
		 *
		 * @returns {array}
		 */
		getReports() {
			let reports = this.getOption(this.role, 'reports', {})

			reports = reports[this.type] || []

			// To support unexpected format.
			if (typeof reports === 'object' && reports !== null) {
				reports = Object.values(reports)
			}

			return reports
		},
	},

	methods: {
		/**
		 * Set the value for the current report type.
		 *
		 * @param {array} selected Selected items.
		 *
		 * @since 3.2.5
		 */
		setReports(selected) {
			let reports = this.getOption(this.role, 'reports', {})

			reports[this.type] = selected

			this.setOption(this.role, 'reports', reports)
		},

		/**
		 * Handle checkbox click event.
		 *
		 * @param {object} data Data.
		 *
		 * @since 3.2.5
		 */
		handleChange(data) {
			// Only if required data is found.
			if (
				data.item &&
				data.data.type === this.type &&
				data.data.role === this.role
			) {
				if (data.checked) {
					this.setSelected(data.item)
				} else {
					this.removeSelected(data.item)
				}
			}
		},

		/**
		 * Set selected item to the reports.
		 *
		 * @param {string} report Report item.
		 *
		 * @since 3.2.5
		 */
		setSelected(report) {
			let reports = this.getReports

			if (!reports.includes(report)) {
				reports.push(report)
			}

			this.setReports(reports)
		},

		/**
		 * Remove unselected items from the reports.
		 *
		 * @param {string} report Report item.
		 *
		 * @since 3.2.5
		 */
		removeSelected(report) {
			let reports = this.getReports

			// Remove the item.
			if (reports.includes(report)) {
				let index = reports.indexOf(report)

				if (index !== -1) {
					reports.splice(index, 1)
				}
			}

			// Set reports.
			this.setReports(reports)
		},
	},
}
</script>
