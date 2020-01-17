<?php
/**
 * The table template.
 *
 * @var string $id      Table ID.
 * @var string $class   Class name.
 * @var string $caption Caption text.
 * @var array  $headers Header data.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

$args = [];

// Assign table role.
$args['role'] = 'table';

// Assign unique identifier.
if ( isset( $id ) && '' !== $id ) {
	$args['id'] = 'beehive-' . $id;
}

// Assign table main class.
$args['class'] = 'beehive-table';

// Assign table extra classes.
if ( isset( $class ) && '' !== $class ) {
	$args['class'] .= ' ' . esc_attr( $class );
}

// Assign a table label for screen readers.
if ( isset( $label ) && '' !== $label ) {
	$args['aria-label'] = $label;
}

// Assign a table description for screen readers.
if ( isset( $id ) && '' !== $id && isset( $caption ) && '' !== $caption ) {
	$args['aria-describedby'] = 'beehive-' . $id . '-caption';
}

foreach ( $args as $key => $value ) {
	$attrs[] = $key . '="' . $value . '"';
}

?>

<div <?php echo implode( ' ', wp_kses_post_deep( $attrs ) ); ?>>

	<?php if ( isset( $caption ) && '' !== $caption ) : ?>
		<div id="beehive-<?php echo esc_attr( $id ); ?>-caption" class="sui-screen-reader-text"><?php echo esc_html( $caption ); ?></div>
	<?php endif; ?>

	<?php if ( isset( $headers ) && '' !== $headers ) : ?>

		<div role="rowgroup" class="beehive-table-header">

			<div role="row">

				<?php foreach ( $headers as $th ) : ?>
					<span role="columnheader" data-col="<?php echo esc_attr( $th['col'] ); ?>"><?php echo esc_html( $th['title'] ); ?></span>
				<?php endforeach; ?>

			</div>

		</div>

	<?php endif; ?>

	<div role="rowgroup" class="beehive-table-body">

		<div role="row">

			<span role="cell" class="sui-screen-reader-text" data-empty="<?php esc_html_e( 'No information', 'ga_trans' ); ?>"><?php esc_html_e( 'Table content is being loaded.', 'ga_trans' ); ?></span>

		</div>

	</div>

</div>