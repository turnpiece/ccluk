<?php

/**
 * Admin dasboard stats page template.
 *
 * @var bool   $network         Network flag.
 * @var string $selected_period Selected date.
 * @var bool   $logged_in       Is logged in.
 * @var array  $periods         Date periods.
 * @var bool   $delay_notice    Should show delay notice.
 */

defined( 'WPINC' ) || die();

use Beehive\Core\Helpers\Permission;

?>

<div id="beehive-analytics-statistics-page" class="beehive-wrap">

	<?php wp_nonce_field( 'beehive_admin_nonce', 'beehive_admin_nonce' ); // This can be used for form processing. ?>

	<div class="sui-header">

		<h1 class="sui-header-title"><?php esc_html_e( 'Statistics', 'ga_trans' ); ?></h1>

		<div class="sui-actions-right">

			<select id="beehive-analytics-range" class="beehive-select">
				<?php foreach ( $periods as $period => $data ) : ?>
					<option value="<?php echo esc_attr( $period ); ?>" data-end="<?php echo esc_attr( $data['end'] ); ?>" <?php selected( $selected_period, $period ); ?>><?php echo esc_html( $data['label'] ); ?></option>
				<?php endforeach; ?>
				<option value="custom"><?php esc_html_e( 'Custom', 'ga_trans' ); ?></option>
			</select>

			<label id="beehive-analytics-compare"  class="inline-checkbox sui-hidden-important">
				<input type="checkbox" name="compare_periods" id="compare_periods" value="1"/>
				<span class="checkbox-title"><?php esc_html_e( 'Compare to previous period', 'ga_trans' ); ?></span>
			</label>

		</div>

	</div>

	<form id="beehive-analytics-custom-period-form" class="beehive-search-form sui-hidden-important">

		<div class="beehive-field">
			<label><?php esc_html_e( 'From', 'ga_trans' ); ?></label>
			<input id="beehive-date-from" class="beehive-date-picker" autocomplete="off"/>
		</div>

		<div class="beehive-field">
			<label><?php esc_html_e( 'To', 'ga_trans' ); ?></label>
			<input id="beehive-date-to" class="beehive-date-picker" autocomplete="off"/>
		</div>

		<div class="beehive-buttons">
			<button data-action="reset" type="reset"><?php esc_html_e( 'Reset', 'ga_trans' ); ?></button>
			<button data-action="apply" type="button" id="beehive-widget-custom-period-submit"><?php esc_html_e( 'Apply', 'ga_trans' ); ?></button>
		</div>

	</form>

	<div id="poststuff">

		<?php if ( Permission::has_report_cap( 'visitors', false, 'statistics', $network ) ) : ?>
			<?php $this->view( 'stats/google/stats-page/metaboxes/visitors' ); ?>
		<?php endif; ?>

		<?php if ( Permission::has_report_cap( 'countries', false, 'statistics', $network ) ) : ?>
			<?php $this->view( 'stats/google/stats-page/metaboxes/countries' ); ?>
		<?php endif; ?>

		<div id="post-body" class="postbox-row">

			<?php if ( Permission::has_report_cap( 'pages', false, 'statistics', $network ) ) : ?>

				<div class="postbox-col">

					<?php $this->view( 'stats/google/stats-page/metaboxes/pages' ); ?>

				</div>

			<?php endif; ?>

			<?php if ( Permission::has_report_cap( 'referrals', false, 'statistics', $network ) ) : ?>

				<div class="postbox-col">

					<?php $this->view( 'stats/google/stats-page/metaboxes/referrals' ); ?>

				</div>

			<?php endif; ?>

		</div>

	</div>

</div>