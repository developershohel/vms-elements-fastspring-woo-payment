<?php
/**
 * Aggregations used by the admin dashboard.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Stats.
 */
class WP_FastSpring_Stats {

	/**
	 * Build the orders table name.
	 *
	 * @return string
	 */
	private static function orders_table() {
		global $wpdb;
		return $wpdb->prefix . 'fastspring_orders';
	}

	/**
	 * Build the subscriptions table name.
	 *
	 * @return string
	 */
	private static function subscriptions_table() {
		global $wpdb;
		return $wpdb->prefix . 'fastspring_subscriptions';
	}

	/**
	 * Sum of orders for a given period (UTC).
	 *
	 * @param string $start ISO date.
	 * @param string $end   ISO date.
	 * @param bool   $include_test Whether to include test orders.
	 * @return array
	 */
	public static function sales_summary( $start, $end, $include_test = false ) {
		global $wpdb;
		$table = self::orders_table();

		$test_where = $include_test ? '' : ' AND is_test = 0';

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					COUNT(*) AS orders,
					COALESCE(SUM(total),0) AS revenue,
					COALESCE(SUM(tax),0) AS tax,
					COALESCE(SUM(discount),0) AS discount,
					COALESCE(SUM(CASE WHEN status='refunded' THEN total ELSE 0 END),0) AS refunded
				FROM $table
				WHERE created_at >= %s AND created_at <= %s
				AND status NOT IN ('cancelled')
				$test_where",
				$start,
				$end
			),
			ARRAY_A
		);

		return $row ? $row : array(
			'orders'   => 0,
			'revenue'  => 0,
			'tax'      => 0,
			'discount' => 0,
			'refunded' => 0,
		);
	}

	/**
	 * Daily revenue for the last N days.
	 *
	 * @param int  $days Number of days.
	 * @param bool $include_test Include test data?
	 * @return array Array of [ 'date' => 'YYYY-MM-DD', 'revenue' => float, 'orders' => int ].
	 */
	public static function daily_revenue( $days = 30, $include_test = false ) {
		global $wpdb;
		$table = self::orders_table();

		$days       = max( 1, min( 365, (int) $days ) );
		$test_where = $include_test ? '' : ' AND is_test = 0';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE(created_at) AS date, COUNT(*) AS orders, COALESCE(SUM(total),0) AS revenue
				 FROM $table
				 WHERE created_at >= (NOW() - INTERVAL %d DAY)
				 AND status NOT IN ('cancelled','refunded')
				 $test_where
				 GROUP BY DATE(created_at)
				 ORDER BY date ASC",
				$days
			),
			ARRAY_A
		);

		$by_date = array();
		foreach ( (array) $rows as $r ) {
			$by_date[ $r['date'] ] = $r;
		}

		$result = array();
		for ( $i = $days - 1; $i >= 0; $i-- ) {
			$d = gmdate( 'Y-m-d', time() - ( $i * DAY_IN_SECONDS ) );
			if ( isset( $by_date[ $d ] ) ) {
				$result[] = array(
					'date'    => $d,
					'orders'  => (int) $by_date[ $d ]['orders'],
					'revenue' => (float) $by_date[ $d ]['revenue'],
				);
			} else {
				$result[] = array( 'date' => $d, 'orders' => 0, 'revenue' => 0.0 );
			}
		}
		return $result;
	}

	/**
	 * Top products by revenue.
	 *
	 * @param int  $limit Limit.
	 * @param bool $include_test Include test orders?
	 * @return array
	 */
	public static function top_products( $limit = 5, $include_test = false ) {
		global $wpdb;
		$table = self::orders_table();
		$limit = max( 1, min( 50, (int) $limit ) );

		$test_where = $include_test ? '' : ' AND is_test = 0';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT payload, total FROM $table WHERE 1=1 $test_where ORDER BY created_at DESC LIMIT 1000",
				array()
			),
			ARRAY_A
		);

		$products = array();
		foreach ( (array) $rows as $r ) {
			$payload = json_decode( $r['payload'], true );
			if ( ! is_array( $payload ) ) {
				continue;
			}
			$items = isset( $payload['items'] ) ? (array) $payload['items'] : array();
			foreach ( $items as $item ) {
				$key = isset( $item['product'] ) ? $item['product'] : ( isset( $item['display'] ) ? $item['display'] : 'unknown' );
				if ( ! isset( $products[ $key ] ) ) {
					$products[ $key ] = array(
						'product'  => $key,
						'display'  => isset( $item['display'] ) ? $item['display'] : $key,
						'quantity' => 0,
						'revenue'  => 0.0,
					);
				}
				$products[ $key ]['quantity'] += isset( $item['quantity'] ) ? (int) $item['quantity'] : 1;
				$products[ $key ]['revenue']  += isset( $item['subtotal'] ) ? (float) $item['subtotal'] : 0;
			}
		}

		usort(
			$products,
			static function ( $a, $b ) {
				if ( $a['revenue'] === $b['revenue'] ) {
					return 0;
				}
				return ( $a['revenue'] < $b['revenue'] ) ? 1 : -1;
			}
		);
		return array_slice( array_values( $products ), 0, $limit );
	}

	/**
	 * Subscription summary (active, paused, cancelled, MRR).
	 *
	 * @param bool $include_test Include test data?
	 * @return array
	 */
	public static function subscriptions_summary( $include_test = false ) {
		global $wpdb;
		$table = self::subscriptions_table();
		$test_where = $include_test ? '' : ' AND is_test = 0';

		$counts = $wpdb->get_results(
			"SELECT status, COUNT(*) AS total FROM $table WHERE 1=1 $test_where GROUP BY status",
			ARRAY_A
		);

		$out = array(
			'active'    => 0,
			'paused'    => 0,
			'overdue'   => 0,
			'canceled'  => 0,
			'deactivated' => 0,
			'trial'     => 0,
		);
		foreach ( (array) $counts as $row ) {
			$out[ $row['status'] ] = (int) $row['total'];
		}

		// Approximate MRR using active subscription prices (normalized to monthly).
		$active = $wpdb->get_results(
			"SELECT price, currency, interval_unit, interval_length FROM $table WHERE status='active' $test_where",
			ARRAY_A
		);

		$mrr_by_currency = array();
		foreach ( (array) $active as $sub ) {
			$months = self::interval_to_months( $sub['interval_unit'], (int) $sub['interval_length'] );
			if ( $months <= 0 ) {
				continue;
			}
			$cur = $sub['currency'] ? $sub['currency'] : 'USD';
			$mrr_by_currency[ $cur ] = ( isset( $mrr_by_currency[ $cur ] ) ? $mrr_by_currency[ $cur ] : 0 ) + ( (float) $sub['price'] / $months );
		}

		$out['mrr'] = $mrr_by_currency;
		return $out;
	}

	/**
	 * Convert FastSpring interval (day/week/month/year) into months.
	 *
	 * @param string $unit   Unit.
	 * @param int    $length Length.
	 * @return float
	 */
	private static function interval_to_months( $unit, $length ) {
		$length = max( 1, (int) $length );
		switch ( strtolower( (string) $unit ) ) {
			case 'day':
				return ( $length / 30 );
			case 'week':
				return ( $length * 7 / 30 );
			case 'month':
				return $length;
			case 'year':
				return $length * 12;
		}
		return $length; // fall back to assuming months.
	}

	/**
	 * Recent orders for dashboard.
	 *
	 * @param int $limit Limit.
	 * @return array
	 */
	public static function recent_orders( $limit = 10 ) {
		global $wpdb;
		$table = self::orders_table();
		$limit = max( 1, min( 50, (int) $limit ) );
		$rows  = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table ORDER BY created_at DESC LIMIT %d", $limit ),
			ARRAY_A
		);
		return $rows ? $rows : array();
	}

	/**
	 * Country breakdown.
	 *
	 * @param int  $limit Limit.
	 * @param bool $include_test Test mode?
	 * @return array
	 */
	public static function top_countries( $limit = 5, $include_test = false ) {
		global $wpdb;
		$table = self::orders_table();
		$limit = max( 1, min( 50, (int) $limit ) );
		$test_where = $include_test ? '' : ' AND is_test = 0';
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT country, COUNT(*) AS orders, COALESCE(SUM(total),0) AS revenue
				 FROM $table
				 WHERE country IS NOT NULL AND country != ''
				 $test_where
				 GROUP BY country
				 ORDER BY revenue DESC
				 LIMIT %d",
				$limit
			),
			ARRAY_A
		);
		return $rows ? $rows : array();
	}
}
