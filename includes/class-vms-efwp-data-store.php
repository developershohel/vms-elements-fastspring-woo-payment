<?php
/**
 * Persistence layer for FastSpring orders, subscriptions, events.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Data_Store.
 */
class VMS_EFWP_Data_Store {

	/**
	 * Hook in custom post types if/when needed in future versions.
	 */
	public static function register_post_types() {
		// Reserved for future structured data via CPT.
	}

	/* -------------------------------------------------------------------- *
	 * Orders
	 * -------------------------------------------------------------------- */

	/**
	 * Insert or update an order from a FastSpring payload.
	 *
	 * @param array $payload FastSpring order payload (events `order.completed` etc).
	 * @param bool  $is_test Whether the event was a test order.
	 * @return int|false Inserted/updated row ID.
	 */
	public static function upsert_order( $payload, $is_test = false ) {
		global $wpdb;
		$table = VMS_EFWP_Install::table_name( 'orders' );

		$fs_order_id = isset( $payload['id'] ) ? $payload['id'] : ( isset( $payload['order'] ) ? $payload['order'] : '' );
		if ( empty( $fs_order_id ) ) {
			return false;
		}

		$now = current_time( 'mysql', true );

		$customer = isset( $payload['customer'] ) ? $payload['customer'] : array();
		$email    = $customer['email'] ?? ( $payload['email'] ?? '' );
		$first    = $customer['first'] ?? $customer['firstName'] ?? '';
		$last     = $customer['last'] ?? $customer['lastName'] ?? '';

		if ( ! empty( $payload['completed'] ) ) {
			$status = 'completed';
		} elseif ( ! empty( $payload['canceled'] ) ) {
			$status = 'canceled';
		} elseif ( ! empty( $payload['status'] ) ) {
			$status = sanitize_key( (string) $payload['status'] );
		} else {
			$status = 'pending';
		}

		$data = array(
			'fs_order_id'    => $fs_order_id,
			'fs_reference'   => isset( $payload['reference'] ) ? $payload['reference'] : null,
			'wc_order_id'    => isset( $payload['tags']['wc_order_id'] ) ? (int) $payload['tags']['wc_order_id'] : null,
			'email'          => $email,
			'customer_name'  => trim( $first . ' ' . $last ),
			'currency'       => isset( $payload['currency'] ) ? $payload['currency'] : null,
			'total'          => isset( $payload['total'] ) ? (float) $payload['total'] : 0,
			'tax'            => isset( $payload['tax'] ) ? (float) $payload['tax'] : 0,
			'subtotal'       => isset( $payload['subtotal'] ) ? (float) $payload['subtotal'] : 0,
			'discount'       => isset( $payload['discount'] ) ? (float) $payload['discount'] : 0,
			'status'         => $status,
			'payment_method' => isset( $payload['payment']['type'] ) ? $payload['payment']['type'] : null,
			'country'        => isset( $payload['address']['country'] ) ? $payload['address']['country'] : null,
			'is_test'        => $is_test ? 1 : 0,
			'payload'        => wp_json_encode( $payload ),
			'updated_at'     => $now,
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table from VMS_EFWP_Install::table_name().
		$existing_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE fs_order_id = %s", $fs_order_id ) );
		if ( $existing_id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$wpdb->update( $table, $data, array( 'id' => $existing_id ) );
			return (int) $existing_id;
		}

		$data['created_at'] = $now;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$wpdb->insert( $table, $data );
		return (int) $wpdb->insert_id;
	}

	/**
	 * Mark an order refunded.
	 *
	 * @param string $fs_order_id ID.
	 */
	public static function mark_order_refunded( $fs_order_id ) {
		global $wpdb;
		$table = VMS_EFWP_Install::table_name( 'orders' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$wpdb->update(
			$table,
			array( 'status' => 'refunded', 'updated_at' => current_time( 'mysql', true ) ),
			array( 'fs_order_id' => $fs_order_id )
		);
	}

	/**
	 * Get orders.
	 *
	 * @param array $args Query args.
	 * @return array
	 */
	public static function get_orders( $args = array() ) {
		global $wpdb;
		$table = VMS_EFWP_Install::table_name( 'orders' );

		$args = wp_parse_args(
			$args,
			array(
				'per_page' => 20,
				'page'     => 1,
				'status'   => '',
				'search'   => '',
				'orderby'  => 'created_at',
				'order'    => 'DESC',
			)
		);

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}
		if ( ! empty( $args['search'] ) ) {
			$where[]  = '(email LIKE %s OR customer_name LIKE %s OR fs_order_id LIKE %s OR fs_reference LIKE %s)';
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$orderby = in_array( $args['orderby'], array( 'created_at', 'total', 'email' ), true ) ? $args['orderby'] : 'created_at';
		$order   = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
		$offset  = max( 0, ( ( (int) $args['page'] - 1 ) * (int) $args['per_page'] ) );
		$where_sql = implode( ' AND ', $where );
		$list_params = array_merge( $params, array( (int) $args['per_page'], $offset ) );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
				...$list_params
			),
			ARRAY_A
		);

		$count_params = $params;
		if ( $count_params ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$total = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} WHERE {$where_sql}",
					...$count_params
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE 1=1" );
		}
		// phpcs:enable

		return array(
			'rows'  => $rows ? $rows : array(),
			'total' => $total,
		);
	}

	/* -------------------------------------------------------------------- *
	 * Subscriptions
	 * -------------------------------------------------------------------- */

	/**
	 * Insert/update subscription from FastSpring payload.
	 *
	 * @param array $payload Payload.
	 * @param bool  $is_test Test mode.
	 * @return int|false
	 */
	public static function upsert_subscription( $payload, $is_test = false ) {
		global $wpdb;
		$table = VMS_EFWP_Install::table_name( 'subscriptions' );

		$id = isset( $payload['id'] ) ? $payload['id'] : ( isset( $payload['subscription'] ) ? $payload['subscription'] : '' );
		if ( empty( $id ) ) {
			return false;
		}

		$now = current_time( 'mysql', true );

		$data = array(
			'fs_subscription_id' => $id,
			'fs_account_id'      => isset( $payload['account'] ) ? $payload['account'] : null,
			'email'              => $payload['customer']['email'] ?? $payload['email'] ?? null,
			'product'            => isset( $payload['product'] ) ? $payload['product'] : null,
			'currency'           => isset( $payload['currency'] ) ? $payload['currency'] : null,
			'price'              => isset( $payload['price'] ) ? (float) $payload['price'] : 0,
			'interval_unit'      => isset( $payload['intervalUnit'] ) ? $payload['intervalUnit'] : null,
			'interval_length'    => isset( $payload['intervalLength'] ) ? (int) $payload['intervalLength'] : 1,
			'status'             => isset( $payload['state'] ) ? $payload['state'] : ( isset( $payload['status'] ) ? $payload['status'] : 'active' ),
			'next_charge'        => isset( $payload['nextChargeDate'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $payload['nextChargeDate'] ) ) : null,
			'begin_date'         => isset( $payload['begin'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $payload['begin'] ) ) : null,
			'end_date'           => isset( $payload['end'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $payload['end'] ) ) : null,
			'is_test'            => $is_test ? 1 : 0,
			'payload'            => wp_json_encode( $payload ),
			'updated_at'         => $now,
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$existing_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE fs_subscription_id = %s", $id ) );
		if ( $existing_id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$wpdb->update( $table, $data, array( 'id' => $existing_id ) );
			return (int) $existing_id;
		}

		$data['created_at'] = $now;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$wpdb->insert( $table, $data );
		return (int) $wpdb->insert_id;
	}

	/**
	 * Update subscription status.
	 *
	 * @param string $fs_subscription_id ID.
	 * @param string $status             Status.
	 */
	public static function set_subscription_status( $fs_subscription_id, $status ) {
		global $wpdb;
		$table = VMS_EFWP_Install::table_name( 'subscriptions' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$wpdb->update(
			$table,
			array( 'status' => $status, 'updated_at' => current_time( 'mysql', true ) ),
			array( 'fs_subscription_id' => $fs_subscription_id )
		);
	}

	/**
	 * Get subscriptions.
	 *
	 * @param array $args Args.
	 * @return array
	 */
	public static function get_subscriptions( $args = array() ) {
		global $wpdb;
		$table = VMS_EFWP_Install::table_name( 'subscriptions' );

		$args = wp_parse_args(
			$args,
			array(
				'per_page' => 20,
				'page'     => 1,
				'status'   => '',
				'search'   => '',
			)
		);

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}
		if ( ! empty( $args['search'] ) ) {
			$where[]  = '(email LIKE %s OR product LIKE %s OR fs_subscription_id LIKE %s)';
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$offset = max( 0, ( ( (int) $args['page'] - 1 ) * (int) $args['per_page'] ) );
		$where_sql = implode( ' AND ', $where );
		$list_params = array_merge( $params, array( (int) $args['per_page'], $offset ) );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE {$where_sql} ORDER BY updated_at DESC LIMIT %d OFFSET %d",
				...$list_params
			),
			ARRAY_A
		);

		$count_params = $params;
		if ( $count_params ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$total = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} WHERE {$where_sql}",
					...$count_params
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE 1=1" );
		}
		// phpcs:enable

		return array(
			'rows'  => $rows ? $rows : array(),
			'total' => $total,
		);
	}

	/* -------------------------------------------------------------------- *
	 * Events
	 * -------------------------------------------------------------------- */

	/**
	 * Record a webhook event.
	 *
	 * @param array $event   Event payload.
	 * @param bool  $is_live Whether this was a live event.
	 * @return int|false
	 */
	public static function record_event( $event, $is_live = true ) {
		global $wpdb;
		$table = VMS_EFWP_Install::table_name( 'events' );

		$event_id = isset( $event['id'] ) ? $event['id'] : '';
		if ( empty( $event_id ) ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE event_id = %s", $event_id ) );
		if ( $existing ) {
			return (int) $existing;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$wpdb->insert(
			$table,
			array(
				'event_id'   => $event_id,
				'event_type' => isset( $event['type'] ) ? $event['type'] : 'unknown',
				'processed'  => 0,
				'live'       => $is_live ? 1 : 0,
				'payload'    => wp_json_encode( $event ),
				'created_at' => current_time( 'mysql', true ),
			)
		);
		return (int) $wpdb->insert_id;
	}

	/**
	 * Mark an event processed.
	 *
	 * @param string      $event_id ID.
	 * @param string|null $error    Error message, or null on success.
	 */
	public static function mark_event_processed( $event_id, $error = null ) {
		global $wpdb;
		$table = VMS_EFWP_Install::table_name( 'events' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$wpdb->update(
			$table,
			array(
				'processed'     => $error ? 0 : 1,
				'error_message' => $error,
				'processed_at'  => current_time( 'mysql', true ),
			),
			array( 'event_id' => $event_id )
		);
	}
}
