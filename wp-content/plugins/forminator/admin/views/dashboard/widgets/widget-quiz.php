<div class="sui-box">

	<div class="sui-box-header">

		<h3 class="sui-box-title"><i class="sui-icon-academy" aria-hidden="true"></i><?php esc_html_e( "Quizzes", Forminator::DOMAIN ); ?></h3>

	</div>

	<?php if ( forminator_quizzes_total() > 0 ) { ?>

		<table class="fui-table fui-table-accordion">

			<tbody>

				<?php foreach( forminator_quizzes_modules() as $module ) { ?>

					<tr>

						<td class="fui-cell-title"><?php echo forminator_get_form_name( $module['id'], 'quiz'); // WPCS: XSS ok. ?></td>

						<td class="fui-table-action">
							<a href="<?php echo forminator_quiz_get_edit_url( $module, $module['id'] ); // WPCS: XSS ok. ?>"
								class="sui-button-icon sui-tooltip sui-tooltip-top-left"
								data-tooltip="<?php esc_html_e( 'Edit quiz settings', Forminator::DOMAIN ); ?>"><i class="sui-icon-widget-settings-config" aria-hidden="true"></i></a>
						</td>

					</tr>

				<?php } ?>

			</tbody>

		</table>

	<?php } else { ?>

		<div class="sui-box-body">

			<p><?php esc_html_e( "Create fun quizzes for your users to take and share on social media. A great way to drive more traffic to your site.", Forminator::DOMAIN ); ?></p>

			<p><button class="sui-button sui-button-blue wpmudev-open-modal" data-modal="quizzes"><?php esc_html_e( "Create Quiz", Forminator::DOMAIN ); ?></button></p>

		</div>

	<?php } ?>

	<?php if ( forminator_quizzes_total() > 0 ) { ?>

		<div class="sui-box-footer">

			<div class="fui-action-buttons">

				<a href="<?php echo forminator_get_admin_link( 'forminator-quiz' ); // WPCS: XSS ok. ?>" class="sui-button sui-button-ghost"><i class="sui-icon-eye" aria-hidden="true"></i> <?php esc_html_e( "View All", Forminator::DOMAIN ); ?></a>

				<button href="/" class="sui-button sui-button-blue wpmudev-open-modal" data-modal="quizzes"><?php esc_html_e( "Create Quiz", Forminator::DOMAIN ); ?></button>

			</div>

		</div>

	<?php } ?>

</div>