<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Scan\Model\Result_Item;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Result_Table extends \WP_List_Table {
	public $type = Result_Item::STATUS_ISSUE;

	public function __construct( $args = array() ) {
		parent::__construct( array_merge( array(
			'plural'     => 'sui-table sui-accordion',
			'autoescape' => false,
			'screen'     => ''
		), $args ) );
	}

	/**
	 * @return array
	 */
	function get_columns() {
		switch ( $this->type ) {
			case Result_Item::STATUS_ISSUE:
			default:
				$columns = array(
					'col_file'   => esc_html__( 'Suspicious File', wp_defender()->domain ),
					'col_issue'  => esc_html__( 'Details', wp_defender()->domain ),
					'col_action' => '',
				);
				break;
			case Result_Item::STATUS_IGNORED:
				$columns = array(
					'col_file'           => esc_html__( 'Suspicious File', wp_defender()->domain ),
					'col_ignore_date'    => esc_html__( 'Date Ignored', wp_defender()->domain ),
					'col_ignored_action' => '',
				);
				break;
			case Result_Item::STATUS_FIXED:
				$columns = array(
					'col_file'       => esc_html__( 'File Name', wp_defender()->domain ),
					'col_fixed_date' => esc_html__( 'Date Cleaned', wp_defender()->domain ),
				);
				break;
		}

		return $columns;
	}

	/**
	 * @param Result_Item $item
	 *
	 * @return mixed
	 */
	public function column_col_ignore_date( Result_Item $item ) {
		//$time = get_date_from_gmt( $item->dateIgnored, 'Y-m-d H:i:s' );
		return $item->formatDateTime( $item->dateIgnored );
	}

	/**
	 * @param Result_Item $item
	 *
	 * @return mixed
	 */
	public function column_col_fixed_date( Result_Item $item ) {
		//convert to local
		//$time = get_date_from_gmt( $item->dateFixed, 'Y-m-d H:i:s' );

		return $item->formatDateTime( $item->dateFixed );
	}

	/**
	 * @param Result_Item $item
	 *
	 * @return string
	 */
	public function column_col_ignored_action( Result_Item $item ) {
		ob_start();
		?>
        <form method="post" class="ignore-restore scan-frm float-r">
            <input type="hidden" name="action" value="unIgnoreItem"/>
            <input type="hidden" name="id" value="<?php echo $item->id ?>"/>
			<?php wp_nonce_field( 'unIgnoreItem' ) ?>
            <button type="submit" data-tooltip="<?php esc_attr_e( "Restore File", wp_defender()->domain ) ?>"
                    class="sui-button-icon sui-tooltip sui-tooltip-top">
                <i class="sui-icon-update" aria-hidden="true"></i>
            </button>
        </form>
		<?php
		return ob_get_clean();
	}

	/**
	 * prepare logs data
	 */
	function prepare_items() {
		$model        = Scan_Api::getLastScan();
		$itemsPerPage = 40;

		$offset                = ( $this->get_pagenum() - 1 ) * $itemsPerPage;
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$issueType             = HTTP_Helper::retrieve_get( 'type', null );
		if ( ! in_array( $issueType, array(
			'core',
			'vuln',
			'content'
		) ) ) {
			$issueType = null;
		}

		$this->items = $model->getItems( $offset . ',' . $itemsPerPage, $this->type, $issueType );
		$totalItems = Result_Item::count( [
			'type'   => $issueType,
			'status' => $this->type
		] );

		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'total_pages' => ceil( $totalItems / $itemsPerPage ),
			'per_page'    => $itemsPerPage
		) );
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function column_col_bulk( $item ) {
		return '<label class="sui-checkbox"><input value="' . $item->id . '" type="checkbox" class="scan-chk"><span aria-hidden="true"></span></label>';
	}

	/**
	 * @param Result_Item $item
	 *
	 * @return string
	 */
	public function column_col_file( Result_Item $item ) {
		return $this->column_col_bulk( $item ) . '<span>' . $item->getTitle() . '</span>';
	}

	/**
	 * @param Result_Item $item
	 *
	 * @return mixed
	 */
	public function column_col_issue( Result_Item $item ) {
		return $item->getIssueSummary();
	}

	/**
	 * @param Result_Item $item
	 *
	 * @return string
	 */
	public function column_col_action( Result_Item $item ) {
		?>
        <span class="sui-accordion-open-indicator" aria-label="Expand">
            <i class="sui-icon-chevron-down" aria-hidden="true"></i>
        </span>
		<?php
	}

	/**
	 * Display the table
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display() {

		$this->display_tablenav( 'top' );
		?>
        <div class="sui-row sui-flushed">
            <table id="scan-result-table"
                   class="sui-table <?php echo $this->type == Result_Item::STATUS_ISSUE ? 'sui-accordion' : null ?>">
                <thead>
                <tr>
					<?php $this->print_column_headers(); ?>
                </tr>
                </thead>

                <tbody>
				<?php $this->display_rows_or_placeholder(); ?>
                </tbody>
            </table>
        </div>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	public function display_tablenav( $pos ) {
		if ( $this->type == Result_Item::STATUS_FIXED ) {
			return null;
		}
		?>
        <div class="sui-row">
            <div class="sui-col">
                <form method="post" class="scan-bulk-frm">
                    <input type="hidden" name="action" value="scanBulkAction"/>
					<?php wp_nonce_field( 'scanBulkAction' ) ?>
                    <div class="bulk-action-bar">
                        <label class="sui-checkbox">
                            <input type="checkbox" class="apply-all"/>
                            <span aria-hidden="true"></span>
                        </label>
                        <select name="bulk" class="sui-select-sm bulk-action">
                            <option value=""><?php _e( "Bulk action", wp_defender()->domain ) ?></option>
							<?php if ( $this->type == Result_Item::STATUS_ISSUE ): ?>
                                <option value="ignore"><?php _e( "Ignore", wp_defender()->domain ) ?></option>
							<?php endif; ?>
							<?php if ( $this->type == Result_Item::STATUS_IGNORED ): ?>
                                <option value="unignore"><?php _e( "Restore", wp_defender()->domain ) ?></option>
							<?php endif; ?>
                        </select>
                        <button type="submit" class="sui-button" disabled>
							<?php _e( "Bulk Update", wp_defender()->domain ) ?>
                        </button>
                    </div>
                </form>
            </div>
            <div class="sui-col">
                <div class="sui-pagination-wrap">
                    <span class="sui-pagination-results">
                        <?php printf( __( "%s results", wp_defender()->domain ), $this->_pagination_args['total_items'] ) ?>
                    </span>
                    <ul class="sui-pagination">
						<?php $this->pagination( 'top' ) ?>
                    </ul>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @param object $item The current item
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 */
	public function single_row( $item ) {
		echo '<tr id="mid-' . $item->id . '" class="sui-accordion-item sui-error">';
		$this->single_row_columns( $item );
		echo '</tr>';
		if ( $this->type == Result_Item::STATUS_ISSUE ) {
			echo '<tr class="sui-accordion-item-content">';
			echo '<td colspan="' . $this->get_column_count() . '">' . $this->single_row_according_content( $item ) . '</td>';
			echo '</tr>';
		}
	}

	public function single_row_according_content( $item ) {
		return $item->renderIssueContent();
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
			$links['prev'] = sprintf( '<li><a href="%s">%s</a></li>',
				add_query_arg( 'paged', $current_page - 1, $current_url ), '<i class="sui-icon-chevron-left" aria-hidden="true"></i>' );
		}

		for ( $i = 1; $i <= $total_pages; $i ++ ) {
			if ( ( $i >= 1 && $i <= $radius ) || ( $i > $current_page - 2 && $i < $current_page + 2 ) || ( $i <= $total_pages && $i > $total_pages - $radius ) ) {
				if ( $i == $current_page ) {
					$links[ $i ] = sprintf( '<li><a href="#" disabled="">%s</a></li>', $i );
				} else {
					$links[ $i ] = sprintf( '<li><a href="%s">%s</a></li>',
						add_query_arg( 'paged', $i, $current_url ), $i );
				}
			} elseif ( $i == $current_page - $radius || $i == $current_page + $radius ) {
				$links[ $i ] = '<li><a href="#" disabled="">...</a></li>';
			}
		}

		if ( $current_page < $total_pages && $total_pages > $radius ) {
			$links['next'] = sprintf( '<li><a href="%s">%s</a></li>',
				add_query_arg( 'paged', $current_page + 1, $current_url ), '<i class="sui-icon-chevron-right" aria-hidden="true"></i>' );
		}
		$output            = join( "\n", $links );
		$this->_pagination = $output;

		echo $this->_pagination;
	}

	/**
	 * Generates the columns for a single row of the table
	 *
	 * @param object $item The current item
	 *
	 * @since 3.1.0
	 *
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

}