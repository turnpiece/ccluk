<div class="subhead">
    <span class="postauthortop author vcard">
    <i class="icon-user"></i> <?php echo __('by', 'virtue');?>  <span itemprop="author"><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>" class="fn" rel="author"><?php echo get_the_author() ?></a></span> |</span>
    <?php $post_category = get_the_category($post->ID); if ( $post_category==true ) { ?>  
    <span class="postedintop"><i class="icon-folder-open"></i> <?php _e('posted in:', 'virtue'); ?> <?php the_category(', ') ?></span> <?php }?>
    <span class="kad-hidepostedin">|</span>
    <span class="postcommentscount">
    <i class="icon-comments-alt"></i> <?php comments_number( '0', '1', '%' ); ?>
    </span>
</div>