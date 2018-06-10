<div id="wph-wizard-design-floating" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Floating social", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "Style the floating social module.", Opt_In::TEXT_DOMAIN ); ?></label>

        <?php $this->render( "admin/sshare/wizard/boxes/box-floating_preview", array() ); ?>

	</div>

    <div class="wpmudev-box-right">

        <?php $this->render( "admin/sshare/wizard/boxes/box-floating_colors", array() ); ?>

        <?php $this->render( "admin/sshare/wizard/boxes/box-floating_shadow", array() ); ?>

        <# if ( service_type === 'native' && _.isTrue(click_counter) ) { #>

        <?php $this->render( "admin/sshare/wizard/boxes/box-floating_inline_count", array() ); ?>

        <# } #>

        <?php $this->render( "admin/sshare/wizard/boxes/box-floating_animate", array() ); ?>

    </div>

</div>