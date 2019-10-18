<?php

/**
 * Onboarding Screen for settings.
 *
 * @var bool       $is_logged_in Is logged in with Google?.
 * @var bool       $network      Network flag.
 * @var array|bool $ps_levels    Pro Sites levels.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

use Beehive\Core\Helpers\Template;

?>

<div class="sui-dialog sui-dialog-onboard sui-fade-out" id="beehive-onboarding-setup" aria-hidden="true">

	<div class="sui-dialog-overlay" data-a11y-dialog-hide="beehive-onboarding-setup"></div>

	<div class="sui-dialog-content sui-content-fade-out" aria-labelledby="beehive-onboarding-setup-title" aria-describedby="beehive-onboarding-setup-description" role="dialog">

		<div class="sui-slider">

			<ul class="sui-slider-content" role="document">

				<li class="sui-current sui-loaded">

					<?php
					/**
					 * Action hook to print form in Google account section.
					 *
					 * @since 3.2.0
					 */
					do_action( 'beehive_onboarding_google_settings' );
					?>

					<p class="sui-onboard-skip">
						<a href="#" data-a11y-dialog-hide="beehive-onboarding-setup" class="beehive-onboarding-skip beehive-onboarding-skip-link"><?php esc_html_e( 'Skip this', 'ga_trans' ); ?></a>
					</p>

				</li>

				<?php if ( ! $is_logged_in ) : ?>
					<li>

						<div class="sui-box">

							<div class="sui-box-banner" role="banner" aria-hidden="true">
								<img src="<?php echo Template::asset_url( 'images/onboarding/tracking.png' ); ?>" srcset="<?php echo Template::asset_url( 'images/onboarding/tracking.png' ); ?> 1x, <?php echo Template::asset_url( 'images/onboarding/tracking@2x.png' ); ?> 2x">
							</div>

							<div class="sui-box-header sui-lg sui-block-content-center">

								<h2 id="beehive-onboarding-setup-title" class="sui-box-title"><?php esc_html_e( 'Add Tracking ID', 'ga_trans' ); ?></h2>

								<span id="beehive-onboarding-setup-description" class="sui-description">
									<?php if ( $network ) : ?>
										<?php printf( __( 'Paste your Google Analytics tracking ID in the field below to enable analytics tracking for the whole network. You can get your ID <a href="%s" target="_blank">here</a>.', 'ga_trans' ), 'https://support.google.com/analytics/answer/2763052?hl=en' ); ?>
									<?php else : ?>
										<?php printf( __( 'Paste your Google Analytics tracking ID in the field below to enable analytics tracking. You can get your ID <a href="%s" target="_blank">here</a>.', 'ga_trans' ), 'https://support.google.com/analytics/answer/2763052?hl=en' ); ?>
									<?php endif; ?>
								</span>

								<button class="sui-dialog-back" aria-label="<?php esc_html_e( 'Go to previous slide', 'ga_trans' ); ?>" data-a11y-dialog-tour-back></button>

								<button data-a11y-dialog-hide="beehive-onboarding-setup" class="sui-dialog-close beehive-onboarding-skip" aria-label="<?php esc_html_e( 'Close this dialog window', 'ga_trans' ); ?>"></button>

							</div>

							<div class="sui-box-body sui-lg sui-block-content-center">

								<div class="sui-form-field">
									<label for="beehive-onboarding-tracking-code" class="sui-label">
										<?php if ( $network ) : ?>
											<?php esc_html_e( 'Network-wide Tracking ID', 'ga_trans' ); ?>
										<?php else : ?>
											<?php esc_html_e( 'Tracking ID', 'ga_trans' ); ?>
										<?php endif; ?>
									</label>
									<input type="text" id="beehive-onboarding-tracking-code" placeholder="<?php esc_html_e( 'E.g: UA-XXXXXXXXX-X', 'ga_trans' ); ?>" class="sui-form-control" value="<?php echo beehive_analytics()->settings->get( 'code', 'tracking', $network ); ?>">
									<span class="sui-error-message"></span>
								</div>

								<button id="beehive-onboarding-save-tracking" class="sui-button sui-button-blue">
									<span class="sui-loading-text"><?php esc_html_e( 'Save Code', 'ga_trans' ); ?></span>
									<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
								</button>

							</div>

						</div>

						<p class="sui-onboard-skip">
							<a data-a11y-dialog-hide="beehive-onboarding-setup" class="beehive-onboarding-skip beehive-onboarding-skip-link"><?php esc_html_e( 'Skip this', 'ga_trans' ); ?></a>
						</p>

					</li>
				<?php endif; ?>

				<?php if ( $network ) : ?>

					<?php if ( $ps_levels ) : ?>

						<li>

							<?php
							// Load Pro Sites content if required.
							$this->view( 'modals/onboarding/pro-sites', [
								'ps_levels' => $ps_levels,
								'network'   => $network,
							] );
							?>

							<p class="sui-onboard-skip">
								<a href="#" data-a11y-dialog-hide="beehive-onboarding-setup" class="beehive-onboarding-skip beehive-onboarding-skip-link"><?php esc_html_e( 'Skip this', 'ga_trans' ); ?></a>
							</p>

						</li>

					<?php endif; ?>

					<li>

						<div class="sui-box">

							<div class="sui-box-banner" role="banner" aria-hidden="true">
								<img src="<?php echo Template::asset_url( 'images/onboarding/setup.png' ); ?>" srcset="<?php echo Template::asset_url( 'images/onboarding/setup.png' ); ?> 1x, <?php echo Template::asset_url( 'images/onboarding/setup@2x.png' ); ?> 2x">
							</div>

							<div class="sui-box-header sui-lg sui-block-content-center">

								<h2 id="beehive-onboarding-setup-title" class="sui-box-title"><?php esc_html_e( 'Admin pages tracking', 'ga_trans' ); ?></h2>

								<span id="beehive-onboarding-setup-description" class="sui-description">
									<?php esc_html_e( 'When enabled, you will get statistics from all admin pages.', 'ga_trans' ); ?>
								</span>

								<button class="sui-dialog-back" aria-label="<?php esc_html_e( 'Go to previous slide', 'ga_trans' ); ?>" data-a11y-dialog-tour-back></button>

								<button data-a11y-dialog-hide="beehive-onboarding-setup" class="sui-dialog-close beehive-onboarding-skip" aria-label="<?php esc_html_e( 'Close this dialog window', 'ga_trans' ); ?>"></button>

							</div>

							<div class="sui-box-body sui-lg sui-block-content-center">

								<div class="beehive-onboarding-toggle">
									<label for="beehive-onboarding-admin-tracking" class="sui-toggle">
										<input type="checkbox" id="beehive-onboarding-admin-tracking" value="1" <?php checked( beehive_analytics()->settings->get( 'track_admin', 'general', $network ), 1 ); ?>>
										<span class="sui-toggle-slider"></span>
									</label>
									<label for="beehive-onboarding-admin-tracking" class="sui-toggle-label"><?php esc_html_e( 'Enable Admin pages tracking', 'ga_trans' ); ?></label>
								</div>

								<button class="sui-button" id="beehive-onboarding-finish" data-a11y-dialog-tour-next><?php esc_html_e( 'Finish', 'ga_trans' ); ?></button>

							</div>

						</div>

						<p class="sui-onboard-skip">
							<a data-a11y-dialog-hide="beehive-onboarding-setup" class="beehive-onboarding-skip beehive-onboarding-skip-link"><?php esc_html_e( 'Skip this', 'ga_trans' ); ?></a>
						</p>

					</li>

				<?php endif; ?>

				<li class="beehive-onboarding-finishing-slide">

					<div class="sui-box">

						<div class="sui-box-header sui-lg sui-block-content-center">

							<span class="beehive-onboarding-finishing-loader">
								<i class="sui-icon-loader sui-loading sui-lg" aria-hidden="true"></i>
							</span>

							<h2 id="beehive-onboarding-setup-title" class="sui-box-title"><?php esc_html_e( 'Finishing Setup...', 'ga_trans' ); ?></h2>

							<div class="sui-notice sui-notice-error sui-hidden" id="beehive-onboarding-finish-notice">
								<p class="onboarding-notice-content"></p>
								<span data-a11y-dialog-hide="beehive-onboarding-setup" class="sui-notice-dismiss beehive-onboarding-skip" aria-label="<?php esc_html_e( 'Close this dialog window', 'ga_trans' ); ?>">
									<a href="#"><?php esc_html_e( 'Skip', 'ga_trans' ); ?></a>
								</span>
							</div>

						</div>

						<div class="sui-box-body sui-lg sui-block-content-center">

							<span id="beehive-onboarding-setup-description" class="sui-description">
								<?php esc_html_e( 'Please wait a few moments while we set up your account. Note that data can take up to 24 hours to display.', 'ga_trans' ); ?>
							</span>

						</div>

						<img class="sui-image sui-image-center" src="<?php echo Template::asset_url( 'images/onboarding/finishing.png' ); ?>" srcset="<?php echo Template::asset_url( 'images/onboarding/finishing.png' ); ?> 1x, <?php echo Template::asset_url( 'images/onboarding/finishing@2x.png' ); ?> 2x">

					</div>

				</li>

			</ul>

		</div>

	</div>

</div>