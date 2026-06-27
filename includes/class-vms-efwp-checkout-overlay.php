<?php
/**
 * REST + transient storage for FastSpring popup overlay payloads.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Checkout_Overlay.
 */
class VMS_EFWP_Checkout_Overlay {

	/**
	 * Transient prefix.
	 */
	const TRANSIENT_PREFIX = 'vms_efwp_overlay_';

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 */
	public static function register_routes() {
		register_rest_route(
			'vms-efwp/v1',
			'/overlay/(?P<order_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_overlay' ),
				'permission_callback' => array( __CLASS__, 'can_read_overlay' ),
				'args'                => array(
					'order_id' => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'key'      => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'token'    => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			'vms-efwp/v1',
			'/complete/(?P<order_id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'complete_payment' ),
				'permission_callback' => array( __CLASS__, 'can_read_overlay' ),
				'args'                => array(
					'order_id'    => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'fs_order_id' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'key'         => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'token'       => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Save overlay payload for later retrieval (Blocks checkout fallback).
	 *
	 * @param int   $order_id Order ID.
	 * @param array $overlay  Overlay payload.
	 * @return array Overlay payload including fetch token.
	 */
	public static function stash( $order_id, array $overlay ) {
		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return $overlay;
		}

		$token = wp_generate_password( 32, false, false );
		$overlay['fetchToken'] = $token;

		set_transient( self::TRANSIENT_PREFIX . $order_id, $overlay, 15 * MINUTE_IN_SECONDS );
		set_transient( self::TRANSIENT_PREFIX . 'token_' . $token, $order_id, 15 * MINUTE_IN_SECONDS );

		return $overlay;
	}

	/**
	 * Permission check for overlay REST endpoint.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public static function can_read_overlay( $request ) {
		$order_id = absint( $request->get_param( 'order_id' ) );
		$order    = wc_get_order( $order_id );

		if ( ! $order || 'vms_efwp' !== $order->get_payment_method() ) {
			return false;
		}

		$key = (string) $request->get_param( 'key' );
		if ( $key && hash_equals( $order->get_order_key(), $key ) ) {
			return true;
		}

		$token = (string) $request->get_param( 'token' );
		if ( $token ) {
			$token_order = get_transient( self::TRANSIENT_PREFIX . 'token_' . $token );
			return (int) $token_order === $order_id;
		}

		if ( $order->needs_payment() && get_transient( self::TRANSIENT_PREFIX . $order_id ) ) {
			return true;
		}

		return current_user_can( 'pay_for_order', $order_id );
	}

	/**
	 * Return overlay payload for an order.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_overlay( $request ) {
		$order_id = absint( $request->get_param( 'order_id' ) );
		$overlay  = get_transient( self::TRANSIENT_PREFIX . $order_id );

		if ( ! is_array( $overlay ) || empty( $overlay['sessionId'] ) ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return new WP_Error( 'invalid_order', __( 'Order not found.', 'vms-elements-fastspring-woo-payment' ), array( 'status' => 404 ) );
			}

			$session_id = (string) $order->get_meta( '_vms_efwp_session_id' );
			if ( '' === $session_id ) {
				return new WP_Error( 'missing_session', __( 'FastSpring session not found for this order.', 'vms-elements-fastspring-woo-payment' ), array( 'status' => 404 ) );
			}

			$overlay = array(
				'sessionId'  => $session_id,
				'successUrl' => $order->get_checkout_order_received_url(),
				'cancelUrl'  => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' ),
				'orderId'    => $order_id,
				'orderKey'   => $order->get_order_key(),
			);
		}

		if ( empty( $overlay['pushPayload'] ) && function_exists( 'WC' ) && WC()->payment_gateways() ) {
			$order = isset( $order ) ? $order : wc_get_order( $order_id );
			if ( $order ) {
				$gateways = WC()->payment_gateways()->payment_gateways();
				if ( isset( $gateways['vms_efwp'] ) && $gateways['vms_efwp'] instanceof VMS_EFWP_WC_Gateway ) {
					$overlay['pushPayload'] = $gateways['vms_efwp']->build_sbl_checkout_payload( $order );
					$overlay['useSecure']   = $gateways['vms_efwp']->uses_sbl_secure();
					$overlay['tags']        = VMS_EFWP_Data_Store::build_session_tags( $order_id );
				}
			}
		}

		return rest_ensure_response( $overlay );
	}

	/**
	 * Confirm a FastSpring payment and mark the WooCommerce order paid.
	 *
	 * Used when the popup closes successfully. Webhooks remain the source of
	 * truth in production, but localhost and slow webhook delivery need this fallback.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function complete_payment( $request ) {
		$order_id    = absint( $request->get_param( 'order_id' ) );
		$fs_order_id = sanitize_text_field( (string) $request->get_param( 'fs_order_id' ) );

		if ( '' === $fs_order_id ) {
			return new WP_Error(
				'missing_fs_order',
				__( 'FastSpring order id is required.', 'vms-elements-fastspring-woo-payment' ),
				array( 'status' => 400 )
			);
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new WP_Error(
				'invalid_order',
				__( 'Order not found.', 'vms-elements-fastspring-woo-payment' ),
				array( 'status' => 404 )
			);
		}

		if ( $order->is_paid() ) {
			self::persist_fastspring_order_for_wc( $order, $fs_order_id );
			return rest_ensure_response(
				array(
					'status'      => 'already_paid',
					'order_id'    => $order_id,
					'fs_order_id' => $fs_order_id,
				)
			);
		}

		if ( ! function_exists( 'vms_efwp' ) || ! vms_efwp()->api ) {
			return new WP_Error(
				'plugin_unavailable',
				__( 'FastSpring API client is not available.', 'vms-elements-fastspring-woo-payment' ),
				array( 'status' => 500 )
			);
		}

		$fs_order = self::wait_for_completed_fastspring_order( $fs_order_id, true );
		if ( is_wp_error( $fs_order ) ) {
			if ( 'not_completed' === $fs_order->get_error_code() ) {
				self::persist_fastspring_order_for_wc( $order, $fs_order_id );
				return rest_ensure_response(
					array(
						'status'      => 'pending',
						'order_id'    => $order_id,
						'fs_order_id' => $fs_order_id,
					)
				);
			}
			return $fs_order;
		}

		if ( function_exists( 'vms_efwp' ) && vms_efwp()->api ) {
			$fs_order = vms_efwp()->api->ensure_order_invoice( $fs_order );
		}

		$valid = self::validate_fastspring_order_for_wc_order( $fs_order, $order_id );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		self::apply_fastspring_payment( $order, $fs_order );

		return rest_ensure_response(
			array(
				'status'      => 'completed',
				'order_id'    => $order_id,
				'fs_order_id' => $fs_order_id,
			)
		);
	}

	/**
	 * Poll FastSpring until the order is marked completed (popup closes before API catches up).
	 *
	 * @param string $fs_order_id FastSpring order id.
	 * @param bool   $quick       When true, only a few fast API checks (used by REST confirm).
	 * @return array|WP_Error Completed order payload.
	 */
	public static function wait_for_completed_fastspring_order( $fs_order_id, $quick = false ) {
		if ( ! function_exists( 'vms_efwp' ) || ! vms_efwp()->api ) {
			return new WP_Error(
				'plugin_unavailable',
				__( 'FastSpring API client is not available.', 'vms-elements-fastspring-woo-payment' ),
				array( 'status' => 500 )
			);
		}

		if ( $quick ) {
			return vms_efwp()->api->wait_for_completed_order(
				$fs_order_id,
				array(
					'max_attempts' => (int) apply_filters( 'vms_efwp_complete_payment_quick_attempts', 3 ),
					'wait_ms'      => (int) apply_filters( 'vms_efwp_complete_payment_quick_ms', 150 ),
				)
			);
		}

		return vms_efwp()->api->wait_for_completed_order( $fs_order_id );
	}

	/**
	 * Validate a FastSpring order belongs to a WooCommerce order before completing payment.
	 *
	 * @param array $fs_order  FastSpring order.
	 * @param int   $order_id  WooCommerce order id.
	 * @return true|WP_Error
	 */
	public static function validate_fastspring_order_for_wc_order( $fs_order, $order_id ) {
		$tag_site = VMS_EFWP_Data_Store::resolve_site_url_from_payload( $fs_order );
		if ( $tag_site && ! VMS_EFWP_Data_Store::site_urls_equivalent( $tag_site, VMS_EFWP_Data_Store::get_site_url() ) ) {
			return new WP_Error(
				'site_mismatch',
				__( 'This FastSpring order belongs to a different website.', 'vms-elements-fastspring-woo-payment' ),
				array( 'status' => 403 )
			);
		}

		$tag_wc_id = isset( $fs_order['tags']['wc_order_id'] ) ? (int) $fs_order['tags']['wc_order_id'] : 0;
		if ( $tag_wc_id && $tag_wc_id !== (int) $order_id ) {
			return new WP_Error(
				'order_mismatch',
				__( 'This FastSpring order is linked to a different WooCommerce order.', 'vms-elements-fastspring-woo-payment' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Mark a WooCommerce order paid from a FastSpring order payload.
	 *
	 * @param WC_Order $order    WooCommerce order.
	 * @param array    $fs_order FastSpring order payload.
	 * @return bool
	 */
	public static function apply_fastspring_payment( WC_Order $order, array $fs_order ) {
		if ( function_exists( 'vms_efwp' ) && vms_efwp()->api && ! empty( $fs_order['completed'] ) ) {
			$fs_order = vms_efwp()->api->ensure_order_invoice( $fs_order );
		}

		$fs_order = VMS_EFWP_Data_Store::prepare_payload_for_site( $fs_order, $order->get_id() );
		self::persist_fastspring_order_for_wc( $order, isset( $fs_order['id'] ) ? (string) $fs_order['id'] : '', $fs_order );

		if ( $order->is_paid() ) {
			return true;
		}

		if ( empty( $fs_order['completed'] ) ) {
			return false;
		}

		$account_id = VMS_EFWP_Data_Store::extract_account_id_from_payload( $fs_order );
		if ( $account_id ) {
			$order->update_meta_data( '_vms_efwp_fs_account_id', $account_id );
		}

		$fs_id = isset( $fs_order['id'] ) ? (string) $fs_order['id'] : '';
		$order->payment_complete( $fs_id );
		$order->add_order_note( __( 'Payment captured by FastSpring.', 'vms-elements-fastspring-woo-payment' ) );

		return true;
	}

	/**
	 * Save a FastSpring order payload to the plugin store for this site.
	 *
	 * @param WC_Order   $order       WooCommerce order.
	 * @param string     $fs_order_id FastSpring order ID.
	 * @param array|null $fs_order    Optional already-fetched FastSpring order payload.
	 * @return bool
	 */
	public static function persist_fastspring_order_for_wc( WC_Order $order, $fs_order_id = '', $fs_order = null ) {
		$fs_order_id = sanitize_text_field( (string) $fs_order_id );
		if ( '' === $fs_order_id ) {
			$fs_order_id = (string) $order->get_transaction_id();
		}
		if ( '' === $fs_order_id ) {
			return false;
		}

		if ( ! is_array( $fs_order ) && function_exists( 'vms_efwp' ) && vms_efwp()->api ) {
			$raw = vms_efwp()->api->get_order( $fs_order_id );
			if ( is_wp_error( $raw ) ) {
				return false;
			}
			$fs_order = vms_efwp()->api->parse_order( $raw );
			if ( is_wp_error( $fs_order ) ) {
				return false;
			}
		}

		if ( ! is_array( $fs_order ) ) {
			return false;
		}

		$fs_order = VMS_EFWP_Data_Store::prepare_payload_for_site( $fs_order, $order->get_id() );
		if ( ! VMS_EFWP_Data_Store::should_persist_for_site( $fs_order ) ) {
			return false;
		}

		$is_live = isset( $fs_order['live'] ) ? (bool) $fs_order['live'] : (
			function_exists( 'vms_efwp' ) && vms_efwp()->settings && ! vms_efwp()->settings->is_sandbox()
		);
		$is_test = ! $is_live || ! empty( $fs_order['test'] ) || ! empty( $fs_order['isTest'] );

		return false !== VMS_EFWP_Data_Store::upsert_order( $fs_order, $is_test );
	}
}
