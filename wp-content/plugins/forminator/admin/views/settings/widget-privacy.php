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

<div class="wpmudev-box wpmudev-can--hide">

	<div class="wpmudev-box-header">

		<div class="wpmudev-header--text">

			<h2 class="wpmudev-subtitle"><?php esc_html_e( "Privacy Settings", Forminator::DOMAIN ); ?></h2>

		</div>

		<div class="wpmudev-header--action">

			<button class="wpmudev-box--action">

				<span class="wpmudev-icon--plus" aria-hidden="true"></span>

				<span class="wpmudev-sr-only"><?php esc_html_e( "Hide box", Forminator::DOMAIN ); ?></span>

			</button>

		</div>

	</div>

	<div class="wpmudev-box-section">

		<div class="wpmudev-section--table">

			<table class="wpmudev-table">

				<thead>

				<tr>
					<th colspan="2"><?php esc_html_e( "Forms", Forminator::DOMAIN ); ?></th>
				</tr>

				</thead>

				<tbody>

				<tr>

					<th><p class="wpmudev-table--text"><?php esc_html_e( "Account erasure requests :", Forminator::DOMAIN ); ?></p></th>

					<td><p class="wpmudev-table--text" style="text-align: left">
							<?php echo esc_html( $form_submission_erasure_enabled ? 'Remove Form Submissions' : 'Retain Form Submissions' ); ?>
						</p></td>

				</tr>
				<tr>

					<th><p class="wpmudev-table--text"><?php esc_html_e( "Submissions retention :", Forminator::DOMAIN ); ?></p></th>

					<td><p class="wpmudev-table--text" style="text-align: left">
							<?php echo esc_html( $cform_retain_number . ' ' . $cfrom_retain_unit ); ?>
						</p></td>

				</tr>

				</tbody>


			</table>

			<table class="wpmudev-table">

				<thead>

				<tr>
					<th colspan="2"><?php esc_html_e( "Polls", Forminator::DOMAIN ); ?></th>
				</tr>

				</thead>

				<tbody>
				<tr>

					<th><p class="wpmudev-table--text"><?php esc_html_e( "IP address retention :", Forminator::DOMAIN ); ?></p></th>

					<td><p class="wpmudev-table--text" style="text-align: left">
							<?php echo esc_html( $polls_retain_number . ' ' . $polls_retain_unit ); ?>
						</p></td>

				</tr>

				</tbody>


			</table>

			<table class="wpmudev-table">
				<tfoot>

				<tr>

					<td colspan="2">

						<button class="wpmudev-button wpmudev-button-sm wpmudev-button-blue wpmudev-open-modal" data-modal="privacy_settings"
						        data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_popup_privacy_settings' ) ); ?>">
							<?php esc_html_e( "Edit Settings", Forminator::DOMAIN ); ?></button>

					</td>

				</tr>

				</tfoot>
			</table>

		</div>

	</div>

</div>