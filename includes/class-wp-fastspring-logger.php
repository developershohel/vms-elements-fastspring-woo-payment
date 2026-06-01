<?php
/**
 * Lightweight logger writing to a custom DB table.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Logger.
 */
class WP_FastSpring_Logger {

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

		$settings = wp_fastspring()->settings;
		if ( $settings && 'yes' !== $settings->get( 'enable_logging', 'yes' ) && 'error' !== $level ) {
			return;
		}

		$wpdb->insert(
			$wpdb->prefix . 'fastspring_log',
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
