<?php
/**
 * Performance summary meta box.
 *
 * @package Hummingbird
 *
 * @var bool     $error             Was there an error during performance scan.
 * @var string   $error_text        Error text.
 * @var string   $error_details     Error details.
 * @var string   $retry_url         Url to start a new performance scan.
 * @var bool     $report_dismissed  If performance report is dismissed.
 * @var bool     $can_run_test      If there is no cool down period and user can run a new test.
 * @var stdClass $last_test         Last test details.
 * @var bool     $is_subsite        Is this a subsite.
 */

if ( $error ) : ?>
	<div class="sui-box-body">
		<div class="sui-notice sui-notice-error wphb-notice-box">
			<p><?php echo $error_text; ?></p>
			<div id="wphb-error-details">
				<p><code><?php echo $error_details; ?></code></p>
			</div>
			<div class="sui-notice-buttons">
				<a href="<?php echo esc_url( $retry_url ); ?>" class="sui-button sui-button-primary button-notice"><?php esc_html_e( 'Try again', 'wphb' ); ?></a>
				<a target="_blank" href="<?php echo esc_url( WP_Hummingbird_Utils::get_link( 'support' ) ); ?>" class="sui-button sui-button-primary button-notice"><?php esc_html_e( 'Support', 'wphb' ); ?></a>
			</div>
		</div>
	</div>
	<script>
		var pressedKeys = [],
			timer;

		function wphbSetInterval() {
			timer = window.setInterval(function(){
				// Clean pressedKeys every 1sec
				pressedKeys = [];
			}, 1000);
		}

		wphbSetInterval();

		document.onkeyup = function( e ) {
			clearInterval( timer );
			wphbSetInterval();
			e = e || event;
			pressedKeys.push( e.keyCode );
			var count = pressedKeys.length;
			if ( count >= 2 ) {
				// Get the previous key pressed. If they are H+B, we'll display the error
				if ( pressedKeys[ count - 1 ] == 66 && pressedKeys[ count - 2 ] == 72 ) {
					var errorDetails = document.getElementById('wphb-error-details');
					errorDetails.style.display = 'block';
				}
			}
		};
	</script>
<?php else : ?>
	<div class="sui-box-body">
		<?php if ( $report_dismissed ) : ?>
			<div class="sui-notice">
				<p><?php esc_html_e( 'You have chosen to ignore this performance test. Run a new test to see new recommendations.', 'wphb' ); ?></p>
				<div class="sui-notice-buttons">
					<?php if ( true === $can_run_test ) : ?>
						<a href="<?php echo esc_url( $retry_url ); ?>"  class="sui-button sui-button-primary"><?php esc_html_e( 'Run Test', 'wphb' ); ?></a>
					<?php
					else :
						$tooltip = sprintf(
							/* translators: %d: number of minutes. */
							_n(
								'Hummingbird is just catching her breath - you can run another test in %d minute',
								'Hummingbird is just catching her breath - you can run another test in %d minutes',
								$can_run_test,
								'wphb'
							),
							number_format_i18n( $can_run_test )
						);
						?>
						<span class="sui-tooltip sui-tooltip-constrained" disabled="disabled" data-tooltip="<?php echo esc_attr( $tooltip ); ?>" aria-hidden="true">
							<a href="<?php echo esc_url( $retry_url ); ?>" disabled class="sui-button sui-button-primary"><?php esc_html_e( 'Run Test', 'wphb' ); ?></a>
						</span>
					<?php endif; ?>
				</div>
				<?php
				$impact_score_class = 'dismissed';
				$impact_icon_class = 'warning-alert';
				?>
			</div>
		<?php else :
			echo '<p>' . esc_html_e( 'Here are your latest performance test results. Action as many fixes as possible, however you can always ignore warnings if you are unable to fix them.', 'wphb' ) . '</p>';
		endif; ?>
	</div>
	<table class="sui-table sui-accordion performance-report-table">
		<tbody>
		<?php
		foreach ( $last_test->rule_result as $rule => $rule_result ) :
			if ( ! $report_dismissed ) :
				if ( 85 <= $rule_result->impact_score ) :
					$impact_score_class = 'success';
					$impact_icon_class = 'check-tick';
				elseif ( 65 <= $rule_result->impact_score ) :
					$impact_score_class = 'warning';
					$impact_icon_class = 'warning-alert';
				else :
					$impact_score_class = 'error';
					$impact_icon_class = 'warning-alert';
				endif;
			endif;
			$colspan = '';
			if ( $is_subsite && 'server' === $rule_result->type ) :
				$impact_score_class = 'disabled';
				$colspan = 'colspan=2';
			endif;
			$has_url_blocks = ! empty( $rule_result->urlblocks ) && is_array( $rule_result->urlblocks ) && ! empty( $rule_result->urlblocks[0] );
			?>
			<tr class="sui-accordion-item sui-<?php echo esc_attr( $impact_score_class ); ?>">
				<td class="sui-accordion-item-title">
					<i class="sui-icon-<?php echo esc_attr( $impact_icon_class ); ?> sui-<?php echo esc_attr( $impact_score_class ); ?>"></i> <?php echo esc_html( $rule_result->label ); ?>
				</td>
				<td>
					<div class="sui-circle-score sui-grade-<?php echo esc_attr( $impact_score_class ); ?> sui-tooltip"
						 data-tooltip="<?php echo esc_html( $rule_result->impact_score ); ?>/100" data-score="<?php echo esc_attr( $rule_result->impact_score ); ?>"></div>
				</td>
				<td class="sui-hidden-xs sui-hidden-sm wphb-performance-report-item-type <?php echo esc_attr( $impact_score_class ); ?>" <?php echo esc_attr( $colspan ); ?>>
					<?php
					if ( 'disabled' === $impact_score_class ) :
						$disabled_label = __( 'This improvement is controlled by the network admin for this site.', 'wphb' );
						if ( is_super_admin() ) :
							$disabled_label = __( 'This can’t be improved at the subsite level.', 'wphb' );
						endif;
						echo esc_html( $disabled_label );
					else :
						echo esc_html( $rule_result->type );
					endif;
					?>
				</td>

				<?php if ( 'disabled' !== $impact_score_class ) : ?>
					<td class="sui-hidden-xs sui-hidden-sm sui-hidden-md">
						<span class="sui-accordion-open-indicator">
							<?php if ( ! empty( $rule_result->summary ) || ! empty( $rule_result->tip ) ) : ?>
								<?php if ( $rule_result->impact_score < 85 && ( ! $is_subsite && 'server' !== $rule_result->type ) && ! $report_dismissed ) : ?>
									<a class="sui-button sui-button-ghost"><?php esc_html_e( 'Improve', 'wphb' ); ?></a>
								<?php endif; ?>
							<i class="sui-icon-chevron-down"></i>
							<?php endif; ?>
						</span>
					</td>
				<?php endif; ?>
			</tr>
			<tr class="sui-accordion-item-content">
				<td colspan=4>
					<div class="sui-box">
						<div class="sui-box-body sui-box-performance-report-additional-content <?php echo $is_subsite ? 'disable-buttons' : ''; ?>">
							<?php if ( ! empty( $rule_result->summary ) ) : ?>
								<h4><?php esc_html_e( 'Overview', 'wphb' ); ?></h4>
								<p><?php echo $rule_result->summary; ?></p>
							<?php endif; ?>

							<?php if ( $has_url_blocks ) : ?>
								<h4><?php esc_html_e( 'Benchmarks', 'wphb' ); ?></h4>

								<ol>
									<?php
									foreach ( $rule_result->urlblocks as $url_block ) :
										if ( empty( $url_block ) ) {
											continue;
										}
										?>

										<p><?php echo $url_block->header; ?></p>

										<?php
										if ( ! empty( $url_block->urls ) && is_array( $url_block->urls ) ) :
											foreach ( $url_block->urls as $url ) :
											?>
											<li><?php echo make_clickable( $url ); ?></li>
										<?php
											endforeach;
										endif;
									endforeach;
									?>
								</ol>
							<?php endif; ?>

							<?php if ( ! empty( $rule_result->tip ) ) : ?>
								<h4><?php esc_html_e( 'How to improve', 'wphb' ); ?></h4>
								<?php echo wpautop( $rule_result->tip ); ?>
							<?php endif; ?>
						</div>
					</div>
				</td>
			</tr>
			<?php
		endforeach;
		?>
		</tbody>
	</table>
<?php endif; ?>

<?php WP_Hummingbird_Utils::get_modal( 'dismiss-report' ); ?>