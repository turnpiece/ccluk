<?php
$path = forminator_plugin_dir();
$hero_happy = $path . "assets/icons/forminator-icons/hero-happy.php";
$hero_face = $path . "assets/icons/forminator-icons/hero-face.php";
?>

<div class="wpmudev-row">

	<div class="wpmudev-col col-12">

		<div id="orminator-dashboard-box--resume" class="wpmudev-box wpmudev-box--hero">

			<div class="wpmudev-box-section">

				<div class="wpmudev-hero--image" aria-hidden="true">

					<div class="wpmudev-image--wrap wpmudev-image--desktop"><?php include( $hero_happy ); ?></div>
					<div class="wpmudev-image--wrap wpmudev-image--mobile"><?php include( $hero_face ); ?></div>

				</div>

				<div class="wpmudev-hero--text">

                    <?php if ( forminator_total_forms() > 0 ) { ?>

                        <div class="wpmudev-text--resume">

                            <div class="wpmudev-text--message">

                                <div class="wpmudev-text--align">

                                    <h2 class="wpmudev-title"><?php _e( "Welcome back.", Forminator::DOMAIN ); ?></h2>

                                    <h3 class="wpmudev-subtitle"><?php _e( "Here’s some data about your Forms, Quizzes & Polls, that you might find useful.", Forminator::DOMAIN ); ?></h3>

                                </div>

                            </div>

                            <div class="wpmudev-text--table">

                                <table class="wpmudev-table" cellspacing="0" cellpadding="0">

                                    <tbody>

                                        <tr>

                                            <th><?php _e( "Top Converting Form", Forminator::DOMAIN ); ?></th>

                                            <?php if ( forminator_cforms_total() > 0 ) { ?>

                                                <td><?php echo forminator_top_converting_form(); ?></td>

                                            <?php } else { ?>

                                                <td>—</td>

                                            <?php } ?>

                                        </tr>

                                        <tr>

                                            <th><?php _e( "Most Shared Quiz", Forminator::DOMAIN ); ?></th>

                                            <?php if ( forminator_quizzes_total() > 0 ) { ?>

                                                <td><?php echo forminator_most_shared_quiz(); ?></td>

                                            <?php } else { ?>

                                                <td>—</td>

                                            <?php } ?>

                                        </tr>

                                        <tr>

                                            <th><?php _e( "Most Popular Poll", Forminator::DOMAIN ); ?></th>

                                            <?php if ( forminator_polls_total() > 0 ) { ?>

                                                <td><?php echo forminator_most_popular_poll(); ?></td>

                                            <?php } else { ?>

                                                <td>—</td>

                                            <?php } ?>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    <?php } else { ?>

                        <h2 class="wpmudev-title"><?php _e( "Welcome back.", Forminator::DOMAIN ); ?></h2>

                        <p><?php _e( "You don't have enough information to show modules data resume.", Forminator::DOMAIN ); ?></p>

                        <p><?php _e( "Come back later. Don't forget to track your modules.", Forminator::DOMAIN ); ?></p>

                    <?php } ?>

				</div>

			</div>

		</div><?php // .wpmudev-box ?>

	</div><?php // .wpmudev-col ?>

</div><?php // .wpmudev-row ?>