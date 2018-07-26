<?php

class MS_View_Membership_Overview_Simple extends MS_View {

	public function to_html() {
		$this->check_simulation();

		$membership = $this->data['membership'];

		$toggle = array(
			'id' => 'ms-toggle-' . $membership->id,
			'type' => MS_Helper_Html::INPUT_TYPE_RADIO_SLIDER,
			'value' => $membership->active,
			'class' => '',
			'data_ms' => array(
				'action' => MS_Controller_Membership::AJAX_ACTION_TOGGLE_MEMBERSHIP,
				'field' => 'active',
				'membership_id' => $membership->id,
			),
		);

		$status_class = '';
		if ( $membership->active ) {
			$status_class = 'ms-active';
		}

		$edit_button = sprintf(
			'<a href="?page=%1$s&step=%2$s&tab=%3$s&membership_id=%4$s" class="button">%5$s</a>',
			esc_attr( $_REQUEST['page'] ),
			MS_Controller_Membership::STEP_EDIT,
			MS_Controller_Membership::TAB_DETAILS,
			esc_attr( $membership->id ),
			'<i class="wpmui-fa wpmui-fa-pencil"></i> ' . __( 'Edit', 'membership2' )
		);

		ob_start();
		?>
		<div class="wrap ms-wrap ms-membership-overview">
			<div class="ms-wrap-top ms-group">
				<div class="ms-membership-status-wrapper">
					<?php MS_Helper_Html::html_element( $toggle ); ?>
					<div id="ms-membership-status" class="ms-membership-status <?php echo esc_attr( $status_class ); ?>">
						<?php
						printf(
							'<div class="ms-active">%s</div>',
							sprintf(
								__( 'Membership is %s', 'membership2' ),
								'<span id="ms-membership-status-text" class="ms-ok">' .
								__( 'Active', 'membership2' ) .
								'</span>'
							)
						);
						printf(
							'<div>%s</div>',
							sprintf(
								__( 'Membership is %s', 'membership2' ),
								'<span id="ms-membership-status-text" class="ms-nok">' .
								__( 'Inactive', 'membership2' ) .
								'</span>'
							)
						);
						?>
					</div>
				</div>
				<div class="ms-membership-edit-wrapper">
					<?php echo $edit_button; ?>
				</div>
				<?php

				$title = sprintf(
					__( '%s Overview', 'membership2' ),
					$membership->get_name_tag( true )
				);
				$desc = array(
					__( 'Here you find a summary of this membership, and alter any of its details.', 'membership2' ),
					sprintf(
						__( 'This is a %s', 'membership2' ),
						$membership->get_type_description()
					),
				);

				MS_Helper_Html::settings_header(
					array(
						'title' => $title,
						'desc' => $desc,
						'title_icon_class' => 'wpmui-fa wpmui-fa-dashboard',
					)
				);
				?>
				<div class="clear"></div>
			</div>
			<?php $this->available_content_panel(); ?>
			<div class="clear"></div>
		</div>

		<?php
		$html = ob_get_clean();

		return $html;
	}

	public function news_panel() {
		?>
		<div class="ms-half ms-settings-box">
			<h3>
				<i class="ms-low wpmui-fa wpmui-fa-globe"></i>
				<?php _e( 'Recent News', 'membership2' ); ?>
			</h3>

			<?php if ( ! empty( $this->data['events'] ) ) : ?>
				<div class="inside group">
					<?php $this->news_panel_data( $this->data['events'] ); ?>
				</div>

				<div class="ms-news-view-wrapper">
					<?php
					$url = esc_url_raw(
						add_query_arg( array( 'step' => MS_Controller_Membership::STEP_NEWS ) )
					);
					MS_Helper_Html::html_element(
						array(
							'id' => 'view_news',
							'type' => MS_Helper_Html::TYPE_HTML_LINK,
							'value' => __( 'View More News', 'membership2' ),
							'url' => $url,
							'class' => 'wpmui-field-button button',
						)
					);
					?>
				</div>
			<?php else : ?>
				<div class="inside group">
					<p class="ms-italic">
					<?php _e( 'There will be some interesting news here when your site gets going.', 'membership2' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Renders the Members panel
	 *
	 * @since  1.0.0
	 */
	public function members_panel() {
		$count = count( $this->data['members'] );
		$membership_id = $this->data['membership']->id;
		?>
		<div class="ms-half ms-settings-box">
			<h3>
				<i class="ms-low wpmui-fa wpmui-fa-user"></i>
				<?php printf( __( 'New Members (%s)', 'membership2' ), $count ); ?>
			</h3>

			<?php if ( $count > 0 ) : ?>
				<div class="inside group">
					<?php
					$this->members_panel_data(
						$this->data['members'],
						$membership_id
					);
					?>
				</div>

				<div class="ms-member-edit-wrapper">
					<?php
					$url = MS_Controller_Plugin::get_admin_url(
						'members',
						array( 'membership_id' => $membership_id )
					);
					MS_Helper_Html::html_element(
						array(
							'id' => 'edit_members',
							'type' => MS_Helper_Html::TYPE_HTML_LINK,
							'value' => __( 'All Members', 'membership2' ),
							'url' => $url,
							'class' => 'wpmui-field-button button',
						)
					);
					?>
				</div>
			<?php else : ?>
				<div class="inside group">
					<p class="ms-italic">
					<?php _e( 'No members yet.', 'membership2' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Echo the news-contents. This function can be overwritten by other views
	 * to customize the list.
	 *
	 * @since  1.0.0
	 *
	 * @param array $items List of news to display.
	 */
	protected function news_panel_data( $items ) {
		$item = 0;
		$max_items = 10;
		$class = '';
		?>
		<table class="ms-list-table widefat">
			<thead>
				<tr>
					<th><?php _e( 'Date', 'membership2' ); ?></th>
					<th><?php _e( 'Member', 'membership2' ); ?></th>
					<th><?php _e( 'Event', 'membership2' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $this->data['events'] as $event ) :
				$item += 1;
				if ( $item > $max_items ) { break; }
				$class = ('alternate' == $class ? '' : 'alternate' );
				?>
				<tr class="<?php echo esc_attr( $class ); ?>">
					<td><?php
					echo esc_html(
						MS_Helper_Period::format_date( $event->post_modified )
					);
					?></td>
					<td><?php echo esc_html( MS_Model_Member::get_username( $event->user_id ) ); ?></td>
					<td><?php echo esc_html( $event->description ); ?></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Echo a member-list. This function can be overwritten by other views
	 * to customize the list.
	 *
	 * @since  1.0.0
	 *
	 * @param array $members List of members to display.
	 */
	protected function members_panel_data( $members, $membership_id ) {
		$item = 0;
		$max_items = 10;
		$class = '';
		$status_types = MS_Model_Relationship::get_status_types();
		?>
		<table class="ms-list-table widefat">
			<thead>
				<th><?php _e( 'Member', 'membership2' ); ?></th>
				<th><?php _e( 'Since', 'membership2' ); ?></th>
				<th><?php _e( 'Status', 'membership2' ); ?></th>
			</thead>
			<tbody>
			<?php foreach ( $this->data['members'] as $member ) :
				$item += 1;
				if ( $item > $max_items ) { break; }
				$class = ('alternate' == $class ? '' : 'alternate' );
				$subscription = $member->get_subscription( $membership_id );
				?>
				<tr class="<?php echo esc_attr( $class ); ?>">
					<td><?php echo esc_html( $member->username ); ?></td>
					<td><?php
					echo esc_html(
						MS_Helper_Period::format_date( $subscription->start_date )
					);
					?></td>
					<td><?php echo esc_html( $status_types[ $subscription->status ] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	public function available_content_panel() {
		$membership = $this->data['membership'];

		$desc = $membership->get_description();
		$desc_empty_class = (empty( $desc ) ? '' : 'hidden');

		?>
		<div class="ms-overview-container">
			<div class="ms-settings">
				<div class="ms-overview-top">
					<div class="ms-settings-desc ms-description membership-description">
						<?php echo $desc; ?>
					</div>
					<?php
					MS_Helper_Html::html_separator();
					$this->news_panel();
					$this->members_panel();
					?>
				<div class="clear"></div>
				</div>
				<div class="ms-overview-available-content-wrapper ms-overview-bottom">
					<h3><i class="ms-img-unlock"></i> <?php _e( 'Available Content', 'membership2' ); ?></h3>
					<div class="ms-description ms-indented-description">
					<?php
					printf(
						__( 'This is Membership2 which <span class="ms-bold">%s</span> members has access to.', 'membership2' ),
						esc_html( $this->data['membership']->name )
					);
					?>
					</div>
					<div class="inside">
						<?php $this->available_content_panel_data(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	protected function available_content_panel_data() {
		$membership = $this->data['membership'];
		$rule_types = MS_Model_Rule::get_rule_types();

		?>
		<div class="ms-settings ms-group">
			<div class="ms-group">
			<?php
			foreach ( $rule_types as $rule_type ) {
				$rule = $membership->get_rule( $rule_type );
				if ( ! $rule->is_active() ) { continue; }

				if ( $rule->has_rules() ) {
					$this->content_box( array(), $rule );
				}
			}
			?>
			</div>
		</div>
		<?php

		if ( ! $membership->is_free ) {
			$payment_url = esc_url_raw(
				add_query_arg(
					array(
						'step' => MS_Controller_Membership::STEP_PAYMENT,
						'edit' => 1,
					)
				)
			);

			MS_Helper_Html::html_element(
				array(
					'id' => 'setup_payment',
					'type' => MS_Helper_Html::TYPE_HTML_LINK,
					'value' => __( 'Payment Options', 'membership2' ),
					'url' => $payment_url,
					'class' => 'wpmui-field-button button',
				)
			);
		}
	}

	/**
	 * Echo a content list as tag-list.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $contents List of content items to display.
	 */
	protected function content_box( $contents = array(), $rule ) {
		static $row_items = 0;

		$rule_titles = MS_Model_Rule::get_rule_type_titles();
		$title = $rule_titles[ $rule->rule_type ];
		$contents = (array) $rule->get_contents( null, true );

		$membership_id = $this->data['membership']->id;

		$row_items += 1;
		$new_row = (0 == $row_items % 4);
		$show_sep = (0 == ($row_items - 1) % 4);

		if ( $show_sep && $row_items > 1 ) {
			MS_Helper_Html::html_separator();
		}
		?>
		<div class="ms-part-4 ms-min-height">
			<?php if ( ! $new_row ) { MS_Helper_Html::html_separator( 'vertical' ); } ?>
			<div class="ms-bold">
				<?php printf( '%s (%s):', $title, $rule->count_rules() ); ?>
			</div>

			<div class="inside">
				<ul class="ms-content-tag-list ms-group">
				<?php
				foreach ( $contents as $content ) {
					if ( $content->access ) {
						MS_Helper_Html::content_tag( $content );
					}
				}
				?>
				</ul>

				<div class="ms-protection-edit-wrapper">
					<?php
					$edit_url = MS_Controller_Plugin::get_admin_url(
						'protection',
						array(
							'tab' => $rule->rule_type,
							'membership_id' => $membership_id,
						)
					);

					MS_Helper_Html::html_element(
						array(
							'id' => 'edit_' . $rule->rule_type,
							'type' => MS_Helper_Html::TYPE_HTML_LINK,
							'title' => $title,
							'value' => sprintf( __( 'Edit %s Access', 'membership2' ), $title ),
							'url' => $edit_url,
							'class' => 'wpmui-field-button button',
						)
					);
					?>
				</div>
			</div>
		</div>
		<?php
		if ( $new_row ) {
			echo '</div><div class="ms-group">';
		}
	}
}