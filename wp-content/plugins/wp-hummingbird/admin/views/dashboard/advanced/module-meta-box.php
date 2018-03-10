<?php
/**
 * Advanced tools meta box.
 *
 * @since 1.8
 * @package Hummingbird
 *
 * @var int $count  Number of issues.
 */

?>

<p>
	<?php esc_html_e( 'Database Cleanups and tools to remove unnecessary functions WordPress does that
							can slow down your server.', 'wphb' ); ?>
</p>

<div class="wphb-dash-table two-columns">
	<div class="wphb-dash-table-row">
		<div>
			<?php esc_html_e( 'Database Cleanup', 'wphb' ); ?>
		</div>

		<div>
			<?php if ( $count > 0 ) : ?>
				<span>
					<?php /* translators: %d: number of entries */
						printf( __( '%d dispensable entries', 'wphb' ), absint( $count ) ); ?>
				</span>
			<?php else : ?>
				<span class="no-issues">
					<?php esc_html_e( 'Up to date', 'wphb' ); ?>
				</span>
			<?php endif; ?>
		</div>
	</div>
</div>