<?php
$path = forminator_plugin_dir();

$icon_close = $path . "assets/icons/admin-icons/close.php";
$hero_happy = $path . "assets/icons/forminator-icons/hero-happy.php";
$hero_face = $path . "assets/icons/forminator-icons/hero-face.php";

$count = $this->countModules();
?>

<section id="wpmudev-section">

	<?php if ( $count > 0 ) { ?>

		<form method="post" name="bulk-action-form" style="margin: 0 0 30px;">

			<?php wp_nonce_field( 'forminatorQuizFormRequest', 'forminatorNonce' ) ?>

			<input type="hidden" name="ids" value=""/>

			<div class="wpmudev-actions">

				<div class="wpmudev-action--bulk">

					<select class="wpmudev-select"  name="formninator_action">

						<option value=""><?php _e( "Bulk Actions", Forminator::DOMAIN ); ?></option>

						<?php $bulk_actions = $this->bulk_actions();
						foreach ( $bulk_actions as $action => $label ) { ?>

							<option value="<?php echo $action; ?>"><?php echo $label; ?></option>

						<?php } ?>

					</select>

					<button class="wpmudev-button wpmudev-button-ghost"><?php _e( "Apply", Forminator::DOMAIN ); ?></button>

				</div>

				<div class="wpmudev-action--page">

					<div class="wpmudev-page--resume"><p><?php if ( $count == 1 ) { printf( __( "%s result", Forminator::DOMAIN ), $count ); } else { printf( __( "%s results", Forminator::DOMAIN ), $count ); } ?></p></div>

					<?php $this->pagination(); ?>

				</div>

			</div>

		</form>

		<div class="wpmudev-entries wpmudev-listings">

			<div class="wpmudev-entries--header">

				<div class="wpmudev-entries--check">

					<div class="wpmudev-checkbox">
						<input type="checkbox" id="wpf-cform-check_all">
						<label for="wpf-cform-check_all" class="wpdui-icon wpdui-icon-check"></label>
					</div>

				</div>

				<div class="wpmudev-entries--text">

					<p class="wpmudev-entries--title"><?php _e( 'Quiz title', Forminator::DOMAIN ); ?></p>

					<p class="wpmudev-entries--subtitle"><?php _e( 'Shortcode', Forminator::DOMAIN ); ?></p>

					<div class="wpmudev-entries--data">

						<p class="wpmudev-entries--subtitle"><?php _e( 'Views', Forminator::DOMAIN ); ?></p>

						<p class="wpmudev-entries--subtitle"><?php _e( 'Entries', Forminator::DOMAIN ); ?></p>

						<p class="wpmudev-entries--title"><?php _e( 'Conversion rate', Forminator::DOMAIN ); ?></p>

					</div>

				</div>

				<div class="wpmudev-entries--menu" aria-hidden="true"></div>

			</div>

			<div class="wpmudev-entries--section">

				<?php $i = 0;
				foreach ( $this->getModules() as $module ) : $i ++; ?>

				<div id="forminator-entry-<?php echo $module['id']; ?>" class="wpmudev-entries--result">

					<div class="wpmudev-result--header">

						<div class="wpmudev-result--check">

							<div class="wpmudev-checkbox">

								<input type="checkbox" id="wpf-cform-module-<?php echo $i; ?>" value="<?php echo $module['id']; ?>">

								<label for="wpf-cform-module-<?php echo $i; ?>" class="wpdui-icon wpdui-icon-check" aria-hidden="true"></label>

							</div>

						</div>

						<div class="wpmudev-result--text">

							<p class="wpmudev-result--title"><a href="<?php echo $this->getAdminEditUrl( $module['type'], $module['id'] ) ?>"><?php echo forminator_get_form_name( $module['id'], 'quiz'); ?></a></p>

							<div class="wpmudev-result--subtitle">

								<p class="wpmudev-sr-only"><?php _e( "Quiz shortcode", Forminator::DOMAIN ); ?></p>

								<p>[forminator_quiz id="<?php echo $module['id']; ?>"]</p>

								<p class="wpmudev-hidden" aria-hidden="true"><?php _e( "Quiz shortcode", Forminator::DOMAIN ); ?></p>

							</div>

							<p class="wpmudev-result--data">

								<span class="wpmudev-result--subtitle"><?php echo $module["views"]; ?></span>

								<span class="wpmudev-result--subtitle"><?php echo $module["entries"]; ?></span>

								<span class="wpmudev-result--subtitle"><?php echo $this->getRate( $module ); ?>%</span>

							</p>

						</div>

						<div class="wpmudev-result--menu">

							<button class="wpmudev-button-action">

								<span class="wpmudev-icon--dots" aria-hidden="true"><span></span></span>

								<span class="wpmudev-sr-only"><?php _e( 'Open menu', Forminator::DOMAIN ); ?></span>

							</button>

							<ul class="wpmudev-menu wpmudev-hidden">
								<li>
									<a href="<?php echo $this->getAdminEditUrl( $module['type'], $module['id'] ) ?>">
										<?php _e( "Edit settings", Forminator::DOMAIN ); ?>
									</a>
								</li>
								<li>
									<form method="post">
										<input type="hidden" name="formninator_action" value="clone">
										<input type="hidden" name="id" value="<?php echo esc_attr( $module['id'] ) ?>"/>
										<?php wp_nonce_field( 'forminatorQuizFormRequest', 'forminatorNonce' ) ?>
										<button type="submit"><?php _e( "Clone quiz", Forminator::DOMAIN ); ?></button>
									</form>
								</li>
								<li>
									<a href="<?php echo admin_url( 'admin.php?page=forminator-quiz-view&form_id=' . $module['id'] ) ?>"><?php _e( "View entries", Forminator::DOMAIN ); ?></a>
								</li>
								<li>
									<a href="#" class="wpmudev-open-modal" data-modal="preview_quizzes" data-form-id="<?php echo $module['id']; ?>" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_preview_quizzes' ) ?>">
										<?php _e( "Preview quiz", Forminator::DOMAIN ); ?>
									</a>
								</li>
								<hr/>
								<li class="wpmudev-trash">
									<a href="#" class="wpmudev-open-modal" data-modal="delete-module" data-form-id="<?php echo $module['id']; ?>" data-nonce="<?php echo wp_create_nonce( 'forminatorQuizFormRequest' ) ?>"><?php _e( "Delete quiz", Forminator::DOMAIN ); ?></a>
								</li>

							</ul>

						</div>

					</div>

				</div>

				<?php endforeach; ?>

			</div>

		</div>

		<?php if ( $count > 10 ) { ?>

			<form method="post" name="bulk-action-form" style="margin: 30px 0 0;">

				<?php wp_nonce_field( 'forminatorQuizFormRequest', 'forminatorNonce' ) ?>

				<input type="hidden" name="ids" value=""/>

				<div class="wpmudev-actions">

					<div class="wpmudev-action--bulk">

						<select class="wpmudev-select"  name="formninator_action">

							<option value=""><?php _e( "Bulk Actions", Forminator::DOMAIN ); ?></option>

							<?php $bulk_actions = $this->bulk_actions();
							foreach ( $bulk_actions as $action => $label ) { ?>

								<option value="<?php echo $action; ?>"><?php echo $label; ?></option>

							<?php } ?>

						</select>

						<button class="wpmudev-button wpmudev-button-ghost"><?php _e( "Apply", Forminator::DOMAIN ); ?></button>

					</div>

					<div class="wpmudev-action--page">

						<div class="wpmudev-page--resume"><p><?php if ( $count == 1 ) { printf( __( "%s result", Forminator::DOMAIN ), $count ); } else { printf( __( "%s results", Forminator::DOMAIN ), $count ); } ?></p></div>

						<?php $this->pagination(); ?>

					</div>

				</div>

			</form>

		<?php } ?>

	<?php } else { ?>

		<div class="wpmudev-row">

			<div class="wpmudev-col col-12">

				<div id="forminator-dashboard-box--welcome" class="wpmudev-box wpmudev-box--hero">

					<div class="wpmudev-box-header">

						<div class="wpmudev-header--text">

							<h2 class="wpmudev-title"><?php printf( __( "Hello there, %s", Forminator::DOMAIN ), forminator_get_current_username() ); ?></h2>

						</div>

					</div>

					<div class="wpmudev-box-section">

						<div class="wpmudev-hero--image" aria-hidden="true">

							<div class="wpmudev-image--wrap wpmudev-image--desktop"><?php include( $hero_happy ); ?></div>
							<div class="wpmudev-image--wrap wpmudev-image--mobile"><?php include( $hero_face ); ?></div>

						</div>

						<div class="wpmudev-hero--text">

							<h2 class="wpmudev-title"><?php _e( "Start challenging your users!", Forminator::DOMAIN ); ?></h2>

							<p><?php _e( "Create fun quizzes for your users to take and share on social media. A great way to drive more traffic to your site.", Forminator::DOMAIN ); ?></p>

							<p><button class="wpmudev-button wpmudev-button-blue wpmudev-button-open-modal" data-modal="quizzes"><?php _e( "Create", Forminator::DOMAIN ); ?></button></p>

						</div>

					</div>

				</div><?php // .wpmudev-box ?>

			</div><?php // .wpmudev-col ?>

		</div><?php // .wpmudev-row ?>

	<?php } ?>

</section>