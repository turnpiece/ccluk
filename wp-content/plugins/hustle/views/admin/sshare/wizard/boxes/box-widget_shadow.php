<div id="wph-wizard-widget-shadow-options">

    <div class="wpmudev-switch-labeled">

        <div class="wpmudev-switch">

            <input id="wph-shares-widget-shadow" class="toggle-checkbox" type="checkbox" data-attribute="widget_drop_shadow" {{ _.checked( _.isTrue(widget_drop_shadow), true ) }}>

            <label class="wpmudev-switch-design" for="wph-shares-widget-shadow" aria-hidden="true"></label>

        </div>

        <label class="wpmudev-switch-label" for="wph-shares-widget-shadow"><?php _e( "Drop shadow", Opt_In::TEXT_DOMAIN ); ?></label>

    </div>

    <div class="wpmudev-box-gray {{ ( _.isTrue(widget_drop_shadow) ) ? 'wpmudev-show' : 'wpmudev-hidden' }}">

	    <div class="wpmudev-row">

			<div class="wpmudev-col">

				<label><?php _e( "X-offset", Opt_In::TEXT_DOMAIN ); ?></label>

				<input type="number" value="{{widget_drop_shadow_x}}" data-attribute="widget_drop_shadow_x" class="wpmudev-input_number">

			</div>

			<div class="wpmudev-col">

				<label><?php _e( "Y-offset", Opt_In::TEXT_DOMAIN ); ?></label>

				<input type="number" value="{{widget_drop_shadow_y}}" data-attribute="widget_drop_shadow_y" class="wpmudev-input_number">

			</div>

			<div class="wpmudev-col">

				<label><?php _e( "Blur", Opt_In::TEXT_DOMAIN ); ?></label>

				<input type="number" value="{{widget_drop_shadow_blur}}" data-attribute="widget_drop_shadow_blur" class="wpmudev-input_number">

			</div>

			<div class="wpmudev-col">

				<label><?php _e( "Spread", Opt_In::TEXT_DOMAIN ); ?></label>

				<input type="number" value="{{widget_drop_shadow_spread}}" data-attribute="widget_drop_shadow_spread" class="wpmudev-input_number">

			</div>

			<div class="wpmudev-col">

				<label><?php _e( "Color", Opt_In::TEXT_DOMAIN ); ?></label>

				<div class="wpmudev-picker"><input class="wpmudev-color_picker" type="text"  value="{{widget_drop_shadow_color}}" data-attribute="widget_drop_shadow_color" data-alpha="true" /></div>

			</div>

		</div>

	</div>

</div>