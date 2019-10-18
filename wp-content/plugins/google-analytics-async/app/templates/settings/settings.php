<?php

/**
 * Admin settings page template.
 *
 * @var string $title    Optional.
 * @var string $form_url Form url.
 * @var string $tab      Current tab.
 *
 * @since 3.2.0
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Views\Settings;

?>

<div class="sui-row-with-sidenav">

	<?php Settings::instance()->sidenav(); // Render settings side nav. ?>

	<div class="sui-box">

		<form method="post" action="<?php echo esc_url( $form_url ); ?>" id="beehive_settings_form">

			<?php wp_nonce_field( 'beehive_settings_nonce' ); // Nonce field. ?>

			<input type="hidden" name="beehive_settings_form" value="1">

			<div class="sui-box-header">
				<h2 class="sui-box-title"><?php echo empty( $title ) ? esc_html__( 'Settings', 'ga_trans' ) : esc_html( $title ); ?></h2>
			</div>

			<div class="sui-box-body beehive-settings-tab-<?php echo esc_attr( $tab ); ?>">
				<?php
				/**
				 * Action hook to render settings template content.
				 *
				 * @since 3.2.0
				 */
				do_action( 'beehive_settings_page_content' );
				?>
			</div>

			<div class="sui-box-footer">

				<div class="sui-actions-right">
					<button type="submit" class="sui-button sui-button-blue" id="beehive-settings-submit">
						<i class="sui-icon-save" aria-hidden="true"></i>
						<?php esc_html_e( 'Save Changes', 'ga_trans' ); ?>
					</button>
				</div>

			</div>

		</form>
	</div>
</div>