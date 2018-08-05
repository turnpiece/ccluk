<?php

global $wpdb;

check_admin_referer( 'snapshot-nonce', 'snapshot-noonce-field');
if ( isset( $_GET['snapshot-data-item'] ) ) {
	$data_item = $item['data'][ $_GET['snapshot-data-item'] ];
}

$backupFolder = WPMUDEVSnapshot::instance()->snapshot_get_item_destination_path( $item, $data_item );
if ( empty( $backupFolder ) ) {
	$backupFolder = WPMUDEVSnapshot::instance()->get_setting( 'backupBaseFolderFull' );
}

if ( ! empty( $data_item['filename'] ) ) {
	$manifest_filename = Snapshot_Helper_Utility::extract_archive_manifest( trailingslashit( $backupFolder ) . $data_item['filename'] );
	if ( $manifest_filename ) {
		$manifest_data = Snapshot_Helper_Utility::consume_archive_manifest( $manifest_filename );
		if ( $manifest_data ) {
			$item['MANIFEST'] = $manifest_data;
		}
	}
}

$requirements_test = Snapshot_Helper_Utility::check_system_requirements();
$checks = $requirements_test['checks'];
$all_good = $requirements_test['all_good'];
$warning = $requirements_test['warning'];
?>

<div id="snapshot-ajax-warning" class="updated fade" style="display: none;"></div>
<div id="snapshot-ajax-error" class="error snapshot-error" style="display: none;"></div>

<section id="header">
	<h1><?php esc_html_e( 'Snapshots', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<?php $this->render( "snapshots/partials/restore-snapshot-progress", false, array( 'item' => $item ), false, false ); ?>

<form id="snapshot-edit-restore" action="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>" method="post">
	<input type="hidden" name="snapshot-action" value="restore-request"/>
	<input type="hidden" name="item" value="<?php echo esc_attr( $item['timestamp'] ); ?>"/>

	<input type="hidden" name="snapshot-ajax-nonce" id="snapshot-ajax-nonce" value="<?php echo esc_attr( wp_create_nonce( 'snapshot-ajax-nonce' ) ); ?>" />
	<?php wp_nonce_field( 'snapshot-nonce', 'snapshot-noonce-field' ); ?>

	<div id="container" class="snapshot-three wps-page-wizard wps-page-wizard_restore">

		<section class="wpmud-box new-snapshot-main-box">

			<div class="wpmud-box-title has-button">
				<h3><?php esc_html_e( 'Restore Wizard', SNAPSHOT_I18N_DOMAIN ); ?></h3>
				<a class="button button-small button-outline button-gray"
				   href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>">
					<?php esc_html_e( 'Back', SNAPSHOT_I18N_DOMAIN ); ?>
				</a>
			</div>

			<div class="wpmud-box-content">

				<?php $this->render( "common/requirements-test", false, $requirements_test, false, false ); ?>

				<div class="wpmud-box-tab configuration-box<?php if ( $all_good ) echo ' open'; ?>">

					<div class="wpmud-box-tab-title can-toggle">

						<h3>
							<?php esc_html_e( 'Configuration', SNAPSHOT_I18N_DOMAIN ); ?>
							<?php if ( ! $all_good ) { ?>
								<span class="wps-restore-backup-notice">
						<?php esc_html_e( 'You must meet the server requirements before proceeding.', SNAPSHOT_I18N_DOMAIN ); ?>
					</span>
							<?php } ?>
							<?php if ( $all_good && $warning ) { ?>
								<span class="wps-restore-backup-notice">
						<?php esc_html_e( 'You have 1 or more requirements warnings. You can proceed, however Snapshot may run into issues due to the warnings.', SNAPSHOT_I18N_DOMAIN ); ?>
					</span>
							<?php } ?>
						</h3>
						<?php if ( $all_good ): ?>
							<i class="wps-icon i-arrow-right"></i>
						<?php endif; ?>
					</div>

					<?php if ( $all_good ): ?>

						<div class="wpmud-box-tab-content">

							<div id="wps-restore-subsite" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
									<label class="label-box"><?php esc_html_e( 'Blog options', SNAPSHOT_I18N_DOMAIN ); ?></label>
								</div>

								<?php

								global $blog_id;

								$siteurl = '';
								$domain = '';
								if ( isset( $item['blog-id'] ) ) {
									if ( is_multisite() ) {
										$blog_details = get_blog_details( $item['blog-id'] );
									} else {
										$blog_details = new stdClass();
										$blog_details->blog_id = $blog_id;
										$blog_details->siteurl = get_option( 'siteurl' );
										if ( $blog_details->siteurl ) {
											$blog_details->domain = wp_parse_url( $blog_details->siteurl, PHP_URL_HOST );
											$blog_details->path = wp_parse_url( $blog_details->siteurl, PHP_URL_PATH );
											if ( empty( $blog_details->path ) ) {
												$blog_details->path = '/';
											}
										}
									}
								}

								?>

								<input type="hidden" name="snapshot-blog-id" id="snapshot-blog-id"
								       value="<?php echo esc_attr( isset( $item['blog-id'] ) ? $item['blog-id'] : '' ); ?>">

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<?php if ( is_multisite() ) { ?>
											<div class="wps-notice">
												<p><?php echo wp_kses_post( __( 'You can restore the backup to a different blog within your Multisite environment.<br><strong>Note: The destination blog MUST already exist.</strong>', SNAPSHOT_I18N_DOMAIN ) ); ?></p>
											</div>

											<div class="wps-auth-message warning">
												<p><?php esc_html_e( 'This migration logic is considered still in beta.', SNAPSHOT_I18N_DOMAIN ); ?></p>
											</div>

											<?php if ( ! isset( $item['MANIFEST']['WP_SITEURL'] ) || $blog_details->siteurl !== $item['MANIFEST']['WP_SITEURL'] ) { ?>
												<div class="wps-auth-message error">

													<p><?php esc_html_e( 'Restore Note: URL mismatch! The Snapshot archive does not appear made from the current WordPress system. Every attempt will be made to replace the source URL with the URL from the destination.', SNAPSHOT_I18N_DOMAIN ); ?></p>
												</div>

											<?php
                                            }
										}
                                        ?>

										<div class="wps-restore-row">

											<div class="wps-restore-col">

												<label class="label-title"><?php esc_html_e( 'Information from archive', SNAPSHOT_I18N_DOMAIN ); ?></label>
												<?php

												global $wpdb;

												$sections = array(
													__( 'Blog ID:', SNAPSHOT_I18N_DOMAIN ) => 'WP_BLOG_ID',
													__( 'Site URL:', SNAPSHOT_I18N_DOMAIN ) => 'WP_SITEURL',
													__( 'Database Name:', SNAPSHOT_I18N_DOMAIN ) => 'WP_DB_NAME',
													__( 'Database Base Prefix:', SNAPSHOT_I18N_DOMAIN ) => 'WP_DB_BASE_PREFIX',
													__( 'Database Prefix:', SNAPSHOT_I18N_DOMAIN ) => 'WP_DB_PREFIX',
													__( 'Upload Path:', SNAPSHOT_I18N_DOMAIN ) => 'WP_UPLOAD_PATH',
												);

												if ( ! is_multisite() ) {
													unset( $sections[ __( 'Blog ID:', SNAPSHOT_I18N_DOMAIN ) ] );
												}

												?>

												<table cellspacing="0" cellpadding="0">
													<tbody>
													<?php foreach ( $sections as $label => $key ) { ?>

														<tr>
															<th><?php echo esc_html( $label ); ?></th>
															<td class="snapshot-org-<?php echo esc_attr( str_replace( 'database', 'db', sanitize_title_with_dashes( $label ) ) ); ?>">
	                                                            <?php

																	echo esc_html(
	                                                                     ! $item['blog-id'] && isset( $item[ $key ] ) ?
																		$item['IMPORT'][ $key ] : $item['MANIFEST'][ $key ]
	                                                                    );

																?>
                                                            </td>
														</tr>

													<?php } ?>
													</tbody>
												</table>

											</div>

											<div class="wps-restore-col">

												<label class="label-title"><?php esc_html_e( 'Will be restored to', SNAPSHOT_I18N_DOMAIN ); ?></label>

												<table cellspacing="0" cellpadding="0">

													<tbody>

													<?php if ( is_multisite() ) { ?>
														<tr>
															<th><?php esc_html_e( 'Blog ID:', SNAPSHOT_I18N_DOMAIN ); ?></th>
															<td id="snapshot-new-blog-id">
                                                            	<?php
																echo esc_html(
                                                                    $item['blog-id'] && ! isset( $item['IMPORT'] ) ?
																	$item['MANIFEST']['WP_BLOG_ID'] : ''
                                                                );
																?>
                                                            </td>
														</tr>

													<?php } ?>

													<tr>
														<th><?php esc_html_e( 'Site URL:', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<td>
													<span id="snapshot-blog-search-success">
														<span id="snapshot-blog-name">
                                                        <?php

															if ( is_multisite() ) {
																$item_siteurl = $item['blog-id'] && ! isset( $item['IMPORT'] ) ?
																	$blog_details->siteurl : '';
															} else {
																$item_siteurl = get_option( 'siteurl' );
															}

															echo esc_html( $item_siteurl );

															?>
                                                        </span>

														<?php if ( is_multisite() ) { ?>
															<button id="snapshot-blog-id-change" style="margin-left: 10px;"
															        class="button button-small button-gray button-outline">
	                                                                <?php
																	esc_html_e( 'Change', SNAPSHOT_I18N_DOMAIN );
																	?>
                                                            </button>
														<?php } ?>

													</span>

															<?php if ( is_multisite() ) { ?>
																<span id="snapshot-blog-search" style="display: none;">
														<span id="snapshot-blog-search-error" style="color: #FF0000; display:none;">
															<?php esc_html_e( 'Error on blog lookup. Try again', SNAPSHOT_I18N_DOMAIN ); ?>
															<br>
														</span>

														<span class="wps-spinner" style="display: none;"></span>

																	<?php

																	if ( is_subdomain_install() ) {
																		$site_domain = untrailingslashit( preg_replace( '/^(http|https):\/\//', '', network_site_url() ) );
																		$current_sub_domain = str_replace( '.' . network_site_url(), '', wp_parse_url( $item_siteurl, PHP_URL_HOST ) );
																		$site_part = str_replace( '.' . $site_domain, '', $current_sub_domain );

																	} else {
																		$current_scheme = wp_parse_url( network_site_url(), PHP_URL_SCHEME );
																		$current_scheme .= $current_scheme ? '://' : '';

																		$current_domain = apply_filters( 'snapshot_current_domain', DOMAIN_CURRENT_SITE );
																		$current_path = apply_filters( 'snapshot_current_path', PATH_CURRENT_SITE );
																		echo esc_html( $current_scheme . $current_domain . $current_path );

																		$site_part = str_replace( untrailingslashit( network_site_url() ), '', untrailingslashit( $item_siteurl ) );
																		$site_part = ltrim( $site_part, '/\\' );

																	}
                                                                    ?>

																	<input type="text" style="width: 50%; display: inline-block;" name="snapshot-blog-id-search" id="snapshot-blog-id-search"
																	       value="<?php echo esc_attr( $site_part ); ?>">

																	<?php
                                                                    if ( is_subdomain_install() ) {
																		printf( '.%s', esc_html( $site_domain ) );
																	}
                                                                    ?>

																	<p class="description"><small style="white-space: normal;">
                                                                    <?php

																			if ( is_subdomain_install() ) {
																				esc_html_e( 'Enter the blog sub-domain prefix (i.e. site 1) or blog ID (i.e. 22), or a mapped domain, or leave blank for the primary site.', SNAPSHOT_I18N_DOMAIN );
																			} else {
																				esc_html_e( 'Enter the blog path (i.e. site1) or blog ID (i.e. 22), or leave blank for the primary site', SNAPSHOT_I18N_DOMAIN );
																			}
																			?>
                                                                            </small></p>

														<p>
															<button id="snapshot-blog-id-lookup" class="button button-small button-blue">
																<?php esc_html_e( 'Lookup', SNAPSHOT_I18N_DOMAIN ); ?>
															</button>
															<button id="snapshot-blog-id-cancel" class="button button-small button-gray">
																<?php esc_html_e( 'Cancel', SNAPSHOT_I18N_DOMAIN ); ?>
															</button>
															<input type="hidden" name="snapshot-ajax-nonce" id="snapshot-ajax-nonce" value="<?php echo esc_attr( wp_create_nonce( 'snapshot-ajax-nonce' ) ); ?>" />
														</p>
													</span>
															<?php } ?>
														</td>
													</tr>

													<tr>
														<th><?php esc_html_e( 'Database Name:', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<td id="snapshot-new-db-name">
                                                        <?php
															echo is_multisite() && ! $item['blog-id'] && isset( $item['IMPORT'] ) ?
																'' : esc_html( DB_NAME );
															?>
                                                            </td>
													</tr>

													<tr>
														<th><?php esc_html_e( 'Database Base Prefix:', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<td id="snapshot-new-db-base-prefix">
                                                        <?php
															echo is_multisite() && ! $item['blog-id'] && isset( $item['IMPORT'] ) ?
																'' : esc_html( $wpdb->base_prefix );
															?>
                                                            </td>
													</tr>

													<tr>
														<th><?php esc_html_e( 'Database Prefix:', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<td id="snapshot-new-db-prefix">
                                                        <?php

															if ( is_multisite() ) {
																echo ! $item['blog-id'] && isset( $item['IMPORT'] ) ?
																	'' : esc_html( $wpdb->get_blog_prefix( $item['MANIFEST']['WP_BLOG_ID'] ) );
															} else {
																echo esc_html( $wpdb->prefix );
															}

															?>
                                                            </td>
													</tr>

													<tr>
														<th><?php esc_html_e( 'Upload Path:', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<td id="snapshot-new-upload-path">
                                                        <?php

															if ( is_multisite() ) {
																if ( ! $item['blog-id'] && isset( $item['IMPORT'] ) ) {
																	echo '';
																} else {
																	echo esc_html( Snapshot_Helper_Utility::get_blog_upload_path( $item['blog-id'] ) );
																}
															} else {
																echo esc_html( Snapshot_Helper_Utility::get_blog_upload_path( $blog_id ) );
															}

															?>
                                                            </td>
													</tr>

													</tbody>

												</table>

											</div>

										</div>

									</div>

								</div>

							</div>

							<div id="wps-restore-archive" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php esc_html_e( 'Archive', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title"><?php esc_html_e( 'Select the archive you wish to restore from.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<?php
										if ( ( isset( $item['data'] ) ) && ( count( $item['data'] ) ) ) :
											$data_items = $item['data'];
											krsort( $data_items );

											// Do *not* limit the number of items shown for restore!
											// Fixes: https://app.asana.com/0/11140230629075/503490790609676/f .
											//$data_items = array_slice( $data_items, 0, 6, true );

											foreach ( $data_items as $data_key => $data_item ) :
                                            ?>

												<div class="wps-input--item">

													<div class="wps-input--radio">

														<input type="radio" name="snapshot-restore-file" class="snapshot-restore-file"
														       id="snapshot-restore-<?php echo esc_attr( $data_item['timestamp'] ); ?>"
														       value="<?php echo esc_attr( $data_item['timestamp'] ); ?>"
                                                        <?php
														if ( ( isset( $_GET['snapshot-data-item'] ) ) && ( intval( $_GET['snapshot-data-item'] ) === $data_item['timestamp'] ) ) {
															echo ' checked="checked" ';
														}
                                                        ?>
                                                        />

														<label for="snapshot-restore-<?php echo esc_attr( $data_item['timestamp'] ); ?>"></label>

													</div>

													<label for="snapshot-restore-<?php echo esc_attr( $data_item['timestamp'] ); ?>"><?php echo esc_html( Snapshot_Helper_Utility::show_date_time( $data_item['timestamp'], 'F j, Y @ g:i a' ) ); ?></label>

												</div>
											<?php endforeach; ?>
										<?php endif; ?>

									</div>

								</div>

							</div><?php // Archive ?>

							<?php $data_item = $item['data'][ $data_item_key ]; ?>

							<?php if ( ( isset( $data_item['files-sections'] ) ) && ( ! empty( $data_item['files-sections'] ) ) ) : ?>

								<div id="wps-restore-files" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php esc_html_e( 'Files', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">
										<?php
										if ( isset( $data_item['files-sections'] ) ) {
											if ( ( array_search( 'config', $item['data'][ $data_item_key ]['files-sections'], true ) !== false )
											     || ( array_search( 'htaccess', $item['data'][ $data_item_key ]['files-sections'], true ) !== false )
											) {
												?>
                                                <p
														class="snapshot-error"><?php esc_html_e( "Restore Note: The archive you are about to restore includes the .htaccess and/or the wp-config.php files. Normally you do not want to restore these files unless your site is broken. To restore either of these files you must select them from the'include selected files' section below.", SNAPSHOT_I18N_DOMAIN ); ?></p>
												<?php
											}
										}
										?>

										<label class="label-title"><?php esc_html_e( 'Select which files you want to include.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<div class="wps-input--item">

											<div class="wps-input--radio">

												<input type="radio" class="snapshot-files-option" id="snapshot-files-option-none" value="none" name="snapshot-files-option"/>

												<label for="snapshot-files-option-none"></label>

											</div>

											<label for="snapshot-files-option-none"><?php esc_html_e( 'Don\'t include any files', SNAPSHOT_I18N_DOMAIN ); ?></label>

										</div>

										<div class="wps-input--item">

											<div class="wps-input--radio">

												<input type="radio" class="snapshot-files-option" id="snapshot-files-option-all" value="all" checked="checked" name="snapshot-files-option">
												<label for="snapshot-files-option-all"></label>

											</div>

											<label for="snapshot-files-option-all"><?php esc_html_e( 'Restore all files', SNAPSHOT_I18N_DOMAIN ); ?></label>
											<?php
											if ( ( array_search( 'config', $item['data'][ $data_item_key ]['files-sections'], true ) !== false )
											     || ( array_search( 'htaccess', $item['data'][ $data_item_key ]['files-sections'], true ) !== false )
											) {
												?>
                                                <span>
													<strong><?php esc_html_e( '(excluding .htaccess & wp-config.php files)', SNAPSHOT_I18N_DOMAIN ); ?></strong>
												</span>
                                                <?php
											}
											?>

										</div>

										<div class="wps-input--item">

											<div class="wps-input--radio">

												<input type="radio" class="snapshot-files-option" id="snapshot-files-option-selected" value="selected" name="snapshot-files-option">

												<label for="snapshot-files-option-selected"></label>

											</div>

											<label for="snapshot-files-option-selected"><?php esc_html_e( 'Only include selected files', SNAPSHOT_I18N_DOMAIN ); ?></label>

										</div>

										<div id="snapshot-selected-files-container"
										     style="margin-left: 30px; padding-top: 10px; display: none;">

											<?php if ( is_multisite() ) { ?>
												<p class="snapshot-error"><?php esc_html_e( "Restore Note: The files wp-config.php and .htaccess can only be restored for the primary site. Even then it is not advisable to restore these file for a working Multisite installation.", SNAPSHOT_I18N_DOMAIN ); ?></p>
											<?php } ?>

											<ul id="snapshot-select-files-option" class="wpmud-box-gray">
												<?php if ( array_search( 'themes', $item['data'][ $data_item_key ]['files-sections'], true ) !== false ) { ?>
													<li id="snapshot-files-option-themes-li" class="wps-input--item">
														<div class="wps-input--checkbox">
															<input type="checkbox" class="snapshot-backup-sub-options" checked="checked" id="snapshot-files-option-themes" value="themes" name="snapshot-files-sections[themes]">
															<label for="snapshot-files-option-themes"></label>
														</div>
														<label for="snapshot-files-option-themes"><?php esc_html_e( 'Themes', SNAPSHOT_I18N_DOMAIN ); ?></label>
													</li>
												<?php } ?>
												<?php if ( array_search( 'plugins', $item['data'][ $data_item_key ]['files-sections'], true ) !== false ) { ?>
													<li id="snapshot-files-option-plugins-li" class="wps-input--item">
														<div class="wps-input--checkbox">
															<input type="checkbox" class="snapshot-backup-sub-options" checked="checked" id="snapshot-files-option-plugins" value="plugins" name="snapshot-files-sections[plugins]">
															<label for="snapshot-files-option-plugins"></label>
														</div>
														<label for="snapshot-files-option-plugins"><?php esc_html_e( 'Plugins', SNAPSHOT_I18N_DOMAIN ); ?></label>
													</li>
												<?php } ?>
												<?php if ( array_search( 'media', $item['data'][ $data_item_key ]['files-sections'], true ) !== false ) { ?>
													<li id="snapshot-files-option-media-li" class="wps-input--item">
														<div class="wps-input--checkbox">
															<input type="checkbox" class="snapshot-backup-sub-options" checked="checked" id="snapshot-files-option-media" value="media" name="snapshot-files-sections[media]">
															<label for="snapshot-files-option-media"></label>
														</div>
														<label for="snapshot-files-option-media"><?php esc_html_e( 'Media Files', SNAPSHOT_I18N_DOMAIN ); ?></label>
													</li>
												<?php } ?>
												<?php if ( array_search( 'config', $item['data'][ $data_item_key ]['files-sections'], true ) !== false ) { ?>
													<li id="snapshot-files-option-config-li">
														<div class="wps-input--checkbox">
															<input type="checkbox" class="snapshot-backup-sub-options" id="snapshot-files-option-config" value="config" name="snapshot-files-sections[config]">
															<label for="snapshot-files-option-config"></label>
														</div>
														<label for="snapshot-files-option-config"><?php esc_html_e( 'wp-config.php', SNAPSHOT_I18N_DOMAIN ); ?></label>
													</li>
												<?php } ?>
												<?php if ( array_search( 'htaccess', $item['data'][ $data_item_key ]['files-sections'], true ) !== false ) { ?>
													<li id="snapshot-files-option-htaccess-li">
														<div class="wps-input--checkbox">
															<input type="checkbox" class="snapshot-backup-sub-options" id="snapshot-files-option-htaccess" value="htaccess" name="snapshot-files-sections[htaccess]">
															<label for="snapshot-files-option-htaccess"></label>
														</div>
														<label for="snapshot-files-option-htaccess"><?php esc_html_e( '.htaccess', SNAPSHOT_I18N_DOMAIN ); ?></label>
													</li>
												<?php } ?>
											</ul>
										</div>

									</div>

								</div>

								</div><?php // Files ?>

							<?php endif; ?>

							<?php

							if ( is_multisite() ) {
								if ( ( isset( $item['data'][ $data_item_key ]['tables-sections'] ) ) && ( count( $item['data'][ $data_item_key ]['tables-sections'] ) ) ) {
									foreach ( $item['data'][ $data_item_key ]['tables-sections'] as $tables_section => $tables_sections_data ) {
										foreach ( $tables_sections_data as $table_name_idx => $table_name ) {
											$table_name_part = str_replace( $item['MANIFEST']['WP_DB_PREFIX'], '', $table_name );
											//echo "table_name_part=[". $table_name_part ."] [". $table_name ."]<br />";

											if ( array_search( $table_name_part, $wpdb->global_tables, true ) !== false ) {
												if ( ! isset( $item['data'][ $data_item_key ]['tables-sections']['global'] ) ) {
													$item['data'][ $data_item_key ]['tables-sections']['global'] = array();
												}
												$item['data'][ $data_item_key ]['tables-sections']['global'][ $table_name ] = $table_name;

												unset( $item['data'][ $data_item_key ]['tables-sections'][ $tables_section ][ $table_name ] );

											}
										}
									}
								}
							}

							if ( ( isset( $data_item['tables-sections'] ) ) && ( ! empty( $data_item['tables-sections'] ) ) ) :
                            ?>

								<div id="wps-restore-database" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php esc_html_e( 'Database', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<?php if ( is_multisite() && ( isset( $item['data'][ $data_item_key ]['tables-sections']['global'] ) ) && ( count( $item['data'][ $data_item_key ]['tables-sections']['global'] ) ) ) : ?>

											<p class="snapshot-error"><?php esc_html_e( "Restore Note: The archive you are about to restore includes the global database tables users and/or usermeta. Normally, you do not want to restore these tables unless your site is broken. To restore either of these database tables you must select them from the 'Restore selected database tables' section below. The data contained within these tables will be merged with the current global tables", SNAPSHOT_I18N_DOMAIN ); ?></p>

										<?php endif; ?>

										<?php if ( ( ! is_multisite() ) && ( $item['MANIFEST']['WP_DB_PREFIX'] !== $wpdb->prefix ) ) : ?>

											<p class="snapshot-error"><?php echo wp_kses_post( sprintf( __( "Restore Note: The archive contains tables names with a different database prefix ( %1\$s ) than this site ( %2\$s ). The tables restored will automatically be renamed to the site prefix", SNAPSHOT_I18N_DOMAIN ), $item['MANIFEST']['WP_DB_PREFIX'], $wpdb->prefix ) ); ?></p>

										<?php endif; ?>

										<?php if ( ( ! is_multisite() ) && ( ! empty( $item['MANIFEST']['WP_DB_CHARSET_COLLATE'] ) ) && ( $item['MANIFEST']['WP_DB_CHARSET_COLLATE'] !== $wpdb->get_charset_collate() ) ) : ?>

											<p class="snapshot-error"><?php echo wp_kses_post( sprintf( __( "Restore Note: The archive you are about to restore has a different charset collation ( %1\$s ) than this site ( %2\$s ).", SNAPSHOT_I18N_DOMAIN ), $item['MANIFEST']['WP_DB_CHARSET_COLLATE'], $wpdb->get_charset_collate() ) ); ?></p>

										<?php endif; ?>

										<label class="label-title"><?php esc_html_e( 'Select which database tables you want to include.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<div class="wps-input--group">

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-tables-option" id="snapshot-tables-option-all" checked="checked" value="all" name="snapshot-tables-option">
													<label for="snapshot-tables-option-all"></label>

												</div>

												<label for="snapshot-tables-option-all">
													<?php
													echo wp_kses_post(
														( is_multisite() ) ? __( 'Restore <strong>all</strong> blog database tables contained in this archive <strong>(excluding global tables users & usermeta)</strong>', SNAPSHOT_I18N_DOMAIN ) : __( 'Restore <strong>all</strong> blog database tables contained in this archive ', SNAPSHOT_I18N_DOMAIN )
													);
													?>
												</label>

											</div>

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-tables-option" id="snapshot-tables-option-none" value="none" name="snapshot-tables-option">
													<label for="snapshot-tables-option-none"></label>

												</div>

												<label for="snapshot-tables-option-none"><?php esc_html_e( 'Don\'t include any database tables', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-tables-option" id="snapshot-tables-option-selected" value="selected" name="snapshot-tables-option">
													<label for="snapshot-tables-option-selected"></label>

												</div>

												<label for="snapshot-tables-option-selected"><?php esc_html_e( 'Only include selected tables', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

										</div>

										<div id="snapshot-selected-tables-container" class="wpmud-box-gray" style="display: none;">

											<?php
                                            $tables_sets_idx = array(
												'global' => __( "WordPress Global Tables", SNAPSHOT_I18N_DOMAIN ),
												'wp' => __( "WordPress Blog Tables", SNAPSHOT_I18N_DOMAIN ),
												'non' => __( "Non-WordPress Tables", SNAPSHOT_I18N_DOMAIN ),
												'other' => __( "Other Tables", SNAPSHOT_I18N_DOMAIN ),
											);

											//echo "item<pre>"; print_r($item); echo "</pre>";

											foreach ( $tables_sets_idx as $table_set_key => $table_set_title ) {

												if ( isset( $item['data'][ $data_item_key ]['tables-sections'][ $table_set_key ] ) ) {
													$display_set = 'block';
												} else {
													$display_set = 'none';
												}
                                                ?>

											<div id="snapshot-tables-<?php echo esc_attr( $table_set_key ); ?>-set" class="snapshot-tables-set" style="display: <?php echo esc_attr( $display_set ); ?>">

												<h3 class="snapshot-tables-title"><?php echo esc_html( $table_set_title ); ?><?php if ( ( isset( $item['data'][ $data_item_key ]['tables-sections'][ $table_set_key ] ) ) && ( count( $item['data'][ $data_item_key ]['tables-sections'][ $table_set_key ] ) ) ) { ?>
														<a class="button-link snapshot-table-select-all" href="#" id="snapshot-table-<?php echo esc_attr( $table_set_key ); ?>-select-all"><?php esc_html_e( 'Select all', SNAPSHOT_I18N_DOMAIN ); ?></a>
													<?php } ?></h3>

												<?php if ( ( is_multisite() ) && ( "global" === $table_set_key ) ) { ?>

													<p class="snapshot-error"><?php esc_html_e( 'When restoring users and usermeta records under a Multisite environment there are a few limitations. Please read the following carefully', SNAPSHOT_I18N_DOMAIN ); ?></p>

													<ol class="snapshot-error">
														<li><?php esc_html_e( "If restoring to the primary blog ALL user entries will be replaced!", SNAPSHOT_I18N_DOMAIN ); ?></li>
														<li><?php esc_html_e( "If restoring to a non-primary blog, the user's ID and user_name fields are checked against existing users.", SNAPSHOT_I18N_DOMAIN ); ?>
															<ul>
																<li><?php esc_html_e( "- If a match is not found a new user will be created. This means a new user ID will be assigned.", SNAPSHOT_I18N_DOMAIN ); ?></li>
																<li><?php esc_html_e( "- If a match is found but the user ID is different. The found user ID will be used.", SNAPSHOT_I18N_DOMAIN ); ?></li>
															</ul>
														</li>
														<li><?php esc_html_e( "If the restored user ID is changed, Snapshot will update usermeta, posts and comments records with the new user ID. A new usermeta record will be added with the key '_old_user_id' with the value of the previous user ID. Snapshot cannot attempt updates to other tables like BuddyPress where the user ID fields are not known. These will need to be updated manually." ); ?></li>
													</ol>

												<?php } ?>

												<?php
                                                if ( ( isset( $item['data'][ $data_item_key ]['tables-sections'][ $table_set_key ] ) ) && ( count( $item['data'][ $data_item_key ]['tables-sections'][ $table_set_key ] ) ) ) {

													$tables = $item['data'][ $data_item_key ]['tables-sections'][ $table_set_key ];
                                                    ?>

													<ul class="snapshot-table-list" id="snapshot-table-list-<?php echo esc_attr( $table_set_key ); ?>">

														<?php
                                                        foreach ( $tables as $table_key => $table_name ) {

															if ( "global" !== $table_set_key ) {
																$checked = ' checked="checked" ';
															} else {
																if ( is_multisite() ) {
																	$checked = '';
																} else {
																	$checked = ' checked="checked" ';
																}
															}
                                                            ?>

															<li class="wps-input--item">

																<div class="wps-input--checkbox">
																	<input type="checkbox" <?php echo wp_kses_post( $checked ); ?> class="snapshot-table-item" id="snapshot-tables-<?php echo esc_attr( $table_key ); ?>" value="<?php echo esc_attr( $table_key ); ?>" name="snapshot-tables[<?php echo esc_attr( $table_set_key ); ?>][<?php echo esc_attr( $table_key ); ?>]">
																	<label for="snapshot-tables-<?php echo esc_attr( $table_key ); ?>"></label>
																</div>

																<label for="snapshot-tables-<?php echo esc_attr( $table_key ); ?>"><?php echo esc_html( $table_name ); ?></label>

															</li>

														<?php } ?>

													</ul>

												<?php } else { ?>

													<p><?php esc_html_e( 'No tables', SNAPSHOT_I18N_DOMAIN ); ?></p>

												<?php } ?>

												</div><?php // .snapshot-tables-set ?>

											<?php } ?>

										</div><?php // #snapshot-selected-tables-container ?>

									</div>

								</div>

								</div><?php // Database ?>

							<?php endif; ?>

							<div id="wps-restore-plugins" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php esc_html_e( 'Plugins', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<div class="wps-input--item">

											<div class="wps-input--checkbox">

												<input type="checkbox" id="snapshot-restore-option-plugins"
												       name="restore-option-plugins" value="yes"/>

												<label for="snapshot-restore-option-plugins"></label>

											</div>

											<label for="snapshot-restore-option-plugins"><?php esc_html_e( 'Deactivate plugins', SNAPSHOT_I18N_DOMAIN ); ?></label>

											<p>
												<small><?php esc_html_e( 'This will deactivate all plugins. You\'ll then be able to activate them manually after the restoration is complete.', SNAPSHOT_I18N_DOMAIN ); ?></small>
											</p>

										</div>

									</div>

								</div>

							</div><?php // Plugins ?>

							<div id="wps-restore-themes" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php esc_html_e( 'Themes', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title"><?php esc_html_e( 'Select which theme you want to activate when this site is restored.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<?php
										if ( isset( $item['blog-id'] ) ) {
											$current_theme = Snapshot_Helper_Utility::get_current_theme( $item['blog-id'] );
										} else {
											$current_theme = Snapshot_Helper_Utility::get_current_theme();
										}

										if ( isset( $item['blog-id'] ) ) {
											$themes = Snapshot_Helper_Utility::get_blog_active_themes( $item['blog-id'] );
										} else {
											$themes = Snapshot_Helper_Utility::get_blog_active_themes();
										}

										?>

										<?php
                                        if ( $themes ) :
											foreach ( $themes as $theme_key => $theme_name ) :
												?>
												<div class="wps-input--item">

													<div class="wps-input--radio">

														<input type="radio" id="snapshot-restore-option-theme-<?php echo esc_attr( $theme_key ); ?>" <?php echo ( $theme_key === $current_theme ) ? 'checked="checked"' : ''; ?> name="restore-option-theme" value="<?php echo esc_attr( $theme_key ); ?>"/>

														<label for="snapshot-restore-option-theme-<?php echo esc_attr( $theme_key ); ?>"></label>

													</div>

													<label for="snapshot-restore-option-theme-<?php echo esc_attr( $theme_key ); ?>">
														<?php echo ( $theme_key === $current_theme ) ? '<strong>' : ''; ?>
														<?php echo esc_html( $theme_name ); ?>
														<?php echo ( $theme_key === $current_theme ) ? '</strong>' : ''; ?>
													</label>

												</div>
												<?php
											endforeach;
										endif;
										?>

									</div>

								</div>

							</div><?php // Themes ?>

							<div class="row">

								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

									<div class="form-button-container">

										<a class="button button-gray" href=""><?php esc_html_e( 'Cancel', SNAPSHOT_I18N_DOMAIN ); ?></a>
										<input class="button button-blue" id="snapshot-form-restore-submit" class="button-primary" type="submit" value="<?php esc_html_e( 'Restore Now', SNAPSHOT_I18N_DOMAIN ); ?>">

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