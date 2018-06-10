<?php
/**
 *
 * @var bool $is_edit if it's in edit mode
 */
?>

<main id="wpmudev-hustle" class="wpmudev-ui wpmudev-hustle-slidein-wizard-view">

	<header id="wpmudev-hustle-title">

		<h1><?php $is_edit ? _e('Edit Slide-in', Opt_In::TEXT_DOMAIN) : _e('New Slide-in', Opt_In::TEXT_DOMAIN); ?></h1>

	</header>

	<section id="wpmudev-hustle-content">

		<div class="wpmudev-tabs-page">

			<aside class="wpmudev-menu">

				<ul>

                    <?php $id_link = ( $is_edit ) ? '&id=' . $module_id : '';?>

					<li class="wpmudev-menu-content-link<?php if ($section === 'content') { echo ' current'; } ?>"><a href="<?php echo admin_url( 'admin.php?page=hustle_slidein' . $id_link );?>" data-link="<?php echo admin_url( 'admin.php?page=hustle_slidein' . $id_link );?>"><?php _e( "Content", Opt_In::TEXT_DOMAIN ); ?></a></li>
					<li class="wpmudev-menu-design-link<?php if ($section === 'design') { echo ' current'; } ?>"><a href="<?php echo ( $is_edit ) ? admin_url( 'admin.php?page=hustle_slidein'. $id_link .'&section=design' ) : '#';?>" data-link="<?php echo admin_url( 'admin.php?page=hustle_slidein'. $id_link .'&section=design' );?>"><?php _e( "Design", Opt_In::TEXT_DOMAIN ); ?></a></li>
					<li class="wpmudev-menu-settings-link<?php if ($section === 'settings') { echo ' current'; } ?>"><a href="<?php echo ( $is_edit ) ? admin_url( 'admin.php?page=hustle_slidein'. $id_link .'&section=settings' ) : '#';?>" data-link="<?php echo admin_url( 'admin.php?page=hustle_slidein'. $id_link .'&section=settings' );?>"><?php _e( "Display Settings", Opt_In::TEXT_DOMAIN ); ?></a></li>

				</ul>

				<select class="wpmudev-select">

					<option value="content" <?php if ($section === 'content') {echo 'selected';}?>><?php _e( "Content", Opt_In::TEXT_DOMAIN ); ?></option>
					<option value="design" <?php if ($section === 'design') {echo 'selected';}?>><?php _e( "Design", Opt_In::TEXT_DOMAIN ); ?></option>
					<option value="settings" <?php if ($section === 'settings') {echo 'selected';}?>><?php _e( "Display Settings", Opt_In::TEXT_DOMAIN ); ?></option>

				</select>

				<div class="wpmudev-preview-anchor" aria-hidden="true"></div>

				<div class="wpmudev-preview" aria-hidden="true" data-nonce="<?php echo $shortcode_render_nonce;?>">

					<?php $this->render( "general/icons/icon-preview", array() ); ?>

					<span><?php _e( "Preview Slide-in", Opt_In::TEXT_DOMAIN ); ?></span>

				</div>

			</aside>

			<section class="wpmudev-content">

				<div class="wpmudev-box">

					<div class="wpmudev-box-head">

						<?php if ($section === 'content') { ?>

							<h3><?php _e( "Content", Opt_In::TEXT_DOMAIN ); ?></h3>

						<?php } ?>

						<?php if ($section === 'design') { ?>

							<h3><?php _e( "Design", Opt_In::TEXT_DOMAIN ); ?></h3>

						<?php } ?>

						<?php if ($section === 'settings') { ?>

							<h3><?php _e( "Display Settings", Opt_In::TEXT_DOMAIN ); ?></h3>

						<?php } ?>

					</div>

					<div class="wpmudev-box-body">

						<?php if ($section === 'content') { ?>

							<?php $this->render( "admin/slidein/wizard/wizard-content", array(
                                'is_edit' => $is_edit,
                                'module' => $module,
                                'providers' => $providers,
								'default_form_fields' => $default_form_fields
                            ) ); ?>

                        <?php } ?>

						<?php if ($section === 'design') { ?>

                            <?php $this->render( "admin/slidein/wizard/wizard-design", array( 'content_data' => ( !is_null($module) && $module ) ? $module->get_content() : array() ) ); ?>

						<?php } ?>

						<?php if ($section === 'settings') { ?>

							<?php $this->render( "admin/slidein/wizard/wizard-settings", array() ); ?>

						<?php } ?>

						<div class="wpmudev-box-footer">

							<div class="wpmudev-box-fwrap">

								<?php if ($section === 'content') { ?>

									<a class="wpmudev-button wpmudev-button-cancel"><?php _e( "Cancel", Opt_In::TEXT_DOMAIN ); ?></a>

								<?php } ?>

								<?php if ($section === 'design' || $section === 'settings') { ?>

									<a class="wpmudev-button wpmudev-button-back">
										<span class="wpmudev-loading-text"><?php _e( "Back", Opt_In::TEXT_DOMAIN ); ?></span>
										<span class="wpmudev-loading"></span>
									</a>

								<?php } ?>

							</div>

							<div class="wpmudev-box-fwrap">

								<?php if ($section === 'content' || $section === 'design') { ?>

									<a class="wpmudev-button wpmudev-button-save" data-nonce="<?php echo $save_nonce;?>" data-id="<?php echo $module_id;?>">
										<span class="wpmudev-loading-text"><?php _e( "Save", Opt_In::TEXT_DOMAIN ); ?></span>
										<span class="wpmudev-loading"></span>
									</a>


									<a class="wpmudev-button wpmudev-button-blue wpmudev-button-continue" data-nonce="<?php echo $save_nonce;?>" data-id="<?php echo $module_id;?>">
										<span class="wpmudev-loading-text"><?php _e( "Continue", Opt_In::TEXT_DOMAIN ); ?></span>
										<span class="wpmudev-loading"></span>
									</a>

								<?php } ?>

								<?php if ($section === 'settings') { ?>

									<a class="wpmudev-button wpmudev-button-save" data-nonce="<?php echo $save_nonce;?>" data-id="<?php echo $module_id;?>">
										<span class="wpmudev-loading-text"><?php _e( "Save", Opt_In::TEXT_DOMAIN ); ?></span>
										<span class="wpmudev-loading"></span>
									</a>


									<a class="wpmudev-button wpmudev-button-blue wpmudev-button-finish" data-nonce="<?php echo $save_nonce;?>" data-id="<?php echo $module_id;?>"><?php _e( "Finish", Opt_In::TEXT_DOMAIN ); ?></a>

								<?php } ?>

							</div>

						</div>

					</div>

				</div>

			</section>

		</div>

	</section>

	<?php $this->render( "admin/commons/footer", array() ); ?>

	<?php $this->render( "admin/commons/wizard/add-new-service", array(
        'module' => $module,
        'providers' => $providers
    ) ); ?>

	<?php $this->render( "admin/commons/wizard/manage-form-fields", array() ); ?>

	<?php $this->render( "admin/commons/wizard/preview-modal", array() ); ?>

    <?php $this->render("admin/settings/conditions"); ?>

</main>