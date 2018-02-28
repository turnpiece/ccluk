<?php
$path = forminator_plugin_dir();
$icon_minus = $path . "assets/icons/admin-icons/minus.php";
$icon_polls = $path . "assets/icons/forminator-icons/polls.php";
?>

<div class="wpmudev-box wpmudev-can--hide">

    <div class="wpmudev-box-header">

        <div class="wpmudev-header--icon">

            <?php include( $icon_polls ); ?>

        </div>

        <div class="wpmudev-header--text">

            <h2 class="wpmudev-title"><?php _e( "Polls", Forminator::DOMAIN ); ?></h2>

        </div>

        <div class="wpmudev-header--action">

            <button class="wpmudev-box--action">

                <span class="wpmudev-icon--plus" aria-hidden="true"></span>

                <span class="wpmudev-sr-only"><?php _e( "Hide box", Forminator::DOMAIN ); ?></span>

            </button>

		</div>

    </div>

    <div class="wpmudev-box-section">

        <?php if ( forminator_polls_total() > 0 ) { ?>

            <div class="wpmudev-section--table">

                <table class="wpmudev-table wpmudev-can--edit">

                    <thead>

                        <tr>

                            <th><?php _e( "Name", Forminator::DOMAIN ); ?></th>

                            <td class="wpmudev-row--64"><?php _e( "Views", Forminator::DOMAIN ); ?></td>

                            <td class="wpmudev-row--92"><?php _e( "Votes", Forminator::DOMAIN ); ?></td>

                            <td class="wpmudev-row--58"><?php _e( "Rate", Forminator::DOMAIN ); ?></td>

                            <td></td>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach( forminator_polls_modules() as $module ) { ?>

                            <tr>

                                <th>

                                    <p class="wpmudev-table--text"><?php echo forminator_get_form_name( $module['id'], 'poll'); ?></p>

                                </th>

                                <td class="wpmudev-row--64">

                                    <p class="wpmudev-table--title"><?php _e( "Views:", Forminator::DOMAIN ); ?></p>

                                    <p class="wpmudev-table--text"><?php echo $module["views"]; ?></p>

                                </td>

                                <td class="wpmudev-row--92">

                                    <p class="wpmudev-table--title"><?php _e( "Votes:", Forminator::DOMAIN ); ?></p>

                                    <p class="wpmudev-table--text"><?php echo $module["entries"]; ?></p>

                                </td>

                                <td class="wpmudev-row--58">

                                    <p class="wpmudev-table--title"><?php _e( "Conv. Rate:", Forminator::DOMAIN ); ?></p>

                                    <p class="wpmudev-table--text"><?php echo forminator_get_rate( $module ); ?>%</p>

                                </td>

                                <td>

                                    <p class="wpmudev-table--text"><a href="<?php echo admin_url( 'admin.php?page=forminator-poll-wizard&id=' . $module['id'] ) ?>" class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost"><?php _e( "Edit", Forminator::DOMAIN ); ?></a></p>

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                    <tfoot>

                        <tr><td colspan="5">
                            <div class="wpmudev-table--buttons">
                                <button href="/" class="wpmudev-button wpmudev-button-sm wpmudev-button-blue wpmudev-open-modal" data-modal="polls"><?php _e( "New Poll", Forminator::DOMAIN ); ?></button>
                                <a href="<?php echo forminator_get_admin_link( 'forminator-poll' ); ?>" class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost"><?php _e( "View All", Forminator::DOMAIN ); ?></a>
                            </div>
                        </td></tr>

                </table>

            </div>

        <?php } else { ?>

            <div class="wpmudev-section--text">

                <p><?php _e( "Create polls, and collect user data. Choose a visualization style that best suits your needs.", Forminator::DOMAIN ); ?></p>

                <p><button href="/" class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost wpmudev-open-modal" data-modal="polls"><?php _e( "Create Poll", Forminator::DOMAIN ); ?></button></p>

            </div>

        <?php } ?>

    </div>

</div>