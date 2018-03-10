<?php
/**
 * Asset optimization meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var float  $compressed_size          Overall compressed files size in Kb.
 * @var float  $compressed_size_scripts  Amount of space saved by compressing JavaScript.
 * @var float  $compressed_size_styles   Amount of space saved by compressing CSS.
 * @var int    $enqueued_files           Number of enqueued files.
 * @var float  $original_size            Overall original file size in Kb.
 * @var float  $percentage               Percentage saved.
 */

?>
<div class="content">
	<p><?php esc_html_e( 'Compress, combine and position your assets to dramatically improve your page load speed.', 'wphb' ); ?></p>
</div>

<div class="wphb-dash-table two-columns">
	<div class="wphb-dash-table-row">
		<div><?php esc_html_e( 'Total Enqueued Files', 'wphb' ); ?></div>
		<div><?php echo absint( $enqueued_files ); ?></div>
	</div>

	<div class="wphb-dash-table-row">
		<div><?php esc_html_e( 'Total Size Reductions', 'wphb' ); ?></div>
		<div>
			<div class="wphb-pills-group">
				<span class="wphb-pills with-arrow right grey"><?php echo esc_html( $original_size ); ?>KB</span>
				<span class="wphb-pills"><?php echo esc_html( $compressed_size ); ?>KB</span>
			</div>
		</div>
	</div>

	<div class="wphb-dash-table-row">
		<div><?php esc_html_e( 'Total % Reductions', 'wphb' ); ?></div>
		<div><?php echo esc_html( $percentage ); ?>%</div>
	</div>

	<div class="wphb-dash-table-row">
		<div>
			<span class="wphb-filename-extension wphb-filename-extension-js"><?php esc_html_e( 'JS', 'wphb' ); ?></span>
			<?php esc_html_e( 'JavaScript', 'wphb' ); ?>
		</div>
		<div><?php echo esc_html( $compressed_size_scripts ); ?>KB</div>
	</div>

	<div class="wphb-dash-table-row">
		<div>
			<span class="wphb-filename-extension wphb-filename-extension-css"><?php esc_html_e( 'CSS', 'wphb' ); ?></span>
			<?php esc_html_e( 'CSS', 'wphb' ); ?>
		</div>
		<div><?php echo esc_html( $compressed_size_styles ); ?>KB</div>
	</div>
</div><!-- end wphb-dash-table -->