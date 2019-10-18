<?php

/**
 * Reports settings page template.
 *
 * @var bool  $network             Is network settings page?.
 * @var array $roles               Roles array.
 * @var array $dashboard_tree      Report item tree.
 * @var array $statistics_tree     Statistics page tree.
 * @var array $dashboard_selected  Selected items in dashboard reports.
 * @var array $statistics_selected Selected items in statistics reports.
 */

defined( 'WPINC' ) || die();

use Beehive\Core\Helpers\Template;
use Beehive\Core\Helpers\Permission;

?>

<input type="hidden" name="beehive_settings_group" value="reports">

<div class="sui-box-settings-row">

	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Analytics Reports', 'ga_trans' ); ?></span>
		<span class="sui-description"><?php esc_html_e( 'Pick and choose the specific types of analytics to include in the Dashboard widgets.', 'ga_trans' ); ?></span>
	</div>

	<div class="sui-box-settings-col-2">

		<?php if ( empty( $roles ) ) : ?>

			<div class="sui-notice sui-notice-warning">
				<p><?php esc_html_e( 'No roles are selected in Permissions settings.', 'ga_trans' ); ?></p>
			</div>

		<?php else : ?>

			<?php $roles_count = count( $roles ); ?>

			<select id="beehive-reports-role" <?php disabled( 1 === $roles_count && isset( $roles['administrator'] ) ); ?>>
				<?php $selected = ( 1 === $roles_count ) ? array_keys( $roles )[0] : array_keys( $roles )[1]; ?>
				<?php foreach ( $roles as $role => $label ) : ?>
					<option value="<?php echo esc_attr( $role ); ?>" <?php selected( $selected, $role ); ?> <?php disabled( $roles_count > 1 && 'administrator' === $role ); ?>><?php echo esc_attr( $label ); ?></option>
				<?php endforeach; ?>
			</select>

			<?php foreach ( $roles as $role => $label ) : ?>

				<?php $disabled = ( 'administrator' === $role ); ?>
				<?php $checked = ( 'administrator' === $role ); ?>

				<div class="sui-border-frame beehive-reports-children <?php echo $selected === $role ? '' : 'sui-hidden'; ?>" id="beehive-reports-settings-<?php echo esc_attr( $role ); ?>">

					<?php if ( 1 === $roles_count && isset( $roles['administrator'] ) ) : ?>
						<?php $permission_settings = Template::settings_page( 'permissions', ! Permission::can_overwrite() ); ?>
						<?php if ( Permission::can_overwrite() || current_user_can( 'manage_network' ) ) : ?>
							<div class="sui-notice sui-notice-info">
								<p><?php printf( __( 'You have allowed only administrator to have access to dashboard widgets. You can configure it in <a href="%s">Permissions tab</a>.', 'ga_trans' ), $permission_settings ); ?></p>
							</div>
						<?php else : ?>
							<div class="sui-notice sui-notice-info">
								<p><?php esc_html_e( 'You have allowed only administrator to have access to dashboard widgets.', 'ga_trans' ); ?></p>
							</div>
						<?php endif; ?>
					<?php endif; ?>

					<!-- Dahboard reports -->
					<div class="beehive-reports-child dashboard-stats">

						<span class="sui-description"><?php esc_html_e( 'Select which statistics you want to display according to roles', 'ga_trans' ); ?></span>

						<span class="sui-label"><?php esc_html_e( 'Dashboard Widget', 'ga_trans' ); ?></span>

						<?php $role_selected = isset( $dashboard_selected[ $role ] ) ? $dashboard_selected[ $role ] : []; ?>

						<ul class="sui-tree" data-tree="selector" role="group" id="sui-dashboard-tree-<?php echo esc_attr( $role ); ?>">

							<?php foreach ( $dashboard_tree as $parent_key => $data ) : ?>

								<?php $parent_selected = isset( $role_selected[ $parent_key ], $data['items'] ) ? count( $data['items'] ) === count( $role_selected[ $parent_key ] ) : $checked; ?>
								<?php $parent_selected = 'pages' === $parent_key && isset( $role_selected[ $parent_key ] ) ? true : $parent_selected; ?>

								<?php $parent_input_id = "reports-dashboard-{$role}-{$parent_key}"; ?>
								<?php $parent_input_name = "reports[dashboard][$role][$parent_key]"; ?>

								<li role="treeitem" aria-selected="<?php echo $parent_selected ? 'true' : 'false'; ?>" aria-disabled="<?php echo $disabled ? 'true' : 'false'; ?>">

									<div class="sui-tree-node">
										<label for="<?php echo esc_attr( $parent_input_id ); ?>" class="sui-node-checkbox">
											<input type="checkbox" name="<?php echo esc_html( $parent_input_name ); ?>" id="<?php echo esc_attr( $parent_input_id ); ?>" value="1" <?php checked( $parent_selected ); ?> <?php disabled( $disabled ); ?> />
											<span aria-hidden="true"></span>
											<span><?php echo esc_html( $data['label'] ); ?></span>
										</label>
										<span class="sui-node-text"><?php echo esc_html( $data['label'] ); ?></span>
										<button data-button="expander">
											<span aria-hidden="true"></span>
											<span class="sui-screen-reader-text"><?php esc_html_e( 'Open or close this item', 'ga_trans' ); ?></span>
										</button>
									</div>

									<?php if ( ! empty( $data['items'] ) ) : ?>

										<ul role="group">

											<?php foreach ( $data['items'] as $item_key => $item_label ) : ?>

												<?php $child_input_id = "reports-dashboard-{$role}-{$parent_key}-{$item_key}"; ?>
												<?php $child_input_name = "reports[dashboard][$role][$parent_key][$item_key]"; ?>
												<?php $child_checked = isset( $role_selected[ $parent_key ][ $item_key ] ) || $checked; ?>

												<li role="treeitem" aria-selected="<?php echo isset( $role_selected[ $parent_key ][ $item_key ] ) ? 'true' : 'false'; ?>" aria-disabled="<?php echo $disabled ? 'true' : 'false'; ?>">
													<div class="sui-tree-node">
														<label for="<?php echo esc_attr( $child_input_id ); ?>" class="sui-node-checkbox">
															<input type="checkbox" name="<?php echo esc_html( $child_input_name ); ?>" id="<?php echo esc_attr( $child_input_id ); ?>" value="1" <?php checked( $child_checked ); ?> <?php disabled( $disabled ); ?> />
															<span aria-hidden="true"></span>
															<span><?php echo esc_html( $item_label ); ?></span>
														</label>
														<span class="sui-node-text"><?php echo esc_html( $item_label ); ?></span>
													</div>
												</li>

											<?php endforeach; ?>

										</ul>

									<?php endif; ?>

								</li>

							<?php endforeach; ?>

						</ul>

					</div>
					<!-- Dahboard reports end -->

					<!-- Statistics reports -->
					<div class="beehive-reports-child all-stats">

						<span class="sui-label"><?php esc_html_e( 'All Statistics', 'ga_trans' ); ?></span>

						<?php $role_selected = isset( $statistics_selected[ $role ] ) ? $statistics_selected[ $role ] : []; ?>

						<ul class="sui-tree" data-tree="selector" role="group" id="sui-statistics-tree-<?php echo esc_attr( $role ); ?>">

							<?php foreach ( $statistics_tree as $key => $label ) : ?>

								<?php $child_checked = isset( $role_selected[ $key ] ) || $checked; ?>

								<?php $input_id = "report-statistics-{$role}-{$key}"; ?>
								<?php $input_name = "reports[statistics][$role][$key]"; ?>

								<li role="treeitem" aria-selected="<?php echo $child_checked ? 'true' : 'false'; ?>" aria-disabled="<?php echo $disabled ? 'true' : 'false'; ?>">

									<div class="sui-tree-node">
										<label for="<?php echo esc_attr( $input_id ); ?>" class="sui-node-checkbox">
											<input type="checkbox" name="<?php echo esc_html( $input_name ); ?>" id="<?php echo esc_attr( $input_id ); ?>" value="1" <?php checked( $child_checked ); ?> <?php disabled( $disabled ); ?> />
											<span aria-hidden="true"></span>
											<span><?php echo esc_html( $label ); ?></span>
										</label>
										<span class="sui-node-text"><?php echo esc_html( $label ); ?></span>
									</div>

								</li>

							<?php endforeach; ?>

						</ul>

					</div>
					<!-- Statistics reports end -->

				</div>

			<?php endforeach; ?>

		<?php endif; ?>

	</div>
</div>