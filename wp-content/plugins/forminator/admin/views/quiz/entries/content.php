<?php
$entries    = $this->get_table();
$form_type  = $this->get_form_type();
$count      = $this->get_total_entries();
$per_page   = 10;
$total_page = ceil( $count / $per_page );
?>
<section id="wpmudev-section">

    <div class="wpmudev-row">

        <div class="wpmudev-col col-12 col-sm-6">

            <div id="forminator-entries--display_settings" class="wpmudev-box wpmudev-can--hide">

                <div class="wpmudev-box-header">

                    <div class="wpmudev-header--text">

                        <h2 class="wpmudev-subtitle"><?php _e( "Results Display Settings", Forminator::DOMAIN ); ?></h2>

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

						<?php wp_nonce_field( 'forminatorQuizEntries', 'forminatorEntryNonce' ); ?>

                        <div class="wpmudev-section--multicheck">

                            <div class="wpmudev-multicheck--header">

                                <label><?php $this->fields_header(); ?></label>

                                <p><?php printf( __( "Select <a class='wpmudev-check-all' href='%s'>All</a> | <a class='wpmudev-uncheck-all' href='%s'>None</a>" ), "#", "#" ); ?></p>

                            </div>

                            <ul class="wpmudev-multicheck">

                                <li class="wpmudev-multicheck--item">

                                    <div class="wpmudev-checkbox">

                                        <input type="checkbox" id="date-enable" name="field[]"
                                               value="date" <?php $this->checked_field( 'date' ); ?>>
                                        <label for="date-enable" class="wpdui-icon wpdui-icon-check"></label>

                                    </div>

                                    <label for="date-enable"
                                           class="wpmudev-item--label"><?php _e( "Date added", Forminator::DOMAIN ); ?></label>

                                </li>

								<?php
								foreach ( $this->get_fields() as $field ) {
									$label = $field->__get( 'main_label' );
									if ( ! $label ) {
										$label = $field->__get( 'field_label' );
										if ( ! $label ) {
											$label = $field->title;
										}
									}
									$slug = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
									?>
                                    <li class='wpmudev-multicheck--item'>
                                        <div class='wpmudev-checkbox'>
                                            <input type='checkbox' id='<?php echo $slug; ?>-enable'
                                                   name="field[]" <?php $this->checked_field( $slug ); ?>
                                                   value="<?php echo $slug; ?>">
                                            <label for='<?php echo $slug; ?>'
                                                   class='wpdui-icon wpdui-icon-check'></label>
                                        </div>
                                        <label for='<?php echo $slug; ?>-enable'
                                               class='wpmudev-item--label'><?php echo $label; ?></label>
                                    </li>
									<?php
								}
								?>

                            </ul>

                            <button class="wpmudev-button"><?php _e( "Update Table", Forminator::DOMAIN ); ?></button>

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

                        <label><?php _e( "Manual exports", Forminator::DOMAIN ); ?></label>

						<div id="forminator-export--buttons">

                            <form class="wpmudev-export--form" method="post">
                                <button class="wpmudev-button"><?php _e( "Export Entries", Forminator::DOMAIN ); ?></button>
                                <input type="hidden" name="forminator_export" value="1">
                                <input type="hidden" name="form_id" value="<?php echo $this->form_id ?>">
                                <input type="hidden" name="form_type" value="quiz">
								<?php wp_nonce_field( 'forminator_export', '_forminator_nonce' ) ?>
                            </form>
							<!--
                            <div class="wpmudev-export--prev">
                                <button class="wpmudev-button wpmudev-button-ghost wpmudev-open-modal"
                                        data-modal="exports" data-nonce="<?php echo wp_create_nonce( 'forminator_load_exports' ) ?>"
										data-form-id="<?php echo $_GET['form_id'] ?>"><?php _e( "Previous Exports", Forminator::DOMAIN ); ?></button>
                            </div>
                            -->
                        </div>

                        <div id="forminator-export--schedule">

                            <label><?php _e( "Scheduled exports", Forminator::DOMAIN ); ?></label>

							<?php
							$schedule_day       = forminator_get_exporter_info( 'day', forminator_get_form_id_helper() . forminator_get_form_type_helper() );
							$schedule_time      = forminator_get_exporter_info( 'hour', forminator_get_form_id_helper() . forminator_get_form_type_helper() );
							$schedule_timeframe = forminator_get_exporter_info( 'interval', forminator_get_form_id_helper() . forminator_get_form_type_helper() );
							$email              = forminator_get_exporter_info( 'email', forminator_get_form_id_helper() . forminator_get_form_type_helper() );
							?>

                            <label class="wpmudev-label--info">
								<?php if ( empty( $email ) ): ?>
                                    <span><?php _e( "Scheduled export is not enabled", Forminator::DOMAIN ) ?></span>
								<?php else: ?>
                                    <span><?php printf( __( "Export schedule: <strong>%s</strong> on <strong>%s</strong> at <strong>%s</strong>", Forminator::DOMAIN ), ucfirst( $schedule_timeframe ), ucfirst( $schedule_day ), $schedule_time ); ?>
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

    <div class="wpmudev-row--border"></div>
    <form method="post">
		<?php wp_nonce_field( 'forminator_quiz_bulk_action', 'forminatorEntryNonce' ) ?>
        <input type="hidden" name="form_id" value="<?php echo $this->form_id ?>"/>
        <div class="wpmudev-row">
            <div class="wpmudev-col col-12">

                <div class="wpmudev-actions">
                    <?php $this->bulk_actions(); ?>

                    <div class="wpmudev-action--page">

						<?php if ( count( $entries ) > 0 ) : ?>
							<div class="wpmudev-page--resume"><p><?php if ( $count == 1 ) {
										printf( __( "%s result", Forminator::DOMAIN ), $count );
									} else {
										printf( __( "%s results", Forminator::DOMAIN ), $count );
									} ?></p></div>

							<ul class="wpmudev-pagination">

								<li class="wpmudev-pagination--item wpmudev-pagination--prev <?php echo $this->get_paged() == 1 ? 'wpmudev-is_disabled' : null ?>">
									<a href="<?php echo admin_url( 'admin.php?page=forminator-quiz-view&form_id=' . $this->form_id . '&paged=' . ( $this->get_paged() - 1 ) ) ?>">
										<span class="wpdui-icon wpdui-icon-arrow-left-carats"></span>
										<span class="wpmudev-sr-only"><?php _e( 'Previous page', Forminator::DOMAIN ); ?></span>
									</a></li>
								<?php for ( $i = 1; $i <= $total_page; $i ++ ): ?>
									<li class="wpmudev-pagination--item <?php echo( $this->get_paged() == $i ? ' wpmudev-is_active' : '' ) ?>">
										<a href="<?php echo admin_url( 'admin.php?page=forminator-quiz-view&form_id=' . $this->form_id . '&paged=' . $i ) ?>"><?php echo $i ?></a>
									</li>
								<?php endfor; ?>

								<li class="wpmudev-pagination--item wpmudev-pagination--next <?php echo $this->get_paged() == $total_page ? 'wpmudev-is_disabled' : null ?>">
									<a href="<?php echo admin_url( 'admin.php?page=forminator-quiz-view&form_id=' . $this->form_id . '&paged=' . ( $this->get_paged() + 1 ) ) ?>">
										<span class="wpdui-icon wpdui-icon-arrow-right-carats"></span>
										<span class="wpmudev-sr-only"><?php _e( 'Next page', Forminator::DOMAIN ); ?></span>
									</a></li>

							</ul>
						<?php endif; ?>

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

						<?php if ( count( $entries ) == 0 ) : ?>

                            <div class="wpmudev-entries--empty">

                                <p><?php _e( 'No results were found.', Forminator::DOMAIN ); ?></p>

                            </div>

						<?php else : ?>

							<?php
							$first_item 	= $count;
							$page_number 	= $this->get_paged();
							if ( $page_number > 1 ) {
								$first_item = $count - ( ( $page_number - 1 ) * $per_page );
							}
							foreach ( $entries as $entry ) : ?>

                                <div id="forminator-entry-<?php echo $entry->entry_id; ?>"
                                     class="wpmudev-entries--result">

                                    <div class="wpmudev-result--header wpmudev-open-entry" data-entry="entry-<?php echo $entry->entry_id ?>">

                                        <div class="wpmudev-result--check">

                                            <div class="wpmudev-checkbox">

                                                <input name="ids[]" value="<?php echo $entry->entry_id ?>"
                                                       type="checkbox"
                                                       id="quiz-answer-<?php echo $entry->entry_id ?>"/>

                                                <label for="quiz-answer-<?php echo $entry->entry_id ?>"
                                                       class="wpdui-icon wpdui-icon-check" aria-hidden="true"></label>

                                            </div>

                                        </div>

                                        <div class="wpmudev-result--text">

                                            <p class="wpmudev-result--title"><?php printf( __( 'Result #%s', Forminator::DOMAIN ), $first_item ); ?></p>

                                            <p class="wpmudev-result--subtitle"><?php echo date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $entry->date_created_sql ) ); ?></p>

                                        </div>

                                        <div class="wpmudev-result--action">

                                            <button class="wpmudev-button-action wpmudev-open-entry" data-entry="entry-<?php echo $entry->entry_id ?>">

                                                <span class="wpmudev-icon--plus" aria-hidden="true"></span>

                                                <span class="wpmudev-sr-only"><?php _e( 'Open result details', Forminator::DOMAIN ); ?></span>

                                            </button>

                                        </div>

                                    </div>

                                    <div class="wpmudev-result--section">

                                        <div class="wpmudev-box">

                                            <div class="wpmudev-box-section">

                                                <div class="wpmudev-section--text">

													<?php
													if ( $form_type == 'knowledge' ) {

														$meta = $entry->meta_data['entry']['value']; ?>

                                                        <ol class="wpmudev-results--list">

															<?php
															$total = 0;
															$right = 0;

															foreach ( $meta as $answer ) {

																$total ++;

																if ( $answer['isCorrect'] ) {
																	$right ++;
																} ?>

                                                                <li>

                                                                    <p class="wpmudev-results--question"><?php echo $answer['question'] ?></p>

                                                                    <p class="wpmudev-results--answer">
																		<?php if ( $answer['isCorrect'] ) { ?><i
                                                                                class="wpdui-icon wpdui-icon-check"></i><?php } else { ?>
                                                                            <i class="wpdui-icon wpdui-icon-close"></i><?php } ?>
                                                                        <span><?php echo $answer['answer'] ?></span>
                                                                    </p>

                                                                </li>

															<?php } ?>

                                                        </ol>

                                                        <p class="wpmudev-results--summary"><?php echo sprintf( __( 'You got %s/%s correct', Forminator::DOMAIN ), $right, $total );?></p>

													<?php } else {

														$meta = $entry->meta_data['entry']['value'][0]['value']; ?>

                                                        <ul class="wpmudev-results--list">
															<?php if ( isset( $meta['answers'] ) && is_array( $meta['answers'] ) ) :?>
																<?php foreach ( $meta['answers'] as $answer ) : ?>

																	<li>
																		<p class="wpmudev-results--question"><?php echo $answer['question'] ?></p>
																		<p class="wpmudev-results--answer"><?php echo $answer['answer'] ?></p>
																	</li>

																<?php endforeach; ?>
															<?php endif; ?>
                                                        </ul>

                                                        <p class="wpmudev-results--summary"><?php echo $meta['result']['title'] ?></p>

													<?php } ?>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

							<?php
							$first_item--;
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

						<?php if ( count( $entries ) > 0 ) : ?>
							<div class="wpmudev-page--resume"><p><?php
									if ( $count == 1 ) {
										printf( __( "%s result", Forminator::DOMAIN ), $count );
									} else {
										printf( __( "%s results", Forminator::DOMAIN ), $count );
									} ?></p></div>

							<ul class="wpmudev-pagination">

								<li class="wpmudev-pagination--item wpmudev-pagination--prev <?php echo $this->get_paged() == 1 ? 'wpmudev-is_disabled' : null ?>">
									<a href="<?php echo admin_url( 'admin.php?page=forminator-quiz-view&form_id=' . $this->form_id . '&paged=' . ( $this->get_paged() - 1 ) ) ?>">
										<span class="wpdui-icon wpdui-icon-arrow-left-carats"></span>
										<span class="wpmudev-sr-only"><?php _e( 'Previous page', Forminator::DOMAIN ); ?></span>
									</a></li>
								<?php for ( $i = 1; $i <= $total_page; $i ++ ): ?>
									<li class="wpmudev-pagination--item <?php echo( $this->get_paged() == $i ? ' wpmudev-is_active' : '' ) ?>">
										<a href="<?php echo admin_url( 'admin.php?page=forminator-quiz-view&form_id=' . $this->form_id . '&paged=' . $i ) ?>"><?php echo $i ?></a>
									</li>
								<?php endfor; ?>

								<li class="wpmudev-pagination--item wpmudev-pagination--next <?php echo $this->get_paged() == $total_page ? 'wpmudev-is_disabled' : null ?>">
									<a href="<?php echo admin_url( 'admin.php?page=forminator-quiz-view&form_id=' . $this->form_id . '&paged=' . ( $this->get_paged() + 1 ) ) ?>">
										<span class="wpdui-icon wpdui-icon-arrow-right-carats"></span>
										<span class="wpmudev-sr-only"><?php _e( 'Next page', Forminator::DOMAIN ); ?></span>
									</a></li>

							</ul>
						<?php endif; ?>

                    </div>

                </div>

            </div>

        </div>
    </form>