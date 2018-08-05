<div id="container" class="hidden snapshot-three wps-page-builder">

	<section class="wpmud-box">

		<div class="wpmud-box-title has-button">

			<div class="wps-title-progress">

				<h3><?php esc_html_e('Create Snapshot', SNAPSHOT_I18N_DOMAIN); ?></h3>

				<button  id="wps-show-full-log" data-wps-show-title="<?php esc_html_e('Show Full Log', SNAPSHOT_I18N_DOMAIN); ?>" data-wps-hide-title="<?php esc_html_e('Hide Full Log', SNAPSHOT_I18N_DOMAIN); ?>" class="button button-small button-outline button-gray"><?php esc_html_e('Show Full Log', SNAPSHOT_I18N_DOMAIN); ?></button>

			</div>

			<div class="wps-title-result hidden">

				<h3><?php esc_html_e('Snapshot Result', SNAPSHOT_I18N_DOMAIN); ?></h3>

			</div>

		</div>

		<div class="wpmud-box-content">

			<div class="row">

				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

						<div id="wps-build-error" class="hidden">

							<div class="wps-auth-message error">

								<p></p>

							</div>

							<p>

								<a href="#" id="wps-build-error-back" class="button button-outline button-gray"><?php esc_html_e('Back', SNAPSHOT_I18N_DOMAIN); ?></a>

								<a href="#" id="wps-build-error-again" class="button button-gray"><?php esc_html_e('Try Again', SNAPSHOT_I18N_DOMAIN); ?></a>

							</p>

						</div>

						<div id="wps-build-progress">

							<p><?php echo wp_kses_post( __('Your snapshot is in progress. <strong> You need to keep this page open for the backup to complete. </strong> Once your website has been backed up, it will be uploaded to your destination. If your site is small, this will only take a few minutes, but could take a couple of hours for larger sites.', SNAPSHOT_I18N_DOMAIN) ); ?></p>

							<div class="wpmud-box-gray">

								<div class="wps-loading-status wps-total-status wps-spinner">

									<p class="wps-loading-number">0%</p>

									<div class="wps-loading-bar">

										<div class="wps-loader">

											<span style="width: 0%"></span>

										</div>

									</div>

								</div>

							</div>

							<p><a id="wps-cancel" class="button button-outline button-gray"><?php esc_html_e('Cancel', SNAPSHOT_I18N_DOMAIN); ?></a></p>

						</div>

						<div id="wps-build-success" class="hidden">

							<div class="wps-auth-message success">

								<p><?php echo wp_kses_post( __('Your snapshot has been successfully created and stored! <a href="">View snapshot</a>.', SNAPSHOT_I18N_DOMAIN) ); ?></p>

							</div>

							<div class="wpmud-box-gray">

								<div class="wps-loading-status">

									<p class="wps-loading-number">100%</p>

									<div class="wps-loading-bar">

										<div class="wps-loader done">

											<span style="width: 100%"></span>

										</div>

									</div>

								</div>

							</div>

							<p>
								<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-snapshots') ); ?>&amp;snapshot-action=view&amp;item=<?php echo esc_attr( $item['timestamp'] ); ?>&amp;snapshot-noonce-field=<?php echo esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ); ?>" class="button button-gray"><?php esc_html_e('View Snapshot', SNAPSHOT_I18N_DOMAIN); ?></a>
							</p>

						</div>

						<div id="wps-log" class="hidden">

							<h4><?php esc_html_e('Snapshot Log', SNAPSHOT_I18N_DOMAIN); ?></h4>

							<div id="wps-log-resume" class="wpmud-box-gray">

								<div class="log-memory">

									<p><strong><?php esc_html_e('Memory limit', SNAPSHOT_I18N_DOMAIN); ?>:</strong><span class="number"><?php echo esc_html( ini_get( 'memory_limit' ) ); ?></span></p>

								</div>

								<div class="log-usage">

									<p><strong><?php esc_html_e('Usage', SNAPSHOT_I18N_DOMAIN); ?>:</strong><span class="number"><?php echo esc_html( Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) ); ?></span></p>

								</div>

								<div class="log-peak">

									<p><strong><?php esc_html_e('Peak', SNAPSHOT_I18N_DOMAIN); ?>:</strong><span class="number"><?php echo esc_html( Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) ) ); ?></span></p>

								</div>

							</div>

							<table cellpadding="0" cellspacing="0">

								<thead>

									<tr>

										<th class="wps-log-process"><?php esc_html_e('Process', SNAPSHOT_I18N_DOMAIN); ?></th>

										<th class="wps-log-progress"><?php esc_html_e('Progress', SNAPSHOT_I18N_DOMAIN); ?></th>

									</tr>

								</thead>

								<tbody>

									<tr id="wps-log-process-init">

										<td class="wps-log-process"><?php esc_html_e('Snapshot Initializing', SNAPSHOT_I18N_DOMAIN); ?></td>

										<td class="wps-log-progress">

											<div class="wps-log-progress-elements">

												<a class="snapshot-button-abort button button-small button-outline button-gray"><?php esc_html_e('Cancel', SNAPSHOT_I18N_DOMAIN); ?></a>

												<span class="wps-spinner hidden"></span>

												<div class="wps-loading-status">

													<p class="wps-loading-number">0%</p>

													<div class="wps-loading-bar">

														<div class="wps-loader">

															<span style="width: 0%"></span>

														</div>

													</div>

												</div>

											</div>

										</td>

									</tr>

									<?php // A template TR that will be clonned and managed by javascript ?>
									<tr id="wps-log-process-template" style="display: none;">

										<td class="wps-log-process name"></td>

										<td class="wps-log-progress">

											<div class="wps-log-progress-elements">

												<a class="snapshot-button-abort hidden button button-small button-outline button-gray"><?php esc_html_e('Cancel', SNAPSHOT_I18N_DOMAIN); ?></a>

												<span class="wps-spinner hidden"></span>

												<div class="wps-loading-status">

													<p class="wps-loading-number">0%</p>

													<div class="wps-loading-bar">

														<div class="wps-loader">

															<span style="width: 0%"></span>

														</div>

													</div>

												</div>

											</div>

										</td>

									</tr>

									<tr id="wps-log-process-finish">

										<td class="wps-log-process"><?php esc_html_e('Snapshot Finishing (creating zip archive of tables)', SNAPSHOT_I18N_DOMAIN); ?></td>

										<td class="wps-log-progress">

											<div class="wps-log-progress-elements">

												<a class="snapshot-button-abort hidden button button-small button-outline button-gray"><?php esc_html_e('Cancel', SNAPSHOT_I18N_DOMAIN); ?></a>

												<span class="wps-spinner hidden"></span>

												<div class="wps-loading-status">

													<p class="wps-loading-number">0%</p>

													<div class="wps-loading-bar">

														<div class="wps-loader done">

															<span style="width: 0%"></span>

														</div>

													</div>

												</div>

											</div>

										</td>

									</tr>

								</tbody>

							</table>

						</div><?php // #wps-log ?>

					</div><?php // .col-xs-12 ?>

				</div><?php // .row ?>

			</div><?php // .col ?>

		<?php // </div> .wpmud-box-content ?>

	</section>

</div>