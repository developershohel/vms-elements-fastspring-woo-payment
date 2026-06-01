<?php
/**
 * Dashboard screen.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Dashboard.
 */
class WP_FastSpring_Admin_Dashboard {

	/**
	 * Render dashboard.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = wp_fastspring()->settings;
		$mode     = $settings->get_mode();
		$has_creds = $settings->has_credentials();

		$today_start = gmdate( 'Y-m-d 00:00:00' );
		$today_end   = gmdate( 'Y-m-d 23:59:59' );
		$today       = WP_FastSpring_Stats::sales_summary( $today_start, $today_end, false );

		$week_start = gmdate( 'Y-m-d 00:00:00', time() - 6 * DAY_IN_SECONDS );
		$week       = WP_FastSpring_Stats::sales_summary( $week_start, $today_end, false );

		$month_start = gmdate( 'Y-m-d 00:00:00', time() - 29 * DAY_IN_SECONDS );
		$month       = WP_FastSpring_Stats::sales_summary( $month_start, $today_end, false );

		$all_time   = WP_FastSpring_Stats::sales_summary( '1970-01-01 00:00:00', $today_end, false );
		$subs       = WP_FastSpring_Stats::subscriptions_summary( false );
		$currency   = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$';

		?>
		<div class="wrap wpfs-wrap">
			<div class="wpfs-header">
				<div class="wpfs-header__title">
					<h1><?php esc_html_e( 'FastSpring Analytics', 'wp-fastspring' ); ?></h1>
					<span class="wpfs-mode-pill wpfs-mode-pill--<?php echo esc_attr( $mode ); ?>">
						<?php echo 'live' === $mode ? esc_html__( 'LIVE', 'wp-fastspring' ) : esc_html__( 'SANDBOX', 'wp-fastspring' ); ?>
					</span>
				</div>
				<div class="wpfs-header__actions">
					<label class="wpfs-toggle">
						<input type="checkbox" id="wpfs-include-test" <?php checked( $settings->is_sandbox() ); ?> />
						<?php esc_html_e( 'Include test orders', 'wp-fastspring' ); ?>
					</label>
					<select id="wpfs-range" class="wpfs-select">
						<option value="7"><?php esc_html_e( 'Last 7 days', 'wp-fastspring' ); ?></option>
						<option value="30" selected><?php esc_html_e( 'Last 30 days', 'wp-fastspring' ); ?></option>
						<option value="90"><?php esc_html_e( 'Last 90 days', 'wp-fastspring' ); ?></option>
						<option value="365"><?php esc_html_e( 'Last 12 months', 'wp-fastspring' ); ?></option>
					</select>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=wp-fastspring-settings' ) ); ?>">
						<?php esc_html_e( 'Settings', 'wp-fastspring' ); ?>
					</a>
				</div>
			</div>

			<?php if ( ! $has_creds ) : ?>
				<div class="notice notice-warning"><p>
					<?php
					printf(
						/* translators: %s: settings url */
						wp_kses_post( __( 'Add your FastSpring API credentials to start syncing data. <a href="%s">Open settings</a>.', 'wp-fastspring' ) ),
						esc_url( admin_url( 'admin.php?page=wp-fastspring-settings' ) )
					);
					?>
				</p></div>
			<?php endif; ?>

			<div class="wpfs-stats">
				<div class="wpfs-stat">
					<div class="wpfs-stat__label"><?php esc_html_e( 'Today', 'wp-fastspring' ); ?></div>
					<div class="wpfs-stat__value"><?php echo esc_html( $currency . number_format_i18n( (float) $today['revenue'], 2 ) ); ?></div>
					<div class="wpfs-stat__sub"><?php echo esc_html( sprintf( /* translators: %d orders */ _n( '%d order', '%d orders', (int) $today['orders'], 'wp-fastspring' ), (int) $today['orders'] ) ); ?></div>
				</div>
				<div class="wpfs-stat">
					<div class="wpfs-stat__label"><?php esc_html_e( '7 days', 'wp-fastspring' ); ?></div>
					<div class="wpfs-stat__value"><?php echo esc_html( $currency . number_format_i18n( (float) $week['revenue'], 2 ) ); ?></div>
					<div class="wpfs-stat__sub"><?php echo esc_html( sprintf( _n( '%d order', '%d orders', (int) $week['orders'], 'wp-fastspring' ), (int) $week['orders'] ) ); ?></div>
				</div>
				<div class="wpfs-stat">
					<div class="wpfs-stat__label"><?php esc_html_e( '30 days', 'wp-fastspring' ); ?></div>
					<div class="wpfs-stat__value"><?php echo esc_html( $currency . number_format_i18n( (float) $month['revenue'], 2 ) ); ?></div>
					<div class="wpfs-stat__sub"><?php echo esc_html( sprintf( _n( '%d order', '%d orders', (int) $month['orders'], 'wp-fastspring' ), (int) $month['orders'] ) ); ?></div>
				</div>
				<div class="wpfs-stat">
					<div class="wpfs-stat__label"><?php esc_html_e( 'All time', 'wp-fastspring' ); ?></div>
					<div class="wpfs-stat__value"><?php echo esc_html( $currency . number_format_i18n( (float) $all_time['revenue'], 2 ) ); ?></div>
					<div class="wpfs-stat__sub"><?php echo esc_html( sprintf( _n( '%d order', '%d orders', (int) $all_time['orders'], 'wp-fastspring' ), (int) $all_time['orders'] ) ); ?></div>
				</div>
				<div class="wpfs-stat wpfs-stat--accent">
					<div class="wpfs-stat__label"><?php esc_html_e( 'Active subscriptions', 'wp-fastspring' ); ?></div>
					<div class="wpfs-stat__value"><?php echo esc_html( number_format_i18n( (int) $subs['active'] ) ); ?></div>
					<div class="wpfs-stat__sub">
						<?php
						$mrr_strings = array();
						foreach ( (array) $subs['mrr'] as $cur => $value ) {
							$mrr_strings[] = $cur . ' ' . number_format_i18n( $value, 2 );
						}
						echo $mrr_strings
							? esc_html( sprintf( __( 'MRR: %s', 'wp-fastspring' ), implode( ' / ', $mrr_strings ) ) )
							: esc_html__( 'No active recurring revenue yet.', 'wp-fastspring' );
						?>
					</div>
				</div>
				<div class="wpfs-stat">
					<div class="wpfs-stat__label"><?php esc_html_e( 'Refunded (all time)', 'wp-fastspring' ); ?></div>
					<div class="wpfs-stat__value wpfs-stat__value--neg">
						<?php echo esc_html( $currency . number_format_i18n( (float) $all_time['refunded'], 2 ) ); ?>
					</div>
					<div class="wpfs-stat__sub"><?php esc_html_e( 'Across all currencies (approx.)', 'wp-fastspring' ); ?></div>
				</div>
			</div>

			<div class="wpfs-grid">
				<div class="wpfs-card wpfs-card--wide">
					<div class="wpfs-card__head">
						<h2><?php esc_html_e( 'Revenue trend', 'wp-fastspring' ); ?></h2>
						<span class="wpfs-spinner" id="wpfs-trend-spinner" hidden></span>
					</div>
					<canvas id="wpfs-revenue-chart" height="110"></canvas>
				</div>

				<div class="wpfs-card">
					<div class="wpfs-card__head">
						<h2><?php esc_html_e( 'Subscriptions', 'wp-fastspring' ); ?></h2>
					</div>
					<canvas id="wpfs-subscription-chart" height="220"></canvas>
				</div>

				<div class="wpfs-card">
					<div class="wpfs-card__head">
						<h2><?php esc_html_e( 'Top products', 'wp-fastspring' ); ?></h2>
					</div>
					<table class="widefat striped wpfs-table" id="wpfs-top-products">
						<thead><tr>
							<th><?php esc_html_e( 'Product', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Qty', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Revenue', 'wp-fastspring' ); ?></th>
						</tr></thead>
						<tbody></tbody>
					</table>
				</div>

				<div class="wpfs-card">
					<div class="wpfs-card__head">
						<h2><?php esc_html_e( 'Top countries', 'wp-fastspring' ); ?></h2>
					</div>
					<table class="widefat striped wpfs-table" id="wpfs-top-countries">
						<thead><tr>
							<th><?php esc_html_e( 'Country', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Orders', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Revenue', 'wp-fastspring' ); ?></th>
						</tr></thead>
						<tbody></tbody>
					</table>
				</div>

				<div class="wpfs-card wpfs-card--wide">
					<div class="wpfs-card__head">
						<h2><?php esc_html_e( 'Recent orders', 'wp-fastspring' ); ?></h2>
						<a class="button button-link" href="<?php echo esc_url( admin_url( 'admin.php?page=wp-fastspring-orders' ) ); ?>"><?php esc_html_e( 'View all', 'wp-fastspring' ); ?></a>
					</div>
					<table class="widefat striped wpfs-table" id="wpfs-recent-orders">
						<thead><tr>
							<th><?php esc_html_e( 'Order', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Customer', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Total', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Status', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Date', 'wp-fastspring' ); ?></th>
						</tr></thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}
}
