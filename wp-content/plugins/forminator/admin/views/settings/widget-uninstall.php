<?php
$modules = get_option( "forminator_uninstall_clear_data", false );
?>

<div class="wpmudev-box wpmudev-can--hide">

	<div class="wpmudev-box-header">

		<div class="wpmudev-header--text">

			<h2 class="wpmudev-subtitle"><?php _e( "Uninstall Settings", Forminator::DOMAIN ); ?></h2>

		</div>

		<div class="wpmudev-header--action">

			<button class="wpmudev-box--action">

                <span class="wpmudev-icon--plus" aria-hidden="true"></span>

                <span class="wpmudev-sr-only"><?php _e( "Hide box", Forminator::DOMAIN ); ?></span>

            </button>

		</div>

	</div>

	<div class="wpmudev-box-section">

		<div class="wpmudev-section--text">

			<label class="wpmudev-label--notice"><span><?php echo sprintf( __( "This option allows you to delete or keep all your data when the plugin is deleted from the %splugins menu%s",
                                                                               Forminator::DOMAIN ), '<a href="' . get_admin_url( null, 'plugins.php' ) . '">', '</a>' ); ?></label>

		</div>

		<div class="wpmudev-section--table">

			<table class="wpmudev-table">


				<tbody>

					<tr>

						<th><p class="wpmudev-table--text"><?php _e( "Delete data  on uninstall :", Forminator::DOMAIN ); ?></p></th>

                        <td><p class="wpmudev-table--text" style="text-align: left"><?php echo $modules ? __( "Yes", Forminator::DOMAIN ) : __( "No", Forminator::DOMAIN ) ; ?></p></td>

                    </tr>

				</tbody>

				<tfoot>

                    <tr>

                        <td colspan="2">

							<button class="wpmudev-button wpmudev-button-sm wpmudev-button-blue wpmudev-open-modal" data-modal="uninstall_settings" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_uninstall_form' ) ?>"><?php _e( "Edit Settings", Forminator::DOMAIN ); ?></button>

						</td>

                    </tr>

                </tfoot>

			</table>

		</div>

	</div>

</div>