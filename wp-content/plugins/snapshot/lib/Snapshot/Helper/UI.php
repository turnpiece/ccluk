<?php // phpcs:ignore
/**
 * Snapshot utility class for creating common UI components.
 *
 * @since 2.5
 *
 * @package Snapshot
 * @subpackage Helper
 */

if ( ! class_exists( 'Snapshot_Helper_UI' ) ) {

	class Snapshot_Helper_UI {

		/**
		 * @param int $minute_value
		 */
		public static function form_show_minute_selector_options( $minute_value = 0 ) {
			$_minute = 0;

			while ( $_minute < 60 ) {
				?>
				<option value="<?php echo esc_attr( $_minute ); ?>"<?php if ( $_minute === $minute_value ) echo ' selected="selected" '; ?>><?php echo wp_kses_post( sprintf( "%02d", $_minute ) ); ?></option>
				<?php
				$_minute++;
			}
		}

		/**
		 * @param int $hour_value
		 */
		public static function form_show_hour_selector_options( $hour_value = 0 ) {

			$_hour = 0;

			while ( $_hour < 24 ) {

				if ( 0 === $_hour ) {
					$_hour_label = __( "Midnight", SNAPSHOT_I18N_DOMAIN );
				} else if ( 12 === $_hour ) {
					$_hour_label = __( "Noon", SNAPSHOT_I18N_DOMAIN );
				} else if ( $_hour < 13 ) {
					$_hour_label = $_hour . __( "am", SNAPSHOT_I18N_DOMAIN );
				} else {
					$_hour_label = ( $_hour - 12 ) . __( "pm", SNAPSHOT_I18N_DOMAIN );
				}

				?>
				<option value="<?php echo esc_attr( $_hour ); ?>"<?php if ( intval( $hour_value ) === $_hour ) echo ' selected="selected" '; ?>><?php echo esc_html( $_hour_label ); ?></option>
				<?php
				$_hour++;
			}
		}

		/**
		 * @param int $mday_value
		 */
		public static function form_show_mday_selector_options( $mday_value = 0 ) {

			$_dom = 1;

			while ( $_dom < 32 ) {
				?>
				<option value="<?php echo esc_attr( $_dom ); ?>"<?php if ( intval( $mday_value ) === $_dom ) echo ' selected="selected" '; ?>><?php echo esc_html( $_dom ); ?></option>
				<?php
				$_dom++;
			}
		}

		/**
		 * @param int $wday_value
		 */
		public static function form_show_wday_selector_options( $wday_value = 0 ) {

			$_dow = array(
				'0' => __( 'Sunday', SNAPSHOT_I18N_DOMAIN ),
				'1' => __( 'Monday', SNAPSHOT_I18N_DOMAIN ),
				'2' => __( 'Tuesday', SNAPSHOT_I18N_DOMAIN ),
				'3' => __( 'Wednesday', SNAPSHOT_I18N_DOMAIN ),
				'4' => __( 'Thursday', SNAPSHOT_I18N_DOMAIN ),
				'5' => __( 'Friday', SNAPSHOT_I18N_DOMAIN ),
				'6' => __( 'Saturday', SNAPSHOT_I18N_DOMAIN ),
			);

			foreach ( $_dow as $_key => $_label ) {
				?>
				<option value="<?php echo esc_attr( $_key ); ?>"<?php if ( intval( $_key ) === intval( $wday_value ) ) echo ' selected="selected" '; ?>><?php echo esc_html( $_label ); ?></option>
				<?php
			}
		}

		/**
		 * Utility function to display the AJAX information elements above the
		 * Add New and Restore forms.
		 *
		 * @since 1.0.2
		 */
		public static function form_ajax_panels() {
			?>
			<div id="snapshot-ajax-warning" class="updated fade" style="display:none"></div>
			<div id="snapshot-ajax-error" class="error snapshot-error" style="display:none"></div>
			<div id="snapshot-progress-bar-container" style="display: none" class="hide-if-no-js"></div>
		<?php
		}

		/**
		 * @param $all_destinations
		 * @param string $selected_destination
		 * @param string $destinationClasses
		 */
		public static function destination_select_options_groups( $all_destinations, $selected_destination = '', $destinationClasses = '' ) {
			if ( ( isset( $all_destinations ) ) && ( count( $all_destinations ) ) ) {

				$destinations = array();
				foreach ( $all_destinations as $key => $destination ) {
					$destination['key'] = $key;

					$type = $destination['type'];
					if ( ! isset( $destinations[ $type ] ) ) {
						$destinations[ $type ] = array();
					}

					$name = $destination['name'];

					$destinations[ $type ][ $name ] = $destination;
				}

				//echo "destinations<pre>"; print_r($destinations); echo "</pre>";
				//echo "destinationClasses<pre>"; print_r($destinationClasses); echo "</pre>";
				//die();
				foreach ( $destinations as $type => $destination_items ) {
					if ( ( 'local' === $type ) || ( isset( $destinationClasses[ $type ] ) ) ) {


						if ( 'local' === $type ) {
							$type_name = $type;
						} else {
							$destinationClass = $destinationClasses[ $type ];
							$type_name        = $destinationClass->name_display;
						}
						?>
						<optgroup label="<?php echo esc_attr( $type_name ); ?>">
						<?php
						foreach ( $destination_items as $key => $destination ) {
						?>
							<option class="<?php echo esc_attr( $type ); ?>" value="<?php echo esc_attr( $destination['key'] ); ?>"
							<?php
							if ( $selected_destination === $destination['key'] ) {
								echo ' selected="selected" ';
								global $snapshot_destination_selected_type;
								$snapshot_destination_selected_type = $type;
							}
							?>
							>
								<?php echo esc_html( stripslashes( $destination['name'] ) ); ?>
							</option>
						<?php
						}
						?>
						</optgroup>
						<?php
					}
				}
			}
		}

		/**
		 * @param $all_destinations
		 * @param string $selected_destination
		 * @param string $destinationClasses
		 */
		public static function destination_select_radio_boxes( $all_destinations, $selected_destination = '', $destinationClasses = '' )  {
			$i = 0;
			$destinations['row1'] = array();
			$destinations['row2'] = array();
			foreach ( $all_destinations as $key => $item ) {
				if(isset( $destinationClasses[$item['type']] )){
					$item["type_name_display"] = $destinationClasses[$item['type']]->name_display;
				} else {
					$item["type_name_display"] = "local";
					$item["type"] = "local";
				}
				$item["key"] = $key;
				if( 0 === $i % 2){
					$destinations['row1'][] = $item;
				} else {
					$destinations['row2'][] = $item;
				}
				$i++;
			}

			if(! isset( $selected_destination ) ){
				$selected_destination = "local";
			}
			?>
			<div class="wpmud-box-gray">

				<div class="radio-destination">

						<?php foreach ( $destinations['row1'] as $destination ) : ?>

						<div class="wps-input--item">

							<div class="wps-input--radio">

								<input data-destination-type="<?php echo esc_attr( $destination['type'] ); ?>" <?php echo ( $destination['key'] === $selected_destination ) ? "checked" : ""; ?> type="radio" name="snapshot-destination" id="snap-<?php echo esc_attr( $destination['key'] ); ?>" value="<?php echo esc_attr( $destination['key'] ); ?>" />

								<label for="snap-<?php echo esc_attr( $destination['key'] ); ?>"></label>

					    </div>

							<label for="snap-<?php echo esc_attr( $destination['key'] ); ?>"><span><?php echo esc_html( $destination['name'] ); ?></span><i class="wps-typecon <?php echo esc_attr( $destination['type'] ); ?>"></i></label>

					</div>

					<?php endforeach; ?>

						<?php foreach ( $destinations['row2'] as $destination ) : ?>

						<div class="wps-input--item">

							<div class="wps-input--radio">

								<input data-destination-type="<?php echo esc_attr( $destination['type'] ); ?>" <?php echo ( $destination['key'] === $selected_destination ) ? "checked" : ""; ?> type="radio" name="snapshot-destination" id="snap-<?php echo esc_attr( $destination['key'] ); ?>" value="<?php echo esc_attr( $destination['key'] ); ?>" />

								<label for="snap-<?php echo esc_attr( $destination['key'] ); ?>"></label>

						</div>

							<label for="snap-<?php echo esc_attr( $destination['key'] ); ?>"><span><?php echo esc_html( $destination['name'] ); ?></span><i class="wps-typecon <?php echo esc_attr( $destination['type'] ); ?>"></i></label>

					</div>

					<?php endforeach; ?>

				</div>
				<?php if( count( $all_destinations ) < 2 ) : ?>
				<div class="wps-notice"><p><?php echo wp_kses_post( sprintf( __( "You haven't added any third party destinations yet. It's much safer to store your snapshots off-site so we recommend you add <a href='%s'>another destination</a>.", SNAPSHOT_I18N_DOMAIN ), WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ) ); ?></p></div>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 *
		 */
		public static function show_panel_messages() {

			$session_save_path = session_save_path();
			//echo "session_save_path=[". $session_save_path ."]<br />";
			if ( ! file_exists( $session_save_path ) ) {
				WPMUDEVSnapshot::instance()->snapshot_admin_notices_proc( "error", sprintf( __( "<p>The session save path (%s) is not set to a valid directory. Check your PHP (php.ini) settings or contact your hosting provider.</p>", SNAPSHOT_I18N_DOMAIN ), $session_save_path ) );

			} else if ( ! is_writable( $session_save_path ) ) {
				WPMUDEVSnapshot::instance()->snapshot_admin_notices_proc( "error", sprintf( __( "<p>The session_save_path (%s) is not writeable. Check your PHP (php.ini) settings or contact your hosting provider.</p>", SNAPSHOT_I18N_DOMAIN ), $session_save_path ) );
			}
		}

		public static function table_pagination($total = 1, $echo = true){
			// We need to remove the message parameter in order to make the pagination work,
			// in case the user has deleted a snapshot
			add_filter( 'paginate_links', array( 'Snapshot_Helper_UI', 'remove_delete_message' ) );

			$big = 999999999; // need an unlikely integer
			if ( isset( $_GET['paged'] ) ){
				if ( ! isset( $_REQUEST['snapshot-pagination-nonce']  ) ) {
					return;
				}
				if ( ! wp_verify_nonce( $_REQUEST['snapshot-pagination-nonce'], 'snapshot-pagination-nonce' ) ) {
					return;
				}
			}
			$paged = ( !isset( $_GET['paged'] ) ) ? 1 : intval( $_GET['paged'] );
			$old_base = ( strpos( get_pagenum_link( $big, false ), '&message=') !== false ?
				remove_query_arg( 'message', get_pagenum_link( $big, false ) )
				:
				get_pagenum_link( $big )
			);

			$base = str_replace( '&#038;snapshot-pagination-nonce=' . wp_create_nonce( 'snapshot-pagination-nonce' ), '', str_replace( '&#038;paged=' . $big, '%_%', esc_url( $old_base ) ) );

			if ( isset( $_GET['destination'] ) && 'All Destinations' !== $_GET['destination'] )
				$base = str_replace( '&#038;destination=' . $_GET['destination'], '', $base);
			if ( isset( $_GET['destination'] ) && 'All Destinations' === $_GET['destination'] )
				$base = str_replace( '&#038;destination=All+Destinations', '', $base);

			$pages = paginate_links( array(
					'base' => $base,
					'format' => '&paged=%#%&snapshot-pagination-nonce=' . wp_create_nonce( 'snapshot-pagination-nonce' ),
					'current' => max( 1, $paged ),
					'total' => $total,
					'type'  => 'array',
					'prev_next'   => true,
					'prev_text'    => '',
					'next_text'    => '',
					'add_args' => false,
				)
			);

			if( is_array( $pages ) ) {
				$pagination = '';
				foreach ( $pages as $page ) {
					$pagination .= "<li class='pagination-number'>$page</li>";
				}

				if ( $echo ) {
					echo wp_kses( $pagination, array(
						'li' => array( 'class' => array() ),
						'a' => array( 'href' => array(),
							'class' => array()
						),
						'span' => array( 'aria-current' => array(),
							'class' => array()
						)
					) );
				} else {
					return $pagination;
				}
			}
		}

		public static function remove_delete_message( $link ) {
				return
				isset( $_REQUEST['message'] ) // phpcs:ignore
				? remove_query_arg( 'message', $link )
				: $link;
		}


	}
}