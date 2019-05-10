<?php
// Render the page header section.
/** @var WPMUDEV_Dashboard_Sui $this */
$page_title = __( 'Overview', 'wpmudev' );
$this->render_sui_header( $page_title );

/** @var WPMUDEV_Dashboard_Sui_Page_Urls $urls */

// Find the 5 most popular plugins, that are not installed yet.
$popular = array();
$count   = 0;
foreach ( $data['projects'] as $item ) {
	// Skip themes.
	if ( 'plugin' != $item['type'] ) {
		continue;
	}

	$plugin = WPMUDEV_Dashboard::$site->get_project_infos( $item['id'] );

	// Skip plugin if it's already installed.
	if ( $plugin->is_installed ) {
		continue;
	}

	// Skip plugins that are not compatible with current site.
	if ( ! $plugin->is_compatible ) {
		continue;
	}

	// Skip hidden/deprecated projects.
	if ( $plugin->is_hidden ) {
		continue;
	}

	$popular[] = $item;
	$count ++;

	if ( $count >= 5 ) {
		break;
	}
}

?>

<div class="sui-row">

	<?php // BOX: Tools ?>
	<div class="sui-col-md-6">

		<div class="sui-box">

			<div class="sui-box-header">
				<h3 class="sui-box-title"><?php esc_html_e( 'Tools', 'wpmudev' ); ?></h3>
				<div class="sui-actions-right">
					<a class="sui-button sui-button-ghost" href="<?php echo esc_url( $urls->hub_url ); ?>" target="_blank">
						<i class="sui-icon-hub" aria-hidden="true"></i>
						<?php esc_html_e( 'THE HUB', 'wpmudev' ); ?>
					</a>
				</div>
			</div>

			<div class="sui-box-body">
				<p><?php esc_html_e( 'We don’t just build plugins… take advantage of our great services included with your membership.', 'wpmudev' ); ?></p>
			</div>

			<table class="sui-table sui-table-flushed dashui-table-tools">

				<tbody>

					<tr>
						<td class="dashui-item-content">
							<h4><?php esc_html_e( 'The Hub', 'wpmudev' ); ?></h4>
							<span class="sui-description"><?php esc_html_e( 'Manage all your websites updates &amp; more in one place.', 'wpmudev' ); ?></span>
						</td>
						<td>
							<a class="sui-button-icon sui-tooltip sui-tooltip-top-right-mobile" href="<?php echo esc_url( $urls->hub_url ); ?>" target="_blank" data-tooltip="<?php esc_html_e( 'Go to The Hub', 'wpmudev' ); ?>">
								<i class="sui-icon-arrow-right" aria-hidden="true"></i>
							</a>
						</td>
					</tr>

					<tr>
						<td class="dashui-item-content">
							<h4><?php esc_html_e( 'Support', 'wpmudev' ); ?></h4>
							<span class="sui-description"><?php esc_html_e( 'Get 24/7 expert WordPress support for any issue.', 'wpmudev' ); ?></span>
						</td>
						<td>
							<a class="sui-button-icon sui-tooltip sui-tooltip-top-right-mobile" href="<?php echo esc_url( $urls->external_support_url ); ?>" target="_blank"
							   data-tooltip="<?php esc_html_e( 'Get Support', 'wpmudev' ); ?>">
								<i class="sui-icon-arrow-right" aria-hidden="true"></i>
							</a>
						</td>
					</tr>

					<tr>
						<td class="dashui-item-content">
							<h4><?php esc_html_e( 'Community', 'wpmudev' ); ?></h4>
							<span class="sui-description"><?php esc_html_e( 'Discuss your favorite topics with other developers.', 'wpmudev' ); ?></span>
						</td>
						<td>
							<a class="sui-button-icon sui-tooltip sui-tooltip-top-right-mobile" href="<?php echo esc_url( $urls->community_url ); ?>" target="_blank"
							   data-tooltip="<?php esc_html_e( 'View Forums', 'wpmudev' ); ?>">
								<i class="sui-icon-arrow-right" aria-hidden="true"></i>
							</a>
						</td>
					</tr>

					<tr>
						<td class="dashui-item-content">
							<h4><?php esc_html_e( 'Learn', 'wpmudev' ); ?></h4>
							<span class="sui-description"><?php esc_html_e( 'Become an expert by taking an Academy course.', 'wpmudev' ); ?></span>
						</td>
						<td>
							<a class="sui-button-icon sui-tooltip sui-tooltip-top-right-mobile" href="<?php echo esc_url( $urls->academy_url ); ?>" target="_blank"
							   data-tooltip="<?php esc_html_e( 'Go to The Academy', 'wpmudev' ); ?>">
								<i class="sui-icon-arrow-right" aria-hidden="true"></i>
							</a>
						</td>
					</tr>

				</tbody>

			</table>

		</div>

	</div>

	<?php // BOX: Plugins ?>
	<div class="sui-col-md-6">

		<?php
		// Single membership
		if ( $my_project ) : ?>

			<div class="sui-box">

				<div class="sui-box-header">
					<h3 class="sui-box-title"><?php esc_html_e( 'Purchased', 'wpmudev' ); ?></h3>
					<div class="sui-actions-right">
						<a class="sui-button sui-button-ghost" href="<?php echo esc_url( $urls->hub_account_url ); ?>" target="_blank">
							<i class="sui-icon-unlock" aria-hidden="true"></i>
							<?php esc_html_e( 'UPGRADE MEMBERSHIP', 'wpmudev' ); ?>
						</a>
					</div>
				</div>

				<div class="sui-box-body">

					<?php
					$url = $urls->plugins_url;
					$url .= '#pid=' . $my_project->pid;
					?>

					<table class="sui-table sui-table-flushed">
						<tbody>
							<tr>
								<td style="width: 90%">
									<h4><?php echo esc_html( $my_project->name ); ?></h4>
									<p class="sui-description"><?php echo esc_html( $my_project->info ); ?></p>
								</td>
								<td>
									<a class="sui-button-icon sui-tooltip" href="<?php echo esc_url( $url ); ?>"
									data-tooltip="<?php esc_html_e( 'View plugin info', 'wpmudev' ); ?>">
										<i class="sui-icon-arrow-right" aria-hidden="true"></i>
									</a>
								</td>
							</tr>
						</tbody>
					</table>

				</div>

			</div>

		<?php endif; ?>

		<div class="sui-box">

			<div class="sui-box-header">
				<h3 class="sui-box-title"><?php esc_html_e( 'Plugins', 'wpmudev' ); ?></h3>
				<div class="sui-actions-right">
					<a class="sui-button sui-button-ghost" href="<?php echo esc_url( $urls->plugins_url ); ?>">
						<i class="sui-icon-plugin-2" aria-hidden="true"></i>
						<?php esc_html_e( 'VIEW ALL', 'wpmudev' ); ?>
					</a>
				</div>
			</div>

			<div class="sui-box-body">
				<p><?php esc_html_e( 'Your WPMU DEV membership gives you access to 100+ premium plugins. Here’s our most popular!', 'wpmudev' ); ?></p>
			</div>

			<table class="sui-table sui-table-flushed dashui-table-tools">

				<tbody>

					<?php
					foreach ( $popular as $item ) :

						$url = $urls->plugins_url;
						$url .= '#pid=' . $item['id']; ?>

						<tr>
							<td class="dashui-item-image">
								<img src="<?php echo esc_url( $item['thumbnail_square'] ); ?>" class="sui-image plugin-image" />
							</td>

							<td class="dashui-item-content">
								<h4><?php echo esc_html( $item['name'] ); ?></h4>
								<span class="sui-description"><?php echo esc_html( $item['short_description'] ); ?></span>
							</td>

							<td><a href="<?php echo esc_url( $url ); ?>"
								class="sui-button-icon sui-tooltip sui-tooltip-top-left-mobile"
								data-tooltip="<?php esc_html_e( 'View plugin info', 'wpmudev' ); ?>"
							>
								<i class="sui-icon-arrow-right" aria-hidden="true"></i>
							</a></td>

						</tr>

					<?php endforeach; ?>

				</tbody>

			</table>

		</div>


	</div>
</div>

<?php $this->load_sui_template( 'footer', array(), true ); ?>
<?php if ( 'free' === $type ) : ?>
	<?php $this->render_upgrade_box( 'free' ); ?>
<?php endif; ?>
