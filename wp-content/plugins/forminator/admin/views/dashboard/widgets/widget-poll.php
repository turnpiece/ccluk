<div class="sui-box">

	<div class="sui-box-header">

		<h3 class="sui-box-title"><i class="sui-icon-graph-bar" aria-hidden="true"></i><?php esc_html_e( "Polls", Forminator::DOMAIN ); ?></h3>

	</div>

	<?php if ( forminator_polls_total() > 0 ) { ?>

		<table class="fui-table fui-table-accordion">

			<tbody>

				<?php foreach( forminator_polls_modules() as $module ) { ?>

					<tr>

						<td class="fui-cell-title"><?php echo forminator_get_form_name( $module['id'], 'poll'); // WPCS: XSS ok. ?></td>

						<td class="fui-table-action"><a href="<?php echo admin_url( 'admin.php?page=forminator-poll-wizard&id=' . $module['id'] ); // WPCS: XSS ok. ?>"
							class="sui-button-icon sui-tooltip sui-tooltip-top-left"
							data-tooltip="<?php esc_html_e( 'Edit poll settings', Forminator::DOMAIN ); ?>"><i class="sui-icon-widget-settings-config" aria-hidden="true"></i></a>
						</td>

					</tr>

				<?php } ?>

			</tbody>

		</table>

	<?php } else { ?>

		<div class="sui-box-body">

			<p><?php esc_html_e( "Create polls, and collect user data. Choose a visualization style that best suits your needs.", Forminator::DOMAIN ); ?></p>

			<p><button href="/" class="sui-button sui-button-blue wpmudev-open-modal" data-modal="polls"><?php esc_html_e( "Create Poll", Forminator::DOMAIN ); ?></button></p>

		</div>

	<?php } ?>

	<?php if ( forminator_polls_total() > 0 ) { ?>

		<div class="sui-box-footer">

			<div class="fui-action-buttons">

				<a href="<?php echo forminator_get_admin_link( 'forminator-poll' ); // WPCS: XSS ok. ?>" class="sui-button sui-button-ghost"><i class="sui-icon-eye" aria-hidden="true"></i> <?php esc_html_e( "View All", Forminator::DOMAIN ); ?></a>

				<button href="/" class="sui-button sui-button-blue wpmudev-open-modal" data-modal="polls"><?php esc_html_e( "Create Poll", Forminator::DOMAIN ); ?></button>

			</div>

		</div>

	<?php } ?>

</div>