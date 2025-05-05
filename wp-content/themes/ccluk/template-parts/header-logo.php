<?php
/*
 * Logo Option
 */

$show		 = true;
$logo_id	 = 366;
$site_title	 = get_bloginfo( 'name' );
$logo		 = ( $show && $logo_id ) ? wp_get_attachment_image( $logo_id, 'medium', '', array( 'class' => 'boss-logo' ) ) : $site_title;

// This is for better SEO
$elem = ( is_front_page() && is_home() ) ? 'h1' : 'h2';
?>

<div id="logo-area">

	<<?php echo $elem; ?> class="site-title">

	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<?php echo $logo; ?>
	</a>

	</<?php echo $elem; ?>>

	<p class="site-description"><?php bloginfo( 'description' ); ?></p>

</div>