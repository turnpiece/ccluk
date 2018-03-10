<?php
/** @var WP_Hummingbird_Performance_Report_Page $this */
$last_test = WP_Hummingbird_Module_Performance::get_last_report();
?>

<?php if ( $this->has_meta_boxes( 'summary' ) ) : ?>
	<div class="row">
		<?php $this->do_meta_boxes( 'summary' ); ?>
	</div>
<?php endif; ?>

<div class="row">
	<?php if ( $last_test ) : ?>
		<div class="col-fifth">
			<?php $this->show_tabs(); ?>
		</div><!-- end col-sixth -->

		<div class="col-four-fifths">
			<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>
		</div>
	<?php else : ?>
		<?php $this->do_meta_boxes( 'main' ); ?>
	<?php endif; ?>
</div><!-- end row -->

<script>
	jQuery(document).ready( function() {
		window.WPHB_Admin.getModule( 'performance' );
	});
</script>