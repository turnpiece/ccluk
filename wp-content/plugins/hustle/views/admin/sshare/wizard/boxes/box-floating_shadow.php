<div id="wph-wizard-floating-shadow-options">

    <div class="wpmudev-switch-labeled">

        <div class="wpmudev-switch">

            <input id="wph-shares-floating-shadow" class="toggle-checkbox" type="checkbox" data-attribute="drop_shadow" {{_.checked( _.isTrue(drop_shadow), true )}}>

            <label class="wpmudev-switch-design" for="wph-shares-floating-shadow" aria-hidden="true"></label>

        </div>

        <label class="wpmudev-switch-label" for="wph-shares-floating-shadow"><?php _e( "Drop shadow", Opt_In::TEXT_DOMAIN ); ?></label>

    </div>

    <div class="wpmudev-box-gray {{ ( _.isTrue(drop_shadow) ) ? 'wpmudev-show' : 'wpmudev-hidden' }}">

	    <div class="wpmudev-row">

			<div class="wpmudev-col">

				<label><?php _e( "X-offset", Opt_In::TEXT_DOMAIN ); ?></label>

				<input type="number" value="{{drop_shadow_x}}" data-attribute="drop_shadow_x" class="wpmudev-input_number">

			</div>

			<div class="wpmudev-col">

				<label><?php _e( "Y-offset", Opt_In::TEXT_DOMAIN ); ?></label>

				<input type="number" value="{{drop_shadow_y}}" data-attribute="drop_shadow_y" class="wpmudev-input_number">

			</div>

			<div class="wpmudev-col">

				<label><?php _e( "Blur", Opt_In::TEXT_DOMAIN ); ?></label>

				<input type="number" value="{{drop_shadow_blur}}" data-attribute="drop_shadow_blur" class="wpmudev-input_number">

			</div>

			<div class="wpmudev-col">

				<label><?php _e( "Spread", Opt_In::TEXT_DOMAIN ); ?></label>

				<input type="number" value="{{drop_shadow_spread}}" data-attribute="drop_shadow_spread" class="wpmudev-input_number">

			</div>

			<div class="wpmudev-col">

				<label><?php _e( "Color", Opt_In::TEXT_DOMAIN ); ?></label>

				<div class="wpmudev-picker"><input class="wpmudev-color_picker" type="text"  value="{{drop_shadow_color}}" data-attribute="drop_shadow_color" data-alpha="true" /></div>

			</div>

		</div>

	</div>

</div>