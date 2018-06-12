<?php
$path	= forminator_plugin_url();
$count	= Forminator_Form_Entry_Model::count_entries( $this->form_id );
?>

<?php if ( $count > 0 ) : ?>

	<div class="sui-row">

		<?php // Custom Votes ?>
		<div class="sui-col-md-6">

			<?php $custom_votes = $this->map_custom_votes(); ?>

			<div class="sui-box">

				<?php if ( ! empty( $custom_votes ) && count( $custom_votes ) > 0 ) { ?>

					<div class="sui-box-header">

						<h2 class="sui-box-title"><?php esc_html_e( "Custom Votes", Forminator::DOMAIN ); ?></h2>

					</div>

					<div id="forminator-polls-custo-votes" class="sui-box-body">

						<?php
						foreach ( $custom_votes as $element_id => $custom_vote ) {

							echo '<span class="sui-tag sui-tag-inactive">' . $this->get_field_title( $element_id ) . '</span>'; // WPCS: XSS ok.

							foreach ( $custom_vote as $answer => $vote ) {

								echo '<span class="sui-tag sui-tag-inactive">' . $answer . ': ' . $vote . '</span>'; // WPCS: XSS ok.

							}

						}
						?>

					</div>

				<?php } else { ?>

					<div class="sui-box-body sui-block-content-center">

						<img src="<?php echo $path . 'assets/img/forminator-disabled.png'; // WPCS: XSS ok. ?>"
							srcset="<?php echo $path . 'assets/img/forminator-disabled.png'; // WPCS: XSS ok. ?> 1x, <?php echo $path . 'assets/img/forminator-disabled@2x.png'; // WPCS: XSS ok. ?> 2x" alt="<?php esc_html_e( 'Forminator', Forminator::DOMAIN ); ?>"
							class="sui-image sui-image-center fui-image" />

						<div class="fui-limit-block-600 fui-limit-block-center">

							<h2><?php esc_html_e( "Custom Votes", Forminator::DOMAIN ); ?></h2>

							<p><?php esc_html_e( "You haven’t received any custom votes for this poll yet.", Forminator::DOMAIN ); ?></p>

						</div>

					</div>

				<?php } ?>

			</div>

		</div>

		<?php // Export Entries ?>
		<div class="sui-col-md-6">

			<div class="sui-box">

				<div class="sui-box-header">

					<h2 class="sui-box-title"><?php esc_html_e( "Export Settings", Forminator::DOMAIN ); ?></h2>

				</div>

				<div class="sui-box-body">

					<p><?php esc_html_e( "You can do manual exports or schedule automatic exports and receive them on your mailbox.", Forminator::DOMAIN ); ?></p>

				</div>

				<table class="sui-table sui-accordion fui-table-exports">

					<tbody>

						<tr>

							<td><?php esc_html_e( "Manual Exports", Forminator::DOMAIN ); ?></td>

							<td><form class="wpmudev-export--form" method="post">
                                <input type="hidden" name="forminator_export" value="1">
                                <input type="hidden" name="form_id" value="<?php echo esc_attr( $this->form_id ); ?>">
                                <input type="hidden" name="form_type" value="poll">
								<?php wp_nonce_field( 'forminator_export', '_forminator_nonce' ); ?>
								<button class="sui-button sui-button-primary"><?php esc_html_e( "Download", Forminator::DOMAIN ); ?></button>
                            </form></td>

						</tr>

						<tr>

							<td><?php esc_html_e( "Scheduled Exports", Forminator::DOMAIN ); ?></td>

							<td><a href="/" class="sui-button wpmudev-open-modal" data-modal="exports-schedule"><?php esc_html_e( "Edit", Forminator::DOMAIN ); ?></a></td>

					</tbody>

				</table>

			</div>

		</div>

	</div>

	<div class="sui-box">

		<div class="sui-box-body sui-block-content-center">

			<?php if ( ! empty( $this->get_poll_question() ) ) { ?>

				<h2><?php echo $this->get_poll_question(); // WPCS: XSS ok. ?></h2>

			<?php } ?>

			<?php if ( ! empty( $this->get_poll_description() ) ) { ?>

				<p><?php echo $this->get_poll_description(); // WPCS: XSS ok. ?></p>

			<?php } ?>

			<div id="forminator-chart-poll" class="forminator-poll--chart" style="width: 100%; height: 400px;"></div>

		</div>

	</div>

<?php else : ?>

	<div class="sui-box">

		<div class="sui-box-body sui-block-content-center">

			<img src="<?php echo $path . 'assets/img/forminator-submissions.png'; // WPCS: XSS ok. ?>"
				srcset="<?php echo $path . 'assets/img/forminator-submissions.png'; // WPCS: XSS ok. ?> 1x, <?php echo $path . 'assets/img/forminator-submissions@2x.png'; // WPCS: XSS ok. ?> 2x" alt="<?php esc_html_e( 'Forminator', Forminator::DOMAIN ); ?>"
				class="sui-image sui-image-center fui-image" />

			<h2><?php echo forminator_get_form_name( $this->form_id, 'poll'); // WPCS: XSS ok. ?></h2>

			<p class="fui-limit-block-600 fui-limit-block-center"><?php esc_html_e( "You haven’t received any submissions for this poll yet. When you do, you’ll be able to view all the data here.", Forminator::DOMAIN ); ?></p>

		</div>

	</div>

<?php
endif;
?>