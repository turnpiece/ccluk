<?php if ( $error ) : ?>
	<div class="wphb-table-wrapper add-side-padding">
		<div class="row">
			<div class="wphb-notice wphb-notice-error wphb-notice-box">
				<p><?php echo $error_text; ?></p>
				<div id="wphb-error-details">
					<p><code style="background:white;"><?php echo $error_details; ?></code></p>
				</div>
				<a href="<?php echo esc_url( $retry_url ); ?>" class="button button-notice"><?php esc_html_e( 'Try again', 'wphb' ); ?></a>
				<a target="_blank" href="<?php echo esc_url( WP_Hummingbird_Utils::get_link( 'support' ) ); ?>" class="button button-notice"><?php esc_html_e( 'Support', 'wphb' ); ?></a>
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
	<div class="box-content no-vertical-padding no-vertical-margin">
		<div class="content">
			<?php if ( $report_dismissed ) : ?>
				<div class="wphb-notice wphb-notice-grey">
					<p><?php esc_html_e( 'You have chosen to ignore this performance test. Run a new test to see new recommendations.', 'wphb' ); ?></p>
					<?php if ( ! $disabled ) : ?>
						<div class="buttons">
							<a href="<?php echo esc_url( $retry_url ); ?>" <?php disabled( $disabled ); ?> class="button"><?php esc_html_e( 'Run Test', 'wphb' ); ?></a>
						</div>
					<?php endif; ?>
				</div>
			<?php else :
				echo '<p>' . esc_html_e( 'Here are your latest performance test results. Action as many fixes as possible, however you can always ignore warnings if you are unable to fix them.', 'wphb' ) . '</p>';
			endif; ?>
		</div>
	</div>

	<div class="wphb-table-wrapper complex">

		<table class="list-table hover-effect wphb-table performance-report-table">
			<tbody>
				<?php foreach ( $last_test->rule_result as $rule => $rule_result ) :
					$class = 'dismissed';
					if ( ! $report_dismissed ) :
						switch ( $rule_result->impact_score_class ) {
							case 'aplus':
							case 'a':
							case 'b':
								$class = 'success';
								break;
							case 'c':
							case 'd':
								$class = 'warning';
								break;
							case 'e':
							case 'f':
								$class = 'error';
								break;
						}
					endif;
					$colspan = '';
					if ( $is_subsite && 'server' === $rule_result->type ) :
						$class = 'disabled';
						$colspan = 'colspan=2';
					endif;
					$has_url_blocks = ! empty( $rule_result->urlblocks ) && is_array( $rule_result->urlblocks ) && ! empty( $rule_result->urlblocks[0] ); ?>
					<tr class="wphb-performance-report-item wphb-table-score-<?php echo esc_attr( $class ); ?>" id="rule-<?php echo esc_attr( $rule ); ?>">
						<td class="wphb-performance-report-item-recommendation">
							<?php echo esc_html( $rule_result->label ); ?>
						</td><!-- end wphb-performance-report-item-recommendation -->
						<td class="wphb-performance-report-item-score">
							<div class="wphb-score wphb-score-have-label">
								<div class="tooltip-box">
								<?php if ( $report_dismissed ) : ?>
									<div class="wphb-score-result wphb-score-result-grade-dismissed tooltip-s" tooltip="<?php echo esc_attr( $rule_result->impact_score ); ?>/100">
								<?php elseif ( 'disabled' === $class ) : ?>
									<div class="wphb-score-result wphb-score-result-grade-<?php echo esc_attr( $class ); ?> tooltip-s" tooltip="<?php echo esc_attr( $rule_result->impact_score ); ?>/100">
								<?php else : ?>
									<div class="wphb-score-result wphb-score-result-grade-<?php echo esc_attr( $rule_result->impact_score_class ); ?> tooltip-s" tooltip="<?php echo esc_attr( $rule_result->impact_score ); ?>/100">
								<?php endif; ?>
										<div class="wphb-score-type wphb-score-type-circle">
											<svg class="wphb-score-graph wphb-score-graph-svg" xmlns="http://www.w3.org/2000/svg" width="30" height="30">
												<circle class="wphb-score-graph-circle" r="12.5" cx="15" cy="15" fill="transparent" stroke-dasharray="0" stroke-dashoffset="0"></circle>
												<circle class="wphb-score-graph-circle wphb-score-graph-result" r="12.5" cx="15" cy="15" fill="transparent" stroke-dasharray="80" stroke-dashoffset="0"></circle>
											</svg>
										</div><!-- end wphb-score-type -->
										<div class="wphb-score-result-label"><?php echo esc_html( $rule_result->impact_score ); ?></div>
									</div><!-- end wphb-score-result -->
								</div><!-- end tooltip-box -->
							</div><!-- end wphb-score -->
						</td><!-- end wphb-performance-report-item-score -->
						<td class="wphb-performance-report-item-type <?php echo esc_attr( $class ); ?>" <?php echo esc_attr( $colspan ); ?>>
							<?php
							if ( 'disabled' === $class ) :
								$disabled_label = __( 'This improvement is controlled by the network admin for this site.', 'wphb' );
								if ( is_super_admin() ) :
									$disabled_label = __( 'This can’t be improved at the subsite level.', 'wphb' );
								endif;
								echo esc_html( $disabled_label );
							else :
								echo esc_html( $rule_result->type );
							endif;
							?>
						</td><!-- end wphb-performance-report-item-type -->

						<?php if ( 'disabled' !== $class ) : ?>
							<td class="wphb-performance-report-item-cta">
								<?php if ( ! empty( $rule_result->summary ) || ! empty( $rule_result->tip ) ) : ?>
									<?php if ( 100 !== $rule_result->impact_score && ( ! $is_subsite && 'server' !== $rule_result->type ) && ! $report_dismissed ) : ?>
										<button class="button button-ghost additional-content-opener"><?php esc_html_e( 'Improve', 'wphb' ); ?></button>
									<?php endif; ?>
									<span class="additional-content-opener trigger-additional-content"><i class="dev-icon dev-icon-caret_down"></i></span>
								<?php endif; ?>
							</td><!-- end wphb-performance-report-item-cta -->
						<?php endif; ?>

					</tr><!-- end wphb-performance-report-item -->

					<tr class="wphb-performance-report-item-additional-content wphb-table-additional-<?php echo esc_attr( $class ); ?>">
						<td colspan="4" class="wphb-performance-report-item-additional-content-inner">
							<div class="dev-box-performance-report-additional-content <?php echo $is_subsite ? 'disable-buttons' : ''; ?>">
								<?php if ( ! empty( $rule_result->summary ) ) : ?>
									<h4><?php esc_html_e( 'Overview', 'wphb' ); ?></h4>
									<p><?php echo $rule_result->summary; ?></p>
								<?php endif; ?>

								<?php if ( $has_url_blocks ) : ?>
									<h4><?php esc_html_e( 'Benchmarks', 'wphb' ); ?></h4>

									<ol>
										<?php foreach ( $rule_result->urlblocks as $url_block ) :
											if ( empty( $url_block ) ) {
												continue;
											} ?>

											<p><?php echo $url_block->header; ?></p>

											<?php if ( ! empty( $url_block->urls ) && is_array( $url_block->urls ) ) :
												foreach ( $url_block->urls as $url ) : ?>
													<li><?php echo make_clickable( $url ); ?></li>
												<?php endforeach;
											endif;
										endforeach; ?>
									</ol>
								<?php endif; ?>

								<?php if ( ! empty( $rule_result->tip ) ) : ?>
									<h4><?php esc_html_e( 'How to improve', 'wphb' ); ?></h4>
									<?php echo wpautop( $rule_result->tip ); ?>
								<?php endif; ?>
							</div>
						</td>
					</tr><!-- end wphb-performance-report-item-additional-content -->
				<?php endforeach; ?>

			</tbody>

		</table><!-- end list-table-performance-report -->
	</div>

<?php endif; ?>

<?php WP_Hummingbird_Utils::get_modal( 'dismiss-report' ); ?>