<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\IP_Lockout\Component;

use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\IP_Lockout\Model\IP_Model;
use WP_Defender\Module\IP_Lockout\Model\Log_Model;

class Logs_Table extends \WP_List_Table {
	protected $from;
	protected $to;

	public function __construct( $args = array() ) {
		parent::__construct( array_merge( array(
			'plural'     => '',
			'autoescape' => false,
			'screen'     => 'lockout_logs'
		), $args ) );

		$date_format = 'm/d/Y';
		$this->from  = Http_Helper::retrieve_get( 'date_from', date( $date_format, strtotime( 'today midnight', strtotime( '-14 days', current_time( 'timestamp' ) ) ) ) );
		$this->to    = Http_Helper::retrieve_get( 'date_to', date( $date_format, current_time( 'timestamp' ) ) );
	}

	/**
	 * @return array
	 */
	function get_table_classes() {
		return array(
			'list-table',
			//'hover-effect',
			'logs',
			'sui-table',
			'sui-accordion'
		);
	}

	/**
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'reason' => esc_html__( 'Details', wp_defender()->domain ),
			'date'   => esc_html__( 'Time', wp_defender()->domain ),
			'action' => ''
		);

		return $columns;
	}

	protected function get_sortable_columns() {
		return array(
			//'reason' => array( 'log', true ),
//			'date' => array( 'date', true ),
//			'ip'   => array( 'ip', true ),
		);
	}

	function prepare_items() {
		$paged    = $this->get_pagenum();
		$per_page = 20;
		$offset   = ( $paged - 1 ) * $per_page;

		$params = array(
			'date' => array(
				'compare' => 'between',
				'from'    => strtotime( 'midnight', strtotime( $this->from ) ),
				'to'      => strtotime( 'tomorrow', strtotime( $this->to ) )
			)
		);

		if ( ( $filter = Http_Helper::retrieve_get( 'type', null ) ) != null ) {
			$params['type'] = $filter;
		}
		if ( ( $ip = Http_Helper::retrieve_get( 'ip_address', null ) ) != null ) {
			$params['ip'] = $ip;
		}

		$logs = Log_Model::findAll( $params,
			HTTP_Helper::retrieve_get( 'orderby', 'id' ),
			HTTP_Helper::retrieve_get( 'order', 'desc' ),
			$offset . ',' . $per_page
		);

		$cache      = WP_Helper::getArrayCache();
		$totalItems = $cache->get( Login_Protection_Api::COUNT_TOTAL, false );

		if ( $totalItems == false ) {
			$totalItems = Log_Model::count( $params );
			$cache->set( Login_Protection_Api::COUNT_TOTAL, $totalItems, 3600 );
		}

		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'total_pages' => ceil( $totalItems / $per_page ),
			'per_page'    => $per_page
		) );

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$this->items           = $logs;
	}

	/**
	 * @param Log_Model $log
	 *
	 * @return string
	 */
	public function column_action( Log_Model $log ) {
		return '<i class="dev-icon dev-icon-caret_down"></i>';
	}

	/**
	 * @param Log_Model $log
	 *
	 * @return string
	 */
	public function column_reason( Log_Model $log ) {
		$format = false;
		if ( $log->type == Log_Model::ERROR_404 ) {
			$format = true;
		}
		ob_start();
		?>
        <label class="sui-checkbox">
            <input type="checkbox" class="single-select" name="ids[]" value="<?php echo $log->id ?>"/>
            <span aria-hidden="true"></span>
        </label>
        <span class="badge <?php echo $log->type == 'auth_lock' || $log->type == '404_lockout' ? 'locked' : null ?>"><?php echo $log->type == 'auth_fail' || $log->type == 'auth_lock' ? 'login' : '404' ?></span>
		<?php
		echo wp_trim_words( $log->get_log_text( $format ), 20 );

		return ob_get_clean();
	}

	/**
	 * @param Log_Model $log
	 *
	 * @return string
	 */
	public function column_date( Log_Model $log ) {
		return $log->get_date();
	}

	/**
	 * @param Log_Model $log
	 *
	 * @return string
	 */
	public function column_ip( Log_Model $log ) {
		$ip = Utils::instance()->getUserIp();
		if ( $ip == $log->get_ip() ) {
			return '<span tooltip="' . esc_attr( $ip ) . '" class="badge">' . __( "You", wp_defender()->domain ) . '</span>';
		} else {
			return $log->get_ip();
		}
	}

	public function display() {
		$singular = $this->_args['singular'];

		$this->screen->render_screen_reader_content( 'heading_list' );
		?>
        <div class="lockout-logs-container">
			<?php $this->display_tablenav( 'top' ); ?>
			<?php if ( $this->_pagination_args['total_items'] > 0 ): ?>
                <div class="lockout-logs-inner">
                    <div class="lockout-logs-filter sui-pagination-filter">
                        <form method="post">
                            <div class="sui-row">
                                <div class="sui-col">
                                    <div class="sui-form-field">
                                        <label class="sui-label">
											<?php _e( "Lockout Type", wp_defender()->domain ) ?>
                                        </label>
                                        <select name="type">
                                            <option value=""><?php esc_html_e( "All", wp_defender()->domain ) ?></option>
                                            <option <?php selected( \WP_Defender\Module\IP_Lockout\Model\Log_Model::AUTH_FAIL, \Hammer\Helper\HTTP_Helper::retrieve_get( 'filter' ) ) ?>
                                                    value="<?php echo \WP_Defender\Module\IP_Lockout\Model\Log_Model::AUTH_FAIL ?>">
												<?php esc_html_e( "Failed login attempts", wp_defender()->domain ) ?></option>
                                            <option <?php selected( \WP_Defender\Module\IP_Lockout\Model\Log_Model::AUTH_LOCK, \Hammer\Helper\HTTP_Helper::retrieve_get( 'filter' ) ) ?>
                                                    value="<?php echo \WP_Defender\Module\IP_Lockout\Model\Log_Model::AUTH_LOCK ?>"><?php esc_html_e( "Login lockout", wp_defender()->domain ) ?></option>
                                            <option <?php selected( \WP_Defender\Module\IP_Lockout\Model\Log_Model::ERROR_404, \Hammer\Helper\HTTP_Helper::retrieve_get( 'filter' ) ) ?>
                                                    value="<?php echo \WP_Defender\Module\IP_Lockout\Model\Log_Model::ERROR_404 ?>"><?php esc_html_e( "404 error", wp_defender()->domain ) ?></option>
                                            <option <?php selected( \WP_Defender\Module\IP_Lockout\Model\Log_Model::LOCKOUT_404, \Hammer\Helper\HTTP_Helper::retrieve_get( 'filter' ) ) ?>
                                                    value="<?php echo \WP_Defender\Module\IP_Lockout\Model\Log_Model::LOCKOUT_404 ?>"><?php esc_html_e( "404 lockout", wp_defender()->domain ) ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="sui-col">
                                    <div class="sui-form-field">
                                        <label class="sui-label">
											<?php _e( "IP Address", wp_defender()->domain ) ?>
                                        </label>
                                        <input name="ip_address" type="text" class="sui-form-control"
                                               placeholder="<?php esc_attr_e( "Enter an IP address", wp_defender()->domain ) ?>">
                                    </div>
                                </div>
                                <div class="sui-col"></div>
                            </div>
                            <hr/>
                            <div class="sui-row">
                                <div class="sui-col">
                                    <button type="button" class="sui-button sui-button-ghost">
										<?php _e( "Clear Filters", wp_defender()->domain ) ?>
                                    </button>
                                </div>
                                <div class="sui-col">
                                    <button type="submit" class="sui-button float-r">
                                        <i class="sui-icon-check" aria-hidden="true"></i>
										<?php _e( "Apply", wp_defender()->domain ) ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="sui-row sui-flushed">
                        <table id="iplockout-table"
                               class="<?php echo implode( ' ', $this->get_table_classes() ); ?>">
                            <thead>
                            <tr>
								<?php $this->print_column_headers(); ?>
                            </tr>
                            </thead>

                            <tbody id="the-list"<?php
							if ( $singular ) {
								echo " data-wp-lists='list:$singular'";
							} ?>>
							<?php $this->display_rows_or_placeholder(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
				<?php
				$this->display_tablenav( 'bottom' );
				?>
			<?php else: ?>
                <div class="sui-row sui-flushed">
                    <table class="sui-table no-border margin-bottom-20">
                        <tr>
                            <td>
                                <div class="sui-notice">
                                    <p>
										<?php _e( "No lockout events have been logged within the selected time period.", wp_defender()->domain ) ?>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
			<?php endif; ?>
        </div>
		<?php
	}

	/**
	 * @param object $item
	 */
	public function single_row( $item ) {
		$class = 'sui-accordion-item sui-warning ';
		if ( in_array( $item->type, array(
			Log_Model::AUTH_LOCK,
			Log_Model::AUTH_FAIL
		) ) ) {
			$class .= 'log-login';
		} elseif ( in_array( $item->type, array(
			Log_Model::ERROR_404,
			Log_Model::ERROR_404_IGNORE,
			Log_Model::LOCKOUT_404
		) ) ) {
			$class .= 'log-404';
		}

		if ( in_array( $item->type, array(
			Log_Model::LOCKOUT_404,
			Log_Model::AUTH_LOCK
		) ) ) {
			$class .= ' lockout';
		}
		$class .= ' ';
		echo '<tr class="' . $class . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
		echo '<tr class="sui-accordion-item-content">';
		echo $this->detailRow( $item );
		echo '<tr>';
	}

	/**
	 * Generates the columns for a single row of the table
	 *
	 * @since 3.1.0
	 *
	 * @param object $item The current item
	 */
	protected function single_row_columns( $item ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "$column_name column-$column_name";
			if ( $primary === $column_name ) {
				$classes .= ' sui-table-item-title';
			}

			if ( in_array( $column_name, $hidden ) ) {
				$classes .= ' hidden';
			}

			// Comments column uses HTML in the display name with screen reader text.
			// Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
			$data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';

			$attributes = "class='$classes' $data";

			if ( 'cb' === $column_name ) {
				echo '<th scope="row" class="check-column">';
				echo $this->column_cb( $item );
				echo '</th>';
			} elseif ( method_exists( $this, '_column_' . $column_name ) ) {
				echo call_user_func(
					array( $this, '_column_' . $column_name ),
					$item,
					$classes,
					$data,
					$primary
				);
			} elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo "<td $attributes>";
				echo call_user_func( array( $this, 'column_' . $column_name ), $item );
				echo "</td>";
			} else {
				echo "<td $attributes>";
				echo $this->column_default( $item, $column_name );
				echo "</td>";
			}
		}
	}

	public function detailRow( $item ) {

		?>
        <td colspan="<?php echo count( $this->get_columns() ) ?>">
            <div class="sui-box">
                <div class="sui-box-body">
                    <div class="sui-row">
                        <div class="sui-col">
                            <p><strong><?php _e( "Description", wp_defender()->domain ) ?></strong></p>
                            <p><?php
								echo $item->get_log_text();
								?></p>
                        </div>
                        <div class="sui-col">
                            <p><strong><?php _e( "Type", wp_defender()->domain ) ?></strong></p>
                            <p>
                                <a href=""><?php echo in_array( $item->type, array(
										Log_Model::ERROR_404,
										Log_Model::ERROR_404_IGNORE,
										Log_Model::LOCKOUT_404
									) ) ? __( "404 error", wp_defender()->domain ) : __( "Login failed", wp_defender()->domain ) ?></a>
                            </p>
                        </div>
                    </div>
                    <div class="sui-row">
                        <div class="sui-col">
                            <p><strong><?php _e( "IP", wp_defender()->domain ) ?></strong></p>
                            <p><a href=""><?php
									echo $item->ip
									?></a></p>
                        </div>
                        <div class="sui-col">
                            <p><strong><?php _e( "Date/Time", wp_defender()->domain ) ?></strong></p>
                            <p><?php
								echo Utils::instance()->formatDateTime( $item->date )
								?></p>
                        </div>
                        <div class="sui-col">
                            <p><strong><?php _e( "Ban Status", wp_defender()->domain ) ?></strong></p>
                            <p><?php
								echo Login_Protection_Api::getIPStatusText( $item->ip )
								?></p>
                        </div>
                    </div>
                    <div class="sui-border-frame">
                        <div>
							<?php
							echo Login_Protection_Api::getLogsActionsText( $item );
							?>
                        </div>
                        <p>
							<?php _e( "Note: Make sure this IP is not a legitimate operation, banning the IP will result in being permanently locked out from accessing your website.", wp_defender()->domain ) ?>
                        </p>
                    </div>
                </div>
            </div>
        </td>
		<?php
	}

	protected function display_tablenav( $which ) {
		?>
        <div class="sui-row">
            <div class="sui-col-md-5">
				<?php if ( $which == 'top' ): ?>
                    <small class="font-heavy"><?php _e( "Date range", wp_defender()->domain ) ?></small>
                    <div class="sui-date">
                        <i class="sui-icon-calendar" aria-hidden="true"></i>
                        <input name="date_from" id="wd_range_from" type="text"
                               class="sui-form-control filterable"
                               value="<?php echo esc_attr( $this->from . ' - ' . $this->to ) ?>">
                    </div>
				<?php endif; ?>
            </div>
            <div class="sui-col">
                <div class="sui-pagination-wrap">
                    <span class="sui-pagination-results">
                        <?php printf( __( "%s results", wp_defender()->domain ), $this->_pagination_args['total_items'] ) ?>
                    </span>
                    <ul class="sui-pagination">
						<?php $this->pagination( 'top' ) ?>
                    </ul>
                    <button rel="show-filter" data-target=".lockout-logs-filter"
                            class="sui-button-icon sui-button-outlined sui-pagination-open-filter">
                        <i class="sui-icon-filter" aria-hidden="true"></i>
                        <span class="sui-screen-reader-text">Open search filters</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="sui-row">
            <div class="sui-col">
                <form id="bulk-action" class="ip-frm" method="post">
                    <div class="bulk-action-bar">
                        <label class="sui-checkbox apply-all">
                            <input type="checkbox" id="apply-all"/>
                            <span aria-hidden="true"></span>
                        </label>
                        <select name="type" class="sui-select-sm">
                            <option value=""><?php _e( "Bulk action", wp_defender()->domain ) ?></option>
                            <option value="ban"><?php _e( "Ban", wp_defender()->domain ) ?></option>
                            <option value="whitelist"><?php _e( "Whitelist", wp_defender()->domain ) ?></option>
                            <option value="delete"><?php _e( "Delete", wp_defender()->domain ) ?></option>
                        </select>
                        <input type="hidden" name="ids" class="ids"/>
                        <input type="hidden" name="action" value="bulkAction"/>
						<?php wp_nonce_field( 'bulkAction' ) ?>
                        <button type="submit" class="sui-button">
							<?php _e( "Bulk Update", wp_defender()->domain ) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
		<?php
	}

	/**
	 * @param string $which
	 */
	protected function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items = $this->_pagination_args['total_items'];
		$total_pages = $this->_pagination_args['total_pages'];

		if ( $total_items == 0 ) {
			return;
		}

		if ( $total_pages < 2 ) {
			return;
		}

		$links        = array();
		$current_page = $this->get_pagenum();
		/**
		 * if pages less than 7, display all
		 * if larger than 7 we will get 3 previous page of current, current, and .., and, and previous, next, first, last links
		 */
		$current_url = set_url_scheme( 'http://' . parse_url( get_site_url(), PHP_URL_HOST ) . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );
		$current_url = esc_url( $current_url );

		$radius = 2;
		if ( $current_page > 1 && $total_pages > $radius ) {
			$links['prev'] = sprintf( '<li><a  data-paged="%s" class="lockout-nav " href="%s">%s</a></li>', $current_page - 1,
				add_query_arg( 'paged', $current_page - 1, $current_url ), '<i class="sui-icon-chevron-left" aria-hidden="true"></i>' );
		}

		for ( $i = 1; $i <= $total_pages; $i ++ ) {
			if ( ( $i >= 1 && $i <= $radius ) || ( $i > $current_page - 2 && $i < $current_page + 2 ) || ( $i <= $total_pages && $i > $total_pages - $radius ) ) {
				if ( $i == $current_page ) {
					$links[ $i ] = sprintf( '<li><a class="lockout-nav" href="#" data-paged="%s" disabled="">%s</a></li>', $i, $i );
				} else {
					$links[ $i ] = sprintf( '<li><a class="lockout-nav" data-paged="%s" href="%s">%s</a></li>', $i,
						add_query_arg( 'paged', $i, $current_url ), $i );
				}
			} elseif ( $i == $current_page - $radius || $i == $current_page + $radius ) {
				$links[ $i ] = '<li><a class="lockout-nav " href="#" disabled="">...</a></li>';
			}
		}

		if ( $current_page < $total_pages && $total_pages > $radius ) {
			$links['next'] = sprintf( '<li><a class="lockout-nav " data-paged="%s" href="%s">%s</a></li>', $current_page + 1,
				add_query_arg( 'paged', $current_page + 1, $current_url ), '<i class="sui-icon-chevron-right" aria-hidden="true"></i>' );
		}
		$output            = join( "\n", $links );
		$this->_pagination = $output;

		echo $this->_pagination;
	}

	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		$current_url = network_admin_url( 'admin.php?page=wdf-ip-lockout&view=logs' );

		if ( isset( $_GET['orderby'] ) ) {
			$current_orderby = $_GET['orderby'];
		} else {
			$current_orderby = '';
		}

		if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
			                 . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter ++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			if ( in_array( $column_key, $hidden ) ) {
				$class[] = 'hidden';
			}

			if ( 'cb' === $column_key ) {
				$class[] = 'check-column';
			} elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) ) {
				$class[] = 'num';
			}

			if ( $column_key === $primary ) {
				$class[] = 'column-primary';
			}

			if ( isset( $sortable[ $column_key ] ) ) {
				list( $orderby, $desc_first ) = $sortable[ $column_key ];

				if ( $current_orderby === $orderby ) {
					$order   = 'asc' === $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order   = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id    = $with_id ? "id='$column_key'" : '';

			if ( ! empty( $class ) ) {
				$class = "class='" . join( ' ', $class ) . "'";
			}

			echo "<$tag $scope $id $class>$column_display_name</$tag>";
		}
	}
}
