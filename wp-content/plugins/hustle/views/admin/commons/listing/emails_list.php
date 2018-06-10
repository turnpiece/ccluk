<?php
// ERASE THIS FILE WHEN modal-email.php
// HAS BEEN PROPERLY TESTED AND WE'RE SURE
// NOTHING IS MISSING AND IS WORKING PERFECT.
?>

<script id="wph-modal-email-tpl" type="text/template">

	<div class="hustle-two">

		<div class="wpoi-complete-mask"></div>

		<div class="wpoi-complete-wrap row">

			<div class="col-xs-12">

				<section class="box">

					<div class="box-title">

						<h3>{{name}}<span class="wpoi-total-subscribers"><?php _e('Total {{total}} subscriptions', Opt_In::TEXT_DOMAIN); ?></span></h3>

						<a href="#" aria-label="Close" class="wph-icon i-close inc-opt-close-emails-list"></a>

					</div>

					<div class="box-content">
						<div class="wpoi-emails-list">

							<table class="wph-table wph-table--fixed wpoi-emails-list-header" cellpadding="0" cellspacing="0"></table>

							<div id="wpoi-emails-list-content"></div>

							<div class="wpoi-emails-list-footer">

								<a href="<?php echo wp_nonce_url( get_admin_url(null, 'admin-ajax.php?action=inc_optin_export_subscriptions&id=__id'  ), 'inc_optin_export_subscriptions' ) ?>" class="wph-button wph-button--small wph-button--filled wph-button--gray button-export-csv" data-id="{{id}}" target="_blank"><?php _e("Export CSV", Opt_In::TEXT_DOMAIN); ?></a>

							</div>

						</div>

					</div>

				</section>

			</div>

		</div>

	</div>

</script>

<script id="wpoi-email-list-header-tpl" type="text/template">
	<thead>
		<# _.each( module_fields, function( field ) { #>
			<th>
				<div class="wpoi-email-list-field wpoi-list-{{field.name}}">{{field.label}}</div>
			</th>
		<# }) #>
	</thead>
</script>

<script id="wpoi-emails-list-tpl" type="text/template">
	<table class="wph-table wph-table--fixed" cellpadding="0" cellspacing="0">
	<# if ( subscriptions.length ) {
		_.each( subscriptions, function( sub ) { #>
			<tr>
				<# _.each( module_fields, function( field ) {
							// Check for legacy name fields
							var name_field = field.name;

							if ( sub.f_name && 'first_name' === name_field ) name_field = 'f_name';
							if ( sub.l_name && 'last_name' === name_field ) name_field = 'l_name';
							#>
					<td>
						<div class="wpoi-email-list-field wpoi-list-{{name_field}}" data-title="{{field.label}}">{{sub[name_field]}}</div>
					</td>
				<# }) #>
			</tr>
	<# }) } #>
	</table>
</script>