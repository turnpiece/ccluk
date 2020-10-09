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
import VariableRow from './visitors/variable-row'

export default {
	name: 'VisitorsVariables',

	components: { VariableRow },

	data() {
		return {
			variables: {
				login_status: this.$i18n.visitor.login_status,
				user_role: this.$i18n.visitor.user_role,
				user_id: this.$i18n.visitor.user_id,
				user_name: this.$i18n.visitor.user_name,
				user_email: this.$i18n.visitor.user_email,
				user_creation_date: this.$i18n.visitor.user_creation_date,
			},
		}
	},
}
</script>
