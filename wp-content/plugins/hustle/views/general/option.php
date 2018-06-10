<?php
$type = strtolower( $type );
$type_class = 'optin_' .  $type . '_' . $type . ' ' . $type;
$for = ( isset($for) ) ? $for : '';
?>

<?php if( "label" === $type  ): // label ?>
    <label <?php Opt_In::render_attributes( isset( $attributes ) ? $attributes : array() ); ?> for="<?php echo esc_attr( $for ); ?>"><?php echo $value; ?> </label>

<?php elseif( "notice" === $type  ): // Type textarea ?>
	<label <?php Opt_In::render_attributes( isset( $attributes ) ? $attributes : array() ); ?> class="<?php echo isset( $class ) ? esc_attr( $class ) : ''; ?>"><span><?php echo $value; ?> </span></label>

<?php elseif( "small" === $type  ): // small label ?>
    <p><small <?php Opt_In::render_attributes( isset( $attributes ) ? $attributes : array() ); ?> for="<?php echo esc_attr( $for ); ?>"><?php echo $value; ?> </small></p>

<?php elseif( "notice" === $type  ): // Type textarea ?>
	<label <?php Opt_In::render_attributes( isset( $attributes ) ? $attributes : array() ); ?> class="<?php echo isset( $class ) ? esc_attr( $class ) : ''; ?>"><span><?php echo $value; ?> </span></label>

<?php elseif( "textarea" === $type  ): // Type textarea ?>
    <textarea <?php Opt_In::render_attributes( isset( $attributes ) ? $attributes : array() ); ?> name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" cols="30" rows="10"><?php echo esc_textarea( $value ? $value : $default ); ?></textarea>


<?php elseif( "select" === $type  ): // type select  ?>
    <select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ) ?>" <?php Opt_In::render_attributes( isset( $attributes ) ? $attributes : array() ); ?> class="<?php echo isset( $class ) ? esc_attr( $class ) : ''; ?>" >
        <?php foreach( $options as $option ):
			$option = (array) $option;
			$value 	= isset( $option['value'] ) ? $option['value'] : '';
            ?>
            <option <?php selected( $selected, $value ); ?> value="<?php echo esc_attr( $value ); ?>"><?php echo esc_attr( $option['label'] ); ?></option>
        <?php endforeach; ?>
    </select>


<?php elseif( "link" === $type ): // type select  ?>
    <a <?php Opt_In::render_attributes( isset( $attributes ) ? $attributes : array() ); ?> href="<?php echo esc_url( $href ) ?>" target="<?php echo isset( $target ) ? esc_attr( $target ) : '_self' ?>" class="<?php echo esc_attr( $type_class ); ?> <?php echo isset( $class ) ? esc_attr( $class ) : ''; ?>" id="<?php echo isset( $id ) ? esc_attr( $id ) : ''; ?>" ><?php echo $text ?></a>


<?php elseif( "wrapper" === $type ): // type wrapper  ?>
    <div <?php Opt_In::render_attributes( isset( $attributes ) ? $attributes : array() ); ?> id="<?php echo esc_attr( $id ) ?>" class="<?php echo esc_attr( $class ); ?>">
        <?php foreach( (array) $elements as $element ): ?>
            <?php
            $params = $element;
            if( isset( $apikey ) && $element['id'] == 'optin_api_key' ) $params['value'] = $apikey;
            Opt_In::static_render("general/option", $params);
            ?>
        <?php endforeach; ?>
    </div>


<?php elseif( "radios" === $type ): // Radios type ?>
    <?php
    $_selected = -1;

    if( isset( $default ) )
        $_selected = $default;

    if( isset( $selected ) )
        $_selected = $selected;

    echo '<div class="wpmudev-options-list">';

        foreach( $options as $option):
            $option = (array) $option;
            $id =  esc_attr( $id . "-" . str_replace( " ", "-", strtolower( $option['value'] ) ) );
            $label_before = isset( $label_before ) ? $label_before : false;
            ?>

            <div class="wpmudev-options-item">

                <div class="wpmudev-input_radio">

                    <input value="<?php echo esc_attr( $option['value']  ); ?>" type="radio" id="<?php echo $id; ?>" name="<?php echo esc_attr( $name ) ?>" <?php selected( $_selected, $option['value'] ) ?> <?php Opt_In::render_attributes( isset( $item_attributes ) ? $item_attributes :  array()  ); ?> >

                    <label for="<?php echo $id ?>" class="wpdui-fi wpdui-fi-check"></label>

                </div>

                <?php if( $label_before ): ?>
                    <label for="<?php echo $id ?>" class="wpmudev-helper"><?php echo $option['label']; ?></label>
                <?php endif; ?>

                <?php if( !$label_before ): ?>
                    <label for="<?php echo $id ?>" class="wpmudev-helper"><?php echo  $option['label']; ?></label>
                <?php endif; ?>

            </div>

        <?php endforeach;

    echo '</div>'; ?>

<?php elseif( "checkboxes" === $type ): // Radios type ?>
    <?php
    $_selected = -1;

    if( isset( $default ) )
        $_selected = $default;

    if( isset( $selected ) )
        $_selected = $selected;

    echo '<div class="wpmudev-options-list">';

        foreach ( $options as $option):
            $option = (array) $option;
            $id =  esc_attr( $id . "-" . str_replace( " ", "-", strtolower( $option['value'] ) ) );
            $label_before = isset( $label_before ) ? $label_before : false;
            $checked = is_array( $_selected ) ? in_array( $option['value'], $_selected ) ? checked(true, true, false) : "" : checked( $_selected, $option['value'], false );
            ?>

            <div class="wpmudev-options-item">

                <div class="wpmudev-input_checkbox">

                    <input value="<?php echo esc_attr( $option['value']  ); ?>" type="checkbox" id="<?php echo $id; ?>" name="<?php echo esc_attr( $name ) ?>" <?php echo $checked; ?> <?php Opt_In::render_attributes( isset( $item_attributes ) ? $item_attributes :  array()  ); ?> >

                    <label for="<?php echo $id ?>" class="wpdui-fi wpdui-fi-check"></label>

                </div>

                <?php if( $label_before ): ?>
                    <label for="<?php echo $id ?>" class="wpmudev-helper"><?php echo $option['label']; ?></label>
                <?php endif; ?>

                <?php if( !$label_before ): ?>
                    <label for="<?php echo $id ?>" class="wpmudev-helper"><?php echo  $option['label']; ?></label>
                <?php endif; ?>

            </div>

        <?php endforeach;

    echo '</div>'; ?>

<?php elseif( "ajax_button" === $type  ): // button that is not an input submit ?>
    <button class="<?php echo isset( $class ) ? esc_attr( $class ) : ''; ?>"><?php echo $value; ?></button>

<?php else: ?>
    <input <?php Opt_In::render_attributes( isset( $attributes ) ? $attributes : array() ); ?> type="<?php echo esc_attr( $type ); ?>" value="<?php echo esc_attr( $value ); ?>" class="<?php echo esc_attr( $type_class ); ?> <?php echo isset( $class ) ? esc_attr( $class ) : ''; ?>" name="<?php echo esc_attr( $name ); ?>" placeholder="<?php echo isset($placeholder ) ? esc_attr($placeholder)  : ''; ?>" id="<?php echo isset( $id ) ? esc_attr( $id ) : ''; ?>"  />
<?php endif?>