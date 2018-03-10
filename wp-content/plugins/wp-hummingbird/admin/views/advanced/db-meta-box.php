<?php
/**
 * Advanced tools: database cleanup meta box.
 *
 * @package Hummingbird
 *
 * @since 1.8
 *
 * @var array $fields  Array with data about rows.
 */
?>

<div class="row settings_form">
	<p>
		<?php esc_html_e( 'Clean up your database of unnecessary data you probably don’t need. You can schedule daily, weekly or monthly automatic cleanups.', 'wphb' ); ?>
	</p>
</div>

<div class="wphb-border-frame">
	<div class="table-header">
		<div><?php esc_html_e( 'Data Type', 'wphb' ); ?></div>
		<div><?php esc_html_e( 'Entries', 'wphb' ); ?></div>
		<div class="">&nbsp;</div>
	</div>

	<?php $total = 0;
	foreach ( $fields as $type => $field ) :
		$total = $total + $field['value']; ?>
		<div class="table-row" data-type="<?php echo esc_attr( $type ); ?>">
			<div>
				<?php echo $field['title']; ?>
				<span class="tooltip" tooltip="<?php echo esc_attr( $field['tooltip'] ); ?>">
					<i class="wphb-icon hb-wpmudev-icon-info"></i>
				</span>
			</div>
			<div class="wphb-db-items"><?php echo absint( $field['value'] ); ?></div>
			<div>
				<span class="spinner standalone"></span>
				<a class="wphb-db-row-delete tooltip tooltip-s tooltip-right"
				   tooltip="<?php esc_attr_e( 'Delete entries', 'wphb' ); ?>"
				   onclick="WPHB_Admin.advanced.showModal( <?php echo absint( $field['value'] ); ?>, '<?php echo esc_attr( $type ); ?>' )">
					<i class="wphb-icon hb-fi-trash"></i>
				</a>
			</div>
		</div>
	<?php endforeach; ?>

	<div class="table-footer" data-type="all">
		<div class="buttons buttons-on-right">
			<span class="status-text alignleft">
				<?php esc_html_e( 'Tip: Make sure you have a current backup before running a cleanup.', 'wphb' ); ?>
			</span>
			<span class="spinner standalone"></span>
			<a class="button button-grey wphb-db-delete-all"
			   onclick="WPHB_Admin.advanced.showModal( <?php echo absint( $total ); ?>, 'all' )">
				<?php esc_html_e( 'Delete All', 'wphb' ); ?> (<?php echo absint( $total ); ?>)
			</a>
		</div>
	</div>
</div>

<?php WP_Hummingbird_Utils::get_modal( 'database-cleanup' ); ?>