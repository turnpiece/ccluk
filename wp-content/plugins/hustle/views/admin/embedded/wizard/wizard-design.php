<?php $use_email_collection = ( ( !empty($content_data) ) ? (bool) $content_data->use_email_collection : false ); ?>

<script id="wpmudev-hustle-embedded-section-design-tpl" type="text/template">

<?php if ( $use_email_collection ) { ?>

	<?php $this->render( "admin/embedded/wizard/boxes/box-form_layout", array() ); ?>

	<?php $this->render( "admin/embedded/wizard/boxes/box-image_position", array() ); ?>

	<?php $this->render( "admin/embedded/wizard/boxes/box-image_fit", array() ); ?>

	<?php $this->render( "admin/embedded/wizard/boxes/box-palette", array() ); ?>

	<?php $this->render( "admin/embedded/wizard/boxes/box-shapes", array() ); ?>

<?php } else { ?>

	<?php $this->render( "admin/embedded/wizard/boxes/box-style", array() ); ?>

	<?php $this->render( "admin/embedded/wizard/boxes/box-image_position", array() ); ?>

	<?php $this->render( "admin/embedded/wizard/boxes/box-image_fit", array() ); ?>

	<?php $this->render( "admin/embedded/wizard/boxes/box-border", array() ); ?>

<?php } ?>

<?php $this->render( "admin/embedded/wizard/boxes/box-shadow", array() ); ?>

<?php $this->render( "admin/embedded/wizard/boxes/box-size", array() ); ?>

<?php $this->render( "admin/embedded/wizard/boxes/box-css", array() ); ?>

<?php $this->render( "admin/embedded/wizard/boxes/box-css_holder", array() ); ?>

</script>

<div id="wpmudev-hustle-box-section-design"></div>