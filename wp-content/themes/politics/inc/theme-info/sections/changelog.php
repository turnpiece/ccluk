<?php
/**
 * Welcome screen changelog template
 */
?>

<div id="changelog" class="politics-changelog panel">

	<div class="changelog-intro">

		<h3><?php _e( 'Version Update Details', 'politics' ); ?> </h3>
		<p><?php _e( 'Review Politics version details and release dates.', 'politics' ); ?></p>

	</div><!-- .changelog-intro -->

	<div class="content-section">

		<?php
		/**
		 * Display the changelog file from the theme
		 */
			echo wp_kses_post ( $this->politics_changlog() );
		?>

	</div><!-- .content-section -->


</div><!-- #changelog -->
