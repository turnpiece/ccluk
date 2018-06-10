<script id="hustle-modal-error-tpl" type="text/template">

<div class="wpmudev-modal-mask"></div>

<div class="wpmudev-box-modal">

    <div class="wpmudev-box-head">

        <div class="wpmudev-box-reset">

            <h2>{{name}}</h2>

            <label class="wpmudev-helper"><?php _e( "Total errors", Opt_In::TEXT_DOMAIN ); ?></label>

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

        <div class="wpmudev-footer-clear">

            <button type="button" class="wpmudev-button wpmudev-button-clear-logs"><?php _e( "Clear Logs", Opt_In::TEXT_DOMAIN ); ?></button>

            <span class="hustle-delete-logs-confirmation wpmudev-hidden">

                <label><?php _e( "Are you sure?", Opt_In::TEXT_DOMAIN ); ?></label>

                <button type="button" class="wpmudev-button wpmudev-button-sm wpmudev-button-delete-logs"><?php _e( "Yes", Opt_In::TEXT_DOMAIN ); ?></button>

                <button type="button" class="wpmudev-button wpmudev-button-sm wpmudev-button-cancel-delete-logs"><?php _e( "No", Opt_In::TEXT_DOMAIN ); ?></button>

			</span>

        </div>

        <div class="wpmudev-footer-export">
            <a href="<?php echo wp_nonce_url( get_admin_url(null, 'admin-ajax.php?action=inc_optin_export_error_logs&id=__id&type=__type'  ), 'optin_export_error_logs' ) ?>" class="wpmudev-button wpmudev-button-blue button-download-csv" data-id="{{id}}" target="_blank"><?php _e("Export CSV", Opt_In::TEXT_DOMAIN); ?></a>
		</div>

    </div>

</div>

</script>

<script id="hustle-error-header-list-tpl" type="text/template">

    <# _.each( module_fields, function( v, k ) { #>

		<# if ( v.name !== 'submit' || v.label.toLowerCase() !== 'submit' ) { #>

			<div class="wpmudev-listing-col">{{v.label}}</div>

		<# } #>

    <# }) #>

    <div class="wpmudev-listing-error"><?php _e( "Error", Opt_In::TEXT_DOMAIN ); ?></div>

    <div class="wpmudev-listing-date"><?php _e( "Date", Opt_In::TEXT_DOMAIN ); ?></div>

</script>

<script id="hustle-error-list-tpl" type="text/template">

	<# if ( model !== null && typeof model !== 'undefined' ) {  #>

        <# _.each( module_fields, function( v, k ) {  #>

			<# if ( v.name !== 'submit' || v.label.toLowerCase() !== 'submit' ) { #>

				<div class="wpmudev-listing-col">

                    <p class="wpmudev-listing-title">{{v.label}}</p>

					<p class="wpmudev-listing-content"><# if (model[v.name] ) { #>{{ model[v.name] }}<# } else { #>–<# } #></p>

				</div>

			<# } #>

        <# }) #>

        <div class="wpmudev-listing-error">

            <p class="wpmudev-listing-title"><?php _e( "Error", Opt_In::TEXT_DOMAIN ); ?></p>

			<p class="wpmudev-listing-content">

                <# if ( model.error ) { #>

                    <span class="hustle-optin-error-text">{{model.error}}</span>

                    <span class="hustle-optin-error wpmudev-tip wpmudev-tip--big" data-tip="{{model.error}}"><?php $this->render("general/icons/admin-icons/icon-warning" ); ?></span>

                <# } else { #>

                    <span class="hustle-optin-success-text"><?php _e( "All good", Opt_In::TEXT_DOMAIN ); ?></span>

                    <span class="hustle-optin-error"><?php $this->render("general/icons/admin-icons/icon-checkmark" ); ?></span>

                <# } #>

            </p>

        </div>

        <div class="wpmudev-listing-date">

            <p class="wpmudev-listing-title"><?php _e( "Date", Opt_In::TEXT_DOMAIN ); ?></p>

		    <p class="wpmudev-listing-content"><# if( model.date ) { #>{{ model.date }}<# } else { #>–<# } #></p>

        </div>

	<# } #>

</script>