<?php
/**
 * Caching meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $caching_url    Caching URL.
 * @var array  $human_results  Array of results. Readable format.
 * @var array  $recommended    Array of recommended values.
 * @var array  $results        Array of results. Raw.
 */

?>
<div class="content">
	<p><?php esc_html_e( 'Caching stores temporary data on your visitors devices so that they don’t have to download assets twice if they don’t have to.', 'wphb' ); ?></p>
</div>

<div class="wphb-dash-table three-columns">
	<div class="wphb-dash-table-header">
		<span><?php esc_html_e( 'File Type', 'wphb' ); ?></span>
		<span><?php esc_html_e( 'Recommended', 'wphb' ); ?></span>
		<span><?php esc_html_e( 'Current', 'wphb' ); ?></span>
	</div>

	<?php foreach ( $human_results as $type => $result ) :
		if ( $result && $recommended[ $type ]['value'] <= $results[ $type ] ) {
			$result_status       = $result;
			$result_status_color = 'green';
			$tooltip_text        = __( 'Caching is enabled', 'wphb' );
		} elseif ( $result ) {
			$result_status       = $result;
			$result_status_color = 'yellow';
			$tooltip_text        = __( "Caching is enabled but you aren't using our recommended value", 'wphb' );
		} else {
			$result_status       = __( 'Disabled', 'wphb' );
			$result_status_color = 'yellow';
			$tooltip_text        = __( 'Caching is disabled', 'wphb' );
		} ?>
		<div class="wphb-dash-table-row">
			<div>
				<span class="wphb-filename-extension wphb-filename-extension-<?php echo esc_attr( $type ); ?>">
					<?php
					switch ( $type ) {
						case 'javascript':
							$label = 'JavaScript';
							echo 'js';
							break;
						case 'images':
							$label = 'Images';
							echo 'img';
							break;
						case 'css':
							$label = 'CSS';
							echo esc_html( $type );
							break;
						case 'media':
							$label = 'Media';
							echo esc_html( $type );
							break;
					} ?>
				</span>
				<?php echo esc_html( $label ); ?>
			</div>

			<div>
				<span class="wphb-button-label wphb-button-label-light" tooltip="<?php printf( esc_attr( 'The recommended value for this file type is at least %s. The longer the better!', 'wphb' ), esc_html( $recommended[ $type ]['label'] ) ); ?>">
					<?php echo esc_html( $recommended[ $type ]['label'] ); ?>
				</span>
			</div>

			<div>
				<span class="wphb-button-label wphb-button-label-<?php echo esc_attr( $result_status_color ); ?> tooltip-right" tooltip="<?php echo esc_attr( $tooltip_text ); ?>">
					<?php echo esc_html( $result_status ); ?>
				</span>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<div class="buttons">
	<a href="<?php echo esc_url( $caching_url ); ?>" class="button button-ghost" name="submit">
		<?php esc_html_e( 'Configure', 'wphb' ); ?>
	</a>
</div>