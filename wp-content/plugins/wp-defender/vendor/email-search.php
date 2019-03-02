<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Vendor;

use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\Log_Helper;
use Hammer\WP\Component;

class Email_Search extends Component {
	public $eId = '';
	public $lite = false;
	public $settings;
	public $attribute = 'receipts';
	public $empty_msg = '';
	public $placeholder = '';
	public $noExclude = false;

	public function add_hooks() {
		//this should add in init
		$this->add_action( 'wp_ajax_wd_username_search_' . $this->eId, 'ajax_search_user' );
		$this->add_action( 'wp_ajax_add_receipt_' . $this->eId, 'add_receipt' );
		$this->add_action( 'wp_ajax_remove_receipt_' . $this->eId, 'remove_receipt' );
	}

	public function add_script() {
		$this->add_action( 'admin_footer', 'scripts' );
	}

	public function remove_receipt() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$user_id = HTTP_Helper::retrieve_post( 'user' );
		$user    = get_user_by( 'id', $user_id );
		if ( is_object( $user ) ) {
			$index = array_search( $user_id, $this->settings->{$this->attribute} );
			if ( $index !== false ) {
				unset( $this->settings->{$this->attribute}[ $index ] );
				$this->settings->save();
			}
		}
	}

	public function add_receipt() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$usernames = HTTP_Helper::retrieve_post( 'user' );
		$results   = array();
		foreach ( $usernames as $username ) {
			$user = get_user_by( 'login', $username );
			if ( is_object( $user ) ) {
				if ( ! in_array( $user->ID, $this->settings->{$this->attribute} ) ) {
					$this->settings->{$this->attribute}[] = $user->ID;
					$this->settings->save();
					$results[] = array(
						'name'       => $this->getDisplayName( $user->ID ),
						'is_current' => get_current_user_id() == $user->ID,
						'user_id'    => $user->ID,
						'email'      => $user->user_email
					);
				}
			}
		}
		wp_send_json( array(
			'status'  => 1,
			'results' => $results
		) );
	}

	public function ajax_search_user() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$args = array(
			'search'         => '*' . HTTP_Helper::retrieve_get( 'term' ) . '*',
			'search_columns' => array( 'user_login' ),
			'number'         => 10,
			'exclude'        => $this->settings->{$this->attribute},
			'orderby'        => 'user_login',
			'order'          => 'ASC'
		);
		if ( $this->noExclude == true ) {
			unset( $args['exclude'] );
		}

		$query   = new \WP_User_Query( $args );
		$results = array();
//		foreach ( $query->get_results() as $row ) {
//			$results[] = array(
//				'id'    => $row->user_login,
//				'label' => '<span class="name title">' . esc_html( $this->getDisplayName( $row->ID ) ) . '</span> <span class="email">' . esc_html( $row->user_email ) . '</span>',
//				'thumb' => $this->getAvatarURL( get_avatar( $row->user_email ) )
//			);
//		}
		foreach ( $query->get_results() as $row ) {
			$results['results'][] = array(
				'id'   => $row->user_login,
				'text' => esc_html( $this->getDisplayName( $row->ID ) ),
				//'thumb' => $this->getAvatarURL( get_avatar( $row->user_email ) )
			);
		}
		echo json_encode( $results );
		exit;
	}

	protected function getAvatarURL( $get_avatar ) {
		preg_match( "/src='(.*?)'/i", $get_avatar, $matches );

		return $matches[1];
	}

	public function renderInput() {
		if ( empty( $this->placeholder ) ) {
			$this->placeholder = __( "Enter a username", wp_defender()->domain );
		}
		?>
		<?php if ( $this->lite == false ): ?>
            <div class="sui-recipients">
				<?php foreach ( $this->settings->{$this->attribute} as $id ): ?>
					<?php $user = get_user_by( 'id', $id ) ?>
					<?php if ( is_object( $user ) ): ?>
                        <div class="sui-recipient">
                            <span class="sui-recipient-name"><?php echo esc_html( $this->getDisplayName( $user->ID ) ) ?></span>
                            <span class="sui-recipient-email"><?php echo $user->user_email ?></span>
                            <button data-id="<?php echo $id ?>" type="button"
                                    class="sui-button-icon remove wd-remove-recipient"><i
                                        class="sui-icon-trash" aria-hidden="true"></i></button>
                        </div>
					<?php endif; ?>
				<?php endforeach; ?>
                <button id="add-new-receipt" data-a11y-dialog-show="email-search-username-dialog" type="button"
                        class="sui-button sui-button-ghost">
                    <i class="sui-icon-plus"
                       aria-hidden="true"></i> <?php _e( "Add Recipient", wp_defender()->domain ) ?>
                </button>
            </div>

		<?php else: ?>
            <input name="term" data-empty-msg="<?php echo esc_attr( $this->empty_msg ) ?>"
                   placeholder="<?php echo esc_attr( $this->placeholder ) ?>" id="wd-username-search" type="search"/>
		<?php endif; ?>
		<?php
	}

	/**
	 * @param $id
	 *
	 * @return null|string
	 */
	protected function getDisplayName( $id ) {
		$user = get_user_by( 'id', $id );
		if ( ! is_object( $user ) ) {
			return null;
		}
		if ( ! empty( $user->user_nicename ) ) {
			return $user->user_nicename;
		} else {
			return $user->user_firstname . ' ' . $user->user_lastname;
		}
	}

	public function scripts() {
		?>
        <div class="sui-wrap">
            <div class="sui-dialog sui-dialog-sm" aria-hidden="true" tabindex="-1" id="email-search-username-dialog">
                <div class="sui-dialog-overlay"></div>

                <div class="sui-dialog-content" aria-labelledby="dialogTitle" aria-describedby="dialogDescription"
                     role="dialog">

                    <div class="sui-box" role="document">
                        <div class="sui-box-header">
                            <h3 class="sui-box-title">
								<?php _e( "Add Recipient", wp_defender()->domain ) ?>
                            </h3>
                            <div class="sui-actions-right">
                                <button data-a11y-dialog-hide class="sui-dialog-close"
                                        aria-label="Close this dialog window"></button>
                            </div>
                        </div>
                        <div class="sui-box-body">
                            <p>
								<?php _e( "Add as many recipients as you like, they will receive email reports as per the schedule you set.", wp_defender()->domain ) ?>
                            </p>
                            <div class="sui-form-field">
                                <label class="sui-label"><?php _e( "Username" ) ?></label>
                                <select class="none-sui sui-select"
                                        id="<?php echo $this->eId ?>"
                                        multiple>
                                </select>
                            </div>
                        </div>

                        <div class="sui-box-footer">
                            <button class="sui-button sui-button-ghost"
                                    data-a11y-dialog-hide="email-search-username-dialog">
								<?php _e( "Cancel", wp_defender()->domain ) ?></button>
                            <button id="add-receipt"
                                    class="sui-modal-close sui-button"><?php _e( "Add", wp_defender()->domain ) ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(function ($) {
                $('#<?php echo $this->eId ?>').SUIselect2({
                    minimumInputLength: 3,
                    width: '100%',
                    dropdownCssClass: 'sui-form-control sui-select-dropdown',
                    ajax: {
                        url: ajaxurl,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            var query = {
                                'action': 'wd_username_search_<?php echo $this->eId ?>',
                                'id': '<?php echo $this->eId ?>',
                                'term': params.term
                            }

                            return query;
                        },
                    }
                });

                $('#add-receipt').click(function () {
                    var that = $(this);
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'add_receipt_<?php echo $this->eId ?>',
                            'id': '<?php echo $this->eId ?>',
                            user: $("#<?php _e( $this->eId ) ?>").val()
                        },
                        beforeSend: function () {
                            that.attr('disabled', 'disabled')
                        },
                        success: function (data) {
                            $.each(SUI.dialogs, function (i, v) {
                                v.hide();
                            })
                            $.each(data.results, function (i, v) {
                                var user_row = $('<div class="sui-recipient"/>');
                                user_row.append($('<span class="sui-recipient-name"/>').html(v.name));
                                user_row.append($('<span class="sui-recipient-email"/>').html(v.email));
                                user_row.append($('<button/>').attr({
                                    'data-id': data.user_id,
                                    'class': 'remove wd-remove-recipient sui-button-icon',
                                }).html('<i class="sui-icon-trash" aria-hidden="true"></i>'))
                                user_row.insertBefore('#add-new-receipt');
                            })
                            that.removeAttr('disabled');
                        }
                    })
                    return false;
                })
                $('body').on('click', '.wd-remove-recipient', function (e) {
                    e.preventDefault();
                    var that = $(this);
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'remove_receipt_<?php echo $this->eId ?>',
                            'id': '<?php echo $this->eId ?>',
                            user: that.data('id')
                        },
                        beforeSend: function () {
                            that.attr('disabled', 'disabled')
                        },
                        success: function (data) {
                            that.closest('.sui-recipient').remove();
                        }
                    })
                })
            })
        </script>
		<?php
	}
}