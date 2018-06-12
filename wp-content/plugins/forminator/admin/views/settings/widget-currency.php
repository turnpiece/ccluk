<?php
$icon_minus   = forminator_plugin_dir() . "assets/icons/admin-icons/minus.php";
$currency     = get_option( "forminator_currency", "USD" );
?>
<div class="wpmudev-box wpmudev-can--hide">

	<div class="wpmudev-box-header">

		<div class="wpmudev-header--text">

			<h2 class="wpmudev-subtitle"><?php esc_html_e( "Currency", Forminator::DOMAIN ); ?></h2>

		</div>

		<div class="wpmudev-header--action">

			<button class="wpmudev-box--action">

				<span class="wpmudev-icon--plus" aria-hidden="true"></span>

				<span class="wpmudev-sr-only"><?php esc_html_e( "Hide box", Forminator::DOMAIN ); ?></span>

			</button>

		</div>

	</div>

	<div class="wpmudev-box-section">
		<div class="wpmudev-section--text">

			<label class="wpmudev-label--notice"><span><?php esc_html_e( "Please note this is the currency that will be used for all your product fields.", Forminator::DOMAIN ); ?></label>

			<table class="wpmudev-table">
				<thead>

					<tr><th colspan="2"><?php esc_html_e( "Product Currency", Forminator::DOMAIN ); ?></th></tr>

				</thead>
				<tbody>
					<tr>

						<th>
							<p class="wpmudev-table--text" style="text-align: left"><?php esc_html_e( "Currency:", Forminator::DOMAIN ); ?></p>
						</th>

						<td style="padding-bottom: 0;padding-top: 0;">
							<p class="wpmudev-table--text" style="text-align: left"><?php echo esc_html( $currency ); ?></p>
						</td>

					</tr>
				</tbody>
				<tfoot>

                    <tr>
                        <td colspan="2">
                            <div class="wpmudev-table--text"><button class="wpmudev-button wpmudev-button-sm wpmudev-button-blue wpmudev-open-modal" data-modal="currency" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_currency' ); // WPCS: XSS ok. ?>"><?php esc_html_e( "Change currency", Forminator::DOMAIN ); ?></button></div>
                        </td>
                    </tr>

                </tfoot>
			</table>
		</div>
	</div>

</div>