<div id="wpmudev-dashboard-widget-resume" class="wpmudev-row">

    <div class="wpmudev-col col-12">

        <div class="wpmudev-box">

			<div class="wpmudev-box-body">

				<div class="wpmudev-box-character" aria-hidden="true"><?php $this->render("general/characters/character-two" ); ?></div>

				<div class="wpmudev-box-content">

                    <div class="wpmudev-dashboard-resume-text">

                        <?php if ( $has_modules === true ) { ?>

                            <h2><?php _e( "WELCOME BACK.", Opt_In::TEXT_DOMAIN ); ?></h2>

                            <p><?php _e( "We have collected some conversion data that is summarized on this page.", Opt_In::TEXT_DOMAIN ); ?></p>

                        <?php } else { ?>

                            <h2><?php _e( "LET'S GET YOU STARTED", Opt_In::TEXT_DOMAIN ); ?></h2>

                            <p><?php _e( "First, choose what type of marketing material you want to set-up.", Opt_In::TEXT_DOMAIN ); ?></p>

                        <?php } ?>

                    </div>

                    <div class="wpmudev-dashboard-resume-table">

                        <table class="wpmudev-table" cellspacing="0" cellpadding="0">

                            <tbody>

                                <tr>

                                    <th><?php _e( "Active Modules", Opt_In::TEXT_DOMAIN ); ?></th>

                                    <td><?php echo count($active_modules); ?></td>

                                </tr>

                                <tr>

                                    <th><?php _e( "Today's Conversions", Opt_In::TEXT_DOMAIN ); ?></th>

                                    <td><?php echo $today_total_conversions; ?></td>

                                </tr>

                                <?php if ( $most_converted_module !== '' ) { ?>

                                    <tr>

                                        <th><?php _e( "Most Conversions (All Time)", Opt_In::TEXT_DOMAIN ); ?></th>

                                        <td><?php echo $most_converted_module; ?></td>

                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>

				</div>

			</div>

		</div><?php // .wpmudev-box ?>

    </div>

</div><?php // #wpmudev-dashboard-widget-resume ?>