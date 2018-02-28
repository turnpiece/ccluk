<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front render class for custom forms
 */
class Forminator_QForm_Front extends Forminator_Render_Form {

	/**
	 * Class instance
	 *
	 * @var Forminator_Render_Form|null
	 */
	private static $instance = null;

	/**
	 * @var array
	 */
	private static $forms_properties = array();

	/**
	 * Return class instance
	 *
	 * @since 1.0
	 * @return Forminator_QForm_Front
	 */
	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Initialize method
	 *
	 * @since 1.0
	 */
	public function init() {
		add_shortcode( 'forminator_quiz', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render shortcode
	 *
	 * @since 1.0
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function render_shortcode( $atts = array() ) {
		//use already created instance if already available
		$view = self::get_instance();
		if ( ! isset( $atts['id'] ) ) {
			return $view->message_required();
		}

		ob_start();

		$view->display( $atts['id'] );

		return ob_get_clean();
	}

	/**
	 * Display form method
	 *
	 * @since 1.0
	 *
	 * @param $id
	 */
	public function display( $id, $ajax = false, $data = false ) {
		if ( $data && ! empty( $data ) ) {
			$this->model = Forminator_Quiz_Form_Model::model()->load_preview( $id, $data );
		} else {
			$this->model = Forminator_Quiz_Form_Model::model()->load( $id );
		}

		if ( is_object( $this->model ) ) {
			$this->generate_render_id( $id );
			$this->render( $id );

			if ( ! $ajax ) {
				forminator_print_front_styles();
				forminator_print_front_scripts();
				self::$forms_properties[] = array(
					'id'        => $id,
					'render_id' => self::$render_ids[ $id ],
				);
				add_action( 'wp_footer', array( $this, 'forminator_render_front_scripts' ), 9999 );
			}
		} else {
			$this->message_not_found();
		}
	}

	/**
	 * Return fields
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_fields() {
		return $this->model->questions;
	}

	/**
	 * Return form fields markup
	 *
	 * @since 1.0
	 *
	 * @param bool $render
	 *
	 * @return mixed
	 */
	public function render_fields( $render = true ) {
		$html = '';

		$fields = $this->get_fields();
		foreach ( $fields as $key => $field ) {
			do_action( 'forminator_before_field_render', $field );

			// Render field
			$html .= $this->render_field( $field );

			do_action( 'forminator_after_field_render', $field );
		}

		if ( $render ) {
			echo $html;
		} else {
			return apply_filters( 'forminator_render_fields_markup', $html, $fields );
		}

	}

	/**
	 * Render field
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function render_field( $field ) {
		if ( $field['type'] == 'knowledge' ) {
			$html = $this->_render_knowledge( $field );
		} else {
			$html = $this->_render_nowrong( $field );
		}

		return apply_filters( 'forminator_field_markup', $html, $field, $this );
	}

	/**
	 * Render No wrong quiz
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	private function _render_nowrong( $field ) {
		ob_start();
		$uniq_id = '-' . uniqid();
		?>
        <div class="forminator-quiz--question">
            <p class="forminator-question--title"><?php echo $field['title'] ?></p>
            <div class="forminator-question--answers">
				<?php if ( isset ( $field['answers'] ) ): ?>
					<?php foreach ( $field['answers'] as $key => $answer ): ?>

                        <div class="forminator-answer--wrap">

                            <div class="forminator-answer">

                                <input type="radio" name="answers[<?php echo $field['slug'] ?>]"
                                       value="<?php echo $key ?>"
                                       id="<?php echo $field['slug'] . '-' . $key . $uniq_id ?>"
                                       class="forminator-answer--input" title="<?php echo $answer['title'] ?>"/>

                                <label class="forminator-answer--design" for="<?php echo $field['slug'] . '-' . $key . $uniq_id ?>"
                                       aria-hidden="true">

									<?php if ( isset( $answer['image'] ) && ! empty( $answer['image'] ) ) : ?>

                                        <span class="forminator-answer--image"
                                              style="background-image: url('<?php echo $answer['image'] ?>');"></span>

									<?php endif; ?>

                                    <span class="forminator-answer--text">
									<span class="forminator-answer--check"><i class="wpdui-icon wpdui-icon-check"></i></span>
									<span class="forminator-answer--name"><?php echo $answer['title'] ?></span>
								</span>

                                </label>

                            </div>

                        </div>

					<?php endforeach; ?>
				<?php endif; ?>
            </div>

        </div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render knowledge quiz
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	private function _render_knowledge( $field ) {
		ob_start();
		$class   = ( isset( $this->model->settings['results_behav'] ) && $this->model->settings['results_behav'] == 'end' ) ? '' : 'forminator-submit-rightaway';
		$uniq_id = '-' . uniqid();

		?>
        <div class="forminator-quiz--question" id="<?php echo $field['slug'] ?>">
            <p class="forminator-question--title"><?php echo $field['title'] ?></p>
            <div class="forminator-question--answers">
				<?php foreach ( $field['answers'] as $k => $answer ): ?>
					<?php $eID = $field['slug'] . '-' . $k . $uniq_id ?>
                    <div class="forminator-answer--wrap">
                        <div class="forminator-answer">
                            <input type="radio" name="answers[<?php echo $field['slug'] ?>]" value="<?php echo $k ?>"
                                   id="<?php echo $eID ?>"
                                   class="forminator-answer--input <?php echo $class ?>"
                                   title="<?php echo $answer['title'] ?>"/>

                            <label class="forminator-answer--design" for="<?php echo $eID ?>" aria-hidden="true">
								<?php if ( isset( $answer['image'] ) && ! empty( $answer['image'] ) ): ?>
                                    <span class="forminator-answer--image"
                                          style="background-image: url('<?php echo $answer['image'] ?>');"></span>
								<?php endif; ?>
                                <span class="forminator-answer--text">
                                 <span class="forminator-answer--name"><?php echo $answer['title'] ?></span>
                            </span>
                            </label>
                        </div>
                    </div>
				<?php endforeach ?>
            </div>
            <p class="forminator-question--result"></p>
        </div><!-- END Sample Question #1 -->
		<?php
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Return Form ID required message
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function message_required() {
		return __( "Form ID attribute is required!", Forminator::DOMAIN );
	}

	/**
	 * Return From ID not found message
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function message_not_found() {
		return __( "Form ID not found!", Forminator::DOMAIN );
	}

	/**
	 * Return form type
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_form_type() {
		return 'quiz';
	}

	/**
	 * Return form settings
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_form_settings() {
		return $this->model->settings;
	}

	/**
	 * Return form desing
	 *
	 * @since 1.0
	 * @return mixed|string
	 */
	public function get_form_design() {
		$form_settings = $this->get_form_settings();

		if ( ! isset( $form_settings['visual_style'] ) ) {
			return 'list';
		}

		return $form_settings['visual_style'];
	}

	/**
	 * Render quiz header
	 *
	 * @since 1.0
	 * @return string
	 */
	public function render_form_header() {
		ob_start();
		?>
        <div class="forminator-quiz--header">
            <p class="forminator-quiz--title"><?php echo forminator_get_form_name( $this->model->id, $this->get_form_type() ) ?></p>
			<?php if ( isset( $this->model->settings['quiz_feat_image'] ) && ! empty( $this->model->settings['quiz_feat_image'] ) ): ?>
                <figure class="forminator-quiz--image">
                    <img src="<?php echo $this->model->settings['quiz_feat_image'] ?>">
                </figure>
			<?php endif; ?>
			<?php if ( isset( $this->model->settings['quiz_description'] ) && ! empty( $this->model->settings['quiz_description'] ) ): ?>
                <p class="forminator-quiz--description"><?php echo $this->model->settings['quiz_description'] ?></p>
			<?php endif; ?>
        </div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Return form submit button markup
	 *
	 * @since 1.0
	 *
	 * @param      $form_id
	 * @param bool $render
	 *
	 * @return mixed
	 */
	public function get_submit( $form_id, $render = true ) {
		$nonce   = wp_nonce_field( 'forminator_submit_quizzes', true, false, false );
		$post_id = $this->get_post_id();

		$html = '<div class="quiz-form-button-holder">';
		if ( $this->model->quiz_type == 'nowrong' || ( isset( $this->model->settings['results_behav'] ) && $this->model->settings['results_behav'] == 'end' ) ) {
			$html .= sprintf( '<button class="forminator-button">%s</button>', __( "Ready to send", Forminator::DOMAIN ) );
		}
		$html .= '</div>';
		$html .= $nonce;
		$html .= sprintf( '<input type="hidden" name="form_id" value="%s">', $form_id );
		$html .= sprintf( '<input type="hidden" name="page_id" value="%s">', $post_id );
		$html .= '<input type="hidden" name="action" value="forminator_submit_quizzes">';
		if ( $render ) {
			echo apply_filters( 'forminator_render_form_submit_markup', $html, $form_id, $post_id, $nonce );
		} else {
			return apply_filters( 'forminator_render_form_submit_markup', $html, $form_id, $post_id, $nonce );
		}
	}

	/**
	 *
	 */
	public function forminator_render_front_scripts() {
		?>
        <script type="text/javascript">
			jQuery(document).ready(function () {
				<?php
				if ( ! empty( self::$forms_properties ) ) {
				foreach ( self::$forms_properties as $form_properties ) {
				?>
				jQuery('#forminator-module-<?php echo $form_properties['id'] ?>[data-forminator-render="<?php echo $form_properties['render_id']; ?>"]').forminatorFront({
					form_type: '<?php echo $this->get_form_type(); ?>',
				});
				<?php
				}
				}?>
			});
        </script>
		<?php

	}
}