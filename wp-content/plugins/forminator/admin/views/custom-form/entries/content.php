<section id="wpmudev-section">

    <div class="wpmudev-row">

        <div class="wpmudev-col col-12 col-sm-6">

            <div id="forminator-entries--display_settings" class="wpmudev-box wpmudev-can--hide">

                <div class="wpmudev-box-header">

                    <div class="wpmudev-header--text">

                        <h2 class="wpmudev-subtitle"><?php _e( "Entries Display Settings", Forminator::DOMAIN ); ?></h2>

                    </div>

                    <div class="wpmudev-header--action">

                        <button class="wpmudev-box--action">

                            <span class="wpmudev-icon--plus" aria-hidden="true"></span>

                            <span class="wpmudev-sr-only"><?php _e( "Open Entries Display Settings", Forminator::DOMAIN ); ?></span>

                        </button>

                    </div>

                </div>

                <div class="wpmudev-box-section">

					<form method="POST">
						<?php wp_nonce_field( 'forminatorCustomFormEntries', 'forminatorEntryNonce' ); ?>
                        <div class="wpmudev-section--multicheck">

							<div class="wpmudev-multicheck--header">

                                <label><?php $this->fields_header(); ?></label>

                                <p><?php printf( __( "Select <a class='wpmudev-check-all' href='%s'>All</a> | <a class='wpmudev-uncheck-all' href='%s'>None</a>" ), "#", "#" ); ?></p>

                            </div>

							<ul class="wpmudev-multicheck">

								<?php
								$ignored_field_types 	= Forminator_Form_Entry_Model::ignored_fields();
								foreach ( $this->get_fields() as $field ) {
									$label       = $field->__get( 'field_label' );
									$field_type  = $field->__get( 'type' );
									if ( in_array( $field_type, $ignored_field_types ) ) {
										continue;
									}

									if ( !$label ) {
										$label =  $field->title;
									}
									if ( empty( $label ) ) {
										$label = ucfirst( $field_type );
									}
									$slug	= isset( $field->slug ) ? $field->slug : sanitize_title( $label );
									?>
									<li class='wpmudev-multicheck--item'>
										<div class='wpmudev-checkbox'>
											<input type='checkbox' id='<?php echo $slug; ?>-enable' name="field[]" <?php $this->checked_field( $slug ); ?> value="<?php echo $slug; ?>">
											<label for='<?php echo $slug; ?>-enable' class='wpdui-icon wpdui-icon-check'></label>
										</div>
										<label for='<?php echo $slug; ?>-enable' class='wpmudev-item--label'><?php echo $label; ?></label>
									</li>
									<?php
								}
								?>

							</ul>

							<button class="wpmudev-button"><?php _e( "Filter Entries", Forminator::DOMAIN ); ?></button>

                        </div>

					</form>

                </div>

            </div>

        </div>

        <div class="wpmudev-col col-12 col-sm-6">

            <div id="forminator-entries--export" class="wpmudev-box wpmudev-can--hide">

                <div class="wpmudev-box-header">

                    <div class="wpmudev-header--text">

                        <h2 class="wpmudev-subtitle"><?php _e( "Export Entries", Forminator::DOMAIN ); ?></h2>

                    </div>

                    <div class="wpmudev-header--action">

                        <button class="wpmudev-box--action">

                            <span class="wpmudev-icon--plus" aria-hidden="true"></span>

                            <span class="wpmudev-sr-only"><?php _e( "Open Export Entries", Forminator::DOMAIN ); ?></span>

                        </button>

                    </div>

                </div>

                <div class="wpmudev-box-section">

                    <div class="wpmudev-section--text">

                        <?php $path = forminator_plugin_dir(); ?>

                        <div id="forminator-export--buttons">

                            <form class="wpmudev-export--form" method="post">
                                <button class="wpmudev-button"><?php _e( "Export Entries", Forminator::DOMAIN ); ?></button>
                                <input type="hidden" name="forminator_export" value="1">
                                <input type="hidden" name="form_id" value="<?php echo $this->form_id ?>">
                                <input type="hidden" name="form_type" value="cform">
		                        <?php wp_nonce_field( 'forminator_export', '_forminator_nonce' ) ?>
                            </form>
							<!--
							<div class="wpmudev-export--prev">
								<button class="wpmudev-button wpmudev-button-ghost wpmudev-open-modal" data-modal="exports" data-nonce="<?php echo wp_create_nonce( 'forminator_load_exports' ) ?>" data-form-id="<?php echo $_GET['form_id'] ?>"><?php _e( "Previous Exports", Forminator::DOMAIN ); ?></button>
							</div>
							-->
                        </div>

                        <div id="forminator-export--schedule">

                            <label><?php _e( "Scheduled exports", Forminator::DOMAIN ); ?></label>

	                        <?php
	                        $schedule_day       = forminator_get_exporter_info( 'day', forminator_get_form_id_helper() . forminator_get_form_type_helper() );
							$schedule_month_day = forminator_get_exporter_info( 'month_day', forminator_get_form_id_helper() . forminator_get_form_type_helper() );
	                        $schedule_time      = forminator_get_exporter_info( 'hour', forminator_get_form_id_helper() . forminator_get_form_type_helper() );
	                        $schedule_timeframe = forminator_get_exporter_info( 'interval', forminator_get_form_id_helper() . forminator_get_form_type_helper() );
	                        $email              = forminator_get_exporter_info( 'email', forminator_get_form_id_helper() . forminator_get_form_type_helper() );
							$enabled			= ( forminator_get_exporter_info( 'enabled', forminator_get_form_id_helper() . forminator_get_form_type_helper() ) === 'true' );
	                        ?>

                            <label class="wpmudev-label--info">
		                        <?php if ( ! $enabled || empty( $email ) ): ?>
                                    <span><?php _e( "Scheduled export is not enabled", Forminator::DOMAIN ) ?></span>
		                        <?php else: ?>
			                        <?php if (  $schedule_timeframe == 'weekly' ): ?>
                                        <span><?php printf( __( "Export schedule: <strong>%s</strong> on <strong>%s</strong> at <strong>%s</strong>", Forminator::DOMAIN ), ucfirst( $schedule_timeframe ), ucfirst( $schedule_day ), $schedule_time ); ?>
			                        <?php elseif ( $schedule_timeframe == 'monthly' ): ?>
                                        <span><?php printf( __( "Export schedule: <strong>%s</strong> every <strong>%s</strong> at <strong>%s</strong>", Forminator::DOMAIN ), ucfirst( $schedule_timeframe ), ( $schedule_month_day ? $schedule_month_day : 1 ), $schedule_time ); ?>
			                        <?php else: ?>
                                        <span><?php printf( __( "Export schedule: <strong>%s</strong> at <strong>%s</strong>", Forminator::DOMAIN ), ucfirst( $schedule_timeframe ), $schedule_time ); ?>
                                    <?php endif; ?>
                                    <br/><?php _e( "To be sent by email.", Forminator::DOMAIN ); ?></span>
		                        <?php endif; ?>
                                <a href="/"
                                   class="wpmudev-button wpmudev-button-ghost wpmudev-button-sm wpmudev-open-modal"
                                   data-modal="exports-schedule"><?php _e( "Edit", Forminator::DOMAIN ); ?></a>

                            </label>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

	<div class="wpmudev-row--boder"></div>

	<div class="wpmudev-row">

		<div class="wpmudev-col col-12">

			<form method="POST">

				<?php wp_nonce_field( 'forminatorCustomFormEntries', 'forminatorEntryNonce' ); ?>

				<div class="wpmudev-actions">

					<?php $this->bulk_actions(); ?>

					<div class="wpmudev-action--page">

						<div class="wpmudev-page--resume"><p><?php $count = $this->total_entries(); if ( $count == 1 ) { printf( __( "%s result", Forminator::DOMAIN ), $count ); } else { printf( __( "%s results", Forminator::DOMAIN ), $count ); } ?></p></div>

						<?php $this->paginate(); ?>

					</div>

				</div>

				<div id="forminator-cform-table" class="wpmudev-entries">

					<div class="wpmudev-entries--header">

						<div class="wpmudev-entries--check">

							<div class="wpmudev-checkbox">
								<input type="checkbox" id="forminator-entries-all"/>
								<label for="forminator-entries-all" class="wpdui-icon wpdui-icon-check"></label>
							</div>

						</div>

						<div class="wpmudev-entries--text">

							<p class="wpmudev-entries--title"><?php _e( 'Title', Forminator::DOMAIN ); ?></p>
							<p class="wpmudev-entries--subtitle"><?php _e( 'Date', Forminator::DOMAIN ); ?></p>

						</div>

						<div class="wpmudev-entries--action" aria-hidden="true"></div>

					</div>

					<div class="wpmudev-entries--section">

						<?php
						$count = $this->total_entries();

						if ( $count == 0 ) : ?>

							<div class="wpmudev-entries--empty">

								<p><?php _e( 'No results were found.', Forminator::DOMAIN ); ?></p>

							</div>

						<?php else : ?>

							<?php
							$entries 		= $this->get_entries();
							$first_item 	= $count;
							$page_number 	= $this->get_page_number();
							$per_page 		= $this->get_per_page();
							if ( $page_number > 1 ) {
								$first_item = $count - ( ( $page_number - 1 ) * $per_page );
							}
							foreach ( $entries as $entry ) :

								 ?>

								<div id="forminator-entry-<?php echo $entry->entry_id; ?>" class="wpmudev-entries--result">

									<div class="wpmudev-result--header wpmudev-open-entry" data-entry="entry-<?php echo $entry->entry_id; ?>">

										<div class="wpmudev-result--check">

											<div class="wpmudev-checkbox">

												<input type="checkbox" id="wpf-cform-check_entry_<?php echo $entry->entry_id; ?>" name="entry[]" value="<?php echo $entry->entry_id; ?>"/>

												<label for="wpf-cform-check_entry_<?php echo $entry->entry_id; ?>" class="wpdui-icon wpdui-icon-check" aria-hidden="true"></label>

											</div>

										</div>

										<div class="wpmudev-result--text">

											<p class="wpmudev-result--title"><?php printf( __( 'Entry #%s', Forminator::DOMAIN ), $first_item ); ?></p>
											<p class="wpmudev-result--subtitle"><?php  echo date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $entry->date_created_sql ) ); ?></p>

										</div>

										<div class="wpmudev-result--action">

											<button class="wpmudev-button-action wpmudev-open-entry" data-entry="entry-<?php echo $entry->entry_id; ?>">

												<span class="wpmudev-icon--plus" aria-hidden="true"></span>

												<span class="wpmudev-sr-only"><?php _e( 'Open result details', Forminator::DOMAIN ); ?></span>

											</button>

										</div>

									</div>

									<div class="wpmudev-result--section">

										<div class="wpmudev-box">

											<div class="wpmudev-box-section">

												<div class="wpmudev-section--text">

													<ul class="wpmudev-results--list">

														<?php
														$fields 				= $this->get_fields();
														$total_product 			= Forminator_CForm_View_Page::render_entry( $entry, 'product_shipping' );
														$currency_symbol 		= forminator_get_currency_symbol();
														$ignored_field_types 	= Forminator_Form_Entry_Model::ignored_fields();
														$visible_fields 		= $this->get_visible_fields();
														foreach ( $fields as $field ) :
                                                            /** @var  Forminator_Form_Field_Model $field */
															$field_array = $field->toFormattedArray();
															$field_type  = $field->__get( 'type' );
															if ( in_array( $field_type, $ignored_field_types ) ) {
																continue;
															}

															$label = $field->__get( 'field_label' );

															if ( !$label ) {
																$label =  $field->title;
															}

															$slug = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
															if ( !empty( $visible_fields ) && is_array( $visible_fields ) ) {
																if ( !in_array( $slug, $visible_fields ) ) {
																	continue;
																}
															}
															if ( $field_type == "product" ) {
																$total_product += Forminator_CForm_View_Page::render_raw_entry( $entry, $slug );
															}
															if ( empty( $label ) ) {
																$label = ucfirst( $field_type );
															}

															if ( $field_type =='name' ) {
																$label = $field->get_label_for_entry();
															}
															?>

															<li>
																<?php
																if ( !empty( $label ) ) {
																	?><p class="wpmudev-results--question"><?php echo $label; ?></p><?php
																}
																?>

																<?php
																if ( strtolower( $label ) == 'total' ) {
																	?>
																		<p class="wpmudev-results--answer"><?php echo sprintf( __( '<strong>Total</strong> %s', Forminator::DOMAIN ), $currency_symbol . '' . $total_product ); ?></p>
																	<?php
																} else {
																?>
																	<p class="wpmudev-results--answer"><?php echo Forminator_CForm_View_Page::render_entry( $entry, $slug ); ?></p>
																<?php } ?>
															</li>

														<?php endforeach; ?>

													</ul>

												</div>

											</div>

										</div>

									</div>

								</div>

								<?php  $first_item--;

							endforeach; ?>

						<?php endif; ?>

					</div>

					<div class="wpmudev-entries--footer">

						<div class="wpmudev-entries--check">

							<div class="wpmudev-checkbox">
								<input type="checkbox" id="forminator-entries-all"/>
								<label for="forminator-entries-all" class="wpdui-icon wpdui-icon-check"></label>
							</div>

						</div>

						<div class="wpmudev-entries--text">

							<p class="wpmudev-entries--title"><?php _e( 'Title', Forminator::DOMAIN ); ?></p>

							<p class="wpmudev-entries--subtitle"><?php _e( 'Date', Forminator::DOMAIN ); ?></p>

						</div>

					</div>

				</div>

				<div class="wpmudev-actions">

					<?php $this->bulk_actions( 'bottom' ); ?>

					<div class="wpmudev-action--page">

						<div class="wpmudev-page--resume"><p><?php $count = $this->total_entries(); if ( $count == 1 ) { printf( __( "%s result", Forminator::DOMAIN ), $count ); } else { printf( __( "%s results", Forminator::DOMAIN ), $count ); } ?></p></div>

						<?php $this->paginate(); ?>

					</div>

				</div>

			</form>

		</div>

	</div>