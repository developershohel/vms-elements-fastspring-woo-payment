<?php
/**
 * Installation routines.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Install.
 */
class VMS_EFWP_Install {

	/**
	 * Allowed custom table keys.
	 *
	 * @var array<string, string>
	 */
	private static $tables = array(
		'orders'        => 'vms_efwp_orders',
		'subscriptions' => 'vms_efwp_subscriptions',
		'events'        => 'vms_efwp_events',
		'log'           => 'vms_efwp_log',
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
		require_once VMS_EFWP_PATH . 'includes/class-vms-efwp-migrate.php';
		VMS_EFWP_Migrate::maybe_run();
		self::create_tables();
		self::create_default_options();
	}

	/**
	 * Create custom tables for orders, subscriptions, events.
	 */
	private static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$tables = array();

		$tables[] = "CREATE TABLE {$wpdb->prefix}vms_efwp_orders (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			fs_order_id VARCHAR(64) NOT NULL,
			fs_reference VARCHAR(64) NULL,
			wc_order_id BIGINT UNSIGNED NULL,
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
			is_test TINYINT(1) NOT NULL DEFAULT 0,
			payload LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY fs_order_id (fs_order_id),
			KEY email (email),
			KEY status (status),
			KEY created_at (created_at)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$wpdb->prefix}vms_efwp_subscriptions (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			fs_subscription_id VARCHAR(64) NOT NULL,
			fs_account_id VARCHAR(64) NULL,
			wc_user_id BIGINT UNSIGNED NULL,
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
			KEY next_charge (next_charge)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$wpdb->prefix}vms_efwp_events (
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

		$tables[] = "CREATE TABLE {$wpdb->prefix}vms_efwp_log (
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
			'gateway_title'           => __( 'Pay with FastSpring', 'vms-elements-fastspring-woo-payment' ),
			'gateway_description'     => __( 'Secure checkout powered by FastSpring.', 'vms-elements-fastspring-woo-payment' ),
		);

		$existing = get_option( 'vms_efwp_settings', array() );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}
		$merged = array_merge( $defaults, $existing );
		update_option( 'vms_efwp_settings', $merged );
		update_option( 'vms_efwp_db_version', VMS_EFWP_VERSION );
	}
}
