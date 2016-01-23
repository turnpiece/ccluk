<?php 
extract(cosmica_get_theme_var());
?>
<div class="clearfix"></div>
<div class="home-wrapper">
<div class="container">
	<div class="row">
		<div class="home-services-title">
		<h2><?php echo esc_html($cosmca_services_header_text); ?></h2>
		<div class="separator-solid"></div>
		</div>
		<div class="home-portfolio-desc">
			<span> <?php echo esc_html($cosmca_services_desc_text); ?> </span>
		</div>
	</div>
	<div class="row">
		<div class="home-service-con">
			<?php	cosmica_get_demo_services();  ?>
		</div>
	</div>
</div>
</div>