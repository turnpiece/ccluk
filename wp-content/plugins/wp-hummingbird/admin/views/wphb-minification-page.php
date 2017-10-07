<?php if ( $this->has_meta_boxes( 'box-enqueued-files-empty' ) ) : ?>
	<div class="row">
		<?php $this->do_meta_boxes( 'box-enqueued-files-empty' ); ?>
	</div>
<?php endif; ?>

<div class="row">
	<?php $this->do_meta_boxes( 'summary' ); ?>
</div>

<?php if ( ! $this->has_meta_boxes( 'box-enqueued-files-empty' ) ) : ?>
	<div class="row">
		<div class="col-fifth">
			<?php $this->show_tabs(); ?>
		</div><!-- end col-sixth -->

		<div class="col-four-fifths">
			<form action="" method="post" id="wphb-minification-form">
				<?php if ( 'files' === $this->get_current_tab() ) : ?>
					<div class="minification-main-screen">
						<?php $this->do_meta_boxes( 'main' ); ?>

						<?php if ( $this->has_meta_boxes( 'main-2' ) ) : ?>
							<div class="wphb-notice wphb-notice-box no-top-space">
								<p><?php esc_html_e( 'Hummingbird will combine your files as best it can, however, depending on your settings, combining all your files might not be possible. What you see here is the best output Hummingbird can muster!', 'wphb' ); ?></p>
							</div>
							<?php $this->do_meta_boxes( 'main-2' ); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( 'settings' === $this->get_current_tab() ) : ?>
					<div class="minification-settings-screen">
						<?php $this->do_meta_boxes( 'settings' ); ?>
					</div>
				<?php endif; ?>
			</form>
		</div><!-- end col-five-sixths -->

	</div><!-- end row -->
<?php endif; ?>

<?php
wphb_membership_modal();
?>

<script>
	jQuery(document).ready( function() {
		var module = window.WPHB_Admin.getModule( 'minification' );
		<?php if ( isset( $_GET['run'] ) ) : ?>
			module.$checkFilesButton.trigger( 'click' );
		<?php endif; ?>
	});
</script>