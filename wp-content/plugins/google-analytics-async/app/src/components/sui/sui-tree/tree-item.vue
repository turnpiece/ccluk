<template>
	<li
		role="treeitem"
		ref="mainTree"
		:aria-selected="isSelected"
		:aria-disabled="isDisabled"
	>
		<div class="sui-tree-node">
			<label
				:for="`${tree}-${parent}-${child}`"
				class="sui-node-checkbox"
			>
				<input
					type="checkbox"
					:id="`${tree}-${parent}-${child}`"
					v-model="checked"
					@change="expandChildren"
				/>
				<span aria-hidden="true"></span>
				<span>{{ $i18n.tree.select }}</span>
			</label>

			<span class="sui-node-text">{{ title }}</span>

			<button v-if="hasChildren" data-button="expander" ref="mainOpener">
				<span aria-hidden="true"></span>
				<span class="sui-screen-reader-text">
					{{ $i18n.tree.open_close }}
				</span>
			</button>
		</div>

		<ul v-if="hasChildren" role="group">
			<tree-item
				v-for="childData in children"
				:key="childData.name"
				:tree="tree"
				:data="data"
				:child="childData.name"
				:parent="child"
				:title="childData.title"
				:children="getChildren(childData)"
				:selected-items="selectedItems"
				:disabled-items="disabledItems"
				@itemSelect="itemSelect"
			/>
		</ul>
	</li>
</template>

<script>
export default {
	name: 'TreeItem',

	props: {
		children: Array,
		child: String,
		parent: {
			type: String,
			default: '',
		},
		tree: {
			type: String,
			required: true,
		},
		title: {
			type: String,
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

	data() {
		return {
			checked: this.isSelected,
		}
	},

	watch: {
		/**
		 * Perform the change event when checked status changed.
		 *
		 * @param {boolean} checked
		 *
		 * @since 3.2.3
		 */
		checked(checked) {
			// Emit checked event.
			this.emitChange(
				{
					name: this.child,
					children: this.hasChildren ? this.children : [],
				},
				checked
			)
		},
	},

	computed: {
		/**
		 * Check if current tree has children.
		 *
		 * @since 3.2.3
		 *
		 * @returns {boolean}
		 */
		hasChildren() {
			return this.children.length > 0
		},

		/**
		 * Check if current item is selected.
		 *
		 * @since 3.2.3
		 *
		 * @returns {boolean}
		 */
		isSelected() {
			let items = Array.isArray(this.selectedItems)
				? this.selectedItems
				: []

			return items && items.includes(this.child)
		},

		/**
		 * Check if current item is disabled.
		 *
		 * @since 3.2.3
		 *
		 * @returns {boolean}
		 */
		isDisabled() {
			let items = Array.isArray(this.disabledItems)
				? this.disabledItems
				: []

			return items && items.includes(this.child)
		},
	},

	methods: {
		/**
		 * Get children of of current tree item.
		 *
		 * @since 3.2.3
		 *
		 * @returns {array}
		 */
		getChildren(data) {
			return data.children || []
		},

		/**
		 * Emit the single item change event.
		 *
		 * @param {object} child Child.
		 * @param {boolean} checked Is checked?
		 *
		 * @since 3.2.3
		 */
		emitChange(child, checked) {
			const self = this

			// Recursive checks.
			if (child.children && child.children.length > 0) {
				child.children.forEach(function (child) {
					self.emitChange(child, checked)
				})
			}

			// Emit the current child click.
			this.$emit('itemSelect', {
				tree: this.tree,
				item: child.name,
				checked: checked,
				data: this.data,
			})
		},

		/**
		 * Emit a single item select event.
		 *
		 * @param {object} data
		 *
		 * @since 3.2.3
		 */
		itemSelect(data) {
			// Emit the current child click.
			this.$emit('itemSelect', data)
		},

		/**
		 * Expand children if required.
		 *
		 * For the main tree item, expand children
		 * when selected.
		 *
		 * @since 3.3.2
		 *
		 * @returns {void}
		 */
		expandChildren() {
			if (!this.parent) {
				// Get the expanded attribute.
				let expanded = this.$refs.mainTree.getAttribute('aria-expanded')

				// Expand if not opened yet.
				if ('false' === expanded && this.checked) {
					// Click on the opener button.
					this.$refs.mainOpener.click()
				}
			}
		},
	},
}
</script>
