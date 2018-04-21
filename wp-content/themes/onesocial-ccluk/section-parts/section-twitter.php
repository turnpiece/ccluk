<?php if (function_exists('ctf_init')) : ?>
<section id="twitter_feed" class="section twitter site-content">
	<div class="section-title">
        <h4><?php _e( "Tweets", 'onesocial' ) ?></h4>
    </div>
    <?php echo do_shortcode('[custom-twitter-feeds]') ?>
</section>
<?php endif; ?>