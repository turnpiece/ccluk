<?php
$path = forminator_plugin_dir();

$total_forms = forminator_cforms_total();
$total_polls = forminator_polls_total();
$total_quizz = forminator_quizzes_total();

$total_modules = $total_forms + $total_polls + $total_quizz;

$last_submission = forminator_get_latest_entry_time( 'all' );
?>

<div class="sui-box sui-summary">

	<div class="sui-summary-image-space"></div>

	<div class="sui-summary-segment">

		<div class="sui-summary-details">

			<?php if ( $total_modules > 0 ) { ?>
				<span class="sui-summary-large"><?php echo esc_html( $total_modules ); ?></span>
			<?php } else { ?>
				<span class="sui-summary-large">0</span>
			<?php } ?>

			<span class="sui-summary-sub"><?php esc_html_e( "Active Modules", Forminator::DOMAIN ); ?></span>

			<?php if ( $total_modules > 0 ) { ?>
				<span class="sui-summary-detail"><strong><?php echo esc_html( $last_submission ); ?></strong></span>
			<?php } else { ?>
				<span class="sui-summary-detail"><strong><?php esc_html_e( "Never", Forminator::DOMAIN ); ?></strong></span>
			<?php } ?>

			<span class="sui-summary-sub"><?php esc_html_e( "Last Submission", Forminator::DOMAIN ); ?></span>

		</div>

	</div>

	<div class="sui-summary-segment">

		<ul class="sui-list">

			<li>
				<span class="sui-list-label"><?php esc_html_e( "Top Converting Form", Forminator::DOMAIN ); ?></span>
				<?php if ( $total_forms > 0 ) { ?>
					<span class="sui-list-detail"><?php echo forminator_top_converting_form(); // WPCS: XSS ok. ?></span>
				<?php } else { ?>
					<span class="sui-list-detail">&mdash;</span>
				<?php } ?>
			</li>

			<li>
				<span class="sui-list-label"><?php esc_html_e( "Most Shared Quiz", Forminator::DOMAIN ); ?></span>
				<?php if ( $total_quizz > 0 ) { ?>
					<span class="sui-list-detail"><?php echo forminator_most_shared_quiz(); // WPCS: XSS ok. ?></span>
				<?php } else { ?>
					<span class="sui-list-detail">&mdash;</span>
				<?php } ?>
			</li>

			<li>
				<span class="sui-list-label"><?php esc_html_e( "Most Popular Poll", Forminator::DOMAIN ); ?></span>
				<?php if ( $total_polls > 0 ) { ?>
					<span class="sui-list-detail"><?php echo forminator_most_popular_poll(); // WPCS: XSS ok. ?></span>
				<?php } else { ?>
					<span class="sui-list-detail">&mdash;</span>
				<?php } ?>
			</li>

		</ul>

	</div>

</div>