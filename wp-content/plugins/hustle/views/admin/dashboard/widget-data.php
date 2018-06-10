<?php

?>

<div id="wpmudev-dashboard-widget-data" class="wpmudev-row">

    <div class="wpmudev-col col-12">

        <div class="wpmudev-box">

			<div class="wpmudev-box-body">

				<div class="wpmudev-box-content">

					<div class="wpmudev-dashboard-data-content">

						<?php if ( count( $top_active_modules ) ) { ?>

							<div class="wpmudev-dashboard-data-table">

								<table class="wpmudev-table" cellspacing="0" cellpadding="0">

									<thead>

										<tr>

											<th class="wpmudev-table-module_name"><?php _e( "Module Name", Opt_In::TEXT_DOMAIN ); ?></th>

											<th class="wpmudev-table-module_rate"><?php _e( "Rate", Opt_In::TEXT_DOMAIN ); ?></th>

											<th class="wpmudev-table-module_week"><?php _e( "7 Days", Opt_In::TEXT_DOMAIN ); ?></th>

											<th class="wpmudev-table-module_month"><?php _e( "30 Days", Opt_In::TEXT_DOMAIN ); ?></th>

											<th class="wpmudev-table-module_all"><?php _e( "All Time", Opt_In::TEXT_DOMAIN ); ?></th>

										</tr>

									</thead>

									<tbody>

										<?php foreach( $top_active_modules as $top_active_module ) : ?>

											<tr>

												<td class="wpmudev-table-module_name">

													<svg height="10" width="10">
														<circle cx="5" cy="5" r="4" fill="<?php echo $top_active_module['color'];?>" />
													</svg>

													<span><?php echo $top_active_module['module_name']; ?></span>

												</td>

												<td class="wpmudev-table-module_rate" data-name="<?php echo __( "Rate", Opt_In::TEXT_DOMAIN ); ?>"><?php echo $top_active_module['rate'];?></td>

												<td class="wpmudev-table-module_week" data-name="<?php echo __( "7 Days", Opt_In::TEXT_DOMAIN ); ?>"><?php echo $top_active_module['past_week'];?></td>

												<td class="wpmudev-table-module_month" data-name="<?php echo __( "30 Days", Opt_In::TEXT_DOMAIN ); ?>"><?php echo $top_active_module['past_month'];?></td>

												<td class="wpmudev-table-module_all" data-name="<?php  echo __( "All Time", Opt_In::TEXT_DOMAIN ); ?>"><?php echo $top_active_module['all_time'];?></td>

											</tr>

										<?php endforeach; ?>

									</tbody>

								</table>

							</div>

						<?php } ?>

						<?php if ( count( $top_active_modules ) ) { ?>

							<div class="wpmudev-dashboard-data-graph">

								<div id="hustle_chart" class="wpmudev-google-chart"></div>

							</div>

						<?php } else { ?>

							<div class="wpmudev-dashboard-data-no_content">

								<h5><?php _e( "No data available.", Opt_In::TEXT_DOMAIN ); ?></h5>

								<h6><?php _e( "Turn on tracking to activate stats and graphs.", Opt_In::TEXT_DOMAIN ); ?></h6>

							</div>

						<?php } ?>

					</div>

				</div>

			</div>

		</div><?php // .wpmudev-box ?>

    </div>

</div><?php // #wpmudev-dashboard-widget-data ?>