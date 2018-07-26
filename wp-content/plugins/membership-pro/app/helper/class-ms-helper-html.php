<?php
/**
 * Helper class for rendering HTML components.
 *
 * Methods for creating form INPUT components.
 * Method for creating vertical tabbed navigation.
 *
 * @todo Create add methods to parent class or remove 'extends MS_Helper' to use standalone.
 *
 * @since  1.0.0
 *
 * @return object
 */
class MS_Helper_Html extends MS_Helper {

	/* Constants for default HTML input elements. */
	const INPUT_TYPE_HIDDEN 		= 'hidden';
	const INPUT_TYPE_TEXT_AREA 		= 'textarea';
	const INPUT_TYPE_SELECT 		= 'select';
	const INPUT_TYPE_RADIO 			= 'radio';
	const INPUT_TYPE_SUBMIT 		= 'submit';
	const INPUT_TYPE_BUTTON 		= 'button';
	const INPUT_TYPE_CHECKBOX 		= 'checkbox';
	const INPUT_TYPE_IMAGE 			= 'image';
	// Different input types
	const INPUT_TYPE_TEXT 			= 'text';
	const INPUT_TYPE_PASSWORD 		= 'password';
	const INPUT_TYPE_NUMBER 		= 'number';
	const INPUT_TYPE_EMAIL 			= 'email';
	const INPUT_TYPE_URL 			= 'url';
	const INPUT_TYPE_TIME 			= 'time';
	const INPUT_TYPE_SEARCH 		= 'search';
	const INPUT_TYPE_FILE 			= 'file';

	/* Constants for advanced HTML input elements. */
	const INPUT_TYPE_WP_EDITOR 		= 'wp_editor';
	const INPUT_TYPE_DATEPICKER 	= 'datepicker';
	const INPUT_TYPE_RADIO_SLIDER 	= 'radio_slider';
	const INPUT_TYPE_TAG_SELECT 	= 'tag_select';
	const INPUT_TYPE_WP_PAGES 		= 'wp_pages';

	/* Constants for default HTML elements. */
	const TYPE_HTML_LINK 			= 'html_link';
	const TYPE_HTML_SEPARATOR 		= 'html_separator';
	const TYPE_HTML_TEXT 			= 'html_text';
	const TYPE_HTML_TABLE 			= 'html_table';

	/**
	 * Method for creating HTML elements/fields.
	 *
	 * Pass in array with field arguments. See $defaults for argmuments.
	 * Use constants to specify field type. e.g. MS_Helper_Html::INPUT_TYPE_TEXT
	 *
	 * @since  1.0.0
	 *
	 * @return void|string If $return param is false the HTML will be echo'ed,
	 *           otherwise returned as string
	 */
	public static function html_element( $field_args, $return = false ) {
		return mslib3()->html->element( $field_args, $return );
	}


	/**
	 * Echo the header part of a settings form, including the title and
	 * description.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $args Title, description and breadcrumb infos.
	 */
	public static function settings_header( $args = null ) {
		$defaults = array(
			'title' 			=> '',
			'title_icon_class' 	=> '',
			'desc' 				=> '',
			'bread_crumbs' 		=> null,
		);
		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'ms_helper_html_settings_header_args', $args );
		extract( $args );

		if ( ! is_array( $desc ) ) {
			$desc = array( $desc );
		}

		MS_Helper_Html::bread_crumbs( $bread_crumbs );
		?>
		<h2 class="ms-settings-title">
			<?php if ( ! empty( $title_icon_class ) ) : ?>
				<i class="<?php echo esc_attr( $title_icon_class ); ?>"></i>
			<?php endif; ?>
			<?php echo $title; ?>
		</h2>
		<div class="ms-settings-desc-wrapper">
			<?php foreach ( $desc as $description ) : ?>
				<div class="ms-settings-desc ms-description">
					<?php printf( $description ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Echo the footer section of a settings form.
	 *
	 * @since  1.0.0
	 *
	 * @param  null|array $fields List of fields to display in the footer.
	 * @param  bool|array $submit_info What kind of submit button to add.
	 */
	public static function settings_footer( $fields = null, $submit_info = null ) {
		// Default Submit-Button is "Next >>"
		if ( true === $submit_info ) {
			$submit_info 		= array(
				'id' 		=> 'next',
				'value' 	=> __( 'Next', 'membership2' ),
				'action' 	=> 'next',
			);
		}

		if ( empty( $fields ) ) {
			$fields = array();
		}

		if ( $submit_info ) {
			$submit_fields = array(
				'next' 			=> array(
					'id' 	=> @$submit_info['id'],
					'type' 	=> MS_Helper_Html::INPUT_TYPE_SUBMIT,
					'value' => @$submit_info['value'],
				),
				'action' 		=> array(
					'id' 	=> 'action',
					'type' 	=> MS_Helper_Html::INPUT_TYPE_HIDDEN,
					'value' => @$submit_info['action'],
				),
				'_wpnonce' 		=> array(
					'id' 	=> '_wpnonce',
					'type' 	=> MS_Helper_Html::INPUT_TYPE_HIDDEN,
					'value' => wp_create_nonce( @$submit_info['action'] ),
				),
			);

			foreach ( $submit_fields as $key => $field ) {
				if ( ! isset( $fields[ $key ] ) ) {
					$fields[ $key ] = $field;
				}
			}
		}

		$args = array(
			'saving_text'	=> __( 'Saving changes...', 'membership2' ),
			'saved_text' 	=> __( 'All changes saved.', 'membership2' ),
			'error_text' 	=> __( 'Could not save changes.', 'membership2' ),
			'fields' 		=> $fields,
		);
		$args 				= apply_filters( 'ms_helper_html_settings_footer_args', $args );
		$fields 			= $args['fields'];
		unset( $args['fields'] );

		?>
			<div class="ms-settings-footer">
				<form method="post" action="">
					<?php
					foreach ( $fields as $field ) {
						MS_Helper_Html::html_element( $field );
					}
					self::save_text( $args );
					?>
				</form>
			</div>
		</div>
		<?php
	}

	public static function settings_tab_header( $args = null ) {
		$defaults = array(
			'title' => '',
			'desc' 	=> array(),
			'class' => '',
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'ms_helper_html_settings_header_args', $args );
		extract( $args );

		if ( ! is_array( $desc ) ) {
			$desc = array( $desc );
		}
		foreach ( $desc as $id => $text ) {
			if ( empty( $text ) ) { unset( $desc[$id] ); }
		}

		?>
		<div class="ms-settings-wrapper <?php echo esc_attr( $class ); ?>">
			<?php if ( ! empty( $title ) || ! empty( $desc ) ) : ?>
			<div class="ms-header">
				<?php if ( ! empty( $title ) ) : ?>
				<div class="ms-settings-tab-title">
					<h3><?php printf( $title ); ?></h3>
				</div>
				<?php endif; ?>

				<?php if ( ! empty( $desc ) ) : ?>
				<div class="ms-settings-description">
					<?php foreach ( $desc as $description ): ?>
						<div class="ms-description">
							<?php echo '' . $description; ?>
						</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<?php self::html_separator(); ?>
			</div>
			<?php endif; ?>
		<?php
	}

	/**
	 * Echo a single content box including the header and footer of the box.
	 * The fields-list will be used to render the box body.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $fields_in List of fields to render
	 * @param  string $title Box title
	 * @param  string $description Description to display
	 * @param  string $state Toggle-state of the box: static/open/closed
	 */
	public static function settings_box( $fields_in, $title = '', $description = '', $state = 'static', $class = '' ) {
		// If its a fields array, great, if not, make a fields array.
		$fields = $fields_in;
		if ( ! is_array( $fields_in ) ) {
			$fields 	= array();
			$fields[] 	= $fields_in;
		}

		self::settings_box_header( $title, $description, $state, $class );
		foreach ( $fields as $field ) {
			MS_Helper_Html::html_element( $field );
		}
		self::save_text();
		self::settings_box_footer();
	}

	/**
	 * Echo the header of a content box. That box has a similar layout to a
	 * normal WordPress meta-box.
	 * The box has a title and description and can optionally be collapsible.
	 *
	 * @since  1.0.0
	 * @param  string $title Box title displayed in the top
	 * @param  string $description Description to display
	 * @param  string $state Toggle-state of the box: static/open/closed
	 */
	public static function settings_box_header( $title = '', $description = '', $state = 'static', $class = '' ) {
		do_action( 'ms_helper_settings_box_header_init', $title, $description, $state );

		$handle = '';
		if ( 'static' !== $state ) {
			$state 	= ('closed' === $state ? 'closed' : 'open');
			$handle = sprintf(
				'<div class="handlediv" title="%s"></div>',
				__( 'Click to toggle' ) // Intentionally no text-domain, so we use WordPress default translation.
			);
		}
		$box_class = $state;
		if ( ! strlen( $title ) && ! strlen( $description ) ) {
			$box_class .= ' nohead';
		}

		?>
		<div class="ms-settings-box-wrapper <?php echo esc_attr( $class ); ?>">
			<div class="ms-settings-box <?php echo esc_attr( $box_class ); ?>">
				<div class="ms-header">
					<?php printf( $handle ); ?>
					<?php if ( ! empty( $title ) ) : ?>
						<h3><?php printf( $title ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $description ) ) : ?>
						<span class="ms-settings-description ms-description"><?php echo '' . $description; ?></span>
					<?php endif; ?>
				</div>
				<div class="inside">
		<?php
		do_action( 'ms_helper_settings_box_header_end', $title, $description, $state );
	}

	/**
	 * Echo the footer of a content box.
	 *
	 * @since  1.0.0
	 */
	public static function settings_box_footer() {
		do_action( 'ms_helper_settings_box_footer_init' );
		?>
		</div> <!-- .inside -->
		</div> <!-- .ms-settings-box -->
		</div> <!-- .ms-settings-box-wrapper -->
		<?php
		do_action( 'ms_helper_settings_box_footer_end' );
	}

	/**
	 * Method for creating submit button.
	 *
	 * Pass in array with field arguments. See $defaults for argmuments.
	 *
	 * @since  1.0.0
	 *
	 * @return void But does output HTML.
	 */
	public static function html_submit( $args = array(), $return = false ) {
		$defaults = array(
			'id'        => 'submit',
			'value'     => __( 'Save Changes', 'membership2' ),
			'class'     => 'button button-primary',
		);
		wp_parse_args( $args, $defaults );

		$args['type'] = self::INPUT_TYPE_SUBMIT;

		if ( $return ) {
			return self::html_element( $args, true );
		} else {
			self::html_element( $args );
		}
	}

	/**
	 * Method for creating html link.
	 *
	 * Pass in array with link arguments. See $defaults for arguments.
	 *
	 * @since  1.0.0
	 *
	 * @return string But does output HTML.
	 */
	public static function html_link( $args = array(), $return = false ) {
		$defaults = array(
			'id'    => '',
			'title' => '',
			'value' => '',
			'class' => '',
			'url'   => '',
		);
		wp_parse_args( $args, $defaults );

		$args['type'] = self::TYPE_HTML_LINK;

		if ( $return ) {
			return self::html_element( $args, true );
		} else {
			self::html_element( $args );
		}
	}

	/**
	 * Method for outputting vertical tabs.
	 *
	 * Returns the active tab key. Vertical tabs need to be wrapped in additional code.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $tabs
	 * @param  string $active_tab
	 * @param  array $persistent
	 * @return string Active tab.
	 */
	public static function html_admin_vertical_tabs( $tabs, $active_tab = null, $persistent = array( 'edit' ) ) {
		reset( $tabs );
		$first_key = key( $tabs );

		// Setup navigation tabs.
		if ( empty( $active_tab ) ) {
			$active_tab = ! empty( $_GET['tab'] ) ? $_GET['tab'] : $first_key;
		}

		if ( ! array_key_exists( $active_tab, $tabs ) ) {
			$active_tab = $first_key;
		}

		// Render tabbed interface.
		?>
		<div class="ms-tab-container">
			<ul id="sortable-units" class="ms-tabs" style="">
				<?php foreach ( $tabs as $tab_name => $tab ) :
					$tab_class	= $tab_name == $active_tab ? 'active' : '';
					$title 		= esc_html( $tab['title'] );
					$url 		= $tab['url'];
					$attributes = array();

					foreach ( $persistent as $param ) {
						mslib3()->array->equip_request( $param );
						$value 	= $_REQUEST[ $param ];
						$url 	= esc_url_raw(
							add_query_arg( $param, $value, $url )
						);
					}

					$attributes[] = 'class="ms-tab-link"';
					$attributes[] = 'href="' . esc_url( $url ) .'"';
					if ( isset( $tab['target'] ) ) {
						$attributes[] = 'target="' . esc_attr( $tab['target'] ) .'"';
						if ( '_blank' == $tab['target'] ) {
							$title 	.= ' <i class="wpmui-fa wpmui-fa-external-link-square"></i>';
						}
					}
					?>
					<li class="ms-tab <?php echo esc_attr( $tab_class ); ?>">
						<a <?php echo implode( ' ', $attributes ); ?>>
							<?php echo $title; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php

		// Return current active tab.
		return $active_tab;
	}

	/**
	 * Method for outputting tooltips.
	 *
	 * @since  1.0.0
	 *
	 * @return string But does output HTML.
	 */
	public static function tooltip( $tip = '', $return = false ) {
		if ( empty( $tip ) ) {
			return;
		}

		if ( $return ) { ob_start(); }
		?>
		<div class="wpmui-tooltip-wrapper">
		<div class="wpmui-tooltip-info"><i class="wpmui-fa wpmui-fa-info-circle"></i></div>
		<div class="wpmui-tooltip">
			<div class="wpmui-tooltip-button">&times;</div>
			<div class="wpmui-tooltip-content">
			<?php printf( $tip ); ?>
			</div>
		</div>
		</div>
		<?php
		if ( $return ) { return ob_get_clean(); }
	}

	/**
	 * Echo HTML separator element.
	 * Vertical separators will be on the right side of the parent element.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $type Either 'horizontal' or 'vertical'
	 */
	public static function html_separator( $type = 'horizontal' ) {
		mslib3()->html->element(
			array(
				'type' 	=> self::TYPE_HTML_SEPARATOR,
				'value' => $type,
			)
		);
	}

	/**
	 * Echo HTML structure for save-text and animation.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $texts Optionally override the default save-texts.
	 * @param  bool $return If set to true the HTML code will be returned.
	 * @param  bool $animation If an animation should be displayed while saving.
	 */
	public static function save_text( $texts = array(), $animation = false, $return = false ) {
		$defaults = array(
			'saving_text' 	=> __( 'Saving changes...', 'membership2' ),
			'saved_text' 	=> __( 'All changes saved.', 'membership2' ),
			'error_text' 	=> __( 'Could not save changes.', 'membership2' ),
		);
		extract( wp_parse_args( $texts, $defaults ) );

		if ( $return ) {
			$command = 'sprintf';
		} else {
			$command = 'printf';
		}

		if ( $animation ) {
			$saving_text = '<div class="loading-animation"></div> ' . $saved_text;
		}

		return $command(
			'<span class="ms-save-text-wrapper">
				<span class="ms-saving-text">%1$s</span>
				<span class="ms-saved-text">%2$s</span>
				<span class="ms-error-text">%3$s<span class="err-code"></span></span>
			</span>',
			$saving_text,
			$saved_text,
			$error_text
		);
	}

	/**
	 * Used by the overview views to display a list of available content items.
	 * The items are typically formatted like a taglist via CSS.
	 *
	 * @since  1.0.0
	 *
	 * @param  WP_Post $item The item to display.
	 * @param  string $tag The tag will be wrapped inside this HTML tag.
	 */
	public static function content_tag( $item, $tag = 'li' ) {
		$label = property_exists( $item, 'post_title' ) ? $item->post_title : $item->name;

		if ( ! empty( $item->id ) && is_a( $item, 'WP_Post' ) ) {
			printf(
				'<%1$s class="ms-content-tag"><a href="%3$s">%2$s</a></%1$s>',
				esc_attr( $tag ),
				esc_html( $label ),
				get_edit_post_link( $item->id )
			);
		}
		else {
			printf(
				'<%1$s class="ms-content-tag"><span>%2$s</span></%1$s>',
				esc_attr( $tag ),
				esc_html( $label )
			);
		}
	}

	/**
	 * Return bread crumb navigation HTML code.
	 *
	 * @since  1.0.0
	 * @param  array $bread_crumbs
	 * @return string
	 */
	public static function bread_crumbs( $bread_crumbs ) {
		$crumbs = array();
		$html 	= '';

		if ( is_array( $bread_crumbs ) ) {
			foreach ( $bread_crumbs as $key => $bread_crumb ) {
				if ( ! empty( $bread_crumb['url'] ) ) {
					$crumbs[] = sprintf(
						'<span class="ms-bread-crumb-%s"><a href="%s">%s</a></span>',
						esc_attr( $key ),
						$bread_crumb['url'],
						$bread_crumb['title']
					);
				}
				elseif ( ! empty( $bread_crumb['title'] ) ) {
					$crumbs[] = sprintf(
						'<span class="ms-bread-crumb-%s">%s</span>',
						esc_attr( $key ),
						$bread_crumb['title']
					);
				}
			}

			if ( count( $crumbs ) > 0 ) {
				$html 	= '<div class="ms-bread-crumb">';
				$html 	.= implode( '<span class="ms-bread-crumb-sep"> &raquo; </span>', $crumbs );
				$html 	.= '</div>';
			}
		}
		$html = apply_filters( 'ms_helper_html_bread_crumbs', $html );

		printf( $html );
	}

	/**
	 * Return HTML code that displays a human readable Period representation.
	 *
	 * @since  1.0.0
	 * @param  array $period
	 * @param  string $class
	 * @return string
	 */
	public static function period_desc( $period, $class = '' ) {
		$html = sprintf(
			'<span class="ms-period-desc %s"> <span class="ms-period-unit">%s</span> <span class="ms-period-type">%s</span></span>',
			esc_attr( $class ),
			$period['period_unit'],
			$period['period_type']
		);

		return apply_filters( 'ms_helper_html_period_desc', $html );
	}

	/**
	 * Removes lines breaks and tralining/leading whitespace.
	 *
	 * Use this function:   $code = apply_filters( 'ms_compact_code', $code );
	 *
	 * Intention of this function is to make HTML code compatible with certain
	 * Themes that would add <br> tags at every newline, even when the newline
	 * was inside an HTML tag.
	 *
	 * e.g.             <div class="myclass"
	 *                  id="myid">
	 *
	 * was replaced by  <div class="myclass" <br>
	 *                  id="myid">
	 *
	 * @since  1.0.1.0
	 * @param  string $html HTML code.
	 * @return string Compressed HTML code.
	 */
	public static function compact_code( $html ) {
		$html 		= str_replace( array( "\r\n", "\r" ), "\n", $html );
		$lines 		= explode( "\n", $html );
		$new_lines 	= array();

		foreach ( $lines as $i => $line ) {
			$line = trim( $line );
			if ( ! empty( $line ) ) {
				$new_lines[] = $line;
			}
		}
		$html = implode( ' ', $new_lines );

		return $html;
	}

}