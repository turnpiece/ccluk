<section class="wpmudev-dashboard-section">

	<?php
	if ( forminator_total_forms() === 0 ) { ?>

		<?php $welcome_dismissed = (bool) get_option( "forminator_welcome_dismissed", false ); ?>

		<?php if ( ! $welcome_dismissed ) : ?>

		<?php $this->template( 'dashboard/widgets/widget-welcome' ); ?>

		<?php endif; ?>

	<?php } ?>

	<?php $this->template( 'dashboard/widgets/widget-resume' ); ?>

	<?php $this->template( 'dashboard/widgets/widget-cform' ); ?>

	<div class="sui-row">

		<div class="sui-col-md-6">

			<?php $this->template( 'dashboard/widgets/widget-poll' ); ?>

		</div>

		<div class="sui-col-md-6">

			<?php $this->template( 'dashboard/widgets/widget-quiz' ); ?>

		</div>

	</div>

</section>