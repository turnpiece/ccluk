<?php 
	
	extract(cosmica_get_theme_var());   
?>
<div class="clearfix"></div>
	<div class="home-counter-container t-fixed-background"  style="background-image:url(<?php echo esc_url(COSMICA_URI.'/images/background/home-callout.jpg'); ?>);">
		<div class="home-hounter">
		<div class="home-bg-overlay"></div>
			<div class="home-counter-inner">			
				<div class="container">
					 <div class="home-counter-heading">
						<div class="counter-heading">
							<h3> <?php echo esc_html($cosmca_call_header_text); ?> </h3> 
						</div>
						<div class="counter-description">
							<span> <?php echo esc_html($cosmca_call_desc_text); ?> </span>
						</div>
					</div>			
					<div class="clearfix"></div>
					<div class="counter-content">
						<div class="counter-button-container">
							<a href="<?php echo esc_url($cosmca_call_bt1_link); ?>" class="button button-main button-success"><span><?php echo esc_html($cosmca_call_bt1_text); ?></span></a>
							<a href="<?php echo esc_url($cosmca_call_bt2_link); ?>" class="button button-main button-warning"><span><?php echo esc_html($cosmca_call_bt2_text); ?></span></a>
						</div>
					</div> 
				</div>
			</div>
	</div>
</div>
