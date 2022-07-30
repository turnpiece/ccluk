<?php
/**
 * Shipper package migration modals: package-specific settings template, database section
 *
 * @since v1.1
 * @package shipper
 */

$select_item  = __( 'Select this item', 'shipper' );
$openclose    = __( 'Open or close this item', 'shipper' );
$all_tables   = Shipper_Helper_Template_Sorter::get_grouped_tables();
$wp_tables    = $all_tables[ Shipper_Helper_Template_Sorter::WP_TABLES ];
$nonwp_tables = $all_tables[ Shipper_Helper_Template_Sorter::NONWP_TABLES ];
$other_tables = $all_tables[ Shipper_Helper_Template_Sorter::OTHER_TABLES ];
?>
<ul class="sui-tree" data-tree="selector" role="group">
	<li role="treeitem" aria-expanded="true" aria-selected="true">
		<span class="sui-tree-node">
			<span class="sui-node-checkbox" role="checkbox">
				<span class="sui-screen-reader-text"><?php echo esc_html( $select_item ); ?></span>
			</span><!-- sui-node-checkbox -->
			<span class="sui-node-text"><?php esc_html_e( 'All', 'shipper' ); ?></span>
			<span role="button" data-button="expander">
				<span class="sui-screen-reader-text"><?php echo esc_html( $openclose ); ?></span>
			</span><!-- data-button expander -->
		</span><!-- sui-tree-node -->

		<ul role="group" data-source="wp">
			<li role="treeitem">
				<span class="sui-tree-node">
					<span class="sui-node-checkbox" role="checkbox">
						<span class="sui-screen-reader-text"><?php echo esc_html( $select_item ); ?></span>
					</span><!-- sui-node-checkbox -->
					<span class="sui-node-text"><?php esc_html_e( 'WordPress Core Tables', 'shipper' ); ?></span>
					<span role="button" data-button="expander">
						<span class="sui-screen-reader-text"><?php echo esc_html( $openclose ); ?></span>
					</span><!-- data-button expander -->
				</span><!-- sui-tree-node -->


				<ul role="group">
					<?php foreach ( $wp_tables as $table ) { ?>
						<li role="treeitem">
							<span class="sui-tree-node">
								<span class="sui-node-checkbox" role="checkbox">
									<span class="sui-screen-reader-text"><?php echo esc_html( $select_item ); ?></span>
								</span><!-- sui-node-checkbox -->
								<span
									data-table="<?php echo esc_attr( $table ); ?>"
									class="sui-node-text"><?php echo esc_html( $table ); ?></span>
								<span role="button" data-button="expander">
									<span class="sui-screen-reader-text"><?php echo esc_html( $openclose ); ?></span>
								</span><!-- data-button expander -->
							</span><!-- sui-tree-node -->
						</li><!-- treeitem -->
					<?php } ?>
				</ul>
			</li><!-- treeitem -->
		</ul> <!-- data-source wp -->

		<ul role="group" data-source="nonwp">
			<li role="treeitem">
				<span class="sui-tree-node">
					<span class="sui-node-checkbox" role="checkbox">
						<span class="sui-screen-reader-text"><?php echo esc_html( $select_item ); ?></span>
					</span><!-- sui-node-checkbox -->
					<span class="sui-node-text"><?php esc_html_e( 'Non - WordPress Core Tables', 'shipper' ); ?></span>
					<span role="button" data-button="expander">
						<span class="sui-screen-reader-text"><?php echo esc_html( $openclose ); ?></span>
					</span><!-- data-button expander -->
				</span><!-- sui-tree-node -->


				<ul role="group">
					<?php foreach ( $nonwp_tables as $table ) { ?>
						<li role="treeitem">
							<span class="sui-tree-node">
								<span class="sui-node-checkbox" role="checkbox">
									<span class="sui-screen-reader-text"><?php echo esc_html( $select_item ); ?></span>
								</span><!-- sui-node-checkbox -->
								<span
									data-table="<?php echo esc_attr( $table ); ?>"
									class="sui-node-text"><?php echo esc_html( $table ); ?></span>
								<span role="button" data-button="expander">
									<span class="sui-screen-reader-text"><?php echo esc_html( $openclose ); ?></span>
								</span><!-- data-button expander -->
							</span><!-- sui-tree-node -->
						</li><!-- treeitem -->
					<?php } ?>
				</ul>
			</li><!-- treeitem -->
		</ul> <!-- data-source nonwp -->

		<ul role="group" data-source="other">
			<li role="treeitem">
				<span class="sui-tree-node">
					<span class="sui-node-checkbox" role="checkbox">
						<span class="sui-screen-reader-text"><?php echo esc_html( $select_item ); ?></span>
					</span><!-- sui-node-checkbox -->
					<span class="sui-node-text"><?php esc_html_e( 'Other Tables', 'shipper' ); ?></span>
					<span role="button" data-button="expander">
						<span class="sui-screen-reader-text"><?php echo esc_html( $openclose ); ?></span>
					</span><!-- data-button expander -->
				</span><!-- sui-tree-node -->


				<ul role="group">
					<?php foreach ( $other_tables as $table ) { ?>
						<li role="treeitem">
							<span class="sui-tree-node">
								<span class="sui-node-checkbox" role="checkbox">
									<span class="sui-screen-reader-text"><?php echo esc_html( $select_item ); ?></span>
								</span><!-- sui-node-checkbox -->
								<span
									data-table="<?php echo esc_attr( $table ); ?>"
									class="sui-node-text"><?php echo esc_html( $table ); ?></span>
								<span role="button" data-button="expander">
									<span class="sui-screen-reader-text"><?php echo esc_html( $openclose ); ?></span>
								</span><!-- data-button expander -->
							</span><!-- sui-tree-node -->
						</li><!-- treeitem -->
					<?php } ?>
				</ul>
			</li><!-- treeitem -->
		</ul> <!-- data-source other -->

	</li><!-- treeitem -->

</ul><!-- sui-tree -->