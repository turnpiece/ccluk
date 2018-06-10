<div id="wph-comulative-shares-modal" class="wpmudev-modal">

    <div class="wpmudev-modal-mask" aria-hidden="true"></div>

    <div class="wpmudev-box-modal">

        <div class="wpmudev-box-head">

            <h2><?php _e( "Social Shares Stats", Opt_In::TEXT_DOMAIN ); ?></h2>

            <?php $this->render("general/icons/icon-close" ); ?>

        </div>

        <div class="wpmudev-box-body">

            <table cellspacing="0" cellpadding="0" class="wpmudev-table<?php if ( $ss_total_share_stats > 5 ) { echo ' wpmudev-table-paginated'; } ?>">

                <thead>

                    <tr>

                        <th><?php _e("Page / Post", Opt_In::TEXT_DOMAIN); ?></th>

                        <th><?php _e("Comulative Shares", Opt_In::TEXT_DOMAIN); ?></th>

                    </tr>

                </thead>

                <tbody>

					<?php foreach( $ss_share_stats_data as $ss ) : ?>

						<tr>

							<td><a target="_blank" href="<?php echo ( $ss->ID != 0 ) ? esc_url(get_permalink($ss->ID)) : esc_url(get_home_url()) ; ?>"><?php echo ( $ss->ID != 0 ) ? $ss->post_title : bloginfo('title'); ?></a></td>

							<td><?php echo $ss->page_shares; ?></td>

						</tr>

					<?php endforeach; ?>

                </tbody>

                <?php if ( $ss_total_share_stats > 5 ) {
						$pages = (int) ($ss_total_share_stats / 5);
						if ( ($ss_total_share_stats % 5) ) {
							$pages++;
						}
						$first_page = 1;
						$last_page = $pages;
				?>

                    <tfoot>

                        <tr><td colspan="2">

                            <ul class="wpmudev-pagination" data-total="<?php echo $pages;?>" data-nonce="<?php echo wp_create_nonce('hustle_ss_stats_paged_data'); ?>">

								<li class="wpmudev-prev wph-sshare--prev_page" data-page="1"><span><?php $this->render("general/icons/icon-arrow" ); ?></span></li>

								<li class="wpmudev-number wpmudev-current wph-sshare--current_page" data-page="1"><span>1</span></li>

								<?php if( $pages > 1 ): ?>

								<li class="wpmudev-number wph-sshare--page_number" data-page="2"><a href="#">2</a></li>

								<li class="wpmudev-next wph-sshare--next_page" data-page="2"><a href="#"><?php $this->render("general/icons/icon-arrow" ); ?></a></li>

								<?php else: ?>

								<li class="wpmudev-next wph-sshare--next_page" data-page="2"><span><?php $this->render("general/icons/icon-arrow" ); ?></span></li>

								<?php endif; ?>

                            </ul>

                        </td></tr>

                    </tfoot>

                <?php } ?>

            </table>

        </div>

    </div>

</div>
<script id="wpmudev-hustle-sshare-stats-modal-tpl" type="text/template">

    <# _.each( ss_share_stats, function(ss, key){ #>

		<tr>

			<td><a target="_blank" href="{{ss.page_url}}">{{ss.page_title}}</a></td>
			<td>{{ss.page_shares}}</td>

		</tr>

	<# }); #>

</script>