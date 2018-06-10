<?php

$content_hide = false;
?>

<div id="wpmudev-dashboard-widget-shares" class="wpmudev-box wpmudev-box-close">

    <div class="wpmudev-box-head">

        <?php $this->render("general/icons/admin-icons/icon-shares" ); ?>

        <h2><?php _e("Social Shares", Opt_In::TEXT_DOMAIN); ?></h2>

        <div class="wpmudev-box-action"><?php $this->render("general/icons/icon-plus" ); ?></div>

    </div>

    <div class="wpmudev-box-body<?php if ($content_hide === true) { echo ' wpmudev-hidden'; } ?>">

        <?php if ( count($social_sharings) ) { ?>

            <?php if ( count($ss_share_stats_data) > 0 ) { ?>

                <table cellspacing="0" cellpadding="0" class="wpmudev-table wpmudev-table-comulative">

                    <thead>

                        <tr>

                            <th><?php _e("Page / Post", Opt_In::TEXT_DOMAIN); ?></th>

                            <th><?php _e("Comulative Shares", Opt_In::TEXT_DOMAIN); ?></th>

                        </tr>

                    </thead>

                    <tbody>

						<?php foreach( $ss_share_stats_data as $ss ) : ?>

							<tr>

								<td>

									<a target="_blank" href="<?php echo ( $ss->ID != 0 ) ? esc_url(get_permalink($ss->ID)) : esc_url(get_home_url()) ; ?>"><?php echo ( $ss->ID != 0 ) ? $ss->post_title : bloginfo('title'); ?></a></td><td><?php echo $ss->page_shares; ?>

								</td>

							</tr>

						<?php endforeach; ?>

                    </tbody>

                    <?php if ( $ss_total_share_stats > 5 ) { ?>

                        <tfoot>

                            <tr><td colspan="2"><a href="#" id="sshare_view_all_stats" class="wpmudev-button wpmudev-button-sm wpmudev-button-blue">View All Shares</a></td></tr>

                        </tfoot>

                    <?php } ?>

                </table>

            <?php } else { ?>

                <p><?php _e( "Nothing has been shared yet.", Opt_In::TEXT_DOMAIN ); ?></p>

            <?php } ?>

        <?php } else { ?>

            <p><?php _e("You don't have any social sharing modules set-up just yet.<br />
            Click the button below to setup social sharing.", Opt_In::TEXT_DOMAIN); ?></p>

            <p><a href="<?php echo admin_url( "admin.php?page=" . Hustle_Module_Admin::SOCIAL_SHARING_WIZARD_PAGE ); ?>" class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost"><?php _e("Setup Social Sharing", Opt_In::TEXT_DOMAIN); ?></a></p>

        <?php } ?>

    </div>

</div>