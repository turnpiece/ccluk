<?php
$polls_retain_number = get_option( 'forminator_retain_votes_interval_number', 0 );
$polls_retain_unit   = get_option( 'forminator_retain_votes_interval_unit', 'days' );

$cform_retain_number             = get_option( 'forminator_retain_submissions_interval_number', 0 );
$cfrom_retain_unit               = get_option( 'forminator_retain_submissions_interval_unit', 'days' );
$form_submission_erasure_enabled = get_option( 'forminator_enable_erasure_request_erase_form_submissions', false );

$polls_retain_unit = ucfirst( $polls_retain_unit );
if ( empty( $polls_retain_number ) ) {
	$polls_retain_number = __( 'Forever', Forminator::DOMAIN );
	$polls_retain_unit   = '';
}

$cfrom_retain_unit = ucfirst( $cfrom_retain_unit );
if ( empty( $cform_retain_number ) ) {
	$cform_retain_number = __( 'Forever', Forminator::DOMAIN );
	$cfrom_retain_unit   = '';
}

?>

<div class="sui-box">

	<div class="sui-box-header">

		<h3 class="sui-box-title"><?php esc_html_e( "Privacy Settings", Forminator::DOMAIN ); ?></h3>

	</div>

	<table class="sui-table sui-accordion fui-table-exports">
		<thead class="fui-thead-gray">
			<tr>
				<th colspan="2"><?php esc_html_e( "Forms", Forminator::DOMAIN ); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td><?php esc_html_e( "Account erasure requests", Forminator::DOMAIN ); ?></td>
				<td><?php echo esc_html( $form_submission_erasure_enabled ? 'Remove Form Submissions' : 'Retain Form Submissions' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( "Submissions retention", Forminator::DOMAIN ); ?></td>
				<td><?php echo esc_html( $cform_retain_number . ' ' . $cfrom_retain_unit ); ?></td>
			</tr>
		</tbody>

	</table>

	<table class="sui-table sui-accordion fui-table-exports">
		<thead class="fui-thead-gray">
			<tr>
				<th colspan="2"><?php esc_html_e( "Polls", Forminator::DOMAIN ); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td><?php esc_html_e( "IP address retention", Forminator::DOMAIN ); ?></td>
				<td><?php echo esc_html( $polls_retain_number . ' ' . $polls_retain_unit ); ?></td>
			</tr>
		</tbody>

	</table>

	<div class="sui-box-footer">
		<button class="sui-button wpmudev-open-modal" data-modal="privacy_settings"
		        data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_popup_privacy_settings' ) ); ?>">
			<?php esc_html_e( "Edit Settings", Forminator::DOMAIN ); ?></button>
	</div>

</div>