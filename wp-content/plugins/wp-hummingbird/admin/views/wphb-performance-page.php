<?php
/** @var WP_Hummingbird_Performance_Report_Page $this */
$last_test = WP_Hummingbird_Module_Performance::get_last_report();
?>

<?php if ( $this->has_meta_boxes( 'summary' ) ) : ?>
	<?php $this->do_meta_boxes( 'summary' ); ?>
<?php endif; ?>

<div class="sui-row-with-sidenav">
	<?php if ( $last_test ) : ?>
		<?php $this->show_tabs(); ?>
		<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>
	<?php else : ?>
		<?php $this->do_meta_boxes( 'main' ); ?>
	<?php endif; ?>
</div><!-- end row -->

<script>
	jQuery(document).ready( function() {
		window.WPHB_Admin.getModule( 'performance' );
	});
</script>