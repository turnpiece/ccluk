<template>
	<ul class="sui-tree" data-tree="selector" role="group" :id="id">
		<tree-item
			v-for="item in items"
			:tree="id"
			:data="data"
			:key="item.name"
			:child="item.name"
			:title="item.title"
			:children="item.children"
			:selected-items="selectedItems"
			:disabled-items="disabledItems"
			@itemSelect="itemSelect"
		/>
	</ul>
</template>

<script>
import TreeItem from './tree-item'

export default {
	name: 'SuiTree',

	props: {
		id: {
			type: String,
			required: true,
		},
		items: {
			type: Array,
			required: true,
		},
		data: {
			type: Object,
			default: {},
		},
		disabledItems: {
			type: Array,
		},
		selectedItems: {
			type: Array,
		},
	},

	components: { TreeItem },

	mounted() {
		// Initialize tree only after all children rendered.
		this.$nextTick(function () {
			SUI.suiTree(jQuery('#' + this.id), true)
		})
	},

	methods: {
		/**
		 * Emit item select event.
		 *
		 * @param {object} data
		 *
		 * @since 3.2.3
		 */
		itemSelect(data) {
			// Emit the current child click.
			this.$emit('itemSelect', data)
		},
	},
}
</script>
