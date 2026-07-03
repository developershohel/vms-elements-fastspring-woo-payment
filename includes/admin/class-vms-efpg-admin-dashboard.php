<?php
/**
 * Dashboard screen.
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFPG_Admin_Dashboard.
 */
class VMS_EFPG_Admin_Dashboard {

	/**
	 * Render dashboard.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings  = vms_efpg()->settings;
		$mode      = $settings->get_mode();
		$has_creds = $settings->has_credentials();

		VMS_EFPG_Data_Store::sync_site_orders();
		$site_context = VMS_EFPG_Data_Store::get_site_context();

		$today_start = gmdate( 'Y-m-d 00:00:00' );
		$today_end   = gmdate( 'Y-m-d 23:59:59' );
		$today       = VMS_EFPG_Stats::sales_summary( $today_start, $today_end, false );

		$week_start = gmdate( 'Y-m-d 00:00:00', time() - 6 * DAY_IN_SECONDS );
		$week       = VMS_EFPG_Stats::sales_summary( $week_start, $today_end, false );

		$month_start = gmdate( 'Y-m-d 00:00:00', time() - 29 * DAY_IN_SECONDS );
		$month       = VMS_EFPG_Stats::sales_summary( $month_start, $today_end, false );

		$all_time   = VMS_EFPG_Stats::sales_summary( '1970-01-01 00:00:00', $today_end, false );
		$subs       = VMS_EFPG_Stats::subscriptions_summary( false );
		$currency   = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$';

		?>
		<div class="wrap vms-efpg-wrap">
			<div class="vms-efpg-header">
				<div class="vms-efpg-header__title">
					<div class="vms-efpg-header__title-group">
						<h1><?php esc_html_e( 'FastSpring Analytics', 'vms-elements-fastspring-payment-gateway' ); ?></h1>
						<span class="vms-efpg-mode-pill vms-efpg-mode-pill--<?php echo esc_attr( $mode ); ?>">
							<?php echo 'live' === $mode ? esc_html__( 'LIVE', 'vms-elements-fastspring-payment-gateway' ) : esc_html__( 'SANDBOX', 'vms-elements-fastspring-payment-gateway' ); ?>
						</span>
					</div>
					<p class="vms-efpg-site-context">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: site name, 2: site URL, 3: stored order count */
								__( 'Site: %1$s — %2$s (%3$d orders)', 'vms-elements-fastspring-payment-gateway' ),
								$site_context['site_name'],
								$site_context['site_url'],
								(int) $site_context['stored_orders']
							)
						);
						?>
					</p>
				</div>
				<div class="vms-efpg-header__actions">
					<label class="vms-efpg-toggle">
						<input type="checkbox" id="vms-efpg-include-test" <?php checked( $settings->is_sandbox() ); ?> />
						<?php esc_html_e( 'Include test orders', 'vms-elements-fastspring-payment-gateway' ); ?>
					</label>
					<select id="vms-efpg-range" class="vms-efpg-select">
						<option value="7"><?php esc_html_e( 'Last 7 days', 'vms-elements-fastspring-payment-gateway' ); ?></option>
						<option value="30" selected><?php esc_html_e( 'Last 30 days', 'vms-elements-fastspring-payment-gateway' ); ?></option>
						<option value="90"><?php esc_html_e( 'Last 90 days', 'vms-elements-fastspring-payment-gateway' ); ?></option>
						<option value="365"><?php esc_html_e( 'Last 12 months', 'vms-elements-fastspring-payment-gateway' ); ?></option>
					</select>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=vms-efpg-settings' ) ); ?>">
						<?php esc_html_e( 'Settings', 'vms-elements-fastspring-payment-gateway' ); ?>
					</a>
				</div>
			</div>

			<?php if ( ! $has_creds ) : ?>
				<div class="notice notice-warning"><p>
					<?php
					printf(
						/* translators: %s: settings url */
						wp_kses_post( __( 'Add your FastSpring API credentials to start syncing data. <a href="%s">Open settings</a>.', 'vms-elements-fastspring-payment-gateway' ) ),
						esc_url( admin_url( 'admin.php?page=vms-efpg-settings' ) )
					);
					?>
				</p></div>
			<?php endif; ?>

			<div class="vms-efpg-stats" id="vms-efpg-kpi-cards">
				<div class="vms-efpg-stat" data-kpi="today">
					<div class="vms-efpg-stat__label"><?php esc_html_e( 'Today', 'vms-elements-fastspring-payment-gateway' ); ?></div>
					<div class="vms-efpg-stat__value" data-kpi-value="revenue"><?php echo esc_html( $currency . number_format_i18n( (float) $today['revenue'], 2 ) ); ?></div>
					<div class="vms-efpg-stat__sub" data-kpi-value="orders"><?php
					/* translators: %d: number of orders today */
					echo esc_html( sprintf( _n( '%d order', '%d orders', (int) $today['orders'], 'vms-elements-fastspring-payment-gateway' ), (int) $today['orders'] ) );
					?></div>
				</div>
				<div class="vms-efpg-stat" data-kpi="week">
					<div class="vms-efpg-stat__label"><?php esc_html_e( '7 days', 'vms-elements-fastspring-payment-gateway' ); ?></div>
					<div class="vms-efpg-stat__value" data-kpi-value="revenue"><?php echo esc_html( $currency . number_format_i18n( (float) $week['revenue'], 2 ) ); ?></div>
					<div class="vms-efpg-stat__sub" data-kpi-value="orders"><?php
					/* translators: %d: number of orders in the last 7 days */
					echo esc_html( sprintf( _n( '%d order', '%d orders', (int) $week['orders'], 'vms-elements-fastspring-payment-gateway' ), (int) $week['orders'] ) );
					?></div>
				</div>
				<div class="vms-efpg-stat" data-kpi="month">
					<div class="vms-efpg-stat__label"><?php esc_html_e( '30 days', 'vms-elements-fastspring-payment-gateway' ); ?></div>
					<div class="vms-efpg-stat__value" data-kpi-value="revenue"><?php echo esc_html( $currency . number_format_i18n( (float) $month['revenue'], 2 ) ); ?></div>
					<div class="vms-efpg-stat__sub" data-kpi-value="orders"><?php
					/* translators: %d: number of orders in the last 30 days */
					echo esc_html( sprintf( _n( '%d order', '%d orders', (int) $month['orders'], 'vms-elements-fastspring-payment-gateway' ), (int) $month['orders'] ) );
					?></div>
				</div>
				<div class="vms-efpg-stat" data-kpi="all_time">
					<div class="vms-efpg-stat__label"><?php esc_html_e( 'All time', 'vms-elements-fastspring-payment-gateway' ); ?></div>
					<div class="vms-efpg-stat__value" data-kpi-value="revenue"><?php echo esc_html( $currency . number_format_i18n( (float) $all_time['revenue'], 2 ) ); ?></div>
					<div class="vms-efpg-stat__sub" data-kpi-value="orders"><?php
					/* translators: %d: total number of orders */
					echo esc_html( sprintf( _n( '%d order', '%d orders', (int) $all_time['orders'], 'vms-elements-fastspring-payment-gateway' ), (int) $all_time['orders'] ) );
					?></div>
				</div>
				<div class="vms-efpg-stat vms-efpg-stat--accent" data-kpi="subscriptions">
					<div class="vms-efpg-stat__label"><?php esc_html_e( 'Active subscriptions', 'vms-elements-fastspring-payment-gateway' ); ?></div>
					<div class="vms-efpg-stat__value" data-kpi-value="active"><?php echo esc_html( number_format_i18n( (int) $subs['active'] ) ); ?></div>
					<div class="vms-efpg-stat__sub" data-kpi-value="mrr">
						<?php
						$mrr_strings = array();
						foreach ( (array) $subs['mrr'] as $cur => $value ) {
							$mrr_strings[] = $cur . ' ' . number_format_i18n( $value, 2 );
						}
						echo $mrr_strings
							? esc_html( sprintf(
								/* translators: %s: formatted MRR amounts */
								__( 'MRR: %s', 'vms-elements-fastspring-payment-gateway' ),
								implode( ' / ', $mrr_strings )
							) )
							: esc_html__( 'No active recurring revenue yet.', 'vms-elements-fastspring-payment-gateway' );
						?>
					</div>
				</div>
				<div class="vms-efpg-stat" data-kpi="refunded">
					<div class="vms-efpg-stat__label"><?php esc_html_e( 'Refunded (all time)', 'vms-elements-fastspring-payment-gateway' ); ?></div>
					<div class="vms-efpg-stat__value vms-efpg-stat__value--neg" data-kpi-value="refunded">
						<?php echo esc_html( $currency . number_format_i18n( (float) $all_time['refunded'], 2 ) ); ?>
					</div>
					<div class="vms-efpg-stat__sub"><?php esc_html_e( 'Across all currencies (approx.)', 'vms-elements-fastspring-payment-gateway' ); ?></div>
				</div>
			</div>

			<div class="vms-efpg-grid">
				<div class="vms-efpg-card vms-efpg-card--wide">
					<div class="vms-efpg-card__head">
						<h2><?php esc_html_e( 'Revenue trend', 'vms-elements-fastspring-payment-gateway' ); ?></h2>
						<span class="vms-efpg-spinner" id="vms-efpg-trend-spinner" hidden aria-hidden="true"></span>
					</div>
					<p class="vms-efpg-chart-error" id="vms-efpg-chart-error" hidden></p>
					<div class="vms-efpg-chart-wrap vms-efpg-chart-wrap--wide">
						<canvas id="vms-efpg-revenue-chart" aria-label="<?php esc_attr_e( 'Revenue trend chart', 'vms-elements-fastspring-payment-gateway' ); ?>"></canvas>
					</div>
				</div>

				<div class="vms-efpg-card">
					<div class="vms-efpg-card__head">
						<h2><?php esc_html_e( 'Subscriptions', 'vms-elements-fastspring-payment-gateway' ); ?></h2>
					</div>
					<div class="vms-efpg-chart-wrap">
						<canvas id="vms-efpg-subscription-chart" aria-label="<?php esc_attr_e( 'Subscription breakdown chart', 'vms-elements-fastspring-payment-gateway' ); ?>"></canvas>
					</div>
				</div>

				<div class="vms-efpg-card">
					<div class="vms-efpg-card__head">
						<h2><?php esc_html_e( 'Top products', 'vms-elements-fastspring-payment-gateway' ); ?></h2>
					</div>
					<table class="widefat striped vms-efpg-table" id="vms-efpg-top-products">
						<thead><tr>
							<th><?php esc_html_e( 'Product', 'vms-elements-fastspring-payment-gateway' ); ?></th>
							<th><?php esc_html_e( 'Qty', 'vms-elements-fastspring-payment-gateway' ); ?></th>
							<th><?php esc_html_e( 'Revenue', 'vms-elements-fastspring-payment-gateway' ); ?></th>
						</tr></thead>
						<tbody></tbody>
					</table>
				</div>

				<div class="vms-efpg-card">
					<div class="vms-efpg-card__head">
						<h2><?php esc_html_e( 'Top countries', 'vms-elements-fastspring-payment-gateway' ); ?></h2>
					</div>
					<table class="widefat striped vms-efpg-table" id="vms-efpg-top-countries">
						<thead><tr>
							<th><?php esc_html_e( 'Country', 'vms-elements-fastspring-payment-gateway' ); ?></th>
							<th><?php esc_html_e( 'Orders', 'vms-elements-fastspring-payment-gateway' ); ?></th>
							<th><?php esc_html_e( 'Revenue', 'vms-elements-fastspring-payment-gateway' ); ?></th>
						</tr></thead>
						<tbody></tbody>
					</table>
				</div>

				<div class="vms-efpg-card vms-efpg-card--wide">
					<div class="vms-efpg-card__head">
						<h2><?php esc_html_e( 'Recent orders', 'vms-elements-fastspring-payment-gateway' ); ?></h2>
						<a class="button button-link" href="<?php echo esc_url( admin_url( 'admin.php?page=vms-efpg-orders' ) ); ?>"><?php esc_html_e( 'View all', 'vms-elements-fastspring-payment-gateway' ); ?></a>
					</div>
					<table class="widefat striped vms-efpg-table" id="vms-efpg-recent-orders">
						<thead><tr>
							<th><?php esc_html_e( 'Order', 'vms-elements-fastspring-payment-gateway' ); ?></th>
							<th><?php esc_html_e( 'Customer', 'vms-elements-fastspring-payment-gateway' ); ?></th>
							<th><?php esc_html_e( 'Total', 'vms-elements-fastspring-payment-gateway' ); ?></th>
							<th><?php esc_html_e( 'Status', 'vms-elements-fastspring-payment-gateway' ); ?></th>
							<th><?php esc_html_e( 'Date', 'vms-elements-fastspring-payment-gateway' ); ?></th>
						</tr></thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}
}
