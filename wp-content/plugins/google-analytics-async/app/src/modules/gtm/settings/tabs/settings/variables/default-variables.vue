<template>
	<table class="sui-table">
		<thead>
			<tr>
				<th>{{ $i18n.label.variable }}</th>
				<th>{{ $i18n.label.custom_name }}</th>
			</tr>
		</thead>

		<tbody>
			<variable-row
				v-for="(title, id) in variables"
				:title="title"
				:id="id"
				:key="id"
			/>
		</tbody>
	</table>
</template>

<script>
import VariableRow from './default/variable-row'

export default {
	name: 'DefaultVariables',

	components: { VariableRow },

	data() {
		return {
			variables: {
				post_id: this.$i18n.variable.post_id,
				post_title: this.$i18n.variable.post_title,
				post_type: this.$i18n.variable.post_type,
				post_date: this.$i18n.variable.post_date,
				post_author: this.$i18n.variable.post_author,
				post_author_name: this.$i18n.variable.post_author_name,
				post_categories: this.$i18n.variable.post_categories,
				post_tags: this.$i18n.variable.post_tags,
			},
		}
	},
}
</script>
