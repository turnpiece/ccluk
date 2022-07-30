<?php
/**
 * Shipper dash notice templates: not logged in
 *
 * @package shipper
 */

?>
<div class="sui-notice sui-notice-warning">
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: website url. */
						__( 'Whoops, it appears you still haven\'t logged into the WPMU DEV Dashboard. Click <a href="%s">here</a> to log in with your WPMU DEV account details.', 'shipper' ),
						esc_url( $action )
					)
				);
				?>
			</p>
		</div>
	</div>
</div>