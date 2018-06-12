<?php
$modules = get_option( "forminator_pagination_listings", 10 );
$submissions = get_option( "forminator_pagination_entries", 10 );
?>

<div class="sui-box">

	<div class="sui-box-header">

		<h3 class="sui-box-title"><?php esc_html_e( "Pagination Settings", Forminator::DOMAIN ); ?></h3>

	</div>

	<table class="sui-table sui-accordion fui-table-exports">

		<tbody>

			<tr>

				<td><?php esc_html_e( "Listings", Forminator::DOMAIN ); ?></td>

				<td><?php printf( __( "%s per page", Forminator::DOMAIN ), $modules); // WPCS: XSS ok. ?></td>

				<td><button class="sui-button wpmudev-open-modal" data-modal="pagination_listings" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_pagination_listings' ); // WPCS: XSS ok. ?>"><?php esc_html_e( "Edit", Forminator::DOMAIN ); ?></button></td>

			</tr>

			<tr>

				<td><?php esc_html_e( "Submissions", Forminator::DOMAIN ); ?></td>

				<td><?php printf( esc_html__( "%s per page", Forminator::DOMAIN ), $submissions ); // WPCS: XSS ok. ?></td>

				<td><button class="sui-button wpmudev-open-modal" data-modal="pagination_entries" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_pagination_entries' ); // WPCS: XSS ok. ?>"><?php esc_html_e( "Edit", Forminator::DOMAIN ); ?></button></td>

			</tr>

		</tbody>

	</table>

</div>