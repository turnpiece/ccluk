<?php
/*
Template Name: Feature
*/
?>
	<?php global $post; 
			$headoption = get_post_meta( $post->ID, '_kad_page_head', true ); 
				if ($headoption == 'flex') {
					get_template_part('templates/flex', 'slider');
				} else if ($headoption == 'carousel') {
					get_template_part('templates/carousel', 'slider');
				} else if ($headoption == 'rev') {
					get_template_part('templates/rev', 'slider');
				} else if ($headoption == 'video') { ?>
					<section class="postfeat container">
					 	<?php $swidth = get_post_meta( $post->ID, '_kad_posthead_width', true ); 
					 		if (!empty($swidth)) {
					 			$slidewidth = $swidth;
					 		} else {
					 			$slidewidth = 1140;
					 		} ?>
				        	<div class="videofit" style="max-width:<?php echo esc_attr($slidewidth);?>px; margin-left: auto; margin-right:auto;">
				              <?php echo get_post_meta( $post->ID, '_kad_post_video', true ); ?>
				          	</div>
				    </section>
				<?php } else if ($headoption == 'image') {
                		$height 	 = get_post_meta( $post->ID, '_kad_posthead_height', true );
                        $swidth 	 = get_post_meta( $post->ID, '_kad_posthead_width', true );  
                        $uselightbox = get_post_meta( $post->ID, '_kad_feature_img_lightbox', true );
                        if (!empty($height)) {
                        	$slideheight = $height;
                        } else {
                        	$slideheight = 400;
                        }
                        if (!empty($swidth)) {
                        	$slidewidth = $swidth;
                        } else {
                        	$slidewidth = 1140;
                        }
                        if (!empty($uselightbox)) {
                        	$lightbox = $uselightbox;
                        } else {
                        	$lightbox = 'yes';
                        }     
                    	$thumb 		 = get_post_thumbnail_id();
                    	$img_url  	 = wp_get_attachment_url( $thumb,'full' ); 
                    	$image       = aq_resize( $img_url, $slidewidth, $slideheight, true ); //resize & crop the image
                    	if(empty($image)) {
                    	 	$image = $img_url;
                    	} ?>
		                    <div class="postfeat container">
		                    	<div class="imghoverclass img-margin-center">
		                      	<?php if($lightbox == 'yes') { ?>
		                      		<a href="<?php echo esc_url($img_url); ?>" data-rel="lightbox" class="lightboxhover">
		                      	<?php } ?>
		                      		<img src="<?php echo esc_url($image); ?>" alt="<?php the_title(); ?>" />
		                      	<?php if($lightbox == 'yes') {?>
		                      		</a>
		                      	<?php } ?>
		                      	</div>
		                    </div>
                <?php } ?>

	<div id="pageheader" class="titleclass">
		<div class="container">
			<?php get_template_part('templates/page', 'header'); ?>
		</div><!--container-->
	</div><!--titleclass-->
	
    <div id="content" class="container">
   		<div class="row">
     		<div class="main <?php echo esc_attr(kadence_main_class()); ?>" role="main">
				<div class="entry-content" itemprop="mainContentOfPage">
					<?php get_template_part('templates/content', 'page'); ?>
				</div>
				<?php global $virtue; 
					if(isset($virtue['page_comments']) && $virtue['page_comments'] == '1') { 
						comments_template('/templates/comments.php');
					} ?>
			</div><!-- /.main -->