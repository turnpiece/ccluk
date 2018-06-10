<main id="wpmudev-hustle" class="wpmudev-ui wpmudev-hustle-sshare-wizard-view">

	<header id="wpmudev-hustle-title">

		<h1><?php $is_edit ? _e('Edit Social Share', Opt_In::TEXT_DOMAIN) : _e('New Social Share', Opt_In::TEXT_DOMAIN); ?></h1>

	</header>

	<section id="wpmudev-hustle-content">

		<div class="wpmudev-tabs-page">

			<aside class="wpmudev-menu">

				<ul>

                    <?php $id_link = ( $is_edit ) ? '&id=' . $module_id : '';?>

					<li class="wpmudev-menu-services-link<?php if ($section === 'services') { echo ' current'; } ?>"><a href="<?php echo admin_url( 'admin.php?page=hustle_sshare' . $id_link );?>" data-link="<?php echo admin_url( 'admin.php?page=hustle_sshare' . $id_link );?>"><?php _e( "Name & Services", Opt_In::TEXT_DOMAIN ); ?></a></li>
					<li class="wpmudev-menu-design-link<?php if ($section === 'design') { echo ' current'; } ?>"><a href="<?php echo ( $is_edit ) ? admin_url( 'admin.php?page=hustle_sshare'. $id_link .'&section=design' ) : '#';?>" data-link="<?php echo admin_url( 'admin.php?page=hustle_sshare'. $id_link .'&section=design' );?>"><?php _e( "Design", Opt_In::TEXT_DOMAIN ); ?></a></li>
					<li class="wpmudev-menu-settings-link<?php if ($section === 'settings') { echo ' current'; } ?>"><a href="<?php echo ( $is_edit ) ?  admin_url( 'admin.php?page=hustle_sshare'. $id_link .'&section=settings' ) : '#';?>" data-link="<?php echo admin_url( 'admin.php?page=hustle_sshare'. $id_link .'&section=settings' );?>" ><?php _e( "Display Settings", Opt_In::TEXT_DOMAIN ); ?></a></li>

				</ul>

				<select class="wpmudev-select">

					<option value="services" <?php if ($section === 'services') {echo 'selected';}?>><?php _e( "Name & Services", Opt_In::TEXT_DOMAIN ); ?></option>
					<option value="design" <?php if ($section === 'design') {echo 'selected';}?>><?php _e( "Design", Opt_In::TEXT_DOMAIN ); ?></option>
					<option value="settings" <?php if ($section === 'settings') {echo 'selected';}?>><?php _e( "Display Settings", Opt_In::TEXT_DOMAIN ); ?></option>

				</select>

			</aside>

			<section class="wpmudev-content">

				<div class="wpmudev-box">

					<div class="wpmudev-box-head">

						<?php if ($section === 'services') { ?>

							<h3><?php _e( "Name & Services", Opt_In::TEXT_DOMAIN ); ?></h3>

						<?php } ?>

						<?php if ($section === 'design') { ?>

							<h3><?php _e( "Design", Opt_In::TEXT_DOMAIN ); ?></h3>

						<?php } ?>

						<?php if ($section === 'settings') { ?>

							<h3><?php _e( "Display Settings", Opt_In::TEXT_DOMAIN ); ?></h3>

						<?php } ?>

					</div>

					<div class="wpmudev-box-body">

						<?php if ($section === 'services') { ?>

							<?php $this->render( "admin/sshare/wizard/wizard-services", array(
                                'module' => $module
                            ) ); ?>

						<?php } ?>

						<?php if ($section === 'design') { ?>

							<?php $this->render( "admin/sshare/wizard/wizard-design", array(
                                'module' => $module
                            ) ); ?>

						<?php } ?>

						<?php if ($section === 'settings') { ?>

							<?php $this->render( "admin/sshare/wizard/wizard-settings", array(
                                'module' => $module
                            ) ); ?>

						<?php } ?>

						<div class="wpmudev-box-footer">

							<div class="wpmudev-box-fwrap">

								<?php if ($section === 'services') { ?>

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

								<?php if ($section === 'services' || $section === 'design') { ?>

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

    <?php $this->render("admin/settings/conditions"); ?>

</main>