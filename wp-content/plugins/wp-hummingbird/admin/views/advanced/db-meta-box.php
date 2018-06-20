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

<p>
	<?php esc_html_e( 'Clean up your database of unnecessary data you probably don’t need. You can schedule daily, weekly or monthly automatic cleanups.', 'wphb' ); ?>
</p>

<div class="wphb-border-frame">
	<div class="table-header">
		<div><?php esc_html_e( 'Data Type', 'wphb' ); ?></div>
		<div><?php esc_html_e( 'Entries', 'wphb' ); ?></div>
		<div class="">&nbsp;</div>
	</div>

	<?php
	$total = 0;
	foreach ( $fields as $type => $field ) :
		$total = $total + $field['value'];
	?>
		<div class="table-row" data-type="<?php echo esc_attr( $type ); ?>">
			<div>
				<?php echo esc_html( $field['title'] ); ?>
				<span class="sui-tooltip sui-tooltip-constrained" data-tooltip="<?php echo esc_attr( $field['tooltip'] ); ?>">
					<i class="sui-icon-info" aria-hidden="true"></i>
				</span>
			</div>
			<div class="wphb-db-items"><?php echo absint( $field['value'] ); ?></div>
			<div>
				<span class="spinner standalone"></span>
				<a id="wphb-db-row-delete"
				   class="wphb-db-row-delete sui-tooltip sui-tooltip-top-left"
				   data-tooltip="<?php esc_attr_e( 'Delete entries', 'wphb' ); ?>"
				   data-type="<?php echo esc_attr( $type ); ?>"
				   data-entries="<?php echo absint( $field['value'] ); ?>">
					<i class="sui-icon-trash" aria-hidden="true"></i>
				</a>
			</div>
		</div>
	<?php endforeach; ?>

	<div class="sui-box-footer" data-type="all">
		<div class="sui-actions-left sui-no-margin-left">
			<span class="status-text">
				<?php esc_html_e( 'Tip: Make sure you have a current backup before running a cleanup.', 'wphb' ); ?>
			</span>
		</div>
		<div class="sui-actions-right">
			<i class="sui-icon-loader sui-loading sui-fw sui-hidden" aria-hidden="true"></i>
			<a id="wphb-db-delete-all"
			   class="sui-button wphb-db-delete-all"
			   data-type="all"
			   data-entries="<?php echo absint( $total ); ?>">
				<?php esc_html_e( 'Delete All', 'wphb' ); ?> (<?php echo absint( $total ); ?>)
			</a>
		</div>
	</div>
</div>

<?php WP_Hummingbird_Utils::get_modal( 'database-cleanup' ); ?>