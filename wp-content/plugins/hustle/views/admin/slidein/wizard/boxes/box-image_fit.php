<div id="wph-wizard-content-image_fit" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Featured image fitting", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

        <label class="wpmudev-helper"><?php _e("Improve the way the featured image fits its container. Preview each option to find one that suits you.", Opt_In::TEXT_DOMAIN); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<label><?php _e("Choose image fitting type", Opt_In::TEXT_DOMAIN); ?></label>

        <div class="wpmudev-tabs">

            <ul class="wpmudev-tabs-menu wpmudev-tabs-menu_full wpmudev-feature-image-fit-options">

                <li class="wpmudev-tabs-menu_item{{ ( feature_image_fit === 'fill' ) ? ' current' : '' }}">
                    <input type="checkbox" value="fill">
                    <label><?php _e( "Fill", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

                <li class="wpmudev-tabs-menu_item{{ ( feature_image_fit === 'contain' ) ? ' current' : '' }}">
                    <input type="checkbox" value="contain">
                    <label><?php _e( "Contain", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

                <li class="wpmudev-tabs-menu_item{{ ( feature_image_fit === 'cover' ) ? ' current' : '' }}">
                    <input type="checkbox" value="cover">
                    <label><?php _e( "Cover", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

                <li class="wpmudev-tabs-menu_item{{ ( feature_image_fit === 'none' ) ? ' current' : '' }}">
                    <input type="checkbox" value="none">
                    <label><?php _e( "None", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

            </ul>

        </div>

        <div id="wph-wizard-content-image_fit_horizontal_vertical_options" class="wpmudev-box-gray {{ ( feature_image_fit === 'contain' || feature_image_fit === 'cover' ) ? 'wpmudev-show' : 'wpmudev-hidden' }}">

            <label><?php _e("Horizontal image position", Opt_In::TEXT_DOMAIN); ?></label>

            <div class="wpmudev-tabs">

                <ul class="wpmudev-tabs-menu wpmudev-tabs-menu_full wpmudev-feature-image-horizontal-options">

                    <li class="wpmudev-tabs-menu_item{{ ( feature_image_horizontal === 'left' ) ? ' current' : '' }}">
                        <input type="checkbox" value="left">
                        <label><?php _e( "Left", Opt_In::TEXT_DOMAIN ); ?></label>
                    </li>

                    <li class="wpmudev-tabs-menu_item{{ ( feature_image_horizontal === 'center' ) ? ' current' : '' }}">
                        <input type="checkbox" value="center">
                        <label><?php _e( "Center", Opt_In::TEXT_DOMAIN ); ?></label>
                    </li>

                    <li class="wpmudev-tabs-menu_item{{ ( feature_image_horizontal === 'right' ) ? ' current' : '' }}">
                        <input type="checkbox" value="right">
                        <label><?php _e( "Right", Opt_In::TEXT_DOMAIN ); ?></label>
                    </li>

                    <li class="wpmudev-tabs-menu_item{{ ( feature_image_horizontal === 'custom' ) ? ' current' : '' }}">
                        <input type="checkbox" value="custom">
                        <label><?php _e( "Custom", Opt_In::TEXT_DOMAIN ); ?></label>
                    </li>

                </ul>

            </div>

            <div id="wph-wizard-design-horizontal-position" class="{{ ( feature_image_horizontal === 'custom' ) ? 'wpmudev-show' : 'wpmudev-hidden' }}">

                <label><?php _e("Horizontal position (px)", Opt_In::TEXT_DOMAIN); ?></label>

                <input type="number" data-attribute="feature_image_horizontal_px" value="{{feature_image_horizontal_px}}" class="wpmudev-input_number">

            </div>

            <label><?php _e("Vertical image position", Opt_In::TEXT_DOMAIN); ?></label>

            <div class="wpmudev-tabs">

                <ul class="wpmudev-tabs-menu wpmudev-tabs-menu_full wpmudev-feature-image-vertical-options">

                    <li class="wpmudev-tabs-menu_item{{ ( feature_image_vertical === 'top' ) ? ' current' : '' }}">
                        <input type="checkbox" value="top">
                        <label><?php _e( "Top", Opt_In::TEXT_DOMAIN ); ?></label>
                    </li>

                    <li class="wpmudev-tabs-menu_item{{ ( feature_image_vertical === 'center' ) ? ' current' : '' }}">
                        <input type="checkbox" value="center">
                        <label><?php _e( "Center", Opt_In::TEXT_DOMAIN ); ?></label>
                    </li>

                    <li class="wpmudev-tabs-menu_item{{ ( feature_image_vertical === 'bottom' ) ? ' current' : '' }}">
                        <input type="checkbox" value="bottom">
                        <label><?php _e( "Bottom", Opt_In::TEXT_DOMAIN ); ?></label>
                    </li>

                    <li class="wpmudev-tabs-menu_item{{ ( feature_image_vertical === 'custom' ) ? ' current' : '' }}">
                        <input type="checkbox" value="custom">
                        <label><?php _e( "Custom", Opt_In::TEXT_DOMAIN ); ?></label>
                    </li>

                </ul>

            </div>

            <div id="wph-wizard-design-vertical-position" class="{{ ( feature_image_vertical === 'custom' ) ? 'wpmudev-show' : 'wpmudev-hidden' }}">

                <label><?php _e("Vertical position (px)", Opt_In::TEXT_DOMAIN); ?></label>

                <input type="number" data-attribute="feature_image_vertical_px" value="{{feature_image_vertical_px}}" class="wpmudev-input_number">

            </div>

        </div>

	</div>

</div><?php // #wph-wizard-content-form_image ?>