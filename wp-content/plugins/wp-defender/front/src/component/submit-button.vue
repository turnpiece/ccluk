<template>
	<button :id="id" :type="type" @click="$emit('click')"
	        :class="[getClass,{'sui-button-onload':state.on_saving}]" :disabled="state.on_saving"
	        class="sui-button">
         <span class="sui-loading-text">
             <slot></slot>
         </span>
		<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
	</button>
</template>

<script>
	export default {
		name: "submit-button",
		props: ['id', 'state', 'text', 'css-class', 'type'],
		computed: {
			getClass: function () {
				return 'sui-button ' + this.cssClass;
			}
		}
	}
</script>