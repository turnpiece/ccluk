<?php
/**
 * @var $this Hustle_Slidein_Admin
 * @var $module Hustle_Module_Model
 * @var $new_module Hustle_Module_Model
 */
?>

<?php if ( count( $slideins ) === 0 ) { ?>

	<?php $this->render("admin/slidein/welcome", array(
        'new_url' => $add_new_url,
        'user_name' => $user_name
    )); ?>

<?php } else { ?>

	<main id="wpmudev-hustle" class="wpmudev-ui wpmudev-hustle-listings-view">

		<header id="wpmudev-hustle-title" class="wpmudev-has-button">

			<h1><?php _e( "Slide-ins Dashboard", Opt_In::TEXT_DOMAIN ); ?></h1>

			<a class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost" href="<?php echo esc_url( $add_new_url ); ?>"><?php _e('New Slide-in', Opt_In::TEXT_DOMAIN); ?></a>

		</header>

		<section id="wpmudev-hustle-content">

			<div class="wpmudev-row">

				<div class="wpmudev-col col-12">

					<div class="wpmudev-list">

						<div class="wpmudev-list--action">

							<div class="wpmudev-action--left">

								<select id="wpmudev-bulk-action" class="wpmudev-select">

									<option value=""><?php _e( "Bulk Actions", Opt_In::TEXT_DOMAIN ); ?></option>
									<option value="delete" data-nonce="<?php echo wp_create_nonce('hustle_delete_module'); ?>" ><?php _e( "Delete", Opt_In::TEXT_DOMAIN ); ?></option>

								</select>

								<button id="wpmudev-bulk-action-button" class="wpmudev-button wpmudev-button-ghost"><?php _e( "Apply", Opt_In::TEXT_DOMAIN ); ?></button>

							</div>

							<div class="wpmudev-action--right">

								<?php
								$count = count( $slideins );

								if ( $count > 1 ) {
									$count_text = __("results", Opt_In::TEXT_DOMAIN);
								} else {
									$count_text = __("result", Opt_In::TEXT_DOMAIN);
								} ?>

								<p><?php printf( "%s %s", $count, $count_text ); ?></p>

							</div>

						</div>

						<div class="wpmudev-list--header">

							<div class="wpmudev-header--check">

								<div class="wpmudev-input_checkbox">

									<input id="wph-all-slideins" type="checkbox">

									<label for="wph-all-slideins" class="wpdui-fi wpdui-fi-check"></label>

								</div>

							</div>

							<div class="wpmudev-header--name"><?php _e( "Slide-in title", Opt_In::TEXT_DOMAIN ); ?></div>

							<div class="wpmudev-header--email"><?php _e( "Email service", Opt_In::TEXT_DOMAIN ); ?></div>

							<div class="wpmudev-header--conditions"><?php _e( "Display conditions", Opt_In::TEXT_DOMAIN ); ?></div>

							<div class="wpmudev-header--views"><?php _e( "Views", Opt_In::TEXT_DOMAIN ); ?></div>

							<div class="wpmudev-header--conversions"><?php _e( "Conversions", Opt_In::TEXT_DOMAIN ); ?></div>

							<div class="wpmudev-header--rate"><?php _e( "Conv. rate", Opt_In::TEXT_DOMAIN ); ?></div>

							<div class="wpmudev-header--status"><?php _e( "Slide-in status", Opt_In::TEXT_DOMAIN ); ?></div>

							<div class="wpmudev-header--settings"></div>

						</div>

						<div class="wpmudev-list--section">

						<?php wp_nonce_field("hustle_get_emails_list", "hustle_get_emails_list_nonce");

						foreach( $slideins as $key => $module ) :  ?>

								<div class="wpmudev-list--element">

									<div class="wpmudev-element--check">

										<div class="wpmudev-input_checkbox">

											<input id="wph-slidein-<?php echo $module->id; ?>" class="wph-module-checkbox" type="checkbox" data-id="<?php echo $module->id; ?>" >

											<label for="wph-slidein-<?php echo $module->id; ?>" class="wpdui-fi wpdui-fi-check"></label>

										</div>

									</div>

									<div class="wpmudev-element--name">

										<p class="wpmudev-element--content"><a href="<?php echo $module->decorated->get_edit_url( Hustle_Module_Admin::SLIDEIN_WIZARD_PAGE ,'' ); ?>"><?php echo esc_html( $module->module_name ); ?></a></p>

									</div>

									<div class="wpmudev-element--email">

										<p class="wpmudev-element--title"><?php _e( "Email service", Opt_In::TEXT_DOMAIN ); ?>:</p>

										<p class="wpmudev-element--content"><?php echo (int) $module->test_mode  ? '–' : esc_html( $module->decorated->mail_service_label ); ?></p>

									</div>

									<div class="wpmudev-element--conditions">

										<p class="wpmudev-element--title"><?php _e( "Display conditions", Opt_In::TEXT_DOMAIN ); ?>:</p>

										<p class="wpmudev-element--content"><?php echo $module->decorated->get_condition_labels(false); ?></p>

									</div>

									<div class="wpmudev-element--views">

										<p class="wpmudev-element--title"><?php _e( "Views", Opt_In::TEXT_DOMAIN ); ?>:</p>

										<p class="wpmudev-element--content"><?php echo $module->get_statistics($module->module_type)->views_count; ?></p>

									</div>

									<div class="wpmudev-element--conversions">

										<p class="wpmudev-element--title"><?php _e( "Conversions", Opt_In::TEXT_DOMAIN ); ?>:</p>

										<p class="wpmudev-element--content"><?php echo $module->get_statistics($module->module_type)->conversions_count; ?></p>

									</div>

									<div class="wpmudev-element--rate">

										<p class="wpmudev-element--title"><?php _e( "Conv. rate", Opt_In::TEXT_DOMAIN ); ?>:</p>

										<p class="wpmudev-element--content"><?php echo $module->get_statistics($module->module_type)->conversion_rate; ?>%</p>

									</div>

									<div class="wpmudev-element--status">

										<p class="wpmudev-element--title"><?php printf( __( "%s status", Opt_In::TEXT_DOMAIN ), esc_html( $module->module_name ) ); ?>:</p>

										<div class="wpmudev-element--content">

											<div class="wpmudev-tabs">

												<ul class="wpmudev-tabs-menu wpmudev-tabs-menu_full">

													<li class="wpmudev-tabs-menu_item <?php echo ( !$module->active && !$module->is_test_type_active( $module->module_type ) ) ? 'current' : '' ?>">
														<input id="wph-module-<?php echo esc_html( $module->id ); ?>-status--off" type="radio" value="off" name="wph-module-status" data-nonce="<?php echo wp_create_nonce('slidein_module_toggle_state') ?>" data-id="<?php echo esc_attr($module->id); ?>">
														<label for="wph-module-<?php echo esc_html( $module->id ); ?>-status--off" class="wpmudev-status-off"><?php _e( "Off", Opt_In::TEXT_DOMAIN ); ?></label>
													</li>

													<li class="wpmudev-tabs-menu_item <?php echo ( $module->is_test_type_active( $module->module_type ) ) ? 'current' : '' ?>">
														<input id="wph-module-<?php echo esc_html( $module->id ); ?>-status--test" type="radio" value="test" name="wph-module-status" data-nonce="<?php echo wp_create_nonce('slidein_toggle_test_activity'); ?>" data-type="<?php echo esc_attr($module->module_type); ?>" data-id="<?php echo esc_attr($module->id);  ?>" >
														<label for="wph-module-<?php echo esc_html( $module->id ); ?>-status--test" class="wpmudev-status-test"><?php _e( "Test", Opt_In::TEXT_DOMAIN ); ?></label>
													</li>

													<li class="wpmudev-tabs-menu_item <?php echo ( $module->active && !$module->is_test_type_active( $module->module_type ) ) ? 'current' : '' ?>">
														<input id="wph-module-<?php echo esc_html( $module->id ); ?>-status--live" type="radio" value="live" name="wph-module-status" data-nonce="<?php echo wp_create_nonce('slidein_module_toggle_state') ?>" data-id="<?php echo esc_attr($module->id); ?>">
														<label for="wph-module-<?php echo esc_html( $module->id ); ?>-status--live" class="wpmudev-status-live"><?php _e( "Live", Opt_In::TEXT_DOMAIN ); ?></label>
													</li>

												</ul>

											</div>

										</div>

									</div>

									<div class="wpmudev-element--settings">

										<p class="wpmudev-element--title"><?php _e( "Slide-in status", Opt_In::TEXT_DOMAIN ); ?></p>

										<div class="wpmudev-element--content">

											<div class="wpmudev-dots-dropdown">

												<button class="wpmudev-dots-button"><svg height="4" width="16">
													<circle cx="2" cy="2" r="2" fill="#B5BBBB" />
													<circle cx="8" cy="2" r="2" fill="#B5BBBB" />
													<circle cx="14" cy="2" r="2" fill="#B5BBBB" />
												</svg></button>

												<ul class="wpmudev-dots-nav wpmudev-hide">

													<li><a href="<?php echo $module->decorated->get_edit_url( Hustle_Module_Admin::SLIDEIN_WIZARD_PAGE ,'' ); ?>"><?php _e( "Edit Slide-in", Opt_In::TEXT_DOMAIN ); ?></a></li>
													<?php if( $module->get_total_subscriptions() ): ?>
														<li><a href="#" class="button-view-email-list" data-total="<?php echo esc_attr( $module->get_total_subscriptions() ); ?>" data-id="<?php echo esc_attr( $module->id ); ?>" data-name="<?php echo esc_attr( $module->module_name ); ?>" ><?php _e( "View email list", Opt_In::TEXT_DOMAIN ); ?></a></li>
													<?php endif; ?>
													<?php if ( ( $log_count = $module->get_total_log_errors() ) ) : ?>
														<li><a href="#" class="button-view-log-list" data-total="<?php echo esc_attr( $log_count ); ?>" data-id="<?php echo esc_attr( $module->id ); ?>" data-name="<?php echo esc_attr( $module->module_name ); ?>" ><?php _e( "View error log", Opt_In::TEXT_DOMAIN ); ?></a></li>
													<?php endif; ?>
													<li><a href="#" class="module-toggle-tracking-activity" data-id="<?php echo esc_attr( $module->id ) ?>" data-type="<?php echo esc_attr( $module->module_type ); ?>" <?php checked( $module->is_track_type_active( $module->module_type ), true); ?> data-nonce="<?php echo wp_create_nonce('slidein_toggle_tracking_activity') ?>" data-current="<?php echo $module->is_track_type_active( $module->module_type ); ?>" ><?php ( $module->is_track_type_active( $module->module_type ) ) ? _e( "Disable tracking", Opt_In::TEXT_DOMAIN ) : _e( "Enable tracking", Opt_In::TEXT_DOMAIN ); ?></a></li>
													<li><a href="#" class="hustle-delete-module" data-nonce="<?php echo wp_create_nonce('hustle_delete_module'); ?>" data-id="<?php echo esc_attr( $module->id ); ?>" ><?php _e( "Delete Slide-in", Opt_In::TEXT_DOMAIN ); ?></a></li>

												</ul>

											</div>

										</div>

									</div>

							</div>

						<?php endforeach; ?>

					</div>

				</div>

			</div>

		</section>

		<?php $this->render( "admin/commons/footer", array() ); ?>

		<?php $this->render("admin/commons/listing/modal-error"); ?>

		<?php $this->render("admin/commons/listing/modal-email"); ?>

		<?php $this->render("admin/commons/listing/delete-confirmation"); ?>

	</main>

<?php } ?>