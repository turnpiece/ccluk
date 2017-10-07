<?php if( wphb_is_htaccess_writable() === true ) : ?>
	<div id="enable-cache-wrap" class="<?php echo ! in_array( $server_type, array( 'apache', 'LiteSpeed' ) ) ? 'hidden' : ''; ?>">

		<?php if ( $show_enable_button ): ?>
			<?php if( wphb_is_htaccess_written('gzip') === true ) : ?>
				<a href="<?php echo esc_url( $disable_link ); ?>" class="button button-ghost"><?php esc_attr_e( 'Disable compression', 'wphb' ); ?></a>
			<?php else : ?>
				<a href="<?php echo esc_url( $enable_link ); ?>" class="button"><?php esc_attr_e( 'Enable compression', 'wphb' ); ?></a>
			<?php endif; ?>
		<?php endif; ?>
	</div>
<?php endif; ?>