<?php
/**
 * Dashboard screen.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Dashboard.
 */
class VMS_EFWP_Admin_Dashboard {

	/**
	 * Render dashboard.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = vms_efwp()->settings;
		$mode     = $settings->get_mode();
		$has_creds = $settings->has_credentials();

		$today_start = gmdate( 'Y-m-d 00:00:00' );
		$today_end   = gmdate( 'Y-m-d 23:59:59' );
		$today       = VMS_EFWP_Stats::sales_summary( $today_start, $today_end, false );

		$week_start = gmdate( 'Y-m-d 00:00:00', time() - 6 * DAY_IN_SECONDS );
		$week       = VMS_EFWP_Stats::sales_summary( $week_start, $today_end, false );

		$month_start = gmdate( 'Y-m-d 00:00:00', time() - 29 * DAY_IN_SECONDS );
		$month       = VMS_EFWP_Stats::sales_summary( $month_start, $today_end, false );

		$all_time   = VMS_EFWP_Stats::sales_summary( '1970-01-01 00:00:00', $today_end, false );
		$subs       = VMS_EFWP_Stats::subscriptions_summary( false );
		$currency   = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$';

		?>
		<div class="wrap vefwp-wrap">
			<div class="vefwp-header">
				<div class="vefwp-header__title">
					<h1><?php esc_html_e( 'FastSpring Analytics', 'vms-elements-fastspring-woo-payment' ); ?></h1>
					<span class="vefwp-mode-pill vefwp-mode-pill--<?php echo esc_attr( $mode ); ?>">
						<?php echo 'live' === $mode ? esc_html__( 'LIVE', 'vms-elements-fastspring-woo-payment' ) : esc_html__( 'SANDBOX', 'vms-elements-fastspring-woo-payment' ); ?>
					</span>
				</div>
				<div class="vefwp-header__actions">
					<label class="vefwp-toggle">
						<input type="checkbox" id="vefwp-include-test" <?php checked( $settings->is_sandbox() ); ?> />
						<?php esc_html_e( 'Include test orders', 'vms-elements-fastspring-woo-payment' ); ?>
					</label>
					<select id="vefwp-range" class="vefwp-select">
						<option value="7"><?php esc_html_e( 'Last 7 days', 'vms-elements-fastspring-woo-payment' ); ?></option>
						<option value="30" selected><?php esc_html_e( 'Last 30 days', 'vms-elements-fastspring-woo-payment' ); ?></option>
						<option value="90"><?php esc_html_e( 'Last 90 days', 'vms-elements-fastspring-woo-payment' ); ?></option>
						<option value="365"><?php esc_html_e( 'Last 12 months', 'vms-elements-fastspring-woo-payment' ); ?></option>
					</select>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=vms-efwp-settings' ) ); ?>">
						<?php esc_html_e( 'Settings', 'vms-elements-fastspring-woo-payment' ); ?>
					</a>
				</div>
			</div>

			<?php if ( ! $has_creds ) : ?>
				<div class="notice notice-warning"><p>
					<?php
					printf(
						/* translators: %s: settings url */
						wp_kses_post( __( 'Add your FastSpring API credentials to start syncing data. <a href="%s">Open settings</a>.', 'vms-elements-fastspring-woo-payment' ) ),
						esc_url( admin_url( 'admin.php?page=vms-efwp-settings' ) )
					);
					?>
				</p></div>
			<?php endif; ?>

			<div class="vefwp-stats" id="vefwp-kpi-cards">
				<div class="vefwp-stat" data-kpi="today">
					<div class="vefwp-stat__label"><?php esc_html_e( 'Today', 'vms-elements-fastspring-woo-payment' ); ?></div>
					<div class="vefwp-stat__value" data-kpi-value="revenue"><?php echo esc_html( $currency . number_format_i18n( (float) $today['revenue'], 2 ) ); ?></div>
					<div class="vefwp-stat__sub" data-kpi-value="orders"><?php
					/* translators: %d: number of orders today */
					echo esc_html( sprintf( _n( '%d order', '%d orders', (int) $today['orders'], 'vms-elements-fastspring-woo-payment' ), (int) $today['orders'] ) );
					?></div>
				</div>
				<div class="vefwp-stat" data-kpi="week">
					<div class="vefwp-stat__label"><?php esc_html_e( '7 days', 'vms-elements-fastspring-woo-payment' ); ?></div>
					<div class="vefwp-stat__value" data-kpi-value="revenue"><?php echo esc_html( $currency . number_format_i18n( (float) $week['revenue'], 2 ) ); ?></div>
					<div class="vefwp-stat__sub" data-kpi-value="orders"><?php
					/* translators: %d: number of orders in the last 7 days */
					echo esc_html( sprintf( _n( '%d order', '%d orders', (int) $week['orders'], 'vms-elements-fastspring-woo-payment' ), (int) $week['orders'] ) );
					?></div>
				</div>
				<div class="vefwp-stat" data-kpi="month">
					<div class="vefwp-stat__label"><?php esc_html_e( '30 days', 'vms-elements-fastspring-woo-payment' ); ?></div>
					<div class="vefwp-stat__value" data-kpi-value="revenue"><?php echo esc_html( $currency . number_format_i18n( (float) $month['revenue'], 2 ) ); ?></div>
					<div class="vefwp-stat__sub" data-kpi-value="orders"><?php
					/* translators: %d: number of orders in the last 30 days */
					echo esc_html( sprintf( _n( '%d order', '%d orders', (int) $month['orders'], 'vms-elements-fastspring-woo-payment' ), (int) $month['orders'] ) );
					?></div>
				</div>
				<div class="vefwp-stat" data-kpi="all_time">
					<div class="vefwp-stat__label"><?php esc_html_e( 'All time', 'vms-elements-fastspring-woo-payment' ); ?></div>
					<div class="vefwp-stat__value" data-kpi-value="revenue"><?php echo esc_html( $currency . number_format_i18n( (float) $all_time['revenue'], 2 ) ); ?></div>
					<div class="vefwp-stat__sub" data-kpi-value="orders"><?php
					/* translators: %d: total number of orders */
					echo esc_html( sprintf( _n( '%d order', '%d orders', (int) $all_time['orders'], 'vms-elements-fastspring-woo-payment' ), (int) $all_time['orders'] ) );
					?></div>
				</div>
				<div class="vefwp-stat vefwp-stat--accent" data-kpi="subscriptions">
					<div class="vefwp-stat__label"><?php esc_html_e( 'Active subscriptions', 'vms-elements-fastspring-woo-payment' ); ?></div>
					<div class="vefwp-stat__value" data-kpi-value="active"><?php echo esc_html( number_format_i18n( (int) $subs['active'] ) ); ?></div>
					<div class="vefwp-stat__sub" data-kpi-value="mrr">
						<?php
						$mrr_strings = array();
						foreach ( (array) $subs['mrr'] as $cur => $value ) {
							$mrr_strings[] = $cur . ' ' . number_format_i18n( $value, 2 );
						}
						echo $mrr_strings
							? esc_html( sprintf(
								/* translators: %s: formatted MRR amounts */
								__( 'MRR: %s', 'vms-elements-fastspring-woo-payment' ),
								implode( ' / ', $mrr_strings )
							) )
							: esc_html__( 'No active recurring revenue yet.', 'vms-elements-fastspring-woo-payment' );
						?>
					</div>
				</div>
				<div class="vefwp-stat" data-kpi="refunded">
					<div class="vefwp-stat__label"><?php esc_html_e( 'Refunded (all time)', 'vms-elements-fastspring-woo-payment' ); ?></div>
					<div class="vefwp-stat__value vefwp-stat__value--neg" data-kpi-value="refunded">
						<?php echo esc_html( $currency . number_format_i18n( (float) $all_time['refunded'], 2 ) ); ?>
					</div>
					<div class="vefwp-stat__sub"><?php esc_html_e( 'Across all currencies (approx.)', 'vms-elements-fastspring-woo-payment' ); ?></div>
				</div>
			</div>

			<div class="vefwp-grid">
				<div class="vefwp-card vefwp-card--wide">
					<div class="vefwp-card__head">
						<h2><?php esc_html_e( 'Revenue trend', 'vms-elements-fastspring-woo-payment' ); ?></h2>
						<span class="vefwp-spinner" id="vefwp-trend-spinner" hidden aria-hidden="true"></span>
					</div>
					<p class="vefwp-chart-error" id="vefwp-chart-error" hidden></p>
					<div class="vefwp-chart-wrap vefwp-chart-wrap--wide">
						<canvas id="vefwp-revenue-chart" aria-label="<?php esc_attr_e( 'Revenue trend chart', 'vms-elements-fastspring-woo-payment' ); ?>"></canvas>
					</div>
				</div>

				<div class="vefwp-card">
					<div class="vefwp-card__head">
						<h2><?php esc_html_e( 'Subscriptions', 'vms-elements-fastspring-woo-payment' ); ?></h2>
					</div>
					<div class="vefwp-chart-wrap">
						<canvas id="vefwp-subscription-chart" aria-label="<?php esc_attr_e( 'Subscription breakdown chart', 'vms-elements-fastspring-woo-payment' ); ?>"></canvas>
					</div>
				</div>

				<div class="vefwp-card">
					<div class="vefwp-card__head">
						<h2><?php esc_html_e( 'Top products', 'vms-elements-fastspring-woo-payment' ); ?></h2>
					</div>
					<table class="widefat striped vefwp-table" id="vefwp-top-products">
						<thead><tr>
							<th><?php esc_html_e( 'Product', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Qty', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Revenue', 'vms-elements-fastspring-woo-payment' ); ?></th>
						</tr></thead>
						<tbody></tbody>
					</table>
				</div>

				<div class="vefwp-card">
					<div class="vefwp-card__head">
						<h2><?php esc_html_e( 'Top countries', 'vms-elements-fastspring-woo-payment' ); ?></h2>
					</div>
					<table class="widefat striped vefwp-table" id="vefwp-top-countries">
						<thead><tr>
							<th><?php esc_html_e( 'Country', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Orders', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Revenue', 'vms-elements-fastspring-woo-payment' ); ?></th>
						</tr></thead>
						<tbody></tbody>
					</table>
				</div>

				<div class="vefwp-card vefwp-card--wide">
					<div class="vefwp-card__head">
						<h2><?php esc_html_e( 'Recent orders', 'vms-elements-fastspring-woo-payment' ); ?></h2>
						<a class="button button-link" href="<?php echo esc_url( admin_url( 'admin.php?page=vms-efwp-orders' ) ); ?>"><?php esc_html_e( 'View all', 'vms-elements-fastspring-woo-payment' ); ?></a>
					</div>
					<table class="widefat striped vefwp-table" id="vefwp-recent-orders">
						<thead><tr>
							<th><?php esc_html_e( 'Order', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Customer', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Total', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Status', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Date', 'vms-elements-fastspring-woo-payment' ); ?></th>
						</tr></thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}
}
