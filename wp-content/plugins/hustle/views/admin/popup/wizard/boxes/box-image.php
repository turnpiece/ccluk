<div id="wph-wizard-content-image" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Featured image", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "A Featured image is an optional part of the design. You can also insert images inside content, but Featured images result in a better design.", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-popup-feature-image" class="toggle-checkbox" type="checkbox" data-attribute="use_feature_image" {{_.checked(_.isTrue(use_feature_image), true)}} >

				<label class="wpmudev-switch-design" for="wph-popup-feature-image" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-popup-feature-image"><?php _e( "Use featured image", Opt_In::TEXT_DOMAIN ); ?></label>

		</div>

		<div id="wph-wizard-content-image-options" class="wpmudev-box-gray {{ ( _.isFalse(use_feature_image) ) ? 'wpmudev-hidden' : '' }}">

			<?php $this->render( "admin/commons/wizard/featured-image", array() ); ?>

            <div class="wpmudev-switch-labeled">

                <div class="wpmudev-switch">

                    <input id="wph-popup-mobile_hide" class="toggle-checkbox" type="checkbox" data-attribute="feature_image_hide_on_mobile" {{_.checked(_.isTrue(feature_image_hide_on_mobile), true)}}>

                    <label class="wpmudev-switch-design" for="wph-popup-mobile_hide" aria-hidden="true"></label>

                </div>

                <label class="wpmudev-switch-label" for="wph-popup-mobile_hide"><?php _e( "Hide on mobile devices", Opt_In::TEXT_DOMAIN ); ?></label>

            </div>

		</div>

	</div>

</div><?php // #wph-wizard-content-image ?>