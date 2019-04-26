<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Vendor;

use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\Log_Helper;
use Hammer\WP\Component;
use Nette\Utils\Html;

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
		$this->add_action( 'wp_ajax_add_receipt_' . $this->eId, 'add_receipt' );
	}

	public function add_script() {
		$this->add_action( 'admin_footer', 'scripts' );
	}

	public function add_receipt() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$first_name = wp_strip_all_tags( HTTP_Helper::retrieve_post( 'firstname' ) );
		$email      = wp_strip_all_tags( HTTP_Helper::retrieve_post( 'email' ) );

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			wp_send_json_error( array(
				'error' => __( "Please, insert a valid email.", wp_defender()->domain )
			) );
		}

		foreach ( $this->settings->{$this->attribute} as $item ) {
			if ( $item['email'] == $email ) {
				wp_send_json_error( array(
					'error' => __( "Recipient already exists.", wp_defender()->domain )
				) );
			}
		}

		if ( empty( $first_name ) ) {
			//find first name by email
			$user = get_user_by_email( $email );
			if ( is_object( $user ) ) {
				$first_name = $user->first_name;
			}
		}

		$results[] = array(
			'name'  => $first_name,
			'email' => $email,
			'index' => time()
		);

		wp_send_json_success( array(
			'results' => $results
		) );
	}

	public function renderInput() {
		if ( empty( $this->placeholder ) ) {
			$this->placeholder = __( "Enter a username", wp_defender()->domain );
		}
		if ( ! is_array( $this->settings->{$this->attribute} ) ) {
			$this->settings->{$this->attribute} = array();
		}
		?>
		<?php if ( $this->lite == false ): ?>
            <div class="sui-notice sui-notice-warning wd-hide no-receipt-warning">
                <p>
					<?php _e( "You've removed all recipients. If you save without a recipient, we'll automatically turn of reports", wp_defender()->domain ) ?>
                </p>
            </div>
            <div class="sui-recipients">
                <input type="hidden" name="<?php echo $this->attribute ?>[]" value=""/>
				<?php foreach ( $this->settings->{$this->attribute} as $index => $item ): ?>
                    <div class="sui-recipient">
                        <span class="sui-recipient-name"><?php echo esc_html( $item['first_name'] ) ?></span>
                        <span class="sui-recipient-email"><?php echo $item['email'] ?></span>
                        <input type="hidden" name="<?php echo $this->attribute ?>[<?php echo $index ?>][first_name]"
                               value="<?php echo esc_html( $item['first_name'] ) ?>"/>
                        <input type="hidden" name="<?php echo $this->attribute ?>[<?php echo $index ?>][email]"
                               value="<?php echo esc_html( $item['email'] ) ?>"/>
                        <button type="button"
                                class="sui-button-icon remove wd-remove-recipient"><i
                                    class="sui-icon-trash" aria-hidden="true"></i></button>
                    </div>
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
                                <label class="sui-label"><?php _e( "First name" ) ?></label>
                                <input type="text" id="<?php echo $this->eId ?>first_name" name="first_name"
                                       class="sui-form-control">
                            </div>
                            <div class="sui-form-field">
                                <label class="sui-label"><?php _e( "Email" ) ?></label>
                                <input type="text" id="<?php echo $this->eId ?>email" name="email"
                                       class="sui-form-control">
                            </div>
                        </div>

                        <div class="sui-box-footer">
                            <button type="button" class="sui-button sui-button-ghost"
                                    data-a11y-dialog-hide="email-search-username-dialog">
								<?php _e( "Cancel", wp_defender()->domain ) ?></button>
                            <button id="add-receipt" type="button"
                                    class="sui-modal-close sui-button"><?php _e( "Add", wp_defender()->domain ) ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(function ($) {
                $('#add-receipt').click(function () {
                    var that = $(this);
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'add_receipt_<?php echo $this->eId ?>',
                            'id': '<?php echo $this->eId ?>',
                            firstname: $("#<?php _e( $this->eId ) ?>first_name").val(),
                            email: $("#<?php _e( $this->eId ) ?>email").val(),
                        },
                        beforeSend: function () {
                            that.attr('disabled', 'disabled')
                        },
                        success: function (data) {
                            var parent = $('#<?php echo $this->eId ?>email').closest('.sui-form-field');
                            if (data.success === true) {
                                parent.removeClass('sui-form-field-error');
                                parent.find('.sui-error-message').remove();
                                $.each(SUI.dialogs, function (i, v) {
                                    v.hide();
                                })
                                $.each(data.data.results, function (i, v) {
                                    var user_row = $('<div class="sui-recipient"/>');
                                    user_row.append($('<span class="sui-recipient-name"/>').html(v.name));
                                    user_row.append($('<span class="sui-recipient-email"/>').html(v.email));
                                    user_row.append($('<input type="hidden" name="<?php echo $this->attribute ?>[' + v.index + '][first_name]"/>').val(v.name));
                                    user_row.append($('<input type="hidden" name="<?php echo $this->attribute ?>[' + v.index + '][email]"/>').val(v.email));
                                    user_row.append($('<button/>').attr({
                                        'class': 'remove wd-remove-recipient sui-button-icon',
                                    }).html('<i class="sui-icon-trash" aria-hidden="true"></i>'))
                                    user_row.insertBefore('#add-new-receipt');
                                    $("#<?php _e( $this->eId ) ?>first_name").val('');
                                    $("#<?php _e( $this->eId ) ?>email").val('')
                                })
                            } else {
                                parent.addClass('sui-form-field-error');
                                if (parent.find('.sui-error-message').size() == 0) {
                                    parent.append($('<span class="sui-error-message"/>').text(data.data.error))
                                }
                            }
                            that.removeAttr('disabled', 'disabled');
                        }
                    })
                    return false;
                })
                $('body').on('click', '.wd-remove-recipient', function (e) {
                    $(this).closest('.sui-recipient').remove();
                    if ($('.sui-recipient').size() == 0) {
                        $('.no-receipt-warning').removeClass('wd-hide');
                    }
                })
            })
        </script>
		<?php
	}
}