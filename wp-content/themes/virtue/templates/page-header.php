<div class="page-header">
	<h1 class="entry-title" itemprop="name">
		<?php echo apply_filters('kadence_page_title', kadence_title() ); ?>
	</h1>
   	<?php global $post; 
  	if(is_page()) {
  		$bsub = get_post_meta( $post->ID, '_kad_subtitle', true );
  		if(!empty($bsub)){
  			echo '<p class="subtitle"> '.$bsub.' </p>';
  		} 
	} else if(is_category()) { 
   		echo '<p class="subtitle">'.category_description().' </p>';
   	} ?>
</div>