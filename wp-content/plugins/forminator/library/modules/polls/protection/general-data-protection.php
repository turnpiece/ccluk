<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Polls_General_Data_Protection
 *
 * General Data Protection Applied for Polls
 *
 * @since 1.0.6
 */
class Forminator_Polls_General_Data_Protection extends Forminator_General_Data_Protection {

	public function __construct() {
		parent::__construct( __( 'Forminator Polls', Forminator::DOMAIN ) );
	}

	/**
	 * Add Privacy Message
	 *
	 * @since 1.0.6
	 *
	 * @return string
	 */
	public function get_privacy_message() {
		ob_start();
		include dirname( __FILE__ ) . '/policy-text.php';
		$content = ob_get_clean();
		$content = apply_filters( 'forminator_polls_privacy_policy_content', $content );

		return $content;
	}

	/**
	 * Anon IP
	 *
	 * @since 1.0.6
	 * @return bool
	 */
	public function personal_data_cleanup() {
		$overridden_polls_privacy = get_option( 'forminator_poll_privacy_settings', array() );
		// process overridden
		foreach ( $overridden_polls_privacy as $form_id => $retentions ) {
			$retain_number = (int) $retentions['ip_address_retention_number'];
			$retain_unit   = $retentions['ip_address_retention_unit'];
			if ( empty( $retain_number ) ) {
				// forever
				continue;
			}
			$possible_units = array(
				'days',
				'weeks',
				'months',
				'years',
			);

			if ( ! in_array( $retain_unit, $possible_units, true ) ) {
				continue;
			}

			$retain_time = strtotime( '-' . $retain_number . ' ' . $retain_unit, current_time( 'timestamp' ) );
			$retain_time = date_i18n( 'Y-m-d H:i:s', $retain_time );

			$entry_ids = Forminator_Form_Entry_Model::get_older_entry_ids_of_form_id( $form_id, $retain_time );

			foreach ( $entry_ids as $entry_id ) {
				$entry_model = new Forminator_Form_Entry_Model( $entry_id );
				$this->anonymize_entry_model( $entry_model );
			}

		}

		$retain_number = get_option( 'forminator_retain_votes_interval_number', 0 );
		$retain_unit   = get_option( 'forminator_retain_votes_interval_unit', 'days' );

		if ( empty( $retain_number ) ) {
			return false;
		}

		$possible_units = array(
			'days',
			'weeks',
			'months',
			'years',
		);

		if ( ! in_array( $retain_unit, $possible_units, true ) ) {
			return false;
		}

		$retain_time = strtotime( '-' . $retain_number . ' ' . $retain_unit, current_time( 'timestamp' ) );
		$retain_time = date_i18n( 'Y-m-d H:i:s', $retain_time );

		// todo : select only un-anonymized
		$entry_ids = Forminator_Form_Entry_Model::get_older_entry_ids( 'poll', $retain_time );

		foreach ( $entry_ids as $entry_id ) {
			$entry_model = new Forminator_Form_Entry_Model( $entry_id );
			if ( in_array( $entry_model->form_id, array_keys( $overridden_polls_privacy ) ) ) {
				// use overridden
				continue;
			}
			$this->anonymize_entry_model( $entry_model );
		}

		return true;
	}

	/**
	 * Anon Entry model
	 *
	 * @since 1.0.6
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 */
	private function anonymize_entry_model( Forminator_Form_Entry_Model $entry_model ) {
		if ( isset( $entry_model->meta_data['_forminator_user_ip'] ) ) {
			$meta_id    = $entry_model->meta_data['_forminator_user_ip']['id'];
			$meta_value = $entry_model->meta_data['_forminator_user_ip']['value'];

			if ( function_exists( 'wp_privacy_anonymize_data' ) ) {
				$anon_value = wp_privacy_anonymize_data( 'ip', $meta_value );
			} else {
				$anon_value = '';
			}

			if ( $anon_value !== $meta_value ) {
				$entry_model->update_meta( $meta_id, '_forminator_user_ip', $anon_value );
			}

		}
	}
}