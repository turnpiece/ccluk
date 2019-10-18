<?php
/**
 * Side nav template for sub site admin.
 *
 * @var array  $tabs    Available tabs.
 * @var string $current Current tab.
 * @var bool   $network Network flag.
 */

defined( 'WPINC' ) || die();

use Beehive\Core\Helpers\Template;

?>

<div class="sui-sidenav">
	<?php if ( ! empty( $tabs ) ) : ?>
        <ul class="sui-vertical-tabs sui-sidenav-hide-md">
			<?php foreach ( $tabs as $tab => $title ) : ?>
                <li class="sui-vertical-tab <?php echo $tab === $current ? 'current' : ''; ?>">
                    <a href="<?php echo Template::settings_page( $tab, $network ); ?>"><?php echo $title; ?></a>
                </li>
			<?php endforeach; ?>
        </ul>
        <div class="sui-sidenav-hide-lg">
            <select class="sui-mobile-nav" style="display: none;">
				<?php foreach ( $tabs as $tab => $title ) : ?>
                    <option value="<?php echo Template::settings_page( $tab, $network ); ?>" <?php selected( $tab === $current ); ?>><?php echo $title; ?></option>
				<?php endforeach; ?>
            </select>
        </div>
	<?php endif; ?>
</div>