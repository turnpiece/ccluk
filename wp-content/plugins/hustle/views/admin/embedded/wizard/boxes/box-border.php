<div id="wph-wizard-design-border" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Border", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

	</div>

	<div class="wpmudev-box-right">

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-aftercontent-border" class="toggle-checkbox" type="checkbox" data-attribute="border" {{_.checked(_.isTrue(border), true)}} >

				<label class="wpmudev-switch-design" for="wph-aftercontent-border" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-aftercontent-border"><?php _e( "Show border", Opt_In::TEXT_DOMAIN ); ?></label>

		</div>

		<div id="wph-wizard-design-border-options" class="wpmudev-box-gray {{ ( _.isFalse(border) ) ? 'wpmudev-hidden' : 'wpmudev-show' }}">

			<div class="wpmudev-row">

				<div class="wpmudev-col">

					<label><?php _e( "Radius", Opt_In::TEXT_DOMAIN ); ?></label>

					<input type="number" data-attribute="border_radius" value="{{border_radius}}" class="wpmudev-input_number" min="0" >

				</div>

				<div class="wpmudev-col">

					<label><?php _e( "Weight", Opt_In::TEXT_DOMAIN ); ?></label>

					<input type="number" data-attribute="border_weight" value="{{border_weight}}" class="wpmudev-input_number" min="0" >

				</div>

				<div class="wpmudev-col">

					<label><?php _e( "Type", Opt_In::TEXT_DOMAIN ); ?></label>

					<select class="wpmudev-select" data-attribute="border_type" >
						<option value="solid" {{ ( border_type === 'solid' ) ? 'selected' : '' }} ><?php _e( "Solid", Opt_In::TEXT_DOMAIN ); ?></option>
						<option value="dotted" {{ ( border_type === 'dotted' ) ? 'selected' : '' }} ><?php _e( "Dotted", Opt_In::TEXT_DOMAIN ); ?></option>
						<option value="dashed" {{ ( border_type === 'dashed' ) ? 'selected' : '' }} ><?php _e( "Dashed", Opt_In::TEXT_DOMAIN ); ?></option>
						<option value="double" {{ ( border_type === 'double' ) ? 'selected' : '' }} ><?php _e( "Double", Opt_In::TEXT_DOMAIN ); ?></option>
						<option value="none" {{ ( border_type === 'none' ) ? 'selected' : '' }} ><?php _e( "None", Opt_In::TEXT_DOMAIN ); ?></option>
					</select>

				</div>

				<div class="wpmudev-col">

					<label><?php _e( "Border color", Opt_In::TEXT_DOMAIN ); ?></label>

					<div class="wpmudev-picker"><input id="aftercontent_modal_border" class="wpmudev-color_picker" type="text"  value="{{border_color}}" data-attribute="border_color" data-alpha="true" /></div>

				</div>

			</div>

		</div>

	</div>

</div><?php // #wph-wizard-design-border ?>