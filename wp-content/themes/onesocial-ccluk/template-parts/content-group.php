     
<article>
    <header class="item-avatar">
        <a href="<?php bp_group_permalink() ?>"><?php bp_group_avatar( 'type=thumb&width=50&height=50' ) ?></a>
    </header>

    <div class="item entry-content">
        <div class="item-title"><a href="<?php bp_group_permalink() ?>"><?php bp_group_name() ?></a></div>
        <div class="item-meta"><span class="activity"><?php printf( __( 'active %s ago', 'buddypress' ), bp_get_group_last_active() ) ?></span></div>

        <div class="item-desc"><?php bp_group_description_excerpt() ?></div>

        <?php do_action( 'bp_directory_groups_item' ) ?>
    </div>

    <footer class="action">
        <?php bp_group_join_button() ?>

        <div class="meta">
            <?php bp_group_type() ?> / <?php bp_group_member_count() ?>
        </div>

        <?php do_action( 'bp_directory_groups_actions' ) ?>
    </footer>
</article>