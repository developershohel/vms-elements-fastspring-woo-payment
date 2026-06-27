<?php
/**
 * Persistence layer for FastSpring orders, subscriptions, events.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table names from VMS_EFWP_Install::table_name().

/**
 * Class VMS_EFWP_Data_Store.
 */
class VMS_EFWP_Data_Store {

	/**
	 * Orders site-scope WHERE fragment (single %s = normalized site URL).
	 */
	public const ORDERS_SITE_SCOPE_WHERE = "(
		LOWER(REPLACE(REPLACE(site_url, 'https://', 'http://'), 'www.', '')) = LOWER(REPLACE(REPLACE(%s, 'https://', 'http://'), 'www.', ''))
		OR (
			(site_url IS NULL OR site_url = '')
			AND wc_order_id IS NOT NULL
			AND wc_order_id > 0
		)
	)";

	/**
	 * Subscriptions site-scope WHERE fragment (single %s = normalized site URL).
	 */
	public const SUBSCRIPTIONS_SITE_SCOPE_WHERE = "(
		LOWER(REPLACE(REPLACE(site_url, 'https://', 'http://'), 'www.', '')) = LOWER(REPLACE(REPLACE(%s, 'https://', 'http://'), 'www.', ''))
		OR (
			(site_url IS NULL OR site_url = '')
			AND wc_user_id IS NOT NULL
			AND wc_user_id > 0
		)
	)";

	/**
	 * Hook in custom post types if/when needed in future versions.
	 */
	public static function register_post_types() {
		// Reserved for future structured data via CPT.
	}

	/* -------------------------------------------------------------------- *
	 * Site / user scope helpers
	 * -------------------------------------------------------------------- */

	/**
	 * Normalized site URL for scoping stored FastSpring records to one WordPress site.
	 *
	 * @param string $url Raw URL.
	 * @return string
	 */
	public static function normalize_site_url( $url ) {
		$url = untrailingslashit( esc_url_raw( (string) $url ) );
		return $url;
	}

	/**
	 * Current WordPress site URL used to tag FastSpring sessions and filter admin data.
	 *
	 * @return string
	 */
	public static function get_site_url() {
		return self::normalize_site_url( home_url() );
	}

	/**
	 * Build FastSpring session tags linking checkout to this site and WordPress user.
	 *
	 * @param int|WC_Order $order WooCommerce order or order ID.
	 * @return array<string, string>
	 */
	public static function build_session_tags( $order ) {
		if ( is_numeric( $order ) && function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( (int) $order );
		}

		$tags = array(
			'site_url' => self::get_site_url(),
		);

		if ( ! $order instanceof WC_Order ) {
			return $tags;
		}

		$tags['wc_order_id'] = (string) $order->get_id();

		$user_id = (int) $order->get_user_id();
		if ( $user_id > 0 ) {
			$tags['wc_user_id'] = (string) $user_id;
		}

		return $tags;
	}

	/**
	 * Read wc_user_id from a FastSpring payload tag or linked WooCommerce order.
	 *
	 * @param array $payload      FastSpring payload.
	 * @param int   $wc_order_id  Optional known WooCommerce order ID.
	 * @return int
	 */
	public static function resolve_wc_user_id_from_payload( $payload, $wc_order_id = 0 ) {
		if ( ! is_array( $payload ) ) {
			$payload = array();
		}

		$tags = isset( $payload['tags'] ) && is_array( $payload['tags'] ) ? $payload['tags'] : array();
		if ( ! empty( $tags['wc_user_id'] ) ) {
			return max( 0, (int) $tags['wc_user_id'] );
		}

		if ( ! $wc_order_id && ! empty( $tags['wc_order_id'] ) ) {
			$wc_order_id = (int) $tags['wc_order_id'];
		}
		if ( ! $wc_order_id && ! empty( $payload['reference'] ) ) {
			$wc_order_id = (int) preg_replace( '/[^0-9]/', '', (string) $payload['reference'] );
		}

		if ( $wc_order_id && function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( $wc_order_id );
			if ( $order ) {
				return max( 0, (int) $order->get_user_id() );
			}
		}

		return 0;
	}

	/**
	 * Read site_url from a FastSpring payload tag.
	 *
	 * @param array $payload FastSpring payload.
	 * @return string
	 */
	public static function resolve_site_url_from_payload( $payload ) {
		if ( ! is_array( $payload ) ) {
			return '';
		}

		$tags = isset( $payload['tags'] ) && is_array( $payload['tags'] ) ? $payload['tags'] : array();
		if ( empty( $tags['site_url'] ) ) {
			return '';
		}

		return self::normalize_site_url( (string) $tags['site_url'] );
	}

	/**
	 * Normalize a site URL for loose comparison (http/https, optional www).
	 *
	 * @param string $url Raw URL.
	 * @return string
	 */
	public static function normalize_site_url_for_compare( $url ) {
		$url = strtolower( self::normalize_site_url( $url ) );
		$url = str_replace( 'https://', 'http://', $url );
		return preg_replace( '#^http://www\.#', 'http://', $url );
	}

	/**
	 * Whether two site URLs refer to the same WordPress site.
	 *
	 * @param string $a First URL.
	 * @param string $b Second URL.
	 * @return bool
	 */
	public static function site_urls_equivalent( $a, $b ) {
		return self::normalize_site_url_for_compare( $a ) === self::normalize_site_url_for_compare( $b );
	}

	/**
	 * Read wc_order_id from a FastSpring payload tag or reference.
	 *
	 * @param array $payload FastSpring payload.
	 * @return int
	 */
	public static function resolve_wc_order_id_from_payload( $payload ) {
		if ( ! is_array( $payload ) ) {
			return 0;
		}

		$tags = isset( $payload['tags'] ) && is_array( $payload['tags'] ) ? $payload['tags'] : array();
		if ( ! empty( $tags['wc_order_id'] ) ) {
			return max( 0, (int) $tags['wc_order_id'] );
		}

		if ( ! empty( $payload['reference'] ) ) {
			return max( 0, (int) preg_replace( '/[^0-9]/', '', (string) $payload['reference'] ) );
		}

		return 0;
	}

	/**
	 * Whether a WooCommerce order belongs to this site and was created via FastSpring checkout.
	 *
	 * @param int $wc_order_id WooCommerce order ID.
	 * @return bool
	 */
	public static function is_local_vms_efwp_wc_order( $wc_order_id ) {
		$wc_order_id = (int) $wc_order_id;
		if ( ! $wc_order_id || ! function_exists( 'wc_get_order' ) ) {
			return false;
		}

		$order = wc_get_order( $wc_order_id );
		if ( ! $order || 'vms_efwp' !== $order->get_payment_method() ) {
			return false;
		}

		return (bool) $order->get_meta( '_vms_efwp_session_id' ) || (bool) $order->get_transaction_id();
	}

	/**
	 * Whether a FastSpring order payload belongs to the current WordPress site.
	 *
	 * Uses session tags (`site_url`, `wc_order_id`). Untagged legacy orders are treated as local
	 * only when they reference a FastSpring WooCommerce order on this install.
	 *
	 * @param array  $payload  FastSpring order payload.
	 * @param string $site_url Optional site URL override.
	 * @return bool
	 */
	public static function order_belongs_to_site( $payload, $site_url = '' ) {
		return self::should_persist_for_site( $payload, $site_url );
	}

	/**
	 * Whether a FastSpring payload should be stored or shown on this WordPress site.
	 *
	 * @param array  $payload  FastSpring order or subscription payload.
	 * @param string $site_url Optional site URL override.
	 * @return bool
	 */
	public static function should_persist_for_site( $payload, $site_url = '' ) {
		if ( ! is_array( $payload ) ) {
			return false;
		}

		$site_url = $site_url ? $site_url : self::get_site_url();
		$tag_site = self::resolve_site_url_from_payload( $payload );

		if ( $tag_site ) {
			return self::site_urls_equivalent( $tag_site, $site_url );
		}

		return self::is_local_vms_efwp_wc_order( self::resolve_wc_order_id_from_payload( $payload ) );
	}

	/**
	 * Ensure site and WooCommerce tags are present before persisting a FastSpring payload.
	 *
	 * @param array $payload     FastSpring payload.
	 * @param int   $wc_order_id Optional known WooCommerce order ID.
	 * @return array
	 */
	public static function prepare_payload_for_site( array $payload, $wc_order_id = 0 ) {
		if ( ! isset( $payload['tags'] ) || ! is_array( $payload['tags'] ) ) {
			$payload['tags'] = array();
		}

		if ( ! $wc_order_id ) {
			$wc_order_id = self::resolve_wc_order_id_from_payload( $payload );
		}

		if ( ! self::is_local_vms_efwp_wc_order( $wc_order_id ) ) {
			return $payload;
		}

		if ( empty( $payload['tags']['site_url'] ) ) {
			$payload['tags']['site_url'] = self::get_site_url();
		}
		if ( empty( $payload['tags']['wc_order_id'] ) ) {
			$payload['tags']['wc_order_id'] = (string) $wc_order_id;
		}

		$user_id = self::resolve_wc_user_id_from_payload( $payload, $wc_order_id );
		if ( $user_id && empty( $payload['tags']['wc_user_id'] ) ) {
			$payload['tags']['wc_user_id'] = (string) $user_id;
		}

		return $payload;
	}

	/**
	 * SQL fragment for site-scoped order queries.
	 *
	 * @param string $column Column name.
	 * @return array{sql:string,params:array}
	 */
	public static function orders_site_scope_sql( $column = 'site_url' ) {
		$column = preg_replace( '/[^a-z_]/', '', (string) $column );
		if ( '' === $column ) {
			$column = 'site_url';
		}

		if ( 'site_url' === $column ) {
			return array(
				'sql'    => self::ORDERS_SITE_SCOPE_WHERE,
				'params' => array( self::get_site_url() ),
			);
		}

		$compare = "LOWER(REPLACE(REPLACE({$column}, 'https://', 'http://'), 'www.', ''))";

		return array(
			'sql'    => "(
				{$compare} = LOWER(REPLACE(REPLACE(%s, 'https://', 'http://'), 'www.', ''))
				OR (
					({$column} IS NULL OR {$column} = '')
					AND wc_order_id IS NOT NULL
					AND wc_order_id > 0
				)
			)",
			'params' => array( self::get_site_url() ),
		);
	}

	/**
	 * SQL fragment for site-scoped subscription queries.
	 *
	 * Subscriptions are tagged with site_url and wc_user_id (no wc_order_id column).
	 *
	 * @param string $column Column name.
	 * @return array{sql:string,params:array}
	 */
	public static function subscriptions_site_scope_sql( $column = 'site_url' ) {
		$column = preg_replace( '/[^a-z_]/', '', (string) $column );
		if ( '' === $column ) {
			$column = 'site_url';
		}

		if ( 'site_url' === $column ) {
			return array(
				'sql'    => self::SUBSCRIPTIONS_SITE_SCOPE_WHERE,
				'params' => array( self::get_site_url() ),
			);
		}

		$compare = "LOWER(REPLACE(REPLACE({$column}, 'https://', 'http://'), 'www.', ''))";

		return array(
			'sql'    => "(
				{$compare} = LOWER(REPLACE(REPLACE(%s, 'https://', 'http://'), 'www.', ''))
				OR (
					({$column} IS NULL OR {$column} = '')
					AND wc_user_id IS NOT NULL
					AND wc_user_id > 0
				)
			)",
			'params' => array( self::get_site_url() ),
		);
	}

	/**
	 * SQL fragment for site-scoped queries (defaults to orders).
	 *
	 * @param string $column  Column name.
	 * @param string $context Table context: orders or subscriptions.
	 * @return array{sql:string,params:array}
	 */
	public static function site_scope_sql( $column = 'site_url', $context = 'orders' ) {
		if ( 'subscriptions' === $context ) {
			return self::subscriptions_site_scope_sql( $column );
		}

		return self::orders_site_scope_sql( $column );
	}

	/**
	 * @deprecated 1.0.0 Use site_scope_sql().
	 * @param string $column Column name.
	 * @return array{sql:string,params:array}
	 */
	public static function admin_site_scope_sql( $column = 'site_url' ) {
		return self::site_scope_sql( $column );
	}

	/**
	 * Site context shown on the admin dashboard and orders screen.
	 *
	 * @return array{site_url:string,site_name:string,stored_orders:int,wc_orders:int}
	 */
	public static function get_site_context() {
		global $wpdb;

		$table = VMS_EFWP_Install::table_name( 'orders' );
		$scope = self::orders_site_scope_sql( 'site_url' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$stored = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE {$scope['sql']}",
				...$scope['params']
			)
		);

		$wc_count = 0;
		if ( function_exists( 'wc_get_orders' ) ) {
			$wc_count = count(
				wc_get_orders(
					array(
						'limit'          => -1,
						'return'         => 'ids',
						'payment_method' => 'vms_efwp',
						'status'         => array_keys( wc_get_order_statuses() ),
					)
				)
			);
		}

		return array(
			'site_url'       => self::get_site_url(),
			'site_name'      => get_bloginfo( 'name' ),
			'stored_orders'  => $stored,
			'wc_orders'      => $wc_count,
		);
	}

	/**
	 * Ensure locally stored orders include every FastSpring payment on this site.
	 *
	 * Pulls missing orders from WooCommerce transaction IDs and, when API credentials exist,
	 * paginates FastSpring /orders and saves rows tagged with this site's URL.
	 *
	 * @param bool $force Skip the throttle transient.
	 * @return array{synced:int,skipped:bool,errors:int}
	 */
	public static function sync_site_orders( $force = false ) {
		$transient_key = 'vms_efwp_site_orders_sync';
		if ( ! $force && get_transient( $transient_key ) ) {
			return array(
				'synced'   => 0,
				'skipped'  => true,
				'errors'   => 0,
			);
		}

		$synced = 0;
		$errors = 0;
		$site   = self::get_site_url();
		$now    = current_time( 'mysql', true );

		global $wpdb;
		$table = VMS_EFWP_Install::table_name( 'orders' );
		if ( $table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table} SET site_url = %s, updated_at = %s
					 WHERE wc_order_id IS NOT NULL AND wc_order_id > 0
					 AND (site_url IS NULL OR site_url = '')",
					$site,
					$now
				)
			);
		}

		if ( function_exists( 'wc_get_orders' ) && function_exists( 'vms_efwp' ) && vms_efwp()->api ) {
			$wc_ids = wc_get_orders(
				array(
					'limit'          => 500,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'return'         => 'ids',
					'payment_method' => 'vms_efwp',
					'status'         => array_keys( wc_get_order_statuses() ),
				)
			);

			foreach ( (array) $wc_ids as $wc_id ) {
				$order = wc_get_order( $wc_id );
				if ( ! $order ) {
					continue;
				}

				$fs_id = (string) $order->get_transaction_id();
				if ( '' === $fs_id ) {
					continue;
				}

				if ( self::get_order_by_fs_id( $fs_id ) ) {
					continue;
				}

				$raw = vms_efwp()->api->get_order( $fs_id );
				if ( is_wp_error( $raw ) ) {
					++$errors;
					continue;
				}

				$parsed = vms_efwp()->api->parse_order( $raw );
				if ( is_wp_error( $parsed ) ) {
					++$errors;
					continue;
				}

				$parsed = self::prepare_payload_for_site( $parsed, $wc_id );
				if ( ! self::should_persist_for_site( $parsed, $site ) ) {
					continue;
				}

				$is_test = ! empty( $parsed['test'] ) || ! empty( $parsed['isTest'] );
				$is_live = isset( $parsed['live'] ) ? (bool) $parsed['live'] : (
					function_exists( 'vms_efwp' ) && vms_efwp()->settings && ! vms_efwp()->settings->is_sandbox()
				);
				if ( ! $is_live ) {
					$is_test = true;
				}

				if ( false !== self::upsert_order( $parsed, $is_test ) ) {
					++$synced;
				}
			}
		}

		$settings = function_exists( 'vms_efwp' ) ? vms_efwp()->settings : null;
		if ( $settings && $settings->has_credentials() && function_exists( 'vms_efwp' ) && vms_efwp()->api ) {
			$api   = vms_efwp()->api;
			$begin = gmdate( 'Y-m-d', strtotime( '-365 days' ) );
			$end   = gmdate( 'Y-m-d' );
			$page  = 1;

			do {
				$result = $api->list_orders(
					array(
						'begin' => $begin,
						'end'   => $end,
						'page'  => $page,
						'limit' => 50,
					)
				);

				if ( is_wp_error( $result ) ) {
					++$errors;
					break;
				}

				if ( ! empty( $result['orders'] ) && is_array( $result['orders'] ) && is_array( $result['orders'][0] ?? null ) ) {
					$orders = $result['orders'];
				} else {
					$orders = $api->hydrate_orders( $api->extract_order_ids( $result ) );
				}

				if ( empty( $orders ) ) {
					break;
				}

				foreach ( $orders as $order_payload ) {
					$parsed = self::prepare_payload_for_site( $order_payload );
					if ( ! self::should_persist_for_site( $parsed, $site ) ) {
						continue;
					}

					$fs_id = isset( $parsed['id'] ) ? (string) $parsed['id'] : '';
					if ( ! $fs_id || self::get_order_by_fs_id( $fs_id ) ) {
						continue;
					}

					$is_test = ! empty( $parsed['test'] ) || ! empty( $parsed['isTest'] );
					$is_live = isset( $parsed['live'] ) ? (bool) $parsed['live'] : ! $settings->is_sandbox();
					if ( ! $is_live ) {
						$is_test = true;
					}

					if ( false !== self::upsert_order( $parsed, $is_test ) ) {
						++$synced;
					}
				}

				$next = ! empty( $result['nextPage'] );
				++$page;
			} while ( $next && $page <= 20 );
		}

		set_transient( $transient_key, 1, 2 * MINUTE_IN_SECONDS );

		return array(
			'synced'  => $synced,
			'skipped' => false,
			'errors'  => $errors,
		);
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

		if ( ! is_array( $payload ) ) {
			return false;
		}

		$payload = self::prepare_payload_for_site( $payload );
		if ( ! self::should_persist_for_site( $payload ) ) {
			return false;
		}

		if ( ! empty( $payload['completed'] ) && function_exists( 'vms_efwp' ) && vms_efwp()->api ) {
			$payload = vms_efwp()->api->ensure_order_invoice( $payload );
		}

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

		$invoice_meta = self::resolve_order_invoice_meta( $payload );

		$wc_order_id = self::resolve_wc_order_id_from_payload( $payload );
		$wc_user_id  = self::resolve_wc_user_id_from_payload( $payload, $wc_order_id );
		$site_url    = self::resolve_site_url_from_payload( $payload );
		if ( ! $site_url ) {
			$site_url = self::get_site_url();
		}
		$account_id  = self::extract_account_id_from_payload( $payload );

		$data = array(
			'fs_order_id'    => $fs_order_id,
			'fs_reference'   => isset( $payload['reference'] ) ? $payload['reference'] : null,
			'wc_order_id'    => $wc_order_id ? $wc_order_id : null,
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
			'fs_invoice_id'  => $invoice_meta['fs_invoice_id'],
			'invoice_url'    => $invoice_meta['invoice_url'],
			'is_test'        => $is_test ? 1 : 0,
			'payload'        => wp_json_encode( $payload ),
			'updated_at'     => $now,
		);

		if ( $wc_user_id ) {
			$data['wc_user_id'] = $wc_user_id;
		}
		if ( $account_id ) {
			$data['fs_account_id'] = $account_id;
		}
		if ( $site_url ) {
			$data['site_url'] = $site_url;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table from VMS_EFWP_Install::table_name().
		$existing_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE fs_order_id = %s", $fs_order_id ) );
		if ( $existing_id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$existing_site = $wpdb->get_var(
				$wpdb->prepare( "SELECT site_url FROM {$table} WHERE id = %d", (int) $existing_id )
			);
			if ( $existing_site && ! self::site_urls_equivalent( (string) $existing_site, self::get_site_url() ) ) {
				return false;
			}

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
	 * Resolve invoice metadata from an order payload.
	 *
	 * @param array $payload FastSpring order payload.
	 * @return array{invoice_url:?string,fs_invoice_id:?string}
	 */
	public static function resolve_order_invoice_meta( $payload ) {
		if ( function_exists( 'vms_efwp' ) && vms_efwp()->api ) {
			return vms_efwp()->api->extract_order_invoice_meta( $payload );
		}

		return array(
			'invoice_url'   => null,
			'fs_invoice_id' => null,
		);
	}

	/**
	 * Read invoice metadata from a stored order row.
	 *
	 * @param array $row Order row from get_orders().
	 * @return array{invoice_url:?string,fs_invoice_id:?string}
	 */
	public static function get_order_invoice_meta( $row ) {
		$invoice_url   = ! empty( $row['invoice_url'] ) ? (string) $row['invoice_url'] : null;
		$fs_invoice_id = ! empty( $row['fs_invoice_id'] ) ? (string) $row['fs_invoice_id'] : null;

		if ( $invoice_url || $fs_invoice_id ) {
			return array(
				'invoice_url'   => $invoice_url,
				'fs_invoice_id' => $fs_invoice_id,
			);
		}

		if ( empty( $row['payload'] ) ) {
			return array(
				'invoice_url'   => null,
				'fs_invoice_id' => null,
			);
		}

		$payload = json_decode( (string) $row['payload'], true );
		if ( ! is_array( $payload ) ) {
			return array(
				'invoice_url'   => null,
				'fs_invoice_id' => null,
			);
		}

		return self::resolve_order_invoice_meta( $payload );
	}

	/**
	 * Display-friendly invoice identifiers for an stored order row.
	 *
	 * Checkout receipts usually only have an order ID plus invoiceUrl. A separate
	 * payment invoice ID appears only for some URL formats and B2B invoices.
	 *
	 * @param array $row Order row from get_orders().
	 * @return array{
	 *     invoice_url:?string,
	 *     payment_invoice_id:?string,
	 *     order_id:string,
	 *     order_reference:string,
	 *     receipt_reference:?string,
	 *     lookup_id:string
	 * }
	 */
	public static function get_order_invoice_display( $row ) {
		$meta = self::maybe_sync_order_invoice_columns( $row );

		$order_id       = (string) ( $row['fs_order_id'] ?? '' );
		$order_reference = (string) ( $row['fs_reference'] ?? '' );
		$invoice_url    = $meta['invoice_url'] ?? null;
		$payment_id     = $meta['fs_invoice_id'] ?? null;
		$receipt_ref    = null;

		if ( $invoice_url && preg_match( '#/order/([^/]+)/invoice#i', (string) $invoice_url, $matches ) ) {
			$receipt_ref = (string) $matches[1];
		}

		return array(
			'invoice_url'          => $invoice_url,
			'payment_invoice_id'   => $payment_id,
			'order_id'             => $order_id,
			'order_reference'      => $order_reference,
			'receipt_reference'    => $receipt_ref,
			'lookup_id'            => $payment_id ? (string) $payment_id : $order_id,
		);
	}

	/**
	 * Persist invoice columns when payload already contains invoiceUrl.
	 *
	 * @param array $row Order row.
	 * @return array{invoice_url:?string,fs_invoice_id:?string}
	 */
	public static function maybe_sync_order_invoice_columns( $row ) {
		$meta = self::get_order_invoice_meta( $row );

		if ( ( ! empty( $row['invoice_url'] ) || ! empty( $row['fs_invoice_id'] ) ) || ( empty( $meta['invoice_url'] ) && empty( $meta['fs_invoice_id'] ) ) ) {
			return $meta;
		}

		global $wpdb;
		$table = VMS_EFWP_Install::table_name( 'orders' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$wpdb->update(
			$table,
			array(
				'fs_invoice_id' => $meta['fs_invoice_id'],
				'invoice_url'   => $meta['invoice_url'],
				'updated_at'    => current_time( 'mysql', true ),
			),
			array( 'id' => (int) $row['id'] )
		);

		return $meta;
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
				'per_page'    => 20,
				'page'        => 1,
				'status'      => '',
				'search'      => '',
				'has_invoice' => false,
				'wc_user_id'  => 0,
				'site_url'    => '',
				'scope_site'  => false,
				'orderby'     => 'created_at',
				'order'       => 'DESC',
			)
		);

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['wc_user_id'] ) ) {
			$where[]  = 'wc_user_id = %d';
			$params[] = (int) $args['wc_user_id'];
		}

		if ( ! empty( $args['site_url'] ) ) {
			$scope = self::orders_site_scope_sql( 'site_url' );
			$where[] = $scope['sql'];
			$params  = array_merge( $params, $scope['params'] );
		} elseif ( ! empty( $args['scope_site'] ) ) {
			$scope = self::orders_site_scope_sql( 'site_url' );
			$where[] = $scope['sql'];
			$params  = array_merge( $params, $scope['params'] );
		}

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}
		if ( ! empty( $args['has_invoice'] ) ) {
			$where[] = "(invoice_url IS NOT NULL AND invoice_url != '' OR fs_invoice_id IS NOT NULL AND fs_invoice_id != '')";
		}
		if ( ! empty( $args['search'] ) ) {
			$where[]  = '(email LIKE %s OR customer_name LIKE %s OR fs_order_id LIKE %s OR fs_reference LIKE %s OR fs_invoice_id LIKE %s)';
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$params[] = $like;
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
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter

		return array(
			'rows'  => $rows ? $rows : array(),
			'total' => $total,
		);
	}

	/**
	 * Get FastSpring orders for a WordPress user on this site.
	 *
	 * @param int   $user_id WordPress user ID.
	 * @param array $args    Optional query args.
	 * @return array
	 */
	public static function get_orders_for_user( $user_id, $args = array() ) {
		$user_id = (int) $user_id;
		if ( ! $user_id ) {
			return array(
				'rows'  => array(),
				'total' => 0,
			);
		}

		$args['wc_user_id'] = $user_id;
		$args['site_url']   = self::get_site_url();

		return self::get_orders( $args );
	}

	/**
	 * Get FastSpring subscriptions for a WordPress user on this site.
	 *
	 * @param int   $user_id WordPress user ID.
	 * @param array $args    Optional query args.
	 * @return array
	 */
	public static function get_subscriptions_for_user( $user_id, $args = array() ) {
		$user_id = (int) $user_id;
		if ( ! $user_id ) {
			return array(
				'rows'  => array(),
				'total' => 0,
			);
		}

		$args['wc_user_id'] = $user_id;
		$args['site_url']   = self::get_site_url();

		return self::get_subscriptions( $args );
	}

	/**
	 * Fetch a stored order row by FastSpring order ID.
	 *
	 * @param string $fs_order_id Order ID.
	 * @return array|null
	 */
	public static function get_order_by_fs_id( $fs_order_id ) {
		global $wpdb;

		$fs_order_id = sanitize_text_field( (string) $fs_order_id );
		if ( '' === $fs_order_id ) {
			return null;
		}

		$table = VMS_EFWP_Install::table_name( 'orders' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE fs_order_id = %s", $fs_order_id ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			ARRAY_A
		);

		return $row ? $row : null;
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
	public static function upsert_subscription( $payload, $is_test = false, $wc_user_id = 0 ) {
		global $wpdb;
		$table = VMS_EFWP_Install::table_name( 'subscriptions' );

		if ( ! is_array( $payload ) ) {
			return false;
		}

		$payload = self::prepare_payload_for_site( $payload );
		if ( ! self::should_persist_for_site( $payload ) ) {
			return false;
		}

		$id = isset( $payload['id'] ) ? $payload['id'] : ( isset( $payload['subscription'] ) ? $payload['subscription'] : '' );
		if ( empty( $id ) ) {
			return false;
		}

		$now = current_time( 'mysql', true );

		$account_id = self::extract_account_id_from_payload( $payload );

		if ( ! $wc_user_id ) {
			$wc_user_id = self::resolve_wc_user_id_from_payload( $payload );
		}

		$site_url = self::resolve_site_url_from_payload( $payload );
		if ( ! $site_url ) {
			$site_url = self::get_site_url();
		}

		$data = array(
			'fs_subscription_id' => $id,
			'fs_account_id'      => $account_id ? $account_id : null,
			'email'              => self::resolve_subscription_email( $payload ),
			'product'            => isset( $payload['product'] ) ? $payload['product'] : null,
			'currency'           => isset( $payload['currency'] ) ? $payload['currency'] : null,
			'price'              => isset( $payload['price'] ) ? (float) $payload['price'] : 0,
			'interval_unit'      => isset( $payload['intervalUnit'] ) ? $payload['intervalUnit'] : null,
			'interval_length'    => isset( $payload['intervalLength'] ) ? (int) $payload['intervalLength'] : 1,
			'status'             => isset( $payload['state'] ) ? $payload['state'] : ( isset( $payload['status'] ) ? $payload['status'] : 'active' ),
			'next_charge'        => self::parse_payload_datetime( $payload, 'nextChargeDate', array( 'next' ) ),
			'begin_date'         => self::parse_payload_datetime( $payload, 'begin' ),
			'end_date'           => self::parse_payload_datetime( $payload, 'end' ),
			'is_test'            => $is_test ? 1 : 0,
			'payload'            => wp_json_encode( $payload ),
			'updated_at'         => $now,
		);

		if ( $wc_user_id ) {
			$data['wc_user_id'] = (int) $wc_user_id;
		}
		if ( $site_url ) {
			$data['site_url'] = $site_url;
		}

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
	 * Resolve a customer email from a subscription payload.
	 *
	 * @param array $payload Subscription payload.
	 * @return string|null
	 */
	public static function extract_account_id_from_payload( $payload ) {
		if ( function_exists( 'vms_efwp' ) && vms_efwp()->api ) {
			return vms_efwp()->api->extract_account_id_from_payload( $payload );
		}

		if ( ! is_array( $payload ) || empty( $payload['account'] ) ) {
			return '';
		}

		if ( is_string( $payload['account'] ) ) {
			return sanitize_text_field( $payload['account'] );
		}

		if ( is_array( $payload['account'] ) ) {
			$id = $payload['account']['id'] ?? $payload['account']['account'] ?? '';
			return $id ? sanitize_text_field( (string) $id ) : '';
		}

		return '';
	}

	/**
	 * Collect FastSpring account IDs linked to customer emails from local records.
	 *
	 * @param string[] $emails Customer emails.
	 * @return string[]
	 */
	public static function get_account_ids_for_emails( $emails ) {
		global $wpdb;

		$normalized = array();
		foreach ( (array) $emails as $email ) {
			$email = sanitize_email( (string) $email );
			if ( $email && is_email( $email ) ) {
				$normalized[] = strtolower( $email );
			}
		}
		$normalized = array_values( array_unique( $normalized ) );
		if ( empty( $normalized ) ) {
			return array();
		}

		$ids            = array();
		$sub_table      = VMS_EFWP_Install::table_name( 'subscriptions' );
		$order_table    = VMS_EFWP_Install::table_name( 'orders' );
		$placeholders   = implode( ', ', array_fill( 0, count( $normalized ), '%s' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$subscription_accounts = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT fs_account_id FROM {$sub_table} WHERE fs_account_id IS NOT NULL AND fs_account_id != '' AND LOWER(email) IN ({$placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter
				...$normalized
			)
		);
		if ( $subscription_accounts ) {
			$ids = array_merge( $ids, $subscription_accounts );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$order_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT payload FROM {$order_table} WHERE LOWER(email) IN ({$placeholders}) ORDER BY updated_at DESC LIMIT 200", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter
				...$normalized
			),
			ARRAY_A
		);
		foreach ( (array) $order_rows as $row ) {
			$payload = ! empty( $row['payload'] ) ? json_decode( (string) $row['payload'], true ) : null;
			if ( ! is_array( $payload ) ) {
				continue;
			}
			$account_id = self::extract_account_id_from_payload( $payload );
			if ( $account_id ) {
				$ids[] = $account_id;
			}
		}

		return array_values( array_unique( array_filter( $ids ) ) );
	}

	/**
	 * Collect FastSpring account IDs from subscriptions linked to a WordPress user.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return string[]
	 */
	public static function get_account_ids_for_user( $user_id ) {
		global $wpdb;

		$user_id = (int) $user_id;
		if ( ! $user_id ) {
			return array();
		}

		$table = VMS_EFWP_Install::table_name( 'subscriptions' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT fs_account_id FROM {$table} WHERE wc_user_id = %d AND fs_account_id IS NOT NULL AND fs_account_id != ''", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$user_id
			)
		);

		$orders_table = VMS_EFWP_Install::table_name( 'orders' );
		if ( $orders_table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$order_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT fs_account_id FROM {$orders_table} WHERE wc_user_id = %d AND fs_account_id IS NOT NULL AND fs_account_id != ''", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$user_id
				)
			);
			$ids = array_merge( (array) $ids, (array) $order_ids );
		}

		return array_values( array_unique( array_filter( (array) $ids ) ) );
	}

	/**
	 * Parse a FastSpring date field group into a MySQL UTC datetime string.
	 *
	 * @param array    $payload        Payload array.
	 * @param string   $base_field     Base field name.
	 * @param string[] $fallback_bases Alternate base field names.
	 * @return string|null
	 */
	public static function parse_payload_datetime( $payload, $base_field, $fallback_bases = array() ) {
		if ( function_exists( 'vms_efwp' ) && vms_efwp()->api ) {
			return vms_efwp()->api->parse_payload_datetime( $payload, $base_field, $fallback_bases );
		}

		if ( ! is_array( $payload ) ) {
			return null;
		}

		$bases = array_merge( array( $base_field ), (array) $fallback_bases );
		foreach ( $bases as $base ) {
			$seconds_key = $base . 'InSeconds';
			if ( isset( $payload[ $seconds_key ] ) && is_numeric( $payload[ $seconds_key ] ) && (int) $payload[ $seconds_key ] > 0 ) {
				return gmdate( 'Y-m-d H:i:s', (int) $payload[ $seconds_key ] );
			}

			$iso_key = $base . 'DisplayISO8601';
			if ( ! empty( $payload[ $iso_key ] ) && is_string( $payload[ $iso_key ] ) ) {
				$timestamp = strtotime( $payload[ $iso_key ] . ' UTC' );
				if ( $timestamp ) {
					return gmdate( 'Y-m-d H:i:s', $timestamp );
				}
			}

			foreach ( array( $base, $base . 'Value' ) as $key ) {
				if ( ! isset( $payload[ $key ] ) || $payload[ $key ] === '' || $payload[ $key ] === null || ! is_numeric( $payload[ $key ] ) ) {
					continue;
				}

				$timestamp = (int) $payload[ $key ];
				if ( $timestamp > 9999999999 ) {
					$timestamp = (int) floor( $timestamp / 1000 );
				}
				if ( $timestamp > 0 ) {
					return gmdate( 'Y-m-d H:i:s', $timestamp );
				}
			}
		}

		return null;
	}

	/**
	 * Resolve a customer email from a subscription payload.
	 *
	 * @param array $payload Subscription payload.
	 * @return string|null
	 */
	public static function resolve_subscription_email( $payload ) {
		if ( ! is_array( $payload ) ) {
			return null;
		}

		$candidates = array(
			$payload['customer']['email'] ?? '',
			$payload['email'] ?? '',
			$payload['account']['contact']['email'] ?? '',
			$payload['contact']['email'] ?? '',
		);

		foreach ( $candidates as $email ) {
			$email = sanitize_email( (string) $email );
			if ( $email && is_email( $email ) ) {
				return $email;
			}
		}

		return null;
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
	 * Get a subscription row by FastSpring subscription ID.
	 *
	 * @param string $fs_subscription_id Subscription ID.
	 * @return array|null
	 */
	public static function get_subscription_by_fs_id( $fs_subscription_id ) {
		global $wpdb;
		$table = VMS_EFWP_Install::table_name( 'subscriptions' );

		if ( ! $fs_subscription_id ) {
			return null;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE fs_subscription_id = %s LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$fs_subscription_id
			),
			ARRAY_A
		);

		return $row ? $row : null;
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
				'per_page'   => 20,
				'page'       => 1,
				'status'     => '',
				'search'     => '',
				'email'      => '',
				'emails'     => array(),
				'wc_user_id' => 0,
				'site_url'   => '',
				'scope_site' => false,
			)
		);

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['wc_user_id'] ) ) {
			$where[]  = 'wc_user_id = %d';
			$params[] = (int) $args['wc_user_id'];
		} elseif ( ! empty( $args['email'] ) || ! empty( $args['emails'] ) ) {
			$email_filters = array();
			if ( ! empty( $args['emails'] ) && is_array( $args['emails'] ) ) {
				foreach ( $args['emails'] as $email ) {
					$email = sanitize_email( (string) $email );
					if ( $email && is_email( $email ) ) {
						$email_filters[] = strtolower( $email );
					}
				}
				$email_filters = array_values( array_unique( $email_filters ) );
			} elseif ( ! empty( $args['email'] ) && is_email( $args['email'] ) ) {
				$email_filters[] = strtolower( sanitize_email( $args['email'] ) );
			}

			if ( ! empty( $email_filters ) ) {
				$placeholders = implode( ', ', array_fill( 0, count( $email_filters ), '%s' ) );
				$where[]      = "LOWER(email) IN ({$placeholders})";
				$params       = array_merge( $params, $email_filters );
			}
		}

		if ( ! empty( $args['site_url'] ) ) {
			$scope = self::subscriptions_site_scope_sql( 'site_url' );
			$where[] = $scope['sql'];
			$params  = array_merge( $params, $scope['params'] );
		} elseif ( ! empty( $args['scope_site'] ) ) {
			$scope = self::subscriptions_site_scope_sql( 'site_url' );
			$where[] = $scope['sql'];
			$params  = array_merge( $params, $scope['params'] );
		}
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
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter

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

// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter
