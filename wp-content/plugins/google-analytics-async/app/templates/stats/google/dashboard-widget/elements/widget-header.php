<?php
/**
 * The dashboard widget header template.
 *
 * @var bool   $logged_in       Is logged in?.
 * @var array  $periods         Periods list.
 * @var bool   $network         Is network admin?.
 * @var string $settings_url    Settings page url.
 * @var string $statistics_url  Statistics page url.
 * @var string $selected_period Selected date.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

use Beehive\Core\Helpers\Permission;

?>

<div class="beehive-widget-header">

	<div class="beehive-widget-header-options">

		<?php if ( Permission::is_admin_user( $network ) ) : ?>

			<ul>
				<li>
					<a href="<?php echo esc_url( $statistics_url ); ?>"><?php esc_html_e( 'See all stats', 'ga_trans' ); ?></a>
				</li>
				<li>
					<a href="<?php echo esc_url( $settings_url ); ?>"><?php esc_html_e( 'Go to Settings', 'ga_trans' ); ?></a>
				</li>
			</ul>

		<?php endif; ?>

		<select id="beehive-analytics-range" class="beehive-select beehive-analytics-range">
			<?php foreach ( $periods as $period => $data ) : ?>
				<option value="<?php echo esc_attr( $period ); ?>" data-end="<?php echo esc_attr( $data['end'] ); ?>" <?php selected( $selected_period, $period ); ?>><?php echo esc_html( $data['label'] ); ?></option>
			<?php endforeach; ?>
			<option value="custom"><?php esc_html_e( 'Custom', 'ga_trans' ); ?></option>
		</select>

	</div>

	<form class="beehive-widget-header-form sui-hidden-important" id="beehive-widget-custom-period-form">

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

</div>