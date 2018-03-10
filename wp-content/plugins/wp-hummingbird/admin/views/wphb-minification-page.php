<?php if ( $this->has_meta_boxes( 'box-enqueued-files-empty' ) ) : ?>
	<div class="row">
		<?php $this->do_meta_boxes( 'box-enqueued-files-empty' ); ?>
	</div>
<?php endif; ?>

<div class="row">
	<?php
	if ( ! $this->has_meta_boxes( 'box-enqueued-files-empty' ) ) {
		$message = sprintf(
			/* translators: %d: number of files, %d: number of files optimized */
			__( '<strong>Hummingbird found %1$d files and has automatically optimized %2$d of them!</strong>', 'wphb' ),
			WP_Hummingbird_Utils::minified_files_count(),
			WP_Hummingbird_Utils::minified_files_count( true )
		);
		if ( 'basic' === $this->mode ) {
			$message .= ' ';
			$message .= __( 'If you wish to have more control, <a href="#" class="wphb-switch-button">switch to advanced mode</a>.', 'wphb' );
		}
		$this->admin_notices->show( 'minification-optimized', $message, 'warning', false, true );
	}

	$this->do_meta_boxes( 'summary' );
	?>
</div>

<?php if ( ! $this->has_meta_boxes( 'box-enqueued-files-empty' ) ) : ?>
	<div class="row">
		<div class="col-fifth">
			<?php $this->show_tabs(); ?>
		</div><!-- end col-sixth -->

		<div class="col-four-fifths">
			<?php if ( 'files' === $this->get_current_tab() ) : ?>
				<form id="wphb-minification-form" method="post">
					<?php $this->do_meta_boxes( 'main' ); ?>
				</form>
			<?php endif; ?>

			<?php if ( 'tools' === $this->get_current_tab() ) : ?>
				<form id="wphb-minification-tools-form" method="post">
					<?php $this->do_meta_boxes( 'tools' ); ?>
				</form>
			<?php endif; ?>

			<?php if ( 'settings' === $this->get_current_tab() ) : ?>
				<form id="wphb-minification-settings-form" method="post">
					<?php $this->do_meta_boxes( 'settings' ); ?>
				</form>
			<?php endif; ?>
		</div><!-- end col-five-sixths -->

	</div><!-- end row -->
<?php endif;
if ( 'advanced' === $this->mode ) {
	WP_Hummingbird_Utils::get_modal( 'minification-basic' );
} else {
	WP_Hummingbird_Utils::get_modal( 'minification-advanced' );
}
?>

<script>
	jQuery(document).ready( function() {
		var module = window.WPHB_Admin.getModule( 'minification' );
		<?php if ( isset( $_GET['run'] ) ) : ?>
			module.$checkFilesButton.trigger( 'click' );
		<?php endif; ?>
	});
</script>