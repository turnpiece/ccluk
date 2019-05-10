<?php
// Render the page header section.
$page_title = __( 'Plugins', 'wpmudev' );

/** @var $this WPMUDEV_Dashboard_Sui */
$this->render_sui_header( $page_title );
?>

<div class="sui-box">

	<div class="sui-box-header">

		<h2 class="sui-box-title"><?php esc_html_e( 'All Plugins', 'wpmudev' ); ?></h2>

	</div>

	<div class="sui-box-body">

		<div class="dashui-plugins-filter">

			<div class="dashui-plugins-filter-search">

				<div class="sui-form-field">

					<div class="sui-control-with-icon">

						<input type="text"
						       name="search"
						       placeholder="<?php esc_html_e( 'Search plugins', 'wpmudev' ); ?>"
						       tabindex="0"
						       class="sui-form-control"/>

						<i class="sui-icon-magnifying-glass-search" aria-hidden="true"></i>

					</div>

				</div>

			</div>

			<div class="dashui-plugins-filter-tabs">

				<div class="sui-side-tabs">

					<div class="sui-tabs-menu">

						<div role="button"
						     class="sui-tab-item active"
						     data-filter="all"
						     tabindex="1">
							<?php esc_html_e( 'All', 'wpmudev' ); ?>
						</div>

						<div role="button"
						     class="sui-tab-item"
						     data-filter="activated"
						     tabindex="2">
							<?php esc_html_e( 'Activated', 'wpmudev' ); ?>
						</div>

						<div role="button"
						     class="sui-tab-item"
						     data-filter="deactivated"
						     tabindex="3">
							<?php esc_html_e( 'Deactivated', 'wpmudev' ); ?>
						</div>

						<?php if ( ! empty( $update_plugins ) && $update_plugins ) : ?>
							<div role="button"
							     class="sui-tab-item wdev-update-tab"
							     data-filter="hasupdate"
							     data-count="<?php echo esc_attr( $update_plugins ); ?>"
							     tabindex="4">
								<?php esc_html_e( 'Updates', 'wpmudev' ); ?> <span class="sui-tag sui-tag-beta"><?php echo esc_html( $update_plugins ); ?></span>
							</div>
						<?php endif; ?>

					</div>

				</div>

			</div>

		</div>

		<div class="sui-notice sui-notice-info js-no-result-search sui-hidden">
			<p class="js-no-result-search-message"></p>
		</div>

		<div class="sui-row js-plugins-showcase">

			<div class="sui-col-md-4 dashui-top-plugin-box">

				<span tabindex="4" class="dashui-plugin-card-label"><?php esc_html_e( 'Top Plugins', 'wpmudev' ); ?></span>

				<div class="dashui-top-plugin"></div>

			</div>

			<div class="sui-col-md-4 top-plugins-item dashui-top-plugin-box">

				<div class="dashui-top-plugin"></div>

			</div>

			<div class="sui-col-md-4 dashui-new-plugin-box">

				<span class="dashui-plugin-card-label" tabindex="5"><?php esc_html_e( 'New Releases', 'wpmudev' ); ?></span>

				<div class="dashui-new-plugin"></div>

			</div>

		</div>

	</div>

	<table class="sui-table sui-table-flushed dashui-table-plugins">

		<tbody>

		<tr class="dashui-bulk-action bulk-action-row js-plugins-bulk-action">

			<td colspan="3">

				<label for="bulk-actions-all"
				       class="sui-checkbox">
					<input type="checkbox"
					       name="all-actions"
					       id="bulk-actions-all"
					       class="js-plugin-check-all"/>
					<span aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Select all plugins', 'wpmudev' ); ?></span>
				</label>

				<select name="current-bulk-action"
				        class="sui-select-sm sui-select-inline">
					<option value=""><?php esc_html_e( 'Bulk Actions', 'wpmudev' ); ?></option>
					<option value="update"><?php esc_html_e( 'Update', 'wpmudev' ); ?></option>
					<option value="activate"><?php esc_html_e( 'Activate', 'wpmudev' ); ?></option>
					<option value="install"><?php esc_html_e( 'Install', 'wpmudev' ); ?></option>
					<option value="deactivate"><?php esc_html_e( 'Deactivate', 'wpmudev' ); ?></option>
					<option value="delete"><?php esc_html_e( 'Delete', 'wpmudev' ); ?></option>
				</select>

				<button class="sui-button sui-button-ghost js-plugins-bulk-action-button"
				        disabled="disabled">
					<?php esc_html_e( 'Apply', 'wpmudev' ); ?>
				</button>

			</td>

		</tr>

		</tbody>

	</table>

	<div class="sui-box-body">

		<div class="sui-pagination-wrap sui-block-content-center">
			<ul class="sui-pagination plugin-list-pagination" style="margin: 0 auto"></ul>
		</div>

	</div>
</div>

<div class="sui-hidden">
	<?php
	foreach ( $data['projects'] as $project ) {
		if ( empty( $project['id'] ) ) {
			continue;
		}
		if ( 'plugin' !== $project['type'] ) {
			continue;
		}

		$this->render_project( $project['id'] );
	}
	?>

	<div class="js-notifications">
		<div class="sui-notice-top sui-notice-success js-activated-single">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Success', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugin activated.', 'wpmudev' ); ?></p>
				<p><?php esc_html_e( 'Hold on a moment while we are refreshing your WordPress installation...', 'wpmudev' ); ?></p>
			</div>
		</div>
		<div class="sui-notice-top sui-notice-error sui-can-dismiss js-failed-activated-single">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Failed', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugin failed to be activated.', 'wpmudev' ); ?></p>
				<p class="js-custom-message"></p>
			</div>
			<span class="sui-notice-dismiss">
				<a role="button" aria-label="Dismiss" class="sui-icon-check"></a>
			</span>
		</div>
		<div class="sui-notice-top sui-notice-success js-activated-multi">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Success', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugins activated.', 'wpmudev' ); ?></p>
				<p><?php esc_html_e( 'Hold on a moment while we are refreshing your WordPress installation...', 'wpmudev' ); ?></p>
			</div>
		</div>

		<div class="sui-notice-top sui-notice-success js-deactivated-single">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Success', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugin deactivated.', 'wpmudev' ); ?></p>
				<p><?php esc_html_e( 'Hold on a moment while we are refreshing your WordPress installation...', 'wpmudev' ); ?></p>
			</div>
		</div>
		<div class="sui-notice-top sui-notice-success js-deactivated-multi">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Success', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugins deactivated.', 'wpmudev' ); ?></p>
				<p><?php esc_html_e( 'Hold on a moment while we are refreshing your WordPress installation...', 'wpmudev' ); ?></p>
			</div>
		</div>

		<div class="sui-notice-top sui-notice-success sui-can-dismiss js-installed-single">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Success', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugin successfully installed.', 'wpmudev' ); ?></p>
			</div>
			<span class="sui-notice-dismiss">
				<a role="button" aria-label="Dismiss" class="sui-icon-check"></a>
			</span>
		</div>
		<div class="sui-notice-top sui-notice-error sui-can-dismiss js-failed-installed-single">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Failed', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugin failed to be installed.', 'wpmudev' ); ?></p>
				<p class="js-custom-message"></p>
			</div>
			<span class="sui-notice-dismiss">
				<a role="button" aria-label="Dismiss" class="sui-icon-check"></a>
			</span>
		</div>

		<div class="sui-notice-top sui-notice-success sui-can-dismiss js-deleted-single">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Success', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugin successfully deleted.', 'wpmudev' ); ?></p>
			</div>
			<span class="sui-notice-dismiss">
				<a role="button" aria-label="Dismiss" class="sui-icon-check"></a>
			</span>
		</div>
		<div class="sui-notice-top sui-notice-error sui-can-dismiss js-failed-deleted-single">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Failed', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugin failed to be deleted.', 'wpmudev' ); ?></p>
				<p class="js-custom-message"></p>
			</div>
			<span class="sui-notice-dismiss">
				<a role="button" aria-label="Dismiss" class="sui-icon-check"></a>
			</span>
		</div>

		<div class="sui-notice-top sui-notice-success sui-can-dismiss js-updated-single">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Success', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugin successfully updated.', 'wpmudev' ); ?></p>
			</div>
			<span class="sui-notice-dismiss">
				<a role="button" aria-label="Dismiss" class="sui-icon-check"></a>
			</span>
		</div>
		<div class="sui-notice-top sui-notice-error sui-can-dismiss js-failed-updated-single">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Failed', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugin failed to be updated.', 'wpmudev' ); ?></p>
				<p class="js-custom-message"></p>
			</div>
			<span class="sui-notice-dismiss">
				<a role="button" aria-label="Dismiss" class="sui-icon-check"></a>
			</span>
		</div>

		<div class="sui-notice-top sui-notice-success sui-can-dismiss js-updated-bulk">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Success', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugins successfully updated.', 'wpmudev' ); ?></p>
			</div>
			<span class="sui-notice-dismiss">
				<a role="button" aria-label="Dismiss" class="sui-icon-check"></a>
			</span>
		</div>

		<div class="sui-notice-top sui-notice-success sui-can-dismiss js-installed-bulk">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Success', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugins successfully installed.', 'wpmudev' ); ?></p>
			</div>
			<span class="sui-notice-dismiss">
				<a role="button" aria-label="Dismiss" class="sui-icon-check"></a>
			</span>
		</div>

		<div class="sui-notice-top sui-notice-success sui-can-dismiss js-deleted-bulk">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Success', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Plugins successfully deleted.', 'wpmudev' ); ?></p>
			</div>
			<span class="sui-notice-dismiss">
				<a role="button" aria-label="Dismiss" class="sui-icon-check"></a>
			</span>
		</div>


		<div class="sui-notice-top sui-notice-error sui-can-dismiss js-general-fail">
			<div class="sui-notice-content">
				<p><strong><?php esc_html_e( 'Failed', 'wpmudev' ); ?>:</strong> <?php esc_html_e( 'Unexpected response from WordPress.', 'wpmudev' ); ?></p>
			</div>
			<span class="sui-notice-dismiss">
				<a role="button" aria-label="Dismiss" class="sui-icon-check"></a>
			</span>
		</div>
	</div>

</div>

<?php
$this->load_sui_template( 'element-last-refresh', array(), true );
$this->load_sui_template( 'footer', array(), true );
if ( 'full' !== $membership_type ) {
	$this->render_upgrade_box( $membership_type );
}
if ( ! WPMUDEV_Dashboard::$upgrader->can_auto_install( 'plugin' ) ) {
	$this->load_sui_template( 'popup-ftp-details', array(), true );
}
?>

<?php // bulk action ?>
<div class="sui-dialog" aria-hidden="true" tabindex="-1" id="bulk-action-modal">

	<div class="sui-dialog-overlay" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" aria-labelledby="dialogTitle" aria-describedby="dialogDescription" role="alertdialog">

		<div class="sui-box" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="dialogTitle"><?php esc_html_e( 'Bulk Actions', 'wpmudev' ); ?></h3>
				<div class="sui-actions-right">
					<a data-a11y-dialog-hide class="sui-dialog-close" aria-label="<?php esc_html_e( 'Close this dialog window', 'wpmudev' ); ?>"></a>
				</div>
			</div>

			<div class="sui-box-body">

				<div class="sui-notice sui-notice-warning js-bulk-errors" style="text-align:left">
				</div>

				<div class="sui-notice js-bulk-message-need-reload" style="text-align:left">
					<p><?php esc_html_e( 'This page need to be reloaded before changes you just made become visible.', 'wpmudev' ); ?></p>
					<div class="sui-notice-buttons">
						<a href="" class="sui-button"><?php esc_html_e( 'Reload now', 'wpmudev' ); ?></a>
					</div>
				</div>

				<div class="sui-progress-block">

					<div class="sui-progress">

						<span class="sui-progress-icon js-bulk-actions-loader-icon" aria-hidden="true">
							<i class="sui-icon-loader sui-loading"></i>
						</span>

						<span class="sui-progress-text">
							<span>0%</span>
						</span>

						<div class="sui-progress-bar" aria-hidden="true">
							<span style="width: 0%" class="js-bulk-actions-progress"></span>
						</div>
					</div>
				</div>

				<div class="sui-progress-state">
					<span class="js-bulk-actions-state"></span>
				</div>

			</div>


			<div class="sui-hidden js-bulk-hash"
			     data-activate="<?php echo esc_attr( wp_create_nonce( 'project-activate' ) ); ?>"
			     data-deactivate="<?php echo esc_attr( wp_create_nonce( 'project-deactivate' ) ); ?>"
			     data-install="<?php echo esc_attr( wp_create_nonce( 'project-install' ) ); ?>"
			     data-delete="<?php echo esc_attr( wp_create_nonce( 'project-delete' ) ); ?>"
			     data-update="<?php echo esc_attr( wp_create_nonce( 'project-update' ) ); ?>"
			>

			</div>

		</div>

	</div>
</div>



