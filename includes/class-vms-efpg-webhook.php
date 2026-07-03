<?php
/**
 * Webhook listener for FastSpring events.
 *
 * Endpoint: https://example.com/?vms-efpg-webhook=1
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFPG_Webhook.
 */
class VMS_EFPG_Webhook {

	/**
	 * API.
	 *
	 * @var VMS_EFPG_API
	 */
	private $api;

	/**
	 * Settings.
	 *
	 * @var VMS_EFPG_Settings
	 */
	private $settings;

	/**
	 * Webhook permissions helper.
	 *
	 * @var VMS_EFPG_Webhook_Permissions
	 */
	private $permissions;

	/**
	 * Constructor.
	 *
	 * @param VMS_EFPG_API      $api      API.
	 * @param VMS_EFPG_Settings $settings Settings.
	 */
	public function __construct( VMS_EFPG_API $api, VMS_EFPG_Settings $settings ) {
		$this->api         = $api;
		$this->settings    = $settings;
		$this->permissions = new VMS_EFPG_Webhook_Permissions( $api, $settings );

		add_action( 'parse_request', array( $this, 'maybe_handle' ) );
		add_action( 'parse_request', array( $this, 'maybe_handle_localhost_dev' ) );
	}

	/**
	 * Detect webhook requests.
	 *
	 * @param WP $wp Request object.
	 */
	public function maybe_handle( $wp ) {
		if ( empty( $_GET['vms-efpg-webhook'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		if ( 'yes' !== $this->settings->get( 'enable_webhook', 'yes' ) ) {
			status_header( 403 );
			exit;
		}

		$body = file_get_contents( 'php://input' );
		$sig  = isset( $_SERVER['HTTP_X_FS_SIGNATURE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FS_SIGNATURE'] ) ) : '';

		if ( ! $this->verify_signature( $body, $sig ) ) {
			VMS_EFPG_Logger::warning( 'Webhook signature verification failed', 'webhook' );
			status_header( 401 );
			echo wp_json_encode( array( 'error' => 'invalid_signature' ) );
			exit;
		}

		$payload = json_decode( $body, true );
		if ( ! is_array( $payload ) || empty( $payload['events'] ) ) {
			status_header( 400 );
			echo wp_json_encode( array( 'error' => 'invalid_payload' ) );
			exit;
		}

		$payload = self::sanitize_webhook_payload( $payload );
		if ( null === $payload ) {
			status_header( 400 );
			echo wp_json_encode( array( 'error' => 'invalid_payload' ) );
			exit;
		}

		foreach ( (array) $payload['events'] as $event ) {
			$this->process_event( $event );
		}

		status_header( 200 );
		echo wp_json_encode( array( 'ok' => true ) );
		exit;
	}

	/**
	 * Localhost-only webhook simulator for development.
	 *
	 * FastSpring cannot POST to 127.0.0.1 / localhost. Use this endpoint to inject
	 * webhook JSON from a URL parameter or POST body while testing locally.
	 *
	 * Examples:
	 *   ?vms-efpg-webhook-dev=1&payload={...urlencoded json...}
	 *   ?vms-efpg-webhook-dev=1&url=https://example.com/sample-webhook.json
	 *   POST ?vms-efpg-webhook-dev=1 with raw JSON body (same shape as FastSpring).
	 *
	 * Disabled automatically on non-localhost hosts.
	 *
	 * @param WP $wp Request object.
	 */
	public function maybe_handle_localhost_dev( $wp ) {
		unset( $wp );
		if ( empty( $_GET['vms-efpg-webhook-dev'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		if ( ! self::is_localhost_environment() ) {
			VMS_EFPG_Logger::warning( 'Blocked localhost dev webhook on non-local host.', 'webhook' );
			status_header( 403 );
			echo wp_json_encode( array( 'error' => 'localhost_only' ) );
			exit;
		}

		if ( 'yes' !== $this->settings->get( 'enable_webhook', 'yes' ) ) {
			status_header( 403 );
			echo wp_json_encode( array( 'error' => 'webhook_disabled' ) );
			exit;
		}

		$payload = $this->resolve_localhost_dev_payload();
		if ( is_wp_error( $payload ) ) {
			status_header( 400 );
			echo wp_json_encode(
				array(
					'error'   => $payload->get_error_code(),
					'message' => $payload->get_error_message(),
				)
			);
			exit;
		}

		VMS_EFPG_Logger::info(
			'Localhost dev webhook inject started.',
			'webhook',
			array( 'events' => count( $payload['events'] ) )
		);

		$processed = 0;
		foreach ( (array) $payload['events'] as $event ) {
			if ( ! is_array( $event ) ) {
				continue;
			}
			$this->process_event( $event, true );
			++$processed;
		}

		status_header( 200 );
		echo wp_json_encode(
			array(
				'ok'        => true,
				'processed' => $processed,
				'mode'      => 'localhost_dev',
			)
		);
		exit;
	}

	/**
	 * Whether the site is running on localhost / 127.0.0.1 (development).
	 *
	 * @return bool
	 */
	public static function is_localhost_environment() {
		$candidates = array();

		$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( is_string( $home_host ) && '' !== $home_host ) {
			$candidates[] = strtolower( $home_host );
		}

		if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
			$request_host = wp_parse_url( 'http://' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ), PHP_URL_HOST );
			if ( is_string( $request_host ) && '' !== $request_host ) {
				$candidates[] = strtolower( $request_host );
			}
		}

		foreach ( array_unique( $candidates ) as $host ) {
			if ( self::is_localhost_host( $host ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether a hostname is local development.
	 *
	 * @param string $host Hostname.
	 * @return bool
	 */
	private static function is_localhost_host( $host ) {
		$host = strtolower( (string) $host );
		if ( in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
			return true;
		}

		if ( preg_match( '/^127\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $host ) ) {
			return true;
		}

		/**
		 * Filter localhost detection (development only).
		 *
		 * @param bool   $is_local Whether the host is treated as localhost.
		 * @param string $host     Hostname being checked.
		 */
		return (bool) apply_filters( 'vms_efpg_is_localhost_host', false, $host );
	}

	/**
	 * Build the localhost dev webhook inject URL.
	 *
	 * @param array $args Optional query args (payload, url, etc.).
	 * @return string
	 */
	public static function localhost_dev_webhook_url( $args = array() ) {
		$args = array_merge(
			array( 'vms-efpg-webhook-dev' => 1 ),
			(array) $args
		);
		return add_query_arg( $args, home_url( '/' ) );
	}

	/**
	 * Resolve webhook JSON for the localhost dev endpoint.
	 *
	 * @return array|WP_Error Normalized payload with an events array.
	 */
	private function resolve_localhost_dev_payload() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Localhost-only dev endpoint; no nonce on external replay URLs.
		$body = file_get_contents( 'php://input' );
		if ( is_string( $body ) && '' !== trim( $body ) ) {
			$decoded = $this->decode_dev_payload_string( $body );
			if ( is_array( $decoded ) ) {
				$decoded = self::sanitize_decoded_dev_payload( $decoded );
				return $this->normalize_webhook_payload( $decoded );
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Disabled at method start.
		if ( ! empty( $_GET['payload'] ) ) {
			$raw     = sanitize_textarea_field( wp_unslash( $_GET['payload'] ) );
			$decoded = $this->decode_dev_payload_string( $raw );
			if ( is_array( $decoded ) ) {
				$decoded = self::sanitize_decoded_dev_payload( $decoded );
				return $this->normalize_webhook_payload( $decoded );
			}
			return new WP_Error(
				'invalid_payload',
				__( 'Could not decode the payload query parameter as JSON.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		$source_url = ! empty( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : '';
		if ( '' === $source_url && ! empty( $_GET['source'] ) ) {
			$source_url = esc_url_raw( wp_unslash( $_GET['source'] ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( '' !== $source_url ) {
			return $this->fetch_dev_payload_from_url( $source_url );
		}

		return new WP_Error(
			'missing_payload',
			__( 'Provide webhook JSON via POST body, the payload query parameter, or url/source to fetch JSON from.', 'vms-elements-fastspring-payment-gateway' )
		);
	}

	/**
	 * Sanitize a decoded dev-webhook JSON payload before further processing.
	 *
	 * @param array $decoded Decoded array.
	 * @return array
	 */
	private static function sanitize_decoded_dev_payload( $decoded ) {
		if ( ! is_array( $decoded ) || ! function_exists( 'map_deep' ) ) {
			return is_array( $decoded ) ? $decoded : array();
		}

		return map_deep(
			$decoded,
			static function ( $value ) {
				return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : $value;
			}
		);
	}

	/**
	 * Decode a JSON webhook payload from a raw string (plain, URL-encoded, or base64).
	 *
	 * @param string $raw Raw input.
	 * @return array|null
	 */
	private function decode_dev_payload_string( $raw ) {
		$raw = trim( (string) $raw );
		if ( '' === $raw ) {
			return null;
		}

		$attempts = array( $raw, rawurldecode( $raw ) );
		$decoded_b64 = base64_decode( $raw, true );
		if ( false !== $decoded_b64 ) {
			$attempts[] = $decoded_b64;
		}

		foreach ( $attempts as $candidate ) {
			$json = json_decode( $candidate, true );
			if ( is_array( $json ) ) {
				return $json;
			}
		}

		return null;
	}

	/**
	 * Fetch webhook JSON from a remote URL (localhost dev only).
	 *
	 * @param string $url Remote URL.
	 * @return array|WP_Error
	 */
	private function fetch_dev_payload_from_url( $url ) {
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
			return new WP_Error(
				'invalid_source_url',
				__( 'The source URL must use http or https.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 20,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'source_fetch_failed',
				sprintf(
					/* translators: %s: error message */
					__( 'Could not fetch webhook JSON from URL: %s', 'vms-elements-fastspring-payment-gateway' ),
					$response->get_error_message()
				)
			);
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			return new WP_Error(
				'source_fetch_failed',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Source URL returned HTTP %d.', 'vms-elements-fastspring-payment-gateway' ),
					$code
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$decoded = $this->decode_dev_payload_string( $body );
		if ( ! is_array( $decoded ) ) {
			return new WP_Error(
				'invalid_source_payload',
				__( 'The source URL did not return valid webhook JSON.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		$decoded = self::sanitize_decoded_dev_payload( $decoded );
		return $this->normalize_webhook_payload( $decoded );
	}

	/**
	 * Normalize decoded JSON into FastSpring webhook payload shape.
	 *
	 * @param array $data Decoded JSON.
	 * @return array|WP_Error
	 */
	private function normalize_webhook_payload( $data ) {
		if ( ! is_array( $data ) ) {
			return new WP_Error(
				'invalid_payload',
				__( 'Webhook payload must be a JSON object.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		if ( ! empty( $data['events'] ) && is_array( $data['events'] ) ) {
			return array( 'events' => array_values( $data['events'] ) );
		}

		if ( isset( $data['type'] ) || isset( $data['id'] ) || isset( $data['data'] ) ) {
			return array( 'events' => array( $data ) );
		}

		return new WP_Error(
			'invalid_payload',
			__( 'Webhook JSON must contain an events array or a single event object.', 'vms-elements-fastspring-payment-gateway' )
		);
	}

	/**
	 * Sanitize decoded webhook JSON before processing.
	 *
	 * @param array $payload Decoded webhook body.
	 * @return array|null
	 */
	private static function sanitize_webhook_payload( array $payload ) {
		if ( empty( $payload['events'] ) || ! is_array( $payload['events'] ) ) {
			return null;
		}

		if ( function_exists( 'map_deep' ) ) {
			$payload = map_deep(
				$payload,
				static function ( $value ) {
					return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : $value;
				}
			);
		}

		if ( empty( $payload['events'] ) || ! is_array( $payload['events'] ) ) {
			return null;
		}

		return $payload;
	}

	/**
	 * Verify the X-FS-Signature header.
	 *
	 * FastSpring signs the raw body with HMAC-SHA256 using the configured
	 * webhook secret and base64-encodes the result.
	 *
	 * @param string $body Raw request body.
	 * @param string $signature Header value.
	 * @return bool
	 */
	private function verify_signature( $body, $signature ) {
		$secrets = array_filter(
			array(
				$this->settings->get( 'webhook_secret_sandbox' ),
				$this->settings->get( 'webhook_secret_live' ),
			)
		);

		if ( empty( $secrets ) ) {
			VMS_EFPG_Logger::warning( 'No webhook secret configured; accepting unsigned webhook.', 'webhook' );
			return true;
		}
		if ( empty( $signature ) ) {
			return false;
		}

		foreach ( array_unique( $secrets ) as $secret ) {
			$expected = base64_encode( hash_hmac( 'sha256', $body, $secret, true ) );
			if ( hash_equals( $expected, $signature ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Dispatch a single event.
	 *
	 * @param array $event FastSpring event.
	 * @param bool  $force_handlers Run handlers even if event is disabled in FastSpring permissions (localhost dev only).
	 */
	private function process_event( $event, $force_handlers = false ) {
		$is_live = isset( $event['live'] ) ? (bool) $event['live'] : ! $this->settings->is_sandbox();
		$is_test = ! $is_live;

		VMS_EFPG_Data_Store::record_event( $event, $is_live );

		$type = isset( $event['type'] ) ? $event['type'] : '';
		$data = isset( $event['data'] ) ? $event['data'] : array();

		try {
			if ( $force_handlers || $this->permissions->is_event_enabled( $type ) ) {
				$this->apply_event_handlers( $type, $data, $is_test );
			} else {
				VMS_EFPG_Logger::info(
					'Webhook event skipped — not enabled in FastSpring webhook permissions: ' . $type,
					'webhook',
					array( 'type' => $type )
				);
			}

			do_action( 'vms_efpg_event_' . $type, $data, $event );
			do_action( 'vms_efpg_event', $type, $data, $event );

			VMS_EFPG_Data_Store::mark_event_processed( $event['id'] );
		} catch ( Exception $e ) {
			VMS_EFPG_Logger::error( 'Webhook processing error: ' . $e->getMessage(), 'webhook', array( 'type' => $type ) );
			VMS_EFPG_Data_Store::mark_event_processed( $event['id'], $e->getMessage() );
		}
	}

	/**
	 * Apply plugin handlers for a permitted event type.
	 *
	 * @param string $type    Event type.
	 * @param array  $data    Event data.
	 * @param bool   $is_test Whether the event is test mode.
	 */
	private function apply_event_handlers( $type, $data, $is_test ) {
		$data = is_array( $data ) ? $data : array();

		switch ( $type ) {
			case 'order.completed':
			case 'order.approval.pending':
			case 'order.payment.pending':
			case 'order.canceled':
				$data = VMS_EFPG_Data_Store::prepare_payload_for_site( $data );
				if ( ! VMS_EFPG_Data_Store::should_persist_for_site( $data ) ) {
					VMS_EFPG_Logger::info(
						'Webhook order ignored — belongs to another WordPress site.',
						'webhook',
						array(
							'type'     => $type,
							'order_id' => $data['id'] ?? '',
							'site_url' => VMS_EFPG_Data_Store::resolve_site_url_from_payload( $data ),
						)
					);
					break;
				}
				VMS_EFPG_Data_Store::upsert_order( $data, $is_test );
				if ( 'order.completed' === $type ) {
					$this->maybe_complete_wc_order( $data );
				} elseif ( 'order.canceled' === $type ) {
					$this->maybe_cancel_wc_order( $data );
				}
				break;

			case 'return.created':
			case 'order.refund':
				$order_id = $this->resolve_fs_order_id( $data );
				if ( $order_id ) {
					$stored = VMS_EFPG_Data_Store::get_order_by_fs_id( $order_id );
					if ( ! $stored ) {
						break;
					}
					$stored_site = ! empty( $stored['site_url'] ) ? (string) $stored['site_url'] : '';
					if ( $stored_site && ! VMS_EFPG_Data_Store::site_urls_equivalent( $stored_site, VMS_EFPG_Data_Store::get_site_url() ) ) {
						break;
					}
					VMS_EFPG_Data_Store::mark_order_refunded( $order_id );
					$this->maybe_refund_wc_order( $order_id, $data );
				}
				break;

			case 'subscription.activated':
			case 'subscription.charge.completed':
			case 'subscription.updated':
			case 'subscription.trial.reminder':
			case 'subscription.payment.overdue':
			case 'subscription.payment.reminder':
			case 'subscription.canceled':
			case 'subscription.deactivated':
				$data = VMS_EFPG_Data_Store::prepare_payload_for_site( $data );
				if ( ! VMS_EFPG_Data_Store::should_persist_for_site( $data ) ) {
					VMS_EFPG_Logger::info(
						'Webhook subscription ignored — belongs to another WordPress site.',
						'webhook',
						array(
							'type'             => $type,
							'subscription_id'  => $data['id'] ?? '',
							'site_url'         => VMS_EFPG_Data_Store::resolve_site_url_from_payload( $data ),
						)
					);
					break;
				}
				$wc_user_id = VMS_EFPG_Data_Store::resolve_wc_user_id_from_payload( $data );
				VMS_EFPG_Data_Store::upsert_subscription( $data, $is_test, $wc_user_id );
				if ( 'subscription.canceled' === $type || 'subscription.deactivated' === $type ) {
					VMS_EFPG_Data_Store::set_subscription_status( $data['id'], 'canceled' );
				}
				break;

			case 'mailingListEntry.updated':
			case 'account.created':
			case 'account.updated':
				// Customer-related; nothing to persist locally for now but acknowledged.
				break;

			default:
				VMS_EFPG_Logger::info( 'Unhandled FastSpring event type: ' . $type, 'webhook' );
		}
	}

	/**
	 * Resolve a linked WooCommerce order id from FastSpring payload data.
	 *
	 * @param array $data Event data.
	 * @return int
	 */
	private function resolve_wc_order_id( $data ) {
		if ( isset( $data['tags']['wc_order_id'] ) && $data['tags']['wc_order_id'] ) {
			return (int) $data['tags']['wc_order_id'];
		}
		if ( isset( $data['reference'] ) && $data['reference'] ) {
			return (int) preg_replace( '/[^0-9]/', '', (string) $data['reference'] );
		}
		return 0;
	}

	/**
	 * Resolve a FastSpring order id from refund/return payload data.
	 *
	 * @param array $data Event data.
	 * @return string
	 */
	private function resolve_fs_order_id( $data ) {
		if ( ! empty( $data['original']['id'] ) ) {
			return (string) $data['original']['id'];
		}
		if ( ! empty( $data['original']['order'] ) ) {
			return (string) $data['original']['order'];
		}
		if ( ! empty( $data['order'] ) ) {
			return (string) $data['order'];
		}
		return '';
	}

	/**
	 * Mark a linked WooCommerce order completed.
	 *
	 * @param array $data Event data.
	 */
	private function maybe_complete_wc_order( $data ) {
		if ( ! function_exists( 'wc_get_order' ) ) {
			return;
		}
		$wc_order_id = $this->resolve_wc_order_id( $data );
		if ( ! $wc_order_id ) {
			return;
		}
		$order = wc_get_order( $wc_order_id );
		if ( ! $order ) {
			return;
		}
		if ( class_exists( 'VMS_EFPG_Checkout_Overlay' ) ) {
			VMS_EFPG_Checkout_Overlay::apply_fastspring_payment( $order, $data );
			return;
		}
		if ( ! $order->is_paid() ) {
			$order->payment_complete( isset( $data['id'] ) ? $data['id'] : '' );
			$order->add_order_note( __( 'Payment captured by FastSpring.', 'vms-elements-fastspring-payment-gateway' ) );
		}
	}

	/**
	 * Cancel a linked WooCommerce order.
	 *
	 * @param array $data Event data.
	 */
	private function maybe_cancel_wc_order( $data ) {
		if ( ! function_exists( 'wc_get_order' ) ) {
			return;
		}
		$wc_order_id = $this->resolve_wc_order_id( $data );
		if ( ! $wc_order_id ) {
			return;
		}
		$order = wc_get_order( $wc_order_id );
		if ( $order && ! in_array( $order->get_status(), array( 'cancelled', 'refunded' ), true ) ) {
			$order->update_status( 'cancelled', __( 'FastSpring reported the order was canceled.', 'vms-elements-fastspring-payment-gateway' ) );
		}
	}

	/**
	 * Refund a linked WooCommerce order.
	 *
	 * @param string $fs_order_id ID.
	 * @param array  $data        Event data.
	 */
	private function maybe_refund_wc_order( $fs_order_id, $data ) {
		if ( ! function_exists( 'wc_get_order' ) ) {
			return;
		}
		global $wpdb;
		$table = VMS_EFPG_Install::table_name( 'orders' );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wc_order_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT wc_order_id FROM {$table} WHERE fs_order_id = %s",
				$fs_order_id
			)
		);
		// phpcs:enable
		if ( ! $wc_order_id ) {
			return;
		}
		$order = wc_get_order( (int) $wc_order_id );
		if ( $order && 'refunded' !== $order->get_status() ) {
			$order->update_status( 'refunded', __( 'Refund issued via FastSpring.', 'vms-elements-fastspring-payment-gateway' ) );
		}
	}
}
