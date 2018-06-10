<?php
/**
 * @var $this Hustle_Embedded_Admin
 * @var $module Hustle_Module_Model
 * @var $new_module Hustle_Module_Model
 */
?>

<?php if ( count( $embeddeds ) === 0 ) { ?>

	<?php $this->render("admin/embedded/welcome", array(
        'new_url' => $add_new_url,
        'user_name' => $user_name
    )); ?>

<?php } else { ?>

	<main id="wpmudev-hustle" class="wpmudev-ui wpmudev-hustle-listings-view">

		<header id="wpmudev-hustle-title" class="wpmudev-has-button">

			<h1><?php _e( "Embeds Dashboard", Opt_In::TEXT_DOMAIN ); ?></h1>

			<a class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost" href="<?php echo esc_url( $add_new_url ); ?>"><?php _e('New Embed', Opt_In::TEXT_DOMAIN); ?></a>

		</header>

		<section id="wpmudev-hustle-content">

			<?php wp_nonce_field("hustle_get_emails_list", "hustle_get_emails_list_nonce");

			foreach( $embeddeds as $key => $module ) :

			$types = Hustle_Module_Model::get_embedded_types();

			$keep_open = ( count( $embeddeds ) === 1 ) ? true : false;

			if ( !$keep_open && $new_module && $module->id === $new_module->id )
				$keep_open = true;

			if ( !$keep_open && $updated_module && $module->id === $updated_module->id )
				$keep_open = true; ?>

			<div class="wpmudev-row">

				<div class="wpmudev-col col-12">

						<div class="wpmudev-box-listing">

							<div class="wpmudev-box-head">

								<div class="wpmudev-box-group">

									<div class="wpmudev-box-group--inner">

										<div class="wpmudev-group-switch">

											<div class="wpmudev-switch">

												<input data-nonce="<?php echo wp_create_nonce('embed_module_toggle_state') ?>" id="module-active-state-<?php echo esc_attr($module->id); ?>" class="module-active-state" type="checkbox" data-id="<?php echo esc_attr($module->id); ?>" <?php checked( $module->active, 1 ); ?>>

												<label class="wpmudev-switch-design" for="module-active-state-<?php echo esc_attr($module->id); ?>" aria-hidden="true"></label>

											</div><?php // .wpmudev-switch ?>

										</div>

										<div class="wpmudev-group-title">

											<h5><?php echo esc_html( $module->module_name ); ?></h5>

											<label class="wpmudev-helper<?php echo (int) $module->test_mode  ? ' wpoi-module-no-provider' : '' ?>"><?php echo esc_html( $module->decorated->mail_service_label );  ?></label>

										</div>

										<div class="wpmudev-group-buttons">

											<a class="wpmudev-button wpmudev-button-sm hustle-edit-module" href="<?php echo $module->decorated->get_edit_url( Hustle_Module_Admin::EMBEDDED_WIZARD_PAGE ,'' ); ?>">
												<span class="wpmudev-button-icon"><?php $this->render("general/icons/admin-icons/icon-edit" ); ?></span>
												<span class="wpmudev-button-text"><?php _e('Edit', Opt_In::TEXT_DOMAIN); ?></span>
											</a>

											<a class="wpmudev-button wpmudev-button-sm wpmudev-button-red hustle-delete-module" data-nonce="<?php echo wp_create_nonce('hustle_delete_module'); ?>" data-id="<?php echo esc_attr( $module->id ); ?>" >
												<span class="wpmudev-button-icon"><?php $this->render("general/icons/admin-icons/icon-delete" ); ?></span>
												<span class="wpmudev-button-text"><?php _e('Delete', Opt_In::TEXT_DOMAIN); ?></span>
											</a>

										</div>

									</div>

									<?php if ( $module->get_total_subscriptions() || ( $log_count = $module->get_total_log_errors() ) ):
										$log_count = array();
									?>

										<div class="wpmudev-group-buttons">

											<?php if ( ( $log_count = $module->get_total_log_errors() ) ) : ?>
												<button class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost wpmudev-button-red button-view-log-list" href="#" data-total="<?php echo esc_attr( $log_count ); ?>" data-id="<?php echo esc_attr( $module->id ); ?>" data-name="<?php echo esc_attr( $module->module_name ); ?>" >
													<span class="wpmudev-button-icon"><?php $this->render("general/icons/admin-icons/icon-warning" ); ?></span>
													<span class="wpmudev-button-text"><?php _e("View Error Log", Opt_In::TEXT_DOMAIN); ?></span>
												</button>
											<?php endif; ?>

											<?php if( $module->get_total_subscriptions() ): ?>
												<button class="wpmudev-button wpmudev-button-sm button-view-email-list" href="#" data-total="<?php echo esc_attr( $module->get_total_subscriptions() ); ?>" data-id="<?php echo esc_attr( $module->id ); ?>" data-name="<?php echo esc_attr( $module->module_name ); ?>" >
													<span class="wpmudev-button-icon"><?php $this->render("general/icons/admin-icons/icon-email" ); ?></span>
													<span class="wpmudev-button-text"><?php _e("View Email List", Opt_In::TEXT_DOMAIN); ?></span>
												</button>
											<?php endif; ?>

										</div>

									<?php endif; ?>

								</div>

								<div class="wpmudev-box-action"><?php $this->render("general/icons/icon-arrow" ); ?></div>

							</div><?php // .wpmudev-box-head ?>

							<div class="wpmudev-box-body <?php echo $keep_open ? 'wpmudev-show' : 'wpmudev-hidden'; ?>">

								<div class="wpmudev-box-<?php echo $module->active ? 'enabled' : 'disabled'; ?>">

									<label class="wpmudev-helper"><?php _e("Please activate this opt-in to configure it's settings.", Opt_In::TEXT_DOMAIN); ?></label>

								</div>

								<div class="wpmudev-listing">

									<div class="wpmudev-listing-head" aria-hidden="true">

										<div class="wpmudev-listing-type"><?php _e( "Module type", Opt_In::TEXT_DOMAIN ); ?></div>

										<div class="wpmudev-listing-conditions"><?php _e( "Display conditions", Opt_In::TEXT_DOMAIN ); ?></div>

										<div class="wpmudev-listing-views"><?php _e( "Views", Opt_In::TEXT_DOMAIN ); ?></div>

										<div class="wpmudev-listing-conversions"><?php _e( "Conversions", Opt_In::TEXT_DOMAIN ); ?></div>

										<div class="wpmudev-listing-rates"><?php _e( "Conversion rate", Opt_In::TEXT_DOMAIN ); ?></div>

										<div class="wpmudev-listing-status"><?php _e( "Module status", Opt_In::TEXT_DOMAIN ); ?></div>

										<div class="wpmudev-listing-tracking"><?php _e( "Tracking", Opt_In::TEXT_DOMAIN ); ?></div>

									</div><?php // .wpmudev-listing-head ?>

									<div class="wpmudev-listing-body">

										<?php foreach( $types as $type ) : ?>

											<div class="wpmudev-listing-row">

												<div class="wpmudev-listing-type">

                                                    <p class="wpmudev-listing-title"><?php _e( "Module type", Opt_In::TEXT_DOMAIN ); ?></p>

                                                    <?php if ( $type === "after_content" ) { ?>

                                                        <div class="wpmudev-listing-content">

                                                            <div class="wpmudev-listing-type-icon"><?php $this->render("general/icons/admin-icons/icon-embedded" ); ?></div>

                                                            <span><?php _e( "After Content", Opt_In::TEXT_DOMAIN ); ?></span>

                                                        </div>

													<?php } else if ( $type === "widget" ) { ?>

                                                        <div class="wpmudev-listing-content">

                                                            <div class="wpmudev-listing-type-icon"><?php $this->render("general/icons/admin-icons/icon-widget" ); ?></div>

                                                            <span><?php _e( "Widget", Opt_In::TEXT_DOMAIN ); ?></span>

                                                        </div>

                                                    <?php } else if ( $type === "shortcode" ) { ?>

                                                        <div class="wpmudev-listing-content">

                                                            <div class="wpmudev-listing-type-icon"><?php $this->render("general/icons/admin-icons/icon-shortcode" ); ?></div>

                                                            <span><?php _e( "Shortcode", Opt_In::TEXT_DOMAIN ); ?></span>

                                                        </div>

                                                    <?php } ?>

                                                </div>

												<div class="wpmudev-listing-conditions">

													<p class="wpmudev-listing-title"><?php _e( "Display conditions", Opt_In::TEXT_DOMAIN ); ?></p>

													<p class="wpmudev-listing-content">

														<?php if ( $type != 'shortcode' ) {
															echo $module->decorated->get_condition_labels(false);
														} else if ( $type == 'shortcode' ) {
															$shortcode = '[wd_hustle id=&quot;' . $module->shortcode_id . '&quot; type=&quot;embedded&quot;]';
															echo '<input type="text" value="' . $shortcode . '" readonly class="highlight_input_text shortcode_input">';
														} ?>

													</p>

												</div>

												<div class="wpmudev-listing-views">

													<p class="wpmudev-listing-title"><?php _e( "Views", Opt_In::TEXT_DOMAIN ); ?></p>

													<p class="wpmudev-listing-content"><?php echo $module->get_statistics($type)->views_count; ?></p>

												</div>

												<div class="wpmudev-listing-conversions">

													<p class="wpmudev-listing-title"><?php _e( "Conversions", Opt_In::TEXT_DOMAIN ); ?></p>

													<p class="wpmudev-listing-content"><?php echo $module->get_statistics($type)->conversions_count; ?></p>

												</div>

												<div class="wpmudev-listing-rates">

													<p class="wpmudev-listing-title"><?php _e( "Conversions rate", Opt_In::TEXT_DOMAIN ); ?></p>

													<p class="wpmudev-listing-content"><?php echo $module->get_statistics($type)->conversion_rate; ?>%</p>

												</div>

												<div class="wpmudev-listing-status">

                                                    <p class="wpmudev-listing-title"><?php _e( "Module status", Opt_In::TEXT_DOMAIN ); ?></p>

                                                    <div class="wpmudev-listing-content"><div class="wpmudev-tabs">

                                                        <ul class="wpmudev-tabs-menu wpmudev-tabs-menu_full">
                                                            <li class="wpmudev-tabs-menu_item <?php echo ( !$module->is_embedded_type_active($type) && !$module->is_test_type_active( $type ) ) ? 'current' : '' ?>">
                                                                <input id="wph-module-<?php echo esc_attr($type) ."-". esc_attr( $module->id );  ?>-status--off" type="radio" value="off" name="wph-module-status" data-nonce="<?php echo wp_create_nonce('embedded_toggle_module_type_state'); ?>" data-type="<?php echo esc_attr($type); ?>" data-id="<?php echo esc_attr($module->id);  ?>" >
                                                                <label for="wph-module-<?php echo esc_attr($type) ."-". esc_attr( $module->id );  ?>-status--off" class="wpmudev-status-off"><?php _e( "Off", Opt_In::TEXT_DOMAIN ); ?></label>
                                                            </li>

                                                            <li class="wpmudev-tabs-menu_item <?php echo ( $module->is_test_type_active( $type ) ) ? 'current' : '' ?>">
                                                                <input id="wph-module-<?php echo esc_attr($type) ."-". esc_attr( $module->id );  ?>-status--test" type="radio" value="test" name="wph-module-status" data-nonce="<?php echo wp_create_nonce('embedded_toggle_test_activity'); ?>" data-type="<?php echo esc_attr($type); ?>" data-id="<?php echo esc_attr($module->id);  ?>" >
                                                                <label for="wph-module-<?php echo esc_attr($type) ."-". esc_attr( $module->id );  ?>-status--test" class="wpmudev-status-test"><?php _e( "Test", Opt_In::TEXT_DOMAIN ); ?></label>
                                                            </li>

                                                            <li class="wpmudev-tabs-menu_item <?php echo ( $module->is_embedded_type_active($type) && !$module->is_test_type_active( $type ) ) ? 'current' : '' ?>">
                                                                <input id="wph-module-<?php echo esc_attr($type) ."-". esc_attr( $module->id );  ?>-status--live" type="radio" value="live" name="wph-module-status" data-nonce="<?php echo wp_create_nonce('embedded_toggle_module_type_state'); ?>" data-type="<?php echo esc_attr($type); ?>" data-id="<?php echo esc_attr($module->id);  ?>">
                                                                <label for="wph-module-<?php echo esc_attr($type) ."-". esc_attr( $module->id );  ?>-status--live" class="wpmudev-status-live"><?php _e( "Live", Opt_In::TEXT_DOMAIN ); ?></label>
                                                            </li>

                                                        </ul>

                                                    </div></div>

                                                </div>

												<div class="wpmudev-listing-tracking">

													<p class="wpmudev-listing-title"><?php _e( "Tracking", Opt_In::TEXT_DOMAIN ); ?></p>

													<div class="wpmudev-switch">

														<input id="module-toggle-tracking-<?php echo esc_attr($type) . '-' . esc_attr( $module->id ); ?>" class="module-toggle-tracking-activity" type="checkbox" data-id="<?php echo esc_attr( $module->id ) ?>" data-type="<?php echo esc_attr( $type ); ?>" <?php checked( $module->is_track_type_active( $type ), true); ?> data-nonce="<?php echo wp_create_nonce('embedded_toggle_tracking_activity') ?>" >

														<label class="wpmudev-switch-design" for="module-toggle-tracking-<?php echo $type . '-' . esc_attr( $module->id ); ?>" aria-hidden="true"></label>

													</div>

												</div>

											</div><?php // .wpmudev-listing-row ?>

										<?php endforeach; ?>

									</div><?php // .wpmudev-listing-body ?>

								</div><?php // .wpmudev-listing ?>

							</div><?php // .wpmudev-box-body ?>

						</div><?php // .wpmudev-box-listings ?>

					</div>

				</div>

			<?php endforeach; ?>

		</section>

		<?php //if( ! is_null( $new_module ) && count( $embeddeds ) === 1 ) $this->render("admin/new-module_success", array( 'new_module' => $new_module, 'types' => $types )); ?>

		<?php $this->render( "admin/commons/footer", array() ); ?>

		<?php $this->render("admin/commons/listing/modal-error"); ?>

		<?php $this->render("admin/commons/listing/modal-email"); ?>

		<?php $this->render("admin/commons/listing/delete-confirmation"); ?>

	</main>

<?php } ?>