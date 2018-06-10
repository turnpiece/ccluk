<div id="wph-wizard-settings-animation" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Animation settings", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

        <label class="wpmudev-helper"><?php _e( "Choose how you want your embed to animate on entrance", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<label><?php _e( "Embed entrance animation", Opt_In::TEXT_DOMAIN ); ?></label>

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

    </div>

</div><?php // #wph-wizard-settings-animation ?>