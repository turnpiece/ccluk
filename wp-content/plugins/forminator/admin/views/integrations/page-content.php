<?php $path = forminator_plugin_url(); ?>

<p><?php esc_html_e( "Forminator integrates with your favourite email and storage apps. Here’s a list of the currently available apps, you can configure them in your Form / Integrations area.", Forminator::DOMAIN ); ?></p>

<?php
if ( ! empty( $addons['connected'] ) ) {
	?>

	<h3 class="sui-table-title"><?php esc_html_e( "Active", Forminator::DOMAIN ); ?></h3>

	<table class="sui-table fui-integrations-table">

		<tbody>

		<?php foreach ( $addons['connected'] as $key => $provider ) : ?>

			<?php echo forminator_addon_row_html_markup( $provider, 0, true );// wpcs xss ok. ?>

		<?php endforeach; ?>

		</tbody>

	</table>

	<?php
}

if ( ! empty( $addons['not_connected'] ) ) {
	?>

	<h3 class="sui-table-title"><?php esc_html_e( "Available Integrations", Forminator::DOMAIN ); ?></h3>

	<table class="sui-table fui-integrations-table">

		<tbody>

		<?php foreach ( $addons['not_connected'] as $key => $provider ) : ?>

			<?php echo forminator_addon_row_html_markup( $provider, 0, true );// wpcs xss ok. ?>

		<?php endforeach; ?>

		</tbody>

	</table>

	<?php
}
?>