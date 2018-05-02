<section id="wpmudev-section">

    <div class="wpmudev-row">

        <div class="wpmudev-col col-12 col-sm-6">

            <div id="forminator-entries--display_settings" class="wpmudev-box wpmudev-can--hide">

                <div class="wpmudev-box-header">

                    <div class="wpmudev-header--text">

                        <h2 class="wpmudev-subtitle"><?php _e( "List of custom votes", Forminator::DOMAIN ); ?></h2>

                    </div>

                    <div class="wpmudev-header--action">

                        <button class="wpmudev-box--action">

                            <span class="wpmudev-icon--plus" aria-hidden="true"></span>

                            <span class="wpmudev-sr-only"><?php _e( "Open list of custom votes", Forminator::DOMAIN ); ?></span>

                        </button>

                    </div>

                </div>

                <div class="wpmudev-box-section">

                    <div class="wpmudev-section--text">

	                    <?php
	                    $custom_votes = $this->map_custom_votes();
	                    $empty_text   = __( "No one has added custom votes just yet.", Forminator::DOMAIN );
	                    if ( ! empty( $custom_votes ) && count( $custom_votes ) > 0 ) {
		                    foreach ( $custom_votes as $element_id => $custom_vote ) {
			                    echo '<b>' . $this->get_field_title( $element_id ) . '</b><br/>';
			                    foreach ( $custom_vote as $answer => $vote ) {
				                    echo '' . $answer . ': ' . $vote . '<br/>';
			                    }
			                    echo '<hr/>';
		                    }

	                    } else {

		                    echo '<p>' . $empty_text . '</p>';

	                    } ?>

                    </div>

                </div>

            </div>

        </div>

        <div class="wpmudev-col col-12 col-sm-6">

            <div id="forminator-entries--export" class="wpmudev-box wpmudev-can--hide">

                <div class="wpmudev-box-header">

                    <div class="wpmudev-header--text">

                        <h2 class="wpmudev-subtitle"><?php _e( "Export Votes", Forminator::DOMAIN ); ?></h2>

                    </div>

                    <div class="wpmudev-header--action">

                        <button class="wpmudev-box--action">

                            <span class="wpmudev-icon--plus" aria-hidden="true"></span>

                            <span class="wpmudev-sr-only"><?php _e( "Open Export Votes", Forminator::DOMAIN ); ?></span>

                        </button>

                    </div>

                </div>

                <div class="wpmudev-box-section">

                    <div class="wpmudev-section--text">

						<?php $path = forminator_plugin_dir(); ?>

                        <div id="forminator-export--buttons">

                            <form class="wpmudev-export--form" method="post">
                                <button class="wpmudev-button"><?php _e( "Export Votes", Forminator::DOMAIN ); ?></button>
                                <input type="hidden" name="forminator_export" value="1">
                                <input type="hidden" name="form_id" value="<?php echo $this->form_id ?>">
                                <input type="hidden" name="form_type" value="poll">
								<?php wp_nonce_field( 'forminator_export', '_forminator_nonce' ) ?>
                            </form>

							<!--
							<div class="wpmudev-export--prev">
								<button class="wpmudev-button wpmudev-button-ghost wpmudev-open-modal" data-modal="exports" data-nonce="<?php echo wp_create_nonce( 'forminator_load_exports' ) ?>" data-form-id="<?php echo $_GET['form_id'] ?>"><?php _e( "Previous Votes", Forminator::DOMAIN ); ?></button>
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

    <div class="wpmudev-row--border" aria-hidden="true"></div>

    <div class="wpmudev-row">

        <div class="wpmudev-col col-12">

            <div class="wpmudev-box">

                <div class="wpmudev-box-header">

                    <div class="wpmudev-header--text">

                        <h2 class="wpmudev-subtitle"><?php echo $this->get_poll_question(); ?></h2>

                    </div>

                </div>

                <div class="wpmudev-box-section">

                    <div class="wpmudev-section--text">

                        <div class="wpmudev-entries--chart">

                            <div id="forminator-chart-poll" class="forminator-poll--chart" style="width: 100%; height: 400px;"></div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>