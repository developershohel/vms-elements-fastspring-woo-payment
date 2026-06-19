<?php
/**
 * Uninstall handler.
 *
 * @package VMS_EFWP
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$vms_efwp_keep_data = get_option( 'vms_efwp_keep_data_on_uninstall', false );
if ( $vms_efwp_keep_data ) {
	return;
}

$vms_efwp_tables = array(
	$wpdb->prefix . 'vms_efwp_orders',
	$wpdb->prefix . 'vms_efwp_subscriptions',
	$wpdb->prefix . 'vms_efwp_events',
	$wpdb->prefix . 'vms_efwp_log',
);
foreach ( $vms_efwp_tables as $vms_efwp_table ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Hardcoded plugin table names on uninstall.
	$wpdb->query( "DROP TABLE IF EXISTS {$vms_efwp_table}" );
}

delete_option( 'vms_efwp_settings' );
delete_option( 'vms_efwp_db_version' );
delete_option( 'vms_efwp_keep_data_on_uninstall' );
delete_option( 'woocommerce_vms_efwp_settings' );
delete_option( 'vms_efwp_migrated_from_wp_fastspring' );

delete_option( 'wp_fastspring_settings' );
delete_option( 'wp_fastspring_db_version' );
delete_option( 'wp_fastspring_keep_data_on_uninstall' );
delete_option( 'woocommerce_wp_fastspring_settings' );
