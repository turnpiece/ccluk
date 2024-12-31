<?php

// display social links in footer
$social_links = array(
	'facebook' => 'https://www.facebook.com/CitizensClimateLobbyUK/',
	'youtube' => 'https://www.youtube.com/channel/UCKg3OsMPlMzXlE0sGxrwhkg',
	'instagram' => 'https://www.instagram.com/citizensclimatelobby/',
	'linkedin' => 'https://uk.linkedin.com/company/ccluk/'
);

if ( !empty( $social_links ) ) {
	?>

	<div id="footer-icons">

		<ul class="social-icons"><?php
			foreach ( $social_links as $key => $link ) {
				if ( !empty( $link ) ) {
					$href = ( $key == 'email' ) ? 'mailto:' . sanitize_email( $link ) : esc_url( $link );
					?>
					<li>
						<a class="link-<?php echo $key; ?>" title="<?php echo $key; ?>" href="<?php echo $href; ?>" target="_blank">
							<span></span>
						</a>
					</li>
					<?php
				}
			}
		?></ul>

	</div>

	<?php
}