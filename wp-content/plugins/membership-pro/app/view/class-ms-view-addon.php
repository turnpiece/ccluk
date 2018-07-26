<?php
/**
 * Renders Addons Page.
 *
 * Extends MS_View for rendering methods and magic methods.
 *
 * @since  1.0.0
 *
 * @return object
 */
class MS_View_Addon extends MS_View {

	/**
	 * Overrides parent's to_html() method.
	 *
	 * Creates an output buffer, outputs the HTML and grabs the buffer content before releasing it.
	 * Creates a wrapper 'ms-wrap' HTML element to contain content and navigation. The content inside
	 * the navigation gets loaded with dynamic method calls.
	 * e.g. if key is 'settings' then render_settings() gets called, if 'bob' then render_bob().
	 *
	 * @todo Could use callback functions to call dynamic methods from within the helper, thus
	 * creating the navigation with a single method call and passing method pointers in the $tabs array.
	 *
	 * @since  1.0.0
	 *
	 * @return object
	 */
	public function to_html() {
		$this->check_simulation();

		$items = $this->data['addon']->get_addon_list();
		$lang = (object) array(
			'active_badge' 	=> __( 'ACTIVE', 'membership2' ),
			'show_details' 	=> __( 'Details...', 'membership2' ),
			'close_details' => __( 'Close', 'membership2' ),
		);
		$filters = array(
			'all' 		=> __( 'All', 'membership2' ),
			'active' 	=> __( 'Active', 'membership2' ),
			'inactive' 	=> __( 'Inactive', 'membership2' ),
			'options' 	=> __( 'With options', 'membership2' ),
		);

		ob_start();
		?>
		<div class="ms-wrap ms-addon-list">
			<h2 class="ms-settings-title">
				<i class="wpmui-fa wpmui-fa-puzzle-piece"></i>
				<?php  _e( 'Membership Add-ons', 'membership2' ); ?>
			</h2>
			<form action="" method="post">
				<?php mslib3()->html->addon_list( $items, $lang, $filters ); ?>
			</form>
		</div>
		<?php
		$html = ob_get_clean();
		echo $html;
	}
}