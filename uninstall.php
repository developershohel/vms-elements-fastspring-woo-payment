<?php
/**
 * Uninstall handler.
 *
 * @package WP_FastSpring
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$keep_data = get_option( 'wp_fastspring_keep_data_on_uninstall', false );
if ( $keep_data ) {
	return;
}

$tables = array(
	$wpdb->prefix . 'fastspring_orders',
	$wpdb->prefix . 'fastspring_subscriptions',
	$wpdb->prefix . 'fastspring_events',
	$wpdb->prefix . 'fastspring_log',
);
foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS $table" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

delete_option( 'wp_fastspring_settings' );
delete_option( 'wp_fastspring_db_version' );
delete_option( 'wp_fastspring_keep_data_on_uninstall' );
