<div id="wph-wizard-design-css_holder" class="wpmudev-box-content {{ ( _.isTrue(customize_css) ) ? 'wpmudev-show' : 'wpmudev-hidden' }}">

    <div class="wpmudev-box-full">

        <div class="wpmudev-box-gray">

            <label class="wpmudev-annotation"><?php _e('Available CSS Selectors (click to add):', Opt_In::TEXT_DOMAIN); ?></label>

			<div class="wpmudev-css-selectors">

				<# _.each( stylables, function( name, stylable ) { #>
					<a href="#" class="wpmudev-css-stylable" data-stylable="{{stylable}}" >{{name}}</a>
				<# }); #>

			</div>

			<div style="height:210px;" id="hustle_custom_css" data-nonce="<?php echo wp_create_nonce('hustle_module_prepare_custom_css'); ?>">{{custom_css}}</div>

        </div>

    </div>

</div><?php // #wph-wizard-design-css_holder ?>