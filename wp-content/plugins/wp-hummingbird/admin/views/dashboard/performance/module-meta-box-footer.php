<?php
/**
 * Performance meta box footer on dashboard page.
 *
 * @package Hummingbird
 *
 * @since 1.7.0
 *
 * @var bool   $dismissed  Is the report dismissed.
 * @var string $url        Url to performance module.
 */

?>

<?php if ( $dismissed ) : ?>
	<a href="<?php echo esc_url( $url ); ?>" class="sui-button sui-button-ghost">
		<i class="sui-icon-wrench-tool" aria-hidden="true"></i>
		<?php esc_html_e( 'Configure', 'wphb' ); ?>
	</a>
<?php else : ?>
	<a href="<?php echo esc_url( $url ); ?>" class="sui-button sui-button-ghost">
		<i class="sui-icon-eye" aria-hidden="true"></i>
		<?php esc_html_e( 'View Full Report', 'wphb' ); ?>
	</a>
<?php endif; ?>

<div class="sui-actions-right">
	<span class="status-text">
		<a href="<?php echo esc_url( $url . '&view=settings' ); ?>">
			<?php esc_html_e( 'Customize widget', 'wphb' ); ?>
		</a>
	</span>
</div>
