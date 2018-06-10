<div id="wph-wizard-widget-animation-options">

    <div class="wpmudev-switch-labeled">

        <div class="wpmudev-switch">

            <input id="wph-shares-widget-animated" class="toggle-checkbox" type="checkbox" data-attribute="widget_animate_icons" {{ _.checked( _.isTrue(widget_animate_icons), true ) }}>

            <label class="wpmudev-switch-design" for="wph-shares-widget-animated" aria-hidden="true"></label>

        </div>

        <div class="wpmudev-switch-labels">

            <label class="wpmudev-switch-label" for="wph-shares-widget-animated"><?php _e( "Animate icons", Opt_In::TEXT_DOMAIN ); ?></label>

            <label class="wpmudev-helper"><?php _e( "Play an imagion when icon is on hover.", Opt_In::TEXT_DOMAIN ); ?></label>

        </div>

    </div>

</div>