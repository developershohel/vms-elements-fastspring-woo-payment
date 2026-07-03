<?php
/**
 * Installation routines.
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table names from VMS_EFPG_Install::table_name().

/**
 * Class VMS_EFPG_Install.
 */
class VMS_EFPG_Install {

	/**
	 * Allowed custom table keys.
	 *
	 * @var array<string, string>
	 */
	private static $tables = array(
		'orders'        => 'vms_efpg_orders',
		'subscriptions' => 'vms_efpg_subscriptions',
		'events'        => 'vms_efpg_events',
		'log'           => 'vms_efpg_log',
	);

	/**
	 * Return a prefixed custom table name.
	 *
	 * @param string $key Table key (orders, subscriptions, events, log).
	 * @return string
	 */
	public static function table_name( $key ) {
		global $wpdb;

		if ( ! isset( self::$tables[ $key ] ) ) {
			return '';
		}

		return $wpdb->prefix . self::$tables[ $key ];
	}

	/**
	 * Run installation.
	 */
	public static function install() {
		require_once VMS_EFPG_PATH . 'includes/class-vms-efpg-migrate.php';
		VMS_EFPG_Migrate::maybe_run();
		self::create_tables();
		self::create_default_options();
	}

	/**
	 * Backfill invoice columns once per plugin version when the API client is available.
	 */
	public static function maybe_backfill_order_invoices() {
		if ( get_option( 'vms_efpg_order_invoice_backfill', '' ) === VMS_EFPG_VERSION ) {
			return;
		}

		self::backfill_order_invoice_fields();
		update_option( 'vms_efpg_order_invoice_backfill', VMS_EFPG_VERSION, false );
	}

	/**
	 * Backfill wc_user_id, fs_account_id, and site_url on stored orders and subscriptions.
	 */
	public static function maybe_backfill_user_scope() {
		if ( get_option( 'vms_efpg_user_scope_backfill', '' ) === '2' ) {
			return;
		}

		self::backfill_user_scope_fields();
		update_option( 'vms_efpg_user_scope_backfill', '2', false );
	}

	/**
	 * Ensure custom tables exist and include any new columns (safe to run every request).
	 */
	public static function ensure_schema() {
		self::create_tables();
	}

	/**
	 * Upgrade database schema when the plugin version increases.
	 */
	public static function maybe_upgrade() {
		self::ensure_schema();

		$installed = get_option( 'vms_efpg_db_version', '0' );
		if ( version_compare( (string) $installed, VMS_EFPG_VERSION, '>=' ) ) {
			return;
		}

		require_once VMS_EFPG_PATH . 'includes/class-vms-efpg-migrate.php';
		VMS_EFPG_Migrate::maybe_run();
		self::create_default_options();
		update_option( 'vms_efpg_db_version', VMS_EFPG_VERSION, false );
	}

	/**
	 * Create custom tables for orders, subscriptions, events.
	 */
	private static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$tables = array();

		$tables[] = "CREATE TABLE {$wpdb->prefix}vms_efpg_orders (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			fs_order_id VARCHAR(64) NOT NULL,
			fs_reference VARCHAR(64) NULL,
			wc_order_id BIGINT UNSIGNED NULL,
			wc_user_id BIGINT UNSIGNED NULL,
			fs_account_id VARCHAR(64) NULL,
			site_url VARCHAR(255) NULL,
			email VARCHAR(190) NULL,
			customer_name VARCHAR(190) NULL,
			currency CHAR(3) NULL,
			total DECIMAL(15,4) NOT NULL DEFAULT 0,
			tax DECIMAL(15,4) NOT NULL DEFAULT 0,
			subtotal DECIMAL(15,4) NOT NULL DEFAULT 0,
			discount DECIMAL(15,4) NOT NULL DEFAULT 0,
			status VARCHAR(40) NOT NULL DEFAULT '',
			payment_method VARCHAR(40) NULL,
			country CHAR(2) NULL,
			fs_invoice_id VARCHAR(64) NULL,
			invoice_url VARCHAR(512) NULL,
			is_test TINYINT(1) NOT NULL DEFAULT 0,
			payload LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY fs_order_id (fs_order_id),
			KEY email (email),
			KEY status (status),
			KEY wc_user_id (wc_user_id),
			KEY site_url (site_url(191)),
			KEY fs_account_id (fs_account_id),
			KEY fs_invoice_id (fs_invoice_id),
			KEY created_at (created_at)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$wpdb->prefix}vms_efpg_subscriptions (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			fs_subscription_id VARCHAR(64) NOT NULL,
			fs_account_id VARCHAR(64) NULL,
			wc_user_id BIGINT UNSIGNED NULL,
			site_url VARCHAR(255) NULL,
			email VARCHAR(190) NULL,
			product VARCHAR(190) NULL,
			currency CHAR(3) NULL,
			price DECIMAL(15,4) NOT NULL DEFAULT 0,
			interval_unit VARCHAR(20) NULL,
			interval_length INT NOT NULL DEFAULT 1,
			status VARCHAR(40) NOT NULL DEFAULT '',
			next_charge DATETIME NULL,
			begin_date DATETIME NULL,
			end_date DATETIME NULL,
			is_test TINYINT(1) NOT NULL DEFAULT 0,
			payload LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY fs_subscription_id (fs_subscription_id),
			KEY status (status),
			KEY email (email),
			KEY wc_user_id (wc_user_id),
			KEY site_url (site_url(191)),
			KEY next_charge (next_charge)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$wpdb->prefix}vms_efpg_events (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id VARCHAR(64) NOT NULL,
			event_type VARCHAR(80) NOT NULL,
			processed TINYINT(1) NOT NULL DEFAULT 0,
			live TINYINT(1) NOT NULL DEFAULT 0,
			payload LONGTEXT NULL,
			error_message TEXT NULL,
			created_at DATETIME NOT NULL,
			processed_at DATETIME NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY event_id (event_id),
			KEY event_type (event_type),
			KEY processed (processed),
			KEY created_at (created_at)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$wpdb->prefix}vms_efpg_log (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			level VARCHAR(20) NOT NULL,
			channel VARCHAR(40) NOT NULL,
			message TEXT NOT NULL,
			context LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY level (level),
			KEY channel (channel),
			KEY created_at (created_at)
		) $charset_collate;";

		foreach ( $tables as $sql ) {
			dbDelta( $sql );
		}
	}

	/**
	 * Populate invoice columns from stored order payloads.
	 */
	public static function backfill_order_invoice_fields() {
		global $wpdb;

		if ( ! function_exists( 'vms_efpg' ) || ! vms_efpg()->api ) {
			return;
		}

		$table = self::table_name( 'orders' );
		if ( ! $table ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$rows = $wpdb->get_results(
			"SELECT id, payload FROM {$table} WHERE (invoice_url IS NULL OR invoice_url = '') AND payload IS NOT NULL AND payload != '' LIMIT 500",
			ARRAY_A
		);

		if ( empty( $rows ) ) {
			return;
		}

		foreach ( $rows as $row ) {
			$payload = json_decode( (string) $row['payload'], true );
			if ( ! is_array( $payload ) ) {
				continue;
			}

			$meta = vms_efpg()->api->extract_order_invoice_meta( $payload );
			if ( empty( $meta['invoice_url'] ) && empty( $meta['fs_invoice_id'] ) ) {
				continue;
			}

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
		}
	}

	/**
	 * Populate user/site scope columns from payloads and linked WooCommerce orders.
	 */
	public static function backfill_user_scope_fields() {
		global $wpdb;

		if ( ! class_exists( 'VMS_EFPG_Data_Store' ) ) {
			return;
		}

		$orders_table = self::table_name( 'orders' );
		$subs_table   = self::table_name( 'subscriptions' );
		$site_url     = VMS_EFPG_Data_Store::get_site_url();
		$now          = current_time( 'mysql', true );

		if ( $orders_table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rows = $wpdb->get_results(
				"SELECT id, wc_order_id, wc_user_id, fs_account_id, site_url, payload FROM {$orders_table} LIMIT 2000",
				ARRAY_A
			);

			foreach ( (array) $rows as $row ) {
				$payload = ! empty( $row['payload'] ) ? json_decode( (string) $row['payload'], true ) : array();
				if ( ! is_array( $payload ) ) {
					$payload = array();
				}

				$update = array();
				if ( empty( $row['wc_user_id'] ) ) {
					$user_id = VMS_EFPG_Data_Store::resolve_wc_user_id_from_payload( $payload, (int) $row['wc_order_id'] );
					if ( $user_id ) {
						$update['wc_user_id'] = $user_id;
					}
				}
				if ( empty( $row['fs_account_id'] ) ) {
					$account_id = VMS_EFPG_Data_Store::extract_account_id_from_payload( $payload );
					if ( $account_id ) {
						$update['fs_account_id'] = $account_id;
					}
				}
				if ( empty( $row['site_url'] ) ) {
					$resolved_site = VMS_EFPG_Data_Store::resolve_site_url_from_payload( $payload );
					if ( $resolved_site && VMS_EFPG_Data_Store::site_urls_equivalent( $resolved_site, $site_url ) ) {
						$update['site_url'] = $resolved_site;
					} elseif ( VMS_EFPG_Data_Store::is_local_vms_efpg_wc_order( (int) $row['wc_order_id'] ) ) {
						$update['site_url'] = $site_url;
					}
				}
				if ( empty( $update ) ) {
					continue;
				}

				$update['updated_at'] = $now;
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$wpdb->update( $orders_table, $update, array( 'id' => (int) $row['id'] ) );
			}
		}

		if ( $subs_table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rows = $wpdb->get_results(
				"SELECT id, wc_user_id, site_url, payload FROM {$subs_table} LIMIT 2000",
				ARRAY_A
			);

			foreach ( (array) $rows as $row ) {
				$payload = ! empty( $row['payload'] ) ? json_decode( (string) $row['payload'], true ) : array();
				if ( ! is_array( $payload ) ) {
					$payload = array();
				}

				$update = array();
				if ( empty( $row['wc_user_id'] ) ) {
					$user_id = VMS_EFPG_Data_Store::resolve_wc_user_id_from_payload( $payload );
					if ( $user_id ) {
						$update['wc_user_id'] = $user_id;
					}
				}
				if ( empty( $row['site_url'] ) ) {
					$resolved_site = VMS_EFPG_Data_Store::resolve_site_url_from_payload( $payload );
					if ( $resolved_site && VMS_EFPG_Data_Store::site_urls_equivalent( $resolved_site, $site_url ) ) {
						$update['site_url'] = $resolved_site;
					} elseif ( VMS_EFPG_Data_Store::should_persist_for_site( $payload, $site_url ) ) {
						$update['site_url'] = $site_url;
					}
				}
				if ( empty( $update ) ) {
					continue;
				}

				$update['updated_at'] = $now;
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$wpdb->update( $subs_table, $update, array( 'id' => (int) $row['id'] ) );
			}
		}
	}

	/**
	 * Seed default options.
	 */
	private static function create_default_options() {
		$defaults = array(
			'mode'                    => 'sandbox',
			'live_username'           => '',
			'live_password'           => '',
			'live_storefront'         => '',
			'sandbox_username'        => '',
			'sandbox_password'        => '',
			'sandbox_storefront'      => '',
			'webhook_secret_live'     => '',
			'webhook_secret_sandbox'  => '',
			'enable_webhook'          => 'yes',
			'enable_logging'          => 'yes',
			'sync_products'           => 'no',
			'gateway_title'           => __( 'Pay with FastSpring', 'vms-elements-fastspring-payment-gateway' ),
			'gateway_description'     => __( 'Secure checkout with FastSpring.', 'vms-elements-fastspring-payment-gateway' ),
		);

		$existing = get_option( 'vms_efpg_settings', array() );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}
		$merged = array_merge( $defaults, $existing );
		update_option( 'vms_efpg_settings', $merged );
		update_option( 'vms_efpg_db_version', VMS_EFPG_VERSION );
	}
}

// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
