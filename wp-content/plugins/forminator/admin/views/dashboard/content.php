<section id="wpmudev-section" class="wpmudev-dashboard-section">

	<?php
	if ( forminator_total_forms() === 0 ) { ?>

		<?php $welcome_dismissed = (bool) get_option( "forminator_welcome_dismissed", false ); ?>

		<?php if( ! $welcome_dismissed ) : ?>

		<?php $this->template( 'dashboard/widgets/widget-welcome' ); ?>

		<?php endif; ?>

		<?php /*
		$this->template( 'dashboard/widgets/widget-create', array(
			'modules' => forminator_get_modules()
		) ); */ ?>

	<?php } else { ?>

		<?php $this->template( 'dashboard/widgets/widget-resume' ); ?>

	<?php } ?>

	<div class="wpmudev-row">

		<div class="wpmudev-col col-12">

			<?php $this->template( 'dashboard/widgets/widget-cform' ); ?>

		</div>

	</div>

	<div class="wpmudev-row">

		<div class="wpmudev-col col-12 col-lg-6">

			<?php $this->template( 'dashboard/widgets/widget-poll' ); ?>

		</div>

		<div class="wpmudev-col col-12 col-lg-6">

			<?php $this->template( 'dashboard/widgets/widget-quiz' ); ?>

		</div>

	</div>

</section>