<template>
	<box-footer :processing="processing" @submit="$emit('submit')">
		<template v-slot:left>
			<button
				class="sui-button sui-button-ghost"
				data-modal-open="beehive-gtm-deactivate-confirm"
			>
				<i class="sui-icon-power-on-off" aria-hidden="true"></i>
				{{ $i18n.button.deactivate }}
			</button>
		</template>
	</box-footer>
</template>

<script>
import BoxFooter from '@/components/elements/box-footer'

export default {
	name: 'FooterArea',

	components: { BoxFooter },

	props: {
		processing: {
			type: Boolean,
			default: false,
		},
	},
}
</script>
