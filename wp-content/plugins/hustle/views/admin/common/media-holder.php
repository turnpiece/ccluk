<script type="text/template" id="hustle-media-holder-tpl">

	<div class="wph-media--holder">

		<button class="wph-media--add {{_.class(!!image, 'hidden')}}"><span class="dashicons dashicons-format-image"></span><?php _e('Click to Add Image', Opt_In::TEXT_DOMAIN); ?></button>

		<div class="wph-media--options {{_.class(!image, 'hidden')}}" >

			<button class="wph-button wph-button--dots">

				<span class="dot"></span>
				<span class="dot"></span>
				<span class="dot"></span>

			</button>

			<div class="wph-media--list" >

				<div class="svg-triangle hidden">

					<svg xmlns="http://www.w3.org/2000/svg" version="1.1">

						<polygon points="10,10 0,10 5,5 "></polygon>

					</svg>

				</div>

				<ul class="wph-media--items hidden">

					<div class="wph-media--title">

						<span class="f-left"><?php _e('OPTIONS', Opt_In::TEXT_DOMAIN); ?></span>

						<i class="wph-icon i-close"></i>

					</div>

					<li><a class="wpoi-swap-image-button" href="#"><span class="dashicons dashicons-format-gallery"></span><?php _e("Swap Image", Opt_In::TEXT_DOMAIN) ?></a></li>

					<li><a class="wpoi-delete-image-button" href="#"><span class="dashicons dashicons-trash"></span><?php _e("Delete Image", Opt_In::TEXT_DOMAIN) ?></a></li>

				</ul>

			</div>

		</div>

		<div class="wph-media--preview" style="background-image: url({{image}});"></div>

	</div>

</script>