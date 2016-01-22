<div class="sliderclass kad-desktop-slider">
<div id="imageslider" class="container">
	<?php global $virtue;
		if(isset($virtue['slider_size_width'])) {
			$slidewidth = $virtue['slider_size_width'];
		} else {
			$slidewidth = 1140;
		} ?>
			<div class="videofit" style="max-width:<?php echo esc_attr($slidewidth);?>px; margin-left: auto; margin-right:auto;">
                <?php echo $virtue['video_embed'];?>
            </div>
</div><!--Container-->
</div><!--feat-->