<div id="wph-wizard-content-form_image" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Featured image position", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

	</div>

	<div class="wpmudev-box-right">

		<label><?php _e( "Choose the featured image location", Opt_In::TEXT_DOMAIN ); ?></label>

        <div class="wpmudev-tabs">

            <ul class="wpmudev-tabs-menu wpmudev-tabs-menu_full wpmudev-feature-image-position-options">

                <li class="wpmudev-tabs-menu_item{{ ( feature_image_position === 'left' ) ? ' current' : '' }}">
                    <input type="checkbox" value="left">
                    <label><?php _e( "Left", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

                <li class="wpmudev-tabs-menu_item{{ ( feature_image_position === 'right' ) ? ' current' : '' }}">
                    <input type="checkbox" value="right">
                    <label><?php _e( "Right", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

                <li id="wpmudev-tabs-menu_item_above" class="wpmudev-tabs-menu_item{{ ( feature_image_position === 'above' ) ? ' current' : '' }}">
                    <input type="checkbox" value="above">
                	<label><?php _e( "Above content", Opt_In::TEXT_DOMAIN ); ?></label>
            	</li>

                <li id="wpmudev-tabs-menu_item_below" class="wpmudev-tabs-menu_item{{ ( feature_image_position === 'below' ) ? ' current' : '' }}">
                    <input type="checkbox" value="below">
                	<label><?php _e( "Below content", Opt_In::TEXT_DOMAIN ); ?></label>
            	</li>

            </ul>

        </div>

	</div>

</div><?php // #wph-wizard-content-form_image ?>