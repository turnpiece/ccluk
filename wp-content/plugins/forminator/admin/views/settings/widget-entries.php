<?php
$entries = get_option( "forminator_pagination_entries", 10 );
?>

<div class="wpmudev-box wpmudev-can--hide">

	<div class="wpmudev-box-header">

		<div class="wpmudev-header--text">

			<h2 class="wpmudev-subtitle"><?php _e( "Entries Page", Forminator::DOMAIN ); ?></h2>

		</div>

		<div class="wpmudev-header--action">

			<button class="wpmudev-box--action">

                <span class="wpmudev-icon--plus" aria-hidden="true"></span>

                <span class="wpmudev-sr-only"><?php _e( "Hide box", Forminator::DOMAIN ); ?></span>

            </button>

		</div>

	</div>

	<div class="wpmudev-box-section">

		<div class="wpmudev-section--table">

			<table class="wpmudev-table">

				<thead>

					<tr><th colspan="2"><?php _e( "Pagination Settings", Forminator::DOMAIN ); ?></th></tr>

				</thead>

				<tbody>

					<tr>

						<th><p class="wpmudev-table--text"><?php _e( "Limit entries per page:", Forminator::DOMAIN ); ?></p></th>

                        <td><p class="wpmudev-table--text" style="text-align: left"><?php echo $entries; ?></p></td>

                    </tr>

				</tbody>

				<tfoot>

                    <tr>

                        <td colspan="2"><div class="wpmudev-table--text">
							<button class="wpmudev-button wpmudev-button-sm wpmudev-button-blue wpmudev-open-modal" data-modal="pagination_entries" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_pagination_entries' ) ?>"><?php _e( "Edit Settings", Forminator::DOMAIN ); ?></button>
						</td>

                    </tr>

                </tfoot>

			</table>

		</div>

	</div>

</div>