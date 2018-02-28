<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Widget
 *
 * @since 1.0
 */
class Forminator_Widget extends WP_Widget {

	/**
	 * Forminator_Widget constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct(
			'forminator_widget',
			__( "Forminator Widget", Forminator::DOMAIN ),
			array( 'description' => __( 'Forminator Widget', Forminator::DOMAIN ), )
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @since 1.0
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// Extract variables
		extract( $args );
		extract( $instance );

		// Print widget before markup
		echo $before_widget;

		// Make sure $form_type is set
		if( ! isset( $form_type ) ) return;

		if( $form_type == "form" && ! empty( $form_id ) ) {
			forminator_form( $form_id, false );
		}

		if( $form_type == "poll" && ! empty( $form_id ) ) {
			forminator_poll( $poll_id, false );
		}

		if( $form_type == "quiz" && ! empty( $form_id ) ) {
			forminator_quiz( $quiz_id, false );
		}

		// Print widget after markup
		echo $after_widget;
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @since 1.0
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$form_type     = '';
		$form_id       = '';
		$poll_id       = '';
		$quiz_id       = '';

		if( isset( $instance['form_type'] ) ){
			$form_type = $instance['form_type'];
		}

		if( isset( $instance['form_id'] ) ){
			$form_id = $instance['form_id'];
		}

		if( isset( $instance['poll_id'] ) ){
			$poll_id = $instance['poll_id'];
		}

		if( isset( $instance['quiz_id'] ) ){
			$quiz_id = $instance['quiz_id'];
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'form_type' ); ?>">
				<?php _e( "Form Type", Forminator::DOMAIN ); ?>
			</label>
			<select class="widefat forminator-form-type" id="<?php echo $this->get_field_id( 'form_type' ); ?>" name="<?php echo $this->get_field_name( 'form_type' ); ?>">
				<option value="form" <?php selected( 'form', $form_type ); ?> <?php echo $form_type; ?>><?php _e( "Form", Forminator::DOMAIN ); ?></option>
				<option value="poll" <?php selected( 'poll', $form_type ); ?>><?php _e( "Poll", Forminator::DOMAIN ); ?></option>
				<option value="quiz" <?php selected( 'quiz', $form_type ); ?>><?php _e( "Quiz", Forminator::DOMAIN ); ?></option>
			</select>
		</p>

		<p id="forminator-wrapper-form" class="forminator-form-wrapper">
			<label for="<?php echo $this->get_field_id( 'form_id' ); ?>">
				<?php _e( "Select Form", Forminator::DOMAIN ); ?>
			</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'form_id' ); ?>" name="<?php echo $this->get_field_name( 'form_id' ); ?>">
				<?php
				$modules = forminator_cform_modules( 999 );
				foreach( $modules as $module ) {
					$title = forminator_get_form_name( $module['id'], 'custom_form');
					if ( strlen( $title ) > 25 ) {
						$title = substr( $title, 0, 25 ) . '...';
					}
					echo '<option value="' . $module['id'] . '" '. selected( $module['id'], $form_id, false ) .'>' . $title . ' - ID: ' . $module['id'] . '</option>';
				}
				?>
			</select>
		</p>

		<p id="forminator-wrapper-poll" class="forminator-form-wrapper">
			<label for="<?php echo $this->get_field_id( 'poll_id' ); ?>">
				<?php _e( "Select Poll", Forminator::DOMAIN ); ?>
			</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'poll_id' ); ?>" name="<?php echo $this->get_field_name( 'poll_id' ); ?>">
				<?php
				$modules = forminator_polls_modules( 999 );
				foreach( $modules as $module ) {
					$title = forminator_get_form_name( $module['id'], 'poll');
					if ( strlen( $title ) > 25 ) {
						$title = substr( $title, 0, 25 ) . '...';
					}
					echo  '<option value="' . $module['id'] . '" '. selected( $module['id'], $poll_id, false ) .'>' . $title . ' - ID: ' . $module['id'] . '</option>';
				}
				?>
			</select>
		</p>

		<p id="forminator-wrapper-quiz" class="forminator-form-wrapper">
			<label for="<?php echo $this->get_field_id( 'quiz_id' ); ?>">
				<?php _e( "Select Quiz", Forminator::DOMAIN ); ?>
			</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'quiz_id' ); ?>" name="<?php echo $this->get_field_name( 'quiz_id' ); ?>">
				<?php
				$modules = forminator_quizzes_modules( 999 );
				foreach( $modules as $module ) {
					$title = forminator_get_form_name( $module['id'], 'quiz');
					if ( strlen( $title ) > 25 ) {
						$title = substr( $title, 0, 25 ) . '...';
					}
					echo '<option value="' . $module['id'] . '" '. selected( $module['id'], $quiz_id, false ) .'>' . $title . ' - ID: ' . $module['id'] . '</option>';
				}
				?>
			</select>
		</p>

		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery(".forminator-form-type").change(function(){
					var value = $(this).val(),
						$widget = jQuery(this).closest('.widget-content')
					;

					$widget.find(".forminator-form-wrapper").hide();
					$widget.find("#forminator-wrapper-" + value).show();
				});

				jQuery(".forminator-form-type").change();
			});
		</script>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @since 1.0
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['form_type']     = $new_instance['form_type'];
		$instance['form_id']       = $new_instance['form_id'];
		$instance['poll_id']       = $new_instance['poll_id'];
		$instance['quiz_id']       = $new_instance['quiz_id'];

		return $instance;
	}
}

/**
 * Register widget
 *
 * @since 1.0
 */
function forminator_widget_register_widget() {
	register_widget( 'forminator_widget' );
}

add_action( 'widgets_init', 'forminator_widget_register_widget' );