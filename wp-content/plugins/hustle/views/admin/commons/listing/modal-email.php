<script id="hustle-modal-email-tpl" type="text/template">

    <div class="wpmudev-modal-mask"></div>

    <div class="wpmudev-box-modal">

        <div class="wpmudev-box-head">

            <div class="wpmudev-box-reset">

                <h2>{{name}}</h2>

                <label class="wpmudev-helper"><?php _e( "Total {{total}} subscriptions", Opt_In::TEXT_DOMAIN ); ?></label>

            </div>

            <?php $this->render("general/icons/icon-close" ); ?>

        </div>

        <div class="wpmudev-box-body">

            <div class="wpmudev-listing">

                <div class="wpmudev-listing-head" aria-hidden="true">

                </div><?php // .wpmudev-listing-head ?>

                <div class="wpmudev-listing-body">

                </div><?php // .wpmudev-listing-body ?>

            </div>

        </div>

        <div class="wpmudev-box-footer">

            <a href="<?php echo wp_nonce_url( get_admin_url(null, 'admin-ajax.php?action=inc_optin_export_subscriptions&id=__id&type=__type'  ), 'inc_optin_export_subscriptions' ) ?>" class="wpmudev-button wpmudev-button-blue button-export-csv" data-id="{{id}}" target="_blank"><?php _e("Export CSV", Opt_In::TEXT_DOMAIN); ?></a>

        </div>

    </div>

</script>

<script id="wpoi-email-list-header-tpl" type="text/template">

    <# _.each( module_fields, function( field ) { #>

		<# if ( field.name !== 'submit' || field.label.toLowerCase() !== 'submit' ) { #>

			<div class="wpmudev-listing-col">{{field.label}}</div>

		<# } #>

    <# }) #>

</script>

<script id="wpoi-emails-list-tpl" type="text/template">


        <# if ( subscriptions.length ) {

            _.each( subscriptions, function( sub ) {

				if ( sub !== null && typeof sub !== 'undefined' ) { #>

					<div class="wpmudev-listing-row">

						<# _.each( module_fields, function( field ) {

							if ( field.name !== 'submit' || field.label.toLowerCase() !== 'submit' ) { #>

								<div class="wpmudev-listing-col">

									<p class="wpmudev-listing-title">{{field.label}}</p>

									<p class="wpmudev-listing-content"><# if ( typeof sub[field.name] !== 'undefined' && sub[field.name]) { #>{{ sub[field.name] }}<# } else { #>–<# } #></p>

								</div>

							<# }

						}); #>

					</div>

            <# }
			});

        } #>

</script>