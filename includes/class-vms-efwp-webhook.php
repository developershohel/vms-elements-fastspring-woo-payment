<?php
/**
 * Webhook listener for FastSpring events.
 *
 * Endpoint: https://example.com/?vms-efwp-webhook=1
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Webhook.
 */
class VMS_EFWP_Webhook {

	/**
	 * API.
	 *
	 * @var VMS_EFWP_API
	 */
	private $api;

	/**
	 * Settings.
	 *
	 * @var VMS_EFWP_Settings
	 */
	private $settings;

	/**
	 * Webhook permissions helper.
	 *
	 * @var VMS_EFWP_Webhook_Permissions
	 */
	private $permissions;

	/**
	 * Constructor.
	 *
	 * @param VMS_EFWP_API      $api      API.
	 * @param VMS_EFWP_Settings $settings Settings.
	 */
	public function __construct( VMS_EFWP_API $api, VMS_EFWP_Settings $settings ) {
		$this->api         = $api;
		$this->settings    = $settings;
		$this->permissions = new VMS_EFWP_Webhook_Permissions( $api, $settings );

		add_action( 'parse_request', array( $this, 'maybe_handle' ) );
	}

	/**
	 * Detect webhook requests.
	 *
	 * @param WP $wp Request object.
	 */
	public function maybe_handle( $wp ) {
		if ( empty( $_GET['vms-efwp-webhook'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		if ( 'yes' !== $this->settings->get( 'enable_webhook', 'yes' ) ) {
			status_header( 403 );
			exit;
		}

		$body = file_get_contents( 'php://input' );
		$sig  = isset( $_SERVER['HTTP_X_FS_SIGNATURE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FS_SIGNATURE'] ) ) : '';

		if ( ! $this->verify_signature( $body, $sig ) ) {
			VMS_EFWP_Logger::warning( 'Webhook signature verification failed', 'webhook' );
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
		$secrets = array_filter(
			array(
				$this->settings->get( 'webhook_secret_sandbox' ),
				$this->settings->get( 'webhook_secret_live' ),
			)
		);

		if ( empty( $secrets ) ) {
			VMS_EFWP_Logger::warning( 'No webhook secret configured; accepting unsigned webhook.', 'webhook' );
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
	 */
	private function process_event( $event ) {
		$is_live = isset( $event['live'] ) ? (bool) $event['live'] : ! $this->settings->is_sandbox();
		$is_test = ! $is_live;

		VMS_EFWP_Data_Store::record_event( $event, $is_live );

		$type = isset( $event['type'] ) ? $event['type'] : '';
		$data = isset( $event['data'] ) ? $event['data'] : array();

		try {
			if ( $this->permissions->is_event_enabled( $type ) ) {
				$this->apply_event_handlers( $type, $data, $is_test );
			} else {
				VMS_EFWP_Logger::info(
					'Webhook event skipped — not enabled in FastSpring webhook permissions: ' . $type,
					'webhook',
					array( 'type' => $type )
				);
			}

			do_action( 'vms_efwp_event_' . $type, $data, $event );
			do_action( 'vms_efwp_event', $type, $data, $event );

			VMS_EFWP_Data_Store::mark_event_processed( $event['id'] );
		} catch ( Exception $e ) {
			VMS_EFWP_Logger::error( 'Webhook processing error: ' . $e->getMessage(), 'webhook', array( 'type' => $type ) );
			VMS_EFWP_Data_Store::mark_event_processed( $event['id'], $e->getMessage() );
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
		switch ( $type ) {
			case 'order.completed':
			case 'order.approval.pending':
			case 'order.payment.pending':
			case 'order.canceled':
				VMS_EFWP_Data_Store::upsert_order( $data, $is_test );
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
					VMS_EFWP_Data_Store::mark_order_refunded( $order_id );
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
				VMS_EFWP_Data_Store::upsert_subscription( $data, $is_test );
				if ( 'subscription.canceled' === $type || 'subscription.deactivated' === $type ) {
					VMS_EFWP_Data_Store::set_subscription_status( $data['id'], 'canceled' );
				}
				break;

			case 'mailingListEntry.updated':
			case 'account.created':
			case 'account.updated':
				// Customer-related; nothing to persist locally for now but acknowledged.
				break;

			default:
				VMS_EFWP_Logger::info( 'Unhandled FastSpring event type: ' . $type, 'webhook' );
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
		if ( class_exists( 'VMS_EFWP_Checkout_Overlay' ) ) {
			VMS_EFWP_Checkout_Overlay::apply_fastspring_payment( $order, $data );
			return;
		}
		if ( ! $order->is_paid() ) {
			$order->payment_complete( isset( $data['id'] ) ? $data['id'] : '' );
			$order->add_order_note( __( 'Payment captured by FastSpring.', 'vms-elements-fastspring-woo-payment' ) );
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
			$order->update_status( 'cancelled', __( 'FastSpring reported the order was canceled.', 'vms-elements-fastspring-woo-payment' ) );
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
		$table = VMS_EFWP_Install::table_name( 'orders' );
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
			$order->update_status( 'refunded', __( 'Refund issued via FastSpring.', 'vms-elements-fastspring-woo-payment' ) );
		}
	}
}
