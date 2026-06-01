<?php
/**
 * Webhook listener for FastSpring events.
 *
 * Endpoint: https://example.com/?wp-fastspring-webhook=1
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Webhook.
 */
class WP_FastSpring_Webhook {

	/**
	 * API.
	 *
	 * @var WP_FastSpring_API
	 */
	private $api;

	/**
	 * Settings.
	 *
	 * @var WP_FastSpring_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param WP_FastSpring_API      $api      API.
	 * @param WP_FastSpring_Settings $settings Settings.
	 */
	public function __construct( WP_FastSpring_API $api, WP_FastSpring_Settings $settings ) {
		$this->api      = $api;
		$this->settings = $settings;

		add_action( 'parse_request', array( $this, 'maybe_handle' ) );
	}

	/**
	 * Detect webhook requests.
	 *
	 * @param WP $wp Request object.
	 */
	public function maybe_handle( $wp ) {
		if ( empty( $_GET['wp-fastspring-webhook'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		if ( 'yes' !== $this->settings->get( 'enable_webhook', 'yes' ) ) {
			status_header( 403 );
			exit;
		}

		$body = file_get_contents( 'php://input' );
		$sig  = isset( $_SERVER['HTTP_X_FS_SIGNATURE'] ) ? $_SERVER['HTTP_X_FS_SIGNATURE'] : '';

		if ( ! $this->verify_signature( $body, $sig ) ) {
			WP_FastSpring_Logger::warning( 'Webhook signature verification failed', 'webhook' );
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

		foreach ( (array) $payload['events'] as $event ) {
			$this->process_event( $event );
		}

		status_header( 200 );
		echo wp_json_encode( array( 'ok' => true ) );
		exit;
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
		$secret = $this->settings->webhook_secret();
		if ( empty( $secret ) ) {
			// If no secret is configured, skip verification but log a warning.
			WP_FastSpring_Logger::warning( 'No webhook secret configured; accepting unsigned webhook.', 'webhook' );
			return true;
		}
		if ( empty( $signature ) ) {
			return false;
		}
		$expected = base64_encode( hash_hmac( 'sha256', $body, $secret, true ) );
		return hash_equals( $expected, $signature );
	}

	/**
	 * Dispatch a single event.
	 *
	 * @param array $event FastSpring event.
	 */
	private function process_event( $event ) {
		$is_live = isset( $event['live'] ) ? (bool) $event['live'] : ! $this->settings->is_sandbox();
		$is_test = ! $is_live;

		WP_FastSpring_Data_Store::record_event( $event, $is_live );

		$type = isset( $event['type'] ) ? $event['type'] : '';
		$data = isset( $event['data'] ) ? $event['data'] : array();

		try {
			switch ( $type ) {
				case 'order.completed':
				case 'order.approval.pending':
				case 'order.payment.pending':
				case 'order.canceled':
					WP_FastSpring_Data_Store::upsert_order( $data, $is_test );
					if ( 'order.completed' === $type ) {
						$this->maybe_complete_wc_order( $data );
					} elseif ( 'order.canceled' === $type ) {
						$this->maybe_cancel_wc_order( $data );
					}
					break;

				case 'return.created':
				case 'order.refund':
					$order_id = isset( $data['original']['id'] ) ? $data['original']['id'] : ( isset( $data['order'] ) ? $data['order'] : '' );
					if ( $order_id ) {
						WP_FastSpring_Data_Store::mark_order_refunded( $order_id );
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
					WP_FastSpring_Data_Store::upsert_subscription( $data, $is_test );
					if ( 'subscription.canceled' === $type || 'subscription.deactivated' === $type ) {
						WP_FastSpring_Data_Store::set_subscription_status( $data['id'], 'canceled' );
					}
					break;

				case 'mailingListEntry.updated':
				case 'account.created':
				case 'account.updated':
					// Customer-related; nothing to persist locally for now but acknowledged.
					break;

				default:
					WP_FastSpring_Logger::info( 'Unhandled FastSpring event type: ' . $type, 'webhook' );
			}

			do_action( 'wp_fastspring_event_' . $type, $data, $event );
			do_action( 'wp_fastspring_event', $type, $data, $event );

			WP_FastSpring_Data_Store::mark_event_processed( $event['id'] );
		} catch ( Exception $e ) {
			WP_FastSpring_Logger::error( 'Webhook processing error: ' . $e->getMessage(), 'webhook', array( 'type' => $type ) );
			WP_FastSpring_Data_Store::mark_event_processed( $event['id'], $e->getMessage() );
		}
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
		$wc_order_id = isset( $data['tags']['wc_order_id'] ) ? (int) $data['tags']['wc_order_id'] : 0;
		if ( ! $wc_order_id ) {
			$wc_order_id = isset( $data['reference'] ) ? (int) preg_replace( '/[^0-9]/', '', (string) $data['reference'] ) : 0;
		}
		if ( ! $wc_order_id ) {
			return;
		}
		$order = wc_get_order( $wc_order_id );
		if ( ! $order ) {
			return;
		}
		if ( ! $order->is_paid() ) {
			$order->payment_complete( isset( $data['id'] ) ? $data['id'] : '' );
			$order->add_order_note( __( 'Payment captured by FastSpring.', 'wp-fastspring' ) );
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
		$wc_order_id = isset( $data['tags']['wc_order_id'] ) ? (int) $data['tags']['wc_order_id'] : 0;
		if ( ! $wc_order_id ) {
			return;
		}
		$order = wc_get_order( $wc_order_id );
		if ( $order && ! in_array( $order->get_status(), array( 'cancelled', 'refunded' ), true ) ) {
			$order->update_status( 'cancelled', __( 'FastSpring reported the order was canceled.', 'wp-fastspring' ) );
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
		$wc_order_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT wc_order_id FROM {$wpdb->prefix}fastspring_orders WHERE fs_order_id = %s",
				$fs_order_id
			)
		);
		if ( ! $wc_order_id ) {
			return;
		}
		$order = wc_get_order( (int) $wc_order_id );
		if ( $order && 'refunded' !== $order->get_status() ) {
			$order->update_status( 'refunded', __( 'Refund issued via FastSpring.', 'wp-fastspring' ) );
		}
	}
}
