<div id="wph-wizard-design-shapes" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Shapes, borders, icons", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

	</div>

	<div class="wpmudev-box-right">

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-popup-border" class="toggle-checkbox" type="checkbox" data-attribute="border" {{_.checked(_.isTrue(border), true)}} >

				<label class="wpmudev-switch-design" for="wph-popup-border" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-popup-border"><?php _e( "Pop-up module border", Opt_In::TEXT_DOMAIN ); ?></label>

		</div><?php // .wpmudev-switch-labeled ?>

		<div id="wph-wizard-design-border-options" class="wpmudev-box-gray {{ ( _.isFalse(border) ) ? 'wpmudev-hidden' : 'wpmudev-show' }}">

			<div class="wpmudev-row">

				<div class="wpmudev-col">

					<label><?php _e( "Radius", Opt_In::TEXT_DOMAIN ); ?></label>

					<input type="number" value="{{border_radius}}" data-attribute="border_radius" class="wpmudev-input_number">

				</div>

				<div class="wpmudev-col">

					<label><?php _e( "Weight", Opt_In::TEXT_DOMAIN ); ?></label>

					<input type="number" value="{{border_weight}}" data-attribute="border_weight" class="wpmudev-input_number">

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

                    <div class="wpmudev-picker"><input id="popup_modal_border" class="wpmudev-color_picker" type="text"  value="{{border_color}}" data-attribute="border_color" data-alpha="true" /></div>

				</div>

			</div>

		</div><?php // .wpmudev-box-gray ?>

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-popup-border_fields" class="toggle-checkbox" type="checkbox" data-attribute="form_fields_border" {{_.checked(_.isTrue(form_fields_border), true)}}>

				<label class="wpmudev-switch-design" for="wph-popup-border_fields" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-popup-border_fields"><?php _e( "Form fields border", Opt_In::TEXT_DOMAIN ); ?></label>

		</div><?php // .wpmudev-switch-labeled ?>

		<div id="wph-wizard-design-form-fields-border-options" class="wpmudev-box-gray {{ ( _.isFalse(form_fields_border) ) ? 'wpmudev-hidden' : 'wpmudev-show' }}">

			<div class="wpmudev-row">

				<div class="wpmudev-col">

					<label><?php _e( "Radius", Opt_In::TEXT_DOMAIN ); ?></label>

					<input type="number" value="{{form_fields_border_radius}}" data-attribute="form_fields_border_radius" class="wpmudev-input_number">

				</div>

				<div class="wpmudev-col">

					<label><?php _e( "Weight", Opt_In::TEXT_DOMAIN ); ?></label>

					<input type="number" value="{{form_fields_border_weight}}" data-attribute="form_fields_border_weight" class="wpmudev-input_number">

				</div>

				<div class="wpmudev-col">

					<label><?php _e( "Type", Opt_In::TEXT_DOMAIN ); ?></label>

                    <select class="wpmudev-select" data-attribute="form_fields_border_type" >
						<option value="solid" {{ ( form_fields_border_type === 'solid' ) ? 'selected' : '' }} ><?php _e( "Solid", Opt_In::TEXT_DOMAIN ); ?></option>
						<option value="dotted" {{ ( form_fields_border_type === 'dotted' ) ? 'selected' : '' }} ><?php _e( "Dotted", Opt_In::TEXT_DOMAIN ); ?></option>
						<option value="dashed" {{ ( form_fields_border_type === 'dashed' ) ? 'selected' : '' }} ><?php _e( "Dashed", Opt_In::TEXT_DOMAIN ); ?></option>
						<option value="double" {{ ( form_fields_border_type === 'double' ) ? 'selected' : '' }} ><?php _e( "Double", Opt_In::TEXT_DOMAIN ); ?></option>
						<option value="none" {{ ( form_fields_border_type === 'none' ) ? 'selected' : '' }} ><?php _e( "None", Opt_In::TEXT_DOMAIN ); ?></option>
					</select>

				</div>

				<div class="wpmudev-col">

					<label><?php _e( "Border color", Opt_In::TEXT_DOMAIN ); ?></label>

					<div class="wpmudev-picker"><input id="popup_modal_form_fields_border" class="wpmudev-color_picker" type="text"  value="{{form_fields_border_color}}" data-attribute="form_fields_border_color" data-alpha="true" /></div>

				</div>

			</div>

		</div><?php // .wpmudev-box-gray ?>

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-popup-border_button" class="toggle-checkbox" type="checkbox" data-attribute="button_border" {{_.checked(_.isTrue(button_border), true)}}>

				<label class="wpmudev-switch-design" for="wph-popup-border_button" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-popup-border_button"><?php _e( "Button border", Opt_In::TEXT_DOMAIN ); ?></label>

		</div><?php // .wpmudev-switch-labeled ?>

        <div id="wph-wizard-design-button-border-options" class="wpmudev-box-gray {{ ( _.isFalse(button_border) ) ? 'wpmudev-hidden' : 'wpmudev-show' }}">

			<div class="wpmudev-row">

				<div class="wpmudev-col">

					<label><?php _e( "Radius", Opt_In::TEXT_DOMAIN ); ?></label>

					<input type="number" value="{{button_border_radius}}" data-attribute="button_border_radius" class="wpmudev-input_number">

				</div>

				<div class="wpmudev-col">

					<label><?php _e( "Weight", Opt_In::TEXT_DOMAIN ); ?></label>

					<input type="number" value="{{button_border_weight}}" data-attribute="button_border_weight" class="wpmudev-input_number">

				</div>

				<div class="wpmudev-col">

					<label><?php _e( "Type", Opt_In::TEXT_DOMAIN ); ?></label>

                    <select class="wpmudev-select" data-attribute="button_border_type" >
						<option value="solid" {{ ( button_border_type === 'solid' ) ? 'selected' : '' }} ><?php _e( "Solid", Opt_In::TEXT_DOMAIN ); ?></option>
						<option value="dotted" {{ ( button_border_type === 'dotted' ) ? 'selected' : '' }} ><?php _e( "Dotted", Opt_In::TEXT_DOMAIN ); ?></option>
						<option value="dashed" {{ ( button_border_type === 'dashed' ) ? 'selected' : '' }} ><?php _e( "Dashed", Opt_In::TEXT_DOMAIN ); ?></option>
						<option value="double" {{ ( button_border_type === 'double' ) ? 'selected' : '' }} ><?php _e( "Double", Opt_In::TEXT_DOMAIN ); ?></option>
						<option value="none" {{ ( button_border_type === 'none' ) ? 'selected' : '' }} ><?php _e( "None", Opt_In::TEXT_DOMAIN ); ?></option>
					</select>

				</div>

				<div class="wpmudev-col">

					<label><?php _e( "Border color", Opt_In::TEXT_DOMAIN ); ?></label>

                    <div class="wpmudev-picker"><input id="popup_modal_button_border" class="wpmudev-color_picker" type="text"  value="{{button_border_color}}" data-attribute="button_border_color" data-alpha="true" /></div>

				</div>

			</div>

		</div><?php // .wpmudev-box-gray ?>

		<label><?php _e( "Form fields icon", Opt_In::TEXT_DOMAIN ); ?></label>

		<div class="wpmudev-tabs">

            <ul id="wpmudev-form-fields-icon" class="wpmudev-tabs-menu wpmudev-tabs-menu_full wpmudev-form-fields-icon-options">

				<li class="wpmudev-tabs-menu_item{{ ( form_fields_icon === 'none' ) ? ' current' : '' }}">
                    <input type="checkbox" value="none">
                    <label><?php _e( "No icon", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

				<li class="wpmudev-tabs-menu_item{{ ( form_fields_icon === 'static' ) ? ' current' : '' }}">
                    <input type="checkbox" value="static">
                    <label><?php _e( "Static icon", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

				<li class="wpmudev-tabs-menu_item{{ ( form_fields_icon === 'animated' ) ? ' current' : '' }}">
                    <input type="checkbox" value="animated">
                    <label><?php _e( "Animated icon", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

            </ul>

        </div>

		<label><?php _e( "Form fields proximity", Opt_In::TEXT_DOMAIN ); ?></label>

		<div class="wpmudev-tabs">

            <ul id="wpmudev-form-fields-proximity" class="wpmudev-tabs-menu wpmudev-tabs-menu_full wpmudev-form-fields-proximity-options">

				<li class="wpmudev-tabs-menu_item{{ ( form_fields_proximity === 'separated' ) ? ' current' : '' }}">
                    <input type="checkbox" value="separated">
                    <label><?php _e( "Separated form fields", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

				<li class="wpmudev-tabs-menu_item{{ ( form_fields_proximity === 'joined' ) ? ' current' : '' }}">
                    <input type="checkbox" value="joined">
                    <label><?php _e( "Joined form fields", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

            </ul>

        </div>

	</div>

</div><?php // #wph-wizard-design-shape ?>