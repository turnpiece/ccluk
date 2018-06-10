<div id="wph-wizard-design-widget" class="wpmudev-box-content last">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Widget / Shortcode", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "Style the widget & shortcode module.", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

    <div class="wpmudev-box-right">

        <?php $this->render( "admin/sshare/wizard/boxes/box-widget_colors", array() ); ?>

        <?php $this->render( "admin/sshare/wizard/boxes/box-widget_shadow", array() ); ?>

        <# if ( service_type === 'native' && _.isTrue(click_counter) ) { #>

        <?php $this->render( "admin/sshare/wizard/boxes/box-widget_inline_count", array() ); ?>

        <# } #>

        <?php $this->render( "admin/sshare/wizard/boxes/box-widget_animate", array() ); ?>

        <?php $this->render( "admin/sshare/wizard/boxes/box-widget_preview", array() ); ?>

    </div>

</div>