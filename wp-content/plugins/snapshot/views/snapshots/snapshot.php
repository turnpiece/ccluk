<?php

/**
 * @global array $item
 * @global string $action
 */

$action = 'add' === $action ? 'add' : 'update';
$update = 'update' === $action;

$time_key = time();

while ( isset( WPMUDEVSnapshot::instance()->config_data['items'][ $time_key ] ) ) {
	$time_key = time();
}

if ( ! $update ) {
	$item['timestamp'] = $time_key;
}

$requirements_test = Snapshot_Helper_Utility::check_system_requirements();
$checks = $requirements_test['checks'];
$all_good = $requirements_test['all_good'];
$warning = $requirements_test['warning'];

?>

<section id="header">
	<h1><?php esc_html_e( 'Snapshots', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<?php
$this->render(
	'snapshots/partials/create-snapshot-progress', false, array(
		'item' => $item,
		'time_key' => $time_key
	), false, false
);
?>

<form id="snapshot-add-update" method="post" action="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>">
	<input type="hidden" id="snapshot-action" name="snapshot-action" value="<?php echo $update ? 'update' : 'add'; ?>">
	<input type="hidden" id="snapshot-item" name="snapshot-item" value="<?php echo esc_attr( $item['timestamp'] ); ?>">
	<input type="hidden" id="snapshot-data-item" name="snapshot-data-item" value="<?php echo esc_attr( $time_key ); ?>">

	<input type="hidden" name="snapshot-ajax-nonce" id="snapshot-ajax-nonce" value="<?php echo esc_attr( wp_create_nonce( 'snapshot-ajax-nonce' ) ); ?>" />

	<?php wp_nonce_field( 'snapshot-nonce', 'snapshot-noonce-field' ); ?>
	<div id="container" class="snapshot-three wps-page-wizard">

		<section class="wpmud-box new-snapshot-main-box">

			<?php if ( $update ) : ?>

				<div class="wpmud-box-title">
					<h3><?php esc_html_e( 'Edit Snapshot', SNAPSHOT_I18N_DOMAIN ); ?>: <?php echo esc_html( $item['name'] ); ?></h3>
				</div>

			<?php else : ?>

				<div class="wpmud-box-title has-button>">
					<h3><?php esc_html_e( 'Snapshot Wizard', SNAPSHOT_I18N_DOMAIN ); ?></h3>

					<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>"
					   class="button button-small button-gray button-outline"><?php esc_html_e( 'Back', SNAPSHOT_I18N_DOMAIN ); ?></a>

				</div>

			<?php endif; ?>

			<div class="wpmud-box-content">

				<?php $this->render( "common/requirements-test", false, $requirements_test, false, false ); ?>

				<div class="wpmud-box-tab configuration-box<?php echo $all_good ? ' open' : ''; ?>">

					<div class="wpmud-box-tab-title can-toggle">
						<h3><?php esc_html_e( 'Configuration', SNAPSHOT_I18N_DOMAIN ); ?></h3>
						<?php if ( $all_good ): ?>
							<i class="wps-icon i-arrow-right"></i>
						<?php endif; ?>
					</div>

					<?php if ( $all_good ): ?>

						<div class="wpmud-box-tab-content">

							<div id="wps-check-notice" class="row">

								<div class="col-xs-12">

									<div class="wps-auth-message <?php echo $all_good ? ( $warning ? 'warning' : 'success' ) : 'error'; ?>">
										<?php if ( ! $all_good ) { ?>
											<p><?php esc_html_e( 'You must meet the server requirements before proceeding.', SNAPSHOT_I18N_DOMAIN ); ?></p>
										<?php } else if ( $warning ) { ?>
											<p><?php esc_html_e( 'You have 1 or more requirements warnings. You can proceed, however Snapshot may run into issues due to the warnings.', SNAPSHOT_I18N_DOMAIN ); ?></p>
										<?php } else { ?>
											<p><?php esc_html_e( 'You meet the server requirements. You can proceed now.', SNAPSHOT_I18N_DOMAIN ); ?></p>
										<?php } ?>
									</div>

								</div>

							</div>

							<?php if ( ! $update && is_multisite() ) { ?>

								<div id="wps-new-subsite" class="row">

									<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

										<label class="label-box"><?php esc_html_e( 'Blog to backup', SNAPSHOT_I18N_DOMAIN ); ?></label>

									</div>

									<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
										<?php

										$submitted = isset( $item['blog-id'] );

										if ( $submitted ) {
											$blog_info = get_blog_details( $item['blog-id'] );
										}

										?>

										<div class="wpmud-box-mask">
											<div class="wps-subsite-map">

												<?php

												if ( $submitted ) {
													if ( isset( $blog_info ) ) {
														printf( '%s (%s)', esc_html( $blog_info->blogname ), esc_html( $blog_info->domain ) );
													} else {
														esc_html_e( 'Unknown Blog', SNAPSHOT_I18N_DOMAIN );
													}
												} else {
                                                ?>


													<input type="hidden" name="snapshot-blog-id" id="snapshot-blog-id"
													       value="<?php echo esc_attr( $GLOBALS['current_blog']->blog_id ); ?>" />

													<div id="snapshot-blog-search-success" style="display: block;">
														<span id="snapshot-blog-name">
															<?php echo esc_html( trailingslashit( site_url() ) ); ?>
														</span>
														<button id="snapshot-blog-id-change" class="button button-small button-gray button-outline">
															<?php esc_html_e( 'Change', SNAPSHOT_I18N_DOMAIN ); ?>
														</button>
													</div>
													<div id="snapshot-blog-search" style="display: none;">
														<span id="snapshot-blog-search-error" style="color: #FF0000; display: none;">
															<?php esc_html_e( 'Error on blog lookup. Try again', SNAPSHOT_I18N_DOMAIN ); ?>
															<br>
														</span>
														<?php

														if ( ! is_subdomain_install() ) {
															echo esc_html( trailingslashit( site_url() ) );
														}
                                                        ?>

														<input name="snapshot-blog-id-search" id="snapshot-blog-id-search" value="" style="width: 20%;">

														<?php

														if ( is_subdomain_install() ) {
															$blog_path = trailingslashit( network_site_url( $GLOBALS['current_blog']->path ) );
															$blog_path = preg_replace( '/(http|https):\/\/|/', '', $blog_path );

															printf( '.%s', esc_html( $blog_path ) );
														}
                                                        ?>

														<span class="wps-spinner" style="display: none;"></span>

														<p class="description">
															<small>
																<?php
                                                                if ( is_subdomain_install() ) {
																	esc_html_e( 'Enter the blog subdomain prefix (e.g. site1), blog ID (e.g. 22), or mapped domain, or leave blank for the primary site.', SNAPSHOT_I18N_DOMAIN );
																} else {
																	esc_html_e( 'Enter the path, blog ID (e.g. 22), or leave blank for the primary site.', SNAPSHOT_I18N_DOMAIN );
																}
																esc_html_e( ' Once the form is submitted this cannot be changed.', SNAPSHOT_I18N_DOMAIN );

																?>
															</small>
														</p>

														<div class="wps-subsite-btns">
															<button id="snapshot-blog-id-lookup" class="button button-small button-blue">
																<?php esc_html_e( 'Lookup', SNAPSHOT_I18N_DOMAIN ); ?>
															</button>
															<button id="snapshot-blog-id-cancel" class="button button-small button-gray">
																<?php esc_html_e( 'Cancel', SNAPSHOT_I18N_DOMAIN ); ?>
															</button>
															<input type="hidden" name="snapshot-ajax-nonce" id="snapshot-ajax-nonce" value="<?php echo esc_attr( wp_create_nonce( 'snapshot-ajax-nonce' ) ); ?>" />
														</div>

													</div>

												<?php } ?>

											</div><!-- #wps-subsite-map -->
										</div><!-- #wpmud-box-mask -->
									</div>

								</div>

								<?php

							} elseif ( ! $update ) {
								printf( '<input type="hidden" id="snapshot-blog-id" name="snapshot-blog-id" value="%d">', esc_attr( $GLOBALS['wpdb']->blogid ) );
							}

							?>

							<div id="wps-new-destination" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php esc_html_e( 'Destination', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title">
                                        <?php
										echo wp_kses_post(
											sprintf(
												__( 'Choose where to send this snapshot. Add new destinations via the <a href="%s">Destinations</a> tab.', SNAPSHOT_I18N_DOMAIN ),
												esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-destinations' ) )
												)
											);
										?>
										</label>

										<?php
										$all_destinations = WPMUDEVSnapshot::instance()->config_data['destinations'];

										if ( ! isset( $item['destination'] ) ) {
											$item['destination'] = "local";
										}
										$selected_destination = $item['destination'];
										$destinationClasses = WPMUDEVSnapshot::instance()->get_setting( 'destinationClasses' );

										// This global is set within the next calling function. Helps determine which set of descriptions to show.
										global $snapshot_destination_selected_type;

										Snapshot_Helper_UI::destination_select_radio_boxes( $all_destinations, $selected_destination, $destinationClasses );
										?>

									</div>

								</div>

							</div>

							<div id="wps-custom-directory" class="row">
								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
									<label class="label-box" for="snapshot-destination-directory">
										<?php esc_html_e( 'Directory (optional)', SNAPSHOT_I18N_DOMAIN ); ?>
									</label>
								</div>
								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
									<input
										type="text"
										id="snapshot-destination-directory"
										name="snapshot-destination-directory"
										value="<?php echo ! empty( $item['destination-directory'] ) ? esc_attr( $item['destination-directory'] ) : ''; ?>"
									/>
									<p>
										<?php esc_html_e( 'The optional Directory can be used to override or supplement the selected destination directory value.', SNAPSHOT_I18N_DOMAIN ); ?>
										<?php esc_html_e( 'If "local server" is selected and if the directory does not start with a forward slash "/" the directory will be relative to the site root', SNAPSHOT_I18N_DOMAIN ); ?>
									</p>
									<p>
										<?php esc_html_e( 'This field supports tokens you can use to create dynamic values.', SNAPSHOT_I18N_DOMAIN ); ?>
										<?php esc_html_e( 'You can use any combination of the following tokens.', SNAPSHOT_I18N_DOMAIN ); ?>
										<?php esc_html_e( 'Use the forward slash "/" to separate directory elements.', SNAPSHOT_I18N_DOMAIN ); ?>
									</p>
									<p>
										<code>[DEST_PATH]</code> -
										<?php esc_html_e( 'This represents the Directory/Bucket used by the selected Backup Destination or if local, the Settings Folder location. This can be used to supplement the value entered into this Snapshot. If [DEST_PATH] is not used the Directory value here will override the complete value from the selected Destination.', SNAPSHOT_I18N_DOMAIN ); ?>
									</p>
									<p>
										<code>[SITE_DOMAIN]</code> -
										<?php esc_html_e( 'This represents the full domain of the selected site per this snapshot.', SNAPSHOT_I18N_DOMAIN ); ?>
									</p>
									<p>
										<code>[SNAPSHOT_ID]</code> -
										<?php esc_html_e( 'This is the unique ID assigned to this Snapshot.', SNAPSHOT_I18N_DOMAIN ); ?>
									</p>
								</div>
							</div>

							<div id="wps-new-files" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php esc_html_e( 'Files', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title"><?php esc_html_e( 'Select which files you want to include.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<?php

										if ( ! isset( $item['blog-id'] ) ) {
											$item['blog-id'] = $GLOBALS['wpdb']->blogid;
										}

										if ( ! isset( $item['files-option'] ) ) {
											$item['files-option'] = 'all'; // Default to all files
										}

										if ( ! isset( $item['files-sections'] ) ) {
											$item['files-sections'] = array();
										}
                                        ?>

										<div class="wps-input--group">

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-files-option" id="snapshot-files-option-none" value="none"
													       name="snapshot-files-option"<?php checked( $item['files-option'], 'none' ); ?>>

													<label for="snapshot-files-option-none"></label>

												</div>

												<label for="snapshot-files-option-none"><?php esc_html_e( "Don't include any files", SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<?php
                                            $blog_upload_path = Snapshot_Helper_Utility::get_blog_upload_path( $item['blog-id'] );

											if ( ! empty( $blog_upload_path ) ) {
                                            ?>

												<div class="wps-input--item">

													<div class="wps-input--radio">

														<input type="radio" class="snapshot-files-option" id="snapshot-files-option-all" value="all" name="snapshot-files-option"<?php checked( $item['files-option'], 'all' ); ?>>

														<label for="snapshot-files-option-all"></label>

													</div>

													<label for="snapshot-files-option-all">
														<?php esc_html_e( 'Include common files', SNAPSHOT_I18N_DOMAIN ); ?>:
														<span class="snapshot-backup-files-sections-main-only"
                                                        <?php
                                                        if ( ! is_main_site( $item['blog-id'] ) ) {
															echo ' style="display:none" ';
														}
                                                        ?>
                                                        >
														<?php esc_html_e( 'themes, plugins,', SNAPSHOT_I18N_DOMAIN ); ?>
													</span>
														<?php esc_html_e( 'media', SNAPSHOT_I18N_DOMAIN ); ?>
														(<span class="snapshot-media-upload-path"><?php echo esc_html( $blog_upload_path ); ?></span>)
													</label>

												</div>

											<?php } ?>

											<div class="wps-input--item">

												<div class="wps-input--radio">
													<input type="radio" class="snapshot-files-option" id="snapshot-files-option-selected" value="selected" name="snapshot-files-option"<?php checked( $item['files-option'], 'selected' ); ?>>
													<label for="snapshot-files-option-selected"></label>
												</div>

												<label for="snapshot-files-option-selected"><?php esc_html_e( 'Only include selected files', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<div id="snapshot-selected-files-container"
                                            <?php
                                            if ( 'none' === $item['files-option'] || 'all' === $item['files-option'] ) {
												echo ' class="hidden"';
											}
                                            ?>
                                            >

												<ul id="snapshot-select-files-option" class="wpmud-box-gray">

													<li class="wps-input--item">

														<div class="wps-input--checkbox">

															<input type="checkbox" class="snapshot-backup-sub-options"
                                                            <?php
                                                            if ( array_search( 'themes', $item['files-sections'], true ) !== false ) {
																echo ' checked="checked" ';
															}
                                                            ?>
                                                             id="snapshot-files-option-themes" value="themes" name="snapshot-files-sections[themes]">

															<label for="snapshot-files-option-themes"></label>

														</div>

														<label for="snapshot-files-option-themes"><?php esc_html_e( 'All Themes', SNAPSHOT_I18N_DOMAIN ); ?></label>

													</li>

													<li class="wps-input--item">

														<div class="wps-input--checkbox">

															<input type="checkbox" class="snapshot-backup-sub-options"
                                                            <?php
                                                            if ( array_search( 'plugins', $item['files-sections'], true ) !== false ) {
																echo ' checked="checked" ';
															}
                                                            ?>
                                                             id="snapshot-files-option-plugins" value="plugins" name="snapshot-files-sections[plugins]">

															<label for="snapshot-files-option-plugins"></label>

														</div>

														<label for="snapshot-files-option-plugins"><?php esc_html_e( 'All Plugins', SNAPSHOT_I18N_DOMAIN ); ?></label>

													</li>

													<?php if ( is_multisite() ) { ?>

														<li class="wps-input--item">

															<div class="wps-input--checkbox">

																<input type="checkbox" class="snapshot-backup-sub-options"
                                                                <?php
                                                                if ( array_search( 'plugins', $item['files-sections'], true ) !== false ) {
																	echo ' checked="checked" ';
																}
                                                                ?>
                                                                 id="snapshot-files-option-mu-plugins" value="mu-plugins" name="snapshot-files-sections[mu-plugins]">

																<label for="snapshot-files-option-mu-plugins"></label>

															</div>

															<label for="snapshot-files-option-mu-plugins"><?php esc_html_e( 'MU-Plugins: All active and inactive plugins will be included', SNAPSHOT_I18N_DOMAIN ); ?></label>

														</li>

													<?php } ?>

													<li class="wps-input--item">

														<div class="wps-input--checkbox">

															<input type="checkbox" class="snapshot-backup-sub-options"
                                                            <?php
                                                            if ( array_search( 'media', $item['files-sections'], true ) !== false ) {
																echo ' checked="checked" ';
															}
                                                            ?>
                                                             id="snapshot-files-option-media" value="media" name="snapshot-files-sections[media]">

															<label for="snapshot-files-option-media"></label>

														</div>

														<label for="snapshot-files-option-media"><?php esc_html_e( 'Media files:', SNAPSHOT_I18N_DOMAIN ); ?>
															<span class="snapshot-media-upload-path"><?php echo esc_html( Snapshot_Helper_Utility::get_blog_upload_path( $item['blog-id'] ) ); ?></span></label>

													</li>

													<li class="wps-input--item">

														<div class="wps-input--checkbox">

															<input type="checkbox" class="snapshot-backup-sub-options"
                                                            <?php
                                                            if ( array_search( 'config', $item['files-sections'], true ) !== false ) {
																echo ' checked="checked" ';
															}
                                                            ?>
                                                             id="snapshot-files-option-config" value="config" name="snapshot-files-sections[config]">

															<label for="snapshot-files-option-config"></label>

														</div>

														<label for="snapshot-files-option-config"><?php esc_html_e( 'wp-config.php', SNAPSHOT_I18N_DOMAIN ); ?></label>

													</li>

													<li class="wps-input--item">

														<div class="wps-input--checkbox">

															<input type="checkbox" class="snapshot-backup-sub-options"
                                                            <?php
                                                            if ( array_search( 'htaccess', $item['files-sections'], true ) !== false ) {
																echo ' checked="checked" ';
															}
                                                            ?>
                                                             id="snapshot-files-option-htaccess" value="htaccess" name="snapshot-files-sections[htaccess]">

															<label for="snapshot-files-option-htaccess"></label>

														</div>

														<label for="snapshot-files-option-htaccess"><?php esc_html_e( '.htaccess', SNAPSHOT_I18N_DOMAIN ); ?></label>

													</li>

												</ul>

											</div>

											<?php
                                            if ( ! isset( $item['destination-sync'] ) ) {
												$item['destination-sync'] = "archive";
											}
                                            ?>

											<?php if ( Snapshot_Helper_Utility::is_pro() ) { ?>

												<div id="snapshot-selected-files-sync-container">

													<label class="label-title"><?php esc_html_e( 'Dropbox Only - Select Archive or Mirroring option for this Snapshot.', SNAPSHOT_I18N_DOMAIN ); ?></label>

													<ul class="wpmud-box-gray wps-input--group">

														<?php
                                                        $_is_mirror_disabled = ' disabled="disabled" ';

														if ( isset( $item['destination'] ) ) {

															$destination_key = $item['destination'];

															if ( isset( WPMUDEVSnapshot::instance()->config_data['destinations'][ $destination_key ] ) ) {

																$destination = WPMUDEVSnapshot::instance()->config_data['destinations'][ $destination_key ];

																if ( ( isset( $destination['type'] ) ) && ( "dropbox" === $destination['type'] ) ) {

																	$_is_mirror_disabled = '';

																}

															}

														}
                                                        ?>

														<li class="wps-input--item">

															<div class="wps-input--radio">

																<input type="radio" name="snapshot-destination-sync" id="snapshot-destination-sync-archive" value="archive" class="snapshot-destination-sync"
                                                                <?php
                                                                if ( "archive" === $item['destination-sync'] ) {
																	echo ' checked="checked" ';
																}
                                                                ?>
                                                                 />

																<label for="snapshot-destination-sync-archive"></label>

															</div>

															<label for="snapshot-destination-sync-archive"><?php echo wp_kses_post( __( '<strong>Archive</strong> - (Default) Selecting archive will produce a zip archive. This is standard method for backing up your site. A single zip archive will be created for files and database tables.', SNAPSHOT_I18N_DOMAIN ) ); ?></label>

														</li>

														<li class="wps-input--item">

															<div class="wps-input--radio">

																<input type="radio" <?php echo esc_attr( $_is_mirror_disabled ); ?> name="snapshot-destination-sync" id="snapshot-destination-sync-mirror" value="mirror" class="snapshot-destination-sync"
																<?php
                                                                if ( "mirror" === $item['destination-sync'] ) {
																	echo ' checked="checked" ';
																}
                                                                ?>
                                                                />

																<label for="snapshot-destination-sync-mirror"></label>

															</div>

															<label for="snapshot-destination-sync-mirror"><?php echo wp_kses_post( __( '<strong>Mirror/Sync</strong> - <strong>Dropbox ONLY</strong> Select mirroring if you want to replicate your site file structure in Dropbox. If you include database tables they will be added as a zipped archive. <strong>There is currently no restore option for Mirror/Sync.</strong>', SNAPSHOT_I18N_DOMAIN ) ); ?></label>

														</li>

													</ul>

												</div>

											<?php
                                            } else {

												$message = sprintf( __( '<p>Additional options are available for the \'Dropbox\' destination.</p><p>Destinations are available to you in Snapshot Pro from WPMU Dev: <a href="%s">Upgrade Now</a></p>', SNAPSHOT_I18N_DOMAIN ), esc_url( 'https://premium.wpmudev.org/project/snapshot' ) );

												echo wp_kses_post( $message );

											}
                                            ?>

											<label class="label-title"><?php esc_html_e( 'Add any custom URLs you want to not include in this snapshot.', SNAPSHOT_I18N_DOMAIN ); ?></label>

											<?php
											if ( ( isset( $item['files-ignore'] ) ) && ( count( $item['files-ignore'] ) ) ) {
												$snapshot_files_ignore = ( implode( "\n", $item['files-ignore'] ) );
											} else {
												$snapshot_files_ignore = '';
											}
											?>
											<textarea name="snapshot-files-ignore" id="snapshot-files-ignore" cols="20" rows="5"><?php echo wp_kses_post( $snapshot_files_ignore ); ?></textarea>

											<p>
												<small>
													<?php
													echo wp_kses_post( __( 'URLs can be files and must be listed one per line. The exclude feature uses pattern matching, so typing twentyten will exclude the twentyten folder, as well as any filters with twentyten in the filename.', SNAPSHOT_I18N_DOMAIN ) );
                                                    ?>
												</small>
											</p>
											<p>
												<small>
													<?php echo wp_kses_post( __( 'Example: to exclude the Twenty Ten theme, you can use twentyten, theme/twentyten or public/wp-content/theme/twentyten. <strong>The local folder is excluded from Snapshot backups by default.</strong>', SNAPSHOT_I18N_DOMAIN ) ); ?>
												</small>
											</p>

										</div>

									</div>

								</div>

							</div>

							<div id="wps-new-database" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php esc_html_e( 'Database', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<?php
                                        if ( ! isset( $item['blog-id'] ) ) {
											$item['blog-id'] = $wpdb->blogid;
										}

										$table_sets = Snapshot_Helper_Utility::get_database_tables( $item['blog-id'] );

										if ( isset( WPMUDEVSnapshot::instance()->config_data['config']['tables_last'][ $item['blog-id'] ] ) ) {

											$blog_tables_last = WPMUDEVSnapshot::instance()->config_data['config']['tables_last'][ $item['blog-id'] ];

										} else {

											$blog_tables_last = array();

										}

										if ( ! isset( $item['tables-option'] ) ) {

											$item['tables-option'] = "all";

										}
                                        ?>

										<label class="label-title"><?php esc_html_e( 'Select which database tables you want to include.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<div class="wps-input--group">

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-tables-option" id="snapshot-tables-option-none" value="none"
                                                    <?php
                                                    if ( "none" === $item['tables-option'] ) {
														echo ' checked="checked" ';
													}
                                                    ?>
                                                     name="snapshot-tables-option">

													<label for="snapshot-tables-option-none"></label>

												</div>

												<label for="snapshot-tables-option-none"><?php esc_html_e( 'Don\'t include any database tables', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-tables-option" id="snapshot-tables-option-all" value="all"
                                                    <?php
                                                    if ( "all" === $item['tables-option'] ) {
														echo ' checked="checked" ';
													}
                                                    ?>
                                                     name="snapshot-tables-option">

													<label for="snapshot-tables-option-all"></label>

												</div>

												<label for="snapshot-tables-option-all"><?php esc_html_e( 'Include all database tables', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-tables-option" id="snapshot-tables-option-selected" value="selected"
                                                    <?php
                                                    if ( "selected" === $item['tables-option'] ) {
														echo ' checked="checked" ';
													}
                                                    ?>
                                                     name="snapshot-tables-option">

													<label for="snapshot-tables-option-selected"></label>

												</div>

												<label for="snapshot-tables-option-selected"><?php esc_html_e( 'Only include selected database tables', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

										</div>

										<div id="snapshot-selected-tables-container" class="wpmud-box-gray" style="
                                        <?php
                                        if ( ( "none" === $item['tables-option'] ) || ( "all" === $item['tables-option'] ) ) {
											echo ' display:none; ';
										}
                                        ?>
                                        ">

											<?php
											$tables_sets_idx = array(
												'global' => __( "WordPress Global Tables", SNAPSHOT_I18N_DOMAIN ),
												'wp'     => __( "WordPress core Tables", SNAPSHOT_I18N_DOMAIN ),
												'non'    => __( "Non-WordPress Tables", SNAPSHOT_I18N_DOMAIN ),
												'other'  => __( "Other Tables", SNAPSHOT_I18N_DOMAIN ),
												'error'  => __( "Error Tables - These tables are skipped for the noted reasons.", SNAPSHOT_I18N_DOMAIN )
											);

											foreach ( $tables_sets_idx as $table_set_key => $table_set_title ) {

												if ( ( isset( $table_sets[ $table_set_key ] ) ) && ( count( $table_sets[ $table_set_key ] ) ) ) {

													$display_set = 'block';

												} else {

													$display_set = 'none';

												}
                                                ?>

												<div id="snapshot-tables-<?php echo esc_attr( $table_set_key ); ?>-set" style="display: <?php echo esc_attr( $display_set ); ?>">

													<h3><?php echo esc_html( $table_set_title ); ?><?php if ( 'error' !== $table_set_key ) { ?>
															<a class="snapshot-table-select-all" href="#" id="snapshot-table-<?php echo esc_attr( $table_set_key ); ?>-select-all"><?php esc_html_e( 'Select all', SNAPSHOT_I18N_DOMAIN ); ?></a><?php } ?>
													</h3>

													<?php if ( "global" === $table_set_key ) { ?>

														<p class="description"><?php echo wp_kses_post( __( 'These global user tables contain blog specific user information which can be included as part of the snapshot archive. Only users whose primary blog matches this selected blog will be included. <strong>Superadmin users will not be included in the sub-site archive.</strong>', SNAPSHOT_I18N_DOMAIN ) ); ?></p>

													<?php } ?>

													<ul class="snapshot-table-list" id="snapshot-table-list-<?php echo esc_attr( $table_set_key ); ?>">

														<?php
														check_admin_referer( 'snapshot-nonce', 'snapshot-noonce-field');
                                                        if ( ( isset( $table_sets[ $table_set_key ] ) ) && ( count( $table_sets[ $table_set_key ] ) ) ) {

															$tables = $table_sets[ $table_set_key ];

															foreach ( $tables as $table_key => $table_name ) {

																$is_checked = '';

																if ( 'error' === $table_set_key ) {
                                                                ?>

																	<li style="clear:both"><?php echo wp_kses_post( $table_name['name'] ); ?>
																		&ndash; <?php echo wp_kses_post( $table_name['reason'] ); ?></li>

																<?php
                                                                } else {

																	if ( isset( $_REQUEST['backup-tables'] ) ) {

																		if ( isset( $_REQUEST['backup-tables'][ $table_set_key ][ $table_key ] ) ) {
																			$is_checked = ' checked="checked" ';
																		}

																	} else {

																		if ( isset( $_GET['page'] ) && "snapshots_new_panel" === $_GET['page'] ) {
																			if ( isset( $blog_tables_last[ $table_set_key ] ) && array_search( $table_key, $blog_tables_last[ $table_set_key ], true ) !== false ) {
																				$is_checked = ' checked="checked" ';
																			}
																		}

																		if ( isset( $_GET['page'] ) && ( "snapshot_pro_snapshots" === $_GET['page'] || 'snapshot_pro_snapshots' === $_GET['page'] ) ) {

																			if ( isset( $item['tables-sections'] ) ) {

																				if ( isset( $item['tables-sections'][ $table_set_key ][ $table_key ] ) ) {
																					$is_checked = ' checked="checked" ';
																				}

																			} else if ( isset( $item['tables'] ) ) {

																				if ( array_search( $table_key, $item['tables'], true ) !== false ) {
																					$is_checked = ' checked="checked" ';
																				}
																			}
																		}

																	}
                                                                    ?>

																	<li class="wps-input--item">

																		<div class="wps-input--checkbox">

																			<input type="checkbox" <?php echo esc_attr( $is_checked ); ?> class="snapshot-table-item" id="snapshot-tables-<?php echo esc_attr( $table_key ); ?>" value="<?php echo esc_attr( $table_key ); ?>" name="snapshot-tables[<?php echo esc_attr( $table_set_key ); ?>][<?php echo esc_attr( $table_key ); ?>]">

																			<label for="snapshot-tables-<?php echo esc_attr( $table_key ); ?>"></label>

																		</div>

																		<label for="snapshot-tables-<?php echo esc_attr( $table_key ); ?>"><?php echo esc_html( $table_name ); ?></label>

																	</li>

																<?php
                                                                }

															}

														} else {
                                                        ?>

															<li><?php esc_html_e( 'No Tables', SNAPSHOT_I18N_DOMAIN ); ?></li>

														<?php } ?>
													</ul>

												</div>

											<?php } ?>

										</div>

									</div>

								</div>

							</div>

							<div id="wps-new-frequency" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php esc_html_e( 'Frequency', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title"><?php esc_html_e( 'Would you like to schedule this snapshot to run regularly or once off?', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<div class="wps-input--group">

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input id="frequency-once" type="radio" name="frequency" value="once"
                                                    <?php
													checked( ! $update || isset( $item['interval'] ) && 'immediate' === $item['interval'] );
                                                    ?>
                                                    >

													<label for="frequency-once"></label>

												</div>

												<label for="frequency-once"><?php esc_html_e( 'Once off', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input id="frequency-daily" type="radio" name="frequency" value="schedule"
                                                    <?php
													checked( $update && isset( $item['interval'] ) && 'immediate' !== $item['interval'] );
                                                    ?>
                                                    >

													<label for="frequency-daily"></label>

												</div>

												<label for="frequency-daily"><?php esc_html_e( 'Run daily, weekly or monthly', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

										</div>

										<div id="snapshot-schedule-options-container" class="wpmud-box-gray">

											<h3><?php esc_html_e( 'Schedule', SNAPSHOT_I18N_DOMAIN ); ?></h3>

											<input type="hidden" id="snapshot-immediate" name="snapshot-interval" checked="checked" value="immediate" />

											<div class="schedule-inline-form">

												<select name="snapshot-interval" id="snapshot-interval">

													<?php
                                                    if ( isset( $item['interval'] ) ) {
														$item_interval = $item['interval'];
													} else {
														$item_interval = 'snapshot-weekly';
													}

													$scheds = (array) wp_get_schedules();
													foreach ( $scheds as $sched_key => $sched_item ) {
														if ( ! in_array( $sched_key, array( 'snapshot-daily', 'snapshot-weekly', 'snapshot-monthly' ), true ) ) {
															continue;
														}
														if ( substr( $sched_key, 0, strlen( 'snapshot-' ) ) === "snapshot-" ) {
															?>
															<option value="<?php echo esc_attr( $sched_key ); ?>"<?php if ( $item_interval === $sched_key ) echo ' selected="selected" '; ?>><?php echo esc_html( $sched_item['display'] ); ?></option>
                                                            <?php
														}
													}
													?>

												</select>

											<?php //if ( ( ! defined( 'DISABLE_WP_CRON' ) ) || ( DISABLE_WP_CRON == false ) ) { ?>

												<?php
												$default_time = new DateTime( 'monday 4am' );
												$timestamp = $default_time->format( 'U' ) + ( get_option( 'gmt_offset' ) * 3600 );
												$localtime = localtime( $timestamp, true );
												?>

												<div id="interval-offset">
													<div class="interval-offset-daily"
                                                    <?php
													if ( ( "snapshot-daily" === $item_interval ) || ( "snapshot-twicedaily" === $item_interval ) ) {
														echo ' style="display: inline-flex;" ';
													} else {
														echo ' style="display: none;" ';
													}
                                                    ?>
                                                     >
														<span class="inbetween"><?php esc_html_e( 'at', SNAPSHOT_I18N_DOMAIN ); ?></span>
														<select id="snapshot-interval-offset-daily-hour"
																name="snapshot-interval-offset[snapshot-daily][tm_hour]">
															<?php

															if ( !isset( $item['interval-offset']['snapshot-daily']['tm_hour'] ) ) {
																if ( ! isset( $item['interval-offset'] ) ) {
																	$item['interval-offset'] = array();
																}

																$item['interval-offset']['snapshot-daily']['tm_hour'] = $localtime['tm_hour'];
															}

															Snapshot_Helper_UI::form_show_hour_selector_options( $item['interval-offset']['snapshot-daily']['tm_hour'] );
															?>
														</select>&nbsp;&nbsp;
													</div>
													<div class="interval-offset-weekly"
                                                    <?php
													if ( ( "snapshot-weekly" === $item_interval ) || ( "snapshot-twiceweekly" === $item_interval ) ) {
														echo ' style="display: inline-flex;" ';
													} else {
														echo ' style="display: none;" ';
													}
                                                    ?>
                                                     >
														<span class="inbetween"><?php esc_html_e( 'on', SNAPSHOT_I18N_DOMAIN ); ?></span>
														<select id="snapshot-interval-offset-weekly-wday"
																name="snapshot-interval-offset[snapshot-weekly][tm_wday]">
															<?php
															if ( ! isset( $item['interval-offset']['snapshot-weekly']['tm_wday'] ) ) {
																$item['interval-offset']['snapshot-weekly']['tm_wday'] = $localtime['tm_wday'];
															}

															Snapshot_Helper_UI::form_show_wday_selector_options( $item['interval-offset']['snapshot-weekly']['tm_wday'] );
															?>
														</select>&nbsp;&nbsp;

														<span class="inbetween"><?php esc_html_e( 'at', SNAPSHOT_I18N_DOMAIN ); ?></span>
														<select id="snapshot-interval-offset-weekly-hour"
																name="snapshot-interval-offset[snapshot-weekly][tm_hour]">
															<?php
															if ( ! isset( $item['interval-offset']['snapshot-weekly']['tm_hour'] ) ) {
																$item['interval-offset']['snapshot-weekly']['tm_hour'] = $localtime['tm_hour'];
															}

															Snapshot_Helper_UI::form_show_hour_selector_options( $item['interval-offset']['snapshot-weekly']['tm_hour'] );

															?>
														</select>&nbsp;&nbsp;
													</div>
													<div class="interval-offset-monthly"
                                                    <?php
													if ( ( "snapshot-monthly" === $item_interval ) || ( "snapshot-twicemonthly" === $item_interval ) ) {
														echo ' style="display: inline-flex;" ';
													} else {
														echo ' style="display: none;" ';
													}
                                                    ?>
                                                     >

														<span class="inbetween"><?php esc_html_e( 'on', SNAPSHOT_I18N_DOMAIN ); ?></span>
														<select id="snapshot-interval-offset-monthly-mday"
																name="snapshot-interval-offset[snapshot-monthly][tm_mday]">
															<?php
															if ( ! isset( $item['interval-offset']['snapshot-monthly']['tm_mday'] ) ) {
																$item['interval-offset']['snapshot-monthly']['tm_mday'] = 1;
															}

															Snapshot_Helper_UI::form_show_mday_selector_options( $item['interval-offset']['snapshot-monthly']['tm_mday'] );
															?>
														</select>&nbsp;&nbsp;

														<span class="inbetween"><?php esc_html_e( 'at', SNAPSHOT_I18N_DOMAIN ); ?></span>
														<select id="snapshot-interval-offset-monthly-hour"
																name="snapshot-interval-offset[snapshot-monthly][tm_hour]">
															<?php
															if ( ! isset( $item['interval-offset']['snapshot-monthly']['tm_hour'] ) ) {
																$item['interval-offset']['snapshot-monthly']['tm_hour'] = $localtime['tm_hour'];
															}

															Snapshot_Helper_UI::form_show_hour_selector_options( $item['interval-offset']['snapshot-monthly']['tm_hour'] );
															?>
														</select>&nbsp;&nbsp;
													</div>
												</div>

											<?php // } ?>

											</div>

											<h3><?php esc_html_e( 'Storage Limit', SNAPSHOT_I18N_DOMAIN ); ?></h3>

											<div class="storage-inline-form">

												<span class="inbetween">Keep</span>

												<?php
												if ( ! isset( $item['archive-count'] ) ) {
													$item['archive-count'] = 3; // Default to limited number of recurring archives
												}

												?>
												<input type="number" name="snapshot-archive-count" id="snapshot-archive-count"
												       value="<?php echo esc_attr( $item['archive-count'] ); ?>" />

												<span class="inbetween"><?php esc_html_e( 'backups before removing older archives.', SNAPSHOT_I18N_DOMAIN ); ?></span>
											</div>

											<p>
												<small><?php esc_html_e( 'Snapshot will run backups as per your schedule and send them to your chosen destination. In addition to sending the copy off site we keep a local copy just in case things go wrong. Here you can specify how many local archives to keep before removing the oldest. If you put 0 here, Snapshot will keep all local archives.', SNAPSHOT_I18N_DOMAIN ); ?></small>
											</p>

											<h3><?php esc_html_e( 'Optional', SNAPSHOT_I18N_DOMAIN ); ?></h3>

											<div class="wps-input--item">

												<div class="wps-input--checkbox">

													<input type="checkbox" id="checkbox-run-backup-now" class="" value="1"<?php checked( ! $update ); ?>>

													<label for="checkbox-run-backup-now"></label>

												</div>

												<label for="checkbox-run-backup-now"><?php esc_html_e( 'Also run a backup now', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

<div class="wps-input--item">
	<div class="wps-input--checkbox">
		<input type="checkbox" id="snapshot-clean-remote" name="snapshot-clean-remote" <?php echo !empty($item['clean-remote']) ? 'checked' : ''; ?> value="1" />
		<label for="snapshot-clean-remote"></label>
	</div>
	<label for="snapshot-clean-remote">
		<?php esc_html_e('Also clean remote repositories', SNAPSHOT_I18N_DOMAIN); ?>
	</label>
</div>
										</div>

									</div>

								</div>

							</div>

							<div id="wps-new-name" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title"><?php esc_html_e( 'Give your snapshot a nice name!', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<?php
										if ( isset( $_REQUEST['snapshot-name'] ) ) {
											$snapshot_name = sanitize_text_field( $_REQUEST['snapshot-name'] );
										} else if ( isset( $item['name'] ) ) {
											$snapshot_name = sanitize_text_field( $item['name'] );
										} else {
											$snapshot_name = __( "Snapshot", SNAPSHOT_I18N_DOMAIN );
										}
										?>
										<input type="text" name="snapshot-name" id="snapshot-name" value="<?php echo esc_attr( $snapshot_name ); ?>">

										<p>
											<small><?php esc_html_e( 'Snapshot will automatically add the date and an ID to your archive ZIP file.', SNAPSHOT_I18N_DOMAIN ); ?></small>
										</p>

									</div>

								</div>

							</div>

							<div class="row">
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
									<div id="snapshot-ajax-warning" class="wps-auth-message warning" style="display: none;"></div>
									<div id="snapshot-ajax-error" class="wps-auth-message error" style="display: none;"></div>
								</div>
							</div>

							<div class="row">

								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

									<div class="form-button-container">

										<a class="button button-gray" href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>"><?php esc_html_e( 'Cancel', SNAPSHOT_I18N_DOMAIN ); ?></a>

										<button id="snapshot-add-update-submit" data-title-save-only="<?php esc_html_e( 'Save', SNAPSHOT_I18N_DOMAIN ); ?>" data-title-save-and-run="<?php esc_html_e( 'Save & Run Backup', SNAPSHOT_I18N_DOMAIN ); ?>" type="submit" class="button button-blue"><?php esc_html_e( 'Save & Run Backup', SNAPSHOT_I18N_DOMAIN ); ?></button>


									</div>

								</div>

							</div>

						</div>

					<?php endif; ?>

				</div>

			</div>

		</section>

	</div>
</form>

<?php if ( isset( $force_backup ) && $force_backup ) : ?>
	<script type="text/javascript">
        jQuery(function ($) {
            $('#checkbox-run-backup-now').attr('checked', 'checked');
            $('#snapshot-add-update-submit').click();
        });
	</script>
<?php endif; ?>