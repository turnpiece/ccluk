<?php
// ERASE THIS FILE WHEN modal-error.php
// HAS BEEN PROPERLY TESTED AND WE'RE SURE
// NOTHING IS MISSING AND IS WORKING PERFECT.
?>

<script id="wpoi-error-list-modal-tpl" type="text/template">
	<div class="hustle-two">
		<div class="wpoi-complete-mask"></div>
		<div class="wpoi-complete-wrap row">
			<div class="col-xs-12">
				<section class="box">
					<div class="box-title">
						<h3>{{name}}<span class="wpoi-total-errors"><?php _e('Total {{total}} errors', Opt_In::TEXT_DOMAIN); ?></span></h3>
						<a href="#" aria-label="Close" class="wph-icon i-close inc-opt-close-error-list"></a>
					</div>

					<div class="box-content">
						<table class="wph-table wph-table--fixed wph-table-header" cellpadding="0" cellspacing="0"></table>
						<div class="wpoi-error-list-content">
							<table id="wpoi-error-list" class="wph-table wph-table--fixed" cellpadding="0" cellspacing="0"></table>
						</div>
						<div class="wpoi-error-list-footer">
							<span class="hustle-delete-logs-confirmation">
								<label class="wph-label--alt"><?php _e( 'Are you sure?', Opt_In::TEXT_DOMAIN ); ?></label>
								<button type="button" class="wph-button wph-button--small wph-button--filled wph-button--gray button-delete-logs"><?php _e( 'Yes', Opt_In::TEXT_DOMAIN ); ?></button>
								<button type="button" class="wph-button wph-button--small wph-button--filled wph-button--gray button-cancel-delete-logs"><?php _e( 'No', Opt_In::TEXT_DOMAIN ); ?></button>
							</span>
							<button type="button" class="wph-button wph-button--small wph-button--filled wph-button--gray button-clear-logs"><?php _e( 'Clear Logs', Opt_In::TEXT_DOMAIN ); ?></button>
							<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=export_error_logs&optin_id=' ) ); ?>{{optin_id}}&_wpnonce=<?php echo wp_create_nonce( 'optin_export_error_logs' ); ?>" class="wph-button wph-button--small wph-button--filled wph-button--gray button-download-csv"><?php _e( 'Export CSV', Opt_In::TEXT_DOMAIN ); ?></a>
						</div>
					</div>
				</section>
			</div>
		</div>
	</div>
</script>
<script id="wpoi-error-header-list-tpl" type="text/template">
	<tr>
		<# _.each( headers, function( v, k ) { #>
		<th class="column-{{v.name}}">{{v.label}}</th>
		<# }) #>
		<th class="column-error"><?php _e( 'Error', Opt_In::TEXT_DOMAIN ); ?></th>
		<th class="column-date"><?php _e( 'Date', Opt_In::TEXT_DOMAIN ); ?></th>
	</tr>
</script>
<script id="wpoi-error-list-tpl" type="text/template">
	<# _.each( module_fields, function( v, k ) { #>
		<td class="column-{{v.name}}">
			<# if (model[v.name] ) { #>
			{{ model[v.name] }}
			<# } #>
		</td>
	<# }) #>
	<td class="column-error">
		<# if ( model.error ) { #>
		<span class="dashicons dashicons-warning" title="{{model.error}}"></span>
		<# } #>
	</td>
	<td class="column-date">
		<# if( model.date ) { #>
		{{ model.date }}
		<# } #>
	</td>
</script>