     
<article class="list-item">
    <header class="entry-header">
        <a href="<?php bp_group_permalink() ?>"><?php bp_group_avatar( 'width=300&height=300' ) ?></a>
    
        <a href="<?php bp_group_permalink() ?>">
            <h2 class="entry-title"><?php bp_group_name() ?></h2>
        </a>
    </header>

    <div class="entry-content">

        <div class="item-meta"><span class="activity"><?php printf( __( 'active %s ago', 'buddypress' ), bp_get_group_last_active() ) ?></span></div>

        <?php bp_group_description_excerpt() ?>

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