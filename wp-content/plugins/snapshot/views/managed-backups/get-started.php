
<section id="header">
	<h1><?php esc_html_e( 'Managed Backups', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container" class="snapshot-three wps-page-backups">
	<section class="wpmud-box get-started-box">

		<div class="wpmud-box-title">
			<h3><?php esc_html_e( 'Get Started', SNAPSHOT_I18N_DOMAIN ); ?></h3>
		</div>

		<div class="wpmud-box-content">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

					<div class="wps-image img-snappie-four"></div>

					<p><?php echo wp_kses_post( sprintf( __( '%s, as a WPMU DEV member you get 10GB free cloud storage included in your membership. Install the WPMU DEV Dashboard plugin and then come back to add WPMU DEV as a destination.', SNAPSHOT_I18N_DOMAIN ), wp_get_current_user()->display_name ) ); ?></p>

					<?php

					$is_dashboard_active = $model->is_dashboard_active();
					$is_dashboard_installed = $is_dashboard_active ? true : $model->is_dashboard_installed();
					$has_dashboard_key = $model->has_dashboard_key();

					?>

					<?php if ( empty( $is_dashboard_active ) && empty( $is_dashboard_installed ) ) : ?>

						<p>
							<a href="https://premium.wpmudev.org/project/wpmu-dev-dashboard/" target="_blank" class="button button-blue">
								<?php esc_html_e( 'Install The WPMU DEV Dashboard', SNAPSHOT_I18N_DOMAIN ); ?>
							</a>
						</p>

					<?php elseif ( empty( $is_dashboard_active ) && ! empty( $is_dashboard_installed ) ) : ?>

						<p>
							<a href="<?php echo esc_url( network_admin_url( 'plugins.php' ) ); ?>" class="button button-blue">
								<?php esc_html_e( 'Activate The WPMU DEV Dashboard', SNAPSHOT_I18N_DOMAIN ); ?>
							</a>
						</p>

					<?php elseif ( ! $has_dashboard_key ) : ?>

						<p>
							<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=wpmudev' ) ); ?>" class="button button-blue">
								<?php esc_html_e( 'Login to the WPMU DEV Dashboard plugin', SNAPSHOT_I18N_DOMAIN ); ?>
							</a>
						</p>

					<?php endif; ?>

				</div>

			</div>
		</div>

	</section>
</div>