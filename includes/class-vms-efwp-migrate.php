<?php
/**
 * One-time migration from the legacy WP FastSpring plugin identifiers.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Migrate.
 */
class VMS_EFWP_Migrate {

	const FLAG_OPTION = 'vms_efwp_migrated_from_wp_fastspring';

	/**
	 * Legacy → current option keys.
	 *
	 * @var array<string, string>
	 */
	private static $option_map = array(
		'wp_fastspring_settings'                 => 'vms_efwp_settings',
		'wp_fastspring_db_version'               => 'vms_efwp_db_version',
		'wp_fastspring_keep_data_on_uninstall'   => 'vms_efwp_keep_data_on_uninstall',
		'woocommerce_wp_fastspring_settings'     => 'woocommerce_vms_efwp_settings',
	);

	/**
	 * Legacy → current custom table suffixes (without $wpdb->prefix).
	 *
	 * @var array<string, string>
	 */
	private static $table_map = array(
		'fastspring_orders'         => 'vms_efwp_orders',
		'fastspring_subscriptions'  => 'vms_efwp_subscriptions',
		'fastspring_events'         => 'vms_efwp_events',
		'fastspring_log'            => 'vms_efwp_log',
	);

	/**
	 * Legacy → current post/order meta keys.
	 *
	 * @var array<string, string>
	 */
	private static $meta_map = array(
		'_fastspring_product_path' => '_vms_efwp_product_path',
	);

	/**
	 * Run migration once when legacy data is detected.
	 */
	public static function maybe_run() {
		if ( get_option( self::FLAG_OPTION ) ) {
			return;
		}

		if ( ! self::legacy_data_exists() ) {
			update_option( self::FLAG_OPTION, VMS_EFWP_VERSION, false );
			return;
		}

		self::migrate_options();
		self::migrate_tables();
		self::migrate_post_meta();
		self::migrate_order_meta();
		self::migrate_wc_payment_method();

		update_option( self::FLAG_OPTION, VMS_EFWP_VERSION, false );
	}

	/**
	 * Whether any legacy WP FastSpring artifact still exists.
	 *
	 * @return bool
	 */
	private static function legacy_data_exists() {
		foreach ( array_keys( self::$option_map ) as $old_key ) {
			if ( null !== get_option( $old_key, null ) ) {
				return true;
			}
		}

		global $wpdb;

		foreach ( array_keys( self::$table_map ) as $old_suffix ) {
			if ( self::table_exists( $wpdb->prefix . $old_suffix ) ) {
				return true;
			}
		}

		foreach ( array_keys( self::$meta_map ) as $old_meta_key ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key = %s LIMIT 1", $old_meta_key ) ) ) {
				return true;
			}
		}

		if ( self::hpos_orders_meta_has_legacy_key() ) {
			return true;
		}

		if ( self::legacy_payment_method_exists() ) {
			return true;
		}

		return false;
	}

	/**
	 * Copy legacy options into the new keys, then remove the old keys.
	 */
	private static function migrate_options() {
		foreach ( self::$option_map as $old_key => $new_key ) {
			$old_value = get_option( $old_key, null );
			if ( null === $old_value ) {
				continue;
			}

			$new_value = get_option( $new_key, null );
			if ( self::should_copy_option( $old_value, $new_value ) ) {
				update_option( $new_key, $old_value );
			}

			delete_option( $old_key );
		}
	}

	/**
	 * Decide whether a legacy option value should overwrite the new option.
	 *
	 * @param mixed $old_value Legacy value.
	 * @param mixed $new_value Current value.
	 * @return bool
	 */
	private static function should_copy_option( $old_value, $new_value ) {
		if ( false === $new_value || null === $new_value ) {
			return true;
		}

		if ( is_array( $old_value ) && is_array( $new_value ) && empty( $new_value ) && ! empty( $old_value ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Rename legacy tables to the new prefixed names.
	 */
	private static function migrate_tables() {
		foreach ( self::$table_map as $old_suffix => $new_suffix ) {
			self::migrate_table( $old_suffix, $new_suffix );
		}
	}

	/**
	 * Rename one legacy table if needed.
	 *
	 * @param string $old_suffix Table suffix without prefix.
	 * @param string $new_suffix Target suffix without prefix.
	 */
	private static function migrate_table( $old_suffix, $new_suffix ) {
		global $wpdb;

		$old_table = $wpdb->prefix . $old_suffix;
		$new_table = $wpdb->prefix . $new_suffix;

		if ( ! self::table_exists( $old_table ) ) {
			return;
		}

		if ( self::table_exists( $new_table ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$old_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$old_table}`" );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$new_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$new_table}`" );

			if ( 0 === $new_count && $old_count > 0 ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$wpdb->query( "DROP TABLE `{$new_table}`" );
			} elseif ( $old_count > 0 && $new_count > 0 ) {
				return;
			}
		}

		if ( ! self::table_exists( $new_table ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$wpdb->query( "RENAME TABLE `{$old_table}` TO `{$new_table}`" );
		}
	}

	/**
	 * Rename legacy product post meta keys.
	 */
	private static function migrate_post_meta() {
		global $wpdb;

		foreach ( self::$meta_map as $old_key => $new_key ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s",
					$new_key,
					$old_key
				)
			);
		}
	}

	/**
	 * Rename legacy order meta keys (classic postmeta + HPOS meta table).
	 */
	private static function migrate_order_meta() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s",
				'_vms_efwp_session_id',
				'_fastspring_session_id'
			)
		);

		if ( ! self::hpos_is_enabled() ) {
			return;
		}

		$meta_table = $wpdb->prefix . 'wc_orders_meta';
		if ( ! self::table_exists( $meta_table ) ) {
			return;
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `{$meta_table}` SET meta_key = %s WHERE meta_key = %s",
				'_vms_efwp_session_id',
				'_fastspring_session_id'
			)
		);
		// phpcs:enable
	}

	/**
	 * Point existing WooCommerce orders at the renamed gateway id.
	 */
	private static function migrate_wc_payment_method() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = %s AND meta_value = %s",
				'vms_efwp',
				'_payment_method',
				'wp_fastspring'
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = %s AND meta_value = %s",
				'vms_efwp',
				'_payment_method_title',
				'Pay with FastSpring'
			)
		);

		if ( ! self::hpos_is_enabled() ) {
			return;
		}

		$orders_table = $wpdb->prefix . 'wc_orders';
		if ( ! self::table_exists( $orders_table ) ) {
			return;
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `{$orders_table}` SET payment_method = %s WHERE payment_method = %s",
				'vms_efwp',
				'wp_fastspring'
			)
		);
		// phpcs:enable
	}

	/**
	 * Whether HPOS order meta still uses a legacy key.
	 *
	 * @return bool
	 */
	private static function hpos_orders_meta_has_legacy_key() {
		if ( ! self::hpos_is_enabled() ) {
			return false;
		}

		global $wpdb;
		$meta_table = $wpdb->prefix . 'wc_orders_meta';
		if ( ! self::table_exists( $meta_table ) ) {
			return false;
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$found = (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM `{$meta_table}` WHERE meta_key = %s LIMIT 1",
				'_fastspring_session_id'
			)
		);
		// phpcs:enable

		return $found;
	}

	/**
	 * Whether any order still references the legacy gateway id.
	 *
	 * @return bool
	 */
	private static function legacy_payment_method_exists() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		if ( $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
				'_payment_method',
				'wp_fastspring'
			)
		) ) {
			return true;
		}

		if ( ! self::hpos_is_enabled() ) {
			return false;
		}

		$orders_table = $wpdb->prefix . 'wc_orders';
		if ( ! self::table_exists( $orders_table ) ) {
			return false;
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$found = (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM `{$orders_table}` WHERE payment_method = %s LIMIT 1",
				'wp_fastspring'
			)
		);
		// phpcs:enable

		return $found;
	}

	/**
	 * Whether WooCommerce HPOS is active.
	 *
	 * @return bool
	 */
	private static function hpos_is_enabled() {
		if ( ! class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			return false;
		}

		return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
	}

	/**
	 * Check whether a database table exists.
	 *
	 * @param string $table Full table name.
	 * @return bool
	 */
	private static function table_exists( $table ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	}
}
