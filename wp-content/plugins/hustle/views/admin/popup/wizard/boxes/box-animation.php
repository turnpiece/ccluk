<div id="wph-wizard-settings-animation" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Animation settings", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

        <label class="wpmudev-helper"><?php _e( "Choose how you want your pop-up to animate on entrance & exit", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<label><?php _e( "Pop-up entrance animation", Opt_In::TEXT_DOMAIN ); ?></label>

        <select class="wpmudev-select" data-attribute="animation_in">
            <option value="no_animation" {{ ( animation_in === 'no_animation' || animation_in === '' ) ? 'selected' : '' }}><?php _e( "No Animation", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="bounceIn" {{ ( animation_in === 'bounceIn' ) ? 'selected' : '' }}><?php _e( "Bounce In", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="bounceInUp" {{ ( animation_in === 'bounceInUp' ) ? 'selected' : '' }}><?php _e( "Bounce In Up", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="bounceInRight" {{ ( animation_in === 'bounceInRight' ) ? 'selected' : '' }}><?php _e( "Bounce In Right", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="bounceInDown" {{ ( animation_in === 'bounceInDown' ) ? 'selected' : '' }}><?php _e( "Bounce In Down", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="bounceInLeft" {{ ( animation_in === 'bounceInLeft' ) ? 'selected' : '' }}><?php _e( "Bounce In Left", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="fadeIn" {{ ( animation_in === 'fadeIn' ) ? 'selected' : '' }}><?php _e( "Fade In", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="fadeInUp" {{ ( animation_in === 'fadeInUp' ) ? 'selected' : '' }}><?php _e( "Fade In Up", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="fadeInRight" {{ ( animation_in === 'fadeInRight' ) ? 'selected' : '' }}><?php _e( "Fade In Right", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="fadeInDown" {{ ( animation_in === 'fadeInDown' ) ? 'selected' : '' }}><?php _e( "Fade In Down", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="fadeInLeft" {{ ( animation_in === 'fadeInLeft' ) ? 'selected' : '' }}><?php _e( "Fade In Left", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="rotateIn" {{ ( animation_in === 'rotateIn' ) ? 'selected' : '' }}><?php _e( "Rotate In", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="rotateInDownLeft" {{ ( animation_in === 'rotateInDownLeft' ) ? 'selected' : '' }}><?php _e( "Rotate In Down Left", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="rotateInDownRight" {{ ( animation_in === 'rotateInDownRight' ) ? 'selected' : '' }}><?php _e( "Rotate In Down Right", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="rotateInUpLeft" {{ ( animation_in === 'rotateInUpLeft' ) ? 'selected' : '' }}><?php _e( "Rotate In Up Left", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="rotateInUpRight" {{ ( animation_in === 'rotateInUpRight' ) ? 'selected' : '' }}><?php _e( "Rotate In Up Right", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="slideInUp" {{ ( animation_in === 'slideInUp' ) ? 'selected' : '' }}><?php _e( "Slide In Up", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="slideInRight" {{ ( animation_in === 'slideInRight' ) ? 'selected' : '' }}><?php _e( "Slide In Right", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="slideInDown" {{ ( animation_in === 'slideInDown' ) ? 'selected' : '' }}><?php _e( "Slide In Down", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="slideInLeft" {{ ( animation_in === 'slideInLeft' ) ? 'selected' : '' }}><?php _e( "Slide In Left", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="zoomIn" {{ ( animation_in === 'zoomIn' ) ? 'selected' : '' }}><?php _e( "Zoom In", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="zoomInUp" {{ ( animation_in === 'zoomInUp' ) ? 'selected' : '' }}><?php _e( "Zoom In Up", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="zoomInRight" {{ ( animation_in === 'zoomInRight' ) ? 'selected' : '' }}><?php _e( "Zoom In Right", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="zoomInDown" {{ ( animation_in === 'zoomInDown' ) ? 'selected' : '' }}><?php _e( "Zoom In Down", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="zoomInLeft" {{ ( animation_in === 'zoomInLeft' ) ? 'selected' : '' }}><?php _e( "Zoom In Left", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="rollIn" {{ ( animation_in === 'rollIn' ) ? 'selected' : '' }}><?php _e( "Roll In", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="lightSpeedIn" {{ ( animation_in === 'lightSpeedIn' ) ? 'selected' : '' }}><?php _e( "Light Speed In", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="newspaperIn" {{ ( animation_in === 'newspaperIn' ) ? 'selected' : '' }}><?php _e( "Newspaper In", Opt_In::TEXT_DOMAIN ); ?></option>
        </select>

        <label><?php _e( "Pop-up exit animation", Opt_In::TEXT_DOMAIN ); ?></label>

        <select class="wpmudev-select" data-attribute="animation_out">
            <option value="no_animation" {{ ( animation_out === 'no_animation' || animation_out === '' ) ? 'selected' : '' }}><?php _e( "No Animation", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="bounceOut" {{ ( animation_out === 'bounceOut' ) ? 'selected' : '' }}><?php _e( "Bounce Out", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="bounceOutUp" {{ ( animation_out === 'bounceOutUp' ) ? 'selected' : '' }}><?php _e( "Bounce Out Up", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="bounceOutRight" {{ ( animation_out === 'bounceOutRight' ) ? 'selected' : '' }}><?php _e( "Bounce Out Right", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="bounceOutDown" {{ ( animation_out === 'bounceOutDown' ) ? 'selected' : '' }}><?php _e( "Bounce Out Down", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="bounceOutLeft" {{ ( animation_out === 'bounceOutLeft' ) ? 'selected' : '' }}><?php _e( "Bounce Out Left", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="fadeOut" {{ ( animation_out === 'fadeOut' ) ? 'selected' : '' }}><?php _e( "Fade Out", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="fadeOutUp" {{ ( animation_out === 'fadeOutUp' ) ? 'selected' : '' }}><?php _e( "Fade Out Up", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="fadeOutRight" {{ ( animation_out === 'fadeOutRight' ) ? 'selected' : '' }}><?php _e( "Fade Out Right", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="fadeOutDown" {{ ( animation_out === 'fadeOutDown' ) ? 'selected' : '' }}><?php _e( "Fade Out Down", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="fadeOutLeft" {{ ( animation_out === 'fadeOutLeft' ) ? 'selected' : '' }}><?php _e( "Fade Out Left", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="rotateOut" {{ ( animation_out === 'rotateOut' ) ? 'selected' : '' }}><?php _e( "Rotate Out", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="rotateOutUpLeft" {{ ( animation_out === 'rotateOutUpLeft' ) ? 'selected' : '' }}><?php _e( "Rotate Out Up Left", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="rotateOutUpRight" {{ ( animation_out === 'rotateOutUpRight' ) ? 'selected' : '' }}><?php _e( "Rotate Out Up Right", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="rotateOutDownLeft" {{ ( animation_out === 'rotateOutDownLeft' ) ? 'selected' : '' }}><?php _e( "Rotate Out Down Left", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="rotateOutDownRight" {{ ( animation_out === 'rotateOutDownRight' ) ? 'selected' : '' }}><?php _e( "Rotate Out Down Right", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="slideOutUp" {{ ( animation_out === 'slideOutUp' ) ? 'selected' : '' }}><?php _e( "Slide Out Up", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="slideOutRight" {{ ( animation_out === 'slideOutRight' ) ? 'selected' : '' }}><?php _e( "Slide Out Right", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="slideOutDown" {{ ( animation_out === 'slideOutDown' ) ? 'selected' : '' }}><?php _e( "Slide Out Down", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="slideOutLeft" {{ ( animation_out === 'slideOutLeft' ) ? 'selected' : '' }}><?php _e( "Slide Out Left", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="zoomOut" {{ ( animation_out === 'zoomOut' ) ? 'selected' : '' }}><?php _e( "Zoom Out", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="zoomOutUp" {{ ( animation_out === 'zoomOutUp' ) ? 'selected' : '' }}><?php _e( "Zoom Out Up", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="zoomOutRight" {{ ( animation_out === 'zoomOutRight' ) ? 'selected' : '' }}><?php _e( "Zoom Out Right", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="zoomOutDown" {{ ( animation_out === 'zoomOutDown' ) ? 'selected' : '' }}><?php _e( "Zoom Out Down", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="zoomOutLeft" {{ ( animation_out === 'zoomOutLeft' ) ? 'selected' : '' }}><?php _e( "Zoom Out Left", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="rollOut" {{ ( animation_out === 'rollOut' ) ? 'selected' : '' }}><?php _e( "Roll Out", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="lightSpeedOut" {{ ( animation_out === 'lightSpeedOut' ) ? 'selected' : '' }}><?php _e( "Light Speed Out", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="newspaperOut" {{ ( animation_out === 'newspaperOut' ) ? 'selected' : '' }}><?php _e( "Newspaper Out", Opt_In::TEXT_DOMAIN ); ?></option>
        </select>

    </div>

</div><?php // #wph-wizard-settings-animation ?>