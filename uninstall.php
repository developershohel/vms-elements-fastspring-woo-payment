<?php
/**
 * Uninstall handler.
 *
 * @package VMS_EFPG
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$vms_efpg_keep_data = get_option( 'vms_efpg_keep_data_on_uninstall', false );
if ( $vms_efpg_keep_data ) {
	return;
}

$vms_efpg_tables = array(
	$wpdb->prefix . 'vms_efpg_orders',
	$wpdb->prefix . 'vms_efpg_subscriptions',
	$wpdb->prefix . 'vms_efpg_events',
	$wpdb->prefix . 'vms_efpg_log',
);
foreach ( $vms_efpg_tables as $vms_efpg_table ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Hardcoded plugin table names on uninstall.
	$wpdb->query( "DROP TABLE IF EXISTS {$vms_efpg_table}" );
}

delete_option( 'vms_efpg_settings' );
delete_option( 'vms_efpg_db_version' );
delete_option( 'vms_efpg_keep_data_on_uninstall' );
delete_option( 'woocommerce_vms_efpg_settings' );
delete_option( 'vms_efpg_migrated_from_wp_fastspring' );
delete_option( 'vms_efpg_migrated_plugin_slug' );
delete_option( 'vms_efpg_migrated_from_efwp' );

delete_option( 'vms_efwp_settings' );
delete_option( 'vms_efwp_db_version' );
delete_option( 'vms_efwp_keep_data_on_uninstall' );
delete_option( 'woocommerce_vms_efwp_settings' );
delete_option( 'vms_efwp_migrated_from_wp_fastspring' );
delete_option( 'vms_efwp_migrated_plugin_slug' );
delete_option( 'vms_efwp_migrated_from_efwp' );

delete_option( 'wp_fastspring_settings' );
delete_option( 'wp_fastspring_db_version' );
delete_option( 'wp_fastspring_keep_data_on_uninstall' );
delete_option( 'woocommerce_wp_fastspring_settings' );
