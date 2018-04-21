<div class="sui-sidenav">
	<ul class="sui-vertical-tabs sui-sidenav-hide-md">
		<?php foreach ( $this->get_tabs() as $tab => $name ) : ?>
			<li class="sui-vertical-tab <?php echo ( $tab === $this->get_current_tab() ) ? 'current' : null; ?>">
				<a href="<?php echo esc_url( $this->get_tab_url( $tab ) ); ?>">
					<?php echo esc_html( $name ); ?>
				</a>
				<?php do_action( 'wphb_admin_after_tab_' . $this->get_slug(), $tab ); ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<div class="sui-sidenav-hide-lg">
		<select class="sui-mobile-nav">
			<?php foreach ( $this->get_tabs() as $tab => $name ) : ?>
				<option value="<?php echo esc_url( $this->get_tab_url( $tab ) ); ?>" <?php selected( $this->get_current_tab(), $tab ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>