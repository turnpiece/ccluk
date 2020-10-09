<template>
	<div
		class="sui-modal-slide slide-finishing"
		data-modal-size="md"
		:id="`${$parent.modal}-finishing`"
	>
		<div class="sui-box">
			<div
				class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60"
			>
				<span class="beehive-onboarding-finishing-loader">
					<i
						class="sui-icon-loader sui-loading sui-lg"
						aria-hidden="true"
					></i>
				</span>
				<h3 class="sui-box-title sui-lg">
					{{ $i18n.label.finishing_setup }}
				</h3>
				<p class="sui-description">
					{{ $i18n.desc.finishing_setup }}
				</p>
			</div>
			<div class="sui-box-body"></div>
			<image-tag
				v-if="!$vars.whitelabel.hide_branding"
				src="onboarding/finishing.png"
				class="sui-image sui-image-center"
				:alt="$i18n.label.finishing_setup"
			/>
		</div>
	</div>
</template>

<script>
import ImageTag from '@/components/elements/image-tag'

export default {
	name: 'SlideFinishing',

	components: { ImageTag },
}
</script>
