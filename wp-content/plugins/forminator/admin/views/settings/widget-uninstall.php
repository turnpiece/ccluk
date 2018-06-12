<?php
$modules = get_option( "forminator_uninstall_clear_data", false );
?>

<div class="sui-box">

	<div class="sui-box-header">

		<h3 class="sui-box-title"><?php esc_html_e( "Uninstall Settings", Forminator::DOMAIN ); ?></h3>

	</div>

	<div class="sui-box-body">

		<div class="sui-notice sui-notice-sm sui-notice-warning">

			<p><?php echo sprintf( __( "This option allows you to delete or keep all your data when the plugin is deleted from the %splugins menu%s.", Forminator::DOMAIN ), '<a href="' . get_admin_url( null, 'plugins.php' ) . '">', '</a>' ); // phpcs:ignore ?></p>

		</div>

	</div>

	<table class="sui-table sui-accordion fui-table-exports">

		<tbody>

			<tr>

				<td><?php esc_html_e( "Delete data on uninstall", Forminator::DOMAIN ); ?></td>

				<td><?php echo $modules ? esc_html__( "Yes", Forminator::DOMAIN ) : esc_html__( "No", Forminator::DOMAIN ); ?></td>

			</tr>

		</tbody>

	</table>

	<div class="sui-box-footer">

		<button class="sui-button wpmudev-open-modal" data-modal="uninstall_settings" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_uninstall_form' ); // phpcs:ignore ?>"><?php esc_html_e( "Edit Settings", Forminator::DOMAIN ); ?></button>

	</div>

</div>