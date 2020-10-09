<template>
	<div class="sui-header">
		<h1 class="sui-header-title">{{ title }}</h1>

		<div class="sui-actions-right" v-if="rightActions">
			<!-- Extra actions -->
			<slot name="right"></slot>
			<a
				v-if="showDocLink && !$vars.whitelabel.hide_doc_link"
				class="sui-button sui-button-ghost"
				href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/beehive/"
				target="_blank"
			>
				<i class="sui-icon-academy"></i>
				{{ $i18n.header.doc }}
			</a>
		</div>

		<!-- Dynamic admin top notices-->
		<div class="sui-floating-notices">
			<top-notice v-if="showTopNotice"></top-notice>
		</div>
	</div>
</template>

<script>
import TopNotice from './top-notice'

export default {
	name: 'SuiHeader',

	components: { TopNotice },

	props: {
		title: {
			type: String,
			required: true,
		},
		rightActions: {
			type: Boolean,
			default: true,
		},
		showDocLink: {
			type: Boolean,
			default: true,
		},
		showTopNotice: {
			type: Boolean,
			default: true,
		},
	},
}
</script>
