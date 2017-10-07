<div class="wphb-notice wphb-notice-<?php echo $class; ?> can-close" <?php if ( $dismissable ) : ?>
	 id="wphb-dismissable"
	 data-id="<?php echo esc_attr( $id ); ?>"<?php endif; ?>>
	<p><?php echo $message; ?></p>
	<div class="close"></div>
</div>

<?php if ( $auto_hide ) : ?>
	<script type="text/javascript">
		jQuery('.wphb-notice:not(.notice)').delay(3000).slideUp('slow');
	</script>
<?php endif; ?>