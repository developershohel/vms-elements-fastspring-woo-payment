<?php
/**
 * Lightweight logger writing to a custom DB table.
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFPG_Logger.
 */
class VMS_EFPG_Logger {

	/**
	 * Add a log line.
	 *
	 * @param string $message Message.
	 * @param string $level   Level (info, warning, error).
	 * @param string $channel Channel (api, webhook, gateway, sync).
	 * @param array  $context Extra context.
	 */
	public static function log( $message, $level = 'info', $channel = 'general', $context = array() ) {
		global $wpdb;

		$settings = vms_efpg()->settings;
		if ( $settings && 'yes' !== $settings->get( 'enable_logging', 'yes' ) && 'error' !== $level ) {
			return;
		}

		$table = VMS_EFPG_Install::table_name( 'log' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$wpdb->insert(
			$table,
			array(
				'level'      => substr( $level, 0, 20 ),
				'channel'    => substr( $channel, 0, 40 ),
				'message'    => $message,
				'context'    => $context ? wp_json_encode( $context ) : null,
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Convenience methods.
	 *
	 * @param string $message Message.
	 * @param string $channel Channel.
	 * @param array  $context Context.
	 */
	public static function info( $message, $channel = 'general', $context = array() ) {
		self::log( $message, 'info', $channel, $context );
	}

	/**
	 * Warning level log.
	 *
	 * @param string $message Message.
	 * @param string $channel Channel.
	 * @param array  $context Context.
	 */
	public static function warning( $message, $channel = 'general', $context = array() ) {
		self::log( $message, 'warning', $channel, $context );
	}

	/**
	 * Error level log.
	 *
	 * @param string $message Message.
	 * @param string $channel Channel.
	 * @param array  $context Context.
	 */
	public static function error( $message, $channel = 'general', $context = array() ) {
		self::log( $message, 'error', $channel, $context );
	}
}
