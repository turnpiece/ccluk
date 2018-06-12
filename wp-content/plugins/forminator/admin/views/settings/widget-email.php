<?php
$sender_email_address = get_global_sender_email_address();
$sender_name = get_global_sender_name();
?>

<div class="sui-box">

	<div class="sui-box-header">

		<h3 class="sui-box-title"><?php esc_html_e( "Email Settings", Forminator::DOMAIN ); ?></h3>

	</div>

	<div class="sui-box-body">

		<p><?php echo esc_html__( "If your SMTP configuration is not working with Forminator, try adding your SMTP email here.", Forminator::DOMAIN ); ?></p>

	</div>

	<table class="sui-table sui-accordion fui-table-exports">

		<tbody>

			<tr>

				<td><?php esc_html_e( "Sender name", Forminator::DOMAIN ); ?></td>

				<td><?php echo esc_html( $sender_name ); ?></td>

			</tr>

			<tr>

				<td><?php esc_html_e( "Sender email address", Forminator::DOMAIN ); ?></td>

				<td><?php echo esc_html( $sender_email_address ); ?></td>

			</tr>

		</tbody>

	</table>

	<div class="sui-box-footer">

		<button class="sui-button wpmudev-open-modal" data-modal="email_settings" data-nonce="<?php echo wp_create_nonce( 'forminator_load_popup_email_settings' ); // WPCS: XSS ok. ?>"><?php esc_html_e( "Edit Settings", Forminator::DOMAIN ); ?></button>

	</div>

</div>